<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormField extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_id',
        'label',
        'name', // ini akan kita generate otomatis
        'type',
        'is_required',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /**
     * Mendefinisikan bahwa sebuah 'FormField' memiliki banyak 'Option'.
     */
    public function options(): HasMany
    {
        return $this->hasMany(FormFieldOption::class);
    }
}
