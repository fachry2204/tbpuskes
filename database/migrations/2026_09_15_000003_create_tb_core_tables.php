<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('treatment_places', function (Blueprint $table): void {
            $table->id(); $table->string('name'); $table->string('type'); $table->text('address'); $table->string('phone')->nullable(); $table->decimal('latitude', 10, 7)->nullable(); $table->decimal('longitude', 10, 7)->nullable(); $table->boolean('is_active')->default(true); $table->timestamps();
        });
        Schema::create('cadres', function (Blueprint $table): void {
            $table->id(); $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete(); $table->string('nik', 20)->unique(); $table->string('full_name'); $table->string('birth_place'); $table->date('birth_date'); $table->enum('gender', ['L', 'P']); $table->string('phone')->unique(); $table->string('rt', 3); $table->string('rw', 3); $table->text('full_address'); $table->string('working_area')->nullable(); $table->string('photo_path')->nullable(); $table->boolean('is_active')->default(true); $table->timestamps(); $table->softDeletes();
        });
        Schema::create('patients', function (Blueprint $table): void {
            $table->id(); $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete(); $table->string('medical_record_number')->nullable()->unique(); $table->string('nik', 20)->unique(); $table->string('full_name'); $table->string('birth_place'); $table->date('birth_date'); $table->enum('gender', ['L', 'P']); $table->string('phone')->index(); $table->string('rt', 3); $table->string('rw', 3); $table->text('full_address'); $table->foreignId('treatment_place_id')->constrained(); $table->foreignId('cadre_id')->nullable()->constrained('cadres')->nullOnDelete(); $table->date('treatment_start_date'); $table->unsignedTinyInteger('daily_dose_frequency')->default(1); $table->enum('status', ['active', 'completed', 'paused', 'moved', 'deceased'])->default('active')->index(); $table->string('photo_path')->nullable(); $table->decimal('home_latitude', 10, 7)->nullable(); $table->decimal('home_longitude', 10, 7)->nullable(); $table->timestamps(); $table->softDeletes();
        });
        Schema::create('medications', function (Blueprint $table): void {
            $table->id(); $table->string('name'); $table->string('generic_name')->nullable(); $table->string('strength')->nullable(); $table->string('unit'); $table->text('description')->nullable(); $table->string('icon_png_path')->nullable(); $table->boolean('is_active')->default(true); $table->timestamps();
        });
        Schema::create('patient_medication_plans', function (Blueprint $table): void {
            $table->id(); $table->foreignId('patient_id')->constrained()->cascadeOnDelete(); $table->foreignId('medication_id')->constrained(); $table->string('dose'); $table->string('dose_unit'); $table->unsignedTinyInteger('frequency_per_day'); $table->date('start_date'); $table->date('end_date')->nullable(); $table->text('instructions')->nullable(); $table->boolean('is_active')->default(true); $table->timestamps();
        });
        Schema::create('medication_schedule_times', function (Blueprint $table): void {
            $table->id(); $table->foreignId('patient_medication_plan_id')->constrained()->cascadeOnDelete(); $table->time('time_of_day'); $table->string('label')->nullable(); $table->unsignedTinyInteger('sequence'); $table->timestamps();
        });
        Schema::create('medication_reports', function (Blueprint $table): void {
            $table->id(); $table->foreignId('patient_id')->constrained()->cascadeOnDelete(); $table->foreignId('patient_medication_plan_id')->nullable()->constrained()->nullOnDelete(); $table->foreignId('schedule_time_id')->nullable()->constrained('medication_schedule_times')->nullOnDelete(); $table->date('report_date'); $table->time('scheduled_time')->nullable(); $table->boolean('medication_taken'); $table->text('not_taken_reason')->nullable(); $table->boolean('has_side_effect')->default(false); $table->string('side_effect_category')->nullable(); $table->text('side_effect_description')->nullable(); $table->string('photo_path')->nullable(); $table->string('photo_hash', 64)->nullable(); $table->decimal('latitude', 10, 7)->nullable(); $table->decimal('longitude', 10, 7)->nullable(); $table->decimal('gps_accuracy', 8, 2)->nullable(); $table->text('formatted_address')->nullable(); $table->timestamp('device_reported_at')->nullable(); $table->timestamp('server_received_at')->useCurrent(); $table->enum('status', ['submitted', 'verified', 'rejected', 'follow_up'])->default('submitted'); $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamp('verified_at')->nullable(); $table->text('verification_note')->nullable(); $table->timestamps(); $table->unique(['patient_id', 'schedule_time_id', 'report_date'], 'report_slot_unique');
        });
    }
    public function down(): void { Schema::dropIfExists('medication_reports'); Schema::dropIfExists('medication_schedule_times'); Schema::dropIfExists('patient_medication_plans'); Schema::dropIfExists('medications'); Schema::dropIfExists('patients'); Schema::dropIfExists('cadres'); Schema::dropIfExists('treatment_places'); }
};
