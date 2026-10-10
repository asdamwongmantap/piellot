<script setup>
import { computed, reactive, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '../../Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });
const props = defineProps({ summary: Object, packages: Array });

const resetCount = computed(() => Object.values(props.summary).reduce((a, b) => a + b, 0));

const reset = () => {
  if (!confirm('Reset seluruh data transaksi? Tindakan ini tidak dapat dibatalkan.')) return;
  router.post(route('settings.reset'));
};

const packagesOpen = ref(false);
const resetOpen = ref(false);

const showPackageForm = ref(false);
const packageForm = useForm({ code: '', label: '', area: 'CITY', window_label: '', start_hour: 8, duration_hours: 4, rate: 0, driver_rate: 0, late_fee: 50000, tolerance_hours: 0 });
const submitPackage = () => packageForm.post(route('settings.packages.store'), {
  onSuccess: () => { packageForm.reset(); showPackageForm.value = false; },
});

const areas = [{ key: 'CITY', title: 'Sewa Dalam Kota' }, { key: 'OUTSIDE', title: 'Sewa Luar Kota' }];
const packagesByArea = (area) => props.packages.filter((pkg) => pkg.area === area);

const fieldDefs = [
  { key: 'label', label: 'Nama paket', hint: 'Tampil di form booking, contoh: 4 Jam', type: 'text' },
  { key: 'window_label', label: 'Jam operasional', hint: 'Teks keterangan, contoh: 08.00–12.00', type: 'text' },
  { key: 'start_hour', label: 'Jam mulai', hint: 'Jam 0–23, awal sewa dihitung', type: 'number', min: 0, max: 23 },
  { key: 'duration_hours', label: 'Durasi hingga kembali (jam)', hint: 'Batas kembali = jam mulai + durasi', type: 'number', min: 1, max: 72 },
  { key: 'rate', label: 'Tarif sewa (Rp)', hint: 'Harga paket tanpa driver', type: 'number', min: 0, step: 1000 },
  { key: 'driver_rate', label: 'Tarif driver (Rp)', hint: 'Ditambahkan jika PIC menyewa driver', type: 'number', min: 0, step: 1000 },
  { key: 'late_fee', label: 'Denda telat (Rp/jam)', hint: 'Per jam keterlambatan, dibulatkan ke atas', type: 'number', min: 0, step: 1000 },
  { key: 'tolerance_hours', label: 'Toleransi (jam)', hint: 'Telat dalam batas ini tidak didenda', type: 'number', min: 0, max: 24 },
];

const editForms = reactive({});
const editForm = (pkg) => {
  if (!editForms[pkg.id]) {
    editForms[pkg.id] = useForm({
      label: pkg.label,
      area: pkg.area,
      window_label: pkg.window_label,
      start_hour: pkg.start_hour,
      duration_hours: pkg.duration_hours,
      rate: pkg.rate,
      driver_rate: pkg.driver_rate,
      late_fee: pkg.late_fee,
      tolerance_hours: pkg.tolerance_hours,
      is_active: pkg.is_active,
    });
  }
  return editForms[pkg.id];
};

const updatePackage = (pkg) => editForm(pkg).put(route('settings.packages.update', pkg.id));
const togglePackage = (pkg) => {
  const form = editForm(pkg);
  form.is_active = !form.is_active;
  form.put(route('settings.packages.update', pkg.id));
};
const destroyPackage = (pkg) => {
  if (!confirm(`Hapus paket ${pkg.label}?`)) return;
  router.delete(route('settings.packages.destroy', pkg.id));
};
</script>

<template>
  <Head title="Pengaturan" />
  <div class="space-y-6">
    <section>
      <p class="text-xs font-bold uppercase tracking-wide text-[#0c8f86]">Administrasi sistem</p>
      <h1 class="mt-1 text-2xl font-bold text-slate-900">Pengaturan data</h1>
      <p class="mt-2 max-w-2xl text-sm text-slate-500">Kelola data operasional yang tersimpan. Akun Admin Fleet dan seluruh armada tidak terpengaruh oleh reset transaksi.</p>
    </section>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
      <div class="flex cursor-pointer items-center justify-between px-6 py-5" :class="{ 'border-b border-slate-100': packagesOpen }" @click="packagesOpen = !packagesOpen">
        <div>
          <h2 class="font-bold text-slate-900">Paket durasi</h2>
          <p class="mt-1 text-sm text-slate-500">Atur label, jam operasional, tarif sewa, tarif driver, dan denda keterlambatan paket yang tampil pada form booking.</p>
        </div>
        <div class="flex shrink-0 items-center gap-3">
          <button v-if="packagesOpen" class="rounded-xl bg-[#0c8f86] px-4 py-2.5 font-bold text-white hover:bg-[#087e75]" @click.stop="showPackageForm = !showPackageForm">+ Tambah paket</button>
          <svg class="size-5 text-slate-400 transition-transform" :class="{ '-rotate-90': !packagesOpen }" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
        </div>
      </div>

      <template v-if="packagesOpen">
        <form v-if="showPackageForm" class="grid gap-4 border-b border-slate-100 bg-slate-50/60 px-6 py-5 sm:grid-cols-2 lg:grid-cols-4" @submit.prevent="submitPackage">
          <div>
            <label class="block text-sm font-medium text-slate-700">Kode paket</label>
            <input v-model="packageForm.code" type="text" required maxlength="8" placeholder="12h" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
            <p class="mt-1 text-xs text-slate-400">Unik, maks. 8 karakter, tidak bisa diubah</p>
            <p v-if="packageForm.errors.code" class="mt-1 text-sm text-rose-600">{{ packageForm.errors.code }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Area layanan</label>
            <select v-model="packageForm.area" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
              <option v-for="area in areas" :key="area.key" :value="area.key">{{ area.title }}</option>
            </select>
            <p class="mt-1 text-xs text-slate-400">Kelompok paket di form booking</p>
          </div>
          <div v-for="field in fieldDefs" :key="field.key">
            <label class="block text-sm font-medium text-slate-700">{{ field.label }}</label>
            <input v-model="packageForm[field.key]" :type="field.type" required :min="field.min" :max="field.max" :step="field.step" :maxlength="field.type === 'text' ? 60 : null" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
            <p class="mt-1 text-xs text-slate-400">{{ field.hint }}</p>
            <p v-if="packageForm.errors[field.key]" class="mt-1 text-sm text-rose-600">{{ packageForm.errors[field.key] }}</p>
          </div>
          <div class="sm:col-span-2 lg:col-span-4">
            <button :disabled="packageForm.processing" class="rounded-xl bg-[#0c8f86] px-4 py-2.5 font-bold text-white hover:bg-[#087e75]">Simpan paket</button>
          </div>
        </form>

        <div>
          <template v-for="area in areas" :key="area.key">
            <p v-if="packagesByArea(area.key).length" class="border-b border-slate-100 bg-slate-50 px-6 py-2 text-xs font-bold uppercase tracking-wide text-slate-500">{{ area.title }}</p>
            <div v-for="pkg in packagesByArea(area.key)" :key="pkg.id" class="border-b border-slate-100 px-6 py-5">
              <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                  <span class="rounded-lg bg-slate-100 px-2 py-1 font-mono text-xs font-bold text-slate-600">{{ pkg.code }}</span>
                  <span class="text-sm font-bold text-slate-900">{{ pkg.label }}</span>
                </div>
                <div class="flex items-center gap-2">
                  <button class="rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold hover:bg-slate-50" :class="editForm(pkg).is_active ? 'text-emerald-700' : 'text-slate-500'" @click="togglePackage(pkg)">
                    {{ editForm(pkg).is_active ? 'Aktif' : 'Nonaktif' }}
                  </button>
                  <button class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-bold text-white hover:bg-slate-700" @click="updatePackage(pkg)">Simpan</button>
                  <button class="rounded-xl px-3 py-2 text-rose-700 hover:bg-rose-50" title="Hapus paket" @click="destroyPackage(pkg)">✕</button>
                </div>
              </div>
              <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                  <label class="block text-xs font-semibold text-slate-600">Area layanan</label>
                  <select v-model="editForm(pkg).area" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                    <option v-for="item in areas" :key="item.key" :value="item.key">{{ item.title }}</option>
                  </select>
                  <p class="mt-1 text-xs text-slate-400">Kelompok paket di form booking</p>
                </div>
                <div v-for="field in fieldDefs" :key="field.key">
                  <label class="block text-xs font-semibold text-slate-600">{{ field.label }}</label>
                  <input v-model="editForm(pkg)[field.key]" :type="field.type" :min="field.min" :max="field.max" :step="field.step" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                  <p class="mt-1 text-xs text-slate-400">{{ field.hint }}</p>
                  <p v-if="editForm(pkg).errors[field.key]" class="mt-1 text-xs text-rose-600">{{ editForm(pkg).errors[field.key] }}</p>
                </div>
              </div>
            </div>
          </template>
          <p v-if="!packages.length" class="px-6 py-5 text-sm text-slate-500">Belum ada paket durasi. Tambahkan minimal satu paket agar PIC bisa membuat booking.</p>
        </div>
      </template>
    </div>

    <div class="overflow-hidden rounded-2xl border border-rose-200 bg-white shadow-sm">
      <div class="flex cursor-pointer items-center justify-between bg-rose-50/70 px-6 py-5" :class="{ 'border-b border-rose-100': resetOpen }" @click="resetOpen = !resetOpen">
        <div>
          <h2 class="font-bold text-rose-950">Reset data transaksi</h2>
          <p class="mt-1 text-sm text-rose-800/75">Tindakan ini menghapus data perusahaan, akun PIC, booking beserta tagihan, permintaan admin, dan seluruh pesan chat.</p>
        </div>
        <svg class="size-5 shrink-0 text-rose-400 transition-transform" :class="{ '-rotate-90': !resetOpen }" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
      </div>
      <div v-if="resetOpen" class="space-y-5 px-6 py-5">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
          <div class="rounded-xl bg-slate-50 p-3"><p class="text-xs font-semibold uppercase text-slate-400">Perusahaan</p><strong class="mt-1 block text-xl">{{ summary.companies }}</strong></div>
          <div class="rounded-xl bg-slate-50 p-3"><p class="text-xs font-semibold uppercase text-slate-400">Booking & tagihan</p><strong class="mt-1 block text-xl">{{ summary.bookings }}</strong></div>
          <div class="rounded-xl bg-slate-50 p-3"><p class="text-xs font-semibold uppercase text-slate-400">Akun PIC</p><strong class="mt-1 block text-xl">{{ summary.companyAdmins }}</strong></div>
          <div class="rounded-xl bg-slate-50 p-3"><p class="text-xs font-semibold uppercase text-slate-400">Pesan chat</p><strong class="mt-1 block text-xl">{{ summary.messages }}</strong></div>
        </div>

        <div v-if="resetCount > 0" class="flex flex-col gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <p class="font-semibold text-amber-950">Data yang dihapus tidak dapat dikembalikan dari aplikasi.</p>
            <p class="mt-1 text-sm text-amber-800">Pastikan Anda sudah menyimpan ekspor data sebelum melanjutkan.</p>
          </div>
          <button class="rounded-xl bg-rose-600 px-4 py-2.5 font-bold text-white hover:bg-rose-700" @click="reset">Reset data transaksi</button>
        </div>
        <p v-else class="text-sm font-medium text-emerald-700">Data transaksi sudah kosong. Admin dan armada tetap aktif.</p>
      </div>
    </div>
  </div>
</template>
