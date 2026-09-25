<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '../Layouts/AppLayout.vue';
import Metric from '../Components/Metric.vue';
import BookingCard from '../Components/BookingCard.vue';

defineOptions({ layout: AppLayout });

const props = defineProps({
  isAdmin: Boolean,
  recentBookings: Array,
  pendingBookingsCount: Number,
  confirmedBookingsCount: Number,
  totalBookingsCount: Number,
  vehiclesCount: Number,
  availableVehiclesCount: Number,
  paidRevenue: Number,
  unpaidTotal: Number,
  pendingCompaniesCount: Number,
  pendingAdminRequestsCount: Number,
});

const page = usePage();
const firstName = computed(() => page.props.auth.user.name.split(' ')[0]);
const rupiah = (value) => 'Rp ' + Number(value).toLocaleString('id-ID');
const hasPending = computed(() => props.pendingCompaniesCount + props.pendingAdminRequestsCount + props.pendingBookingsCount > 0);
const pendingLink = computed(() => (props.pendingCompaniesCount + props.pendingAdminRequestsCount) ? route('companies.pending') : route('bookings.index'));
</script>

<template>
  <Head title="Ringkasan" />
  <div class="space-y-6">
    <section class="rounded-2xl bg-[#0a2c58] p-8 text-white">
      <p class="text-sm font-bold uppercase tracking-wide text-[#6bd8ce]">{{ isAdmin ? 'Fleet control center' : 'Ringkasan perusahaan' }}</p>
      <h1 class="mt-2 text-3xl font-extrabold">{{ isAdmin ? 'Operasional hari ini' : `Halo, ${firstName}` }}</h1>
      <p class="mt-3 max-w-xl text-sm text-blue-100/70">{{ isAdmin ? 'Pantau permintaan, ketersediaan armada, dan pemasukan dari satu tampilan.' : 'Atur jadwal armada dan pantau setiap booking perusahaan Anda.' }}</p>
      <Link v-if="!isAdmin" :href="route('bookings.create')" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-[#18b9ad] px-5 py-3 font-bold text-[#06254b] hover:bg-[#50d0c5]">+ Booking baru</Link>
    </section>

    <Link v-if="isAdmin && hasPending" :href="pendingLink" class="flex items-center justify-between rounded-2xl border border-amber-200 bg-amber-50 p-4 text-amber-950 hover:bg-amber-100">
      <span>
        <span class="block font-bold">Ada tindakan yang menunggu Anda</span>
        <span class="mt-1 block text-sm text-amber-800">{{ pendingCompaniesCount }} perusahaan, {{ pendingAdminRequestsCount }} admin tambahan, dan {{ pendingBookingsCount }} booking menunggu keputusan.</span>
      </span>
      <span>&rarr;</span>
    </Link>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <template v-if="isAdmin">
        <Metric label="Total armada" :value="vehiclesCount" :note="`${availableVehiclesCount} tersedia`" />
        <Metric label="Booking menunggu" :value="pendingBookingsCount" />
        <Metric label="Sedang aktif" :value="confirmedBookingsCount" />
        <Metric label="Pendapatan lunas" :value="rupiah(paidRevenue)" />
      </template>
      <template v-else>
        <Metric label="Total booking" :value="totalBookingsCount" />
        <Metric label="Menunggu" :value="pendingBookingsCount" />
        <Metric label="Dikonfirmasi" :value="confirmedBookingsCount" />
        <Metric label="Belum dibayar" :value="rupiah(unpaidTotal)" />
      </template>
    </section>

    <section>
      <div class="mb-4 flex items-center justify-between">
        <div>
          <h2 class="text-xl font-bold text-slate-900">Booking terbaru</h2>
          <p class="mt-1 text-sm text-slate-500">Perubahan status dan tagihan tercatat secara online.</p>
        </div>
        <Link :href="route('bookings.index')" class="text-sm font-semibold text-[#0c8f86]">Lihat semua &rarr;</Link>
      </div>
      <div class="grid gap-3 lg:grid-cols-2">
        <BookingCard v-for="booking in recentBookings" :key="booking.id" :booking="booking" :is-admin="isAdmin" />
        <p v-if="!recentBookings.length" class="rounded-2xl border border-dashed border-slate-300 bg-white/60 px-6 py-10 text-center text-slate-500 lg:col-span-2">Belum ada booking.</p>
      </div>
    </section>
  </div>
</template>
