<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->foreignId('feeding_plan_slot_id')->nullable()->after('activity_type_id')->constrained('feeding_plan_slots')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropForeign(['feeding_plan_slot_id']);
            $table->dropColumn('feeding_plan_slot_id');
        });
    }
};
