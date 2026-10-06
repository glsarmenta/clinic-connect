<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, onMounted } from 'vue';

const props = defineProps({
    clinic: Object,
    doctors: Array,
    services: Array,
    authUser: Object,
});

onMounted(() => {
    if (props.clinic?.logo_url) {
        let link = document.querySelector("link[rel~='icon']");
        if (!link) {
            link = document.createElement('link');
            link.rel = 'icon';
            document.getElementsByTagName('head')[0].appendChild(link);
        }
        link.href = props.clinic.logo_url;
    }
});

const mobileMenuOpen = ref(false);

const clinicName = computed(() => props.clinic?.name || 'CarePlus Medical Clinic');
const clinicTagline = computed(() => props.clinic?.tagline || 'Compassionate Care for You and Your Family');
const clinicAbout = computed(() => props.clinic?.about || 'Metro Manila Family & Pediatric Clinic is dedicated to providing compassionate, evidence-based medical care for families and individuals. Founded by leading medical practitioners, our clinic integrates modern diagnostic facilities, child-friendly pediatric environments, and a frictionless digital queue system to eliminate waiting room stress and prioritize your health.');
const clinicAddress = computed(() => props.clinic?.address || 'Unit 302 Medical Arts Tower, Ortigas Center, Pasig City, Metro Manila');
const clinicPhone = computed(() => props.clinic?.phone || '+63 (02) 8876-5432');
const clinicEmergencyPhone = computed(() => props.clinic?.emergency_phone || '+63 917 911 2273');
const clinicEmail = computed(() => props.clinic?.email || 'reception@metromanilaclinic.ph');
const operatingDays = computed(() => props.clinic?.operating_days || 'Lunes hanggang Sabado (Mon – Sat)');
const openTime = computed(() => props.clinic?.open_time ? props.clinic.open_time.substring(0, 5) : '08:00');
const closeTime = computed(() => props.clinic?.close_time ? props.clinic.close_time.substring(0, 5) : '17:00');

// Hero Quick Booking Bar reactive state
const selectedDepartment = ref(props.services.length > 0 ? props.services[0].id : '');
const selectedDoctorId = ref(props.doctors.length > 0 ? props.doctors[0].id : '');
const selectedDate = ref(new Date().toISOString().split('T')[0]);

const handleHeroQuickBook = () => {
    router.visit(route('booking.create'), {
        data: {
            service_id: selectedDepartment.value,
            doctor_id: selectedDoctorId.value,
            date: selectedDate.value,
        }
    });
};

const handleResetPatientData = () => {
    if (confirm('Are you sure you want to clear all patient demo queues, appointments, and medical data for a clean slate demo?')) {
        router.post(route('demo.reset-patients-public'), {}, {
            preserveScroll: true,
        });
    }
};

const handleGeneratePatientData = () => {
    router.post(route('demo.generate-patients-public'), {}, {
        preserveScroll: true,
    });
};

const mapEmbedUrl = computed(() => {
    if (props.clinic?.google_map_embed_url) {
        if (props.clinic.google_map_embed_url.includes('src="')) {
            const match = props.clinic.google_map_embed_url.match(/src=["']([^"']+)["']/);
            if (match) return match[1];
        }
        return props.clinic.google_map_embed_url;
    }
    const query = encodeURIComponent(clinicAddress.value || clinicName.value);
    return `https://maps.google.com/maps?q=${query}&t=&z=15&ie=UTF8&iwloc=&output=embed`;
});

