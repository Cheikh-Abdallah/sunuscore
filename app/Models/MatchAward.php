<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class MatchAward
{
    public static function manOfTheMatch(int $matchId): ?array
    {
        $sql = "SELECT a.*, p.first_name, p.last_name, t.name AS team_name
                FROM match_awards a
                JOIN players p ON p.id = a.player_id
                JOIN teams t ON t.id = a.team_id
                WHERE a.match_id = :mid AND a.award_type = 'man_of_the_match'
                LIMIT 1";
        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute([':mid' => $matchId]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }
}