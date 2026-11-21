<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Protection CSRF - jeton unique par session, comparaison en temps constant.
 *
 * Le nom du champ est FIXÉ à "_csrf_token" (constante) pour éviter toute
 * désynchronisation entre la génération du champ dans le HTML et la lecture
 * côté serveur. Pas de dépendance à config() ici.
 */
final class Csrf
{
    /** Nom du champ caché (identique partout). */
    public const FIELD_NAME = '_csrf_token';

    /** Retourne (ou génère) le jeton de la session courante. */
    public static function token(): string
    {
        Session::start();

        if (!Session::has(self::FIELD_NAME)) {
            Session::set(self::FIELD_NAME, bin2hex(random_bytes(32)));
        }
        return (string) Session::get(self::FIELD_NAME);
    }

    /** Retourne le champ HTML à insérer dans un <form>. */
    public static function field(): string
    {
        $value = htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');
        $name  = self::FIELD_NAME;
        return "<input type=\"hidden\" name=\"{$name}\" value=\"{$value}\">";
    }

    /** Valide un jeton reçu. */
    public static function validate(?string $token): bool
    {
        if ($token === null || $token === '') {
            return false;
        }
        Session::start();
        $stored = (string) Session::get(self::FIELD_NAME, '');
        return $stored !== '' && hash_equals($stored, $token);
    }

    /** Régénère le jeton (après login par exemple). */
    public static function regenerate(): void
    {
        Session::start();
        Session::set(self::FIELD_NAME, bin2hex(random_bytes(32)));
    }

    /** Récupère le jeton envoyé par un formulaire POST. */
    public static function fromPost(): ?string
    {
        $v = $_POST[self::FIELD_NAME] ?? null;
        return is_string($v) ? $v : null;
    }
}