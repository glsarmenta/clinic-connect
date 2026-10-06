<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Sign In - CarePlus Medical" />

        <div class="mb-6 text-center">
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Welcome Back</h2>
            <p class="text-xs text-slate-500 mt-1">Sign in to access your clinic appointments and portal</p>
        </div>

        <div v-if="status" class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200 p-3 text-xs font-semibold text-emerald-800">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="email" value="Email Address" class="text-xs font-semibold text-slate-700 uppercase tracking-wider" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="doctor@clinic.com or patient email"
                />

                <InputError class="mt-1" :message="form.errors.email" />
            </div>

            <div>
                <div class="flex items-center justify-between">
                    <InputLabel for="password" value="Password" class="text-xs font-semibold text-slate-700 uppercase tracking-wider" />
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-xs text-brand-teal hover:text-brand-700 font-semibold"
                    >
                        Forgot password?
                    </Link>
                </div>

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                />

                <InputError class="mt-1" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center cursor-pointer">
                    <Checkbox name="remember" v-model:checked="form.remember" class="rounded text-brand-teal focus:ring-brand-teal" />
                    <span class="ms-2 text-xs text-slate-600 font-medium">Remember me</span>
                </label>
            </div>

            <div class="pt-2">
                <PrimaryButton
                    class="w-full py-3"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Sign In
                </PrimaryButton>
            </div>

            <div class="pt-4 border-t border-slate-100 text-center">
                <p class="text-xs text-slate-500">
                    Don't have an account?
                    <Link :href="route('register')" class="text-brand-teal hover:text-brand-700 font-bold ml-1">
                        Register here
                    </Link>
                </p>
            </div>
        </form>

        <!-- Quick Demo Switcher helper -->
        <div class="mt-6 pt-4 border-t border-dashed border-slate-200">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center mb-2">Instant Demo Logins</div>
            <div class="grid grid-cols-2 gap-1.5 text-[11px]">
                <button 
                    type="button" 
                    @click="form.email = 'doctor@clinic.com'; form.password = 'password'"
                    class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-brand-50 hover:text-brand-700 text-slate-700 font-medium transition-colors text-left"
                >
                    🩺 Dr. Santos
                </button>
                <button 
                    type="button" 
                    @click="form.email = 'secretary@clinic.com'; form.password = 'password'"
                    class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-brand-50 hover:text-brand-700 text-slate-700 font-medium transition-colors text-left"
                >
                    📋 Secretary Maria
                </button>
                <button 
                    type="button" 
                    @click="form.email = 'patient@clinic.com'; form.password = 'password'"
                    class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-brand-50 hover:text-brand-700 text-slate-700 font-medium transition-colors text-left col-span-2"
                >
                    👨‍👩‍👧 Patient Juan dela Cruz (Family Portal)
                </button>
            </div>
        </div>
    </GuestLayout>
</template>
