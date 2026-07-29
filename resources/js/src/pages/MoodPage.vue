<template>
  <AppShell>
    <template #header><TopBar eyebrow="Mood Journal" title="How do you feel today?" /></template>
    <div class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
      <SectionCard>
        <template #header>
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-slate-900">{{ editingId ? 'Edit entry' : 'Choose a mood' }}</h2>
            <button v-if="editingId" type="button" @click="cancelEdit" class="text-sm text-slate-500 hover:text-slate-700">Cancel</button>
          </div>
        </template>
        <MoodPicker v-model="selectedMood" />
        <p v-if="dailyPrompt && !note" class="mt-5 text-sm italic text-slate-400">
          💭 {{ dailyPrompt.content }}
          <span v-if="dailyPrompt.is_ai" class="ml-1.5 inline-flex items-center rounded-full bg-emerald-100 px-1.5 py-0.5 text-[10px] font-medium text-emerald-700 not-italic">✨ IA</span>
          <span v-else class="ml-1.5 inline-flex items-center rounded-full bg-slate-200 px-1.5 py-0.5 text-[10px] font-medium text-slate-500 not-italic">Static</span>
        </p>
        <textarea
          v-model="note"
          rows="4"
          placeholder="Write a few notes about your day"
          class="mt-2 w-full rounded-[24px] border p-4 outline-none transition focus:bg-white"
          :class="noteError ? 'border-red-300 bg-red-50' : 'border-slate-200 bg-slate-50 focus:border-emerald-400'"
        ></textarea>
        <p v-if="noteError" class="mt-1 text-xs text-red-500">{{ noteError }}</p>

        <div class="mt-4 space-y-3">
          <div class="flex items-center gap-3">
            <button @click="photoInput.click()" class="flex items-center gap-2 rounded-2xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 hover:border-emerald-300 hover:text-emerald-700 transition">
              <Camera class="h-4 w-4" /> {{ photoPreviewUrl ? 'Change photo' : 'Add photo' }}
            </button>
            <button v-if="recording" @click="stopRecording" class="flex items-center gap-2 rounded-2xl border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-medium text-red-600 hover:bg-red-100 transition">
              <Square class="h-4 w-4" /> Stop ({{ recordTimer }}s)
            </button>
            <button v-else-if="!audioBlob" @click="startRecording" class="flex items-center gap-2 rounded-2xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 hover:border-emerald-300 hover:text-emerald-700 transition">
              <Mic class="h-4 w-4" /> Record voice
            </button>
            <input ref="photoInput" type="file" accept="image/*" capture="environment" class="hidden" @change="onPhotoSelected" />
          </div>

          <div v-if="photoPreviewUrl" class="relative inline-block">
            <img :src="photoPreviewUrl" class="h-24 w-24 rounded-2xl object-cover border border-slate-200" />
            <button @click="removePhoto" class="absolute -right-2 -top-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-white shadow hover:bg-red-600 transition">
              <X class="h-3.5 w-3.5" />
            </button>
          </div>

          <div v-if="audioBlob" class="flex items-center gap-3">
            <audio :src="audioPreviewUrl" controls class="h-10 max-w-[200px]" />
            <button @click="removeAudio" class="rounded-full p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-500 transition">
              <Trash2 class="h-4 w-4" />
            </button>
          </div>
        </div>

        <div class="mt-4 flex items-center justify-between">
          <span v-if="savedMessage" class="text-sm text-emerald-600">{{ savedMessage }}</span>
          <UiButton class="ml-auto" :disabled="!selectedMood || saving" @click="saveMood">
            {{ saving ? 'Saving...' : (editingId ? 'Update' : 'Save') }}
          </UiButton>
        </div>
      </SectionCard>

      <SectionCard>
        <template #header><h2 class="text-lg font-semibold text-slate-900">Mood history</h2></template>
        <div v-if="history.length === 0" class="py-8 text-center text-sm text-slate-400">
          No mood entries yet. Start logging your moods above!
        </div>
        <div v-else class="space-y-4">
          <article
            v-for="entry in history"
            :key="entry.id"
            class="group relative rounded-[26px] border p-4 transition"
            :class="editingId === entry.id ? 'border-emerald-300 bg-emerald-50/40' : 'border-slate-100'"
          >
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0 flex-1">
                <p class="text-lg">
                  {{ moodEmoji(entry.mood) }}
                  <span class="ml-2 font-medium text-slate-900">{{ moodLabel(entry.mood) }}</span>
                </p>
                <p v-if="entry.note" class="mt-1.5 text-sm text-slate-500">{{ entry.note }}</p>
                <div v-if="entry.photo_url || entry.voice_note_url" class="mt-3 flex items-center gap-3">
                  <img v-if="entry.photo_url" :src="entry.photo_url" class="h-16 w-16 rounded-xl object-cover border border-slate-200 cursor-pointer hover:opacity-80 transition" @click="openPhoto(entry.photo_url)" />
                  <audio v-if="entry.voice_note_url" :src="entry.voice_note_url" controls class="h-9 max-w-[180px]" />
                </div>
              </div>
              <div class="flex flex-col items-end gap-1 flex-shrink-0">
                <span class="text-xs text-slate-400">{{ formatDateTime(entry.created_at) }}</span>
                <div v-if="deletingId === entry.id" class="mt-1 flex items-center gap-2">
                  <span class="text-xs text-red-600">Delete?</span>
                  <button @click="confirmDelete(entry)" class="rounded-lg bg-red-500 px-2.5 py-1 text-xs font-medium text-white hover:bg-red-600 transition">Yes</button>
                  <button @click="deletingId = null" class="rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-medium text-slate-600 hover:bg-slate-50 transition">No</button>
                </div>
                <div v-else class="mt-1 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition">
                  <button @click="startEdit(entry)" class="rounded-full p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition" title="Edit">
                    <Pencil class="h-3.5 w-3.5" />
                  </button>
                  <button @click="deletingId = entry.id" class="rounded-full p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-500 transition" title="Delete">
                    <Trash2 class="h-3.5 w-3.5" />
                  </button>
                </div>
              </div>
            </div>
          </article>
        </div>
      </SectionCard>
    </div>

    <div v-if="lightboxUrl" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4" @click="lightboxUrl = null">
      <img :src="lightboxUrl" class="max-h-[90vh] max-w-[90vw] rounded-2xl object-contain" />
    </div>
  </AppShell>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Pencil, Trash2, Camera, Mic, Square, X } from 'lucide-vue-next';
