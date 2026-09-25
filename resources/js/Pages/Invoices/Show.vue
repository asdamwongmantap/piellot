<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '../../Layouts/AppLayout.vue';
import StatusBadge from '../../Components/StatusBadge.vue';
import { usePage } from '@inertiajs/vue3';

defineOptions({ layout: AppLayout });
const props = defineProps({ booking: Object });

const isAdmin = usePage().props.auth.user.isAdmin;
const packages = { '4h': '4 Jam', '8h': '8 Jam', '24h': '24 Jam' };
const rupiah = (value) => 'Rp ' + Number(value).toLocaleString('id-ID');
const shortDate = (value) => new Date(`${value}T12:00:00`).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });

const otherFee = ref(props.booking.other_fee);
const updateFee = () => router.post(route('invoices.update', props.booking.id), { other_fee: otherFee.value });
const markPaid = () => router.post(route('invoices.paid', props.booking.id));
</script>

<template>
  <Head title="Detail Tagihan" />
  <div class="mx-auto max-w-xl">
    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
      <p class="font-semibold text-slate-900">{{ booking.company.name }}</p>
      <p class="mt-1 text-sm text-slate-500">{{ booking.vehicle.plate }} &middot; {{ shortDate(booking.booking_date) }}</p>
    </div>

    <div class="mt-5 space-y-3 text-sm">
      <div class="flex justify-between"><span class="text-slate-500">Sewa {{ packages[booking.package_code] }}</span><strong>{{ rupiah(booking.rental_fee) }}</strong></div>
      <div class="flex justify-between"><span class="text-slate-500">Driver</span><strong>{{ rupiah(booking.driver_fee) }}</strong></div>
      <div class="flex justify-between"><span class="text-slate-500">BBM, tol, dan lainnya</span><strong>{{ rupiah(booking.other_fee) }}</strong></div>
      <div class="flex justify-between border-t border-dashed border-slate-300 pt-3 text-base"><span class="font-semibold">Total tagihan</span><strong class="text-[#0a2c58]">{{ rupiah(booking.total_fee) }}</strong></div>
    </div>

    <form v-if="isAdmin" class="mt-5 flex items-end gap-3" @submit.prevent="updateFee">
      <div class="flex-1">
        <label class="block text-sm font-medium text-slate-700">Perbarui biaya lain</label>
        <input v-model.number="otherFee" type="number" min="0" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
      </div>
      <button class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold hover:bg-slate-50">Perbarui</button>
    </form>

    <div class="mt-5 flex items-center justify-between rounded-xl border border-slate-200 p-3">
      <span class="text-sm text-slate-500">Status pembayaran</span>
      <StatusBadge :status="booking.paid ? 'ACTIVE' : 'PENDING'" />
    </div>

    <button v-if="isAdmin && !booking.paid" class="mt-4 w-full rounded-xl bg-[#0c8f86] px-4 py-2.5 font-bold text-white hover:bg-[#087e75]" @click="markPaid">Tandai lunas</button>
  </div>
</template>
