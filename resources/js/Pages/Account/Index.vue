<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '../../Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });
defineProps({ account: Object, company: String });

const form = useForm({ current_password: '', password: '', password_confirmation: '' });
const submit = () => form.put(route('account.password'), { preserveScroll: true, onSuccess: () => form.reset() });
</script>

<template>
  <Head title="Akun" />
  <div class="space-y-6">
    <section>
      <p class="text-xs font-bold uppercase tracking-wide text-[#0c8f86]">Akun PIC</p>
      <h1 class="mt-1 text-2xl font-bold text-slate-900">Akses dan sandi</h1>
      <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Periksa email masuk dan ubah kata sandi akun yang digunakan untuk membuka PIELLOT.</p>
    </section>

    <div class="grid gap-5 lg:grid-cols-[0.9fr_1.1fr]">
      <div class="space-y-3 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="font-bold text-slate-900">Identitas akun</h2>
        <div class="rounded-xl bg-slate-50 p-3">
          <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Nama PIC</p>
          <p class="mt-1 font-semibold text-slate-800">{{ account.name }}</p>
        </div>
        <div class="rounded-xl bg-slate-50 p-3">
          <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Email masuk</p>
          <p class="mt-1 break-all font-semibold text-slate-800">{{ account.email }}</p>
        </div>
        <div class="rounded-xl bg-slate-50 p-3">
          <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Perusahaan</p>
          <p class="mt-1 font-semibold text-slate-800">{{ company ?? '—' }}</p>
        </div>
      </div>

      <form class="space-y-4 rounded-3xl border border-[#9ddbd5] bg-white p-6 shadow-sm" @submit.prevent="submit">
        <h2 class="font-bold text-[#082b55]">Ubah kata sandi</h2>
        <div>
          <label class="block text-sm font-medium text-slate-700">Kata sandi saat ini</label>
          <input v-model="form.current_password" type="password" required autocomplete="current-password" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
          <p v-if="form.errors.current_password" class="mt-1 text-sm text-rose-600">{{ form.errors.current_password }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Kata sandi baru</label>
          <input v-model="form.password" type="password" required autocomplete="new-password" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
          <p v-if="form.errors.password" class="mt-1 text-sm text-rose-600">{{ form.errors.password }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Ulangi kata sandi baru</label>
          <input v-model="form.password_confirmation" type="password" required autocomplete="new-password" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
        </div>
        <button :disabled="form.processing" class="rounded-xl bg-[#0c8f86] px-4 py-2.5 font-bold text-white hover:bg-[#087e75] disabled:opacity-60">Simpan kata sandi</button>
      </form>
    </div>
  </div>
</template>