import AppShell from '../components/AppShell.vue';
import MoodPicker from '../components/MoodPicker.vue';
import SectionCard from '../components/SectionCard.vue';
import TopBar from '../components/TopBar.vue';
import UiButton from '../components/UiButton.vue';
import { getStoredToken } from '../utils/auth';

const selectedMood = ref(null);
const note = ref('');
const noteError = ref('');
const history = ref([]);
const saving = ref(false);
const savedMessage = ref('');
const editingId = ref(null);
const deletingId = ref(null);

const photoInput = ref(null);
const photoFile = ref(null);
const photoPreviewUrl = ref(null);
const photoRemoved = ref(false);

const audioBlob = ref(null);
const audioPreviewUrl = ref(null);
const recording = ref(false);
const recordTimer = ref(0);
let recordInterval = null;
let mediaRecorder = null;
let audioChunks = [];

const dailyPrompt = ref(null);
const lightboxUrl = ref(null);

const moods = {
  1: { emoji: '😫', label: 'Very sad' },
  2: { emoji: '😢', label: 'Sad' },
  3: { emoji: '😐', label: 'Neutral' },
  4: { emoji: '🙂', label: 'Happy' },
  5: { emoji: '😄', label: 'Very happy' },
};

const moodEmoji = (val) => moods[val]?.emoji || '';
const moodLabel = (val) => moods[val]?.label || '';

const formatDateTime = (isoStr) => {
  if (!isoStr) return '';
  const d = new Date(isoStr);
  if (isNaN(d.getTime())) return '';
  const date = d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  const time = d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
  return `${date} · ${time}`;
};

const openPhoto = (url) => { lightboxUrl.value = url; };

const onPhotoSelected = (e) => {
  const file = e.target.files[0];
  if (!file) return;
  photoFile.value = file;
  if (photoPreviewUrl.value) URL.revokeObjectURL(photoPreviewUrl.value);
  photoPreviewUrl.value = URL.createObjectURL(file);
  photoRemoved.value = false;
};

const removePhoto = () => {
  photoFile.value = null;
  if (photoPreviewUrl.value) URL.revokeObjectURL(photoPreviewUrl.value);
  photoPreviewUrl.value = null;
  photoRemoved.value = true;
};

const startRecording = async () => {
  try {
    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
    mediaRecorder = new MediaRecorder(stream);
    audioChunks = [];
    recording.value = true;
    recordTimer.value = 0;
    recordInterval = setInterval(() => { recordTimer.value++; }, 1000);

    mediaRecorder.ondataavailable = (e) => { audioChunks.push(e.data); };

    mediaRecorder.onstop = () => {
      clearInterval(recordInterval);
      const blob = new Blob(audioChunks, { type: 'audio/webm' });
      audioBlob.value = blob;
      audioPreviewUrl.value = URL.createObjectURL(blob);
      stream.getTracks().forEach(t => t.stop());
    };

    mediaRecorder.start();
  } catch (err) {
    console.error('Microphone access denied:', err);
  }
};

const stopRecording = () => {
  if (mediaRecorder && mediaRecorder.state !== 'inactive') {
    mediaRecorder.stop();
  }
  recording.value = false;
};

const removeAudio = () => {
  audioBlob.value = null;
  if (audioPreviewUrl.value) URL.revokeObjectURL(audioPreviewUrl.value);
  audioPreviewUrl.value = null;
};

