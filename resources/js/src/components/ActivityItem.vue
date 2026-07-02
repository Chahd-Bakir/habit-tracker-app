<template>
  <div class="flex items-start gap-4 rounded-3xl border border-emerald-100 bg-white p-4 transition hover:bg-emerald-50/60">
    <div class="mt-1 flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700">
      <slot name="icon" />
    </div>
    <div class="min-w-0 flex-1">
      <div class="flex items-center justify-between gap-3">
        <p class="font-medium text-slate-900">{{ title }}</p>
        <span class="text-xs text-slate-400">{{ time }}</span>
      </div>
      <p class="mt-1 text-sm text-slate-500">{{ description }}</p>
    </div>
  </div>
</template>

<script setup>
defineProps({ title: String, description: String, time: String });
</script>
