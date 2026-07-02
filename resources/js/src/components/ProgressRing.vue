<template>
  <div class="flex items-center gap-4 rounded-3xl border border-emerald-100 bg-emerald-50 p-5">
    <div class="relative h-20 w-20 shrink-0 rounded-full" :style="ringStyle">
      <div class="absolute inset-2 flex items-center justify-center rounded-full bg-white text-center">
        <div>
          <p class="text-lg font-semibold text-slate-900">{{ value }}%</p>
          <p class="text-[11px] text-slate-500">done</p>
        </div>
      </div>
    </div>
    <div>
      <p class="text-sm font-medium text-emerald-700">{{ label }}</p>
      <p class="mt-1 text-sm text-slate-600">{{ description }}</p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({ value: Number, label: String, description: String });

const ringStyle = computed(() => ({
  background: `conic-gradient(#4CAF50 ${props.value}%, #dcfce7 0)`,
}));
</script>
