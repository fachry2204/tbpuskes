<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void { Schema::table('medication_schedule_times', function (Blueprint $table): void { $table->time('time_of_day')->nullable()->change(); }); }
    public function down(): void { Schema::table('medication_schedule_times', function (Blueprint $table): void { $table->time('time_of_day')->nullable(false)->change(); }); }
};
