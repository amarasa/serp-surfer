<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UrlList extends Model
{
    use HasFactory;

    protected $table = 'url_list';

    protected $fillable = [
        'url',
        'status',
        'last_seen',
        'sitemap_id',
    ];

    protected $casts = [
        'last_seen' => 'datetime',
    ];

    public function urlList()
    {
        return $this->hasOne(UrlList::class, 'url', 'page_url');
    }

    public function sitemap(): BelongsTo
    {
        return $this->belongsTo(Sitemap::class, 'sitemap_id');
    }

    public function sitemapUrl()
    {
        return $this->belongsTo(SitemapUrl::class, 'url', 'page_url');
    }
}
