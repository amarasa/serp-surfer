<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class Sitemap extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'url',
        'is_index',
        'parent_id',
    ];

    public function queuedUrls()
    {
        return $this->hasMany(QueuedUrl::class);
    }

    public function sitemapUrls()
    {
        return $this->hasMany(SitemapUrl::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}
