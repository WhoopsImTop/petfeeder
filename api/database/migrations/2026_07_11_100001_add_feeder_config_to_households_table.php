<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('households', function (Blueprint $table) {
            $table->string('feeder_webhook_token', 64)->nullable()->unique()->after('name');
            $table->boolean('feeder_webhook_enabled')->default(false)->after('feeder_webhook_token');
            $table->foreignId('feeder_action_open_activity_type_id')
                ->nullable()
                ->after('feeder_webhook_enabled')
                ->constrained('activity_types')
                ->nullOnDelete();
            $table->foreignId('feeder_action_stay_closed_activity_type_id')
                ->nullable()
                ->after('feeder_action_open_activity_type_id')
                ->constrained('activity_types')
                ->nullOnDelete();
            $table->foreignId('feeder_action_none_activity_type_id')
                ->nullable()
                ->after('feeder_action_stay_closed_activity_type_id')
                ->constrained('activity_types')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('households', function (Blueprint $table) {
            $table->dropForeign(['feeder_action_open_activity_type_id']);
            $table->dropForeign(['feeder_action_stay_closed_activity_type_id']);
            $table->dropForeign(['feeder_action_none_activity_type_id']);
            $table->dropColumn([
                'feeder_webhook_token',
                'feeder_webhook_enabled',
                'feeder_action_open_activity_type_id',
                'feeder_action_stay_closed_activity_type_id',
                'feeder_action_none_activity_type_id',
            ]);
        });
    }
};
