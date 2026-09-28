<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Patient extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['user_id', 'medical_record_number', 'nik', 'full_name', 'birth_place', 'birth_date', 'gender', 'phone', 'rt', 'rw', 'full_address', 'treatment_place_id', 'cadre_id', 'treatment_start_date', 'tb_diagnosis', 'diagnosis_type', 'daily_dose_frequency', 'status', 'photo_path', 'home_latitude', 'home_longitude'];
    protected function casts(): array { return ['birth_date' => 'date', 'treatment_start_date' => 'date']; }
    protected function age(): Attribute { return Attribute::get(fn (): int => Carbon::parse($this->birth_date)->age); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function cadre(): BelongsTo { return $this->belongsTo(Cadre::class); }
}
