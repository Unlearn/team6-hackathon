<template>
  <div class="max-w-3xl mx-auto">
    <Card class="shadow-2xl border-2 dark:border-primary/20">
      <CardHeader class="space-y-6 pb-8">
        <div class="space-y-3">
          <div class="flex items-center justify-between text-sm text-muted-foreground mb-2">
            <span class="font-medium">Step 2 of 4</span>
            <span class="font-medium">50% Complete</span>
          </div>
          <Progress :model-value="50" class="h-3" />
        </div>
        <div class="space-y-3 pt-2">
          <CardTitle class="text-3xl">What Do You Do?</CardTitle>
          <CardDescription class="text-base">Select the trades and services you offer</CardDescription>
        </div>
      </CardHeader>
      
      <CardContent class="space-y-8 px-8 pb-8">
        <form @submit.prevent="handleSubmit" class="space-y-8">
          <div class="space-y-3">
            <Label class="text-base">Select Your Trades *</Label>
            <MultiSelect
              v-model="selectedTrades"
              :options="availableTrades"
            />
            <p v-if="errors.trades" class="text-sm text-destructive mt-2">
              {{ errors.trades }}
            </p>
          </div>

          <Alert v-if="submitError" variant="destructive" class="rounded-lg">
            <AlertCircle class="h-5 w-5" />
            <AlertDescription class="text-base ml-2">
              {{ submitError }}
            </AlertDescription>
          </Alert>
        </form>
      </CardContent>

      <CardFooter class="flex justify-between items-center px-8 pb-8 pt-4">
        <Button
          variant="ghost"
          size="lg"
          @click="goToPrevious"
          class="rounded-lg"
        >
          Previous
        </Button>
        <Button
          @click="handleSubmit"
          :disabled="loading"
          size="lg"
          class="rounded-lg px-12"
        >
          {{ loading ? 'Submitting...' : 'Continue' }}
        </Button>
      </CardFooter>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { AlertCircle } from 'lucide-vue-next'
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import { Progress } from '@/components/ui/progress'
import { Alert, AlertDescription } from '@/components/ui/alert'
import MultiSelect from '@/components/ui/MultiSelect.vue'

const router = useRouter()

interface Trade {
  id: number
  name: string
}

const availableTrades = ref<Trade[]>([])
const selectedTrades = ref<Trade[]>([])

const errors = ref<Record<string, string>>({})
const submitError = ref('')
const loading = ref(false)

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000'

onMounted(async () => {
  // Check if previous step is completed
  const step1Data = sessionStorage.getItem('wizardStep1')
  
  if (!step1Data) {
    router.push('/wizard/step1')
    return
  }
  
  // Fetch available trades
  try {
    const response = await axios.get(`${API_URL}/api/wizard/trades`)
    if (response.data.success) {
      availableTrades.value = response.data.trades
    }
  } catch (error) {
    console.error('Failed to fetch trades:', error)
  }
})

const validateForm = (): boolean => {
  errors.value = {}
  
  if (selectedTrades.value.length === 0) {
    errors.value.trades = 'Please select at least one trade'
  }
  
  return Object.keys(errors.value).length === 0
}

const goToPrevious = () => {
  router.push('/wizard/step1')
}

const handleSubmit = async () => {
  submitError.value = ''
  
  if (!validateForm()) {
    return
  }
  
  loading.value = true
  
  try {
    const response = await axios.post(`${API_URL}/api/wizard/step4`, {
      tradeIds: selectedTrades.value.map(t => t.id)
    })
    
    if (response.data.success) {
      // Store step 2 data in sessionStorage
      sessionStorage.setItem('wizardStep2', JSON.stringify({
        tradeIds: selectedTrades.value.map(t => t.id)
      }))
      // Navigate to step 3
      router.push('/wizard/step3')
    }
  } catch (error: any) {
    if (error.response?.data?.errors) {
      const apiErrors = error.response.data.errors
      if (typeof apiErrors === 'object') {
        errors.value = apiErrors
      } else {
        submitError.value = Array.isArray(apiErrors) ? apiErrors.join(', ') : 'Validation error occurred'
      }
    } else {
      submitError.value = 'Failed to submit form. Please try again.'
    }
  } finally {
    loading.value = false
  }
}
</script>
