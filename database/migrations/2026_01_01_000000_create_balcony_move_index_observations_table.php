<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('balcony_move_index_observations', function (Blueprint $table): void {
            $table->id();
            $table->date('observed_at')->unique('balcony_move_observed_at_unique');
            $table->decimal('close_value', 8, 4);
            $table->decimal('day_change', 8, 4)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('balcony_move_index_observations');
    }
};
