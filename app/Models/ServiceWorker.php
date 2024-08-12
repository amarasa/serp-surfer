<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceWorker extends Model
{
    use HasFactory;

    protected $fillable = ['address', 'json_key', 'used'];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
