<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MvpWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected Clinic $clinic;

    protected User $doctor;

    protected User $secretary;

    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->clinic = Clinic::create([
            'name' => 'Metro Manila Family & Pediatric Clinic',
            'address' => 'Unit 302 Medical Arts Tower, Ortigas Center, Pasig City',
            'phone' => '+63 (02) 8876-5432',
        ]);

        $this->doctor = User::factory()->create([
            'name' => 'Dr. Maria Cristina Santos',
            'clinic_id' => $this->clinic->id,
            'specialization' => 'Pediatrics',
            'live_status' => 'in_clinic',
        ]);
        $this->doctor->assignRole('Doctor');

        $this->secretary = User::factory()->create([
            'name' => 'Maria Teresa Gonzales',
            'clinic_id' => $this->clinic->id,
        ]);
        $this->secretary->assignRole('Secretary');

        $this->service = Service::create([
            'clinic_id' => $this->clinic->id,
            'name' => 'Pediatric Immunization',
            'duration_minutes' => 30,
            'price' => 750.00,
            'is_active' => true,
        ]);
    }

    public function test_public_homepage_can_be_rendered(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Home')
            ->has('clinic')
            ->has('doctors')
            ->has('services')
        );
    }

    public function test_public_booking_page_can_be_rendered(): void
    {
        $response = $this->get(route('booking.create'));

        $response->assertStatus(200);
    }

    public function test_doctor_or_owner_can_access_clinic_settings_page(): void
    {
        $this->actingAs($this->doctor);

        $response = $this->get(route('doctor.clinic-settings.edit'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Clinic/Settings')
            ->has('clinic')
        );
    }

    public function test_anyone_or_patient_can_access_clinic_settings_during_demo(): void
    {
        // Secretary can access without 403
        $response = $this->actingAs($this->secretary)->get(route('doctor.clinic-settings.edit'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Clinic/Settings')
            ->has('clinic')
        );

        // Guest can also access (auto-logs in demo doctor)
        auth()->logout();
        $guestResponse = $this->get(route('doctor.clinic-settings.edit'));
        $guestResponse->assertStatus(200);
    }

    public function test_doctor_or_owner_can_update_clinic_homepage_details_and_branding(): void
    {
        $this->actingAs($this->doctor);

        $updatePayload = [
            'name' => 'Metro Premier Care Clinic',
            'tagline' => 'Advanced Family Medicine & Pediatric Center',
            'about' => 'Metro Premier Care is committed to compassionate outpatient excellence.',
            'address' => '100 Healthcare Boulevard, Suite 500, Quezon City',
            'phone' => '+63 (02) 8888-9999',
            'emergency_phone' => '+63 917 911 4357',
            'email' => 'director@metropremier.example',
            'google_map_url' => 'https://maps.google.com/?q=100+Healthcare+Boulevard',
            'google_map_embed_url' => 'https://maps.google.com/maps?q=100+Healthcare+Boulevard&output=embed',
            'open_time' => '07:30',
            'close_time' => '18:00',
            'operating_days' => 'Lunes hanggang Linggo (Mon – Sun)',
            'logo_url' => 'https://example.com/clinic-logo.png',
        ];

        $response = $this->post(route('doctor.clinic-settings.update'), $updatePayload);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('clinics', [
            'id' => $this->clinic->id,
            'name' => 'Metro Premier Care Clinic',
            'tagline' => 'Advanced Family Medicine & Pediatric Center',
            'phone' => '+63 (02) 8888-9999',
            'email' => 'director@metropremier.example',
            'logo_url' => 'https://example.com/clinic-logo.png',
        ]);
    }

    public function test_guest_patient_can_book_and_receive_queue_token(): void
    {
        $payload = [
            'doctor_id' => $this->doctor->id,
            'service_id' => $this->service->id,
            'appointment_date' => Carbon::today()->toDateString(),
            'time_slot' => '09:00 AM - 09:30 AM',
            'patient_name' => 'Mateo dela Cruz',
            'patient_age' => 5,
            'patient_phone' => '+63 917 555 1234',
            'chief_complaint' => 'Routine 5-year booster vaccination.',
        ];

        $response = $this->post(route('booking.store'), $payload);

        $this->assertDatabaseHas('appointments', [
            'doctor_id' => $this->doctor->id,
            'patient_name' => 'Mateo dela Cruz',
            'status' => 'booked',
            'source' => 'online',
        ]);

        $appointment = Appointment::where('patient_name', 'Mateo dela Cruz')->first();
        $this->assertNotNull($appointment->queue_number);

        $response->assertRedirect(route('booking.confirmation', $appointment->id));

        $confirmationResponse = $this->get(route('booking.confirmation', $appointment->id));
        $confirmationResponse->assertStatus(200);
    }

    public function test_secretary_can_view_queue_and_register_walk_in(): void
    {
        $this->actingAs($this->secretary);

        $response = $this->get(route('secretary.dashboard'));
        $response->assertStatus(200);

        $walkInPayload = [
            'doctor_id' => $this->doctor->id,
            'service_id' => $this->service->id,
            'patient_name' => 'Sofia Beatrice Mendoza',
            'patient_age' => 4,
            'patient_phone' => '+63 918 777 8899',
            'chief_complaint' => 'May ubo at sinat',
        ];

        $walkInResponse = $this->post(route('secretary.walkin.store'), $walkInPayload);
        $walkInResponse->assertRedirect();

        $this->assertDatabaseHas('appointments', [
            'patient_name' => 'Sofia Beatrice Mendoza',
            'status' => 'waiting_in_lobby',
            'source' => 'walk_in',
        ]);
    }

    public function test_secretary_can_transition_patient_status(): void
    {
        $this->actingAs($this->secretary);

        $appointment = Appointment::create([
            'clinic_id' => $this->clinic->id,
            'doctor_id' => $this->doctor->id,
            'service_id' => $this->service->id,
            'queue_number' => 'Q-101',
            'appointment_date' => Carbon::today()->toDateString(),
            'patient_name' => 'Rodrigo Bautista',
            'patient_age' => 8,
            'patient_phone' => '+63 908 222 3344',
            'status' => 'booked',
            'source' => 'online',
        ]);

        // Transition to waiting in lobby
        $this->patch(route('secretary.appointments.status', $appointment->id), [
            'status' => 'waiting_in_lobby',
        ]);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'waiting_in_lobby',
        ]);
    }

    public function test_doctor_can_update_live_status_and_complete_consultation(): void
    {
        $this->actingAs($this->doctor);

        // Update live status to delayed
        $this->patch(route('doctor.live-status.update'), [
            'live_status' => 'delayed',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->doctor->id,
            'live_status' => 'delayed',
        ]);

        // Create waiting patient
        $appointment = Appointment::create([
            'clinic_id' => $this->clinic->id,
            'doctor_id' => $this->doctor->id,
            'service_id' => $this->service->id,
            'queue_number' => 'Q-101',
            'appointment_date' => Carbon::today()->toDateString(),
            'patient_name' => 'Mateo dela Cruz',
            'patient_age' => 5,
            'patient_phone' => '+63 917 555 1234',
            'status' => 'waiting_in_lobby',
            'source' => 'online',
            'checked_in_at' => Carbon::now(),
        ]);

        // Doctor calls next patient
        $this->post(route('doctor.call-next'));

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'in_consultation',
        ]);

        // Doctor completes consultation
        $this->patch(route('doctor.consultation.complete', $appointment->id), [
            'consultation_notes' => 'Vaccines administered safely. No immediate adverse reaction.',
            'prescription' => 'Paracetamol 120mg syrup prn for fever',
        ]);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'completed',
            'consultation_notes' => 'Vaccines administered safely. No immediate adverse reaction.',
        ]);

        $this->assertDatabaseHas('medical_records', [
            'appointment_id' => $appointment->id,
            'doctor_id' => $this->doctor->id,
            'patient_name' => 'Mateo dela Cruz',
        ]);
    }

    public function test_patient_can_view_family_portal_and_add_child_dependent(): void
    {
        $patient = User::factory()->create([
            'name' => 'Juan dela Cruz',
            'clinic_id' => $this->clinic->id,
            'phone' => '+63 917 567 8901',
        ]);
        $patient->assignRole('Patient');

        $this->actingAs($patient);

        // Can access dashboard
        $response = $this->get(route('patient.dashboard'));
        $response->assertStatus(200);

        // Can add a child dependent
        $dependentPayload = [
            'name' => 'Althea dela Cruz',
            'relationship' => 'Anak (Daughter)',
            'age' => 3,
            'gender' => 'Female',
            'blood_type' => 'A+',
            'allergies' => 'Penicillin',
        ];

        $postResponse = $this->post(route('patient.dependents.store'), $dependentPayload);
        $postResponse->assertRedirect();

        $this->assertDatabaseHas('dependents', [
            'user_id' => $patient->id,
            'name' => 'Althea dela Cruz',
            'relationship' => 'Anak (Daughter)',
            'age' => 3,
        ]);
    }

    public function test_doctor_can_assign_secretary_to_multiple_doctors_and_secretary_dashboard_scopes_queue(): void
    {
        $clinic = Clinic::create(['name' => 'Test Medical Clinic']);

        $doctor1 = User::factory()->create(['clinic_id' => $clinic->id, 'name' => 'Dr. One']);
        $doctor1->assignRole('Doctor');

        $doctor2 = User::factory()->create(['clinic_id' => $clinic->id, 'name' => 'Dr. Two']);
        $doctor2->assignRole('Doctor');

        $secretary = User::factory()->create(['clinic_id' => $clinic->id, 'name' => 'Secretary Maria']);
        $secretary->assignRole('Secretary');

        // Doctor / Clinic Owner can assign Secretary to both doctors
        $this->actingAs($doctor1);

        $payload = [
            'assignments' => [
                [
                    'secretary_id' => $secretary->id,
                    'doctor_ids' => [$doctor1->id, $doctor2->id],
                ],
            ],
        ];

        $response = $this->post(route('doctor.clinic-settings.staff-assignments'), $payload);
        $response->assertRedirect();

        $this->assertDatabaseHas('doctor_secretary', [
            'doctor_id' => $doctor1->id,
            'secretary_id' => $secretary->id,
        ]);
        $this->assertDatabaseHas('doctor_secretary', [
            'doctor_id' => $doctor2->id,
            'secretary_id' => $secretary->id,
        ]);

        // Secretary logs in and views dashboard
        $this->actingAs($secretary);
        $secResponse = $this->get(route('secretary.dashboard'));
        $secResponse->assertStatus(200);
        $secResponse->assertInertia(fn ($page) => $page
            ->component('Secretary/Dashboard')
            ->has('assignedDoctors', 2)
        );
    }
}
