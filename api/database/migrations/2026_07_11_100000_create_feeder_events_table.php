<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feeder_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->timestamp('detected_at');
            $table->string('label');
            $table->string('action');
            $table->decimal('confidence', 5, 4);
            $table->string('mouth_status')->nullable();
            $table->json('detections');
            $table->string('image_path')->nullable();
            $table->foreignId('activity_log_id')->nullable()->constrained('activity_logs')->nullOnDelete();
            $table->timestamps();

            $table->index(['household_id', 'detected_at']);
            $table->index(['household_id', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feeder_events');
    }
};
