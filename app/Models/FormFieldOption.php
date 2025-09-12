<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormFieldOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_field_id',
        'label',
        'value',
    ];

    /**
     * Mendefinisikan bahwa sebuah 'Option' dimiliki oleh satu 'FormField'.
     */
    public function formField(): BelongsTo
    {
        return $this->belongsTo(FormField::class);
    }
}
