<template>
  <div class="min-h-screen bg-gradient-to-br from-primary/30 via-primary/5 to-primary/20 dark:from-[#0f1729] dark:via-[#1a2332] dark:to-[#0f1729] p-4 sm:p-8">
    <div class="container mx-auto max-w-5xl">
      <header v-if="!isGeneratedSite" class="mb-12">
        <div class="flex justify-between items-center mb-8">
          <div class="flex-1"></div>
          <div class="text-center flex-1">
            <img src="/logo.png" alt="SubbieHub" class="h-16 mx-auto" />
          </div>
          <div class="flex-1 flex justify-end">
            <ThemeToggle />
          </div>
        </div>
        <p class="text-center text-xl text-muted-foreground font-medium">Website Generator for Subcontractors</p>
      </header>
      <main>
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import ThemeToggle from '@/components/ThemeToggle.vue'
import { useTheme } from '@/composables/useTheme'

const route = useRoute()
const { theme, initTheme } = useTheme()

const isGeneratedSite = computed(() => route.path.startsWith('/site/'))

onMounted(() => {
  initTheme()
})
</script>
