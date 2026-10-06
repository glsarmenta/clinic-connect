<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->foreignId('patient_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('queue_number', 20)->nullable()->index();
            $table->date('appointment_date')->index();
            $table->string('time_slot', 50)->nullable();
            $table->string('patient_name');
            $table->unsignedTinyInteger('patient_age')->nullable();
            $table->string('patient_phone', 50);
            $table->text('chief_complaint')->nullable();
            $table->enum('status', ['booked', 'waiting_in_lobby', 'in_consultation', 'completed', 'cancelled'])->default('booked')->index();
            $table->enum('source', ['online', 'walk_in', 'phone'])->default('online');
            $table->text('consultation_notes')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('consultation_started_at')->nullable();
            $table->timestamp('consultation_ended_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
