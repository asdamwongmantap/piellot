<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '../../Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

const form = useForm({ email: '', password: '', password_confirmation: '' });

const submit = () => form.post(route('password.update'));
</script>

<template>
  <Head title="Lupa Kata Sandi" />
  <div class="mx-auto max-w-md">
    <h1 class="text-2xl font-bold text-slate-900">Lupa kata sandi</h1>
    <p class="mt-1 text-sm text-slate-500">Masukkan email akun Anda dan kata sandi baru.</p>

    <form class="mt-6 space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" @submit.prevent="submit">
      <div>
        <label class="block text-sm font-medium text-slate-700">Email</label>
        <input v-model="form.email" type="email" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
        <p v-if="form.errors.email" class="mt-1 text-sm text-rose-600">{{ form.errors.email }}</p>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700">Kata sandi baru</label>
        <input v-model="form.password" type="password" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
        <p v-if="form.errors.password" class="mt-1 text-sm text-rose-600">{{ form.errors.password }}</p>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700">Konfirmasi kata sandi</label>
        <input v-model="form.password_confirmation" type="password" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
      </div>
      <button :disabled="form.processing" class="w-full rounded-xl bg-[#0c8f86] px-4 py-2.5 font-bold text-white hover:bg-[#087e75] disabled:opacity-60">Simpan kata sandi baru</button>
    </form>
    <p class="mt-4 text-center text-sm text-slate-500">Ingat kata sandi? <Link :href="route('login')" class="font-semibold text-[#0c8f86]">Masuk</Link></p>
  </div>
</template>
