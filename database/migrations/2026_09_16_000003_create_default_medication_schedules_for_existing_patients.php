<?php

use App\Models\Patient;
use App\Services\PatientMedicationScheduleService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        $service = app(PatientMedicationScheduleService::class);

        Patient::query()
            ->where('status', 'active')
            ->whereNotNull('treatment_start_date')
            ->orderBy('id')
            ->each(fn (Patient $patient) => $service->ensureFor($patient));
    }

    public function down(): void
    {
        // Existing and generated plans are intentionally preserved on rollback.
    }
};
