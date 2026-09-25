<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '../../Layouts/AppLayout.vue';
import StatusBadge from '../../Components/StatusBadge.vue';

defineOptions({ layout: AppLayout });
const props = defineProps({ booking: Object });

const isAdmin = usePage().props.auth.user.isAdmin;
const packages = { '4h': { label: '4 Jam', window: '08.00–12.00' }, '8h': { label: '8 Jam', window: '08.00–17.00' }, '24h': { label: '24 Jam', window: '08.00–08.00 (+1 hari)' } };

const rupiah = (value) => 'Rp ' + Number(value).toLocaleString('id-ID');
const longDate = (value) => new Date(`${value}T12:00:00`).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });

const setStatus = (status) => {
  if (status === 'REJECTED' && !confirm('Tolak booking ini?')) return;
  router.post(route('bookings.status', props.booking.id), { status });
};
</script>

<template>
  <Head title="Detail Booking" />
  <div class="mx-auto max-w-2xl">
    <div class="flex items-start justify-between gap-4 rounded-2xl bg-slate-50 p-5">
      <div>
        <p class="font-semibold text-slate-900">{{ booking.company.name }}</p>
        <p class="mt-1 text-sm text-slate-500">PIC: {{ booking.pic_name }}</p>
      </div>
      <StatusBadge :status="booking.status" />
    </div>

    <dl class="mt-6 grid gap-4 text-sm sm:grid-cols-2">
      <div><dt class="text-xs font-semibold uppercase text-slate-400">Armada</dt><dd class="mt-1 font-semibold text-slate-800">{{ booking.vehicle.plate }} &middot; {{ booking.vehicle.type }}</dd></div>
      <div><dt class="text-xs font-semibold uppercase text-slate-400">Jadwal</dt><dd class="mt-1 font-semibold text-slate-800">{{ longDate(booking.booking_date) }}</dd></div>
      <div><dt class="text-xs font-semibold uppercase text-slate-400">Paket</dt><dd class="mt-1 font-semibold text-slate-800">{{ packages[booking.package_code].label }} &middot; {{ packages[booking.package_code].window }}</dd></div>
      <div><dt class="text-xs font-semibold uppercase text-slate-400">Muatan</dt><dd class="mt-1 font-semibold text-slate-800">{{ Number(booking.load_ton).toFixed(1) }} ton &middot; {{ booking.passengers }} kernet</dd></div>
      <div class="sm:col-span-2"><dt class="text-xs font-semibold uppercase text-slate-400">Rute tujuan</dt><dd class="mt-1 font-semibold text-slate-800">{{ booking.destination }}</dd></div>
    </dl>

    <div class="mt-5 flex items-center justify-between rounded-xl bg-[#e5f7f5] px-4 py-3">
      <span class="text-sm font-medium text-[#0d746e]">Estimasi tagihan</span>
      <strong class="text-[#075c58]">{{ rupiah(booking.total_fee) }}</strong>
    </div>

    <div v-if="isAdmin && booking.status === 'PENDING'" class="mt-5 flex gap-2">
      <button class="rounded-xl border border-rose-200 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-50" @click="setStatus('REJECTED')">Tolak</button>
      <button class="rounded-xl bg-[#0c8f86] px-4 py-2 text-sm font-semibold text-white hover:bg-[#087e75]" @click="setStatus('CONFIRMED')">Konfirmasi</button>
    </div>
    <div v-else-if="isAdmin && booking.status === 'CONFIRMED'" class="mt-5">
      <button class="rounded-xl bg-[#0a2c58] px-4 py-2 text-sm font-semibold text-white hover:bg-[#123f73]" @click="setStatus('COMPLETED')">Tandai selesai</button>
    </div>
  </div>
</template>
