<script setup>
import { computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '../../Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });
const props = defineProps({ summary: Object });

const resetCount = computed(() => Object.values(props.summary).reduce((a, b) => a + b, 0));

const reset = () => {
  if (!confirm('Reset seluruh data transaksi? Tindakan ini tidak dapat dibatalkan.')) return;
  router.post(route('settings.reset'));
};
</script>

<template>
  <Head title="Pengaturan" />
  <div class="space-y-6">
    <section>
      <p class="text-xs font-bold uppercase tracking-wide text-[#0c8f86]">Administrasi sistem</p>
      <h1 class="mt-1 text-2xl font-bold text-slate-900">Pengaturan data</h1>
      <p class="mt-2 max-w-2xl text-sm text-slate-500">Kelola data operasional yang tersimpan. Akun Admin Fleet dan seluruh armada tidak terpengaruh oleh reset transaksi.</p>
    </section>

    <div class="overflow-hidden rounded-2xl border border-rose-200 bg-white shadow-sm">
      <div class="border-b border-rose-100 bg-rose-50/70 px-6 py-5">
        <h2 class="font-bold text-rose-950">Reset data transaksi</h2>
        <p class="mt-1 text-sm text-rose-800/75">Tindakan ini menghapus data perusahaan, akun PIC, booking beserta tagihan, permintaan admin, dan seluruh pesan chat.</p>
      </div>
      <div class="space-y-5 px-6 py-5">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
          <div class="rounded-xl bg-slate-50 p-3"><p class="text-xs font-semibold uppercase text-slate-400">Perusahaan</p><strong class="mt-1 block text-xl">{{ summary.companies }}</strong></div>
          <div class="rounded-xl bg-slate-50 p-3"><p class="text-xs font-semibold uppercase text-slate-400">Booking & tagihan</p><strong class="mt-1 block text-xl">{{ summary.bookings }}</strong></div>
          <div class="rounded-xl bg-slate-50 p-3"><p class="text-xs font-semibold uppercase text-slate-400">Akun PIC</p><strong class="mt-1 block text-xl">{{ summary.companyAdmins }}</strong></div>
          <div class="rounded-xl bg-slate-50 p-3"><p class="text-xs font-semibold uppercase text-slate-400">Pesan chat</p><strong class="mt-1 block text-xl">{{ summary.messages }}</strong></div>
        </div>

        <div v-if="resetCount > 0" class="flex flex-col gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <p class="font-semibold text-amber-950">Data yang dihapus tidak dapat dikembalikan dari aplikasi.</p>
            <p class="mt-1 text-sm text-amber-800">Pastikan Anda sudah menyimpan ekspor data sebelum melanjutkan.</p>
          </div>
          <button class="rounded-xl bg-rose-600 px-4 py-2.5 font-bold text-white hover:bg-rose-700" @click="reset">Reset data transaksi</button>
        </div>
        <p v-else class="text-sm font-medium text-emerald-700">Data transaksi sudah kosong. Admin dan armada tetap aktif.</p>
      </div>
    </div>
  </div>
</template>
