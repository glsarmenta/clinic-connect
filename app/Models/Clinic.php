<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Clinic extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'tagline',
        'about',
        'logo_url',
        'address',
        'phone',
        'emergency_phone',
        'email',
        'google_map_url',
        'google_map_embed_url',
        'open_time',
        'close_time',
        'operating_days',
    ];

    protected $appends = [
        'map_embed_src',
    ];

    /**
     * Get a usable Google Maps embed source URL.
     */
    public function getMapEmbedSrcAttribute(): string
    {
        if (! empty($this->google_map_embed_url)) {
            // If user pasted full iframe html, extract src
            if (preg_match('/src=["\']([^"\']+)["\']/', $this->google_map_embed_url, $matches)) {
                return $matches[1];
            }

            return $this->google_map_embed_url;
        }

        $query = $this->address ?: $this->name;

        return 'https://maps.google.com/maps?q='.urlencode($query).'&t=&z=15&ie=UTF8&iwloc=&output=embed';
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
