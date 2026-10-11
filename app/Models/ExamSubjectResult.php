<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamSubjectResult extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class, 'exam_attempt_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(ExamResponse::class, 'exam_attempt_id', 'exam_attempt_id')
            ->where('subject_id', $this->subject_id)
            ->orderBy('position');
    }

}
