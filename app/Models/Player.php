<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Player
{
    public static function all(): array
    {
        $sql = "SELECT p.* FROM players p ORDER BY p.last_name ASC, p.first_name ASC";
        return Database::getInstance()->query($sql)->fetchAll();
    }

    public static function allByCompetition(int $competitionId): array
    {
        $sql = "SELECT p.*, r.jersey_number, r.position, t.name AS team_name
                FROM players p
                JOIN competition_rosters r ON r.player_id = p.id
                JOIN teams t ON t.id = r.team_id
                WHERE r.competition_id = :cid
                ORDER BY t.name ASC, r.jersey_number IS NULL, r.jersey_number ASC";
        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute([':cid' => $competitionId]);
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $stmt = Database::getInstance()->prepare("SELECT * FROM players WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function create(array $data): int
    {
        $sql = "INSERT INTO players (first_name, last_name, birth_date, phone, created_by)
                VALUES (:first_name, :last_name, :birth_date, :phone, :created_by)";
        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute([
            ':first_name' => $data['first_name'],
            ':last_name'  => $data['last_name'],
            ':birth_date' => $data['birth_date'] ?: null,
            ':phone'      => $data['phone'] ?: null,
            ':created_by' => $data['created_by'] ?? null,
        ]);
        return (int) Database::getInstance()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $sql = "UPDATE players SET
                    first_name = :first_name, last_name = :last_name,
                    birth_date = :birth_date, phone = :phone
                WHERE id = :id";
        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute([
            ':id'         => $id,
            ':first_name' => $data['first_name'],
            ':last_name'  => $data['last_name'],
            ':birth_date' => $data['birth_date'] ?: null,
            ':phone'      => $data['phone'] ?: null,
        ]);
    }

    public static function countDependencies(int $id): int
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            "SELECT (SELECT COUNT(*) FROM match_events WHERE player_id = :id OR related_player_id = :id2)
                  + (SELECT COUNT(*) FROM competition_rosters WHERE player_id = :id3)
                  + (SELECT COUNT(*) FROM match_lineups WHERE player_id = :id4)
                  + (SELECT COUNT(*) FROM match_awards WHERE player_id = :id5)"
        );
        $stmt->execute([':id' => $id, ':id2' => $id, ':id3' => $id, ':id4' => $id, ':id5' => $id]);
        return (int) $stmt->fetchColumn();
    }

    public static function delete(int $id): void
    {
        $stmt = Database::getInstance()->prepare("DELETE FROM players WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }
}