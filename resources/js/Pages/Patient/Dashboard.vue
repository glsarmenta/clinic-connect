<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    patient: Object,
    clinic: Object,
    liveQueue: Object,
    dependents: Array,
    appointments: Array,
    medicalRecords: Array,
    vaccineReminders: Array,
    doctors: Array,
    services: Array,
    todayDate: String,
});

// Modal state
const showAddDependentModal = ref(false);
const showCalendarBookingModal = ref(false);

// Dependent Form
const dependentForm = useForm({
    name: '',
    relationship: 'Child',
    age: '',
    gender: 'Male',
    blood_type: 'Unknown',
    allergies: '',
});

const submitDependent = () => {
    dependentForm.post(route('patient.dependents.store'), {
        preserveScroll: true,
        onSuccess: () => {
            dependentForm.reset();
            showAddDependentModal.value = false;
        },
    });
};

// --- Doctor Calendar State ---
const selectedDoctorId = ref(props.doctors.length > 0 ? props.doctors[0].id : null);

const selectedDoctor = computed(() => {
    return props.doctors.find(d => d.id === Number(selectedDoctorId.value)) || props.doctors[0];
});

// Calendar date selection
const selectedCalendarDate = ref(props.todayDate);

// Check if doctor is available on a given day of week
const isDoctorAvailableOnDay = (dayOfWeek) => {
    if (!selectedDoctor.value || !selectedDoctor.value.availabilities) return false;
    return selectedDoctor.value.availabilities.some(a => a.day_of_week === dayOfWeek && a.is_available);
};

// Generate next 14 days for the interactive calendar strip
const calendarDays = computed(() => {
    const days = [];
    const base = new Date();
    
    for (let i = 0; i < 14; i++) {
        const d = new Date(base);
        d.setDate(base.getDate() + i);
        const iso = d.toISOString().split('T')[0];
        const dayOfWeek = d.getDay(); // 0 is Sun, 6 is Sat
        const dayName = d.toLocaleDateString('en-US', { weekday: 'short' });
        const monthName = d.toLocaleDateString('en-US', { month: 'short' });
        const dayNumber = d.getDate();
        const available = isDoctorAvailableOnDay(dayOfWeek);

        days.push({
            date: iso,
            dayOfWeek,
            dayName,
            monthName,
            dayNumber,
            isAvailable: available,
            isToday: i === 0,
        });
    }
    return days;
});

// Direct booking from calendar modal
const calendarBookingForm = useForm({
    doctor_id: '',
    service_id: props.services.length > 0 ? props.services[0].id : '',
    appointment_date: props.todayDate,
    time_slot: '09:00 AM - 09:30 AM',
    patient_name: props.patient.name,
    patient_age: '',
    patient_phone: props.patient.phone || '',
    chief_complaint: '',
});

const selectedPatientProfile = ref('self');

const onProfileSelect = (val) => {
    selectedPatientProfile.value = val;
    if (val === 'self') {
        calendarBookingForm.patient_name = props.patient.name;
        calendarBookingForm.patient_age = '';
    } else {
        const dep = props.dependents.find(d => d.id === Number(val));
        if (dep) {
            calendarBookingForm.patient_name = dep.name;
            calendarBookingForm.patient_age = dep.age;
        }
    }
};

const openBookingForDate = (day) => {
    if (!day.isAvailable) return;
    selectedCalendarDate.value = day.date;
    calendarBookingForm.doctor_id = selectedDoctor.value.id;
    calendarBookingForm.appointment_date = day.date;
    showCalendarBookingModal.value = true;
};

const submitCalendarBooking = () => {
    calendarBookingForm.post(route('booking.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showCalendarBookingModal.value = false;
        },
    });
};

const getStatusBadge = (status) => {
    switch (status) {
        case 'waiting_in_lobby': return 'bg-amber-100 text-amber-800 border-amber-300';
        case 'in_consultation': return 'bg-emerald-100 text-emerald-800 border-emerald-300 animate-pulse';
        case 'completed': return 'bg-slate-100 text-slate-700 border-slate-300';
        case 'cancelled': return 'bg-rose-100 text-rose-800 border-rose-300';
        case 'booked':
        default: return 'bg-sky-100 text-sky-800 border-sky-300';
    }
};

