<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '../../Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

const form = useForm({ name: '', email: '', password: '', password_confirmation: '' });

const submit = () => form.post(route('register'));
</script>

<template>
  <Head title="Daftar" />
  <div class="mx-auto max-w-md">
    <h1 class="text-2xl font-bold text-slate-900">Buat akun PIELLOT</h1>
    <p class="mt-1 text-sm text-slate-500">Akun pertama di sistem otomatis menjadi Admin Fleet. Selanjutnya, Anda akan diminta mendaftarkan perusahaan sebagai PIC.</p>

    <form class="mt-6 space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" @submit.prevent="submit">
      <div>
        <label class="block text-sm font-medium text-slate-700">Nama lengkap</label>
        <input v-model="form.name" type="text" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
        <p v-if="form.errors.name" class="mt-1 text-sm text-rose-600">{{ form.errors.name }}</p>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700">Email</label>
        <input v-model="form.email" type="email" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
        <p v-if="form.errors.email" class="mt-1 text-sm text-rose-600">{{ form.errors.email }}</p>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700">Kata sandi</label>
        <input v-model="form.password" type="password" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
        <p v-if="form.errors.password" class="mt-1 text-sm text-rose-600">{{ form.errors.password }}</p>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700">Ulangi kata sandi</label>
        <input v-model="form.password_confirmation" type="password" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
      </div>
      <button :disabled="form.processing" class="w-full rounded-xl bg-[#0c8f86] px-4 py-2.5 font-bold text-white hover:bg-[#087e75] disabled:opacity-60">Daftar</button>
    </form>
    <p class="mt-4 text-center text-sm text-slate-500">Sudah punya akun? <Link :href="route('login')" class="font-semibold text-[#0c8f86]">Masuk</Link></p>
  </div>
</template>
