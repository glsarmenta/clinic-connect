<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    clinic: Object,
    doctors: Array,
    services: Array,
    authUser: Object,
    dependents: Array,
});

const todayDate = new Date().toISOString().split('T')[0];

const form = useForm({
    doctor_id: props.doctors.length > 0 ? props.doctors[0].id : '',
    service_id: props.services.length > 0 ? props.services[0].id : '',
    appointment_date: todayDate,
    time_slot: '09:00 AM - 09:30 AM',
    patient_name: props.authUser ? props.authUser.name : '',
    patient_age: '',
    patient_phone: props.authUser ? (props.authUser.phone || '') : '',
    chief_complaint: '',
});

const selectedProfile = ref(props.authUser ? 'self' : 'custom');

const onProfileSelect = (val) => {
    selectedProfile.value = val;
    if (val === 'self' && props.authUser) {
        form.patient_name = props.authUser.name;
        form.patient_age = '';
        if (props.authUser.phone) form.patient_phone = props.authUser.phone;
    } else if (val !== 'custom') {
        const dep = props.dependents?.find(d => d.id === Number(val));
        if (dep) {
            form.patient_name = dep.name;
            form.patient_age = dep.age;
            if (props.authUser?.phone) form.patient_phone = props.authUser.phone;
        }
    } else {
        form.patient_name = '';
        form.patient_age = '';
    }
};

const selectedDoctor = computed(() => {
    return props.doctors.find(d => d.id === Number(form.doctor_id));
});

const selectedService = computed(() => {
    return props.services.find(s => s.id === Number(form.service_id));
});

const selectedDayOfWeek = computed(() => {
    if (!form.appointment_date) return null;
    const parts = form.appointment_date.split('-').map(Number);
    const dateObj = new Date(parts[0], parts[1] - 1, parts[2]);
    return dateObj.getDay(); // 0 = Sun, 1 = Mon ... 6 = Sat
});

const dayNameSelected = computed(() => {
    const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    return selectedDayOfWeek.value !== null ? days[selectedDayOfWeek.value] : '';
});

const doctorAvailabilitiesForDate = computed(() => {
    if (!selectedDoctor.value || selectedDayOfWeek.value === null) return [];
    const availabilities = selectedDoctor.value.availabilities || [];
    return availabilities.filter(a => Number(a.day_of_week) === selectedDayOfWeek.value && a.is_available);
});

const isDoctorAvailableOnDate = computed(() => {
    // If no availabilities are configured at all, default to true
    if (!selectedDoctor.value?.availabilities || selectedDoctor.value.availabilities.length === 0) {
        return true;
    }
    return doctorAvailabilitiesForDate.value.length > 0;
});

const doctorWeeklyScheduleSummary = computed(() => {
    if (!selectedDoctor.value?.availabilities || selectedDoctor.value.availabilities.length === 0) {
        return 'Monday – Saturday (8:00 AM – 5:00 PM)';
    }
    const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    const activeDays = selectedDoctor.value.availabilities
        .filter(a => a.is_available)
        .map(a => dayNames[Number(a.day_of_week)]);
    const uniqueDays = [...new Set(activeDays)];
    return uniqueDays.length > 0 ? uniqueDays.join(', ') : 'Not scheduled';
});

const formatTime12h = (hour, minute) => {
    const period = hour >= 12 ? 'PM' : 'AM';
    const hour12 = hour % 12 || 12;
    const minStr = minute < 10 ? '0' + minute : minute;
    return `${hour12}:${minStr} ${period}`;
};

