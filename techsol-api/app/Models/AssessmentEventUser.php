<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentEventUser extends Model
{
    use HasFactory;

    protected $table = 'assessment_event_user';

    protected $fillable = [
        'assessment_event_id',
        'user_id',
        'school_class_id',
        'scheduled_date',
        'invite_sent_at',
        'credential_code',
        'credential_sent_at',
        'completed_at',
        'status',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'invite_sent_at' => 'datetime',
        'credential_sent_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // -----------------------------
    // Relações
    // -----------------------------

    public function event()
    {
        return $this->belongsTo(AssessmentEvent::class, 'assessment_event_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    // -----------------------------
    // Acessores para a timeline
    // -----------------------------

    public function getSchedulingStatusAttribute(): string
    {
        return $this->scheduled_date ? 'scheduled' : 'not_scheduled';
    }

    public function getCredentialStatusAttribute(): string
    {
        if ($this->credential_sent_at) {
            return 'sent';
        }

        if ($this->invite_sent_at) {
            return 'invited';
        }

        return 'not_sent';
    }

    public function getExamStatusAttribute(): string
    {
        if ($this->completed_at) {
            return 'completed';
        }

        if ($this->scheduled_date) {
            return 'scheduled';
        }

        return 'not_started';
    }
}
