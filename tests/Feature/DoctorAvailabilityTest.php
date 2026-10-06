<?php

namespace Tests\Feature;

use App\Models\Availability;
use App\Models\Clinic;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    protected Clinic $clinic;

    protected User $doctor1;

    protected User $doctor2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->clinic = Clinic::create([
            'name' => 'Metro Manila Family & Pediatric Clinic',
            'address' => 'Unit 302 Medical Arts Tower, Ortigas Center, Pasig City',
            'phone' => '+63 (02) 8876-5432',
        ]);

        $this->doctor1 = User::factory()->create([
            'name' => 'Dr. Maria Cristina Santos',
            'clinic_id' => $this->clinic->id,
            'specialization' => 'Pediatrics',
            'live_status' => 'in_clinic',
        ]);
        $this->doctor1->assignRole('Doctor');

        $this->doctor2 = User::factory()->create([
            'name' => 'Dr. Jose Antonio dela Cruz',
            'clinic_id' => $this->clinic->id,
            'specialization' => 'General Practice',
            'live_status' => 'in_clinic',
        ]);
        $this->doctor2->assignRole('Doctor');
    }

    public function test_doctor_can_update_own_availability_schedule_and_timeline(): void
    {
        $schedule = [
            [
                'day_of_week' => 1, // Monday
                'is_available' => true,
                'slots' => [
                    ['start_time' => '08:00', 'end_time' => '12:00'],
                    ['start_time' => '13:00', 'end_time' => '17:00'],
                ],
            ],
            [
                'day_of_week' => 2, // Tuesday
                'is_available' => true,
                'slots' => [
                    ['start_time' => '09:00', 'end_time' => '14:00'],
                ],
            ],
            [
                'day_of_week' => 0, // Sunday
                'is_available' => false,
                'slots' => [],
            ],
        ];

        $response = $this->actingAs($this->doctor1)->post(route('doctor.availability.update'), [
            'schedule' => $schedule,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check Monday slots
        $this->assertDatabaseHas('availabilities', [
            'doctor_id' => $this->doctor1->id,
            'day_of_week' => 1,
            'start_time' => '08:00:00',
            'end_time' => '12:00:00',
            'is_available' => true,
        ]);

        $this->assertDatabaseHas('availabilities', [
            'doctor_id' => $this->doctor1->id,
            'day_of_week' => 1,
            'start_time' => '13:00:00',
            'end_time' => '17:00:00',
            'is_available' => true,
        ]);

        // Check Tuesday slot
        $this->assertDatabaseHas('availabilities', [
            'doctor_id' => $this->doctor1->id,
            'day_of_week' => 2,
            'start_time' => '09:00:00',
            'end_time' => '14:00:00',
            'is_available' => true,
        ]);

        // Check Sunday off
        $this->assertDatabaseHas('availabilities', [
            'doctor_id' => $this->doctor1->id,
            'day_of_week' => 0,
            'is_available' => false,
        ]);
    }

    public function test_can_update_doctor_availability_via_clinic_settings(): void
    {
        $schedule = [
            [
                'day_of_week' => 6, // Saturday
                'is_available' => true,
                'slots' => [
                    ['start_time' => '09:00', 'end_time' => '13:00'],
                ],
            ],
        ];

        $response = $this->actingAs($this->doctor1)->post(route('doctor.clinic-settings.availabilities'), [
            'doctor_id' => $this->doctor2->id,
            'schedule' => $schedule,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('availabilities', [
            'doctor_id' => $this->doctor2->id,
            'day_of_week' => 6,
            'start_time' => '09:00:00',
            'end_time' => '13:00:00',
            'is_available' => true,
        ]);
    }

    public function test_booking_create_loads_doctors_with_availabilities(): void
    {
        Availability::create([
            'doctor_id' => $this->doctor1->id,
            'day_of_week' => 1,
            'start_time' => '08:00:00',
            'end_time' => '12:00:00',
            'is_available' => true,
        ]);

        $response = $this->get(route('booking.create'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->has('doctors.0.availabilities'));
    }
}
