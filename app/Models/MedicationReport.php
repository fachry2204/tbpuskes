<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class MedicationReport extends Model
{
    protected $guarded = [];
    protected function casts(): array { return ['report_date' => 'date:Y-m-d', 'medication_taken' => 'boolean', 'has_side_effect' => 'boolean', 'server_received_at' => 'datetime']; }
    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
}
