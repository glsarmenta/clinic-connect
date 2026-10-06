<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Service;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Display the rich clinic homepage.
     */
    public function index(): Response
    {
        $clinic = Clinic::first() ?? Clinic::create([
            'name' => 'Metro Manila Family & Pediatric Clinic',
            'tagline' => 'Compassionate Care — Family, Pediatric & Specialist Healthcare',
            'about' => 'Metro Manila Family & Pediatric Clinic is dedicated to providing high-quality, dependable, and accessible medical services for every Filipino family. Led by experienced specialists in pediatrics and general medicine, we offer modern facilities and a zero-friction digital queue system to eliminate prolonged waiting times.',
            'address' => 'Unit 302 Medical Arts Tower, Ortigas Center, Pasig City, Metro Manila',
            'phone' => '+63 (02) 8876-5432',
            'emergency_phone' => '+63 917 911 2273',
            'email' => 'reception@metromanilaclinic.ph',
            'google_map_url' => 'https://maps.google.com/?q=Ortigas+Center+Pasig+City',
            'google_map_embed_url' => 'https://maps.google.com/maps?q=Ortigas+Center,+Pasig+City,+Metro+Manila&t=&z=15&ie=UTF8&iwloc=&output=embed',
            'open_time' => '08:00:00',
            'close_time' => '17:00:00',
            'operating_days' => 'Monday to Saturday (Mon – Sat)',
        ]);

        $doctors = User::whereHas('roles', function ($q): void {
            $q->where('name', 'Doctor');
        })
            ->with(['availabilities'])
            ->select('id', 'name', 'email', 'specialization', 'live_status', 'phone')
            ->get();

        $services = Service::where('clinic_id', $clinic->id)
            ->where('is_active', true)
            ->get();

        $authUser = auth()->user();

        return Inertia::render('Home', [
            'clinic' => $clinic,
            'doctors' => $doctors,
            'services' => $services,
            'authUser' => $authUser ? [
                'id' => $authUser->id,
                'name' => $authUser->name,
                'email' => $authUser->email,
                'roles' => $authUser->getRoleNames(),
            ] : null,
        ]);
    }
}
