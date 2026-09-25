<script setup>
defineProps({ label: String, value: [String, Number], note: { type: String, default: null } });
</script>

<template>
  <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <p class="text-sm font-medium text-slate-500">{{ label }}</p>
    <p class="mt-2 font-mono text-2xl font-bold text-slate-900">{{ value }}</p>
    <p v-if="note" class="mt-1 text-xs text-slate-400">{{ note }}</p>
  </div>
</template>
