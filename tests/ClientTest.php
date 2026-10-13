<?php

declare(strict_types=1);

namespace MichaelDrennen\Balcony\MoveClient\Tests;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use MichaelDrennen\Balcony\MoveClient\Client;
use MichaelDrennen\Balcony\MoveClient\Config;
use MichaelDrennen\Balcony\MoveClient\Exceptions\BalconyClientException;
use PHPUnit\Framework\TestCase;

final class ClientTest extends TestCase
{
    private function fixture(): string
    {
        return (string) file_get_contents(__DIR__ . '/Fixtures/sample_response.csv');
    }

    public function testParsesOldestFirstWithDayChange(): void
    {
        $dtos = (new Client(new Config()))->parse($this->fixture());

        $this->assertCount(2, $dtos);
        $this->assertSame('2026-10-08', $dtos[0]->observedAt->format('Y-m-d'));
        $this->assertNull($dtos[0]->dayChange);
        $this->assertSame(93.45, $dtos[1]->closeValue);
        $this->assertSame(1.25, $dtos[1]->dayChange);
    }

    public function testFetchUsesMockedHttp(): void
    {
        $http = new GuzzleClient([
            'handler' => HandlerStack::create(new MockHandler([new Response(200, [], $this->fixture())])),
        ]);

        $this->assertCount(2, (new Client(new Config(), $http))->fetchIndex());
    }

    public function testRateLimitThrows(): void
    {
        $http = new GuzzleClient([
            'handler' => HandlerStack::create(new MockHandler([new Response(429, ['Retry-After' => '9'], '')])),
            'http_errors' => false,
        ]);

        try {
            (new Client(new Config(), $http))->fetchIndex();
            $this->fail('Expected BalconyClientException.');
        } catch (BalconyClientException $e) {
            $this->assertTrue($e->isRateLimited());
            $this->assertSame(9, $e->retryAfterSeconds);
        }
    }

    public function testMissingCloseColumnThrows(): void
    {
        $this->expectException(BalconyClientException::class);
        (new Client(new Config()))->parse("Date,Open\n2026-10-09,1\n");
    }
}
