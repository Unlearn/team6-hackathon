<script setup lang="ts">
import { type HTMLAttributes } from 'vue'
import type { AlertVariants } from '.'
import { cn } from '@/lib/utils'
import { alertVariants } from '.'

interface Props {
  class?: HTMLAttributes['class']
  variant?: AlertVariants['variant']
}

const props = defineProps<Props>()
</script>

<template>
  <div :class="cn(alertVariants({ variant }), props.class)" role="alert">
    <slot />
  </div>
</template>
