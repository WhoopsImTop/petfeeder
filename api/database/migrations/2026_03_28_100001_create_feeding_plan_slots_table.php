<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feeding_plan_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feeding_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('activity_type_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->time('time');
            /** @var array<int> ISO-8601 weekday 1=Mon … 7=Sun */
            $table->json('weekdays');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feeding_plan_slots');
    }
};
