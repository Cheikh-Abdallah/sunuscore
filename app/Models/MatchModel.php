<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class MatchModel
{
    private const BASE_SELECT = "
        SELECT m.*,
               c.name  AS competition_name,
               c.slug  AS competition_slug,
               th.name AS home_team_name, th.slug AS home_team_slug, th.logo_path AS home_team_logo,
               ta.name AS away_team_name, ta.slug AS away_team_slug, ta.logo_path AS away_team_logo
        FROM matches m
        JOIN competitions c ON c.id = m.competition_id
        JOIN teams th ON th.id = m.home_team_id
        JOIN teams ta ON ta.id = m.away_team_id
    ";

    /* ---------- Lecture publique (Phase 2) ---------- */

    public static function byCompetition(int $competitionId, string $filter = 'all'): array
    {
        $where = ['m.competition_id = :cid'];
        if ($filter === 'live')      $where[] = "m.status IN ('live','half_time','suspended')";
        if ($filter === 'upcoming')  $where[] = "m.status IN ('scheduled','postponed')";
        if ($filter === 'finished')  $where[] = "m.status = 'finished'";
        if ($filter === 'postponed') $where[] = "m.status = 'postponed'";

        $sql = self::BASE_SELECT
            . ' WHERE ' . implode(' AND ', $where)
            . ' ORDER BY m.scheduled_at ASC';

        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute([':cid' => $competitionId]);
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $stmt = Database::getInstance()->prepare(self::BASE_SELECT . ' WHERE m.id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function currentLive(): ?array
    {
        $sql = self::BASE_SELECT . " WHERE m.status IN ('live','half_time','suspended') ORDER BY m.scheduled_at ASC LIMIT 1";
        $row = Database::getInstance()->query($sql)->fetch();
        return $row === false ? null : $row;
    }

    public static function nextUpcoming(int $competitionId): ?array
    {
        $sql = self::BASE_SELECT . " WHERE m.competition_id = :cid AND m.status = 'scheduled'
                                     AND m.scheduled_at >= NOW()
                                     ORDER BY m.scheduled_at ASC LIMIT 1";
        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute([':cid' => $competitionId]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function recentFinished(int $competitionId, int $limit = 5): array
    {
        $sql = self::BASE_SELECT . " WHERE m.competition_id = :cid AND m.status = 'finished'
                                     ORDER BY m.finished_at DESC, m.scheduled_at DESC
                                     LIMIT :lim";
        $stmt = Database::getInstance()->prepare($sql);
        $stmt->bindValue(':cid', $competitionId, \PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function allRecentFinished(int $limit = 5): array
    {
        $sql = self::BASE_SELECT . " WHERE m.status = 'finished'
                                     ORDER BY m.finished_at DESC LIMIT :lim";
        $stmt = Database::getInstance()->prepare($sql);
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /* ---------- Lecture admin ---------- */

    /** Tous les matchs avec filtres admin. */
    public static function allForAdmin(string $filter = 'all'): array
    {
        $where = ['1=1'];
        if ($filter === 'upcoming')   $where[] = "m.status IN ('scheduled','postponed') AND m.scheduled_at >= NOW()";
        if ($filter === 'finished')   $where[] = "m.status = 'finished'";
        if ($filter === 'live')       $where[] = "m.status IN ('live','half_time','suspended')";
        if ($filter === 'postponed')  $where[] = "m.status = 'postponed'";

        $sql = self::BASE_SELECT
            . ' WHERE ' . implode(' AND ', $where)
            . ' ORDER BY m.scheduled_at DESC';

        return Database::getInstance()->query($sql)->fetchAll();
    }

    /* ---------- Validation métier ---------- */

    /**
     * Vérifie qu'une équipe est bien inscrite à une compétition.
     */
    public static function isTeamInCompetition(int $teamId, int $competitionId): bool
    {
        $stmt = Database::getInstance()->prepare(
            "SELECT 1 FROM competition_teams
             WHERE team_id = :tid AND competition_id = :cid AND status = 'active'
             LIMIT 1"
        );
        $stmt->execute([':tid' => $teamId, ':cid' => $competitionId]);
        return $stmt->fetchColumn() !== false;
    }

    /**
     * Vérifie si l'une des deux équipes a déjà un match au même créneau
     * (même date/heure exacte), hors le match en cours d'édition.
     * Retourne le libellé du conflit ou null si aucun.
     */
    public static function findScheduleConflict(
        int $homeId,
        int $awayId,
        string $scheduledAt,
        ?int $exceptId = null
    ): ?string {
        $sql = "SELECT m.id, m.scheduled_at, th.name AS home_name, ta.name AS away_name
                FROM matches m
                JOIN teams th ON th.id = m.home_team_id
                JOIN teams ta ON ta.id = m.away_team_id
                WHERE m.scheduled_at = :sa
                  AND m.status IN ('scheduled','live','half_time','suspended')
                  AND (m.home_team_id IN (:h, :a) OR m.away_team_id IN (:h2, :a2))";
        $params = [':sa' => $scheduledAt, ':h' => $homeId, ':a' => $awayId, ':h2' => $homeId, ':a2' => $awayId];
        if ($exceptId !== null) {
            $sql .= " AND m.id <> :id";
            $params[':id'] = $exceptId;
        }
        $sql .= " LIMIT 1";
        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        if ($row === false) return null;
        return "Conflit : {$row['home_name']} vs {$row['away_name']} déjà programmé à " .
               date('d/m/Y H\hi', strtotime($row['scheduled_at'])) . ".";
    }

    /**
     * Vérifie l'absence de doublon : même compétition + mêmes équipes + même jour.
     */
    public static function findDuplicate(
        int $competitionId,
        int $homeId,
        int $awayId,
        string $scheduledAt,
        ?int $exceptId = null
    ): bool {
        $sql = "SELECT 1 FROM matches
                WHERE competition_id = :cid
                  AND home_team_id = :h
                  AND away_team_id = :a
                  AND DATE(scheduled_at) = DATE(:sa)
                  AND status <> 'cancelled'";
        $params = [':cid' => $competitionId, ':h' => $homeId, ':a' => $awayId, ':sa' => $scheduledAt];
        if ($exceptId !== null) {
            $sql .= " AND id <> :id";
            $params[':id'] = $exceptId;
        }
        $sql .= " LIMIT 1";
        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() !== false;
    }

    /* ---------- CRUD ---------- */

    public static function create(array $data): int
    {
        $sql = "INSERT INTO matches
                (competition_id, home_team_id, away_team_id, zone_id, pool_id,
                 matchday, phase, venue, scheduled_at, call_time, status, created_by)
                VALUES
                (:cid, :h, :a, :zone, :pool,
                 :matchday, :phase, :venue, :sa, :ct, :status, :created_by)";
        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute([
            ':cid'         => $data['competition_id'],
            ':h'           => $data['home_team_id'],
            ':a'           => $data['away_team_id'],
            ':zone'        => $data['zone_id'] ?: null,
            ':pool'        => $data['pool_id'] ?: null,
            ':matchday'    => $data['matchday'] !== '' ? (int) $data['matchday'] : null,
            ':phase'       => $data['phase'] ?: null,
            ':venue'       => $data['venue'] ?: null,
            ':sa'          => $data['scheduled_at'],
            ':ct'          => $data['call_time'] ?: null,
            ':status'      => $data['status'],
            ':created_by'  => $data['created_by'] ?? null,
        ]);
        return (int) Database::getInstance()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $sql = "UPDATE matches SET
                    competition_id = :cid,
                    home_team_id = :h,
                    away_team_id = :a,
                    matchday = :matchday,
                    phase = :phase,
                    venue = :venue,
                    scheduled_at = :sa,
                    call_time = :ct,
                    status = :status
                WHERE id = :id";
        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute([
            ':id'          => $id,
            ':cid'         => $data['competition_id'],
            ':h'           => $data['home_team_id'],
            ':a'           => $data['away_team_id'],
            ':matchday'    => $data['matchday'] !== '' ? (int) $data['matchday'] : null,
            ':phase'       => $data['phase'] ?: null,
            ':venue'       => $data['venue'] ?: null,
            ':sa'          => $data['scheduled_at'],
            ':ct'          => $data['call_time'] ?: null,
            ':status'      => $data['status'],
        ]);
    }

    public static function countDependencies(int $id): int
    {
        $stmt = Database::getInstance()->prepare(
            "SELECT COUNT(*) FROM match_events WHERE match_id = :id"
        );
        $stmt->execute([':id' => $id]);
        return (int) $stmt->fetchColumn();
    }

    public static function delete(int $id): void
    {
        $stmt = Database::getInstance()->prepare("DELETE FROM matches WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    /** Renvoie les équipes actives d'une compétition pour les selects. */
    public static function teamsForSelect(int $competitionId): array
    {
        $stmt = Database::getInstance()->prepare(
            "SELECT t.id, t.name FROM teams t
             JOIN competition_teams ct ON ct.team_id = t.id
             WHERE ct.competition_id = :cid AND ct.status = 'active'
             ORDER BY t.name ASC"
        );
        $stmt->execute([':cid' => $competitionId]);
        return $stmt->fetchAll();
    }
}