<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Availability;
use App\Models\Clinic;
use App\Models\Dependent;
use App\Models\MedicalRecord;
use App\Models\Service;
use App\Models\User;
use App\Models\VaccineReminder;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DemoDataController extends Controller
{
    /**
     * Remove all patient demo records, appointments, queue tickets, medical records, and vaccine reminders.
     */
    public function resetPatients(Request $request): RedirectResponse
    {
        Appointment::query()->delete();
        MedicalRecord::query()->delete();
        VaccineReminder::query()->delete();
        Dependent::query()->delete();

        return redirect()->back()->with('success', 'All patient test data, queue tickets, and medical history have been cleared for a clean slate demo!');
    }

    /**
     * Regenerate realistic Filipino patient test data, live queue scenarios, medical records, and child vaccine reminders.
     */
    public function generatePatients(Request $request): RedirectResponse
    {
        $clinic = Clinic::first() ?? Clinic::create([
            'name' => 'Metro Manila Family & Pediatric Clinic',
            'tagline' => 'Compassionate Care — Family, Pediatric & Specialist Healthcare',
            'about' => 'Metro Manila Family & Pediatric Clinic is dedicated to providing high-quality, dependable, and accessible medical services for every Filipino family. Led by experienced specialists in pediatrics and general medicine, we offer modern facilities and a zero-friction digital queue system to eliminate prolonged waiting times.',
            'address' => 'Unit 302 Medical Arts Tower, Ortigas Center, Pasig City, Metro Manila',
            'phone' => '+63 (02) 8876-5432',
            'emergency_phone' => '+63 917 911 2273',
            'email' => 'reception@metromanilaclinic.ph',
            'open_time' => '08:00:00',
            'close_time' => '17:00:00',
            'operating_days' => 'Monday to Saturday (Mon – Sat)',
        ]);

        // Fetch or create Doctors
        $doctor1 = User::firstOrCreate(
            ['email' => 'doctor1@example.com'],
            [
                'name' => 'Dr. Maria Cristina Santos',
                'password' => Hash::make('password'),
                'clinic_id' => $clinic->id,
                'phone' => '+63 917 234 5678',
                'specialization' => 'Pediatric Specialist (Pedia)',
                'live_status' => 'in_clinic',
            ]
        );
        $doctor1->syncRoles(['Doctor']);

        $doctor2 = User::firstOrCreate(
            ['email' => 'doctor2@example.com'],
            [
                'name' => 'Dr. Jose Antonio dela Cruz',
                'password' => Hash::make('password'),
                'clinic_id' => $clinic->id,
                'phone' => '+63 918 345 6789',
                'specialization' => 'General Practitioner & Family Physician',
                'live_status' => 'delayed',
            ]
        );
        $doctor2->syncRoles(['Doctor']);

        // Fetch or create services
        $services = [
            'General Consultation' => Service::firstOrCreate(
                ['clinic_id' => $clinic->id, 'name' => 'General Consultation'],
                [
                    'description' => 'Comprehensive primary healthcare consultation, diagnosis, and prescription.',
                    'duration_minutes' => 20,
                    'price' => 500.00,
                    'is_active' => true,
                ]
            ),
            'Pediatric Immunization & Wellness' => Service::firstOrCreate(
                ['clinic_id' => $clinic->id, 'name' => 'Pediatric Immunization & Wellness'],
                [
                    'description' => 'Routine developmental vaccines, child growth assessment, and baby checkup.',
                    'duration_minutes' => 30,
                    'price' => 750.00,
                    'is_active' => true,
                ]
            ),
            'Follow-up Checkup' => Service::firstOrCreate(
                ['clinic_id' => $clinic->id, 'name' => 'Follow-up Checkup'],
                [
                    'description' => 'Treatment review, laboratory results evaluation, and maintenance medication adjustments.',
                    'duration_minutes' => 15,
                    'price' => 350.00,
                    'is_active' => true,
                ]
            ),
            'Comprehensive Health Exam' => Service::firstOrCreate(
                ['clinic_id' => $clinic->id, 'name' => 'Comprehensive Health Exam'],
                [
                    'description' => 'Full physical screening, vital signs review, ECG, and preventative wellness care.',
                    'duration_minutes' => 45,
                    'price' => 1200.00,
                    'is_active' => true,
                ]
            ),
        ];

        // Patient User
        $patientUser = User::firstOrCreate(
            ['email' => 'patient@example.com'],
            [
                'name' => 'Juan dela Cruz',
                'password' => Hash::make('password'),
                'clinic_id' => $clinic->id,
                'phone' => '+63 917 567 8901',
            ]
        );
        $patientUser->syncRoles(['Patient']);

        // Dependents
        Dependent::firstOrCreate(
            ['user_id' => $patientUser->id, 'name' => 'Mateo dela Cruz'],
            [
                'relationship' => 'Son',
                'age' => 5,
                'gender' => 'Male',
                'blood_type' => 'O+',
                'allergies' => 'None reported',
            ]
        );

        Dependent::firstOrCreate(
            ['user_id' => $patientUser->id, 'name' => 'Althea dela Cruz'],
            [
                'relationship' => 'Daughter',
                'age' => 3,
                'gender' => 'Female',
                'blood_type' => 'Unknown',
                'allergies' => 'Penicillin',
            ]
        );

        // Child Vaccine Reminders
        VaccineReminder::firstOrCreate(
            ['patient_id' => $patientUser->id, 'vaccine_name' => 'MMR Booster (Measles, Mumps, Rubella)'],
            [
                'patient_name' => 'Mateo dela Cruz (Son)',
                'due_date' => Carbon::today()->addDays(20)->toDateString(),
                'status' => 'pending',
            ]
        );

        VaccineReminder::firstOrCreate(
            ['patient_id' => $patientUser->id, 'vaccine_name' => 'DTaP 5th Booster (Diphtheria, Tetanus, Pertussis)'],
            [
                'patient_name' => 'Mateo dela Cruz (Son)',
                'due_date' => Carbon::today()->subMonths(1)->toDateString(),
                'status' => 'completed',
            ]
        );

        VaccineReminder::firstOrCreate(
            ['patient_id' => $patientUser->id, 'vaccine_name' => 'Varicella (Chickenpox) 2nd Dose'],
            [
                'patient_name' => 'Althea dela Cruz (Daughter)',
                'due_date' => Carbon::today()->addMonths(2)->toDateString(),
                'status' => 'pending',
            ]
        );

        // Previous Medical Consultation Records
        MedicalRecord::firstOrCreate(
            ['patient_id' => $patientUser->id, 'patient_name' => 'Mateo dela Cruz'],
            [
                'doctor_id' => $doctor1->id,
                'diagnosis' => 'Acute Viral Pharyngitis & Mild Nocturnal Cough. Lungs clear bilaterally.',
                'treatment' => 'Pediatric oral rehydration, cool mist humidifier, warm honey-lemon water.',
                'prescription' => 'Amoxicillin 250mg/5ml suspension, 5ml tid x 7 days; Paracetamol syrup 250mg/5ml prn for fever.',
                'visit_date' => Carbon::today()->subMonths(1)->toDateString(),
            ]
        );

        MedicalRecord::firstOrCreate(
            ['patient_id' => $patientUser->id, 'patient_name' => 'Juan dela Cruz'],
            [
                'doctor_id' => $doctor2->id,
                'diagnosis' => 'Mild Contact Dermatitis on forearm. Normal blood pressure (120/80 mmHg).',
                'treatment' => 'Cool compresses and fragrance-free hypoallergenic moisturizer.',
                'prescription' => 'Hydrocortisone 1% topical cream, apply 2x daily x 5 days.',
                'visit_date' => Carbon::today()->subMonths(2)->toDateString(),
            ]
        );

        // Weekly Doctor Availabilities (Mon - Fri 8-12 & 1-5, Sat 9-1)
        foreach ([$doctor1, $doctor2] as $doc) {
            if ($doc->availabilities()->count() === 0) {
                for ($day = 1; $day <= 5; $day++) {
                    Availability::create([
                        'doctor_id' => $doc->id,
                        'day_of_week' => $day,
                        'start_time' => '08:00:00',
                        'end_time' => '12:00:00',
                        'is_available' => true,
                    ]);
                    Availability::create([
                        'doctor_id' => $doc->id,
                        'day_of_week' => $day,
                        'start_time' => '13:00:00',
                        'end_time' => '17:00:00',
                        'is_available' => true,
                    ]);
                }
                Availability::create([
                    'doctor_id' => $doc->id,
                    'day_of_week' => 6,
                    'start_time' => '09:00:00',
                    'end_time' => '13:00:00',
                    'is_available' => true,
                ]);
            }
        }

        // Demo Appointments for Today
        $today = Carbon::today()->toDateString();

        $demoAppointments = [
            [
                'clinic_id' => $clinic->id,
                'doctor_id' => $doctor1->id,
                'service_id' => $services['Pediatric Immunization & Wellness']->id,
                'patient_id' => null,
                'queue_number' => 'Q-101',
                'appointment_date' => $today,
                'time_slot' => '09:00 AM - 09:30 AM',
                'patient_name' => 'Mateo dela Cruz',
                'patient_age' => 5,
                'patient_phone' => '+63 917 555 1234',
                'chief_complaint' => 'Routine 5-year booster vaccination (MMR and DTaP) and pediatric wellness checkup.',
                'status' => 'in_consultation',
                'source' => 'online',
                'checked_in_at' => Carbon::now()->subMinutes(25),
                'consultation_started_at' => Carbon::now()->subMinutes(10),
            ],
            [
                'clinic_id' => $clinic->id,
                'doctor_id' => $doctor1->id,
                'service_id' => $services['General Consultation']->id,
                'patient_id' => null,
                'queue_number' => 'Q-102',
                'appointment_date' => $today,
                'time_slot' => '09:30 AM - 10:00 AM',
                'patient_name' => 'Sofia Beatrice Mendoza',
                'patient_age' => 3,
                'patient_phone' => '+63 918 777 8899',
                'chief_complaint' => 'Mild dry cough and low-grade fever since Thursday, pediatric evaluation.',
                'status' => 'waiting_in_lobby',
                'source' => 'walk_in',
                'checked_in_at' => Carbon::now()->subMinutes(15),
            ],
            [
                'clinic_id' => $clinic->id,
                'doctor_id' => $doctor1->id,
                'service_id' => $services['Follow-up Checkup']->id,
                'patient_id' => $patientUser->id,
                'queue_number' => 'Q-103',
                'appointment_date' => $today,
                'time_slot' => '10:00 AM - 10:30 AM',
                'patient_name' => 'Juan dela Cruz',
                'patient_age' => 34,
                'patient_phone' => '+63 917 567 8901',
                'chief_complaint' => 'Follow-up checkup for skin allergy and refill of maintenance prescription.',
                'status' => 'booked',
                'source' => 'online',
            ],
            [
                'clinic_id' => $clinic->id,
                'doctor_id' => $doctor2->id,
                'service_id' => $services['Comprehensive Health Exam']->id,
                'patient_id' => null,
                'queue_number' => 'Q-201',
                'appointment_date' => $today,
                'time_slot' => '08:30 AM - 09:15 AM',
                'patient_name' => 'Corazon Aquino-Reyes',
                'patient_age' => 52,
                'patient_phone' => '+63 922 888 9900',
                'chief_complaint' => 'Annual executive health checkup and blood pressure monitoring.',
                'status' => 'completed',
                'source' => 'online',
                'consultation_notes' => 'Blood pressure: 120/80 mmHg. Normal heart sounds. Prescribed routine lab tests (CBC, Lipid profile). Advised 30-minute daily walking.',
                'checked_in_at' => Carbon::now()->subHours(2),
                'consultation_started_at' => Carbon::now()->subHours(1)->subMinutes(45),
                'consultation_ended_at' => Carbon::now()->subHours(1),
            ],
            [
                'clinic_id' => $clinic->id,
                'doctor_id' => $doctor2->id,
                'service_id' => $services['General Consultation']->id,
                'patient_id' => null,
                'queue_number' => 'Q-202',
                'appointment_date' => $today,
                'time_slot' => '09:30 AM - 10:00 AM',
                'patient_name' => 'Rodrigo Bautista',
                'patient_age' => 29,
                'patient_phone' => '+63 908 222 3344',
                'chief_complaint' => 'Severe throbbing migraine with mild photophobia.',
                'status' => 'waiting_in_lobby',
                'source' => 'walk_in',
                'checked_in_at' => Carbon::now()->subMinutes(12),
            ],
        ];

        foreach ($demoAppointments as $appt) {
            Appointment::updateOrCreate(
                [
                    'clinic_id' => $appt['clinic_id'],
                    'doctor_id' => $appt['doctor_id'],
                    'queue_number' => $appt['queue_number'],
                    'appointment_date' => $appt['appointment_date'],
                ],
                $appt
            );
        }

        return redirect()->back()->with('success', 'Sample patient demo records, live queue numbers, and medical histories have been successfully generated!');
    }
}
