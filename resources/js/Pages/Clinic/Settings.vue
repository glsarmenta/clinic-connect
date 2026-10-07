<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DoctorAvailabilityTimeline from '@/Components/DoctorAvailabilityTimeline.vue';
import { Head, useForm, Link, usePage, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    clinic: Object,
    secretaries: {
        type: Array,
        default: () => [],
    },
    doctors: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const logoPreview = ref(props.clinic?.logo_url || null);
const logoFileInput = ref(null);
const activeTab = ref('branding'); // branding, about, location, hours, assignments

const assignmentsForm = useForm({
    assignments: (props.secretaries || []).map(sec => ({
        secretary_id: sec.id,
        secretary_name: sec.name,
        secretary_email: sec.email,
        doctor_ids: sec.assigned_doctors ? sec.assigned_doctors.map(d => d.id) : [],
    })),
});

const isDoctorAssigned = (secIndex, docId) => {
    return assignmentsForm.assignments[secIndex]?.doctor_ids?.includes(docId);
};

const toggleDoctorAssignment = (secIndex, docId) => {
    const list = assignmentsForm.assignments[secIndex].doctor_ids;
    const index = list.indexOf(docId);
    if (index > -1) {
        list.splice(index, 1);
    } else {
        list.push(docId);
    }
};

const assignAllDoctors = (secIndex) => {
    assignmentsForm.assignments[secIndex].doctor_ids = (props.doctors || []).map(d => d.id);
};

const clearAllDoctors = (secIndex) => {
    assignmentsForm.assignments[secIndex].doctor_ids = [];
};

const isResettingDemo = ref(false);
const isGeneratingDemo = ref(false);

const resetPatientData = () => {
    if (confirm('Are you sure you want to clear all patient demo queues, appointments, and medical history for a clean slate demo?')) {
        isResettingDemo.value = true;
        router.post(route('demo.reset-patients'), {}, {
            preserveScroll: true,
            onFinish: () => { isResettingDemo.value = false; },
        });
    }
};

const generatePatientData = () => {
    isGeneratingDemo.value = true;
    router.post(route('demo.generate-patients'), {}, {
        preserveScroll: true,
        onFinish: () => { isGeneratingDemo.value = false; },
    });
};

const form = useForm({
    name: props.clinic?.name || '',
    tagline: props.clinic?.tagline || '',
    about: props.clinic?.about || '',
    address: props.clinic?.address || '',
    phone: props.clinic?.phone || '',
    emergency_phone: props.clinic?.emergency_phone || '',
    email: props.clinic?.email || '',
    google_map_url: props.clinic?.google_map_url || '',
    google_map_embed_url: props.clinic?.google_map_embed_url || '',
    open_time: props.clinic?.open_time ? props.clinic.open_time.substring(0, 5) : '08:00',
    close_time: props.clinic?.close_time ? props.clinic.close_time.substring(0, 5) : '17:00',
    operating_days: props.clinic?.operating_days || 'Monday – Saturday',
    logo: null,
    logo_url: props.clinic?.logo_url || '',
});

const handleFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.logo = file;
        const reader = new FileReader();
        reader.onload = (uploadEvent) => {
            logoPreview.value = uploadEvent.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const selectPresetLogo = (svgDataUrl) => {
    form.logo = null;
    form.logo_url = svgDataUrl;
    logoPreview.value = svgDataUrl;
};

const clearLogo = () => {
    form.logo = null;
    form.logo_url = '';
    logoPreview.value = null;
    if (logoFileInput.value) {
        logoFileInput.value.value = '';
    }
};

// Auto-generate Google Maps Embed when address changes if empty
const generateMapEmbedFromAddress = () => {
    if (form.address) {
        const query = encodeURIComponent(form.address);
        form.google_map_embed_url = `https://maps.google.com/maps?q=${query}&t=&z=15&ie=UTF8&iwloc=&output=embed`;
        form.google_map_url = `https://maps.google.com/?q=${query}`;
    }
};

// Computed live embed URL for map preview
const mapPreviewEmbedSrc = computed(() => {
    if (form.google_map_embed_url) {
        if (form.google_map_embed_url.includes('src="')) {
            const match = form.google_map_embed_url.match(/src=["']([^"']+)["']/);
            if (match) return match[1];
        }
        return form.google_map_embed_url;
    }
    const query = encodeURIComponent(form.address || form.name || 'Springfield Clinic');
    return `https://maps.google.com/maps?q=${query}&t=&z=15&ie=UTF8&iwloc=&output=embed`;
});

// Quick-fill templates for About section
const fillAboutTemplate = (type) => {
    if (type === 'family') {
        form.about = `${form.name || 'Our clinic'} is dedicated to providing high-quality, comprehensive family healthcare. From pediatric wellness checks to chronic health management, our accredited physicians prioritize compassionate, patient-first outpatient medicine with a frictionless digital queue.`;
    } else if (type === 'specialist') {
        form.about = `${form.name || 'Our clinic'} is a multidisciplinary specialist medical center offering state-of-the-art diagnostic care, specialized pediatric clinics, and individualized therapy plans with minimal waiting time.`;
    } else if (type === 'urgent') {
        form.about = `${form.name || 'Our clinic'} provides rapid walk-in and online booked outpatient triage, acute minor illness care, routine physical screenings, and personalized health consultations.`;
    }
};

const submitForm = () => {
    form.post(route('doctor.clinic-settings.update'), {
        preserveScroll: true,
        onSuccess: () => {
            // Update browser tab favicon dynamically if changed
            if (logoPreview.value) {
                let link = document.querySelector("link[rel~='icon']");
                if (link) link.href = logoPreview.value;
            }
        },
    });
};
</script>

<template>
    <Head title="Clinic & Homepage Settings - Doctor / Owner Workspace" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-extrabold text-slate-900 leading-tight flex items-center gap-2">
                        <span>🏥 Clinic Information & Homepage Customizer</span>
                    </h2>
                    <p class="text-xs text-brand-teal font-medium mt-0.5">
                        Manage your clinic branding, logo, favicon, Google Map location, contact numbers, and story.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Link
                        :href="route('home')"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-bold bg-brand-50 text-brand-700 hover:bg-brand-100 border border-brand-200 transition-colors shadow-xs"
                    >
                        <span>👁️ View Live Homepage</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </Link>
                    <Link
                        :href="route('doctor.dashboard')"
                        class="px-4 py-2 rounded-full text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors"
                    >
                        &larr; Doctor Workspace
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6 sm:py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            <!-- Success Flash Notification -->
            <div
                v-if="page.props.flash?.success"
                class="p-4 rounded-2xl bg-emerald-50 border border-emerald-300 text-emerald-800 text-sm font-semibold flex items-center gap-3 shadow-xs"
            >
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>{{ page.props.flash.success }}</span>
            </div>

            <!-- Main Form Card -->
            <form @submit.prevent="submitForm" class="bg-white/95 rounded-[32px] sm:rounded-[36px] border border-white/80 shadow-xl shadow-cyan-950/5 overflow-hidden backdrop-blur-md">
                
                <!-- Settings Navigation Tabs -->
                <div class="border-b border-cyan-50 bg-arctic-50/60 px-6 py-3 flex flex-wrap gap-2 text-xs font-bold">
                    <button
                        type="button"
                        @click="activeTab = 'branding'"
                        :class="activeTab === 'branding' ? 'bg-brand-teal text-white shadow-xs' : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200'"
                        class="px-4 py-2 rounded-full transition-all cursor-pointer flex items-center gap-1.5"
                    >
                        <span>🎨 Branding & Logo / Favicon</span>
                    </button>
                    <button
                        type="button"
                        @click="activeTab = 'about'"
                        :class="activeTab === 'about' ? 'bg-brand-teal text-white shadow-xs' : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200'"
                        class="px-4 py-2 rounded-full transition-all cursor-pointer flex items-center gap-1.5"
                    >
                        <span>📝 About Clinic Story</span>
                    </button>
                    <button
                        type="button"
                        @click="activeTab = 'location'"
                        :class="activeTab === 'location' ? 'bg-brand-teal text-white shadow-xs' : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200'"
                        class="px-4 py-2 rounded-full transition-all cursor-pointer flex items-center gap-1.5"
                    >
                        <span>🗺️ Address & Google Map</span>
                    </button>
                    <button
                        type="button"
                        @click="activeTab = 'hours'"
                        :class="activeTab === 'hours' ? 'bg-brand-teal text-white shadow-xs' : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200'"
                        class="px-4 py-2 rounded-full transition-all cursor-pointer flex items-center gap-1.5"
                    >
                        <span>📞 Contact & Hours</span>
                    </button>
                    <button
                        type="button"
                        @click="activeTab = 'schedule'"
                        :class="activeTab === 'schedule' ? 'bg-brand-teal text-white shadow-xs' : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200'"
                        class="px-4 py-2 rounded-full transition-all cursor-pointer flex items-center gap-1.5"
                    >
                        <span>📅 Doctor Schedules & Timelines</span>
                    </button>
                    <button
                        type="button"
                        @click="activeTab = 'assignments'"
                        :class="activeTab === 'assignments' ? 'bg-brand-teal text-white shadow-xs' : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200'"
                        class="px-4 py-2 rounded-full transition-all cursor-pointer flex items-center gap-1.5"
                    >
                        <span>👥 Secretary & Doctor Assignments</span>
                    </button>
                    <button
                        type="button"
                        @click="activeTab = 'demo_tools'"
                        :class="activeTab === 'demo_tools' ? 'bg-rose-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200'"
                        class="px-4 py-2 rounded-full transition-all cursor-pointer flex items-center gap-1.5 sm:ml-auto"
                    >
                        <span>🛠️ Demo Data Tools</span>
                    </button>
                </div>

                <div class="p-6 sm:p-10 space-y-8">

                    <!-- Tab 1: Branding, Clinic Name & Logo / Favicon -->
                    <div v-show="activeTab === 'branding'" class="space-y-6">
                        <div class="border-b border-slate-100 pb-3">
                            <h3 class="text-lg font-bold text-slate-900">Clinic Identity & Logo / Favicon</h3>
                            <p class="text-xs text-slate-500">Configure your public clinic title, tagline, and logo image used in headers and as browser favicon.</p>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                        Clinic Official Name <span class="text-rose-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        v-model="form.name"
                                        placeholder="e.g. Metro Health Medical Clinic"
                                        class="w-full rounded-xl border-slate-300 focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 shadow-xs"
                                        required
                                    />
                                    <div v-if="form.errors.name" class="text-xs text-rose-600 mt-1">{{ form.errors.name }}</div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                        Homepage Tagline / Subtitle
                                    </label>
                                    <input
                                        type="text"
                                        v-model="form.tagline"
                                        placeholder="e.g. Excellence in Family, Pediatric & Specialist Healthcare"
                                        class="w-full rounded-xl border-slate-300 focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 shadow-xs"
                                    />
                                    <div v-if="form.errors.tagline" class="text-xs text-rose-600 mt-1">{{ form.errors.tagline }}</div>
                                </div>

                                <!-- Logo Upload -->
                                <div class="space-y-2">
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                        Upload Clinic Logo (PNG, JPG, SVG, WebP)
                                    </label>
                                    <div class="flex items-center gap-3">
                                        <input
                                            type="file"
                                            ref="logoFileInput"
                                            accept="image/*"
                                            @change="handleFileChange"
                                            class="block w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 cursor-pointer"
                                        />
                                        <button
                                            v-if="logoPreview"
                                            type="button"
                                            @click="clearLogo"
                                            class="px-3 py-2 text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-xl border border-rose-200 transition-colors cursor-pointer"
                                        >
                                            Clear
                                        </button>
                                    </div>
                                    <p class="text-[11px] text-slate-400">Recommended size: 256x256 square format with transparent background.</p>
                                </div>

                                <!-- Logo URL Direct Input -->
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">
                                        Or Use Logo Image URL:
                                    </label>
                                    <input
                                        type="url"
                                        v-model="form.logo_url"
                                        @input="logoPreview = form.logo_url"
                                        placeholder="https://example.com/logo.png"
                                        class="w-full rounded-xl border-slate-300 focus:border-teal-500 focus:ring-teal-500 text-xs py-2 shadow-xs"
                                    />
                                </div>
                            </div>

                            <!-- Live Favicon & Logo Preview Box -->
                            <div class="bg-slate-50 rounded-2xl border border-slate-200 p-6 space-y-4">
                                <div class="text-xs font-bold uppercase tracking-wider text-slate-500">Live Favicon & Branding Preview</div>
                                
                                <!-- Browser Tab Mockup -->
                                <div class="bg-slate-200 rounded-t-xl p-2 border border-slate-300 flex items-center gap-2">
                                    <div class="flex gap-1.5 pl-1">
                                        <div class="w-2.5 h-2.5 rounded-full bg-rose-400"></div>
                                        <div class="w-2.5 h-2.5 rounded-full bg-amber-400"></div>
                                        <div class="w-2.5 h-2.5 rounded-full bg-emerald-400"></div>
                                    </div>
                                    <div class="bg-white rounded-lg px-3 py-1 text-xs text-slate-700 font-medium flex items-center gap-2 shadow-xs max-w-xs truncate">
                                        <img
                                            v-if="logoPreview"
                                            :src="logoPreview"
                                            class="w-4 h-4 object-contain rounded-xs"
                                            alt="Favicon"
                                        />
                                        <span v-else class="text-xs">🏥</span>
                                        <span class="truncate">{{ form.name || 'Clinic Connect' }} - Outpatient Portal</span>
                                    </div>
                                </div>

                                <!-- Header Brand Mockup -->
                                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center gap-3">
                                    <img
                                        v-if="logoPreview"
                                        :src="logoPreview"
                                        class="w-10 h-10 object-contain rounded-xl border border-slate-200 p-1 shadow-xs"
                                        alt="Clinic Logo"
                                    />
                                    <div
                                        v-else
                                        class="w-10 h-10 rounded-xl bg-teal-600 text-white flex items-center justify-center font-bold text-lg"
                                    >
                                        🏥
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 text-sm">{{ form.name || 'Clinic Name' }}</div>
                                        <div class="text-xs text-slate-500">{{ form.tagline || 'Tagline will appear here' }}</div>
                                    </div>
                                </div>

                                <!-- Social Media Sharing Card Mockup (Open Graph) -->
                                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-xs space-y-0">
                                    <div class="px-3 py-2 bg-slate-100/70 border-b border-slate-200 text-[11px] font-bold text-slate-600 flex items-center justify-between">
                                        <span class="flex items-center gap-1.5">
                                            <span>🌐</span>
                                            <span>Social Media Link Preview (Facebook, X, WhatsApp, LinkedIn)</span>
                                        </span>
                                        <span class="text-[10px] text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">Open Graph Active</span>
                                    </div>
                                    <div class="relative aspect-[1.91/1] bg-slate-100 overflow-hidden">
                                        <img
                                            src="/images/og-preview.jpg"
                                            alt="Social Media Preview Image"
                                            class="w-full h-full object-cover"
                                        />
                                    </div>
                                    <div class="p-3 bg-white space-y-1">
                                        <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">
                                            clinicconnect.ph
                                        </div>
                                        <div class="font-bold text-slate-900 text-xs line-clamp-1">
                                            {{ form.name || 'Metro Manila Family & Pediatric Clinic' }} — Healthcare & Clinic Portal
                                        </div>
                                        <div class="text-[11px] text-slate-500 line-clamp-2 leading-relaxed">
                                            {{ form.tagline || 'Modern medical clinic management and patient care portal with live queue tracking, doctor consultations, and patient portal.' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 2: About The Clinic Story -->
                    <div v-show="activeTab === 'about'" class="space-y-6">
                        <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900">About The Clinic & Medical Director Note</h3>
                                <p class="text-xs text-slate-500">Provide an overview of the clinic's mission, facility offerings, and owner commitment.</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-slate-500">Quick Templates:</span>
                                <button
                                    type="button"
                                    @click="fillAboutTemplate('family')"
                                    class="px-2.5 py-1 text-xs bg-slate-100 hover:bg-teal-50 text-slate-700 hover:text-teal-800 rounded-lg border border-slate-200 transition-colors cursor-pointer font-medium"
                                >
                                    Family Practice
                                </button>
                                <button
                                    type="button"
                                    @click="fillAboutTemplate('specialist')"
                                    class="px-2.5 py-1 text-xs bg-slate-100 hover:bg-teal-50 text-slate-700 hover:text-teal-800 rounded-lg border border-slate-200 transition-colors cursor-pointer font-medium"
                                >
                                    Specialist Center
                                </button>
                                <button
                                    type="button"
                                    @click="fillAboutTemplate('urgent')"
                                    class="px-2.5 py-1 text-xs bg-slate-100 hover:bg-teal-50 text-slate-700 hover:text-teal-800 rounded-lg border border-slate-200 transition-colors cursor-pointer font-medium"
                                >
                                    Urgent & Walk-in
                                </button>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Clinic Story & Mission Description
                                </label>
                                <textarea
                                    v-model="form.about"
                                    rows="6"
                                    placeholder="Write about the clinic history, pediatric facilities, diagnostics, and patient commitments..."
                                    class="w-full rounded-2xl border-slate-300 focus:border-teal-500 focus:ring-teal-500 text-sm py-3 shadow-xs leading-relaxed"
                                ></textarea>
                                <div v-if="form.errors.about" class="text-xs text-rose-600 mt-1">{{ form.errors.about }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 3: Address & Google Maps Integration -->
                    <div v-show="activeTab === 'location'" class="space-y-6">
                        <div class="border-b border-slate-100 pb-3">
                            <h3 class="text-lg font-bold text-slate-900">Address & Interactive Google Map</h3>
                            <p class="text-xs text-slate-500">Configure your physical street location and Google Maps embed for patients.</p>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                            Street Address
                                        </label>
                                        <button
                                            type="button"
                                            @click="generateMapEmbedFromAddress"
                                            class="text-[11px] font-bold text-teal-700 hover:underline cursor-pointer"
                                        >
                                            ⚡ Auto-generate Map From Address
                                        </button>
                                    </div>
                                    <textarea
                                        v-model="form.address"
                                        rows="2"
                                        placeholder="e.g. 742 Evergreen Terrace, Medical Suite 300, Springfield"
                                        class="w-full rounded-xl border-slate-300 focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 shadow-xs"
                                    ></textarea>
                                    <div v-if="form.errors.address" class="text-xs text-rose-600 mt-1">{{ form.errors.address }}</div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                        Google Maps Embed URL or &lt;iframe&gt; code
                                    </label>
                                    <textarea
                                        v-model="form.google_map_embed_url"
                                        rows="3"
                                        placeholder="Paste full Google Map embed URL (https://maps.google.com/maps?q=...&output=embed) or <iframe> embed code"
                                        class="w-full rounded-xl border-slate-300 focus:border-teal-500 focus:ring-teal-500 text-xs py-2 shadow-xs font-mono"
                                    ></textarea>
                                    <p class="text-[11px] text-slate-400 mt-1">Patients will see an interactive map embedded on the homepage.</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                        Google Maps Directions / Link URL
                                    </label>
                                    <input
                                        type="url"
                                        v-model="form.google_map_url"
                                        placeholder="https://maps.google.com/?q=..."
                                        class="w-full rounded-xl border-slate-300 focus:border-teal-500 focus:ring-teal-500 text-xs py-2 shadow-xs"
                                    />
                                </div>
                            </div>

                            <!-- Live Map Preview -->
                            <div class="bg-slate-900 rounded-2xl p-4 border border-slate-800 flex flex-col space-y-3">
                                <div class="flex items-center justify-between text-xs text-white">
                                    <span class="font-bold">Live Google Map Preview</span>
                                    <span class="text-teal-400 text-[11px]">Interactive</span>
                                </div>

                                <div class="w-full flex-1 rounded-xl overflow-hidden min-h-[220px] bg-slate-800 relative">
                                    <iframe
                                        :src="mapPreviewEmbedSrc"
                                        class="w-full h-full min-h-[240px] border-0"
                                        allowfullscreen=""
                                        loading="lazy"
                                        referrerpolicy="no-referrer-when-downgrade"
                                    ></iframe>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 4: Contact & Opening Hours -->
                    <div v-show="activeTab === 'hours'" class="space-y-6">
                        <div class="border-b border-slate-100 pb-3">
                            <h3 class="text-lg font-bold text-slate-900">Contact Information & Operating Hours</h3>
                            <p class="text-xs text-slate-500">Set primary reception numbers, urgent emergency hotlines, email, and daily schedule.</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Reception Phone Number
                                </label>
                                <input
                                    type="text"
                                    v-model="form.phone"
                                    placeholder="+1 (555) 019-2834"
                                    class="w-full rounded-xl border-slate-300 focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 shadow-xs"
                                />
                                <div v-if="form.errors.phone" class="text-xs text-rose-600 mt-1">{{ form.errors.phone }}</div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    24/7 Urgent / Emergency Hotline
                                </label>
                                <input
                                    type="text"
                                    v-model="form.emergency_phone"
                                    placeholder="+1 (555) 911-CARE"
                                    class="w-full rounded-xl border-slate-300 focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 shadow-xs"
                                />
                                <div v-if="form.errors.emergency_phone" class="text-xs text-rose-600 mt-1">{{ form.errors.emergency_phone }}</div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Official Contact Email
                                </label>
                                <input
                                    type="email"
                                    v-model="form.email"
                                    placeholder="contact@metrohealth.example"
                                    class="w-full rounded-xl border-slate-300 focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 shadow-xs"
                                />
                                <div v-if="form.errors.email" class="text-xs text-rose-600 mt-1">{{ form.errors.email }}</div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Operating Days Schedule
                                </label>
                                <input
                                    type="text"
                                    v-model="form.operating_days"
                                    placeholder="e.g. Monday – Saturday"
                                    class="w-full rounded-xl border-slate-300 focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 shadow-xs"
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Daily Opening Time
                                </label>
                                <input
                                    type="time"
                                    v-model="form.open_time"
                                    class="w-full rounded-xl border-slate-300 focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 shadow-xs"
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Daily Closing Time
                                </label>
                                <input
                                    type="time"
                                    v-model="form.close_time"
                                    class="w-full rounded-xl border-slate-300 focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 shadow-xs"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Tab 5: Doctor Weekly Schedules & Timelines -->
                    <div v-show="activeTab === 'schedule'" class="space-y-6">
                        <DoctorAvailabilityTimeline
                            :doctors="doctors"
                            :submit-route="route('doctor.clinic-settings.availabilities')"
                        />
                    </div>

                    <!-- Tab 6: Secretary & Doctor Queue Assignments -->
                    <div v-show="activeTab === 'assignments'" class="space-y-6">
                        <div class="border-b border-slate-100 pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900">Secretary Queue Allocation & Multi-Doctor Coverage</h3>
                                <p class="text-xs text-slate-500">Assign which physician queues each secretary manages. A secretary can handle 1 dedicated doctor or float across multiple doctors.</p>
                            </div>
                            <button
                                type="button"
                                @click="submitAssignments"
                                :disabled="assignmentsForm.processing"
                                class="inline-flex items-center gap-2 px-6 py-2.5 bg-brand-teal hover:bg-brand-600 text-white text-xs font-bold rounded-full shadow-md shadow-brand-teal/20 transition-all cursor-pointer disabled:opacity-50"
                            >
                                <svg v-if="assignmentsForm.processing" class="animate-spin -ml-1 mr-1.5 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span>Save Assignments</span>
                            </button>
                        </div>

                        <div v-if="secretaries.length === 0" class="p-8 text-center bg-slate-50 rounded-2xl border border-slate-200 text-xs text-slate-500">
                            No secretary accounts registered yet. You can create secretary accounts via User Management or Database Seeder.
                        </div>

                        <div v-else class="space-y-6">
                            <div
                                v-for="(sec, secIdx) in assignmentsForm.assignments"
                                :key="sec.secretary_id"
                                class="bg-slate-50/80 rounded-3xl border border-slate-200/90 p-6 space-y-4 hover:border-brand-teal/40 transition-all shadow-2xs"
                            >
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200/70 pb-4">
                                    <div class="flex items-center gap-3.5">
                                        <div class="w-12 h-12 rounded-2xl bg-brand-teal text-white flex items-center justify-center font-bold text-base shadow-xs">
                                            📋
                                        </div>
                                        <div>
                                            <div class="font-extrabold text-slate-900 text-sm sm:text-base flex flex-wrap items-center gap-2">
                                                <span>{{ sec.secretary_name }}</span>
                                                <span
                                                    v-if="sec.doctor_ids.length === doctors.length && doctors.length > 0"
                                                    class="text-[10px] px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold border border-emerald-300"
                                                >
                                                    All Doctors (Clinic-Wide Float)
                                                </span>
                                                <span
                                                    v-else-if="sec.doctor_ids.length > 0"
                                                    class="text-[10px] px-2.5 py-0.5 rounded-full bg-sky-100 text-sky-800 font-bold border border-sky-300"
                                                >
                                                    Assigned to {{ sec.doctor_ids.length }} Doctor(s)
                                                </span>
                                                <span
                                                    v-else
                                                    class="text-[10px] px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 font-bold border border-amber-300"
                                                >
                                                    Unassigned
                                                </span>
                                            </div>
                                            <div class="text-xs text-slate-500 mt-0.5">{{ sec.secretary_email }}</div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <button
                                            type="button"
                                            @click="assignAllDoctors(secIdx)"
                                            class="px-3 py-1.5 rounded-xl bg-white hover:bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-700 transition-colors cursor-pointer"
                                        >
                                            Select All (Float)
                                        </button>
                                        <button
                                            type="button"
                                            @click="clearAllDoctors(secIdx)"
                                            class="px-3 py-1.5 rounded-xl bg-white hover:bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-500 hover:text-rose-600 transition-colors cursor-pointer"
                                        >
                                            Clear
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <div class="text-xs font-bold text-slate-600 uppercase tracking-wider mb-2.5">
                                        Select Assigned Physician Queue(s):
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                                        <div
                                            v-for="doc in doctors"
                                            :key="doc.id"
                                            @click="toggleDoctorAssignment(secIdx, doc.id)"
                                            :class="isDoctorAssigned(secIdx, doc.id)
                                                ? 'bg-teal-50/90 border-brand-teal text-teal-950 ring-2 ring-brand-teal/20 shadow-xs'
                                                : 'bg-white border-slate-200 text-slate-700 hover:border-slate-300 hover:bg-slate-50/50'"
                                            class="p-3.5 rounded-2xl border transition-all cursor-pointer flex items-center justify-between gap-3 select-none"
                                        >
                                            <div class="flex items-center gap-3 min-w-0">
                                                <img
                                                    :src="doc.name.includes('Cristina') ? '/images/doctor_cristina.jpg' : '/images/doctor_jose.jpg'"
                                                    :alt="doc.name"
                                                    class="w-10 h-10 rounded-xl object-cover border border-white shadow-2xs shrink-0"
                                                />
                                                <div class="min-w-0">
                                                    <div class="font-bold text-xs truncate">{{ doc.name }}</div>
                                                    <div class="text-[11px] text-teal-700 truncate font-medium">{{ doc.specialization }}</div>
                                                </div>
                                            </div>

                                            <div
                                                :class="isDoctorAssigned(secIdx, doc.id)
                                                    ? 'bg-brand-teal text-white'
                                                    : 'border border-slate-300 bg-slate-100 text-transparent'"
                                                class="w-6 h-6 rounded-full flex items-center justify-center font-bold text-xs shrink-0 transition-colors"
                                            >
                                                ✓
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 6: Admin Demo Data Tools -->
                    <div v-show="activeTab === 'demo_tools'" class="space-y-6">
                        <div class="border-b border-slate-100 pb-3">
                            <h3 class="text-lg font-bold text-slate-900">Admin Demo Data Management</h3>
                            <p class="text-xs text-slate-500">Easily clear test patient queues and medical histories for a clean live demo, or generate fresh Filipino test data scenarios on demand.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Clean Slate Card -->
                            <div class="bg-gradient-to-br from-rose-50/70 to-red-50/40 border border-rose-200 rounded-3xl p-6 flex flex-col justify-between space-y-4 shadow-sm">
                                <div class="space-y-2">
                                    <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-xl font-bold">
                                        🗑️
                                    </div>
                                    <h4 class="text-base font-extrabold text-slate-900">Reset & Clear Patient Data</h4>
                                    <p class="text-xs text-slate-600 leading-relaxed">
                                        Instantly purge all patient queues, today's appointments, past medical consult notes, and dependents. All clinic branding, doctor schedules, and secretary assignments remain 100% intact.
                                    </p>
                                    <div class="text-[11px] text-rose-700 font-semibold bg-rose-100/80 px-3 py-1.5 rounded-xl inline-block">
                                        Recommended before starting a fresh client demo session.
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    @click="resetPatientData"
                                    :disabled="isResettingDemo"
                                    class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition-all shadow-md shadow-rose-600/20 disabled:opacity-50 cursor-pointer"
                                >
                                    <span v-if="isResettingDemo" class="animate-spin">⏳</span>
                                    <span v-else>🗑️</span>
                                    <span>{{ isResettingDemo ? 'Clearing Patient Data...' : 'Clear Patient Test Data (Clean Slate)' }}</span>
                                </button>
                            </div>

                            <!-- Regenerate Test Data Card -->
                            <div class="bg-gradient-to-br from-teal-50/70 to-cyan-50/40 border border-teal-200 rounded-3xl p-6 flex flex-col justify-between space-y-4 shadow-sm">
                                <div class="space-y-2">
                                    <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-teal flex items-center justify-center text-xl font-bold">
                                        🔄
                                    </div>
                                    <h4 class="text-base font-extrabold text-slate-900">Generate Realistic Demo Scenarios</h4>
                                    <p class="text-xs text-slate-600 leading-relaxed">
                                        Generate authentic Filipino demo patients (Juan dela Cruz, Maria Clara Reyes, etc.) with active multi-stage live queues (serving, waiting in lobby, scheduled appointments, vaccine schedules).
                                    </p>
                                    <div class="text-[11px] text-brand-teal font-semibold bg-brand-50/80 px-3 py-1.5 rounded-xl inline-block">
                                        Populates live queues, patient portal histories, and doctor consult records.
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    @click="generatePatientData"
                                    :disabled="isGeneratingDemo"
                                    class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-brand-teal hover:bg-brand-600 text-white text-xs font-bold transition-all shadow-md shadow-brand-teal/20 disabled:opacity-50 cursor-pointer"
                                >
                                    <span v-if="isGeneratingDemo" class="animate-spin">⏳</span>
                                    <span v-else>🔄</span>
                                    <span>{{ isGeneratingDemo ? 'Generating Scenarios...' : 'Generate Demo Test Patients & Queues' }}</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Save Actions Button (for Branding/About/Location/Hours) -->
                    <div v-show="activeTab !== 'assignments' && activeTab !== 'demo_tools' && activeTab !== 'schedule'" class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="text-xs text-slate-500">
                            Updates are applied immediately across the homepage, dynamic favicon, and public portals.
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 bg-brand-teal hover:bg-brand-600 active:bg-brand-700 text-white font-bold rounded-full shadow-lg shadow-brand-teal/25 transition-all text-sm disabled:opacity-50 cursor-pointer"
                        >
                            <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span>Save Clinic Details & Update Homepage</span>
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
