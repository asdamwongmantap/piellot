<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '../../Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });
defineProps({ companyAdmins: Array, adminRequests: Array });

const showForm = ref(false);
const form = useForm({ requested_name: '', requested_email: '' });
const submit = () => form.post(route('admin-requests.store'), { onSuccess: () => { form.reset(); showForm.value = false; } });

const statusLabels = { PENDING: 'Menunggu', APPROVED: 'Disetujui', REJECTED: 'Ditolak' };
const statusClasses = {
  PENDING: 'border-amber-200 bg-amber-50 text-amber-800',
  APPROVED: 'border-emerald-200 bg-emerald-50 text-emerald-700',
  REJECTED: 'border-rose-200 bg-rose-50 text-rose-700',
};
</script>

<template>
  <Head title="Admin PT" />
  <div class="space-y-8">
    <section>
      <div class="mb-4 flex flex-wrap items-end justify-between gap-4">
        <div>
          <p class="text-xs font-bold uppercase tracking-wide text-[#0c8f86]">Akses perusahaan</p>
          <h1 class="mt-1 text-2xl font-bold text-slate-900">Admin booking PT</h1>
        </div>
        <button class="rounded-xl bg-[#0c8f86] px-4 py-2.5 font-bold text-white hover:bg-[#087e75]" @click="showForm = !showForm">+ Tambah admin</button>
      </div>

      <div v-if="showForm" class="mb-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form class="grid gap-4 sm:grid-cols-2" @submit.prevent="submit">
          <div>
            <label class="block text-sm font-medium text-slate-700">Nama admin tambahan</label>
            <input v-model="form.requested_name" type="text" required minlength="2" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
            <p v-if="form.errors.requested_name" class="mt-1 text-sm text-rose-600">{{ form.errors.requested_name }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Email akun</label>
            <input v-model="form.requested_email" type="email" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
            <p v-if="form.errors.requested_email" class="mt-1 text-sm text-rose-600">{{ form.errors.requested_email }}</p>
          </div>
          <div class="sm:col-span-2">
            <button :disabled="form.processing" class="rounded-xl bg-[#0c8f86] px-4 py-2.5 font-bold text-white hover:bg-[#087e75]">Kirim permintaan</button>
          </div>
        </form>
      </div>

      <div class="grid gap-3 md:grid-cols-2">
        <div v-for="admin in companyAdmins" :key="admin.id" class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5">
          <span class="flex size-11 items-center justify-center rounded-full bg-[#e5f7f5] font-bold text-[#087e75]">{{ admin.name.slice(0, 1) }}</span>
          <div class="min-w-0">
            <p class="truncate font-semibold">{{ admin.name }}</p>
            <p class="mt-1 truncate text-sm text-slate-500">{{ admin.email }}</p>
          </div>
        </div>
      </div>
    </section>

    <section>
      <h2 class="mb-3 text-lg font-bold text-slate-900">Riwayat permintaan</h2>
      <div class="space-y-3">
        <div v-for="request in adminRequests" :key="request.id" class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-4">
          <div class="min-w-0">
            <p class="truncate font-semibold">{{ request.requested_name }}</p>
            <p class="mt-1 truncate text-sm text-slate-500">{{ request.requested_email }}</p>
          </div>
          <span class="rounded-full border px-2.5 py-1 text-[11px] font-bold" :class="statusClasses[request.status]">{{ statusLabels[request.status] }}</span>
        </div>
        <p v-if="!adminRequests.length" class="rounded-2xl border border-dashed border-slate-300 bg-white/60 px-6 py-10 text-center text-slate-500">Belum ada permintaan tambahan admin.</p>
      </div>
    </section>
  </div>
</template>
