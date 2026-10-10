<script setup>
const props = defineProps({ status: { type: String, required: true }, kind: { type: String, default: 'general' } });

const labels = {
  ACTIVE: 'Aktif', AVAILABLE: 'Tersedia', MAINTENANCE: 'Perawatan', PENDING: 'Menunggu',
  APPROVED: 'Disetujui', INVITED: 'Belum masuk', CONFIRMED: 'Approved', COMPLETED: 'Selesai', REJECTED: 'Ditolak',
};

// Label & warna khusus status booking dan status pembayaran.
const bookingLabels = {
  PENDING: 'Menunggu Approval', APPROVED: 'Approved', CONFIRMED: 'Approved', ON_GOING: 'On Going', COMPLETED: 'Selesai',
  INVOICING: 'Invoicing', PAID: 'Lunas', REJECTED: 'Ditolak',
};
const paymentLabels = { PAID: 'Lunas', UNPAID: 'Belum Lunas' };
const extraClasses = {
  APPROVED: 'border-sky-200 bg-sky-50 text-sky-700',
  CONFIRMED: 'border-sky-200 bg-sky-50 text-sky-700',
  ON_GOING: 'border-indigo-200 bg-indigo-50 text-indigo-700',
  COMPLETED: 'border-teal-200 bg-teal-50 text-teal-700',
  INVOICING: 'border-violet-200 bg-violet-50 text-violet-700',
  PAID: 'border-emerald-200 bg-emerald-50 text-emerald-700',
  UNPAID: 'border-amber-200 bg-amber-50 text-amber-800',
};

const classesByStatus = {
  ACTIVE: 'border-emerald-200 bg-emerald-50 text-emerald-700',
  AVAILABLE: 'border-emerald-200 bg-emerald-50 text-emerald-700',
  COMPLETED: 'border-emerald-200 bg-emerald-50 text-emerald-700',
  APPROVED: 'border-emerald-200 bg-emerald-50 text-emerald-700',
  CONFIRMED: 'border-emerald-200 bg-emerald-50 text-emerald-700',
  PENDING: 'border-amber-200 bg-amber-50 text-amber-800',
  REJECTED: 'border-rose-200 bg-rose-50 text-rose-700',
};
</script>

<template>
  <span class="rounded-full border px-2.5 py-1 text-[11px] font-bold" :class="(kind === 'general' ? classesByStatus[status] : extraClasses[status] ?? classesByStatus[status]) ?? 'border-slate-200 bg-slate-100 text-slate-700'">
    {{ (kind === 'booking' ? bookingLabels : kind === 'payment' ? paymentLabels : labels)[status] ?? status }}
  </span>
</template>
