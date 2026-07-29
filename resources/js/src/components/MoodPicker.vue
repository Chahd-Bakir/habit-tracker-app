<template>
  <div class="grid grid-cols-5 gap-3">
    <button
      v-for="(item, key) in moods"
      :key="key"
      type="button"
      @click="$emit('update:modelValue', key)"
      class="rounded-2xl border p-3 text-center shadow-sm transition hover:-translate-y-0.5"
      :class="modelValue === key
        ? 'border-emerald-400 bg-emerald-50 ring-2 ring-emerald-200'
        : 'border-emerald-100 bg-white hover:border-emerald-300 hover:bg-emerald-50'"
    >
      <div class="text-2xl">{{ item.emoji }}</div>
      <div class="mt-1 text-xs font-medium" :class="modelValue === key ? 'text-emerald-700' : 'text-slate-500'">
        {{ item.label }}
      </div>
    </button>
  </div>
</template>

<script setup>
defineProps({
  modelValue: { type: Number, default: null },
});

defineEmits(['update:modelValue']);

const moods = {
  1: { emoji: '😫', label: 'Very sad' },
  2: { emoji: '😢', label: 'Sad' },
  3: { emoji: '😐', label: 'Neutral' },
  4: { emoji: '🙂', label: 'Happy' },
  5: { emoji: '😄', label: 'Very happy' },
};
</script>
