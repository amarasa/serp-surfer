<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SitemapUrl extends Model
{
    use HasFactory;

    protected $table = 'sitemap_urls';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sitemap_id',
        'page_title',
        'page_url',
        'index_status',
    ];

    /**
     * Get the sitemap that owns the URL.
     */
    public function sitemap()
    {
        return $this->belongsTo(Sitemap::class);
    }

    public function urlList()
    {
        return $this->hasOne(UrlList::class, 'url', 'page_url');
    }
}
