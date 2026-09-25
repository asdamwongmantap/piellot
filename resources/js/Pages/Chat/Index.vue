<script setup>
import { ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '../../Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });
const props = defineProps({ companies: Array, thread: Array, companyId: [Number, String, null], activeCompany: Object });

const user = usePage().props.auth.user;
const isAdmin = user.isAdmin;
const body = ref('');

const send = () => {
  if (!body.value.trim()) return;
  router.post(route('chat.send'), { company_id: props.companyId, body: body.value }, { onSuccess: () => (body.value = '') });
};

const messageTime = (value) => new Date(value).toLocaleString('id-ID', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
</script>

<template>
  <Head title="Chat" />
  <div class="grid overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm lg:grid-cols-[280px_1fr]" style="min-height: 520px">
    <aside v-if="isAdmin" class="border-r border-slate-200 bg-slate-50/70 p-3">
      <p class="px-2 pb-2 pt-1 text-xs font-bold uppercase tracking-wide text-[#0c8f86]">Perusahaan aktif</p>
      <div class="space-y-1">
        <Link v-for="item in companies" :key="item.id" :href="route('chat.index', { company_id: item.id })" class="block rounded-xl p-3" :class="companyId == item.id ? 'bg-[#0a2c58] text-white' : 'hover:bg-[#e5f7f5]'">
          <div class="flex items-center justify-between gap-2">
            <span class="truncate text-sm font-semibold">{{ item.name }}</span>
            <span v-if="item.unread_count" class="rounded-full bg-rose-600 px-2 py-0.5 text-[10px] font-bold text-white">{{ item.unread_count }}</span>
          </div>
        </Link>
        <p v-if="!companies.length" class="p-3 text-sm text-slate-500">Belum ada perusahaan aktif.</p>
      </div>
    </aside>

    <div class="flex flex-col">
      <template v-if="companyId && activeCompany">
        <header class="flex items-center gap-3 border-b border-slate-200 px-5 py-4">
          <span class="flex size-10 items-center justify-center rounded-full bg-[#e5f7f5] font-bold text-[#087e75]">{{ activeCompany.name.slice(0, 1) }}</span>
          <h2 class="font-bold text-slate-900">{{ activeCompany.name }}</h2>
        </header>
        <div class="flex-1 space-y-3 overflow-y-auto bg-[#f7f9fc] p-5">
          <div v-for="message in thread" :key="message.id" class="max-w-[85%] rounded-2xl px-4 py-3 text-sm shadow-sm" :class="message.sender_role === user.role ? 'ml-auto rounded-br-md bg-[#0a2c58] text-white' : 'rounded-bl-md border border-slate-200 bg-white text-slate-800'">
            <p class="whitespace-pre-wrap leading-6">{{ message.body }}</p>
            <p class="mt-1.5 text-[11px]" :class="message.sender_role === user.role ? 'text-blue-100/60' : 'text-slate-400'">{{ message.sender_name }} &middot; {{ messageTime(message.created_at) }}</p>
          </div>
          <p v-if="!thread.length" class="m-auto text-center text-sm text-slate-500">Mulai percakapan dengan pesan pertama.</p>
        </div>
        <form class="flex gap-2 border-t border-slate-200 bg-white p-3" @submit.prevent="send">
          <textarea v-model="body" rows="1" maxlength="1500" placeholder="Tulis pesan…" class="min-h-11 flex-1 resize-none rounded-xl border border-slate-300 px-3 py-2"></textarea>
          <button class="shrink-0 rounded-xl bg-[#0c8f86] px-4 py-2 font-semibold text-white hover:bg-[#087e75]">Kirim</button>
        </form>
      </template>
      <div v-else class="m-auto max-w-xs text-center text-slate-500">
        <p class="font-semibold text-slate-700">Pilih perusahaan</p>
        <p class="mt-1 text-sm">Pilih percakapan dari daftar untuk melihat dan membalas pesan.</p>
      </div>
    </div>
  </div>
</template>
