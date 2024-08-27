<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndexingResult extends Model
{
    use HasFactory;

    // Define the table associated with the model (optional if Laravel's convention is followed)
    protected $table = 'indexing_results';

    // Specify the fields that are mass assignable
    protected $fillable = [
        'url',
        'index_date',
        'sitemap_id'
    ];

    protected $casts = [
        'index_date' => 'datetime',
    ];

    /**
     * Get the sitemap that owns the indexing result.
     */
    public function sitemap()
    {
        return $this->belongsTo(Sitemap::class);
    }
}
