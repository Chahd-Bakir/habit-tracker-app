<template>
  <article class="rounded-[28px] border bg-white p-5 shadow-[0_18px_45px_rgba(15,23,42,0.06)] transition duration-200 hover:-translate-y-0.5 hover:shadow-[0_22px_55px_rgba(15,23,42,0.08)]" :class="[tones[tone].border, tones[tone].hover]">
    <div class="flex items-start justify-between gap-3">
      <div>
        <p class="text-sm text-slate-500">{{ label }}</p>
        <p class="mt-2 text-2xl font-semibold tracking-tight text-slate-900">{{ value }}</p>
      </div>
      <div class="rounded-2xl p-3" :class="tones[tone].chip">
        <slot name="icon" />
      </div>
    </div>
    <p class="mt-4 text-sm text-slate-500">{{ caption }}</p>
  </article>
</template>

<script setup>
const tones = {
  emerald: { border: 'border-emerald-100', chip: 'bg-emerald-50 text-emerald-600', hover: 'hover:border-emerald-200' },
  teal: { border: 'border-teal-100', chip: 'bg-teal-50 text-teal-600', hover: 'hover:border-teal-200' },
  green: { border: 'border-green-100', chip: 'bg-green-50 text-green-600', hover: 'hover:border-green-200' },
  lime: { border: 'border-lime-100', chip: 'bg-lime-50 text-lime-600', hover: 'hover:border-lime-200' },
};

defineProps({
  label: String,
  value: String,
  caption: String,
  tone: { type: String, default: 'emerald', validator: (v) => ['emerald', 'teal', 'green', 'lime'].includes(v) },
});
</script>
