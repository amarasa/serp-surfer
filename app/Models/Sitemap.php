<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Sitemap extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'url',
        'is_index',
        'parent_id',
        'auto_scan',
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

    public function urlList(): HasMany
    {
        return $this->hasMany(UrlList::class, 'sitemap_id');
    }

    public function indexQueue()
    {
        return $this->hasMany(IndexQueue::class);
    }
}