const googleMapsDirectionUrl = computed(() => {
    if (props.clinic?.google_map_url) {
        return props.clinic.google_map_url;
    }
    return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(clinicAddress.value)}`;
});

const inClinicDoctorsCount = computed(() => {
    return props.doctors.filter(d => d.live_status === 'in_clinic').length;
});

// Curated Departments matching the screenshot
const departments = [
    {
        id: 'emergency',
        title: 'Emergency Care',
        tag: '24/7 Hotline & Triage',
        iconBg: 'bg-[#e0f7fa]',
        iconColor: 'text-[#00a3b4]',
        iconType: 'ambulance'
    },
    {
        id: 'pediatric',
        title: 'Pediatric Department',
        tag: 'Infant & Child Health',
        iconBg: 'bg-[#e6f4ea]',
        iconColor: 'text-[#00a3b4]',
        iconType: 'pediatric'
    },
    {
        id: 'cardiology',
        title: 'Cardiology & Vitals',
        tag: 'ECG & BP Monitoring',
        iconBg: 'bg-[#e0f2fe]',
        iconColor: 'text-[#0284c7]',
        iconType: 'cardiology'
    },
    {
        id: 'family',
        title: 'Family Medicine',
        tag: 'Primary Consultations',
        iconBg: 'bg-[#f0fdf4]',
        iconColor: 'text-[#0d9488]',
        iconType: 'family'
    }
];
</script>

<template>
    <Head :title="`${clinicName} - CarePlus Health Portal`">
        <link v-if="clinic?.logo_url" rel="icon" :href="clinic.logo_url" />
    </Head>

    <div class="min-h-screen bg-[#f0f8fa] text-slate-800 font-sans antialiased selection:bg-[#00a3b4] selection:text-white">
        
        <!-- Top Prototype Demo Switcher Bar -->
        <div class="bg-slate-900 text-white text-xs py-2 px-4 border-b border-slate-800 sticky top-0 z-50">
            <div class="max-w-6xl mx-auto flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="font-bold text-slate-200">Interactive MVP Demo:</span>
                    <span class="text-slate-400 hidden sm:inline">Role Navigation</span>
                </div>
                <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap">
                    <Link :href="route('home')" class="px-2.5 py-1 rounded-lg bg-[#00a3b4]/30 text-teal-300 font-bold border border-teal-500/40 text-[11px]">
                        🏠 Clinic Homepage
                    </Link>
                    <Link :href="route('booking.create')" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors text-[11px]">
                        1. Patient Booking
                    </Link>
                    <a href="/demo-switch/secretary" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors text-[11px]">
                        2. Secretary Queue
                    </a>
                    <a href="/demo-switch/doctor1" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors text-[11px]">
                        3. Doctor Workspace
                    </a>
                    <a href="/demo-switch/patient" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 font-medium text-[11px] transition-colors">
                        4. Patient Portal
                    </a>
                    <Link
                        v-if="authUser?.roles?.includes('Doctor') || authUser?.roles?.includes('Admin')"
                        :href="route('doctor.clinic-settings.edit')"
                        class="px-2.5 py-1 rounded-lg bg-amber-500/20 text-amber-300 font-bold border border-amber-500/40 text-[11px] hover:bg-amber-500/30 transition-colors"
                    >
                        ⚙️ Edit Clinic Details
                    </Link>
                    <div class="h-4 w-px bg-slate-700 mx-1 hidden md:block"></div>
                    <button
                        type="button"
                        @click="handleResetPatientData"
                        class="px-2.5 py-1 rounded-lg bg-rose-900/60 hover:bg-rose-800 text-rose-200 border border-rose-700/50 text-[11px] font-bold cursor-pointer transition-colors"
                        title="Clear all patient queues and medical data for a clean slate demo"
                    >
                        🗑️ Reset Demo Data
                    </button>
                    <button
                        type="button"
                        @click="handleGeneratePatientData"
                        class="px-2.5 py-1 rounded-lg bg-emerald-900/60 hover:bg-emerald-800 text-emerald-200 border border-emerald-700/50 text-[11px] font-bold cursor-pointer transition-colors"
                        title="Regenerate sample patient queues, medical records & vaccine reminders"
                    >
                        🔄 Generate Demo Data
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Wrapper Container with Light Gradient Backing -->
        <div class="max-w-6xl mx-auto px-3 sm:px-6 pt-4 sm:pt-6 pb-16 space-y-12 sm:space-y-16">

            <!-- Hero Section & Navigation Card (Matching Screenshot Frame) -->
            <section class="rounded-[32px] sm:rounded-[40px] bg-gradient-to-b from-[#cbeef4] via-[#ddf4f7] to-[#bcecf3] p-4 sm:p-8 md:p-10 shadow-[0_20px_60px_rgba(0,163,180,0.14)] border border-white/80 relative overflow-hidden">
                
                <!-- Background Medical Elements Overlay -->
                <div class="absolute inset-0 pointer-events-none overflow-hidden opacity-30">
                    <div class="absolute -top-20 -right-20 w-96 h-96 bg-white/60 rounded-full blur-2xl"></div>
                    <div class="absolute bottom-0 left-10 w-80 h-80 bg-teal-100/50 rounded-full blur-2xl"></div>
                </div>

                <!-- Floating Frosted Navigation Bar (Matching Screenshot) -->
                <header class="relative z-20 rounded-full bg-white/75 backdrop-blur-md px-4 sm:px-6 py-2.5 sm:py-3 border border-white/80 shadow-xs flex items-center justify-between gap-2">
                    <!-- Brand & Logo -->
                    <Link :href="route('home')" class="flex items-center gap-2.5 group">
                        <img
                            v-if="clinic?.logo_url"
                            :src="clinic.logo_url"
                            :alt="clinicName"
                            class="h-8 w-8 object-contain rounded-full shadow-xs"
                        />
                        <div
                            v-else
                            class="h-8 w-8 rounded-full bg-[#00a3b4] text-white flex items-center justify-center font-black text-sm shadow-sm"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs sm:text-sm font-extrabold text-slate-900 uppercase tracking-tight leading-tight">
                                {{ clinic?.name ? clinic.name.toUpperCase() : 'CAREPLUS MEDICAL' }}
                            </span>
                        </div>
                    </Link>

                    <!-- Desktop Nav Links (Matching Screenshot) -->
                    <nav class="hidden md:flex items-center space-x-6 text-xs sm:text-sm font-semibold text-slate-600">
                        <a href="#home" class="text-[#00a3b4] font-bold hover:text-teal-800 transition-colors">Home</a>
                        <a href="#about" class="hover:text-[#00a3b4] transition-colors">About Us</a>
                        <a href="#departments" class="hover:text-[#00a3b4] transition-colors">Departments</a>
                        <a href="#services" class="hover:text-[#00a3b4] transition-colors">Services</a>
                        <a href="#doctors" class="hover:text-[#00a3b4] transition-colors">Doctors</a>
                        <a href="#location" class="hover:text-[#00a3b4] transition-colors">Location</a>
                    </nav>

                    <!-- Header CTA Button -->
                    <div class="flex items-center gap-2">
                        <Link
                            :href="route('booking.create')"
                            class="inline-flex items-center justify-center px-4 sm:px-5 py-2 rounded-full bg-[#00a3b4] hover:bg-[#008f9e] active:bg-[#007f8c] text-white text-xs sm:text-sm font-bold shadow-sm transition-all hover:scale-105 cursor-pointer"
                        >
                            Book Appointment
                        </Link>
                    </div>
                </header>

                <!-- Hero Body: Typography & Doctor Patient Visual (Matching Screenshot) -->
                <div id="home" class="relative z-10 pt-8 sm:pt-14 pb-6 sm:pb-8 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                    
                    <!-- Left: Hero Headline -->
                    <div class="lg:col-span-6 space-y-4 text-center lg:text-left">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[11px] font-bold bg-white/70 text-[#00808c] border border-white/80 backdrop-blur-xs">
                            <span class="w-2 h-2 rounded-full bg-[#00a3b4] animate-ping"></span>
                            <span>{{ inClinicDoctorsCount }} Specialist(s) In Clinic Today</span>
                        </div>

                        <h1 class="text-3xl sm:text-5xl lg:text-[52px] font-black text-slate-900 tracking-tight leading-[1.1]">
                            Your Health, <br class="hidden sm:inline" />
                            <span class="text-slate-900">Our Priority</span>
                        </h1>

                        <p class="text-sm sm:text-base text-slate-600 font-medium leading-relaxed max-w-md mx-auto lg:mx-0">
                            {{ clinicTagline || 'Compassionate Care for You and Your Family' }}
                        </p>
                    </div>

                    <!-- Right: Compassionate Doctor & Patient Visual (Matching CarePlus Screenshot) -->
                    <div class="lg:col-span-6 flex justify-center lg:justify-end">
                        <div class="relative w-full max-w-md rounded-[32px] overflow-hidden shadow-2xl border-4 border-white/90 aspect-[4/3] group">
                            <img
                                src="/images/hero_doctor_patient.jpg"
                                alt="Compassionate Care Consultation"
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                            />
                            <div class="absolute bottom-3 left-3 right-3 p-3 rounded-2xl bg-white/85 backdrop-blur-md border border-white/90 shadow-sm flex items-center justify-between text-xs">
                                <div>
                                    <div class="font-extrabold text-slate-900">Dr. Santos &amp; Dr. dela Cruz</div>
                                    <div class="text-[11px] text-brand-teal font-medium">Accredited Specialists On Duty</div>
                                </div>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-bold border border-emerald-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    In Clinic
                                </span>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Floating Glassmorphism Quick Appointment Booking Bar (Matching Screenshot) -->
                <div class="relative z-20 mt-4 pt-2">
                    <div class="rounded-2xl sm:rounded-3xl bg-white/55 backdrop-blur-xl border border-white/80 p-4 sm:p-6 shadow-[0_15px_35px_rgba(0,163,180,0.12)]">
                        <form @submit.prevent="handleHeroQuickBook" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 items-end">
                            
                            <!-- Department Selector -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1 pl-1">
                                    Department
                                </label>
                                <select
                                    v-model="selectedDepartment"
                                    class="w-full rounded-xl bg-white/95 border-slate-200/90 text-slate-800 text-xs sm:text-sm py-2.5 px-3 focus:ring-[#00a3b4] focus:border-[#00a3b4] shadow-xs cursor-pointer font-medium"
                                >
                                    <option v-for="s in services" :key="s.id" :value="s.id">
                                        {{ s.name }}
                                    </option>
                                    <option v-if="services.length === 0" value="">General Consultation</option>
                                </select>
                            </div>

                            <!-- Doctor Selector -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1 pl-1">
                                    Doctor
                                </label>
                                <select
                                    v-model="selectedDoctorId"
                                    class="w-full rounded-xl bg-white/95 border-slate-200/90 text-slate-800 text-xs sm:text-sm py-2.5 px-3 focus:ring-[#00a3b4] focus:border-[#00a3b4] shadow-xs cursor-pointer font-medium"
                                >
                                    <option v-for="d in doctors" :key="d.id" :value="d.id">
                                        {{ d.name }}
                                    </option>
                                    <option v-if="doctors.length === 0" value="">Any Available Specialist</option>
                                </select>
                            </div>

                            <!-- Date Picker -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1 pl-1">
                                    Date
                                </label>
                                <div class="relative">
                                    <input
                                        type="date"
                                        v-model="selectedDate"
                                        class="w-full rounded-xl bg-white/95 border-slate-200/90 text-slate-800 text-xs sm:text-sm py-2.5 px-3 focus:ring-[#00a3b4] focus:border-[#00a3b4] shadow-xs font-medium cursor-pointer"
                                    />
                                </div>
                            </div>

                            <!-- Book Now Button -->
                            <div>
                                <button
                                    type="submit"
                                    class="w-full py-2.5 px-6 rounded-xl bg-[#00a3b4] hover:bg-[#008f9e] active:bg-[#007f8c] text-white font-bold text-xs sm:text-sm shadow-md shadow-[#00a3b4]/30 transition-all hover:scale-[1.02] cursor-pointer flex items-center justify-center gap-2"
                                >
                                    <span>Book Now</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </div>

                        </form>
                    </div>
                </div>

            </section>

            <!-- Section 2: About Our Clinic & Story -->
            <section id="about" class="rounded-[32px] sm:rounded-[40px] bg-white p-6 sm:p-10 md:p-12 shadow-[0_15px_45px_rgba(0,163,180,0.07)] border border-slate-100/90 relative overflow-hidden">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                    
                    <!-- Left: Clinical Team & Facility Visual Collage -->
                    <div class="lg:col-span-5 space-y-4">
                        <div class="relative rounded-3xl overflow-hidden shadow-xl border-4 border-[#e0f7fa] aspect-[4/3] group">
                            <img
                                src="/images/hero_doctor_patient.jpg"
                                alt="About Our Clinic"
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                            />
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-transparent to-transparent"></div>
                            <div class="absolute bottom-4 left-4 right-4 text-white">
                                <div class="text-[11px] font-bold text-teal-200 uppercase tracking-wider">Patient-Centered Mission</div>
                                <div class="font-extrabold text-sm sm:text-base">{{ clinicName }}</div>
                            </div>
                        </div>

                        <!-- Quick Feature Badges -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="p-3.5 rounded-2xl bg-[#f0f9fa] border border-[#d2f3f7] flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-[#00a3b4] text-white flex items-center justify-center font-bold text-sm shadow-xs">
                                    ✓
                                </div>
                                <div>
                                    <div class="text-[11px] font-bold text-slate-900 leading-tight">Zero-Wait Queue</div>
                                    <div class="text-[10px] text-slate-500">Live SMS &amp; Token</div>
                                </div>
                            </div>
                            <div class="p-3.5 rounded-2xl bg-[#f0fdf4] border border-[#d1fae5] flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-sm shadow-xs">
                                    ★
                                </div>
                                <div>
                                    <div class="text-[11px] font-bold text-slate-900 leading-tight">Specialist Care</div>
                                    <div class="text-[10px] text-slate-500">Pediatric &amp; Family</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Story, Mission & Director's Statement -->
                    <div class="lg:col-span-7 space-y-6">
                        <div class="space-y-2">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-[#e0f7fa] text-[#00808c]">
                                <span>🏥 About Our Clinic</span>
                            </div>
                            <h2 class="text-2xl sm:text-4xl font-black text-slate-900 tracking-tight leading-snug">
                                Dedicated to Compassionate Healthcare &amp; Clinical Excellence
                            </h2>
                        </div>

                        <!-- Main Clinic Story Description from Settings -->
                        <div class="text-slate-600 text-sm sm:text-base leading-relaxed space-y-4">
                            <p class="font-medium text-slate-700 whitespace-pre-line">
                                {{ clinicAbout }}
                            </p>
                        </div>

                        <!-- Key Pillars Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 border-t border-slate-100">
                            <div class="space-y-1">
                                <div class="text-xl font-black text-[#00a3b4]">100%</div>
                                <div class="text-xs font-bold text-slate-900">Accredited Doctors</div>
                                <div class="text-[11px] text-slate-500">Board certified pediatricians and family physicians.</div>
                            </div>
                            <div class="space-y-1">
                                <div class="text-xl font-black text-[#00a3b4]">Modern</div>
                                <div class="text-xs font-bold text-slate-900">Digital Queue</div>
                                <div class="text-[11px] text-slate-500">Track your consultation slot remotely in real-time.</div>
                            </div>
                            <div class="space-y-1">
                                <div class="text-xl font-black text-[#00a3b4]">Family-First</div>
                                <div class="text-xs font-bold text-slate-900">Holistic Care</div>
                                <div class="text-[11px] text-slate-500">From infant vaccines to annual executive screenings.</div>
                            </div>
                        </div>

                        <!-- Action buttons -->
                        <div class="flex flex-wrap items-center gap-3 pt-2">
                            <Link
                                :href="route('booking.create')"
                                class="inline-flex items-center justify-center px-6 py-3 rounded-full bg-[#00a3b4] hover:bg-[#008f9e] text-white font-bold text-xs sm:text-sm shadow-md shadow-[#00a3b4]/20 transition-all cursor-pointer"
                            >
                                Book Consultation Today &rarr;
                            </Link>

                            <Link
                                v-if="authUser?.roles?.includes('Doctor') || authUser?.roles?.includes('Admin')"
                                :href="route('doctor.clinic-settings.edit')"
                                class="inline-flex items-center justify-center px-5 py-3 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors cursor-pointer"
                            >
                                ⚙️ Edit Clinic Story
                            </Link>
                        </div>
                    </div>

                </div>
            </section>

            <!-- Section 3: Our Departments (Matching Screenshot Clean 3D Rounded Cards) -->
            <section id="departments" class="space-y-6 sm:space-y-8">
                <div class="text-center space-y-1.5">
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                        Our Departments
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 font-medium max-w-md mx-auto">
                        Dedicated specialized departments offering personalized family care
                    </p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
                    
                    <!-- Emergency Care Card -->
                    <div class="rounded-3xl bg-white p-6 sm:p-7 shadow-[0_10px_30px_rgba(0,163,180,0.06)] border border-slate-100/90 flex flex-col items-center justify-center text-center group hover:-translate-y-1.5 transition-all duration-300">
                        <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl overflow-hidden mb-3 group-hover:scale-105 transition-transform">
                            <img src="/images/dept_emergency.jpg" alt="Emergency Care" class="w-full h-full object-contain" />
                        </div>
                        <h3 class="font-bold text-slate-900 text-sm sm:text-base leading-snug">
                            Emergency <br />Care
                        </h3>
                        <span class="text-[10px] text-teal-700 font-semibold bg-teal-50 px-2 py-0.5 rounded-full mt-2">24/7 Hotline</span>
                    </div>

                    <!-- Pediatric Department Card -->
                    <div class="rounded-3xl bg-white p-6 sm:p-7 shadow-[0_10px_30px_rgba(0,163,180,0.06)] border border-slate-100/90 flex flex-col items-center justify-center text-center group hover:-translate-y-1.5 transition-all duration-300">
                        <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl overflow-hidden mb-3 group-hover:scale-105 transition-transform">
                            <img src="/images/dept_pediatric.jpg" alt="Pediatric Department" class="w-full h-full object-contain" />
                        </div>
                        <h3 class="font-bold text-slate-900 text-sm sm:text-base leading-snug">
                            Pediatric <br />Department
                        </h3>
                        <span class="text-[10px] text-emerald-700 font-semibold bg-emerald-50 px-2 py-0.5 rounded-full mt-2">Kids &amp; Babies</span>
                    </div>

                    <!-- Cardiology Card -->
                    <div class="rounded-3xl bg-white p-6 sm:p-7 shadow-[0_10px_30px_rgba(0,163,180,0.06)] border border-slate-100/90 flex flex-col items-center justify-center text-center group hover:-translate-y-1.5 transition-all duration-300">
                        <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl overflow-hidden mb-3 group-hover:scale-105 transition-transform">
                            <img src="/images/dept_cardiology.jpg" alt="Cardiology & Vitals" class="w-full h-full object-contain" />
                        </div>
                        <h3 class="font-bold text-slate-900 text-sm sm:text-base leading-snug">
                            Cardiology &amp; <br />Vitals
                        </h3>
                        <span class="text-[10px] text-sky-700 font-semibold bg-sky-50 px-2 py-0.5 rounded-full mt-2">Heart &amp; BP</span>
                    </div>

                    <!-- Family Medicine Card -->
                    <div class="rounded-3xl bg-white p-6 sm:p-7 shadow-[0_10px_30px_rgba(0,163,180,0.06)] border border-slate-100/90 flex flex-col items-center justify-center text-center group hover:-translate-y-1.5 transition-all duration-300">
                        <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl overflow-hidden mb-3 group-hover:scale-105 transition-transform">
                            <img src="/images/dept_family.jpg" alt="Family Medicine" class="w-full h-full object-contain" />
                        </div>
                        <h3 class="font-bold text-slate-900 text-sm sm:text-base leading-snug">
                            Family <br />Medicine
                        </h3>
                        <span class="text-[10px] text-teal-700 font-semibold bg-teal-50 px-2 py-0.5 rounded-full mt-2">Primary Care</span>
                    </div>

                </div>
            </section>

            <!-- Section 3: Featured Services (Matching Screenshot Clean Card Format) -->
            <section id="services" class="space-y-6 sm:space-y-8">
                <div class="text-center space-y-1.5">
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                        Featured Services
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 font-medium max-w-md mx-auto">
                        High-quality outpatient medical offerings with transparent consultation fees
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    
                    <div
                        v-for="svc in services"
                        :key="svc.id"
                        class="rounded-3xl bg-white p-5 sm:p-6 shadow-[0_10px_30px_rgba(0,163,180,0.06)] border border-slate-100/90 flex flex-col justify-between space-y-4 hover:shadow-xl hover:border-[#00a3b4]/30 transition-all duration-300 group"
                    >
                        <div class="space-y-3">
                            <!-- Service Thumbnail Visual -->
                            <div class="rounded-2xl overflow-hidden aspect-[16/10] bg-slate-100 group-hover:scale-[1.02] transition-transform">
                                <img
                                    v-if="svc.name.includes('Pediatric')"
                                    src="/images/service_pediatric.jpg"
                                    :alt="svc.name"
                                    class="w-full h-full object-cover"
                                />
                                <img
                                    v-else-if="svc.name.includes('Exam') || svc.name.includes('Comprehensive') || svc.name.includes('Diagnostic')"
                                    src="/images/service_diagnostics.jpg"
                                    :alt="svc.name"
                                    class="w-full h-full object-cover"
                                />
                                <img
                                    v-else-if="svc.name.includes('Surgery') || svc.name.includes('Procedure') || svc.name.includes('Specialized')"
                                    src="/images/service_surgery.jpg"
                                    :alt="svc.name"
                                    class="w-full h-full object-cover"
                                />
                                <img
                                    v-else
                                    src="/images/hero_doctor_patient.jpg"
                                    :alt="svc.name"
                                    class="w-full h-full object-cover"
                                />
                            </div>

                            <div class="flex items-center justify-between pt-1">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#e0f7fa] text-[#00808c]">
                                    ⏱️ {{ svc.duration_minutes }} mins
                                </span>
                                <span class="text-lg font-black text-slate-900">
                                    ₱{{ svc.price }}
                                </span>
                            </div>

                            <h3 class="font-bold text-slate-900 text-sm sm:text-base group-hover:text-[#00a3b4] transition-colors">
                                {{ svc.name }}
                            </h3>

                            <p class="text-xs text-slate-500 leading-relaxed line-clamp-2">
                                {{ svc.description }}
                            </p>
                        </div>

                        <Link
                            :href="route('booking.create')"
                            class="w-full text-center py-2.5 rounded-xl bg-[#f0f9fa] hover:bg-[#00a3b4] text-[#00808c] hover:text-white font-bold text-xs transition-all cursor-pointer shadow-xs"
                        >
                            Book This Service &rarr;
                        </Link>
                    </div>

                </div>
            </section>

            <!-- Section 4: Our Specialists (Doctor Profiles & Live Status) -->
            <section id="doctors" class="space-y-6 sm:space-y-8">
                <div class="text-center space-y-1.5">
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                        Meet Our Physicians
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 font-medium max-w-md mx-auto">
                        Accredited physicians and specialist doctors available for consultations
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto">
                    <div
                        v-for="doc in doctors"
                        :key="doc.id"
                        class="rounded-3xl bg-white p-6 sm:p-8 shadow-[0_10px_30px_rgba(0,163,180,0.06)] border border-slate-100/90 space-y-5 flex flex-col justify-between hover:shadow-xl transition-all"
                    >
                        <div class="space-y-4">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3.5">
                                    <img
                                        v-if="doc.name.includes('Cristina') || doc.name.includes('Maria') || doc.specialization?.includes('Pedia')"
                                        src="/images/doctor_cristina.jpg"
                                        :alt="doc.name"
                                        class="w-16 h-16 rounded-2xl object-cover shadow-md border-2 border-white"
                                    />
                                    <img
                                        v-else
                                        src="/images/doctor_jose.jpg"
                                        :alt="doc.name"
                                        class="w-16 h-16 rounded-2xl object-cover shadow-md border-2 border-white"
                                    />
                                    <div>
                                        <h3 class="font-extrabold text-slate-900 text-base sm:text-lg">
                                            {{ doc.name }}
                                        </h3>
                                        <p class="text-xs font-semibold text-[#00808c]">
                                            {{ doc.specialization }}
                                        </p>
                                    </div>
                                </div>

                                <span
                                    :class="{
                                        'bg-emerald-100 text-emerald-800 border-emerald-300': doc.live_status === 'in_clinic',
                                        'bg-amber-100 text-amber-800 border-amber-300': doc.live_status === 'delayed',
                                        'bg-rose-100 text-rose-800 border-rose-300': doc.live_status === 'out_of_clinic'
                                    }"
                                    class="px-2.5 py-1 rounded-full text-[11px] font-bold border capitalize"
                                >
                                    {{ doc.live_status?.replace('_', ' ') || 'In Clinic' }}
                                </span>
                            </div>

                            <p class="text-xs text-slate-600 leading-relaxed">
                                Providing patient-first consultations, pediatric wellness, diagnostic evaluations, and ongoing therapy support.
                            </p>

                            <div class="text-xs text-slate-500 pt-2 border-t border-slate-100 flex items-center justify-between">
                                <span>Desk: {{ doc.phone }}</span>
                                <span class="text-[11px] text-[#00a3b4] font-semibold">{{ operatingDays }}</span>
                            </div>
                        </div>

                        <Link
                            :href="route('booking.create')"
                            class="w-full inline-flex items-center justify-center gap-2 py-2.5 rounded-xl bg-[#00a3b4] hover:bg-[#008f9e] text-white font-bold text-xs sm:text-sm shadow-sm transition-all cursor-pointer"
                        >
                            <span>Book Consultation With {{ doc.name.split(' ')[1] || doc.name }}</span>
                        </Link>
                    </div>
                </div>
            </section>

            <!-- Section 5: Location & Interactive Google Map -->
            <section id="location" class="space-y-6 sm:space-y-8">
                <div class="text-center space-y-1.5">
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                        Location &amp; Clinic Hours
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 font-medium max-w-md mx-auto">
                        Conveniently located with accessible parking and complete clinic facilities
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
                    
                    <!-- Left: Clinic Details Card -->
                    <div class="lg:col-span-5 rounded-3xl bg-white p-6 sm:p-8 shadow-[0_10px_30px_rgba(0,163,180,0.06)] border border-slate-100/90 flex flex-col justify-between space-y-6">
                        <div class="space-y-5">
                            <div>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-[#00808c] bg-[#e0f7fa] px-2.5 py-0.5 rounded-full">
                                    Physical Address
                                </span>
                                <h3 class="text-lg font-bold text-slate-900 mt-2 leading-snug">
                                    {{ clinicAddress }}
                                </h3>
                            </div>

                            <div class="space-y-2.5 pt-3 border-t border-slate-100 text-xs">
                                <div class="flex items-center justify-between py-1 border-b border-slate-50">
                                    <span class="text-slate-500 font-medium">{{ operatingDays }}</span>
                                    <span class="text-emerald-700 font-bold">{{ openTime }} - {{ closeTime }}</span>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-slate-50">
                                    <span class="text-slate-400">Sunday</span>
                                    <span class="text-slate-400">Closed (On-call Emergency)</span>
                                </div>
                            </div>

                            <div class="space-y-2 pt-2 text-xs">
                                <div class="flex items-center gap-2 text-slate-700">
                                    <span class="w-6 h-6 rounded-full bg-[#e0f7fa] text-[#00a3b4] flex items-center justify-center font-bold text-[10px]">📞</span>
                                    <span>Reception: <strong>{{ clinicPhone }}</strong></span>
                                </div>
                                <div class="flex items-center gap-2 text-slate-700">
                                    <span class="w-6 h-6 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-[10px]">🚨</span>
                                    <span>24/7 Hotline: <strong>{{ clinicEmergencyPhone }}</strong></span>
                                </div>
                                <div class="flex items-center gap-2 text-slate-700">
                                    <span class="w-6 h-6 rounded-full bg-[#e0f7fa] text-[#00a3b4] flex items-center justify-center font-bold text-[10px]">✉️</span>
                                    <span>Email: <strong>{{ clinicEmail }}</strong></span>
                                </div>
                            </div>
                        </div>

                        <a
                            :href="googleMapsDirectionUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="w-full inline-flex items-center justify-center gap-2 py-3 rounded-2xl bg-[#00a3b4] hover:bg-[#008f9e] text-white font-bold text-xs sm:text-sm shadow-md shadow-[#00a3b4]/20 transition-all cursor-pointer"
                        >
                            <span>🗺️ Open in Google Maps / Get Directions</span>
                        </a>
                    </div>

                    <!-- Right: Embedded Interactive Google Map -->
                    <div class="lg:col-span-7 rounded-3xl bg-white overflow-hidden shadow-[0_10px_30px_rgba(0,163,180,0.06)] border border-slate-100/90 relative min-h-[320px] flex flex-col">
                        <div class="px-6 py-3 bg-[#eaf7fa] border-b border-slate-100 flex items-center justify-between text-xs font-bold text-slate-700">
                            <span class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                Google Map Integration
                            </span>
                            <span class="text-slate-500 font-medium">{{ clinicName }}</span>
                        </div>
                        <iframe
                            :src="mapEmbedUrl"
                            class="w-full flex-1 min-h-[300px] border-0"
                            allowfullscreen=""
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                        ></iframe>
                    </div>

                </div>
            </section>

            <!-- Section 6: Contact & Director's Commitment Message -->
            <section id="contact" class="rounded-3xl bg-gradient-to-r from-[#00808c] via-[#00a3b4] to-[#0284c7] text-white p-8 sm:p-12 shadow-xl flex flex-col md:flex-row items-center justify-between gap-8">
                <div class="space-y-3 text-center md:text-left max-w-xl">
                    <span class="text-[11px] font-bold uppercase tracking-wider bg-white/20 text-white px-3 py-1 rounded-full backdrop-blur-xs">
                        CarePlus Quality Guarantee
                    </span>
                    <h3 class="text-2xl sm:text-3xl font-black tracking-tight">
                        Need medical assistance today?
                    </h3>
                    <p class="text-teal-50 text-xs sm:text-sm leading-relaxed">
                        Book online in seconds and receive an instant queue number on your phone. No physical line-ups needed.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-3">
                    <Link
                        :href="route('booking.create')"
                        class="px-8 py-3.5 rounded-2xl bg-white text-[#00808c] hover:bg-slate-50 font-black text-sm shadow-lg transition-all hover:scale-105 cursor-pointer whitespace-nowrap"
                    >
                        Book Appointment Now &rarr;
                    </Link>
                </div>
            </section>

        </div>

        <!-- Footer (Clean & Soft Palette) -->
        <footer class="bg-white border-t border-slate-200/80 py-8 text-xs text-slate-500">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2 font-bold text-slate-800">
                    <span class="text-[#00a3b4]">🏥</span>
                    <span>{{ clinicName }}</span>
                    <span class="text-slate-300">|</span>
                    <span class="font-normal text-slate-400">Pasig City, Metro Manila</span>
                </div>

                <div class="flex items-center space-x-6 text-slate-500">
                    <a href="#home" class="hover:text-[#00a3b4]">Home</a>
                    <a href="#about" class="hover:text-[#00a3b4]">About Us</a>
                    <a href="#departments" class="hover:text-[#00a3b4]">Departments</a>
                    <a href="#services" class="hover:text-[#00a3b4]">Services</a>
                    <a href="#doctors" class="hover:text-[#00a3b4]">Doctors</a>
                    <Link :href="route('login')" class="font-bold text-[#00a3b4] hover:underline">Staff Login</Link>
                </div>
            </div>
        </footer>

    </div>
</template>
