<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feeding_plan_pet', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feeding_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pet_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique('pet_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feeding_plan_pet');
    }
};
