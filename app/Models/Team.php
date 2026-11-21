<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Team
{
    public static function all(): array
    {
        $sql = "SELECT t.*,
                       (SELECT COUNT(*) FROM competition_teams ct
                        WHERE ct.team_id = t.id AND ct.status = 'active') AS competitions_count
                FROM teams t
                ORDER BY t.name ASC";
        return Database::getInstance()->query($sql)->fetchAll();
    }

    public static function allByCompetition(int $competitionId): array
    {
        $sql = "SELECT t.*, ct.zone_id, ct.pool_id
                FROM teams t
                JOIN competition_teams ct ON ct.team_id = t.id
                WHERE ct.competition_id = :cid AND ct.status = 'active'
                ORDER BY t.name ASC";
        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute([':cid' => $competitionId]);
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $stmt = Database::getInstance()->prepare("SELECT * FROM teams WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $sql = "SELECT 1 FROM teams WHERE slug = :s";
        $params = [':s' => $slug];
        if ($exceptId !== null) {
            $sql .= " AND id <> :id";
            $params[':id'] = $exceptId;
        }
        $sql .= " LIMIT 1";
        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() !== false;
    }

    public static function create(array $data): int
    {
        $sql = "INSERT INTO teams
                (name, slug, neighborhood, president_name, coach_name,
                 founded_year, contact_phone, contact_email, description, created_by)
                VALUES
                (:name, :slug, :neighborhood, :president_name, :coach_name,
                 :founded_year, :contact_phone, :contact_email, :description, :created_by)";
        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute([
            ':name'           => $data['name'],
            ':slug'           => $data['slug'],
            ':neighborhood'   => $data['neighborhood'] ?: null,
            ':president_name' => $data['president_name'] ?: null,
            ':coach_name'     => $data['coach_name'] ?: null,
            ':founded_year'   => $data['founded_year'] !== '' ? (int) $data['founded_year'] : null,
            ':contact_phone'  => $data['contact_phone'] ?: null,
            ':contact_email'  => $data['contact_email'] ?: null,
            ':description'    => $data['description'] ?: null,
            ':created_by'     => $data['created_by'] ?? null,
        ]);
        return (int) Database::getInstance()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $sql = "UPDATE teams SET
                    name = :name, slug = :slug, neighborhood = :neighborhood,
                    president_name = :president_name, coach_name = :coach_name,
                    founded_year = :founded_year, contact_phone = :contact_phone,
                    contact_email = :contact_email, description = :description
                WHERE id = :id";
        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute([
            ':id'             => $id,
            ':name'           => $data['name'],
            ':slug'           => $data['slug'],
            ':neighborhood'   => $data['neighborhood'] ?: null,
            ':president_name' => $data['president_name'] ?: null,
            ':coach_name'     => $data['coach_name'] ?: null,
            ':founded_year'   => $data['founded_year'] !== '' ? (int) $data['founded_year'] : null,
            ':contact_phone'  => $data['contact_phone'] ?: null,
            ':contact_email'  => $data['contact_email'] ?: null,
            ':description'    => $data['description'] ?: null,
        ]);
    }

    public static function countDependencies(int $id): int
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            "SELECT (SELECT COUNT(*) FROM matches WHERE home_team_id = :id OR away_team_id = :id2)
                  + (SELECT COUNT(*) FROM competition_teams WHERE team_id = :id3)"
        );
        $stmt->execute([':id' => $id, ':id2' => $id, ':id3' => $id]);
        return (int) $stmt->fetchColumn();
    }

    public static function delete(int $id): void
    {
        $stmt = Database::getInstance()->prepare("DELETE FROM teams WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    public static function roster(int $teamId, int $competitionId): array
    {
        $sql = "SELECT p.id, p.first_name, p.last_name, p.photo_path,
                       r.jersey_number, r.position, r.status
                FROM competition_rosters r
                JOIN players p ON p.id = r.player_id
                WHERE r.team_id = :tid AND r.competition_id = :cid
                ORDER BY r.jersey_number IS NULL, r.jersey_number ASC, p.last_name ASC";
        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute([':tid' => $teamId, ':cid' => $competitionId]);
        return $stmt->fetchAll();
    }
}