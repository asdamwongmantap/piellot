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
  ['schedule.index', 'Jadwal'],
  ['companies.pending', 'Approval'],
  ['bookings.index', 'Booking'],
  ['vehicles.index', 'Armada'],
  ['invoices.index', 'Tagihan'],
  ['chat.index', 'Chat'],
  ['access.index', 'Akses PIC'],
];
const navItemsPic = [
  ['dashboard', 'Ringkasan'],
  ['schedule.index', 'Jadwal'],
  ['bookings.index', 'Booking'],
  ['invoices.index', 'Tagihan'],
  ['chat.index', 'Chat'],
  ['team.index', 'Admin PT'],
  ['account.show', 'Akun'],
];
const badges = computed(() => page.props.badges ?? {});
const currentPattern = (routeName) => routeName === 'dashboard' ? routeName : routeName.replace(/\.[^.]+$/, '.*');

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
                class="flex items-center gap-1.5 whitespace-nowrap px-3 py-3"
                :class="route().current(currentPattern(routeName)) ? 'border-b-2 border-[#18b9ad] font-semibold text-white' : 'text-blue-100/70 hover:text-white'">
            {{ label }}
            <span v-if="badges[routeName]" class="rounded-full bg-rose-600 px-1.5 text-[10px] font-bold leading-4 text-white">{{ badges[routeName] }}</span>
          </Link>
          <template v-if="user.isAdmin">
            <a :href="route('export.download')" class="whitespace-nowrap px-3 py-3 text-blue-100/70 hover:text-white">Export Excel</a>
            <Link :href="route('settings.index')" class="whitespace-nowrap px-3 py-3" :class="route().current('settings.*') ? 'border-b-2 border-[#18b9ad] font-semibold text-white' : 'text-blue-100/70 hover:text-white'">Pengaturan</Link>
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
