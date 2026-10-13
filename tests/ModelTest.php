<?php

declare(strict_types=1);

namespace MichaelDrennen\Balcony\MoveClient\Tests;

use DateTimeImmutable;
use MichaelDrennen\Balcony\MoveClient\DTO\MoveIndexDTO;
use PHPUnit\Framework\TestCase;

final class ModelTest extends TestCase
{
    public function testDtoMapsToModelAttributes(): void
    {
        $dto = new MoveIndexDTO(new DateTimeImmutable('2026-10-09'), 93.45, 1.25);

        $this->assertSame([
            'observed_at' => '2026-10-09',
            'close_value' => 93.45,
            'day_change' => 1.25,
        ], $dto->asModelAttributes());
    }

    public function testEloquentModelAcceptsDtoAttributes(): void
    {
        if (!class_exists('Illuminate\Database\Eloquent\Model')) {
            $this->markTestSkipped('illuminate/database is not installed.');
        }

        // Eloquent's guarded-attribute check reads the column listing, so the model needs a connection.
        $capsule = new \Illuminate\Database\Capsule\Manager();
        $capsule->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        $dto = new MoveIndexDTO(new DateTimeImmutable('2026-10-08'), 92.20, null);
        $model = new \MichaelDrennen\Balcony\MoveClient\Models\MoveIndexObservation($dto->asModelAttributes());

        $this->assertSame('balcony_move_index_observations', $model->getTable());
        $this->assertNull($model->day_change);
        $this->assertSame(['id'], $model->getGuarded());
    }
}
