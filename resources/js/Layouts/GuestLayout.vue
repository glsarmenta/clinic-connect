<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const clinic = page.props.clinic;
</script>

<template>
    <div class="flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-[#cbeef4]/40 via-[#f0f9fb] to-[#e6f7fa]/60 p-4 sm:p-6">
        <!-- Floating Brand Pill -->
        <div class="mb-6 text-center">
            <Link href="/" class="inline-flex items-center gap-3 rounded-full bg-white/80 px-5 py-2.5 shadow-sm backdrop-blur-md border border-white/60 hover:bg-white transition-all group">
                <ApplicationLogo class="h-9 w-9 text-brand-600 transition-transform group-hover:scale-105" />
                <div class="text-left">
                    <span class="block text-sm font-bold text-slate-900 tracking-tight leading-none">{{ clinic?.name || 'CarePlus Medical' }}</span>
                    <span class="text-[11px] text-brand-teal font-medium">Clinic Portal</span>
                </div>
            </Link>
        </div>

        <!-- Frosted Glass Card -->
        <div class="w-full max-w-md overflow-hidden rounded-[28px] sm:rounded-[36px] bg-white/90 p-8 shadow-xl shadow-cyan-950/5 backdrop-blur-md border border-white/80">
            <slot />
        </div>

        <!-- Back to Home Link -->
        <div class="mt-6 text-center">
            <Link href="/" class="text-xs font-semibold text-slate-500 hover:text-brand-600 inline-flex items-center gap-1.5 transition-colors">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Return to Clinic Homepage
            </Link>
        </div>
    </div>
</template>

