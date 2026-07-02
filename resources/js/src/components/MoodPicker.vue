<template>
  <div class="grid grid-cols-5 gap-3 sm:grid-cols-8">
    <button v-for="emoji in emojis" :key="emoji" class="rounded-2xl border border-emerald-100 bg-white p-3 text-2xl shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 hover:bg-emerald-50">
      {{ emoji }}
    </button>
  </div>
</template>

<script setup>
defineProps({ emojis: { type: Array, default: () => ['😀', '🙂', '😌', '🌿', '🔥', '✨', '🤍', '😴'] } });
</script>
