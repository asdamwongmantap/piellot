<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '../../Layouts/AppLayout.vue';
import BookingCard from '../../Components/BookingCard.vue';

defineOptions({ layout: AppLayout });
defineProps({ bookings: Array });

const isAdmin = usePage().props.auth.user.isAdmin;
</script>

<template>
  <Head title="Booking" />
  <div class="mb-5 flex flex-wrap items-end justify-between gap-4">
    <div>
      <p class="text-xs font-bold uppercase tracking-wide text-[#0c8f86]">Riwayat operasional</p>
      <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ isAdmin ? 'Semua booking' : 'Booking perusahaan' }}</h1>
    </div>
    <Link v-if="!isAdmin" :href="route('bookings.create')" class="rounded-xl bg-[#0c8f86] px-4 py-2.5 font-bold text-white hover:bg-[#087e75]">+ Booking baru</Link>
  </div>
  <div class="grid gap-3 lg:grid-cols-2">
    <BookingCard v-for="booking in bookings" :key="booking.id" :booking="booking" :is-admin="isAdmin" />
    <p v-if="!bookings.length" class="rounded-2xl border border-dashed border-slate-300 bg-white/60 px-6 py-10 text-center text-slate-500 lg:col-span-2">Belum ada riwayat booking.</p>
  </div>
</template>