const formatStatusText = (status) => {
    switch (status) {
        case 'waiting_in_lobby': return 'Waiting in Lobby';
        case 'in_consultation': return 'In Consultation';
        case 'completed': return 'Completed';
        case 'cancelled': return 'Cancelled';
        case 'booked': return 'Confirmed & Booked';
        default: return status;
    }
};
</script>

<template>
    <Head title="My Family Health Portal - Clinic Connect" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-extrabold leading-tight text-slate-900 flex items-center gap-2">
                        <span>Family Health & Records Portal</span>
                        <span class="text-xs px-3 py-1 rounded-full bg-brand-100 text-brand-800 font-semibold border border-brand-200">
                            {{ patient.name }}
                        </span>
                    </h2>
                    <p class="text-xs text-brand-teal font-medium mt-0.5">Manage medical records, child vaccines, and appointments for your whole household</p>
                </div>

                <div class="flex items-center gap-2.5">
                    <a
                        href="#doctor-calendar"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-teal hover:bg-brand-600 active:bg-brand-700 text-white text-xs font-bold rounded-full shadow-md shadow-brand-teal/20 transition-all"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        View Doctor Calendar & Book
                    </a>

                    <button
                        @click="showAddDependentModal = true"
                        type="button"
                        class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-full shadow-xs transition-colors cursor-pointer"
                    >
                        + Add Child / Dependent
                    </button>
                </div>
            </div>
        </template>

        <div class="py-6 sm:py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            <!-- 1. LIVE QUEUE TRACKER (ONLY RENDERED IF PATIENT HAS APPOINTMENT TODAY) -->
            <div v-if="liveQueue" class="bg-gradient-to-r from-teal-800 via-brand-teal to-cyan-900 rounded-[32px] sm:rounded-[36px] p-6 sm:p-8 text-white shadow-xl shadow-cyan-950/15 relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 w-48 h-48 rounded-full bg-white/10 pointer-events-none"></div>

                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-bold bg-emerald-400 text-emerald-950 shadow-xs">
                                <span class="w-2 h-2 rounded-full bg-emerald-900 animate-ping"></span>
                                Live Queue Active Today
                            </span>
                            <span class="text-xs text-cyan-200 font-medium">
                                {{ liveQueue.time_slot }}
                            </span>
                        </div>

                        <h3 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                            {{ liveQueue.patient_name }}'s Visit
                        </h3>

                        <p class="text-sm text-teal-100 flex items-center gap-2">
                            <span>With <strong>{{ liveQueue.doctor_name }}</strong> ({{ liveQueue.doctor_specialization }})</span>
                            <span class="w-1.5 h-1.5 rounded-full bg-teal-300"></span>
                            <span>{{ liveQueue.service_name }}</span>
                        </p>
                    </div>

                    <!-- Queue Numbers Comparison -->
                    <div class="flex items-center gap-4 bg-white/10 backdrop-blur-md p-4 rounded-2xl border border-white/20">
                        <div class="text-center px-3 border-r border-white/20">
                            <div class="text-[11px] uppercase tracking-wider text-teal-200 font-bold">Your Queue #</div>
                            <div class="text-3xl sm:text-4xl font-mono font-black text-amber-300 mt-1">
                                {{ liveQueue.queue_number }}
                            </div>
                        </div>

                        <div class="text-center px-3">
                            <div class="text-[11px] uppercase tracking-wider text-teal-200 font-bold">Now Serving</div>
                            <div class="text-2xl sm:text-3xl font-mono font-extrabold text-white mt-1">
                                {{ liveQueue.now_serving_queue }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Progress & Wait Estimation Bar -->
                <div class="mt-6 pt-6 border-t border-white/15 grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <div class="bg-black/20 p-3 rounded-xl">
                        <span class="text-teal-200 block text-[11px] uppercase font-semibold">Doctor Status</span>
                        <div class="font-bold text-white capitalize mt-0.5 flex items-center gap-2">
                            <span
                                :class="{
                                    'bg-emerald-400': liveQueue.doctor_live_status === 'in_clinic',
                                    'bg-amber-400': liveQueue.doctor_live_status === 'delayed',
                                    'bg-rose-400': liveQueue.doctor_live_status === 'out_of_clinic'
                                }"
                                class="w-2.5 h-2.5 rounded-full"
                            ></span>
                            {{ liveQueue.doctor_live_status.replace('_', ' ') }}
                        </div>
                    </div>

                    <div class="bg-black/20 p-3 rounded-xl">
                        <span class="text-teal-200 block text-[11px] uppercase font-semibold">Current Stage</span>
                        <div class="font-bold text-white capitalize mt-0.5">
                            {{ liveQueue.status.replace('_', ' ') }}
                        </div>
                    </div>

                    <div class="bg-black/20 p-3 rounded-xl">
                        <span class="text-teal-200 block text-[11px] uppercase font-semibold">Queue Position</span>
                        <div class="font-bold text-amber-300 mt-0.5">
                            <template v-if="liveQueue.status === 'in_consultation'">
                                🩺 Currently inside consultation room!
                            </template>
                            <template v-else-if="liveQueue.patients_ahead > 0">
                                {{ liveQueue.patients_ahead }} patient(s) ahead (~{{ liveQueue.estimated_wait_minutes }}m wait)
                            </template>
                            <template v-else>
                                You are next in line!
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notice when NO active appointment today -->
            <div v-else class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center font-bold">
                        🕒
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-800">No Clinic Appointments Scheduled for Today</h4>
                        <p class="text-xs text-slate-500">Live queue activates on the date of your confirmed visit. Select a physician below to view their calendar and reserve a slot.</p>
                    </div>
                </div>
                <a href="#doctor-calendar" class="text-xs font-bold text-teal-600 hover:text-teal-800 hidden sm:inline-block">
                    Browse Calendar &rarr;
                </a>
            </div>

            <!-- 2. DOCTOR AVAILABILITY & INTERACTIVE CLINIC CALENDAR -->
            <div id="doctor-calendar" class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 sm:p-8 space-y-6 scroll-mt-20 overflow-hidden">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 flex flex-wrap items-center gap-2">
                            <span>Doctor Availability Calendar</span>
                            <span class="text-xs font-normal text-slate-400">Choose a physician to view their live schedule</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Click any available date to reserve a slot directly for you or your child</p>
                    </div>

                    <!-- Doctor Picker -->
                    <div class="flex flex-col sm:flex-row sm:items-center gap-1.5 sm:gap-2 w-full sm:w-auto min-w-0">
                        <label class="text-xs font-bold text-slate-600 uppercase tracking-wide shrink-0">Physician:</label>
                        <select
                            v-model="selectedDoctorId"
                            class="w-full sm:w-auto min-w-0 max-w-full rounded-xl border-slate-300 text-xs font-semibold py-2 pl-3 pr-8 focus:ring-teal-500 focus:border-teal-500 shadow-xs truncate bg-white text-slate-800"
                        >
                            <option v-for="d in doctors" :key="d.id" :value="d.id">
                                {{ d.name }} ({{ d.specialization }})
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Doctor Profile Banner -->
                <div v-if="selectedDoctor" class="bg-slate-50 border border-slate-200/80 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <img
                            :src="selectedDoctor.name?.includes('Cristina') ? '/images/doctor_cristina.jpg' : '/images/doctor_jose.jpg'"
                            :alt="selectedDoctor.name"
                            class="w-12 h-12 rounded-xl object-cover border border-teal-200 shadow-xs"
                        />
                        <div>
                            <div class="font-bold text-slate-900 text-sm">{{ selectedDoctor.name }}</div>
                            <div class="text-xs text-teal-700 font-medium">{{ selectedDoctor.specialization }}</div>
                            <div class="text-[11px] text-slate-400">Clinic Hours: Mon-Fri (8:00 AM - 5:00 PM), Sat (9:00 AM - 1:00 PM)</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-slate-500">Live Status:</span>
                        <span
                            :class="{
                                'bg-emerald-100 text-emerald-800 border-emerald-300': selectedDoctor.live_status === 'in_clinic',
                                'bg-amber-100 text-amber-800 border-amber-300': selectedDoctor.live_status === 'delayed',
                                'bg-rose-100 text-rose-800 border-rose-300': selectedDoctor.live_status === 'out_of_clinic'
                            }"
                            class="px-2.5 py-1 rounded-full font-bold border capitalize"
                        >
                            {{ selectedDoctor.live_status?.replace('_', ' ') || 'In Clinic' }}
                        </span>
                    </div>
                </div>

                <!-- Interactive 14-Day Calendar Strip -->
                <div>
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">
                        Upcoming 14-Day Schedule (Click an available date to book):
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-2.5">
                        <button
                            v-for="day in calendarDays"
                            :key="day.date"
                            type="button"
                            @click="openBookingForDate(day)"
                            :disabled="!day.isAvailable"
                            :class="{
                                'bg-teal-50 border-teal-400 text-teal-950 hover:bg-teal-100 hover:shadow-md cursor-pointer': day.isAvailable && selectedCalendarDate !== day.date,
                                'bg-teal-600 text-white border-teal-600 ring-2 ring-teal-500/30 shadow-md font-bold cursor-pointer': day.isAvailable && selectedCalendarDate === day.date,
                                'bg-slate-100 border-slate-200 text-slate-400 opacity-60 cursor-not-allowed': !day.isAvailable
                            }"
                            class="p-3 rounded-2xl border text-center transition-all flex flex-col items-center justify-between min-h-[95px] relative"
                        >
                            <span v-if="day.isToday" class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span class="text-[11px] font-semibold uppercase tracking-wider block opacity-80">{{ day.dayName }}</span>
                            <span class="text-xl font-black block my-0.5">{{ day.dayNumber }}</span>
                            <span class="text-[10px] font-medium block">
                                {{ day.isAvailable ? 'Available' : 'Closed' }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- 3. Family Members & Children Profiles -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <span>Household & Dependents</span>
                        <span class="text-xs text-slate-400 font-normal">({{ dependents.length + 1 }} profiles)</span>
                    </h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Account Holder (Self) Card -->
                    <div class="bg-white rounded-2xl border-2 border-teal-500/40 p-5 shadow-xs relative overflow-hidden">
                        <div class="flex items-center justify-between mb-3">
                            <span class="w-9 h-9 rounded-xl bg-teal-100 text-teal-700 font-bold text-sm flex items-center justify-center">
                                👤
                            </span>
                            <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-teal-50 text-teal-800 border border-teal-200">
                                Primary (Self)
                            </span>
                        </div>
                        <h4 class="font-bold text-slate-900 text-sm">{{ patient.name }}</h4>
                        <div class="text-xs text-slate-500 mt-1 space-y-0.5">
                            <div>{{ patient.email }}</div>
                            <div>{{ patient.phone || 'Phone not set' }}</div>
                        </div>
                    </div>

                    <!-- Dependent / Kids Cards -->
                    <div
                        v-for="dep in dependents"
                        :key="dep.id"
                        class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs hover:border-slate-300 transition-all relative overflow-hidden"
                    >
                        <div class="flex items-center justify-between mb-3">
                            <span class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 font-bold text-sm flex items-center justify-center">
                                🧒
                            </span>
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-800 border border-indigo-200">
                                {{ dep.relationship }} (Age {{ dep.age }})
                            </span>
                        </div>
                        <h4 class="font-bold text-slate-900 text-sm">{{ dep.name }}</h4>
                        <div class="text-xs text-slate-500 mt-2 space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">Blood Type:</span>
                                <span :class="dep.blood_type && dep.blood_type !== 'Unknown' ? 'font-semibold text-slate-700' : 'text-slate-400 italic text-[11px]'">
                                    {{ dep.blood_type && dep.blood_type !== 'Unknown' ? dep.blood_type : 'Unknown / Not sure' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">Allergies:</span>
                                <span class="font-semibold text-rose-600 truncate max-w-[120px]">{{ dep.allergies || 'None reported' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Add Dependent Card Shortcut -->
                    <button
                        @click="showAddDependentModal = true"
                        type="button"
                        class="border-2 border-dashed border-slate-200 hover:border-teal-500/60 rounded-2xl p-5 flex flex-col items-center justify-center text-center text-slate-500 hover:text-teal-700 hover:bg-teal-50/20 transition-all cursor-pointer min-h-[140px]"
                    >
                        <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center font-bold text-slate-600 mb-2">
                            +
                        </div>
                        <span class="text-xs font-bold">Register Child / Dependent</span>
                        <span class="text-[11px] text-slate-400 mt-0.5">Track their vaccinations & medical notes</span>
                    </button>
                </div>
            </div>

            <!-- 4. Medical Consultation Records & Prescriptions -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 sm:p-8 space-y-5">
                <div class="border-b border-slate-100 pb-4">
                    <h3 class="text-base font-bold text-slate-900">Medical History & Clinical Prescriptions</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Official clinical diagnoses and prescriptions recorded by your physicians</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs sm:text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50/75 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                <th class="py-3 px-4">Visit Date</th>
                                <th class="py-3 px-4">Patient / Child</th>
                                <th class="py-3 px-4">Consulting Physician</th>
                                <th class="py-3 px-4">Diagnosis / Notes</th>
                                <th class="py-3 px-4">Prescription & Advice</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="rec in medicalRecords" :key="rec.id" class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-4 whitespace-nowrap font-medium text-slate-900">{{ rec.visit_date }}</td>
                                <td class="py-3.5 px-4 font-bold text-slate-900">{{ rec.patient_name || patient.name }}</td>
                                <td class="py-3.5 px-4">
                                    <div class="font-medium text-slate-800">{{ rec.doctor?.name }}</div>
                                    <div class="text-[11px] text-teal-600">{{ rec.doctor?.specialization }}</div>
                                </td>
                                <td class="py-3.5 px-4 max-w-xs text-xs text-slate-700">
                                    {{ rec.diagnosis }}
                                </td>
                                <td class="py-3.5 px-4 text-xs font-mono text-emerald-800 bg-emerald-50/40 rounded-lg">
                                    {{ rec.prescription || 'No medication prescribed' }}
                                </td>
                            </tr>

                            <tr v-if="medicalRecords.length === 0">
                                <td colspan="5" class="py-8 text-center text-slate-400 text-xs">
                                    No completed medical records on file yet. Once a doctor completes a consultation, records appear here automatically.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 5. Child Immunization & Vaccine Schedule -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 sm:p-8 space-y-5">
                <div class="border-b border-slate-100 pb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Pediatric Immunization & Vaccine Tracker</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Automated schedules and developmental vaccination milestones for your kids</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div
                        v-for="vac in vaccineReminders"
                        :key="vac.id"
                        class="p-4 rounded-2xl border border-slate-200 bg-slate-50 flex items-center justify-between text-xs"
                    >
                        <div class="space-y-1">
                            <span class="font-bold text-slate-900 block text-sm">{{ vac.vaccine_name }}</span>
                            <span class="text-indigo-600 font-semibold block">{{ vac.patient_name || 'Dependent' }}</span>
                            <span class="text-slate-400 text-[11px] block">Due: {{ vac.due_date }}</span>
                        </div>

                        <span
                            :class="vac.status === 'completed' ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-amber-100 text-amber-800 border-amber-300'"
                            class="px-2.5 py-1 rounded-full text-[11px] font-bold border capitalize"
                        >
                            {{ vac.status }}
                        </span>
                    </div>

                    <div v-if="vaccineReminders.length === 0" class="col-span-full py-8 text-center text-slate-400 text-xs">
                        No pending vaccine reminders on file.
                    </div>
                </div>
            </div>

        </div>

        <!-- Direct Calendar Booking Modal -->
        <div
            v-if="showCalendarBookingModal"
            class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
        >
            <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-slate-200 overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                <div class="p-6 bg-teal-600 text-white flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold">Book with {{ selectedDoctor?.name }}</h3>
                        <p class="text-xs text-teal-100">Selected Date: {{ calendarBookingForm.appointment_date }}</p>
                    </div>
                    <button @click="showCalendarBookingModal = false" class="text-white/80 hover:text-white text-xl font-bold cursor-pointer">✕</button>
                </div>

                <form @submit.prevent="submitCalendarBooking" class="p-6 space-y-4 text-xs">
                    <!-- Who is this for? -->
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Who is this visit for?</label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                type="button"
                                @click="onProfileSelect('self')"
                                :class="selectedPatientProfile === 'self' ? 'bg-teal-600 text-white font-bold' : 'bg-slate-100 text-slate-700'"
                                class="px-3 py-1.5 rounded-lg transition-colors cursor-pointer"
                            >
                                👤 Myself ({{ patient.name }})
                            </button>
                            <button
                                v-for="d in dependents"
                                :key="d.id"
                                type="button"
                                @click="onProfileSelect(d.id)"
                                :class="selectedPatientProfile === d.id ? 'bg-teal-600 text-white font-bold' : 'bg-slate-100 text-slate-700'"
                                class="px-3 py-1.5 rounded-lg transition-colors cursor-pointer"
                            >
                                👶 {{ d.name }} ({{ d.relationship }}, Age {{ d.age }})
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Service Type</label>
                            <select v-model="calendarBookingForm.service_id" class="w-full rounded-xl border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 text-xs py-2" required>
                                <option v-for="s in services" :key="s.id" :value="s.id">
                                    {{ s.name }} (₱{{ s.price }})
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Preferred Time Window</label>
                            <select v-model="calendarBookingForm.time_slot" class="w-full rounded-xl border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 text-xs py-2" required>
                                <option value="09:00 AM - 09:30 AM">Morning (09:00 AM - 09:30 AM)</option>
                                <option value="10:00 AM - 10:30 AM">Morning (10:00 AM - 10:30 AM)</option>
                                <option value="01:30 PM - 02:00 PM">Afternoon (01:30 PM - 02:00 PM)</option>
                                <option value="02:30 PM - 03:00 PM">Afternoon (02:30 PM - 03:00 PM)</option>
                                <option value="03:30 PM - 04:00 PM">Afternoon (03:30 PM - 04:00 PM)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div class="col-span-2">
                            <label class="block font-semibold text-slate-600 mb-1">Patient Name</label>
                            <input v-model="calendarBookingForm.patient_name" type="text" class="w-full rounded-xl border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 text-xs py-2" required />
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Age</label>
                            <input v-model="calendarBookingForm.patient_age" type="number" min="0" max="130" class="w-full rounded-xl border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 text-xs py-2" required />
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-600 mb-1">Mobile Phone Number</label>
                        <input v-model="calendarBookingForm.patient_phone" type="tel" class="w-full rounded-xl border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 text-xs py-2" required />
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-600 mb-1">Chief Complaint / Reason for Visit</label>
                        <textarea v-model="calendarBookingForm.chief_complaint" rows="2" placeholder="Describe symptoms or checkup purpose..." class="w-full rounded-xl border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 text-xs py-2" required></textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button
                            type="button"
                            @click="showCalendarBookingModal = false"
                            class="px-4 py-2 rounded-full text-xs font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="calendarBookingForm.processing"
                            class="px-6 py-2.5 bg-brand-teal hover:bg-brand-600 text-white text-xs font-bold rounded-full shadow-md shadow-brand-teal/20 cursor-pointer disabled:opacity-50"
                        >
                            Confirm Booking & Get Queue Token
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Add Child / Dependent Modal -->
        <div
            v-if="showAddDependentModal"
            class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
        >
            <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                <div class="p-6 bg-gradient-to-r from-brand-teal to-cyan-700 text-white flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold">Register Child or Dependent</h3>
                        <p class="text-xs text-cyan-100">Saved to your family profile for quick booking and record tracking</p>
                    </div>
                    <button @click="showAddDependentModal = false" class="text-white/80 hover:text-white text-xl font-bold cursor-pointer">✕</button>
                </div>

                <form @submit.prevent="submitDependent" class="p-6 space-y-4 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-600 mb-1">Child / Dependent Full Name</label>
                        <input v-model="dependentForm.name" type="text" placeholder="e.g. Sofia Beatrice Mendoza" class="w-full rounded-xl border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 text-xs py-2" required />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Relationship</label>
                            <select v-model="dependentForm.relationship" class="w-full rounded-xl border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 text-xs py-2" required>
                                <option value="Son">Son</option>
                                <option value="Daughter">Daughter</option>
                                <option value="Child">Child</option>
                                <option value="Spouse">Spouse</option>
                                <option value="Parent">Parent</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Age</label>
                            <input v-model="dependentForm.age" type="number" placeholder="3" min="0" max="130" class="w-full rounded-xl border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 text-xs py-2" required />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Gender</label>
                            <select v-model="dependentForm.gender" class="w-full rounded-xl border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 text-xs py-2">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Blood Type</label>
                            <select v-model="dependentForm.blood_type" class="w-full rounded-xl border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 text-xs py-2">
                                <option value="Unknown">Unknown / Not sure</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                                <option value="A+">A+</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="AB+">AB+</option>
                                <option value="AB-">AB-</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-600 mb-1">Known Allergies / Medical Conditions</label>
                        <textarea v-model="dependentForm.allergies" rows="2" placeholder="e.g. No known allergies, or mild asthma..." class="w-full rounded-xl border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 text-xs py-2"></textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button
                            type="button"
                            @click="showAddDependentModal = false"
                            class="px-4 py-2 rounded-full text-xs font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="dependentForm.processing"
                            class="px-6 py-2.5 bg-brand-teal hover:bg-brand-600 text-white text-xs font-bold rounded-full shadow-md shadow-brand-teal/20 cursor-pointer disabled:opacity-50"
                        >
                            Save Dependent Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
