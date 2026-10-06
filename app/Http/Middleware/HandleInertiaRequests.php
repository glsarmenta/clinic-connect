<?php

namespace App\Http\Middleware;

use App\Models\Clinic;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $clinic = Clinic::first();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? $request->user()->load('roles') : null,
            ],
            'clinic' => $clinic ? [
                'id' => $clinic->id,
                'name' => $clinic->name,
                'tagline' => $clinic->tagline,
                'about' => $clinic->about,
                'logo_url' => $clinic->logo_url,
                'address' => $clinic->address,
                'phone' => $clinic->phone,
                'emergency_phone' => $clinic->emergency_phone,
                'email' => $clinic->email,
                'google_map_url' => $clinic->google_map_url,
                'google_map_embed_url' => $clinic->google_map_embed_url,
                'map_embed_src' => $clinic->map_embed_src,
                'open_time' => $clinic->open_time,
                'close_time' => $clinic->close_time,
                'operating_days' => $clinic->operating_days,
            ] : null,
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
