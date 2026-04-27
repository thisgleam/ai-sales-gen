<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesPage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'product_name',
        'original_input',
        'generated_content',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'original_input' => 'array',
        'generated_content' => 'array',
    ];

    /**
     * Get the user that owns the sales page.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
