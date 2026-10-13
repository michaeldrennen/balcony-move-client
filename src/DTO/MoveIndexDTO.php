<?php

declare(strict_types=1);

namespace MichaelDrennen\Balcony\MoveClient\DTO;

use DateTimeImmutable;

final class MoveIndexDTO implements MetricObservation
{
    public function __construct(
        public readonly DateTimeImmutable $observedAt,
        public readonly float $closeValue,
        public readonly ?float $dayChange,
    ) {
    }

    public function asModelAttributes(): array
    {
        return [
            'observed_at' => $this->observedAt->format('Y-m-d'),
            'close_value' => $this->closeValue,
            'day_change' => $this->dayChange,
        ];
    }
}
