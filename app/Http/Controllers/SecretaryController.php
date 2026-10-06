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

class SecretaryController extends Controller
{
    /**
     * Display the secretary queue dashboard.
     */
    public function index(Request $request): Response
    {
        $clinic = Clinic::first();
        $today = Carbon::today()->toDateString();
        $selectedDoctorId = $request->query('doctor_id');

        if (! auth()->check()) {
            $demoSecretary = User::whereHas('roles', fn ($q) => $q->where('name', 'Secretary'))->first() ?? User::first();
            if ($demoSecretary) {
                auth()->login($demoSecretary);
            }
        }

        /** @var User|null $currentUser */
        $currentUser = (auth()->user()?->hasRole('Secretary') ? auth()->user() : null)
            ?? User::whereHas('roles', fn ($q) => $q->where('name', 'Secretary'))->first()
            ?? $request->user();

        $assignedDoctors = $currentUser ? $currentUser->assignedDoctors()
            ->select('users.id', 'users.name', 'users.specialization', 'users.live_status', 'users.phone')
            ->get() : collect();

        $doctors = User::whereHas('roles', function ($q): void {
            $q->where('name', 'Doctor');
        })
            ->where('clinic_id', $clinic->id)
            ->select('id', 'name', 'specialization', 'live_status', 'phone')
            ->get();

        // If no explicit filter is selected and the secretary is assigned to specific doctor(s)
        if (! $selectedDoctorId && $assignedDoctors->count() === 1) {
            $selectedDoctorId = (string) $assignedDoctors->first()->id;
        }

        $query = Appointment::with(['doctor:id,name,specialization,live_status', 'service:id,name,duration_minutes,price'])
            ->where('clinic_id', $clinic->id)
            ->whereDate('appointment_date', $today);

        if ($selectedDoctorId && $selectedDoctorId !== 'all') {
            $query->where('doctor_id', $selectedDoctorId);
        } elseif (! $selectedDoctorId && $assignedDoctors->isNotEmpty()) {
            $query->whereIn('doctor_id', $assignedDoctors->pluck('id'));
        }

        $appointments = $query->orderByRaw("
            CASE status
                WHEN 'in_consultation' THEN 1
                WHEN 'waiting_in_lobby' THEN 2
                WHEN 'booked' THEN 3
                WHEN 'completed' THEN 4
                WHEN 'cancelled' THEN 5
                ELSE 6
            END
        ")->orderBy('id')->get();

        $services = Service::where('clinic_id', $clinic->id)
            ->where('is_active', true)
            ->get();

        $statsBaseQuery = Appointment::where('clinic_id', $clinic->id)->whereDate('appointment_date', $today);
        if ($selectedDoctorId && $selectedDoctorId !== 'all') {
            $statsBaseQuery->where('doctor_id', $selectedDoctorId);
        } elseif (! $selectedDoctorId && $assignedDoctors->isNotEmpty()) {
            $statsBaseQuery->whereIn('doctor_id', $assignedDoctors->pluck('id'));
        }

        $stats = [
            'total' => (clone $statsBaseQuery)->count(),
            'waiting' => (clone $statsBaseQuery)->where('status', 'waiting_in_lobby')->count(),
            'in_consultation' => (clone $statsBaseQuery)->where('status', 'in_consultation')->count(),
            'completed' => (clone $statsBaseQuery)->where('status', 'completed')->count(),
        ];

        return Inertia::render('Secretary/Dashboard', [
            'clinic' => $clinic,
            'appointments' => $appointments,
            'doctors' => $doctors,
            'assignedDoctors' => $assignedDoctors,
            'services' => $services,
            'stats' => $stats,
            'selectedDoctorId' => $selectedDoctorId,
            'currentSecretary' => [
                'id' => $currentUser->id,
                'name' => $currentUser->name,
                'email' => $currentUser->email,
            ],
        ]);
    }

    /**
     * Register a quick walk-in arrival at front desk.
     */
    public function storeWalkIn(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'doctor_id' => ['required', 'exists:users,id'],
            'service_id' => ['required', 'exists:services,id'],
            'patient_name' => ['required', 'string', 'max:255'],
            'patient_age' => ['required', 'integer', 'min:0', 'max:130'],
            'patient_phone' => ['required', 'string', 'max:50'],
            'chief_complaint' => ['required', 'string', 'max:1000'],
        ]);

        $clinic = Clinic::first();
        $today = Carbon::today()->toDateString();

        $dailyCount = Appointment::where('clinic_id', $clinic->id)
            ->where('appointment_date', $today)
            ->where('doctor_id', $validated['doctor_id'])
            ->count();

        $doctorIndex = $validated['doctor_id'] * 100;
        $queueNumber = sprintf('Q-%d', $doctorIndex + $dailyCount + 1);

        Appointment::create([
            'clinic_id' => $clinic->id,
            'doctor_id' => $validated['doctor_id'],
            'service_id' => $validated['service_id'],
            'patient_id' => null,
            'queue_number' => $queueNumber,
            'appointment_date' => $today,
            'time_slot' => Carbon::now()->format('h:i A').' (Walk-in)',
            'patient_name' => $validated['patient_name'],
            'patient_age' => $validated['patient_age'],
            'patient_phone' => $validated['patient_phone'],
            'chief_complaint' => $validated['chief_complaint'],
            'status' => 'waiting_in_lobby',
            'source' => 'walk_in',
            'checked_in_at' => Carbon::now(),
        ]);

        return back()->with('success', "Walk-in patient {$validated['patient_name']} assigned {$queueNumber} and added to waiting lobby.");
    }

    /**
     * One-click progression of patient status.
     */
    public function updateStatus(Request $request, Appointment $appointment): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:booked,waiting_in_lobby,in_consultation,completed,cancelled'],
        ]);

        $updates = ['status' => $validated['status']];

        if ($validated['status'] === 'waiting_in_lobby' && ! $appointment->checked_in_at) {
            $updates['checked_in_at'] = Carbon::now();
        } elseif ($validated['status'] === 'in_consultation' && ! $appointment->consultation_started_at) {
            $updates['consultation_started_at'] = Carbon::now();
        } elseif ($validated['status'] === 'completed' && ! $appointment->consultation_ended_at) {
            $updates['consultation_ended_at'] = Carbon::now();
        }

        $appointment->update($updates);

        return back()->with('success', "Patient {$appointment->patient_name} status updated to ".str_replace('_', ' ', $validated['status']));
    }
}
