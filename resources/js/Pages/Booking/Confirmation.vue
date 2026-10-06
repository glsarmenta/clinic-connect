<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    appointment: Object,
});

const printToken = () => {
    window.print();
};
</script>

<template>
    <Head title="Booking Confirmed - CarePlus Medical" />

    <div class="min-h-screen bg-gradient-to-br from-[#cbeef4]/40 via-[#f0f9fb] to-[#e6f7fa]/60 text-slate-800 py-12 px-4 sm:px-6">
        <div class="max-w-xl mx-auto space-y-6">
            
            <!-- Success Notification Card -->
            <div class="text-center space-y-2">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 shadow-md shadow-emerald-500/15 mb-2">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Booking Confirmed!</h1>
                <p class="text-sm text-slate-600">Your appointment has been registered with the clinic queue.</p>
            </div>

            <!-- Digital Pass Card -->
            <div class="bg-white/95 rounded-[32px] sm:rounded-[36px] shadow-xl shadow-cyan-950/5 border border-white/80 overflow-hidden relative backdrop-blur-md">
                
                <!-- Ticket Header -->
                <div class="bg-gradient-to-r from-brand-teal to-cyan-700 text-white p-6 sm:p-8 text-center relative overflow-hidden">
                    <div class="absolute -right-6 -bottom-6 w-32 h-32 rounded-full bg-white/10 pointer-events-none"></div>
                    <div class="text-xs uppercase tracking-widest font-semibold text-cyan-100">Assigned Daily Queue Token</div>
                    <div class="text-5xl sm:text-6xl font-black tracking-wider my-2 font-mono drop-shadow-sm">
                        {{ appointment.queue_number }}
                    </div>
                    <div class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-semibold bg-white/20 text-white backdrop-blur-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-300 animate-ping"></span>
                        Status: {{ appointment.status?.replace('_', ' ').toUpperCase() }}
                    </div>
                </div>

                <!-- Ticket Body -->
                <div class="p-6 sm:p-8 space-y-6 text-sm divide-y divide-slate-100">
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="block text-xs text-slate-500 uppercase font-semibold">Doctor</span>
                            <span class="font-bold text-slate-900">{{ appointment.doctor?.name }}</span>
                            <span class="block text-xs text-brand-teal font-medium">{{ appointment.doctor?.specialization }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-slate-500 uppercase font-semibold">Service</span>
                            <span class="font-bold text-slate-900">{{ appointment.service?.name }}</span>
                            <span class="block text-xs text-slate-500">₱{{ appointment.service?.price }} ({{ appointment.service?.duration_minutes }}m)</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-4">
                        <div>
                            <span class="block text-xs text-slate-500 uppercase font-semibold">Date</span>
                            <span class="font-bold text-slate-900">{{ appointment.appointment_date }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-slate-500 uppercase font-semibold">Estimated Slot</span>
                            <span class="font-bold text-slate-900">{{ appointment.time_slot }}</span>
                        </div>
                    </div>

                    <div class="pt-4">
                        <span class="block text-xs text-slate-500 uppercase font-semibold">Patient Information</span>
                        <div class="flex items-baseline justify-between mt-1">
                            <span class="font-bold text-slate-900">{{ appointment.patient_name }} (Age {{ appointment.patient_age }})</span>
                            <span class="text-xs text-slate-600">{{ appointment.patient_phone }}</span>
                        </div>
                    </div>

                    <div class="pt-4">
                        <span class="block text-xs text-slate-500 uppercase font-semibold">Chief Complaint</span>
                        <p class="text-slate-700 text-xs mt-1 bg-arctic-50 p-3 rounded-2xl border border-cyan-100/60">
                            {{ appointment.chief_complaint }}
                        </p>
                    </div>

                    <div class="pt-4 text-xs text-slate-500 flex items-center justify-between">
                        <div>
                            <span class="font-medium text-slate-700">{{ appointment.clinic?.name }}</span>
                            <span class="block text-[11px] text-slate-400">{{ appointment.clinic?.address }}</span>
                        </div>
                        <div class="text-right">
                            <span class="font-semibold text-slate-700">{{ appointment.clinic?.phone }}</span>
                        </div>
                    </div>

                </div>

                <!-- Card Footer Actions -->
                <div class="bg-arctic-50/70 p-4 sm:p-6 border-t border-cyan-100/60 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <button
                        @click="printToken"
                        type="button"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-5 py-2.5 rounded-full border border-slate-200 bg-white text-slate-700 font-semibold hover:bg-slate-50 transition-colors text-xs cursor-pointer shadow-xs"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Print / Save Pass
                    </button>

                    <Link
                        :href="route('booking.create')"
                        class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2.5 bg-brand-teal hover:bg-brand-600 text-white font-bold rounded-full shadow-md shadow-brand-teal/20 text-xs transition-colors"
                    >
                        Book Another Appointment &rarr;
                    </Link>
                </div>
            </div>

            <div class="text-center">
                <Link :href="route('home')" class="text-xs text-slate-500 hover:text-brand-600 transition-colors">
                    &larr; Back to Clinic Homepage
                </Link>
            </div>
        </div>
    </div>
</template>
