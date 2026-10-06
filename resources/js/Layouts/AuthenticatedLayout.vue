<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, router } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);

const handleResetDemoData = () => {
    if (confirm('Are you sure you want to clear all patient demo data and queues for a clean slate demo?')) {
        router.post(route('demo.reset-patients'), {}, {
            preserveScroll: true,
        });
    }
};

const handleGenerateDemoData = () => {
    router.post(route('demo.generate-patients'), {}, {
        preserveScroll: true,
    });
};
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100">
            <!-- Prototype Demo Switcher Bar -->
            <div class="bg-slate-900 text-white text-xs py-2.5 px-4 border-b border-slate-800 shadow-inner">
                <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-cyan-400 animate-pulse"></span>
                        <span class="font-bold text-slate-200">Interactive MVP Demo:</span>
                        <span class="text-slate-400 hidden sm:inline">Role switcher active</span>
                    </div>
                    <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap">
                        <a href="/" class="px-3 py-1 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors text-[11px] font-medium">
                            🏠 Clinic Homepage
                        </a>
                        <a href="/book" class="px-3 py-1 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors text-[11px] font-medium">
                            1. Patient Booking
                        </a>
                        <a href="/demo-switch/secretary" class="px-3 py-1 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors text-[11px] font-medium">
                            2. Secretary Queue
                        </a>
                        <a href="/demo-switch/doctor1" class="px-3 py-1 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors text-[11px] font-medium">
                            3. Doctor Workspace
                        </a>
                        <a href="/demo-switch/patient" class="px-3 py-1 rounded-full bg-cyan-500/20 text-cyan-300 hover:bg-cyan-500/30 font-bold border border-cyan-500/40 text-[11px] transition-colors">
                            4. Patient & Kids Portal
                        </a>
                        <a href="/doctor/clinic-settings" class="px-3 py-1 rounded-full bg-teal-500/20 text-teal-300 hover:bg-teal-500/30 font-bold border border-teal-500/40 text-[11px] transition-colors">
                            ⚙️ Clinic Settings
                        </a>
                        <div class="h-4 w-px bg-slate-700 mx-1 hidden md:block"></div>
                        <button
                            type="button"
                            @click="handleResetDemoData"
                            class="px-2.5 py-1 rounded-full bg-rose-900/60 hover:bg-rose-800 text-rose-200 border border-rose-700/50 text-[11px] font-bold cursor-pointer transition-colors"
                            title="Clear all patient queues and medical data for a clean slate demo"
                        >
                            🗑️ Reset Demo Data
                        </button>
                        <button
                            type="button"
                            @click="handleGenerateDemoData"
                            class="px-2.5 py-1 rounded-full bg-emerald-900/60 hover:bg-emerald-800 text-emerald-200 border border-emerald-700/50 text-[11px] font-bold cursor-pointer transition-colors"
                            title="Regenerate sample patient queues, medical records & vaccine reminders"
                        >
                            🔄 Generate Demo Data
                        </button>
                    </div>
                </div>
            </div>

            <nav
                class="border-b border-cyan-100/60 bg-white/95 backdrop-blur-md sticky top-0 z-40 shadow-xs"
            >
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationLogo
                                        class="block h-9 w-auto fill-current text-gray-800"
                                    />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div
                                class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex"
                            >
                                <NavLink
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard') || route().current('doctor.dashboard') || route().current('secretary.dashboard') || route().current('patient.dashboard')"
                                >
                                    Dashboard
                                </NavLink>
                                <NavLink
                                    :href="route('doctor.clinic-settings.edit')"
                                    :active="route().current('doctor.clinic-settings.*')"
                                >
                                    🏥 Clinic & Homepage Settings
                                </NavLink>
                                <a
                                    :href="route('home')"
                                    target="_blank"
                                    class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none transition duration-150 ease-in-out"
                                >
                                    🌐 View Public Homepage &rarr;
                                </a>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center">
                            <!-- Settings Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink
                                            :href="route('profile.edit')"
                                        >
                                            Profile
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                        >
                                            Log Out
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                        >
                            Dashboard
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div
                        class="border-t border-gray-200 pb-1 pt-4"
                    >
                        <div class="px-4">
                            <div
                                class="text-base font-medium text-gray-800"
                            >
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-gray-500">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Profile
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header
                class="bg-white shadow"
                v-if="$slots.header"
            >
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
