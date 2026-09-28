<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('patient_medication_plans', function (Blueprint $table): void {
            $table->unsignedBigInteger('medication_id')->nullable()->change();
            $table->string('dose_unit')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::table('patient_medication_plans')->whereNull('medication_id')->orWhereNull('dose_unit')->exists()) {
            throw new \RuntimeException('Lengkapi obat dan satuan pada jadwal sebelum rollback.');
        }
        Schema::table('patient_medication_plans', function (Blueprint $table): void {
            $table->unsignedBigInteger('medication_id')->nullable(false)->change();
            $table->string('dose_unit')->nullable(false)->change();
        });
    }
};
