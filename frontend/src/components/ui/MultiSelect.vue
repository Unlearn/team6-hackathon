<template>
  <div class="space-y-2">
    <Input
      v-model="searchQuery"
      type="text"
      placeholder="Search trades..."
      class="h-12 text-base rounded-lg"
    />
    <div class="border rounded-lg p-4 space-y-2 max-h-64 overflow-y-auto">
      <div
        v-for="option in filteredOptions"
        :key="option.id"
        @click="toggleOption(option)"
        class="flex items-center space-x-2 p-2 hover:bg-accent rounded-md cursor-pointer transition-colors"
      >
        <div 
          class="w-5 h-5 border-2 rounded flex items-center justify-center"
          :class="isSelected(option) ? 'bg-primary border-primary' : 'border-input'"
        >
          <svg
            v-if="isSelected(option)"
            class="w-3 h-3 text-primary-foreground"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
          </svg>
        </div>
        <span class="text-base">{{ option.name }}</span>
      </div>
      <div v-if="filteredOptions.length === 0" class="text-center text-muted-foreground py-4">
        No trades found
      </div>
    </div>
    <div v-if="modelValue.length > 0" class="flex flex-wrap gap-2 mt-3">
      <div
        v-for="selected in modelValue"
        :key="selected.id"
        class="inline-flex items-center gap-1 bg-primary text-primary-foreground px-3 py-1 rounded-full text-sm"
      >
        <span>{{ selected.name }}</span>
        <button
          @click.stop="toggleOption(selected)"
          class="hover:bg-primary/80 rounded-full p-0.5"
        >
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Input } from '@/components/ui/input'

interface Trade {
  id: number
  name: string
}

interface Props {
  options: Trade[]
  modelValue: Trade[]
}

const props = defineProps<Props>()
const emit = defineEmits<{
  (e: 'update:modelValue', value: Trade[]): void
}>()

const searchQuery = ref('')

const filteredOptions = computed(() => {
  if (!searchQuery.value) return props.options
  return props.options.filter(option =>
    option.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

const isSelected = (option: Trade) => {
  return props.modelValue.some(v => v.id === option.id)
}

const toggleOption = (option: Trade) => {
  const newValue = isSelected(option)
    ? props.modelValue.filter(v => v.id !== option.id)
    : [...props.modelValue, option]
  emit('update:modelValue', newValue)
}
</script>
