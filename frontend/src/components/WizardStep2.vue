<template>
  <div class="max-w-3xl mx-auto">
    <Card class="shadow-2xl border-2 dark:border-primary/20">
      <CardHeader class="space-y-6 pb-8">
        <div class="space-y-3">
          <div class="flex items-center justify-between text-sm text-muted-foreground mb-2">
            <span class="font-medium">Step 2 of 5</span>
            <span class="font-medium">40% Complete</span>
          </div>
          <Progress :model-value="40" class="h-3" />
        </div>
        <div class="space-y-3 pt-2">
          <CardTitle class="text-3xl">Contact Details</CardTitle>
          <CardDescription class="text-base">How can we reach you?</CardDescription>
        </div>
      </CardHeader>
      
      <CardContent class="space-y-8 px-8 pb-8">
        <form @submit.prevent="handleSubmit" class="space-y-8">
          <div class="space-y-3">
            <Label for="mobile" class="text-base">Mobile Number *</Label>
            <Input
              id="mobile"
              v-model="formData.mobile"
              type="tel"
              placeholder="e.g., 0412 345 678"
              required
              class="h-12 text-base rounded-lg"
              :class="{ 'border-destructive': errors.mobile }"
            />
            <p v-if="errors.mobile" class="text-sm text-destructive mt-2">
              {{ errors.mobile }}
            </p>
          </div>

          <div class="space-y-3">
            <Label for="email" class="text-base">Email Address *</Label>
            <Input
              id="email"
              v-model="formData.email"
              type="email"
              placeholder="e.g., john@example.com"
              required
              class="h-12 text-base rounded-lg"
              :class="{ 'border-destructive': errors.email }"
            />
            <p v-if="errors.email" class="text-sm text-destructive mt-2">
              {{ errors.email }}
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
import { AlertCircle, CheckCircle } from 'lucide-vue-next'
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Progress } from '@/components/ui/progress'
import { Alert, AlertDescription } from '@/components/ui/alert'

const router = useRouter()

const formData = ref({
  mobile: '',
  email: ''
})

const errors = ref<Record<string, string>>({})
const submitError = ref('')
const loading = ref(false)

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000'

onMounted(() => {
  // Check if step 1 is completed
  const step1Data = sessionStorage.getItem('wizardStep1')
  if (!step1Data) {
    router.push('/wizard/step1')
    return
  }
  
  // Load step 2 data if returning to this step
  const savedData = sessionStorage.getItem('wizardStep2')
  if (savedData) {
    const data = JSON.parse(savedData)
    formData.value.mobile = data.mobile || ''
    formData.value.email = data.email || ''
  }
})

const validateForm = (): boolean => {
  errors.value = {}
  
  if (!formData.value.mobile.trim()) {
    errors.value.mobile = 'Mobile number is required'
  }
  
  if (!formData.value.email.trim()) {
    errors.value.email = 'Email address is required'
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.value.email)) {
    errors.value.email = 'Please enter a valid email address'
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
    const response = await axios.post(`${API_URL}/api/wizard/step2`, {
      mobile: formData.value.mobile,
      email: formData.value.email
    })
    
    if (response.data.success) {
      // Store step 2 data in sessionStorage
      sessionStorage.setItem('wizardStep2', JSON.stringify({
        mobile: formData.value.mobile,
        email: formData.value.email
      }))
      // Navigate to step 3 immediately
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
