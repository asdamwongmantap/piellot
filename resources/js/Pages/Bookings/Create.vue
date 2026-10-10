<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '../../Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });
const props = defineProps({ vehicles: Array, packages: Object });

const form = useForm({
  vehicle_id: props.vehicles[0]?.id ?? '',
  package_code: '4h',
  booking_date: '',
  load_ton: '',
  destination: '',
  passengers: 1,
  need_driver: false,
  toll_fee: 0,
});

const areas = [{ key: 'CITY', title: 'Sewa Dalam Kota' }, { key: 'OUTSIDE', title: 'Sewa Luar Kota' }];
const packagesByArea = (area) => Object.entries(props.packages).filter(([, item]) => item.area === area);

const selected = computed(() => props.packages[form.package_code]);
const total = computed(() => (selected.value?.rate ?? 0) + (form.need_driver ? selected.value?.driverRate ?? 0 : 0) + Number(form.toll_fee || 0));

const submit = () => form.post(route('bookings.store'));
const rupiah = (value) => 'Rp ' + Number(value).toLocaleString('id-ID');
const todayIso = new Date().toISOString().slice(0, 10);
</script>

<template>
  <Head title="Booking Baru" />
  <div class="mx-auto max-w-2xl">
    <h1 class="text-2xl font-bold text-slate-900">Buat booking armada</h1>
    <p class="mt-1 text-sm text-slate-500">Booking akan dikirim ke Admin Fleet untuk dikonfirmasi.</p>

    <form class="mt-6 space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" @submit.prevent="submit">
      <div>
        <label class="block text-sm font-medium text-slate-700">Armada tersedia</label>
        <select v-model="form.vehicle_id" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
          <option v-if="!vehicles.length" value="">Tidak ada armada tersedia</option>
          <option v-for="vehicle in vehicles" :key="vehicle.id" :value="vehicle.id">{{ vehicle.plate }} — {{ vehicle.type }} ({{ vehicle.capacity_ton }} ton)</option>
        </select>
        <p v-if="form.errors.vehicle_id" class="mt-1 text-sm text-rose-600">{{ form.errors.vehicle_id }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700">Paket durasi</label>
        <div v-for="area in areas" :key="area.key" v-show="packagesByArea(area.key).length" class="mt-3">
          <p class="text-xs font-bold uppercase tracking-wide text-slate-400">{{ area.title }}</p>
          <div class="mt-2 grid gap-3 sm:grid-cols-2">
          <label v-for="[code, item] in packagesByArea(area.key)" :key="code" class="flex cursor-pointer items-start gap-2 rounded-xl border p-3" :class="form.package_code === code ? 'border-[#0c8f86] bg-[#e5f7f5]' : 'border-slate-200 hover:border-slate-300'">
            <input v-model="form.package_code" type="radio" :value="code" class="mt-1">
            <span>
              <span class="block text-sm font-bold text-slate-800">{{ item.label }}</span>
              <span class="mt-0.5 block text-xs text-slate-500">{{ item.window }}</span>
              <span class="mt-1 block text-xs font-bold text-[#0c8f86]">{{ rupiah(item.rate) }}</span>
              <span class="mt-0.5 block text-xs text-slate-500">Denda telat {{ rupiah(item.lateFee) }}/jam<template v-if="item.toleranceHours"> (toleransi {{ item.toleranceHours }} jam)</template></span>
            </span>
          </label>
        </div>
        </div>
      </div>

      <div class="grid gap-4 sm:grid-cols-2">
        <div>
          <label class="block text-sm font-medium text-slate-700">Tanggal booking</label>
          <input v-model="form.booking_date" type="date" required :min="todayIso" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
          <p v-if="form.errors.booking_date" class="mt-1 text-sm text-rose-600">{{ form.errors.booking_date }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Estimasi muatan (ton)</label>
          <input v-model="form.load_ton" type="number" step="0.1" min="0.1" placeholder="2.5" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
          <p v-if="form.errors.load_ton" class="mt-1 text-sm text-rose-600">{{ form.errors.load_ton }}</p>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700">Tujuan / rute</label>
        <input v-model="form.destination" type="text" required maxlength="160" placeholder="Cikarang – Bekasi" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
      </div>

      <div class="grid gap-4 sm:grid-cols-2">
        <div>
          <label class="block text-sm font-medium text-slate-700">Jumlah kernet</label>
          <input v-model.number="form.passengers" type="number" min="0" max="10" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
        </div>
        <div class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-2">
          <div>
            <label class="text-sm font-medium text-slate-700">Sewa driver</label>
            <p class="text-xs text-slate-500">Tambah {{ rupiah(selected?.driverRate ?? 0) }}</p>
          </div>
          <input v-model="form.need_driver" type="checkbox" class="size-5">
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700">Estimasi biaya tol (Rp)</label>
        <input v-model.number="form.toll_fee" type="number" min="0" step="1000" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
        <p class="mt-1 text-xs text-slate-500">Ditagihkan cost to cost sesuai pemakaian riil. BBM ditanggung Penyewa langsung di lapangan.</p>
        <p v-if="form.errors.toll_fee" class="mt-1 text-sm text-rose-600">{{ form.errors.toll_fee }}</p>
      </div>

      <div class="flex items-center justify-between rounded-xl bg-[#e5f7f5] px-4 py-3">
        <span class="text-sm font-medium text-[#0d746e]">Estimasi total</span>
        <strong class="text-[#075c58]">{{ rupiah(total) }}</strong>
      </div>

      <button :disabled="form.processing" class="w-full rounded-xl bg-[#0c8f86] px-4 py-3 font-bold text-white hover:bg-[#087e75] disabled:opacity-60 sm:w-auto">Kirim booking</button>
    </form>
  </div>
</template>
