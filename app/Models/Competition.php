<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Competition
{
    public static function all(): array
    {
        $sql = "SELECT c.*,
                       (SELECT COUNT(*) FROM competition_teams ct
                        WHERE ct.competition_id = c.id AND ct.status = 'active') AS teams_count
                FROM competitions c
                ORDER BY c.id DESC";
        return Database::getInstance()->query($sql)->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $stmt = Database::getInstance()->prepare(
            "SELECT c.*,
                    (SELECT COUNT(*) FROM competition_teams ct
                     WHERE ct.competition_id = c.id AND ct.status = 'active') AS teams_count
             FROM competitions c WHERE c.id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function findFirstActive(): ?array
    {
        $sql = "SELECT c.*,
                       (SELECT COUNT(*) FROM competition_teams ct
                        WHERE ct.competition_id = c.id AND ct.status = 'active') AS teams_count
                FROM competitions c
                WHERE c.status IN ('ongoing','upcoming')
                ORDER BY FIELD(c.status,'ongoing','upcoming'), c.start_date ASC
                LIMIT 1";
        $row = Database::getInstance()->query($sql)->fetch();
        return $row === false ? null : $row;
    }

    public static function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $sql = "SELECT 1 FROM competitions WHERE slug = :s";
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
        $sql = "INSERT INTO competitions
                (name, slug, description, location, start_date, end_date,
                 points_win, points_draw, points_loss, status, created_by)
                VALUES
                (:name, :slug, :description, :location, :start_date, :end_date,
                 :pw, :pd, :pl, :status, :created_by)";
        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute([
            ':name'        => $data['name'],
            ':slug'        => $data['slug'],
            ':description' => $data['description'] ?: null,
            ':location'    => $data['location'] ?: null,
            ':start_date'  => $data['start_date'] ?: null,
            ':end_date'    => $data['end_date'] ?: null,
            ':pw'          => (int) ($data['points_win'] ?? 3),
            ':pd'          => (int) ($data['points_draw'] ?? 1),
            ':pl'          => (int) ($data['points_loss'] ?? 0),
            ':status'      => $data['status'],
            ':created_by'  => $data['created_by'] ?? null,
        ]);
        return (int) Database::getInstance()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $sql = "UPDATE competitions SET
                    name = :name, slug = :slug, description = :description,
                    location = :location, start_date = :start_date, end_date = :end_date,
                    points_win = :pw, points_draw = :pd, points_loss = :pl,
                    status = :status
                WHERE id = :id";
        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute([
            ':id'          => $id,
            ':name'        => $data['name'],
            ':slug'        => $data['slug'],
            ':description' => $data['description'] ?: null,
            ':location'    => $data['location'] ?: null,
            ':start_date'  => $data['start_date'] ?: null,
            ':end_date'    => $data['end_date'] ?: null,
            ':pw'          => (int) ($data['points_win'] ?? 3),
            ':pd'          => (int) ($data['points_draw'] ?? 1),
            ':pl'          => (int) ($data['points_loss'] ?? 0),
            ':status'      => $data['status'],
        ]);
    }

    /** Retourne le nombre de dépendances bloquantes. */
    public static function countDependencies(int $id): int
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM matches WHERE competition_id = :id");
        $stmt->execute([':id' => $id]);
        return (int) $stmt->fetchColumn();
    }

    public static function delete(int $id): void
    {
        $stmt = Database::getInstance()->prepare("DELETE FROM competitions WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }
}