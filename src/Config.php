<?php

declare(strict_types=1);

namespace MichaelDrennen\Balcony\MoveClient;

final class Config
{
    public const DEFAULT_URL = 'https://stooq.com/q/d/l/?s=^move&i=d';

    public function __construct(
        public readonly string $url = self::DEFAULT_URL,
        public readonly float $timeoutSeconds = 15.0,
    ) {
    }
}