const fetchDailyPrompt = async () => {
  const token = getStoredToken();
  try {
    const response = await fetch('/api/daily-prompt', {
      headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
    });
    const data = await response.json();
    if (response.ok && data.prompt?.content) {
      dailyPrompt.value = {
        content: data.prompt.content,
        is_ai: data.prompt.is_ai ?? false,
      };
    }
  } catch (error) {
    console.error('Failed to fetch daily prompt:', error);
  }
};

const fetchHistory = async () => {
  const token = getStoredToken();
  try {
    const response = await fetch('/api/moods', {
      headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
    });
    const data = await response.json();
    if (response.ok && data.moods) {
      history.value = data.moods;
    }
  } catch (error) {
    console.error('Failed to fetch mood history:', error);
  }
};

const startEdit = (entry) => {
  editingId.value = entry.id;
  selectedMood.value = entry.mood;
  note.value = entry.note || '';
  deletingId.value = null;
  photoRemoved.value = false;
  if (entry.photo_url) {
    photoPreviewUrl.value = entry.photo_url;
    photoFile.value = null;
  } else {
    photoPreviewUrl.value = null;
    photoFile.value = null;
  }
  if (entry.voice_note_url) {
    audioPreviewUrl.value = entry.voice_note_url;
    audioBlob.value = null;
  } else {
    audioPreviewUrl.value = null;
    audioBlob.value = null;
  }
};

const cancelEdit = () => {
  editingId.value = null;
  selectedMood.value = null;
  note.value = '';
  resetMedia();
};

const resetMedia = () => {
  photoFile.value = null;
  if (photoPreviewUrl.value) URL.revokeObjectURL(photoPreviewUrl.value);
  photoPreviewUrl.value = null;
  photoRemoved.value = false;
  audioBlob.value = null;
  if (audioPreviewUrl.value) URL.revokeObjectURL(audioPreviewUrl.value);
  audioPreviewUrl.value = null;
};

const uploadMedia = async (moodId) => {
  const token = getStoredToken();
  const uploads = [];

  if (editingId.value && photoRemoved.value) {
    uploads.push(
      fetch(`/api/moods/${moodId}/photo`, {
        method: 'DELETE',
        headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
      })
    );
  } else if (photoFile.value) {
    const fd = new FormData();
    fd.append('photo', photoFile.value);
    uploads.push(
      fetch(`/api/moods/${moodId}/photo`, {
        method: 'POST',
        headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
        body: fd,
      })
    );
  }

  if (editingId.value && !audioBlob.value && !audioPreviewUrl.value && history.value.find(h => h.id === editingId.value)?.voice_note_url) {
    uploads.push(
      fetch(`/api/moods/${moodId}/voice`, {
        method: 'DELETE',
        headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
      })
    );
  } else if (audioBlob.value) {
    const fd = new FormData();
    fd.append('voice', audioBlob.value, 'voice.webm');
    uploads.push(
      fetch(`/api/moods/${moodId}/voice`, {
        method: 'POST',
        headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
        body: fd,
      })
    );
  }

  await Promise.allSettled(uploads);
};

const saveMood = async () => {
  noteError.value = '';

  if (!selectedMood.value && !note.value.trim()) {
    noteError.value = 'Please select a mood or write a note';
    return;
  }

  saving.value = true;
  savedMessage.value = '';

  const token = getStoredToken();
  const isEditing = editingId.value !== null;
  const url = isEditing ? `/api/moods/${editingId.value}` : '/api/moods';
  const method = isEditing ? 'PATCH' : 'POST';

  try {
    const response = await fetch(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify({
        mood: selectedMood.value,
        note: note.value || null,
      }),
    });

    const data = await response.json();
    if (!response.ok || !data.success) throw new Error(data.message || 'Failed to save mood');

    const moodId = data.mood.id;

    await uploadMedia(moodId);

    resetMedia();
    selectedMood.value = null;
    note.value = '';
    editingId.value = null;
    savedMessage.value = isEditing ? 'Entry updated!' : 'Mood saved!';
    setTimeout(() => { savedMessage.value = ''; }, 3000);
    await fetchHistory();
  } catch (error) {
    console.error('Failed to save mood:', error);
  } finally {
    saving.value = false;
  }
};

const confirmDelete = async (entry) => {
  const token = getStoredToken();
  try {
    const response = await fetch(`/api/moods/${entry.id}`, {
      method: 'DELETE',
      headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
    });
    const data = await response.json();
    if (!response.ok || !data.success) throw new Error(data.message || 'Failed to delete entry');
    history.value = history.value.filter(h => h.id !== entry.id);
  } catch (error) {
    console.error('Failed to delete mood entry:', error);
  }
  deletingId.value = null;
};

onMounted(() => { fetchDailyPrompt(); fetchHistory(); });
onUnmounted(() => {
  clearInterval(recordInterval);
  if (mediaRecorder && mediaRecorder.state !== 'inactive') {
    mediaRecorder.stream?.getTracks().forEach(t => t.stop());
  }
});
</script>
