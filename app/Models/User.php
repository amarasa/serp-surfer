<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_token',
        'google_refresh_token',
        'suspended'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'google_token',
        'google_refresh_token',
    ];

    public function sitemaps(): BelongsToMany
    {
        return $this->belongsToMany(Sitemap::class)->withTimestamps();
    }

    public function serviceWorker()
    {
        return $this->belongsTo(ServiceWorker::class);
    }

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole($role)
    {
        if (is_string($role)) {
            return $this->roles->contains('name', $role);
        }

        return !!$role->intersect($this->roles)->count();
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($user) {
            // Detach all roles
            $user->roles()->detach();

            // Detach all sitemaps
            $user->sitemaps()->detach();

            // Delete related queued URLs and sitemap URLs for each sitemap
            foreach ($user->sitemaps as $sitemap) {
                $sitemap->queuedUrls()->delete();
                $sitemap->sitemapUrls()->delete();
            }

            // Decrement the used count for the associated service worker, if any
            if ($user->serviceWorker) {
                $user->serviceWorker->decrement('used');
            }
        });
    }
}
