<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    clinic: Object,
    appointments: Array,
    doctors: Array,
    assignedDoctors: {
        type: Array,
        default: () => [],
    },
    services: Array,
    stats: Object,
    selectedDoctorId: [String, Number],
    currentSecretary: Object,
});

const showWalkInModal = ref(false);

const initialDoctorId = props.assignedDoctors.length === 1
    ? props.assignedDoctors[0].id
    : (props.doctors.length > 0 ? props.doctors[0].id : '');

const walkInForm = useForm({
    doctor_id: initialDoctorId,
    service_id: props.services.length > 0 ? props.services[0].id : '',
    patient_name: '',
    patient_age: '',
    patient_phone: '',
    chief_complaint: '',
});

const isDoctorAssignedToMe = (docId) => {
    return props.assignedDoctors.some(d => d.id === docId);
};

const submitWalkIn = () => {
    walkInForm.post(route('secretary.walkin.store'), {
        preserveScroll: true,
        onSuccess: () => {
            walkInForm.reset('patient_name', 'patient_age', 'patient_phone', 'chief_complaint');
            showWalkInModal.value = false;
        },
    });
};

const updateStatus = (appointmentId, newStatus) => {
    router.patch(route('secretary.appointments.status', appointmentId), {
        status: newStatus,
    }, {
        preserveScroll: true,
    });
};

