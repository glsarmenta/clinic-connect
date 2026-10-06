<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DoctorAvailabilityTimeline from '@/Components/DoctorAvailabilityTimeline.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    doctor: Object,
    activeAppointment: Object,
    waitingQueue: Array,
    upcomingBooked: Array,
    completedToday: Array,
    waitingCount: Number,
});

const showScheduleModal = ref(false);

const consultationForm = useForm({
    consultation_notes: props.activeAppointment?.consultation_notes || '',
    prescription: '',
});

const setLiveStatus = (status) => {
    router.patch(route('doctor.live-status.update'), {
        live_status: status,
    }, {
        preserveScroll: true,
    });
};

const callNextPatient = () => {
    router.post(route('doctor.call-next'), {}, {
        preserveScroll: true,
    });
};

const completeActiveConsultation = () => {
    if (!props.activeAppointment) return;

    consultationForm.patch(route('doctor.consultation.complete', props.activeAppointment.id), {
        preserveScroll: true,
        onSuccess: () => {
            consultationForm.reset();
        },
    });
};
</script>

<template>
    <Head title="Doctor Workspace - Clinic Connect" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-extrabold leading-tight text-slate-900 flex items-center gap-2">
                        <span>{{ doctor.name }}</span>
                        <span class="text-xs px-3 py-1 rounded-full bg-brand-100 text-brand-800 font-semibold border border-brand-200">
                            {{ doctor.specialization }}
                        </span>
                    </h2>
                    <p class="text-xs text-brand-teal font-medium mt-0.5">Clinical Workspace & Patient Consultation Stream</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <!-- Doctor Availability Live Toggle -->
                    <div class="flex items-center gap-1.5 bg-white/90 backdrop-blur-sm p-1.5 rounded-full border border-slate-200 shadow-xs">
                        <span class="text-[11px] font-bold uppercase text-slate-400 pl-2.5 pr-1">Status:</span>
                        <button
                            @click="setLiveStatus('in_clinic')"
                            :class="doctor.live_status === 'in_clinic' ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                            class="px-3.5 py-1 rounded-full text-xs transition-all flex items-center gap-1.5 cursor-pointer font-medium"
                        >
                            <span class="w-2 h-2 rounded-full bg-emerald-400" :class="{'animate-ping': doctor.live_status === 'in_clinic'}"></span>
                            In Clinic
                        </button>

                        <button
                            @click="setLiveStatus('delayed')"
                            :class="doctor.live_status === 'delayed' ? 'bg-amber-500 text-white font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                            class="px-3.5 py-1 rounded-full text-xs transition-all flex items-center gap-1.5 cursor-pointer font-medium"
                        >
                            <span class="w-2 h-2 rounded-full bg-amber-300"></span>
                            Delayed
                        </button>

                        <button
                            @click="setLiveStatus('out_of_clinic')"
                            :class="doctor.live_status === 'out_of_clinic' ? 'bg-rose-600 text-white font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                            class="px-3.5 py-1 rounded-full text-xs transition-all flex items-center gap-1.5 cursor-pointer font-medium"
                        >
                            <span class="w-2 h-2 rounded-full bg-rose-300"></span>
                            Out of Clinic
                        </button>
                    </div>

                    <!-- My Schedule & Timeline Modal Trigger -->
                    <button
                        type="button"
                        @click="showScheduleModal = true"
                        class="px-4 py-2 bg-brand-teal hover:bg-brand-600 text-white font-bold text-xs rounded-full shadow-xs transition-all flex items-center gap-1.5 cursor-pointer"
                    >
                        <span>📅 My Schedule & Timeline</span>
                    </button>

                    <!-- Clinic Homepage & Settings Direct Link -->
                    <Link
                        :href="route('doctor.clinic-settings.edit')"
                        class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-full shadow-xs transition-all flex items-center gap-1.5"
                    >
                        <span>⚙️ Clinic Customizer</span>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6 sm:py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Main Column: Active Patient Consultation Card -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Consultation Active Card -->
                    <div v-if="activeAppointment" class="bg-white/95 rounded-[32px] sm:rounded-[36px] border-2 border-emerald-500/80 shadow-xl shadow-cyan-950/5 overflow-hidden relative backdrop-blur-md">
                        <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-teal-700 text-white px-6 sm:px-8 py-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="px-3.5 py-1 rounded-full bg-white/20 font-mono font-bold text-lg backdrop-blur-xs">
                                    {{ activeAppointment.queue_number }}
                                </span>
                                <div>
                                    <div class="text-xs uppercase tracking-wider text-emerald-100 font-semibold">Active Consultation</div>
                                    <h3 class="text-lg font-bold leading-tight">{{ activeAppointment.patient_name }}</h3>
                                </div>
                            </div>

                            <span class="inline-flex items-center gap-1.5 text-xs bg-emerald-500/40 border border-emerald-300/40 px-3.5 py-1 rounded-full font-semibold">
                                <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                                In Session
                            </span>
                        </div>

                        <div class="p-6 sm:p-8 space-y-6">
                            <!-- Patient Demographics & Complaint -->
                            <div class="grid grid-cols-3 gap-4 pb-4 border-b border-slate-100 text-xs">
                                <div>
                                    <span class="text-slate-400 font-semibold uppercase tracking-wider block">Age & Gender</span>
                                    <span class="text-slate-900 font-bold text-sm mt-0.5 block">{{ activeAppointment.patient_age }} years old</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 font-semibold uppercase tracking-wider block">Service Type</span>
                                    <span class="text-slate-900 font-bold text-sm mt-0.5 block">{{ activeAppointment.service?.name }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 font-semibold uppercase tracking-wider block">Contact Phone</span>
                                    <span class="text-slate-900 font-bold text-sm mt-0.5 block">{{ activeAppointment.patient_phone }}</span>
                                </div>
                            </div>

                            <!-- Chief Complaint Banner -->
                            <div class="bg-teal-50/60 border border-teal-100 p-4 rounded-2xl">
                                <div class="text-xs font-bold uppercase tracking-wider text-teal-800 mb-1">Chief Complaint / Reason for Visit</div>
                                <p class="text-slate-800 text-sm leading-relaxed font-medium">
                                    "{{ activeAppointment.chief_complaint }}"
                                </p>
                            </div>

                            <!-- Doctor's Notes & Prescriptions -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                                        Clinical Assessment & Consultation Notes
                                    </label>
                                    <textarea
                                        v-model="consultationForm.consultation_notes"
                                        rows="4"
                                        placeholder="Record diagnosis summary, symptoms observed, vitals (BP, temperature), advice..."
                                        class="w-full rounded-2xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm py-3 shadow-xs"
                                    ></textarea>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                                        Prescription / Recommended Treatment (Optional)
                                    </label>
                                    <input
                                        v-model="consultationForm.prescription"
                                        type="text"
                                        placeholder="e.g. Amoxicillin 500mg tid x 7 days, rest, oral hydration"
                                        class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm py-2.5 shadow-xs"
                                    />
                                </div>
                            </div>

                            <!-- Action Complete Button -->
                            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                                <div class="text-xs text-slate-500">
                                    Clicking Done completes consultation and records it in medical history.
                                </div>

                                <button
                                    @click="completeActiveConsultation"
                                    :disabled="consultationForm.processing"
                                    type="button"
                                    class="inline-flex items-center gap-2 px-8 py-3.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold rounded-full shadow-lg shadow-emerald-600/30 text-sm transition-all cursor-pointer disabled:opacity-50"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Done / Complete Consultation
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Idle Screen when no active patient is in consultation -->
                    <div v-else class="bg-white/95 rounded-[32px] sm:rounded-[36px] border border-slate-200/80 p-12 text-center shadow-sm space-y-4 backdrop-blur-md">
                        <div class="w-20 h-20 rounded-2xl overflow-hidden mx-auto shadow-sm">
                            <img src="/images/dept_family.jpg" alt="Clinical Stethoscope" class="w-full h-full object-contain" />
                        </div>
                        <h3 class="text-xl font-bold text-slate-900">No Patient Currently In Consultation</h3>
                        <p class="text-sm text-slate-500 max-w-md mx-auto">
                            There are currently {{ waitingCount }} patient(s) waiting in the lobby. Call in the next patient to begin consultation.
                        </p>

                        <button
                            v-if="waitingCount > 0"
                            @click="callNextPatient"
                            type="button"
                            class="inline-flex items-center gap-2 px-7 py-3 bg-brand-teal hover:bg-brand-600 text-white font-bold text-sm rounded-full shadow-md shadow-brand-teal/20 cursor-pointer transition-all"
                        >
                            Call Next Patient ({{ waitingQueue[0]?.queue_number }}) &rarr;
                        </button>
                    </div>

                    <!-- Completed Today Log -->
                    <div class="bg-white/95 rounded-[32px] border border-slate-200/80 shadow-xs p-6 backdrop-blur-md">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                            <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                                <span>Completed Consultations Today</span>
                                <span class="text-xs px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600 font-bold">
                                    {{ completedToday.length }}
                                </span>
                            </h4>
                        </div>

                        <div class="space-y-3">
                            <div
                                v-for="item in completedToday"
                                :key="item.id"
                                class="p-3.5 rounded-2xl bg-arctic-50/60 border border-slate-100 flex items-center justify-between text-xs"
                            >
                                <div class="flex items-center gap-3">
                                    <span class="font-mono font-bold text-slate-900 bg-white px-2 py-0.5 rounded-lg border border-slate-200 shadow-xs">{{ item.queue_number }}</span>
                                    <div>
                                        <div class="font-bold text-slate-800">{{ item.patient_name }}</div>
                                        <div class="text-[11px] text-slate-500">{{ item.service?.name }}</div>
                                    </div>
                                </div>

                                <div class="text-right">
                                    <span class="text-emerald-700 font-semibold bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200 text-[11px]">Completed</span>
                                    <span class="block text-[10px] text-slate-400 mt-0.5">{{ item.consultation_notes ? 'Notes recorded' : 'No notes' }}</span>
                                </div>
                            </div>

                            <div v-if="completedToday.length === 0" class="text-center py-4 text-xs text-slate-400">
                                No consultations completed yet today.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Side Column: Waiting Room Queue -->
                <div class="space-y-6">
                    
                    <!-- Waiting Room Card -->
                    <div class="bg-white/95 rounded-[32px] border border-slate-200/80 shadow-xs p-6 space-y-4 backdrop-blur-md">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <div>
                                <h3 class="font-bold text-slate-900 text-sm">Waiting Room Queue</h3>
                                <p class="text-xs text-slate-500">Patients checked in and waiting in lobby</p>
                            </div>
                            
                            <!-- Active Queue Counter Badge -->
                            <div class="px-3 py-1 rounded-full bg-amber-100 border border-amber-300 text-amber-800 font-black text-xs">
                                {{ waitingCount }} Waiting
                            </div>
                        </div>

                        <!-- Waiting List Items -->
                        <div class="space-y-2.5">
                            <div
                                v-for="(patient, index) in waitingQueue"
                                :key="patient.id"
                                class="p-3.5 rounded-2xl border transition-all"
                                :class="index === 0 ? 'bg-amber-50/70 border-amber-300 ring-2 ring-amber-400/20' : 'bg-slate-50 border-slate-200'"
                            >
                                <div class="flex items-start justify-between">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono font-bold text-xs bg-white px-2 py-0.5 rounded-lg border border-slate-200 shadow-xs">
                                                {{ patient.queue_number }}
                                            </span>
                                            <span class="font-bold text-slate-900 text-xs">{{ patient.patient_name }}</span>
                                            <span class="text-[11px] text-slate-500">({{ patient.patient_age }}y)</span>
                                        </div>
                                        <div class="text-[11px] text-slate-600 mt-1 line-clamp-1">
                                            {{ patient.chief_complaint }}
                                        </div>
                                    </div>

                                    <button
                                        v-if="index === 0 && !activeAppointment"
                                        @click="callNextPatient"
                                        type="button"
                                        class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] rounded-full shadow-xs cursor-pointer"
                                    >
                                        Call In
                                    </button>
                                </div>
                            </div>

                            <div v-if="waitingQueue.length === 0" class="text-center py-6 text-xs text-slate-400">
                                The waiting room is currently empty.
                            </div>
                        </div>
                    </div>

                    <!-- Upcoming Booked Appointments -->
                    <div class="bg-white/95 rounded-[32px] border border-slate-200/80 shadow-xs p-6 space-y-4 backdrop-blur-md">
                        <div class="border-b border-slate-100 pb-3">
                            <h3 class="font-bold text-slate-900 text-sm">Upcoming Booked (Today)</h3>
                            <p class="text-xs text-slate-500">Not yet checked in at reception</p>
                        </div>

                        <div class="space-y-2">
                            <div
                                v-for="b in upcomingBooked"
                                :key="b.id"
                                class="p-3 rounded-2xl bg-arctic-50/60 border border-slate-100 text-xs flex items-center justify-between"
                            >
                                <div>
                                    <div class="font-bold text-slate-800">{{ b.patient_name }} ({{ b.queue_number }})</div>
                                    <div class="text-[11px] text-slate-500">{{ b.time_slot }}</div>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-brand-50 text-brand-800 border border-brand-200">
                                    Booked
                                </span>
                            </div>

                            <div v-if="upcomingBooked.length === 0" class="text-center py-4 text-xs text-slate-400">
                                No remaining booked arrivals today.
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>

        <!-- Doctor Schedule & Availability Timeline Modal -->
        <div
            v-if="showScheduleModal"
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 sm:p-6"
            @click.self="showScheduleModal = false"
        >
            <div class="bg-white rounded-[32px] sm:rounded-[36px] max-w-5xl w-full shadow-2xl overflow-hidden border border-white/80 my-8">
                <div class="bg-slate-900 text-white px-6 py-4 flex items-center justify-between border-b border-slate-800">
                    <div class="flex items-center gap-2.5">
                        <span class="text-xl">📅</span>
                        <div>
                            <h3 class="font-bold text-sm text-white">Plot Availability & Working Timeline</h3>
                            <p class="text-[11px] text-teal-300">{{ doctor.name }} — {{ doctor.specialization }}</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="showScheduleModal = false"
                        class="w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-300 flex items-center justify-center font-bold text-sm transition-colors cursor-pointer"
                    >
                        ✕
                    </button>
                </div>

                <div class="p-6 sm:p-8 max-h-[80vh] overflow-y-auto">
                    <DoctorAvailabilityTimeline
                        :doctor="doctor"
                        :is-workspace-view="true"
                        :submit-route="route('doctor.availability.update')"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
