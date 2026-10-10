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
const rupiah = (value) => 'Rp ' + Number(value).toLocaleString('id-ID');
const shortDate = (value) => new Date(`${String(value).slice(0, 10)}T12:00:00`).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });

const otherFee = ref(props.booking.other_fee);
const tollFee = ref(props.booking.toll_fee);
const lateFee = ref(props.booking.late_fee);
const returnedAt = ref(props.booking.returned_at ?? '');
const dueLabel = new Date(props.booking.due_at).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
const updateFee = (auto = false) => router.post(route('invoices.update', props.booking.id), {
  other_fee: otherFee.value, toll_fee: tollFee.value, late_fee: lateFee.value,
  returned_at: returnedAt.value || null, auto_late_fee: auto,
});
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
      <div class="flex justify-between"><span class="text-slate-500">Sewa {{ booking.package_label }} ({{ booking.package_window }})</span><strong>{{ rupiah(booking.rental_fee) }}</strong></div>
      <div class="flex justify-between"><span class="text-slate-500">Driver</span><strong>{{ rupiah(booking.driver_fee) }}</strong></div>
      <div class="flex justify-between"><span class="text-slate-500">Tol (cost to cost)</span><strong>{{ rupiah(booking.toll_fee) }}</strong></div>
      <div class="flex justify-between"><span class="text-slate-500">Denda keterlambatan<span v-if="booking.late_fee_adjusted" class="ml-1 text-xs text-amber-600">(disesuaikan manual)</span></span><strong>{{ rupiah(booking.late_fee) }}</strong></div>
      <div class="flex justify-between"><span class="text-slate-500">Biaya lain</span><strong>{{ rupiah(booking.other_fee) }}</strong></div>
      <div class="flex justify-between border-t border-dashed border-slate-300 pt-3 text-base"><span class="font-semibold">Total tagihan</span><strong class="text-[#0a2c58]">{{ rupiah(booking.total_fee) }}</strong></div>
    </div>

    <p class="mt-3 text-xs text-slate-400">BBM ditanggung Penyewa langsung di lapangan dan tidak termasuk tagihan ini.</p>

    <form v-if="isAdmin" class="mt-5 grid gap-3 sm:grid-cols-2" @submit.prevent="updateFee(false)">
      <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-slate-700">Waktu kembali aktual</label>
        <input v-model="returnedAt" type="datetime-local" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
        <p class="mt-1 text-xs text-slate-500">Batas kembali {{ dueLabel }}. Denda dihitung otomatis setelah toleransi paket.</p>
      </div>
      <div class="flex-1">
        <label class="block text-sm font-medium text-slate-700">Denda keterlambatan (Rp)</label>
        <input v-model.number="lateFee" type="number" min="0" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
        <button v-if="booking.late_fee_adjusted" type="button" class="mt-1 text-xs font-semibold text-[#0c8f86] hover:underline" @click="updateFee(true)">Hitung ulang otomatis</button>
      </div>
      <div class="flex-1">
        <label class="block text-sm font-medium text-slate-700">Biaya tol aktual (Rp)</label>
        <input v-model.number="tollFee" type="number" min="0" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
      </div>
      <div class="flex-1">
        <label class="block text-sm font-medium text-slate-700">Biaya lain (Rp)</label>
        <input v-model.number="otherFee" type="number" min="0" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
      </div>
      <button class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold hover:bg-slate-50 sm:col-span-2">Perbarui</button>
    </form>

    <div class="mt-5 flex items-center justify-between rounded-xl border border-slate-200 p-3">
      <span class="text-sm text-slate-500">Status pembayaran</span>
      <StatusBadge :status="booking.paid ? 'ACTIVE' : 'PENDING'" />
    </div>

    <button v-if="isAdmin && !booking.paid" class="mt-4 w-full rounded-xl bg-[#0c8f86] px-4 py-2.5 font-bold text-white hover:bg-[#087e75]" @click="markPaid">Tandai lunas</button>
  </div>
</template>
