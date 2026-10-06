<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="rounded-xl border-slate-200 shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 bg-white/95 text-slate-800 text-sm transition-all"
        v-model="model"
        ref="input"
    />
</template>
