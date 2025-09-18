<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
     * The attributes that should be cast.
     * Ini akan mengubah 'submitted_at' menjadi objek tanggal secara otomatis.
     *
     * @var array
     */
    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    /**
     * Mendefinisikan bahwa sebuah 'Submission' dimiliki oleh sebuah 'Form'.
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /**
     * Sebuah Submission 'memiliki banyak' SubmissionData (jawaban).
     */
    public function submissionData(): HasMany
    {
        return $this->hasMany(SubmissionData::class);
    }
}
