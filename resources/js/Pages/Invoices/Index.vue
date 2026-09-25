<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '../../Layouts/AppLayout.vue';
import StatusBadge from '../../Components/StatusBadge.vue';

defineOptions({ layout: AppLayout });
defineProps({ bookings: Array });

const isAdmin = usePage().props.auth.user.isAdmin;
const packages = { '4h': '4 Jam', '8h': '8 Jam', '24h': '24 Jam' };
const rupiah = (value) => 'Rp ' + Number(value).toLocaleString('id-ID');
const shortDate = (value) => new Date(`${value}T12:00:00`).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
</script>

<template>
  <Head title="Tagihan" />
  <div class="mb-5">
    <p class="text-xs font-bold uppercase tracking-wide text-[#0c8f86]">Keuangan</p>
    <h1 class="mt-1 text-2xl font-bold text-slate-900">Tagihan booking</h1>
  </div>
  <div class="grid gap-3 lg:grid-cols-2">
    <Link v-for="booking in bookings" :key="booking.id" :href="route('invoices.show', booking.id)" class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm hover:border-[#8acbc4] hover:shadow-md">
      <span class="min-w-0">
        <span class="block truncate font-semibold text-slate-900">{{ isAdmin ? booking.company.name : booking.vehicle.plate }}</span>
        <span class="mt-1 block text-sm text-slate-500">{{ shortDate(booking.booking_date) }} &middot; {{ packages[booking.package_code] }}</span>
        <strong class="mt-3 block text-sm text-[#0a2c58]">{{ rupiah(booking.total_fee) }}</strong>
      </span>
      <StatusBadge :status="booking.paid ? 'ACTIVE' : 'PENDING'" />
    </Link>
    <p v-if="!bookings.length" class="rounded-2xl border border-dashed border-slate-300 bg-white/60 px-6 py-10 text-center text-slate-500 lg:col-span-2">Belum ada tagihan.</p>
  </div>
</template>
