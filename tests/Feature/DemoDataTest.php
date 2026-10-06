<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Dependent;
use App\Models\MedicalRecord;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoDataTest extends TestCase
{
    use RefreshDatabase;

    protected Clinic $clinic;

    protected User $doctor;

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
    }

    public function test_can_generate_and_reset_patient_demo_data(): void
    {
        // 1. Generate patient demo data via authenticated route
        $response = $this->actingAs($this->doctor)->post(route('demo.generate-patients'));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertGreaterThan(0, Appointment::count());
        $this->assertGreaterThan(0, Dependent::count());
        $this->assertGreaterThan(0, MedicalRecord::count());

        // Verify patient Filipino naming
        $juan = User::where('email', 'patient@example.com')->first();
        $this->assertNotNull($juan);
        $this->assertEquals('Juan dela Cruz', $juan->name);

        // 2. Reset patient demo data
        $resetResponse = $this->actingAs($this->doctor)->post(route('demo.reset-patients'));
        $resetResponse->assertRedirect();
        $resetResponse->assertSessionHas('success');

        $this->assertEquals(0, Appointment::count());
        $this->assertEquals(0, Dependent::count());
        $this->assertEquals(0, MedicalRecord::count());

        // Doctor and Clinic are preserved
        $this->assertDatabaseHas('clinics', ['id' => $this->clinic->id]);
        $this->assertDatabaseHas('users', ['id' => $this->doctor->id]);
    }

    public function test_can_trigger_demo_data_actions_via_public_route(): void
    {
        $response = $this->post(route('demo.generate-patients-public'));
        $response->assertRedirect();
        $this->assertGreaterThan(0, Appointment::count());

        $resetResponse = $this->post(route('demo.reset-patients-public'));
        $resetResponse->assertRedirect();
        $this->assertEquals(0, Appointment::count());
    }
}
