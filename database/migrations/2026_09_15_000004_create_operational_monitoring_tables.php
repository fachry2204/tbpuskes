<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('control_schedules', function (Blueprint $table): void {
            $table->id(); $table->foreignId('patient_id')->constrained()->cascadeOnDelete(); $table->foreignId('treatment_place_id')->constrained(); $table->date('control_date')->index(); $table->time('start_time'); $table->time('end_time')->nullable(); $table->string('purpose')->nullable(); $table->text('notes')->nullable(); $table->enum('status', ['scheduled', 'confirmed', 'attended', 'missed', 'rescheduled', 'cancelled'])->default('scheduled')->index(); $table->foreignId('created_by')->constrained('users'); $table->timestamp('attended_at')->nullable(); $table->text('attendance_note')->nullable(); $table->timestamps();
        });
        Schema::create('side_effect_reports', function (Blueprint $table): void {
            $table->id(); $table->foreignId('medication_report_id')->constrained()->cascadeOnDelete(); $table->foreignId('patient_id')->constrained()->cascadeOnDelete(); $table->string('category')->nullable(); $table->text('description'); $table->enum('severity', ['low', 'medium', 'high'])->default('low'); $table->boolean('requires_follow_up')->default(false)->index(); $table->enum('follow_up_status', ['open', 'contacted', 'resolved'])->default('open'); $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete(); $table->timestamp('resolved_at')->nullable(); $table->text('resolution_note')->nullable(); $table->timestamps();
        });
        Schema::create('patient_notes', function (Blueprint $table): void {
            $table->id(); $table->foreignId('patient_id')->constrained()->cascadeOnDelete(); $table->foreignId('author_id')->constrained('users'); $table->text('note'); $table->date('note_date'); $table->enum('type', ['general', 'medication', 'control', 'visit', 'follow_up'])->default('general'); $table->timestamps();
        });
        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id(); $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); $table->string('action'); $table->string('entity_type'); $table->unsignedBigInteger('entity_id')->nullable(); $table->string('ip_address', 45)->nullable(); $table->text('user_agent')->nullable(); $table->json('old_values')->nullable(); $table->json('new_values')->nullable(); $table->timestamp('created_at')->useCurrent(); $table->index(['entity_type', 'entity_id']);
        });
        Schema::create('user_notifications', function (Blueprint $table): void {
            $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->string('title'); $table->text('message'); $table->string('type'); $table->string('related_entity_type')->nullable(); $table->unsignedBigInteger('related_entity_id')->nullable(); $table->boolean('is_read')->default(false)->index(); $table->timestamp('read_at')->nullable(); $table->timestamps();
        });
        Schema::create('settings', function (Blueprint $table): void { $table->id(); $table->string('key')->unique(); $table->longText('value'); $table->string('group')->default('general'); $table->boolean('is_public')->default(false); $table->timestamps(); });
    }
    public function down(): void { Schema::dropIfExists('settings'); Schema::dropIfExists('user_notifications'); Schema::dropIfExists('audit_logs'); Schema::dropIfExists('patient_notes'); Schema::dropIfExists('side_effect_reports'); Schema::dropIfExists('control_schedules'); }
};
