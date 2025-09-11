<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\SubmissionData;

class Submission extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'form_id',
        'submitted_at',
    ];

    /**
     * Sebuah Submission 'memiliki banyak' SubmissionData (jawaban).
     */
    public function submissionData(): HasMany
    {
        return $this->hasMany(SubmissionData::class);
    }
}
