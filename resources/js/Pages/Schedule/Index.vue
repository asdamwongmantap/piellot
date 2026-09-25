<script setup>
import { computed, ref } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import BookingCard from '../../Components/BookingCard.vue';

defineOptions({ layout: AppLayout });
const props = defineProps({ bookings: Array });

const isAdmin = usePage().props.auth.user.isAdmin;

const pad = (n) => String(n).padStart(2, '0');
const dateKey = (year, month, day) => `${year}-${pad(month + 1)}-${pad(day)}`;
const todayKey = (() => { const d = new Date(); return dateKey(d.getFullYear(), d.getMonth(), d.getDate()); })();

// booking_date dari Laravel dikirim sebagai ISO datetime; ambil bagian tanggalnya saja.
const dayOf = (booking) => String(booking.booking_date).slice(0, 10);

const now = new Date();
const year = ref(now.getFullYear());
const month = ref(now.getMonth());
const selected = ref(null);

const weekdays = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
const monthLabel = computed(() => new Date(year.value, month.value, 1)
  .toLocaleDateString('id-ID', { month: 'long', year: 'numeric' }));

const byDate = computed(() => {
  const map = {};
  props.bookings.forEach((booking) => { (map[dayOf(booking)] ??= []).push(booking); });
  return map;
});

const cells = computed(() => {
  const offset = (new Date(year.value, month.value, 1).getDay() + 6) % 7; // Senin = 0
  const total = new Date(year.value, month.value + 1, 0).getDate();
  return [...Array(offset).fill(null), ...Array.from({ length: total }, (_, i) => {
    const key = dateKey(year.value, month.value, i + 1);
    const list = byDate.value[key] ?? [];
    return { day: i + 1, key, booked: list.length > 0, confirmed: list.some((b) => b.status === 'CONFIRMED') };
  })];
});

const visible = computed(() => {
  if (selected.value) return byDate.value[selected.value] ?? [];
  const prefix = `${year.value}-${pad(month.value + 1)}-`;
  return props.bookings.filter((booking) => dayOf(booking).startsWith(prefix));
});

const heading = computed(() => selected.value
  ? new Date(`${selected.value}T12:00:00`).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
  : monthLabel.value);

const shift = (delta) => {
  const d = new Date(year.value, month.value + delta, 1);
  year.value = d.getFullYear();
  month.value = d.getMonth();
  selected.value = null;
};
const pick = (cell) => { selected.value = selected.value === cell.key ? null : cell.key; };
</script>

<template>
  <Head title="Jadwal" />
  <div class="grid gap-6 xl:grid-cols-[380px_1fr]">
    <div class="h-fit overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
      <div class="bg-[#0a2c58] px-5 py-5 text-white">
        <h2 class="font-bold">Kalender armada</h2>
        <p class="text-sm text-blue-100/65">Pilih tanggal untuk melihat jadwal.</p>
      </div>
      <div class="p-4">
        <div class="mb-3 flex items-center justify-between">
          <button class="rounded-lg px-3 py-1.5 text-slate-600 hover:bg-slate-100" aria-label="Bulan sebelumnya" @click="shift(-1)">&larr;</button>
          <span class="font-semibold capitalize text-slate-900">{{ monthLabel }}</span>
          <button class="rounded-lg px-3 py-1.5 text-slate-600 hover:bg-slate-100" aria-label="Bulan berikutnya" @click="shift(1)">&rarr;</button>
        </div>
        <div class="grid grid-cols-7 text-center text-xs font-semibold text-slate-400">
          <span v-for="name in weekdays" :key="name" class="py-2">{{ name }}</span>
        </div>
        <div class="grid grid-cols-7 gap-y-1">
          <template v-for="(cell, index) in cells" :key="index">
            <span v-if="!cell" />
            <button v-else type="button"
                    class="relative mx-auto flex size-10 items-center justify-center rounded-xl text-sm transition"
                    :class="[
                      selected === cell.key ? 'bg-[#0a2c58] font-bold text-white' : 'text-slate-700 hover:bg-slate-100',
                      cell.key === todayKey && selected !== cell.key ? 'ring-1 ring-[#0c8f86]' : '',
                    ]"
                    @click="pick(cell)">
              {{ cell.day }}
              <i v-if="cell.booked" class="absolute bottom-1 left-1/2 size-1 -translate-x-1/2 rounded-full"
                 :class="cell.confirmed ? 'bg-[#0c8f86]' : 'bg-amber-500'" />
            </button>
          </template>
        </div>
        <div class="mt-4 flex flex-wrap gap-4 border-t border-slate-100 pt-4 text-xs text-slate-500">
          <span class="flex items-center gap-2"><i class="size-2 rounded-full bg-amber-500" /> Ada booking</span>
          <span class="flex items-center gap-2"><i class="size-2 rounded-full bg-[#0c8f86]" /> Dikonfirmasi</span>
        </div>
      </div>
    </div>

    <section>
      <div class="mb-4 flex items-center justify-between gap-3">
        <div>
          <p class="text-xs font-bold uppercase tracking-wide text-[#0c8f86]">{{ selected ? 'Tanggal terpilih' : 'Bulan terpilih' }}</p>
          <h1 class="mt-1 text-2xl font-bold capitalize text-slate-900">{{ heading }}</h1>
        </div>
        <button v-if="selected" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold hover:bg-slate-50" @click="selected = null">Lihat sebulan</button>
      </div>
      <div class="space-y-3">
        <BookingCard v-for="booking in visible" :key="booking.id" :booking="booking" :is-admin="isAdmin" />
        <p v-if="!visible.length" class="rounded-2xl border border-dashed border-slate-300 bg-white/60 px-6 py-10 text-center text-slate-500">
          Tidak ada jadwal. Belum ada booking aktif pada periode ini.
        </p>
      </div>
    </section>
  </div>
</template>
