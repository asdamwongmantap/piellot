<script setup>
import { computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

const page = usePage();
const user = computed(() => page.props.auth.user);
const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

const navItemsAdmin = [
  ['dashboard', 'Ringkasan'],
  ['bookings.index', 'Booking'],
  ['invoices.index', 'Tagihan'],
  ['chat.index', 'Chat'],
];
const navItemsPic = [
  ['dashboard', 'Ringkasan'],
  ['bookings.index', 'Booking'],
  ['invoices.index', 'Tagihan'],
  ['chat.index', 'Chat'],
];

const logout = () => router.post(route('logout'));
</script>

<template>
  <div>
    <header v-if="user" class="sticky top-0 z-40 bg-[#082b55] text-white shadow">
      <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4">
        <Link :href="route('dashboard')" class="font-extrabold tracking-tight">
          PIELLOT <span class="text-xs font-normal text-blue-200">Fleet Booking</span>
        </Link>
        <div class="flex items-center gap-4 text-sm">
          <span class="hidden text-blue-100 sm:inline">{{ user.name }} &middot; {{ user.isAdmin ? 'Admin Fleet' : (user.companyName ?? 'PIC') }}</span>
          <button class="rounded-lg bg-white/10 px-3 py-1.5 hover:bg-white/20" @click="logout">Keluar</button>
        </div>
      </div>
      <nav v-if="user.status === 'ACTIVE'" class="border-t border-white/10 bg-[#0a2c58]">
        <div class="mx-auto flex max-w-6xl gap-1 overflow-x-auto px-4 text-sm">
          <Link v-for="[routeName, label] in (user.isAdmin ? navItemsAdmin : navItemsPic)" :key="routeName"
                :href="route(routeName)"
                class="whitespace-nowrap px-3 py-3"
                :class="route().current(routeName) ? 'border-b-2 border-[#18b9ad] font-semibold text-white' : 'text-blue-100/70 hover:text-white'">
            {{ label }}
          </Link>
          <template v-if="user.isAdmin">
            <Link :href="route('vehicles.index')" class="whitespace-nowrap px-3 py-3" :class="route().current('vehicles.*') ? 'border-b-2 border-[#18b9ad] font-semibold text-white' : 'text-blue-100/70 hover:text-white'">Armada</Link>
            <Link :href="route('companies.pending')" class="whitespace-nowrap px-3 py-3" :class="route().current('companies.pending') ? 'border-b-2 border-[#18b9ad] font-semibold text-white' : 'text-blue-100/70 hover:text-white'">Approval</Link>
            <a :href="route('export.download')" class="whitespace-nowrap px-3 py-3 text-blue-100/70 hover:text-white">Export Excel</a>
            <Link :href="route('settings.index')" class="whitespace-nowrap px-3 py-3" :class="route().current('settings.*') ? 'border-b-2 border-[#18b9ad] font-semibold text-white' : 'text-blue-100/70 hover:text-white'">Pengaturan</Link>
          </template>
          <template v-else>
            <Link :href="route('team.index')" class="whitespace-nowrap px-3 py-3" :class="route().current('team.*') ? 'border-b-2 border-[#18b9ad] font-semibold text-white' : 'text-blue-100/70 hover:text-white'">Admin PT</Link>
          </template>
        </div>
      </nav>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-8">
      <div v-if="flashSuccess" class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">{{ flashSuccess }}</div>
      <div v-if="flashError" class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800">{{ flashError }}</div>
      <slot />
    </main>
  </div>
</template>
