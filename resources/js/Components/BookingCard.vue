<script setup>
import { Link } from '@inertiajs/vue3';
import StatusBadge from './StatusBadge.vue';

const props = defineProps({ booking: Object, isAdmin: Boolean });

const packages = {
  '4h': { label: '4 Jam' }, '8h': { label: '8 Jam' }, '24h': { label: '24 Jam' },
};

const rupiah = (value) => 'Rp ' + Number(value).toLocaleString('id-ID');
const shortDate = (value) => new Date(`${value}T12:00:00`).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
</script>

<template>
  <Link :href="route('bookings.show', booking.id)" class="block rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-[#8acbc4] hover:shadow-md">
    <div class="flex items-start justify-between gap-3">
      <div class="min-w-0">
        <p class="truncate font-semibold text-slate-900">{{ isAdmin ? booking.company.name : booking.vehicle.plate }}</p>
        <p class="mt-1 text-sm text-slate-500">{{ isAdmin ? booking.vehicle.plate : booking.vehicle.type }} &middot; {{ packages[booking.package_code].label }}</p>
      </div>
      <StatusBadge :status="booking.status" />
    </div>
    <div class="mt-4 grid gap-2 border-t border-dashed border-slate-200 pt-3 text-sm text-slate-600 sm:grid-cols-3">
      <span>{{ shortDate(booking.booking_date) }}</span>
      <span class="truncate">{{ booking.destination }}</span>
      <span class="sm:text-right">{{ Number(booking.load_ton).toFixed(1) }} ton</span>
    </div>
    <div class="mt-3 flex items-center justify-between text-xs font-semibold text-slate-400">
      <span>{{ rupiah(booking.total_fee) }}</span>
      <span class="text-[#0d746e]">Lihat detail &rarr;</span>
    </div>
  </Link>
</template>
