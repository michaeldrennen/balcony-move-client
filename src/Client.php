<?php

declare(strict_types=1);

namespace MichaelDrennen\Balcony\MoveClient;

use DateTimeImmutable;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use MichaelDrennen\Balcony\MoveClient\DTO\MoveIndexDTO;
use MichaelDrennen\Balcony\MoveClient\Exceptions\BalconyClientException;

final class Client
{
    private readonly ClientInterface $http;

    public function __construct(
        private readonly Config $config,
        ?ClientInterface $http = null,
    ) {
        $this->http = $http ?? new GuzzleClient([
            'timeout' => $config->timeoutSeconds,
            'http_errors' => false,
        ]);
    }

    /**
     * @return MoveIndexDTO[] oldest first
     */
    public function fetchIndex(): array
    {
        try {
            $response = $this->http->request('GET', $this->config->url);
        } catch (GuzzleException $e) {
            throw BalconyClientException::transport($e->getMessage(), $e);
        }

        $status = $response->getStatusCode();

        if ($status === 429) {
            $retryHeader = $response->getHeaderLine('Retry-After');
            throw BalconyClientException::rateLimited(is_numeric($retryHeader) ? (int) $retryHeader : null);
        }

        if ($status >= 400) {
            throw BalconyClientException::httpError($status, (string) $response->getBody());
        }

        return $this->parse((string) $response->getBody());
    }

    /**
     * Parse Stooq CSV (Date,Open,High,Low,Close,Volume). Day change is derived
     * from the previous trading day in the feed; the first row has a null change.
     *
     * @return MoveIndexDTO[] oldest first
     */
    public function parse(string $csv): array
    {
        $csv = ltrim($csv, "\xEF\xBB\xBF");
        $lines = preg_split('/\r\n|\r|\n/', trim($csv)) ?: [];

        if ($lines === []) {
            throw BalconyClientException::malformed('MOVE CSV is empty.');
        }

        $header = array_map(static fn (string $h): string => strtolower(trim($h)), str_getcsv(array_shift($lines)));
        $dateIdx = array_search('date', $header, true);
        $closeIdx = array_search('close', $header, true);

        if ($dateIdx === false || $closeIdx === false) {
            throw BalconyClientException::malformed('MOVE CSV is missing Date or Close column.');
        }

        $points = [];

        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }

            $cells = str_getcsv($line);
            $date = self::parseDate($cells[$dateIdx] ?? null);
            $close = self::parseNumber($cells[$closeIdx] ?? null);

            if ($date === null || $close === null) {
                continue;
            }

            $points[] = ['date' => $date, 'close' => $close];
        }

        usort($points, static fn (array $a, array $b): int => $a['date'] <=> $b['date']);

        $rows = [];
        $previousClose = null;

        foreach ($points as $point) {
            $rows[] = new MoveIndexDTO(
                observedAt: $point['date'],
                closeValue: $point['close'],
                dayChange: $previousClose === null ? null : round($point['close'] - $previousClose, 4),
            );
            $previousClose = $point['close'];
        }

        return $rows;
    }

    public static function parseNumber(?string $raw): ?float
    {
        if ($raw === null) {
            return null;
        }

        $trimmed = trim($raw);

        return ($trimmed !== '' && is_numeric($trimmed)) ? (float) $trimmed : null;
    }

    public static function parseDate(?string $raw): ?DateTimeImmutable
    {
        if ($raw === null) {
            return null;
        }

        $trimmed = trim($raw);
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $trimmed);

        return ($date !== false && $date->format('Y-m-d') === $trimmed) ? $date : null;
    }
}
