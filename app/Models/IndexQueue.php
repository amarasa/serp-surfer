<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndexQueue extends Model
{
    use HasFactory;

    // Table name
    protected $table = 'index_queue';

    // Mass assignable attributes
    protected $fillable = [
        'url',
        'sitemap_id',
        'requested_index_date',
        'last_scanned_date',
        'submission_count',
    ];

    /**
     * Relationship: An IndexQueue belongs to a Sitemap.
     */
    public function sitemap()
    {
        return $this->belongsTo(Sitemap::class);
    }
}
