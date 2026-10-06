<?php

namespace App\Http\Controllers;

use App\Models\Availability;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ClinicSettingsController extends Controller
{
    /**
     * Show the form for editing the clinic details and homepage branding.
     */
    public function edit(Request $request): Response
    {
        $clinic = $request->user()->clinic ?? Clinic::first() ?? Clinic::create([
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

        $secretaries = User::whereHas('roles', function ($q) {
            $q->where('name', 'Secretary');
        })
            ->where('clinic_id', $clinic->id)
            ->with('assignedDoctors:id,name,specialization')
            ->select('id', 'name', 'email', 'phone')
            ->get();

        $doctors = User::whereHas('roles', function ($q) {
            $q->where('name', 'Doctor');
        })
            ->where('clinic_id', $clinic->id)
            ->with(['assignedSecretaries:id,name', 'availabilities'])
            ->select('id', 'name', 'specialization', 'phone', 'live_status', 'email')
            ->get();

        return Inertia::render('Clinic/Settings', [
            'clinic' => $clinic,
            'secretaries' => $secretaries,
            'doctors' => $doctors,
        ]);
    }

    /**
     * Update doctor's weekly availability days and timeline slots.
     */
    public function updateDoctorAvailability(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'doctor_id' => ['required', 'exists:users,id'],
            'schedule' => ['required', 'array'],
            'schedule.*.day_of_week' => ['required', 'integer', 'min:0', 'max:6'],
            'schedule.*.is_available' => ['required', 'boolean'],
            'schedule.*.slots' => ['nullable', 'array'],
            'schedule.*.slots.*.start_time' => ['required', 'string'],
            'schedule.*.slots.*.end_time' => ['required', 'string'],
        ]);

        $doctor = User::findOrFail($validated['doctor_id']);

        // Clear existing availabilities
        Availability::where('doctor_id', $doctor->id)->delete();

        foreach ($validated['schedule'] as $daySchedule) {
            $dayOfWeek = (int) $daySchedule['day_of_week'];
            $isAvailable = (bool) $daySchedule['is_available'];
            $slots = $daySchedule['slots'] ?? [];

            if ($isAvailable && ! empty($slots)) {
                foreach ($slots as $slot) {
                    if (! empty($slot['start_time']) && ! empty($slot['end_time'])) {
                        Availability::create([
                            'doctor_id' => $doctor->id,
                            'day_of_week' => $dayOfWeek,
                            'start_time' => strlen($slot['start_time']) === 5 ? $slot['start_time'].':00' : $slot['start_time'],
                            'end_time' => strlen($slot['end_time']) === 5 ? $slot['end_time'].':00' : $slot['end_time'],
                            'is_available' => true,
                        ]);
                    }
                }
            } else {
                Availability::create([
                    'doctor_id' => $doctor->id,
                    'day_of_week' => $dayOfWeek,
                    'start_time' => '00:00:00',
                    'end_time' => '00:00:00',
                    'is_available' => false,
                ]);
            }
        }

        return redirect()->back()->with('success', "Weekly schedule and timeline for {$doctor->name} updated successfully!");
    }

    /**
     * Update doctor and secretary assignment settings.
     */
    public function updateStaffAssignments(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'assignments' => ['required', 'array'],
            'assignments.*.secretary_id' => ['required', 'exists:users,id'],
            'assignments.*.doctor_ids' => ['nullable', 'array'],
            'assignments.*.doctor_ids.*' => ['exists:users,id'],
        ]);

        foreach ($validated['assignments'] as $item) {
            $secretary = User::find($item['secretary_id']);
            if ($secretary) {
                $secretary->assignedDoctors()->sync($item['doctor_ids'] ?? []);
            }
        }

        return redirect()->back()->with('success', 'Secretary & Doctor queue assignments updated successfully!');
    }

    /**
     * Update the clinic homepage details, logo, map, and contact information.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'about' => ['nullable', 'string', 'max:4000'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:50'],
            'emergency_phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:100'],
            'google_map_url' => ['nullable', 'string', 'max:1000'],
            'google_map_embed_url' => ['nullable', 'string', 'max:1000'],
            'open_time' => ['nullable', 'string'],
            'close_time' => ['nullable', 'string'],
            'operating_days' => ['nullable', 'string', 'max:100'],
            'logo' => ['nullable', 'image', 'max:3072'], // 3MB max
            'logo_url' => ['nullable', 'string', 'max:1000'],
        ]);

        $clinic = $request->user()->clinic ?? Clinic::first() ?? new Clinic;

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('clinic_logos', 'public');
            $validated['logo_url'] = Storage::url($path);
        }

        unset($validated['logo']);

        $clinic->fill($validated);
        $clinic->save();

        if (! $request->user()->clinic_id) {
            $request->user()->update(['clinic_id' => $clinic->id]);
        }

        return redirect()->back()->with('success', 'Clinic homepage details and branding updated successfully!');
    }
}
