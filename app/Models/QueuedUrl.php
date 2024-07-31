<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QueuedUrl extends Model
{
    use HasFactory;
    protected $table = 'queued_urls';

    protected $fillable = ['sitemap_id', 'url'];


    public function sitemap()
    {
        return $this->belongsTo(Sitemap::class);
    }
}
