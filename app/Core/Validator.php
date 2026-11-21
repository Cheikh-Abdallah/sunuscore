<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Validateur minimaliste basé sur des règles déclaratives.
 * Retourne un tableau d'erreurs (champ => [messages]).
 */
final class Validator
{
    private array $data;
    private array $errors = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public static function make(array $data): self
    {
        return new self($data);
    }

    public function required(string $field, ?string $label = null): self
    {
        $label = $label ?? $field;
        $value = $this->data[$field] ?? null;
        if ($value === null || (is_string($value) && trim($value) === '') || (is_array($value) && empty($value))) {
            $this->errors[$field][] = "Le champ {$label} est obligatoire.";
        }
        return $this;
    }

    public function email(string $field): self
    {
        $value = $this->data[$field] ?? null;
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = "Le champ {$field} doit être un email valide.";
        }
        return $this;
    }

    public function min(string $field, int $min): self
    {
        $value = $this->data[$field] ?? null;
        if (is_string($value) && mb_strlen($value) < $min) {
            $this->errors[$field][] = "Le champ {$field} doit contenir au moins {$min} caractères.";
        }
        return $this;
    }

    public function max(string $field, int $max): self
    {
        $value = $this->data[$field] ?? null;
        if (is_string($value) && mb_strlen($value) > $max) {
            $this->errors[$field][] = "Le champ {$field} ne doit pas dépasser {$max} caractères.";
        }
        return $this;
    }

    public function int(string $field): self
    {
        $value = $this->data[$field] ?? null;
        if ($value !== null && $value !== '' && filter_var($value, FILTER_VALIDATE_INT) === false) {
            $this->errors[$field][] = "Le champ {$field} doit être un entier.";
        }
        return $this;
    }

    public function in(string $field, array $allowed): self
    {
        $value = $this->data[$field] ?? null;
        if ($value !== null && $value !== '' && !in_array($value, $allowed, true)) {
            $this->errors[$field][] = "Le champ {$field} contient une valeur non autorisée.";
        }
        return $this;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }
}