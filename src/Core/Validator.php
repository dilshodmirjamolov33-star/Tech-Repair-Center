<?php

declare(strict_types=1);

namespace App\Core;

final class Validator
{
    public static function positiveId(mixed $value): ?int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        return $id === false ? null : (int) $id;
    }

    public static function requiredText(mixed $value, int $maxLength = 255): ?string
    {
        if (!is_string($value)) return null;
        $text = trim($value);
        if ($text === '' || mb_strlen($text, 'UTF-8') > $maxLength) return null;
        return $text;
    }

    public static function orderStatus(mixed $value): ?string
    {
        $allowed = ['received', 'diagnosis', 'repair', 'ready', 'delivered'];
        return is_string($value) && in_array($value, $allowed, true) ? $value : null;
    }

    public static function role(mixed $value): ?string
    {
        $allowed = ['admin', 'receptionist', 'master'];
        return is_string($value) && in_array($value, $allowed, true) ? $value : null;
    }
}
