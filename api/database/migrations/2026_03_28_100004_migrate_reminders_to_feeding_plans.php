<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('reminders')) {
            return;
        }

        $reminders = DB::table('reminders')->orderBy('id')->get();
        if ($reminders->isEmpty()) {
            Schema::drop('reminders');

            return;
        }

        $reminders = collect($reminders);

        $weekdaysAll = [1, 2, 3, 4, 5, 6, 7];

        $slotKeys = []; // "planId|time|activityTypeId" => true

        $addSlot = function (int $planId, object $r) use (&$slotKeys, $weekdaysAll): void {
            $key = $planId.'|'.$r->time.'|'.$r->activity_type_id;
            if (isset($slotKeys[$key])) {
                return;
            }
            $slotKeys[$key] = true;
            $weekdays = match ($r->frequency ?? 'daily') {
                'daily' => $weekdaysAll,
                default => $weekdaysAll,
            };
            DB::table('feeding_plan_slots')->insert([
                'feeding_plan_id' => $planId,
                'activity_type_id' => $r->activity_type_id,
                'title' => $r->title,
                'time' => $r->time,
                'weekdays' => json_encode(array_values($weekdays)),
                'is_active' => (bool) $r->is_active,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        };

        $ensurePetOnPlan = function (int $petId, int $planId): void {
            $row = DB::table('feeding_plan_pet')->where('pet_id', $petId)->first();
            if ($row) {
                return;
            }
            DB::table('feeding_plan_pet')->insert([
                'feeding_plan_id' => $planId,
                'pet_id' => $petId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        };

        // 1) Grouped reminders (same push group)
        $grouped = $reminders->filter(fn ($r) => $r->reminder_group_id !== null)->groupBy('reminder_group_id');
        foreach ($grouped as $rows) {
            $rows = collect($rows)->sortBy('id')->values();
            $planId = null;
            foreach ($rows as $r) {
                $existing = DB::table('feeding_plan_pet')->where('pet_id', $r->pet_id)->first();
                if ($existing) {
                    $planId = (int) $existing->feeding_plan_id;
                    break;
                }
            }
            if ($planId === null) {
                $first = $rows->first();
                $planId = (int) DB::table('feeding_plans')->insertGetId([
                    'household_id' => $first->household_id,
                    'name' => $first->title ?: 'Futterplan',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            foreach ($rows->pluck('pet_id')->unique() as $petId) {
                $ensurePetOnPlan((int) $petId, $planId);
            }
            foreach ($rows->unique(fn ($r) => $r->time.'|'.$r->activity_type_id) as $r) {
                $addSlot($planId, $r);
            }
        }

        // 2) Solo reminders
        foreach ($reminders->filter(fn ($r) => $r->reminder_group_id === null) as $r) {
            $existing = DB::table('feeding_plan_pet')->where('pet_id', $r->pet_id)->first();
            if ($existing) {
                $planId = (int) $existing->feeding_plan_id;
            } else {
                $planId = (int) DB::table('feeding_plans')->insertGetId([
                    'household_id' => $r->household_id,
                    'name' => $r->title ?: 'Futterplan',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $ensurePetOnPlan((int) $r->pet_id, $planId);
            }
            $addSlot($planId, $r);
        }

        Schema::drop('reminders');
    }

    public function down(): void
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('activity_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->uuid('reminder_group_id')->nullable();
            $table->index('reminder_group_id');
            $table->string('title');
            $table->time('time');
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'custom'])->default('daily');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
};
