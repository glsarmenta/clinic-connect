<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    /**
     * Display the public self-service booking page.
     */
    public function create(): Response
    {
        $clinic = Clinic::first() ?? Clinic::create([
            'name' => 'Metro Manila Family & Pediatric Clinic',
            'address' => 'Unit 302 Medical Arts Tower, Ortigas Center, Pasig City, Metro Manila',
            'phone' => '+63 (02) 8876-5432',
            'email' => 'reception@metromanilaclinic.ph',
            'open_time' => '08:00:00',
            'close_time' => '17:00:00',
        ]);

        $doctors = User::whereHas('roles', function ($q): void {
            $q->where('name', 'Doctor');
        })
            ->with(['availabilities'])
            ->select('id', 'name', 'specialization', 'live_status', 'phone')
            ->get();

        $services = Service::where('clinic_id', $clinic->id)
            ->where('is_active', true)
            ->get();

        $authUser = auth()->user();
        $dependents = $authUser ? $authUser->dependents : [];

        return Inertia::render('Booking/Create', [
            'clinic' => $clinic,
            'doctors' => $doctors,
            'services' => $services,
            'authUser' => $authUser ? [
                'id' => $authUser->id,
                'name' => $authUser->name,
                'phone' => $authUser->phone,
            ] : null,
            'dependents' => $dependents,
        ]);
    }

    /**
     * Store a newly created patient self-service booking.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'doctor_id' => ['required', 'exists:users,id'],
            'service_id' => ['required', 'exists:services,id'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'time_slot' => ['required', 'string', 'max:50'],
            'patient_name' => ['required', 'string', 'max:255'],
            'patient_age' => ['required', 'integer', 'min:0', 'max:130'],
            'patient_phone' => ['required', 'string', 'max:50'],
            'chief_complaint' => ['required', 'string', 'max:1000'],
        ]);

        $clinic = Clinic::first();
        $date = Carbon::parse($validated['appointment_date'])->toDateString();

        // Generate daily sequential queue number (e.g. Q-101, Q-102)
        $dailyCount = Appointment::where('clinic_id', $clinic->id)
            ->where('appointment_date', $date)
            ->where('doctor_id', $validated['doctor_id'])
            ->count();

        $doctorIndex = $validated['doctor_id'] * 100;
        $queueNumber = sprintf('Q-%d', $doctorIndex + $dailyCount + 1);

        $appointment = Appointment::create([
            'clinic_id' => $clinic->id,
            'doctor_id' => $validated['doctor_id'],
            'service_id' => $validated['service_id'],
            'patient_id' => auth()->id(), // null if guest
            'queue_number' => $queueNumber,
            'appointment_date' => $date,
            'time_slot' => $validated['time_slot'],
            'patient_name' => $validated['patient_name'],
            'patient_age' => $validated['patient_age'],
            'patient_phone' => $validated['patient_phone'],
            'chief_complaint' => $validated['chief_complaint'],
            'status' => 'booked',
            'source' => 'online',
        ]);

        return redirect()->route('booking.confirmation', $appointment->id);
    }

    /**
     * Display the booking confirmation card with queue token.
     */
    public function confirmation(Appointment $appointment): Response
    {
        $appointment->load(['doctor:id,name,specialization,phone', 'service:id,name,duration_minutes,price', 'clinic:id,name,address,phone']);

        return Inertia::render('Booking/Confirmation', [
            'appointment' => $appointment,
        ]);
    }
}
