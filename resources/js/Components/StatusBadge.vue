<script setup>
const props = defineProps({ status: { type: String, required: true } });

const labels = {
  ACTIVE: 'Aktif', AVAILABLE: 'Tersedia', MAINTENANCE: 'Perawatan', PENDING: 'Menunggu',
  APPROVED: 'Disetujui', CONFIRMED: 'Dikonfirmasi', COMPLETED: 'Selesai', REJECTED: 'Ditolak',
};

const classesByStatus = {
  ACTIVE: 'border-emerald-200 bg-emerald-50 text-emerald-700',
  AVAILABLE: 'border-emerald-200 bg-emerald-50 text-emerald-700',
  CONFIRMED: 'border-emerald-200 bg-emerald-50 text-emerald-700',
  COMPLETED: 'border-emerald-200 bg-emerald-50 text-emerald-700',
  APPROVED: 'border-emerald-200 bg-emerald-50 text-emerald-700',
  PENDING: 'border-amber-200 bg-amber-50 text-amber-800',
  REJECTED: 'border-rose-200 bg-rose-50 text-rose-700',
};
</script>

<template>
  <span class="rounded-full border px-2.5 py-1 text-[11px] font-bold" :class="classesByStatus[status] ?? 'border-slate-200 bg-slate-100 text-slate-700'">
    {{ labels[status] ?? status }}
  </span>
</template>