const computedTimeSlots = computed(() => {
    if (!isDoctorAvailableOnDate.value) {
        return [];
    }

    // If doctor has no recorded availabilities, fallback to standard defaults
    if (!selectedDoctor.value?.availabilities || selectedDoctor.value.availabilities.length === 0) {
        return [
            { window: 'Morning', slots: ['08:30 AM - 09:00 AM', '09:00 AM - 09:30 AM', '09:30 AM - 10:00 AM', '10:30 AM - 11:00 AM', '11:00 AM - 11:30 AM'] },
            { window: 'Afternoon', slots: ['01:30 PM - 02:00 PM', '02:00 PM - 02:30 PM', '02:30 PM - 03:00 PM', '03:30 PM - 04:00 PM', '04:00 PM - 04:30 PM'] },
        ];
    }

    const morningSlots = [];
    const afternoonSlots = [];
    const duration = selectedService.value?.duration_minutes || 30;
    const step = Math.max(15, Math.min(60, duration));

    doctorAvailabilitiesForDate.value.forEach(avail => {
        const [startH, startM] = (avail.start_time || '08:00').split(':').map(Number);
        const [endH, endM] = (avail.end_time || '17:00').split(':').map(Number);

        let currentMin = startH * 60 + startM;
        const endTotalMin = endH * 60 + endM;

        while (currentMin + step <= endTotalMin) {
            const slotStartH = Math.floor(currentMin / 60);
            const slotStartM = currentMin % 60;
            const slotEndMin = currentMin + step;
            const slotEndH = Math.floor(slotEndMin / 60);
            const slotEndM = slotEndMin % 60;

            const label = `${formatTime12h(slotStartH, slotStartM)} - ${formatTime12h(slotEndH, slotEndM)}`;

            if (slotStartH < 12) {
                if (!morningSlots.includes(label)) morningSlots.push(label);
            } else {
                if (!afternoonSlots.includes(label)) afternoonSlots.push(label);
            }

            currentMin += step;
        }
    });

    const groups = [];
    if (morningSlots.length > 0) {
        groups.push({ window: 'Morning', slots: morningSlots });
    }
    if (afternoonSlots.length > 0) {
        groups.push({ window: 'Afternoon', slots: afternoonSlots });
    }

    return groups;
});

watch(computedTimeSlots, (newGroups) => {
    const allSlots = newGroups.flatMap(g => g.slots);
    if (allSlots.length > 0 && !allSlots.includes(form.time_slot)) {
        form.time_slot = allSlots[0];
    }
}, { immediate: true });

