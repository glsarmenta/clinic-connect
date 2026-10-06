<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Availability;
use App\Models\MedicalRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DoctorWorkspaceController extends Controller
{
    /**
     * Display the doctor workspace.
     */
    public function index(): Response
    {
        /** @var User $doctor */
        $doctor = auth()->user();
        $today = Carbon::today()->toDateString();

        // Active patient currently in consultation
        $activeAppointment = Appointment::with(['service'])
            ->where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', $today)
            ->where('status', 'in_consultation')
            ->first();

        // Queue waiting in lobby
        $waitingQueue = Appointment::with(['service'])
            ->where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', $today)
            ->where('status', 'waiting_in_lobby')
            ->orderBy('checked_in_at')
            ->orderBy('id')
            ->get();

        // Upcoming booked patients
        $upcomingBooked = Appointment::with(['service'])
            ->where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', $today)
            ->where('status', 'booked')
            ->orderBy('id')
            ->get();

        // Completed today
        $completedToday = Appointment::with(['service'])
            ->where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', $today)
            ->where('status', 'completed')
            ->orderByDesc('consultation_ended_at')
            ->get();

        $doctor->load('availabilities');

        return Inertia::render('Doctor/Dashboard', [
            'doctor' => [
                'id' => $doctor->id,
                'name' => $doctor->name,
                'specialization' => $doctor->specialization,
                'live_status' => $doctor->live_status ?? 'in_clinic',
                'availabilities' => $doctor->availabilities,
            ],
            'activeAppointment' => $activeAppointment,
            'waitingQueue' => $waitingQueue,
            'upcomingBooked' => $upcomingBooked,
            'completedToday' => $completedToday,
            'waitingCount' => $waitingQueue->count(),
        ]);
    }

    /**
     * Update doctor's live availability status (In Clinic, Delayed, Out of Clinic).
     */
    public function updateLiveStatus(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'live_status' => ['required', 'in:in_clinic,delayed,out_of_clinic'],
        ]);

        /** @var User $doctor */
        $doctor = auth()->user();
        $doctor->update(['live_status' => $validated['live_status']]);

        return back()->with('success', 'Your clinic status has been updated.');
    }

    /**
     * Update doctor's weekly plotted schedule and timeline availability.
     */
    public function updateAvailability(Request $request): RedirectResponse
    {
        /** @var User $doctor */
        $doctor = auth()->user();

        $validated = $request->validate([
            'schedule' => ['required', 'array'],
            'schedule.*.day_of_week' => ['required', 'integer', 'min:0', 'max:6'],
            'schedule.*.is_available' => ['required', 'boolean'],
            'schedule.*.slots' => ['nullable', 'array'],
            'schedule.*.slots.*.start_time' => ['required', 'string'],
            'schedule.*.slots.*.end_time' => ['required', 'string'],
        ]);

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

        return back()->with('success', 'Your weekly consultation schedule and availability timeline have been updated!');
    }

    /**
     * Call the next waiting patient into consultation.
     */
    public function callNext(): RedirectResponse
    {
        /** @var User $doctor */
        $doctor = auth()->user();
        $today = Carbon::today()->toDateString();

        $nextPatient = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', $today)
            ->where('status', 'waiting_in_lobby')
            ->orderBy('checked_in_at')
            ->orderBy('id')
            ->first();

        if (! $nextPatient) {
            return back()->with('error', 'No patients currently waiting in the lobby.');
        }

        $nextPatient->update([
            'status' => 'in_consultation',
            'consultation_started_at' => Carbon::now(),
        ]);

        return back()->with('success', "Called patient {$nextPatient->patient_name} ({$nextPatient->queue_number}) into consultation.");
    }

    /**
     * Complete the consultation and save notes.
     */
    public function completeConsultation(Request $request, Appointment $appointment): RedirectResponse
    {
        $validated = $request->validate([
            'consultation_notes' => ['nullable', 'string', 'max:3000'],
            'prescription' => ['nullable', 'string', 'max:2000'],
        ]);

        $appointment->update([
            'status' => 'completed',
            'consultation_notes' => $validated['consultation_notes'] ?? null,
            'consultation_ended_at' => Carbon::now(),
        ]);

        // Automatically archive consultation into MedicalRecord
        MedicalRecord::create([
            'appointment_id' => $appointment->id,
            'doctor_id' => $appointment->doctor_id,
            'patient_id' => $appointment->patient_id,
            'patient_name' => $appointment->patient_name,
            'diagnosis' => $validated['consultation_notes'] ?? 'Consultation completed.',
            'treatment' => $appointment->service?->name ?? 'General examination',
            'prescription' => $validated['prescription'] ?? null,
            'visit_date' => Carbon::today()->toDateString(),
        ]);

        return back()->with('success', "Consultation for {$appointment->patient_name} marked as completed.");
    }
}
