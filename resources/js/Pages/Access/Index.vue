<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import StatusBadge from '../../Components/StatusBadge.vue';

defineOptions({ layout: AppLayout });
const props = defineProps({ accesses: Array, loginUrl: String });

const copiedId = ref(null);

const guide = (access) => `Halo ${access.name},

Akun PIELLOT Anda menggunakan email ${access.email}. Demi keamanan, kata sandi lama tidak dapat dilihat dari PIELLOT.

Jika Anda sudah bisa masuk: buka menu Akun, lalu ubah kata sandi.
Jika Anda belum pernah masuk: buka ${props.loginUrl.replace('/login', '/register')} dan daftar dengan email di atas agar langsung terhubung ke perusahaan ${access.company_name}.
Jika lupa kata sandi: hubungi Admin Fleet agar akun dapat dipulihkan.`;

const copyGuide = async (access) => {
  try {
    await navigator.clipboard.writeText(guide(access));
    copiedId.value = access.id;
    setTimeout(() => { copiedId.value = null; }, 2500);
  } catch {
    window.prompt('Salin panduan berikut:', guide(access));
  }
};
</script>

<template>
  <Head title="Akses PIC" />
  <div class="space-y-6">
    <section>
      <p class="text-xs font-bold uppercase tracking-wide text-[#0c8f86]">Keamanan akun</p>
      <h1 class="mt-1 text-2xl font-bold text-slate-900">Akses dan sandi PIC</h1>
      <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Lihat email akun yang dipakai setiap PIC dan salin panduan saat mereka perlu masuk atau memulihkan akses.</p>
    </section>

    <div class="rounded-2xl border border-[#9ddbd5] bg-[#e9f8f6] p-4 text-[#164d66]">
      <p class="font-semibold text-[#082b55]">Sandi lama tidak dapat ditampilkan</p>
      <p class="mt-1 text-sm leading-6">Kata sandi PIC disimpan dalam bentuk terenkripsi satu arah. Admin dapat membantu proses pemulihan, tetapi tidak dapat membaca atau mengganti sandi milik PIC.</p>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
      <div v-for="access in accesses" :key="access.id" class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <p class="truncate font-semibold text-slate-900">{{ access.name }}</p>
            <p class="mt-1 truncate text-sm text-slate-500">{{ access.company_name }}</p>
          </div>
          <StatusBadge :status="access.status === 'INVITED' ? 'INVITED' : access.status" />
        </div>
        <p class="truncate rounded-xl bg-slate-50 px-3 py-2.5 text-sm text-slate-600">{{ access.email }}</p>
        <button class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold hover:bg-slate-50" @click="copyGuide(access)">
          {{ copiedId === access.id ? 'Panduan tersalin ✓' : 'Salin panduan' }}
        </button>
      </div>
      <p v-if="!accesses.length" class="rounded-2xl border border-dashed border-slate-300 bg-white/60 px-6 py-10 text-center text-slate-500 lg:col-span-2">
        Belum ada akun PIC. Akun PIC akan muncul setelah perusahaan mendaftar atau undangan disetujui.
      </p>
    </div>
  </div>
</template>