const submitBooking = () => {
    form.post(route('booking.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Book Appointment - Clinic Connect" />

    <div class="min-h-screen bg-gradient-to-br from-[#cbeef4]/30 via-[#f0f9fb] to-[#e6f7fa]/50 text-slate-800 pb-16">
        <!-- Prototype Demo Switcher Bar -->
        <div class="bg-slate-900 text-white text-xs py-2.5 px-4 border-b border-slate-800 shadow-inner">
            <div class="max-w-6xl mx-auto flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-cyan-400 animate-pulse"></span>
                    <span class="font-bold text-slate-200">Interactive MVP Demo:</span>
                    <span class="text-slate-400 hidden sm:inline">Role switcher active</span>
                </div>
                <div class="flex items-center gap-1.5 sm:gap-2">
                    <a href="/" class="px-3 py-1 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors text-[11px] font-medium">
                        🏠 Clinic Homepage
                    </a>
                    <a href="/book" class="px-3 py-1 rounded-full bg-cyan-500/20 text-cyan-300 font-bold border border-cyan-500/30 text-[11px]">
                        1. Patient Booking
                    </a>
                    <a href="/demo-switch/secretary" class="px-3 py-1 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors text-[11px] font-medium">
                        2. Secretary Queue
                    </a>
                    <a href="/demo-switch/doctor1" class="px-3 py-1 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors text-[11px] font-medium">
                        3. Doctor Workspace
                    </a>
                    <a href="/demo-switch/patient" class="px-3 py-1 rounded-full bg-teal-500/20 text-teal-300 hover:bg-teal-500/30 font-bold border border-teal-500/40 text-[11px] transition-colors">
                        4. Patient & Kids Portal
                    </a>
                </div>
            </div>
        </div>

        <!-- Top Navigation -->
        <header class="border-b border-cyan-100/70 bg-white/80 backdrop-blur-md sticky top-0 z-30 shadow-xs">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <Link :href="route('home')" class="flex items-center space-x-3 group">
                    <img
                        v-if="clinic?.logo_url"
                        :src="clinic.logo_url"
                        :alt="clinic?.name"
                        class="w-10 h-10 object-contain rounded-xl border border-slate-200 shadow-xs"
                    />
                    <div v-else class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-brand-teal to-cyan-600 text-white flex items-center justify-center font-bold text-xl shadow-md shadow-cyan-500/20">
                        <svg class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M11 2a1 1 0 012 0v8h8a1 1 0 010 2h-8v8a1 1 0 01-2 0v-8H3a1 1 0 010-2h8V2z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight leading-none group-hover:text-brand-600 transition-colors">{{ clinic?.name || 'CarePlus Medical' }}</h1>
                        <p class="text-xs text-brand-teal font-medium mt-0.5">Online Patient Booking & Consultation</p>
                    </div>
                </Link>

                <div class="flex items-center space-x-2 sm:space-x-3 text-sm font-medium">
                    <Link v-if="authUser" :href="route('patient.dashboard')" class="text-xs font-bold text-brand-700 bg-brand-50 border border-brand-200 px-3.5 py-1.5 rounded-full hover:bg-brand-100 transition-colors">
                        My Family Health Portal &rarr;
                    </Link>
                    <Link v-else :href="route('login')" class="text-slate-600 hover:text-brand-600 px-3 py-1.5 rounded-full transition-colors text-xs font-semibold">
                        Staff & Patient Login
                    </Link>
                    <Link :href="route('home')" class="hidden sm:inline-flex text-xs text-brand-teal bg-white border border-brand-200/70 px-3 py-1.5 rounded-full font-semibold hover:bg-brand-50 transition-colors">
                        View Homepage
                    </Link>
                </div>
            </div>
        </header>

        <main class="max-w-4xl mx-auto px-4 sm:px-6 py-8 sm:py-12">
            <!-- Hero Title -->
            <div class="text-center mb-8">
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-semibold bg-brand-100 text-brand-800 mb-3 border border-brand-200/60 shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-brand-teal animate-pulse"></span>
                    Direct Clinic Appointment Booking
                </span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Schedule Your Visit
                </h2>
                <p class="text-slate-600 max-w-xl mx-auto mt-2 text-sm sm:text-base">
                    Reserve an appointment for yourself or your children with immediate token generation.
                </p>
            </div>

            <!-- Booking Card -->
            <div class="bg-white/95 rounded-[32px] sm:rounded-[36px] shadow-xl shadow-cyan-950/5 border border-white/80 overflow-hidden backdrop-blur-md">
                <form @submit.prevent="submitBooking" class="p-6 sm:p-10 space-y-8">
                    
                    <!-- Step 1: Specialist & Service -->
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3 border-b border-cyan-50 pb-3">
                            <span class="w-7 h-7 rounded-full bg-brand-teal text-white font-bold text-sm flex items-center justify-center shadow-xs">1</span>
                            <h3 class="text-lg font-bold text-slate-900">Select Specialist & Service</h3>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Doctor Selector -->
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">
                                    Attending Physician
                                </label>
                                <select
                                    v-model="form.doctor_id"
                                    class="w-full rounded-xl border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 bg-white/95 text-slate-800 text-sm py-2.5 transition-all shadow-xs"
                                    required
                                >
                                    <option v-for="doc in doctors" :key="doc.id" :value="doc.id">
                                        {{ doc.name }} ({{ doc.specialization || 'General Specialist' }})
                                    </option>
                                </select>

                                <!-- Live Doctor Status Indicator -->
                                <div v-if="selectedDoctor" class="mt-2 flex items-center gap-2 text-xs">
                                    <span class="text-slate-500">Live Status:</span>
                                    <span 
                                        :class="{
                                            'bg-emerald-100 text-emerald-800 border-emerald-300': selectedDoctor.live_status === 'in_clinic',
                                            'bg-amber-100 text-amber-800 border-amber-300': selectedDoctor.live_status === 'delayed',
                                            'bg-rose-100 text-rose-800 border-rose-300': selectedDoctor.live_status === 'out_of_clinic'
                                        }"
                                        class="px-2.5 py-0.5 rounded-full font-semibold border text-[11px] capitalize"
                                    >
                                        {{ selectedDoctor.live_status?.replace('_', ' ') || 'In Clinic' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Service Selector -->
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">
                                    Visit Type / Service
                                </label>
                                <select
                                    v-model="form.service_id"
                                    class="w-full rounded-xl border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 bg-white/95 text-slate-800 text-sm py-2.5 transition-all shadow-xs"
                                    required
                                >
                                    <option v-for="svc in services" :key="svc.id" :value="svc.id">
                                        {{ svc.name }} — ₱{{ svc.price }} ({{ svc.duration_minutes }} mins)
                                    </option>
                                </select>
                                <p v-if="selectedService" class="mt-2 text-xs text-slate-500 line-clamp-1">
                                    {{ selectedService.description }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Date & Available Slot Picker -->
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3 border-b border-cyan-50 pb-3">
                            <span class="w-7 h-7 rounded-full bg-brand-teal text-white font-bold text-sm flex items-center justify-center shadow-xs">2</span>
                            <h3 class="text-lg font-bold text-slate-900">Choose Appointment Window</h3>
                        </div>

                        <div class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-end">
                                <div class="max-w-xs">
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">
                                        Visit Date <span v-if="dayNameSelected" class="text-brand-teal font-bold">({{ dayNameSelected }})</span>
                                    </label>
                                    <input
                                        type="date"
                                        v-model="form.appointment_date"
                                        :min="todayDate"
                                        class="w-full rounded-xl border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 bg-white/95 text-slate-800 text-sm py-2.5 shadow-xs"
                                        required
                                    />
                                </div>

                                <!-- Doctor Schedule Summary Badge -->
                                <div class="p-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs space-y-1">
                                    <div class="font-bold text-slate-700 flex items-center gap-1.5">
                                        <span>📅 {{ selectedDoctor?.name }}'s Weekly Schedule:</span>
                                    </div>
                                    <div class="text-[11px] text-teal-800 font-semibold">
                                        {{ doctorWeeklyScheduleSummary }}
                                    </div>
                                </div>
                            </div>

                            <!-- Doctor Not Available on This Day Notice -->
                            <div
                                v-if="!isDoctorAvailableOnDate"
                                class="p-4 rounded-2xl bg-amber-50 border border-amber-300 text-amber-900 text-xs sm:text-sm font-semibold flex items-start gap-3 shadow-xs"
                            >
                                <span class="text-lg">⚠️</span>
                                <div>
                                    <div class="font-bold">{{ selectedDoctor?.name }} is not available on {{ dayNameSelected }}s.</div>
                                    <p class="text-xs text-amber-800 mt-0.5 font-normal">
                                        Please select another date when the physician is in clinic (Available: <span class="font-bold">{{ doctorWeeklyScheduleSummary }}</span>).
                                    </p>
                                </div>
                            </div>

                            <!-- Slots Grid -->
                            <div v-else class="space-y-3">
                                <div v-for="group in computedTimeSlots" :key="group.window">
                                    <div class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                                        {{ group.window }} Windows
                                    </div>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2">
                                        <button
                                            type="button"
                                            v-for="slot in group.slots"
                                            :key="slot"
                                            @click="form.time_slot = slot"
                                            :class="{
                                                'bg-brand-teal text-white font-semibold shadow-md shadow-brand-teal/30 border-brand-teal ring-2 ring-brand-teal/20': form.time_slot === slot,
                                                'bg-slate-50 text-slate-700 hover:bg-brand-50 hover:text-brand-800 border-slate-200': form.time_slot !== slot
                                            }"
                                            class="py-2.5 px-3 text-xs rounded-xl border text-center transition-all cursor-pointer font-medium"
                                        >
                                            {{ slot.replace(' - ', ' - ') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Patient Information -->
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3 border-b border-cyan-50 pb-3">
                            <span class="w-7 h-7 rounded-full bg-brand-teal text-white font-bold text-sm flex items-center justify-center shadow-xs">3</span>
                            <h3 class="text-lg font-bold text-slate-900">Patient Details & Chief Complaint</h3>
                        </div>

                        <!-- Who is this appointment for? -->
                        <div v-if="authUser" class="p-4 rounded-2xl bg-arctic-100/70 border border-brand-200/80 mb-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-brand-900 mb-1.5">
                                Who is this appointment for?
                            </label>
                            <div class="flex flex-wrap gap-2 text-xs">
                                <button
                                    type="button"
                                    @click="onProfileSelect('self')"
                                    :class="selectedProfile === 'self' ? 'bg-brand-teal text-white font-bold shadow-xs' : 'bg-white text-slate-700 hover:bg-brand-50 border border-slate-200'"
                                    class="px-3.5 py-1.5 rounded-full transition-colors cursor-pointer"
                                >
                                    👤 Myself ({{ authUser.name }})
                                </button>
                                <button
                                    v-for="d in dependents"
                                    :key="d.id"
                                    type="button"
                                    @click="onProfileSelect(d.id)"
                                    :class="selectedProfile === d.id ? 'bg-brand-teal text-white font-bold shadow-xs' : 'bg-white text-slate-700 hover:bg-brand-50 border border-slate-200'"
                                    class="px-3.5 py-1.5 rounded-full transition-colors cursor-pointer"
                                >
                                    👶 {{ d.name }} ({{ d.relationship }}, {{ d.age }} y/o)
                                </button>
                                <button
                                    type="button"
                                    @click="onProfileSelect('custom')"
                                    :class="selectedProfile === 'custom' ? 'bg-brand-teal text-white font-bold shadow-xs' : 'bg-white text-slate-700 hover:bg-brand-50 border border-slate-200'"
                                    class="px-3.5 py-1.5 rounded-full transition-colors cursor-pointer"
                                >
                                    Other / Manual
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">
                                    Patient Full Name
                                </label>
                                <input
                                    type="text"
                                    v-model="form.patient_name"
                                    placeholder="e.g. Mateo dela Cruz"
                                    class="w-full rounded-xl border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 bg-white/95 text-slate-800 text-sm py-2.5 shadow-xs"
                                    required
                                />
                                <div v-if="form.errors.patient_name" class="text-xs text-rose-600 mt-1">
                                    {{ form.errors.patient_name }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">
                                    Age
                                </label>
                                <input
                                    type="number"
                                    v-model="form.patient_age"
                                    placeholder="e.g. 5"
                                    min="0"
                                    max="130"
                                    class="w-full rounded-xl border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 bg-white/95 text-slate-800 text-sm py-2.5 shadow-xs"
                                    required
                                />
                                <div v-if="form.errors.patient_age" class="text-xs text-rose-600 mt-1">
                                    {{ form.errors.patient_age }}
                                </div>
                            </div>

                            <div class="sm:col-span-3">
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">
                                    Mobile Phone Number
                                </label>
                                <input
                                    type="tel"
                                    v-model="form.patient_phone"
                                    placeholder="e.g. +63 917 555 1234"
                                    class="w-full rounded-xl border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 bg-white/95 text-slate-800 text-sm py-2.5 shadow-xs"
                                    required
                                />
                                <div v-if="form.errors.patient_phone" class="text-xs text-rose-600 mt-1">
                                    {{ form.errors.patient_phone }}
                                </div>
                            </div>

                            <div class="sm:col-span-3">
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">
                                    Chief Complaint / Reason for Visit
                                </label>
                                <textarea
                                    v-model="form.chief_complaint"
                                    rows="3"
                                    placeholder="Describe current symptoms or checkup purpose (e.g. 5-year routine vaccination and fever check)..."
                                    class="w-full rounded-xl border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 bg-white/95 text-slate-800 text-sm py-2 shadow-xs"
                                    required
                                ></textarea>
                                <div v-if="form.errors.chief_complaint" class="text-xs text-rose-600 mt-1">
                                    {{ form.errors.chief_complaint }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Summary & Submit Button -->
                    <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="text-xs text-slate-500 text-center sm:text-left">
                            <span class="font-medium text-slate-700">Immediate Confirmation:</span> A queue token will be generated on screen upon submission.
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 bg-brand-teal hover:bg-brand-600 active:bg-brand-700 text-white font-bold rounded-full shadow-lg shadow-brand-teal/25 transition-all text-sm disabled:opacity-50 cursor-pointer"
                        >
                            <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            Confirm Booking & Get Queue Token
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</template>