const filterByDoctor = (doctorId) => {
    router.get(route('secretary.dashboard'), {
        doctor_id: doctorId || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'waiting_in_lobby':
            return 'bg-amber-100 text-amber-800 border-amber-300';
        case 'in_consultation':
            return 'bg-emerald-100 text-emerald-800 border-emerald-300 animate-pulse';
        case 'completed':
            return 'bg-slate-100 text-slate-700 border-slate-300';
        case 'cancelled':
            return 'bg-rose-100 text-rose-800 border-rose-300';
        case 'booked':
        default:
            return 'bg-sky-100 text-sky-800 border-sky-300';
    }
};

const formatStatusText = (status) => {
    switch (status) {
        case 'waiting_in_lobby': return 'Waiting in Lobby';
        case 'in_consultation': return 'In Consultation';
        case 'completed': return 'Completed';
        case 'cancelled': return 'Cancelled';
        case 'booked': return 'Booked';
        default: return status;
    }
};
</script>

<template>
    <Head title="Secretary Queue Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-extrabold leading-tight text-slate-900 flex items-center gap-2">
                        <span>Reception & Queue Board</span>
                        <span class="text-xs px-3 py-1 rounded-full bg-brand-100 text-brand-800 font-semibold border border-brand-200">
                            Today's Live Queue
                        </span>
                    </h2>
                    <p class="text-xs text-brand-teal font-medium mt-0.5">{{ clinic?.name }} • Track walk-ins, scheduled arrivals & patient status</p>
                </div>

                <div class="flex items-center gap-3">
                    <button
                        @click="showWalkInModal = true"
                        type="button"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-teal hover:bg-brand-600 active:bg-brand-700 text-white text-xs font-bold rounded-full shadow-md shadow-brand-teal/25 transition-all cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                        </svg>
                        + Add Walk-in
                    </button>
                </div>
            </div>
        </template>

        <div class="py-6 sm:py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Station Assignment Banner -->
            <div
                v-if="assignedDoctors.length > 0"
                class="bg-gradient-to-r from-teal-50 to-cyan-50 border border-brand-teal/30 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs"
            >
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-brand-teal text-white flex items-center justify-center font-bold text-sm shadow-xs shrink-0">
                        🎯
                    </div>
                    <div>
                        <div class="text-xs font-bold text-slate-900 flex items-center gap-2">
                            <span>Assigned Duty Station:</span>
                            <span class="text-brand-teal font-extrabold">
                                {{ assignedDoctors.map(d => d.name).join(' & ') }}
                            </span>
                        </div>
                        <div class="text-[11px] text-slate-500">
                            {{ assignedDoctors.length === 1 ? 'You are the dedicated receptionist for this physician.' : 'You are managing queues across multiple assigned physicians.' }}
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-slate-500">Active Queue Filter:</span>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-white text-slate-800 border border-slate-200 shadow-2xs">
                        {{ selectedDoctorId ? (doctors.find(d => d.id === Number(selectedDoctorId))?.name || 'Selected Doctor') : (assignedDoctors.length === 1 ? assignedDoctors[0].name : 'All Assigned Doctors') }}
                    </span>
                </div>
            </div>

            <div
                v-else
                class="bg-slate-50 border border-slate-200 rounded-2xl p-3 px-4 flex items-center justify-between text-xs text-slate-600"
            >
                <div class="flex items-center gap-2">
                    <span class="font-bold text-slate-700">🌐 Float Receptionist:</span>
                    <span>Clinic-wide coverage (viewing and managing all active physicians).</span>
                </div>
                <Link :href="route('doctor.clinic-settings.edit')" class="text-brand-teal hover:underline font-semibold text-[11px]">
                    Manage Assignments &rarr;
                </Link>
            </div>
            
            <!-- Real-Time Metrics -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white/95 p-5 rounded-[28px] border border-slate-200/80 shadow-xs flex items-center justify-between backdrop-blur-md">
                    <div>
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Today</div>
                        <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">{{ stats.total }}</div>
                    </div>
                    <div class="w-11 h-11 rounded-2xl bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-lg shadow-xs">
                        📋
                    </div>
                </div>

                <div class="bg-white/95 p-5 rounded-[28px] border border-amber-200 shadow-xs flex items-center justify-between backdrop-blur-md">
                    <div>
                        <div class="text-xs font-bold uppercase tracking-wider text-amber-600">Waiting in Lobby</div>
                        <div class="text-2xl sm:text-3xl font-black text-amber-700 mt-1">{{ stats.waiting }}</div>
                    </div>
                    <div class="w-11 h-11 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-lg shadow-xs">
                        ⏳
                    </div>
                </div>

                <div class="bg-white/95 p-5 rounded-[28px] border border-emerald-200 shadow-xs flex items-center justify-between backdrop-blur-md">
                    <div>
                        <div class="text-xs font-bold uppercase tracking-wider text-emerald-600">In Consultation</div>
                        <div class="text-2xl sm:text-3xl font-black text-emerald-700 mt-1">{{ stats.in_consultation }}</div>
                    </div>
                    <div class="w-11 h-11 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-lg shadow-xs">
                        🩺
                    </div>
                </div>

                <div class="bg-white/95 p-5 rounded-[28px] border border-slate-200/80 shadow-xs flex items-center justify-between backdrop-blur-md">
                    <div>
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Completed</div>
                        <div class="text-2xl sm:text-3xl font-black text-slate-700 mt-1">{{ stats.completed }}</div>
                    </div>
                    <div class="w-11 h-11 rounded-2xl bg-slate-100 text-slate-500 flex items-center justify-center font-bold text-lg shadow-xs">
                        ✅
                    </div>
                </div>
            </div>

            <!-- Doctor Status Roster & Filter -->
            <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold uppercase text-slate-400 tracking-wider">Physician Status:</span>
                    <div class="flex flex-wrap gap-2">
                        <div
                            v-for="doc in doctors"
                            :key="doc.id"
                            :class="isDoctorAssignedToMe(doc.id) ? 'bg-teal-50/80 border-teal-300' : 'bg-slate-50 border-slate-200'"
                            class="inline-flex items-center gap-2 px-3 py-1 rounded-xl text-xs font-medium border"
                        >
                            <span v-if="isDoctorAssignedToMe(doc.id)" class="text-[10px] text-teal-700 font-bold">★ Assigned:</span>
                            <span class="font-bold text-slate-800">{{ doc.name }}</span>
                            <span
                                :class="{
                                    'bg-emerald-500': doc.live_status === 'in_clinic',
                                    'bg-amber-500': doc.live_status === 'delayed',
                                    'bg-rose-500': doc.live_status === 'out_of_clinic'
                                }"
                                class="w-2 h-2 rounded-full"
                            ></span>
                            <span class="text-[11px] text-slate-500 capitalize">{{ doc.live_status?.replace('_', ' ') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Doctor Filter Tabs -->
                <div class="flex items-center gap-1.5 text-xs bg-slate-100 p-1 rounded-xl">
                    <button
                        @click="filterByDoctor('')"
                        :class="!selectedDoctorId ? 'bg-white font-bold text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                        class="px-3 py-1.5 rounded-lg transition-all cursor-pointer"
                    >
                        {{ assignedDoctors.length > 0 ? 'Assigned Queues' : 'All Doctors' }}
                    </button>
                    <button
                        v-for="doc in doctors"
                        :key="doc.id"
                        @click="filterByDoctor(doc.id)"
                        :class="Number(selectedDoctorId) === doc.id ? 'bg-white font-bold text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                        class="px-3 py-1.5 rounded-lg transition-all cursor-pointer flex items-center gap-1"
                    >
                        <span v-if="isDoctorAssignedToMe(doc.id)" class="text-teal-600 font-black">★</span>
                        <span>{{ doc.name.split(' ')[1] || doc.name }}</span>
                    </button>
                </div>
            </div>

            <!-- Queue Table -->
            <div class="bg-white/95 rounded-[32px] border border-slate-200/80 shadow-xs overflow-hidden backdrop-blur-md">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-bold text-slate-900 text-sm">Active Patient Visits ({{ appointments.length }})</h3>
                    <span class="text-xs text-brand-teal font-medium">Auto-prioritized: In Consultation &rarr; Waiting &rarr; Booked</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs sm:text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 bg-arctic-50/75 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                <th class="py-3.5 px-4">Queue #</th>
                                <th class="py-3.5 px-4">Patient Details</th>
                                <th class="py-3.5 px-4">Assigned Doctor</th>
                                <th class="py-3.5 px-4">Service & Slot</th>
                                <th class="py-3.5 px-4">Chief Complaint</th>
                                <th class="py-3.5 px-4">Stage</th>
                                <th class="py-3.5 px-4 text-right">Quick Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="appt in appointments"
                                :key="appt.id"
                                :class="{'bg-emerald-50/30': appt.status === 'in_consultation', 'bg-amber-50/20': appt.status === 'waiting_in_lobby'}"
                                class="hover:bg-slate-50/80 transition-colors"
                            >
                                <!-- Queue Token -->
                                <td class="py-3.5 px-4 font-mono font-bold text-base text-slate-900">
                                    {{ appt.queue_number }}
                                    <span v-if="appt.source === 'walk_in'" class="ml-1 text-[10px] font-sans font-medium px-2 py-0.5 rounded-full bg-brand-50 text-brand-700 border border-brand-200">
                                        Walk-in
                                    </span>
                                </td>

                                <!-- Patient Name & Contact -->
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900">{{ appt.patient_name }}</div>
                                    <div class="text-xs text-slate-500">Age: {{ appt.patient_age }} • {{ appt.patient_phone }}</div>
                                </td>

                                <!-- Doctor -->
                                <td class="py-3.5 px-4">
                                    <div class="font-medium text-slate-900">{{ appt.doctor?.name }}</div>
                                    <div class="text-[11px] text-brand-teal font-semibold">{{ appt.doctor?.specialization }}</div>
                                </td>

                                <!-- Service & Slot -->
                                <td class="py-3.5 px-4">
                                    <div class="font-medium text-slate-800">{{ appt.service?.name }}</div>
                                    <div class="text-xs text-slate-500">{{ appt.time_slot }}</div>
                                </td>

                                <!-- Complaint -->
                                <td class="py-3.5 px-4 max-w-xs">
                                    <p class="text-xs text-slate-600 line-clamp-2" :title="appt.chief_complaint">
                                        {{ appt.chief_complaint }}
                                    </p>
                                </td>

                                <!-- Status Badge -->
                                <td class="py-3.5 px-4">
                                    <span
                                        :class="getStatusBadgeClass(appt.status)"
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border"
                                    >
                                        {{ formatStatusText(appt.status) }}
                                    </span>
                                </td>

                                <!-- One-Click Actions -->
                                <td class="py-3.5 px-4 text-right space-x-1.5 whitespace-nowrap">
                                    <!-- Booked -> Waiting in Lobby -->
                                    <button
                                        v-if="appt.status === 'booked'"
                                        @click="updateStatus(appt.id, 'waiting_in_lobby')"
                                        type="button"
                                        class="px-3.5 py-1.5 rounded-full bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-xs transition-colors cursor-pointer"
                                    >
                                        Check In
                                    </button>

                                    <!-- Waiting -> In Consultation -->
                                    <button
                                        v-if="appt.status === 'waiting_in_lobby'"
                                        @click="updateStatus(appt.id, 'in_consultation')"
                                        type="button"
                                        class="px-3.5 py-1.5 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition-colors cursor-pointer"
                                    >
                                        Call In
                                    </button>

                                    <!-- In Consultation -> Completed -->
                                    <button
                                        v-if="appt.status === 'in_consultation'"
                                        @click="updateStatus(appt.id, 'completed')"
                                        type="button"
                                        class="px-3.5 py-1.5 rounded-full bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs shadow-xs transition-colors cursor-pointer"
                                    >
                                        Complete
                                    </button>

                                    <!-- Cancel action -->
                                    <button
                                        v-if="appt.status !== 'completed' && appt.status !== 'cancelled'"
                                        @click="updateStatus(appt.id, 'cancelled')"
                                        type="button"
                                        class="px-2.5 py-1.5 rounded-full border border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-300 font-medium text-xs transition-colors cursor-pointer"
                                        title="Cancel appointment"
                                    >
                                        ✕
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="appointments.length === 0">
                                <td colspan="7" class="py-12 text-center text-slate-400 text-sm">
                                    No patient visits registered for today yet. Use "+ Add Walk-in" to log a patient arrival.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Fast Walk-In Entry Modal -->
        <div
            v-if="showWalkInModal"
            class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
        >
            <div class="bg-white rounded-[32px] max-w-lg w-full shadow-2xl border border-white/80 overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                <div class="p-6 bg-gradient-to-r from-brand-teal to-cyan-700 text-white flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold">Fast Walk-in Registration</h3>
                        <p class="text-xs text-cyan-100">Instantly logs arrival and places patient in waiting lobby</p>
                    </div>
                    <button @click="showWalkInModal = false" class="text-white/80 hover:text-white text-xl font-bold cursor-pointer">✕</button>
                </div>

                <form @submit.prevent="submitWalkIn" class="p-6 space-y-4 text-sm">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Doctor</label>
                            <select v-model="walkInForm.doctor_id" class="w-full rounded-xl border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 text-xs py-2" required>
                                <option v-for="d in doctors" :key="d.id" :value="d.id">{{ d.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Service Type</label>
                            <select v-model="walkInForm.service_id" class="w-full rounded-xl border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 text-xs py-2" required>
                                <option v-for="s in services" :key="s.id" :value="s.id">{{ s.name }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div class="col-span-2">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Patient Full Name</label>
                            <input v-model="walkInForm.patient_name" type="text" placeholder="Halimbawa: Rodrigo Bautista" class="w-full rounded-xl border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 text-xs py-2" required />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Age</label>
                            <input v-model="walkInForm.patient_age" type="number" placeholder="42" min="0" class="w-full rounded-xl border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 text-xs py-2" required />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Phone Number</label>
                        <input v-model="walkInForm.patient_phone" type="tel" placeholder="+63 917 555 9876" class="w-full rounded-xl border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 text-xs py-2" required />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Chief Complaint</label>
                        <textarea v-model="walkInForm.chief_complaint" rows="2" placeholder="Halimbawa: Ubo at sipon nang 3 araw..." class="w-full rounded-xl border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 text-xs py-2" required></textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button
                            type="button"
                            @click="showWalkInModal = false"
                            class="px-4 py-2 rounded-full text-xs font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="walkInForm.processing"
                            class="px-6 py-2.5 bg-brand-teal hover:bg-brand-600 text-white text-xs font-bold rounded-full shadow-md shadow-brand-teal/20 cursor-pointer disabled:opacity-50"
                        >
                            Assign Queue & Check In
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
