<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table): void {
            $table->string('tb_diagnosis')->nullable()->after('treatment_start_date');
            $table->string('diagnosis_type')->nullable()->after('tb_diagnosis');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table): void {
            $table->dropColumn(['tb_diagnosis', 'diagnosis_type']);
        });
    }
};
