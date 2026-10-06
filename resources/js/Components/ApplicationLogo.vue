<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const clinic = computed(() => page.props.clinic);
</script>

<template>
    <div class="flex items-center gap-2.5">
        <img
            v-if="clinic?.logo_url"
            :src="clinic.logo_url"
            :alt="clinic?.name || 'Clinic Logo'"
            class="h-9 w-9 object-contain rounded-xl shadow-xs"
        />
        <div
            v-else
            class="h-9 w-9 rounded-xl bg-gradient-to-tr from-teal-700 to-teal-500 text-white flex items-center justify-center font-black text-sm shadow-md shadow-teal-600/20"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
            </svg>
        </div>
        <span class="font-bold text-slate-900 tracking-tight text-base hidden sm:inline">
            {{ clinic?.name || 'Clinic Connect' }}
        </span>
    </div>
</template>

