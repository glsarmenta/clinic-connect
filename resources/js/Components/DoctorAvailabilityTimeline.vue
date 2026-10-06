<script setup>
import { ref, computed, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    doctors: {
        type: Array,
        default: () => [],
    },
    doctor: {
        type: Object,
        default: null,
    },
    submitRoute: {
        type: String,
        default: null,
    },
    isWorkspaceView: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();
const saving = ref(false);

const dayNames = [
    { day_of_week: 0, name: 'Sunday', short: 'Sun' },
    { day_of_week: 1, name: 'Monday', short: 'Mon' },
    { day_of_week: 2, name: 'Tuesday', short: 'Tue' },
    { day_of_week: 3, name: 'Wednesday', short: 'Wed' },
    { day_of_week: 4, name: 'Thursday', short: 'Thu' },
    { day_of_week: 5, name: 'Friday', short: 'Fri' },
    { day_of_week: 6, name: 'Saturday', short: 'Sat' },
];

// Active doctor selection
const selectedDoctorId = ref(
    props.doctor?.id || (props.doctors?.length > 0 ? props.doctors[0].id : null)
);

const currentDoctor = computed(() => {
    if (props.doctor && (!props.doctors || props.doctors.length === 0)) {
        return props.doctor;
    }
    return props.doctors.find(d => d.id === Number(selectedDoctorId.value)) || props.doctor || null;
});

// Weekly schedule state (0 to 6)
const schedule = ref([]);

const initScheduleFromDoctor = (doc) => {
    if (!doc) {
        schedule.value = dayNames.map(d => ({
            day_of_week: d.day_of_week,
            name: d.name,
            short: d.short,
            is_available: d.day_of_week >= 1 && d.day_of_week <= 5,
            slots: d.day_of_week >= 1 && d.day_of_week <= 5
                ? [
                    { start_time: '08:00', end_time: '12:00' },
                    { start_time: '13:00', end_time: '17:00' },
                ]
                : d.day_of_week === 6
                ? [{ start_time: '09:00', end_time: '13:00' }]
                : [],
        }));
        return;
    }

    const availabilities = doc.availabilities || [];

    schedule.value = dayNames.map(d => {
        const dayAvailabilities = availabilities.filter(
            a => Number(a.day_of_week) === d.day_of_week && a.is_available
        );

        const isAvailable = dayAvailabilities.length > 0;
        const slots = dayAvailabilities.map(a => ({
            start_time: (a.start_time || '08:00').substring(0, 5),
            end_time: (a.end_time || '17:00').substring(0, 5),
        }));

        return {
            day_of_week: d.day_of_week,
            name: d.name,
            short: d.short,
            is_available: isAvailable,
            slots: slots.length > 0 ? slots : (isAvailable ? [{ start_time: '08:00', end_time: '17:00' }] : []),
        };
    });
};

watch(
    () => currentDoctor.value,
    (newDoc) => {
        initScheduleFromDoctor(newDoc);
    },
    { immediate: true, deep: true }
);

// Convert HH:mm to minutes from midnight
const timeToMinutes = (timeStr) => {
    if (!timeStr) return 0;
    const [h, m] = timeStr.split(':').map(Number);
    return (h || 0) * 60 + (m || 0);
};

// Calculate duration in hours between two HH:mm strings
const calculateSlotHours = (start, end) => {
    const diff = timeToMinutes(end) - timeToMinutes(start);
    return Math.max(0, diff / 60);
};

// Calculate total hours for a day
const getDayTotalHours = (day) => {
    if (!day.is_available || !day.slots || day.slots.length === 0) return 0;
    return day.slots.reduce((sum, slot) => sum + calculateSlotHours(slot.start_time, slot.end_time), 0);
};

// Total weekly hours planned
const totalWeeklyHours = computed(() => {
    return schedule.value.reduce((sum, day) => sum + getDayTotalHours(day), 0);
});

// Active working days count
const activeDaysCount = computed(() => {
    return schedule.value.filter(d => d.is_available && d.slots.length > 0).length;
});

// Timeline track visual calculation (Span: 7:00 AM to 8:00 PM -> 13 hours = 780 minutes)
const timelineStartHour = 7;
const timelineEndHour = 20;
const timelineTotalMinutes = (timelineEndHour - timelineStartHour) * 60;

const getSlotStyle = (slot) => {
    const startMin = timeToMinutes(slot.start_time);
    const endMin = timeToMinutes(slot.end_time);

    const leftMin = Math.max(0, startMin - timelineStartHour * 60);
    const rightMin = Math.min(timelineTotalMinutes, endMin - timelineStartHour * 60);
    const durationMin = Math.max(0, rightMin - leftMin);

    const leftPercent = (leftMin / timelineTotalMinutes) * 100;
    const widthPercent = (durationMin / timelineTotalMinutes) * 100;

    return {
        left: `${leftPercent}%`,
        width: `${widthPercent}%`,
    };
};

const formatTime12h = (timeStr) => {
    if (!timeStr) return '';
    const [h, m] = timeStr.split(':').map(Number);
    const period = h >= 12 ? 'PM' : 'AM';
    const hour12 = h % 12 || 12;
    return `${hour12}:${m < 10 ? '0' + m : m} ${period}`;
};

// Actions
const toggleDay = (dayIndex) => {
    const day = schedule.value[dayIndex];
    day.is_available = !day.is_available;
    if (day.is_available && day.slots.length === 0) {
        day.slots.push({ start_time: '08:00', end_time: '12:00' });
        day.slots.push({ start_time: '13:00', end_time: '17:00' });
    }
};

const addSlot = (dayIndex) => {
    const day = schedule.value[dayIndex];
    day.is_available = true;
    if (day.slots.length === 0) {
        day.slots.push({ start_time: '08:00', end_time: '12:00' });
    } else if (day.slots.length === 1) {
        day.slots.push({ start_time: '13:00', end_time: '17:00' });
    } else {
        day.slots.push({ start_time: '18:00', end_time: '20:00' });
    }
};

const removeSlot = (dayIndex, slotIndex) => {
    const day = schedule.value[dayIndex];
    day.slots.splice(slotIndex, 1);
    if (day.slots.length === 0) {
        day.is_available = false;
    }
};

// Preset applications
const applyDayPreset = (dayIndex, preset) => {
    const day = schedule.value[dayIndex];
    if (preset === 'fullday') {
        day.is_available = true;
        day.slots = [
            { start_time: '08:00', end_time: '12:00' },
            { start_time: '13:00', end_time: '17:00' },
        ];
    } else if (preset === 'morning') {
        day.is_available = true;
        day.slots = [{ start_time: '08:00', end_time: '12:00' }];
    } else if (preset === 'afternoon') {
        day.is_available = true;
        day.slots = [{ start_time: '13:00', end_time: '17:00' }];
    } else if (preset === 'evening') {
        day.is_available = true;
        day.slots = [{ start_time: '17:00', end_time: '20:00' }];
    } else if (preset === 'off') {
        day.is_available = false;
        day.slots = [];
    }
};

const applyGlobalPreset = (type) => {
    if (type === 'standard_weekday') {
        // Mon - Fri: 8-12, 1-5; Sat: 9-1; Sun: Off
        schedule.value.forEach(d => {
            if (d.day_of_week >= 1 && d.day_of_week <= 5) {
                d.is_available = true;
                d.slots = [
                    { start_time: '08:00', end_time: '12:00' },
                    { start_time: '13:00', end_time: '17:00' },
                ];
            } else if (d.day_of_week === 6) {
                d.is_available = true;
                d.slots = [{ start_time: '09:00', end_time: '13:00' }];
            } else {
                d.is_available = false;
                d.slots = [];
            }
        });
    } else if (type === 'morning_all') {
        schedule.value.forEach(d => {
            if (d.day_of_week >= 1 && d.day_of_week <= 6) {
                d.is_available = true;
                d.slots = [{ start_time: '08:00', end_time: '13:00' }];
            } else {
                d.is_available = false;
                d.slots = [];
            }
        });
    } else if (type === 'copy_monday') {
        const monday = schedule.value.find(d => d.day_of_week === 1);
        if (monday) {
            schedule.value.forEach(d => {
                if (d.day_of_week >= 2 && d.day_of_week <= 5) {
                    d.is_available = monday.is_available;
                    d.slots = monday.slots.map(s => ({ ...s }));
                }
            });
        }
    } else if (type === 'clear_all') {
        schedule.value.forEach(d => {
            d.is_available = false;
            d.slots = [];
        });
    }
};

const saveSchedule = () => {
    if (!currentDoctor.value) return;

    saving.value = true;
    const targetRoute = props.submitRoute || (props.isWorkspaceView ? route('doctor.availability.update') : route('doctor.clinic-settings.availabilities'));

    router.post(
        targetRoute,
        {
            doctor_id: currentDoctor.value.id,
            schedule: schedule.value.map(d => ({
                day_of_week: d.day_of_week,
                is_available: d.is_available,
                slots: d.slots,
            })),
        },
        {
            preserveScroll: true,
            onFinish: () => {
                saving.value = false;
            },
        }
    );
};
</script>

<template>
    <div class="space-y-6">
        <!-- Header & Doctor Selector -->
        <div class="bg-gradient-to-r from-teal-900 via-brand-teal to-cyan-800 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-cyan-950/10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 backdrop-blur-xs text-teal-100 text-xs font-semibold border border-white/20">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Weekly Practice Timeline Plotter</span>
                </div>
                <h3 class="text-xl sm:text-2xl font-extrabold tracking-tight">
                    Doctor Availability & Working Hours
                </h3>
                <p class="text-xs sm:text-sm text-teal-100 max-w-2xl leading-relaxed">
                    Plot the exact days and timeline blocks when physicians accept online and walk-in clinic consultations. Live slots on the patient booking portal will automatically sync with this schedule.
                </p>
            </div>

            <!-- Stats Badge -->
            <div class="flex items-center gap-3 bg-white/10 backdrop-blur-md p-4 rounded-2xl border border-white/20 shrink-0">
                <div class="text-center px-3 border-r border-white/20">
                    <div class="text-2xl font-black text-white leading-none">{{ activeDaysCount }}</div>
                    <div class="text-[10px] text-teal-200 font-bold uppercase mt-1">Active Days</div>
                </div>
                <div class="text-center px-3">
                    <div class="text-2xl font-black text-emerald-300 leading-none">{{ totalWeeklyHours }}h</div>
                    <div class="text-[10px] text-teal-200 font-bold uppercase mt-1">Weekly Hours</div>
                </div>
            </div>
        </div>

        <!-- Doctor Selector (if in multi-doctor mode) -->
        <div v-if="!isWorkspaceView && doctors && doctors.length > 1" class="bg-slate-50 border border-slate-200/80 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                <span>👨‍⚕️ Select Physician to Schedule:</span>
            </div>
            <div class="flex flex-wrap gap-2">
                <button
                    type="button"
                    v-for="doc in doctors"
                    :key="doc.id"
                    @click="selectedDoctorId = doc.id"
                    :class="selectedDoctorId === doc.id ? 'bg-brand-teal text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-2"
                >
                    <span>{{ doc.name }}</span>
                    <span class="text-[10px] opacity-80">({{ doc.specialization }})</span>
                </button>
            </div>
        </div>

        <!-- Quick Bulk Presets Bar -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs flex flex-wrap items-center justify-between gap-3">
            <div class="text-xs font-bold text-slate-600 flex items-center gap-1.5">
                <span>⚡ Quick Schedule Templates:</span>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    @click="applyGlobalPreset('standard_weekday')"
                    class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-teal-50 hover:text-brand-teal text-slate-700 text-xs font-semibold transition-all border border-slate-200 cursor-pointer"
                >
                    Standard Clinic (Mon–Fri 8–5, Sat 9–1)
                </button>
                <button
                    type="button"
                    @click="applyGlobalPreset('morning_all')"
                    class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-teal-50 hover:text-brand-teal text-slate-700 text-xs font-semibold transition-all border border-slate-200 cursor-pointer"
                >
                    Morning Shift (Mon–Sat 8am–1pm)
                </button>
                <button
                    type="button"
                    @click="applyGlobalPreset('copy_monday')"
                    class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-teal-50 hover:text-brand-teal text-slate-700 text-xs font-semibold transition-all border border-slate-200 cursor-pointer"
                >
                    📋 Copy Monday to All Weekdays
                </button>
                <button
                    type="button"
                    @click="applyGlobalPreset('clear_all')"
                    class="px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold transition-all border border-rose-200 cursor-pointer"
                >
                    Clear All
                </button>
            </div>
        </div>

        <!-- Visual Timeline Hour Markers Legend -->
        <div class="hidden md:block bg-slate-100/80 rounded-2xl p-3 border border-slate-200">
            <div class="flex items-center text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1 px-32">
                <span>Visual Timeline Spectrum (7:00 AM – 8:00 PM)</span>
            </div>
            <div class="relative h-6 flex items-center ml-32 mr-8">
                <div
                    v-for="h in [7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20]"
                    :key="h"
                    class="absolute text-[10px] font-mono text-slate-400 -translate-x-1/2"
                    :style="{ left: `${((h - 7) / 13) * 100}%` }"
                >
                    {{ h % 12 || 12 }}{{ h >= 12 ? 'p' : 'a' }}
                </div>
            </div>
        </div>

        <!-- 7-Day Interactive Plotter List -->
        <div class="space-y-4">
            <div
                v-for="(day, dayIdx) in schedule"
                :key="day.day_of_week"
                :class="day.is_available ? 'bg-white border-slate-200/90 shadow-xs' : 'bg-slate-50/70 border-slate-200/60 opacity-85'"
                class="rounded-3xl border p-5 sm:p-6 transition-all space-y-4 hover:border-brand-teal/40"
            >
                <!-- Day Header Row -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <!-- Toggle switch -->
                        <button
                            type="button"
                            @click="toggleDay(dayIdx)"
                            :class="day.is_available ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-500'"
                            class="w-12 h-6 rounded-full transition-colors relative cursor-pointer focus:outline-none shrink-0"
                        >
                            <span
                                :class="day.is_available ? 'translate-x-6 bg-white' : 'translate-x-1 bg-white'"
                                class="inline-block w-4 h-4 rounded-full transition-transform shadow-xs"
                            ></span>
                        </button>

                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm sm:text-base font-extrabold text-slate-900">{{ day.name }}</span>
                                <span
                                    v-if="day.is_available"
                                    class="text-[11px] px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold border border-emerald-300"
                                >
                                    {{ getDayTotalHours(day) }} hrs planned
                                </span>
                                <span
                                    v-else
                                    class="text-[11px] px-2.5 py-0.5 rounded-full bg-slate-200 text-slate-600 font-semibold"
                                >
                                    Day Off
                                </span>
                            </div>
                            <div class="text-xs text-slate-500 mt-0.5">
                                <span v-if="day.is_available && day.slots.length > 0">
                                    {{ day.slots.map(s => `${formatTime12h(s.start_time)} – ${formatTime12h(s.end_time)}`).join('  •  ') }}
                                </span>
                                <span v-else>No consultations scheduled</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Day Presets & Add Block -->
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="text-[10px] text-slate-400 font-bold uppercase mr-1">Presets:</span>
                        <button
                            type="button"
                            @click="applyDayPreset(dayIdx, 'fullday')"
                            class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-teal-50 hover:text-brand-teal text-slate-600 text-[11px] font-semibold transition-colors cursor-pointer"
                        >
                            Full Day
                        </button>
                        <button
                            type="button"
                            @click="applyDayPreset(dayIdx, 'morning')"
                            class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-teal-50 hover:text-brand-teal text-slate-600 text-[11px] font-semibold transition-colors cursor-pointer"
                        >
                            Morning
                        </button>
                        <button
                            type="button"
                            @click="applyDayPreset(dayIdx, 'afternoon')"
                            class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-teal-50 hover:text-brand-teal text-slate-600 text-[11px] font-semibold transition-colors cursor-pointer"
                        >
                            Afternoon
                        </button>
                        <button
                            type="button"
                            @click="applyDayPreset(dayIdx, 'off')"
                            class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-500 text-[11px] font-semibold transition-colors cursor-pointer"
                        >
                            Off
                        </button>
                        <button
                            type="button"
                            @click="addSlot(dayIdx)"
                            class="px-3 py-1 rounded-lg bg-brand-50 hover:bg-brand-100 text-brand-teal text-[11px] font-bold transition-colors cursor-pointer border border-brand-200/60 ml-2 flex items-center gap-1"
                        >
                            <span>+ Add Slot</span>
                        </button>
                    </div>
                </div>

                <!-- Visual Timeline Strip Representation -->
                <div class="relative bg-slate-100/90 rounded-2xl h-9 overflow-hidden border border-slate-200/80">
                    <div v-if="day.is_available && day.slots.length > 0" class="h-full relative w-full">
                        <!-- Plotted Time Blocks -->
                        <div
                            v-for="(slot, sIdx) in day.slots"
                            :key="sIdx"
                            :style="getSlotStyle(slot)"
                            class="absolute top-1 bottom-1 rounded-xl bg-gradient-to-r from-brand-teal via-teal-600 to-cyan-600 text-white flex items-center justify-center text-[10px] font-bold shadow-xs px-2 truncate transition-all border border-white/20"
                            :title="`${formatTime12h(slot.start_time)} – ${formatTime12h(slot.end_time)} (${calculateSlotHours(slot.start_time, slot.end_time)}h)`"
                        >
                            <span class="truncate">{{ formatTime12h(slot.start_time) }} – {{ formatTime12h(slot.end_time) }}</span>
                        </div>
                    </div>
                    <div v-else class="h-full flex items-center justify-center text-[11px] text-slate-400 font-semibold italic bg-stripes">
                        <span>Clinic Closed / Day Off</span>
                    </div>
                </div>

                <!-- Time Slot Editors (if available) -->
                <div v-if="day.is_available && day.slots.length > 0" class="pt-3 border-t border-slate-100 space-y-2.5">
                    <div class="text-[11px] font-bold text-slate-600 uppercase tracking-wider flex items-center justify-between">
                        <span>Configure Time Windows:</span>
                        <span class="text-[10px] text-slate-400 font-normal lowercase">({{ day.slots.length }} window{{ day.slots.length > 1 ? 's' : '' }} configured)</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                        <div
                            v-for="(slot, sIdx) in day.slots"
                            :key="sIdx"
                            class="bg-slate-50/90 rounded-2xl border border-slate-200/90 p-3 sm:p-3.5 flex items-center justify-between gap-2.5 shadow-2xs hover:border-brand-teal/40 transition-all min-w-0"
                        >
                            <div class="flex items-center gap-2 sm:gap-2.5 flex-wrap min-w-0">
                                <span class="w-6 h-6 rounded-lg bg-teal-100 text-teal-800 font-bold text-[11px] flex items-center justify-center shrink-0">
                                    #{{ sIdx + 1 }}
                                </span>
                                <input
                                    type="time"
                                    v-model="slot.start_time"
                                    class="w-28 sm:w-32 rounded-xl border-slate-300 text-xs py-1.5 px-2.5 font-medium text-slate-800 focus:ring-teal-500 focus:border-teal-500 bg-white shadow-2xs"
                                    required
                                />
                                <span class="text-xs text-slate-400 font-semibold shrink-0">to</span>
                                <input
                                    type="time"
                                    v-model="slot.end_time"
                                    class="w-28 sm:w-32 rounded-xl border-slate-300 text-xs py-1.5 px-2.5 font-medium text-slate-800 focus:ring-teal-500 focus:border-teal-500 bg-white shadow-2xs"
                                    required
                                />
                                <span class="text-[10px] font-semibold text-teal-700 bg-teal-50 border border-teal-200/80 px-2 py-0.5 rounded-md shrink-0 hidden lg:inline-block">
                                    {{ calculateSlotHours(slot.start_time, slot.end_time) }}h
                                </span>
                            </div>

                            <button
                                type="button"
                                @click="removeSlot(dayIdx, sIdx)"
                                class="w-7 h-7 rounded-xl bg-white hover:bg-rose-50 text-slate-400 hover:text-rose-600 border border-slate-200 flex items-center justify-center text-xs font-bold transition-colors cursor-pointer shrink-0 ml-auto"
                                title="Remove time slot"
                            >
                                ✕
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Save Button Footer -->
        <div class="sticky bottom-4 z-20 bg-white/95 backdrop-blur-md rounded-3xl border border-slate-200/90 p-4 sm:p-6 shadow-xl flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-xs text-slate-600">
                <span class="font-bold text-slate-900">Total Planned Schedule:</span>
                <span class="ml-1 text-teal-800 font-bold">{{ activeDaysCount }} Active Day(s)</span> •
                <span class="ml-1 text-teal-800 font-bold">{{ totalWeeklyHours }} Total Hours / week</span>
            </div>

            <button
                type="button"
                @click="saveSchedule"
                :disabled="saving"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 bg-brand-teal hover:bg-brand-600 active:bg-brand-700 text-white font-bold rounded-full shadow-lg shadow-brand-teal/25 transition-all text-sm disabled:opacity-50 cursor-pointer"
            >
                <svg v-if="saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span>{{ saving ? 'Saving Schedule...' : 'Save Doctor Schedule & Update Booking Portal' }}</span>
            </button>
        </div>
    </div>
</template>

<style scoped>
.bg-stripes {
    background-image: repeating-linear-gradient(
        45deg,
        rgba(203, 213, 225, 0.25),
        rgba(203, 213, 225, 0.25) 10px,
        rgba(241, 245, 249, 0.5) 10px,
        rgba(241, 245, 249, 0.5) 20px
    );
}
</style>
