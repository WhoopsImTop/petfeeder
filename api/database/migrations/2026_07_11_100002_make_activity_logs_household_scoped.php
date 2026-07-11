<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->foreignId('household_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropForeign(['pet_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('pet_id')->nullable()->change();
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->foreign('pet_id')->references('id')->on('pets')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropForeign(['household_id']);
            $table->dropColumn('household_id');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropForeign(['pet_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('pet_id')->nullable(false)->change();
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->foreign('pet_id')->references('id')->on('pets')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
