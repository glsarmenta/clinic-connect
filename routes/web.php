<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\ClinicSettingsController;
use App\Http\Controllers\DemoDataController;
use App\Http\Controllers\DoctorWorkspaceController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PatientPortalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SecretaryController;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public Clinic Homepage & Self-Service Patient Booking
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/book', [BookingController::class, 'create'])->name('booking.create');
Route::post('/book', [BookingController::class, 'store'])->name('booking.store');
Route::get('/book/confirmation/{appointment}', [BookingController::class, 'confirmation'])->name('booking.confirmation');

// Clinic Homepage & Branding Customization & Staff Assignments (Accessible to everyone during Demo)
Route::get('/doctor/clinic-settings', [ClinicSettingsController::class, 'edit'])->name('doctor.clinic-settings.edit');
Route::post('/doctor/clinic-settings', [ClinicSettingsController::class, 'update'])->name('doctor.clinic-settings.update');
Route::post('/doctor/clinic-settings/staff-assignments', [ClinicSettingsController::class, 'updateStaffAssignments'])->name('doctor.clinic-settings.staff-assignments');
Route::post('/doctor/clinic-settings/availabilities', [ClinicSettingsController::class, 'updateDoctorAvailability'])->name('doctor.clinic-settings.availabilities');

// Authenticated Routes
Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user->hasRole('Doctor') || $user->hasRole('Admin')) {
        return redirect()->route('doctor.dashboard');
    }
    if ($user->hasRole('Secretary')) {
        return redirect()->route('secretary.dashboard');
    }
    if ($user->hasRole('Patient')) {
        return redirect()->route('patient.dashboard');
    }

    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Doctor & Clinic Owner Workspace Routes
    Route::middleware('role:Doctor|Admin')->group(function () {
        Route::get('/doctor/dashboard', [DoctorWorkspaceController::class, 'index'])->name('doctor.dashboard');
        Route::patch('/doctor/live-status', [DoctorWorkspaceController::class, 'updateLiveStatus'])->name('doctor.live-status.update');
        Route::post('/doctor/availability', [DoctorWorkspaceController::class, 'updateAvailability'])->name('doctor.availability.update');
        Route::post('/doctor/call-next', [DoctorWorkspaceController::class, 'callNext'])->name('doctor.call-next');
        Route::patch('/doctor/appointments/{appointment}/complete', [DoctorWorkspaceController::class, 'completeConsultation'])->name('doctor.consultation.complete');
    });

    // Secretary Queue Dashboard Routes
    Route::middleware('role:Secretary')->group(function () {
        Route::get('/secretary/dashboard', [SecretaryController::class, 'index'])->name('secretary.dashboard');
        Route::post('/secretary/walk-in', [SecretaryController::class, 'storeWalkIn'])->name('secretary.walkin.store');
        Route::patch('/secretary/appointments/{appointment}/status', [SecretaryController::class, 'updateStatus'])->name('secretary.appointments.status');
    });

    // Patient Health & Family Portal
    Route::middleware('role:Patient')->group(function () {
        Route::get('/patient/dashboard', [PatientPortalController::class, 'index'])->name('patient.dashboard');
        Route::post('/patient/dependents', [PatientPortalController::class, 'storeDependent'])->name('patient.dependents.store');
        Route::delete('/patient/dependents/{dependent}', [PatientPortalController::class, 'destroyDependent'])->name('patient.dependents.destroy');
    });

    // Admin Demo Data Management Routes
    Route::post('/demo/reset-patients', [DemoDataController::class, 'resetPatients'])->name('demo.reset-patients');
    Route::post('/demo/generate-patients', [DemoDataController::class, 'generatePatients'])->name('demo.generate-patients');
});

// Demo Data Actions (Public for Quick Demo Bar)
Route::post('/demo/reset-patients-public', [DemoDataController::class, 'resetPatients'])->name('demo.reset-patients-public');
Route::post('/demo/generate-patients-public', [DemoDataController::class, 'generatePatients'])->name('demo.generate-patients-public');

// Demo Prototype Role Quick-Switcher
Route::get('/demo-switch/{role}', function (string $role) {
    if (in_array($role, ['doctor1', 'doctor2'])) {
        $email = $role === 'doctor1' ? 'doctor1@example.com' : 'doctor2@example.com';
        $user = User::where('email', $email)->first();
        if ($user) {
            auth()->login($user);

            return redirect()->route('doctor.dashboard');
        }
    }

    if ($role === 'secretary' || $role === 'secretary1') {
        $user = User::where('email', 'secretary@example.com')->first();
        if ($user) {
            auth()->login($user);

            return redirect()->route('secretary.dashboard');
        }
    }

    if ($role === 'secretary2') {
        $user = User::where('email', 'secretary2@example.com')->first();
        if ($user) {
            auth()->login($user);

            return redirect()->route('secretary.dashboard');
        }
    }

    if ($role === 'patient') {
        $user = User::where('email', 'patient@example.com')->first();
        if ($user) {
            auth()->login($user);

            return redirect()->route('patient.dashboard');
        }
    }

    return redirect()->route('booking.create');
})->name('demo.switch');

require __DIR__.'/auth.php';
