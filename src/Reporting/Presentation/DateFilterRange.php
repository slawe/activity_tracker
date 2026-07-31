<?php

declare(strict_types=1);

namespace App\Reporting\Presentation;

use DateTimeImmutable;

final readonly class DateFilterRange
{
    private function __construct(
        public DateTimeImmutable $dateFrom,
        public DateTimeImmutable $dateTo,
        public DateTimeImmutable $today,
    ) {
    }

    public static function fromInput(?string $dateFrom, ?string $dateTo): self
    {
        $today = new DateTimeImmutable('today');
        $dateFrom = self::date($dateFrom) ?? $today;
        $dateTo = self::date($dateTo) ?? $today;

        if ($dateFrom > $today) {
            $dateFrom = $today;
        }
        if ($dateTo > $today) {
            $dateTo = $today;
        }
        if ($dateFrom > $dateTo) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        return new self($dateFrom, $dateTo, $today);
    }

    private static function date(?string $value): ?DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date !== false && $date->format('Y-m-d') === $value ? $date : null;
    }
}
