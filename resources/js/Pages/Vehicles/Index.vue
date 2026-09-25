<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '../../Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });
defineProps({ vehicles: Array });

const showForm = ref(false);
const form = useForm({ plate: '', type: '', capacity_ton: 3 });

const submit = () => form.post(route('vehicles.store'), { onSuccess: () => { form.reset(); showForm.value = false; } });
const toggle = (vehicle) => router.post(route('vehicles.toggle', vehicle.id));
const destroy = (vehicle) => { if (confirm(`Hapus ${vehicle.plate}?`)) router.delete(route('vehicles.destroy', vehicle.id)); };
</script>

<template>
  <Head title="Armada" />
  <div class="mb-5 flex flex-wrap items-end justify-between gap-4">
    <div>
      <p class="text-xs font-bold uppercase tracking-wide text-[#0c8f86]">Fleet management</p>
      <h1 class="mt-1 text-2xl font-bold text-slate-900">Armada truk</h1>
    </div>
    <button class="rounded-xl bg-[#0c8f86] px-4 py-2.5 font-bold text-white hover:bg-[#087e75]" @click="showForm = !showForm">+ Tambah armada</button>
  </div>

  <div v-if="showForm" class="mb-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="mb-4 font-bold text-slate-900">Tambah armada</h2>
    <form class="grid gap-4 sm:grid-cols-3" @submit.prevent="submit">
      <div>
        <label class="block text-sm font-medium text-slate-700">Plat nomor</label>
        <input v-model="form.plate" type="text" required maxlength="20" placeholder="B 1234 ABC" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 uppercase">
        <p v-if="form.errors.plate" class="mt-1 text-sm text-rose-600">{{ form.errors.plate }}</p>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700">Tipe armada</label>
        <input v-model="form.type" type="text" required maxlength="60" placeholder="Truk Engkel" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700">Kapasitas (ton)</label>
        <input v-model.number="form.capacity_ton" type="number" required min="1" max="30" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
      </div>
      <div class="sm:col-span-3">
        <button :disabled="form.processing" class="rounded-xl bg-[#0c8f86] px-4 py-2.5 font-bold text-white hover:bg-[#087e75]">Simpan armada</button>
      </div>
    </form>
  </div>

  <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
    <div v-for="vehicle in vehicles" :key="vehicle.id" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
      <div class="flex items-start justify-between">
        <span class="flex size-11 items-center justify-center rounded-xl bg-[#e7eef7] font-bold text-[#0a2c58]">🚚</span>
        <span class="rounded-full border px-2.5 py-1 text-[11px] font-bold" :class="vehicle.status === 'AVAILABLE' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-slate-100 text-slate-700'">
          {{ vehicle.status === 'AVAILABLE' ? 'Tersedia' : 'Perawatan' }}
        </span>
      </div>
      <p class="mt-3 text-lg font-bold text-slate-900">{{ vehicle.plate }}</p>
      <p class="text-sm text-slate-500">{{ vehicle.type }}</p>
      <p class="mt-4 flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2 text-sm">
        <span class="text-slate-500">Kapasitas</span>
        <strong>{{ vehicle.capacity_ton }} ton</strong>
      </p>
      <div class="mt-4 flex gap-2">
        <button class="flex-1 rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold hover:bg-slate-50" @click="toggle(vehicle)">Ubah status</button>
        <button class="rounded-xl px-3 py-2 text-rose-700 hover:bg-rose-50" @click="destroy(vehicle)">✕</button>
      </div>
    </div>
  </div>
</template>
