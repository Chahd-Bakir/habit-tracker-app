<template>
  <div class="flex items-center justify-between gap-4">
    <div>
      <p class="text-sm text-slate-500">{{ eyebrow }}</p>
      <h1 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">{{ title }}</h1>
    </div>
    <div class="flex items-center gap-3">
      <slot />
      <div class="flex h-11 w-11 items-center justify-center rounded-full bg-emerald-100 font-semibold text-emerald-700">C</div>
    </div>
  </div>
</template>

<script setup>
defineProps({ eyebrow: String, title: String });
</script>
