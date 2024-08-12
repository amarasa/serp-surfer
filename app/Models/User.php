<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Log;

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
            if ($user->serviceWorker && $user->serviceWorker->used > 0) {
                $user->serviceWorker->decrement('used');
            }
        });

        // static::created(function ($user) {
        //     // Assign the next available service worker
        //     $user->assignServiceWorker();
        // });
    }

    public function assignServiceWorker()
    {
        // Find the next service worker in line
        $worker = ServiceWorker::orderBy('used', 'asc')
            ->orderBy('address', 'asc')
            ->first();

        if ($worker) {
            // Assign the service worker to the user
            $this->service_worker_id = $worker->id;
            $this->service_worker_online = false; // Assuming the service worker isn't online by default
            $this->save();

            // Increment the used count for the service worker
            $worker->increment('used');
        } else {
            Log::warning('No available service workers to assign to user ID ' . $this->id);
        }
    }
}
