<script setup>
import { Head, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '../../Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

const props = defineProps({ account: Object, company: Object });
const rejected = props.account.status === 'REJECTED';
const refresh = () => router.reload();
const logout = () => router.post(route('logout'));
</script>

<template>
  <Head title="Menunggu Persetujuan" />
  <div class="mx-auto max-w-lg rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm">
    <div class="mx-auto flex size-16 items-center justify-center rounded-2xl" :class="rejected ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-800'">
      <span class="text-2xl">{{ rejected ? '✕' : '⏳' }}</span>
    </div>
    <h1 class="mt-6 text-xl font-bold text-slate-900">{{ rejected ? 'Registrasi belum disetujui' : 'Menunggu persetujuan Admin' }}</h1>
    <p class="mx-auto mt-3 max-w-sm text-sm leading-6 text-slate-500">
      <template v-if="rejected">Pendaftaran {{ company?.name ?? 'perusahaan Anda' }} ditolak. Hubungi Admin Fleet untuk pemeriksaan ulang.</template>
      <template v-else>Pendaftaran {{ company?.name ?? 'perusahaan Anda' }} sudah tercatat. Halaman akan aktif setelah Admin Fleet menyetujui akun PIC Anda.</template>
    </p>
    <div class="mt-7 flex justify-center gap-3">
      <button class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold hover:bg-slate-50" @click="refresh">Periksa status</button>
      <button class="rounded-xl px-4 py-2 text-sm font-semibold text-slate-500 hover:bg-slate-50" @click="logout">Keluar</button>
    </div>
  </div>
</template>
