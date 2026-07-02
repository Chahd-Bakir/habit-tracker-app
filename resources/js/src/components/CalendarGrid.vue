<template>
  <div class="grid grid-cols-7 gap-2 text-center text-sm">
    <div v-for="day in days" :key="day" class="py-2 font-medium text-slate-500">{{ day }}</div>
    <button v-for="cell in cells" :key="cell.key" class="rounded-2xl border p-3 transition" :class="cell.completed ? 'border-emerald-200 bg-emerald-100 text-emerald-700' : 'border-slate-100 bg-white text-slate-400'">
      {{ cell.label }}
    </button>
  </div>
</template>

<script setup>
defineProps({
  days: { type: Array, default: () => ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] },
  cells: { type: Array, default: () => [] },
});
</script>
