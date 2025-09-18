<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Submission;
use Illuminate\Support\Str;

class Form extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'description',
        'background_image_path',
        'is_active',
        'is_template',
        'meta_pixel_code',
        'success_redirect_url',
    ];

    /**
     * Mendefinisikan relasi ke model Category.
     * Sebuah Form 'milik' satu Category.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Sebuah Form 'memiliki banyak' FormFields.
     */
    public function formFields(): HasMany
    {
        return $this->hasMany(FormField::class);
    }

    /**
     * Sebuah Form 'memiliki banyak' Submissions. (RELASI YANG LUPA DITAMBAHKAN)
     */
    public function submissions(): HasMany // <-- TAMBAHKAN FUNGSI INI
    {                                      //
        return $this->hasMany(Submission::class); //
    }
}
