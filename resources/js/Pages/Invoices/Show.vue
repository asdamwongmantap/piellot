<script setup>
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '../../Layouts/AppLayout.vue';
import StatusBadge from '../../Components/StatusBadge.vue';
import { usePage } from '@inertiajs/vue3';

defineOptions({ layout: AppLayout });
const props = defineProps({ booking: Object, paymentMethods: Object });

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

const showMethods = ref(false);
const canPay = computed(() => props.booking.status === 'INVOICING' && !props.booking.paid);
const canEditFees = computed(() => props.booking.status !== 'PAID');
const invoiceNo = computed(() => 'INV-' + String(props.booking.id).padStart(5, '0'));
const methodLabel = computed(() => props.paymentMethods[props.booking.payment_method] ?? null);
const pay = (method) => router.post(route('invoices.pay', props.booking.id), { payment_method: method }, { onSuccess: () => { showMethods.value = false; } });
const setPaid = (paid) => router.post(route('invoices.payment-status', props.booking.id), { paid });
const printInvoice = () => window.print();
</script>

<template>
  <Head title="Detail Tagihan" />
  <div class="mx-auto max-w-xl print:max-w-none">
  <div class="print:hidden">
    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
      <p class="font-semibold text-slate-900">{{ booking.company.name }}</p>
      <p class="mt-1 text-sm text-slate-500">{{ booking.vehicle.plate }} &middot; {{ shortDate(booking.booking_date) }}</p>
      <p class="mt-1 text-xs text-slate-400">Invoice {{ invoiceNo }}</p>
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

    <form v-if="isAdmin && canEditFees" class="mt-5 print:hidden grid gap-3 sm:grid-cols-2" @submit.prevent="updateFee(false)">
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

    <div class="mt-5 space-y-2 rounded-xl border border-slate-200 p-3 text-sm">
      <div class="flex items-center justify-between">
        <span class="text-slate-500">Status booking</span>
        <StatusBadge :status="booking.status" kind="booking" />
      </div>
      <div class="flex items-center justify-between">
        <span class="text-slate-500">Status pembayaran</span>
        <StatusBadge :status="booking.paid ? 'PAID' : 'UNPAID'" kind="payment" />
      </div>
      <div v-if="methodLabel" class="flex items-center justify-between">
        <span class="text-slate-500">Metode pembayaran</span>
        <strong>{{ methodLabel }}</strong>
      </div>
    </div>

    <p v-if="methodLabel && !booking.paid" class="mt-3 rounded-xl bg-amber-50 px-3 py-2 text-xs text-amber-800 print:hidden">
      Silakan lakukan transfer sesuai total tagihan. Admin akan memverifikasi dan menandai pembayaran lunas.
    </p>

    <div class="mt-4 space-y-2 print:hidden">
      <button v-if="canPay" class="w-full rounded-xl bg-[#0c8f86] px-4 py-2.5 font-bold text-white hover:bg-[#087e75]" @click="showMethods = true">Bayar</button>
      <button class="w-full rounded-xl border border-slate-300 px-4 py-2.5 font-semibold hover:bg-slate-50" @click="printInvoice">Cetak Invoice</button>

      <div v-if="isAdmin && ['INVOICING', 'PAID'].includes(booking.status)" class="rounded-xl border border-slate-200 p-3">
        <label class="block text-sm font-medium text-slate-700">Ubah status pembayaran</label>
        <select :value="booking.paid ? '1' : '0'" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2" @change="setPaid($event.target.value === '1')">
          <option value="0">Belum Lunas</option>
          <option value="1">Lunas</option>
        </select>
        <p class="mt-1 text-xs text-slate-500">Menandai lunas akan mengubah status booking menjadi Lunas.</p>
      </div>
    </div>

    <div v-if="showMethods" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 print:hidden" @click.self="showMethods = false">
      <div class="w-full max-w-sm rounded-2xl bg-white p-5 shadow-xl">
        <h2 class="text-lg font-bold text-slate-900">Pilih metode pembayaran</h2>
        <div class="mt-4 space-y-2">
          <button v-for="(label, code) in paymentMethods" :key="code" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-left font-semibold hover:border-[#0c8f86] hover:bg-slate-50" @click="pay(code)">{{ label }}</button>
        </div>
        <button class="mt-4 w-full text-sm font-semibold text-slate-500 hover:underline" @click="showMethods = false">Batal</button>
      </div>
    </div>
  </div>

  <!-- Tampilan cetak: invoice resmi (hanya muncul saat print) -->
  <div class="hidden text-sm text-slate-800 print:block">
    <div class="flex items-start justify-between border-b-2 border-slate-800 pb-4">
      <div class="flex items-center gap-3">
        <img src="/logo-multikon.png" alt="PT. Multikon Sarana Indonesia" class="h-20 w-auto">
        <div>
          <p class="text-xl font-bold tracking-wide">PT. MULTIKON SARANA INDONESIA</p>
          <p class="mt-1 max-w-xs text-xs text-slate-500">Jl. Jababeka IVC, Pasirgombong, Kec. Cikarang Utara, Kabupaten Bekasi, Jawa Barat 17530</p>
        </div>
      </div>
      <div class="text-right">
        <p class="inline-block rounded border-2 border-slate-800 px-3 py-0.5 text-base font-bold tracking-[0.3em]">INVOICE</p>
        <p class="mt-2 text-xs text-slate-500">{{ invoiceNo }}</p>
      </div>
    </div>

    <div class="mt-6 grid grid-cols-2 gap-6">
      <div>
        <p class="text-xs font-semibold uppercase text-slate-500">Ditagihkan kepada</p>
        <p class="mt-1 font-semibold">{{ booking.company.name }}</p>
        <p>PIC: {{ booking.pic_name }}</p>
        <p v-if="booking.company.phone">{{ booking.company.phone }}</p>
        <p v-if="booking.company.email">{{ booking.company.email }}</p>
      </div>
      <div class="text-right">
        <p><span class="text-slate-500">Tanggal invoice:</span> {{ shortDate(booking.paid_at ?? new Date().toISOString()) }}</p>
        <p><span class="text-slate-500">Tanggal sewa:</span> {{ shortDate(booking.booking_date) }}</p>
        <p><span class="text-slate-500">Armada:</span> {{ booking.vehicle.plate }} &middot; {{ booking.vehicle.type }}</p>
        <p><span class="text-slate-500">Rute:</span> {{ booking.destination }}</p>
        <p class="mt-2">
          <span class="rounded border px-2 py-0.5 text-xs font-bold" :class="booking.paid ? 'border-emerald-600 text-emerald-700' : 'border-amber-600 text-amber-700'">{{ booking.paid ? 'LUNAS' : 'BELUM LUNAS' }}</span>
        </p>
      </div>
    </div>

    <table class="mt-6 w-full border-collapse">
      <thead>
        <tr class="bg-slate-100 text-left text-xs uppercase text-slate-600">
          <th class="border border-slate-300 px-3 py-2">Deskripsi</th>
          <th class="border border-slate-300 px-3 py-2 text-right">Jumlah</th>
        </tr>
      </thead>
      <tbody>
        <tr><td class="border border-slate-300 px-3 py-2">Sewa armada {{ booking.package_label }} ({{ booking.package_window }})</td><td class="border border-slate-300 px-3 py-2 text-right">{{ rupiah(booking.rental_fee) }}</td></tr>
        <tr v-if="booking.driver_fee > 0"><td class="border border-slate-300 px-3 py-2">Driver</td><td class="border border-slate-300 px-3 py-2 text-right">{{ rupiah(booking.driver_fee) }}</td></tr>
        <tr v-if="booking.toll_fee > 0"><td class="border border-slate-300 px-3 py-2">Tol (cost to cost)</td><td class="border border-slate-300 px-3 py-2 text-right">{{ rupiah(booking.toll_fee) }}</td></tr>
        <tr v-if="booking.late_fee > 0"><td class="border border-slate-300 px-3 py-2">Denda keterlambatan</td><td class="border border-slate-300 px-3 py-2 text-right">{{ rupiah(booking.late_fee) }}</td></tr>
        <tr v-if="booking.other_fee > 0"><td class="border border-slate-300 px-3 py-2">Biaya lain</td><td class="border border-slate-300 px-3 py-2 text-right">{{ rupiah(booking.other_fee) }}</td></tr>
      </tbody>
      <tfoot>
        <tr class="font-bold"><td class="border border-slate-300 px-3 py-2 text-right">Total tagihan</td><td class="border border-slate-300 bg-slate-100 px-3 py-2 text-right">{{ rupiah(booking.total_fee) }}</td></tr>
      </tfoot>
    </table>

    <p v-if="methodLabel" class="mt-4">Metode pembayaran: <strong>{{ methodLabel }}</strong></p>
    <p class="mt-4 text-xs text-slate-500">BBM ditanggung Penyewa langsung di lapangan dan tidak termasuk tagihan ini.</p>

    <div class="mt-12 flex justify-end">
      <div class="w-48 text-center">
        <p class="text-xs text-slate-500">Hormat kami,</p>
        <div class="h-16"></div>
        <p class="border-t border-slate-400 pt-1 font-semibold">PT. MULTIKON SARANA INDONESIA</p>
      </div>
    </div>
    <p class="mt-8 text-center text-xs text-slate-400">Terima kasih atas kepercayaan Anda.</p>
  </div>
  </div>
</template>
