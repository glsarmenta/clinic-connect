<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'doctor_id',
        'service_id',
        'patient_id',
        'queue_number',
        'appointment_date',
        'time_slot',
        'patient_name',
        'patient_age',
        'patient_phone',
        'chief_complaint',
        'status',
        'source',
        'consultation_notes',
        'checked_in_at',
        'consultation_started_at',
        'consultation_ended_at',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
            'patient_age' => 'integer',
            'checked_in_at' => 'datetime',
            'consultation_started_at' => 'datetime',
            'consultation_ended_at' => 'datetime',
        ];
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function medicalRecord(): HasOne
    {
        return $this->hasOne(MedicalRecord::class);
    }
}
