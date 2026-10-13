# balcony-move-client

PHP 8.1+ client for the ICE BofA MOVE bond-volatility index, sourced from Stooq historical CSV, part of The Balcony macro flow system.

## Usage (framework-agnostic)

```php
use MichaelDrennen\Balcony\MoveClient\Client;
use MichaelDrennen\Balcony\MoveClient\Config;

$client = new Client(new Config());

foreach ($client->fetchIndex() as $dto) {
    // $dto->observedAt, $dto->closeValue, $dto->dayChange (?float)
}
```

Results are returned oldest first. `dayChange` is the difference from the previous trading day in the feed, and is `null` for the first row.

## Laravel persistence

```php
use MichaelDrennen\Balcony\MoveClient\Models\MoveIndexObservation;

foreach ($client->fetchIndex() as $dto) {
    MoveIndexObservation::updateOrCreate(
        ['observed_at' => $dto->observedAt->format('Y-m-d')],
        $dto->asModelAttributes(),
    );
}
```

## Errors

Failures throw `MichaelDrennen\Balcony\MoveClient\Exceptions\BalconyClientException`. HTTP 429 sets `isRateLimited()` and exposes `retryAfterSeconds`.

## Tests

```
composer install
vendor/bin/phpunit
```
