<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Dependent;
use App\Models\MedicalRecord;
use App\Models\Service;
use App\Models\User;
use App\Models\VaccineReminder;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PatientPortalController extends Controller
{
    /**
     * Display the patient health portal & family dashboard.
     */
    public function index(): Response
    {
        /** @var User $user */
        $user = auth()->user();
        $today = Carbon::today()->toDateString();
        $clinic = Clinic::first();

        $dependents = $user->dependents()->orderBy('created_at')->get();

        // 1. Check for Active Appointment TODAY (Live Queue)
        $todayAppointment = Appointment::with(['doctor:id,name,specialization,live_status', 'service:id,name,duration_minutes,price'])
            ->where('patient_id', $user->id)
            ->whereDate('appointment_date', $today)
            ->whereIn('status', ['booked', 'waiting_in_lobby', 'in_consultation'])
            ->orderBy('id')
            ->first();

        $liveQueueData = null;
        if ($todayAppointment) {
            $doctor = $todayAppointment->doctor;

            // Find current patient being served by this doctor today
            $currentServing = Appointment::where('doctor_id', $doctor->id)
                ->whereDate('appointment_date', $today)
                ->where('status', 'in_consultation')
                ->first();

            // Count patients ahead in waiting lobby
            $patientsAhead = 0;
            if ($todayAppointment->status === 'waiting_in_lobby') {
                $patientsAhead = Appointment::where('doctor_id', $doctor->id)
                    ->whereDate('appointment_date', $today)
                    ->where('status', 'waiting_in_lobby')
                    ->where('id', '<', $todayAppointment->id)
                    ->count();
                if ($currentServing) {
                    $patientsAhead += 1;
                }
            } elseif ($todayAppointment->status === 'booked') {
                $patientsAhead = Appointment::where('doctor_id', $doctor->id)
                    ->whereDate('appointment_date', $today)
                    ->whereIn('status', ['waiting_in_lobby', 'in_consultation'])
                    ->count();
            }

            $liveQueueData = [
                'appointment_id' => $todayAppointment->id,
                'queue_number' => $todayAppointment->queue_number,
                'patient_name' => $todayAppointment->patient_name,
                'patient_age' => $todayAppointment->patient_age,
                'time_slot' => $todayAppointment->time_slot,
                'service_name' => $todayAppointment->service?->name,
                'doctor_name' => $doctor->name,
                'doctor_specialization' => $doctor->specialization,
                'doctor_live_status' => $doctor->live_status ?? 'in_clinic',
                'status' => $todayAppointment->status,
                'now_serving_queue' => $currentServing?->queue_number ?? 'None currently in consultation',
                'patients_ahead' => $patientsAhead,
                'estimated_wait_minutes' => max(0, $patientsAhead * ($todayAppointment->service?->duration_minutes ?? 20)),
            ];
        }

        // 2. All Patient Appointments
        $appointments = Appointment::with(['doctor:id,name,specialization,live_status', 'service:id,name,duration_minutes,price'])
            ->where('patient_id', $user->id)
            ->orderByDesc('appointment_date')
            ->orderBy('id')
            ->get();

        // 3. Medical Records
        $medicalRecords = MedicalRecord::with(['doctor:id,name,specialization'])
            ->where('patient_id', $user->id)
            ->orderByDesc('visit_date')
            ->orderByDesc('id')
            ->get();

        // 4. Vaccine Reminders
        $vaccineReminders = VaccineReminder::where('patient_id', $user->id)
            ->orderBy('due_date')
            ->get();

        // 5. Doctors & Availabilities for Interactive Calendar
        $doctors = User::whereHas('roles', function ($q): void {
            $q->where('name', 'Doctor');
        })
            ->with(['availabilities'])
            ->select('id', 'name', 'specialization', 'live_status', 'phone')
            ->get();

        $services = Service::where('is_active', true)->get();

        return Inertia::render('Patient/Dashboard', [
            'patient' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
            'clinic' => $clinic,
            'liveQueue' => $liveQueueData,
            'dependents' => $dependents,
            'appointments' => $appointments,
            'medicalRecords' => $medicalRecords,
            'vaccineReminders' => $vaccineReminders,
            'doctors' => $doctors,
            'services' => $services,
            'todayDate' => $today,
        ]);
    }

    /**
     * Add a child or family dependent to the patient account.
     */
    public function storeDependent(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'relationship' => ['required', 'string', 'max:50'],
            'age' => ['required', 'integer', 'min:0', 'max:130'],
            'gender' => ['nullable', 'string', 'max:20'],
            'blood_type' => ['nullable', 'string', 'max:10'],
            'allergies' => ['nullable', 'string', 'max:500'],
        ]);

        /** @var User $user */
        $user = auth()->user();

        $user->dependents()->create($validated);

        return back()->with('success', "Added {$validated['name']} to your family health profile.");
    }

    /**
     * Remove a dependent from the patient account.
     */
    public function destroyDependent(Dependent $dependent): RedirectResponse
    {
        if ($dependent->user_id !== auth()->id()) {
            abort(403);
        }

        $dependent->delete();

        return back()->with('success', 'Family member profile removed.');
    }
}
