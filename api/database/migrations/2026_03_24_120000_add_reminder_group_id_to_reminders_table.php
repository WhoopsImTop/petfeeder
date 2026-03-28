<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reminders', function (Blueprint $table) {
            $table->uuid('reminder_group_id')->nullable()->after('household_id');
            $table->index('reminder_group_id');
        });
    }

    public function down(): void
    {
        Schema::table('reminders', function (Blueprint $table) {
            $table->dropIndex(['reminder_group_id']);
            $table->dropColumn('reminder_group_id');
        });
    }
};
