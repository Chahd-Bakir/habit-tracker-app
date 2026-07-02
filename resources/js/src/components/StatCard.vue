<template>
  <article class="rounded-[28px] border border-emerald-100 bg-white p-5 shadow-[0_18px_45px_rgba(15,23,42,0.06)] transition duration-200 hover:-translate-y-0.5 hover:shadow-[0_22px_55px_rgba(15,23,42,0.08)]">
    <div class="flex items-start justify-between gap-3">
      <div>
        <p class="text-sm text-slate-500">{{ label }}</p>
        <p class="mt-2 text-2xl font-semibold tracking-tight text-slate-900">{{ value }}</p>
      </div>
      <div class="rounded-2xl bg-emerald-50 p-3 text-emerald-600">
        <slot name="icon" />
      </div>
    </div>
    <p class="mt-4 text-sm text-slate-500">{{ caption }}</p>
  </article>
</template>

<script setup>
defineProps({
  label: String,
  value: String,
  caption: String,
});
</script>
