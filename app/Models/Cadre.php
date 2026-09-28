<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Cadre extends Model
{
    use SoftDeletes;
    protected $fillable = ['user_id', 'nik', 'full_name', 'birth_place', 'birth_date', 'gender', 'phone', 'rt', 'rw', 'full_address', 'working_area', 'photo_path', 'is_active'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function patients(): HasMany { return $this->hasMany(Patient::class); }
}
