<script setup>
import { Head, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '../../Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

defineProps({ pendingCompanies: Array, pendingAdminRequests: Array });

const setCompanyStatus = (company, status) => {
  if (status === 'REJECTED' && !confirm(`Tolak ${company.name}?`)) return;
  router.post(route('companies.status', company.id), { status });
};

const setRequestStatus = (request, status) => {
  if (status === 'REJECTED' && !confirm('Tolak permintaan ini?')) return;
  router.post(route('admin-requests.status', request.id), { status });
};
</script>

<template>
  <Head title="Approval" />
  <div class="space-y-10">
    <section>
      <p class="text-xs font-bold uppercase tracking-wide text-[#0c8f86]">Registrasi</p>
      <h2 class="mt-1 text-2xl font-bold text-slate-900">Perusahaan baru</h2>
      <div class="mt-4 grid gap-4 md:grid-cols-2">
        <div v-for="company in pendingCompanies" :key="company.id" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="font-semibold text-slate-900">{{ company.name }}</p>
              <p class="mt-1 text-sm text-slate-500">PIC: {{ company.primary_pic_name }}</p>
            </div>
            <span class="rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-[11px] font-bold text-amber-800">Menunggu</span>
          </div>
          <div class="mt-3 space-y-1 text-sm text-slate-500">
            <p>{{ company.email }}</p>
            <p>{{ company.phone }}</p>
          </div>
          <div class="mt-4 flex gap-2">
            <button class="flex-1 rounded-xl bg-[#0c8f86] px-3 py-2 text-sm font-semibold text-white hover:bg-[#087e75]" @click="setCompanyStatus(company, 'ACTIVE')">Setujui</button>
            <button class="flex-1 rounded-xl border border-rose-200 px-3 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-50" @click="setCompanyStatus(company, 'REJECTED')">Tolak</button>
          </div>
        </div>
        <p v-if="!pendingCompanies.length" class="rounded-2xl border border-dashed border-slate-300 bg-white/60 px-6 py-10 text-center text-slate-500 md:col-span-2">Semua registrasi telah ditinjau.</p>
      </div>
    </section>

    <section>
      <p class="text-xs font-bold uppercase tracking-wide text-[#0c8f86]">Akses perusahaan</p>
      <h2 class="mt-1 text-2xl font-bold text-slate-900">Admin tambahan</h2>
      <div class="mt-4 grid gap-4 md:grid-cols-2">
        <div v-for="request in pendingAdminRequests" :key="request.id" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="font-semibold text-slate-900">{{ request.requested_name }}</p>
              <p class="mt-1 text-sm text-slate-500">{{ request.company.name }}</p>
            </div>
            <span class="rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-[11px] font-bold text-amber-800">Menunggu</span>
          </div>
          <p class="mt-3 text-sm text-slate-500">{{ request.requested_email }}</p>
          <div class="mt-4 flex gap-2">
            <button class="flex-1 rounded-xl bg-[#0c8f86] px-3 py-2 text-sm font-semibold text-white hover:bg-[#087e75]" @click="setRequestStatus(request, 'APPROVED')">Setujui</button>
            <button class="flex-1 rounded-xl border border-rose-200 px-3 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-50" @click="setRequestStatus(request, 'REJECTED')">Tolak</button>
          </div>
        </div>
        <p v-if="!pendingAdminRequests.length" class="rounded-2xl border border-dashed border-slate-300 bg-white/60 px-6 py-10 text-center text-slate-500 md:col-span-2">Tidak ada permintaan admin tambahan.</p>
      </div>
    </section>
  </div>
</template>
