<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionData extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'submission_id',
        'form_field_id',
        'value',
    ];

    /**
     * Kita juga bisa definisikan relasi kebalikannya di sini
     * untuk kelengkapan, meskipun belum tentu terpakai sekarang.
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function formField(): BelongsTo
    {
        return $this->belongsTo(FormField::class);
    }
}