<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '../../Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

const page = usePage();
const form = useForm({
  company_name: '',
  pic_name: page.props.auth.user.name,
  phone: '',
});

const submit = () => form.post(route('company.store'));
</script>

<template>
  <Head title="Registrasi Perusahaan" />
  <div class="mx-auto max-w-lg">
    <p class="text-sm font-bold uppercase tracking-wide text-[#0c8f86]">Registrasi perusahaan</p>
    <h1 class="mt-1 text-2xl font-bold text-slate-900">Hubungkan akun PIC Anda</h1>
    <p class="mt-2 text-sm text-slate-500">Pendaftaran akan masuk ke Admin Fleet untuk disetujui sebelum Anda dapat membuat booking.</p>

    <form class="mt-6 space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" @submit.prevent="submit">
      <div>
        <label class="block text-sm font-medium text-slate-700">Nama perusahaan</label>
        <input v-model="form.company_name" type="text" required minlength="3" placeholder="Contoh: PT Sumber Jaya" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
        <p v-if="form.errors.company_name" class="mt-1 text-sm text-rose-600">{{ form.errors.company_name }}</p>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700">Nama PIC</label>
        <input v-model="form.pic_name" type="text" required minlength="2" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700">Nomor WhatsApp aktif</label>
        <input v-model="form.phone" type="text" required minlength="8" placeholder="08xxxxxxxxxx" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
      </div>
      <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
        <p class="text-xs font-semibold uppercase text-slate-400">Akun</p>
        <p class="mt-1 text-sm font-medium text-slate-700">{{ page.props.auth.user.email }}</p>
      </div>
      <button :disabled="form.processing" class="w-full rounded-xl bg-[#0c8f86] px-4 py-2.5 font-bold text-white hover:bg-[#087e75] disabled:opacity-60">Ajukan registrasi</button>
    </form>
  </div>
</template>
