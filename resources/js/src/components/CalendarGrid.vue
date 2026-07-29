<template>
  <div class="grid grid-cols-7 gap-2 text-center text-sm">
    <div v-for="day in days" :key="day" class="py-2 font-medium text-slate-500">{{ day }}</div>
    <button
      v-for="cell in cells"
      :key="cell.key"
      @click="$emit('cell-click', cell)"
      class="rounded-2xl border p-3 transition hover:shadow-sm"
      :class="cellClass(cell)"
    >
      {{ cell.label }}
    </button>
  </div>
</template>

<script setup>
defineProps({
  days: { type: Array, default: () => ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] },
  cells: { type: Array, default: () => [] },
});

defineEmits(['cell-click']);

const cellClass = (cell) => {
  if (cell.isPadding) return 'border-transparent bg-transparent pointer-events-none';

  const { completed, total } = cell;

  if (total === 0 || completed === 0) return 'border-slate-100 bg-white text-slate-400';
  if (completed === total) return 'border-emerald-600 bg-emerald-500 text-white';
  return 'border-emerald-400 bg-emerald-200 text-emerald-800';
};
</script>
