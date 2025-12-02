<template>
  <div class="max-w-3xl mx-auto">
    <Card class="shadow-2xl border-2 dark:border-primary/20">
      <CardHeader class="space-y-6 pb-8">
        <div class="space-y-3">
          <div class="flex items-center justify-between text-sm text-muted-foreground mb-2">
            <span class="font-medium">Step 1 of 5</span>
            <span class="font-medium">20% Complete</span>
          </div>
          <Progress :model-value="20" class="h-3" />
        </div>
        <div class="space-y-3 pt-2">
          <CardTitle class="text-3xl">Basic Information</CardTitle>
          <CardDescription class="text-base">Let's start with your business details</CardDescription>
        </div>
      </CardHeader>
      
      <CardContent class="space-y-8 px-8 pb-8">
        <form @submit.prevent="handleSubmit" class="space-y-8">
          <div class="space-y-3">
            <Label for="name" class="text-base">Business Name *</Label>
            <Input
              id="name"
              v-model="formData.name"
              type="text"
              placeholder="e.g., Smith Plumbing Services"
              required
              class="h-12 text-base rounded-lg"
              :class="{ 'border-destructive': errors.name }"
            />
            <p v-if="errors.name" class="text-sm text-destructive mt-2">
              {{ errors.name }}
            </p>
          </div>

          <div class="space-y-3">
            <Label for="abn" class="text-base">ABN (Australian Business Number) *</Label>
            <Input
              id="abn"
              v-model="formData.abn"
              type="text"
              placeholder="11 digits"
              maxlength="11"
              required
              class="h-12 text-base rounded-lg"
              :class="{ 'border-destructive': errors.abn }"
            />
            <p v-if="errors.abn" class="text-sm text-destructive mt-2">
              {{ errors.abn }}
            </p>
          </div>

          <div class="space-y-3">
            <Label for="logo" class="text-base">Business Logo (optional)</Label>
            <div v-if="logoPreview" class="mb-3 p-4 border rounded-lg bg-muted/20">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <img :src="logoPreview" alt="Logo preview" class="h-16 w-16 object-contain" />
                  <span class="text-sm text-muted-foreground">{{ logoFilename }}</span>
                </div>
                <Button
                  type="button"
                  variant="ghost"
                  size="sm"
                  @click="clearLogo"
                  class="text-destructive hover:text-destructive"
                >
                  Remove
                </Button>
              </div>
            </div>
            <Input
              v-if="!logoPreview"
              id="logo"
              type="file"
              accept="image/*"
              @change="handleLogoChange"
              class="h-12 text-base rounded-lg cursor-pointer file:text-foreground"
            />
            <p v-if="!logoPreview" class="text-sm text-muted-foreground">Upload your business logo (PNG, JPG, etc.)</p>
          </div>

          <Alert v-if="submitError" variant="destructive" class="rounded-lg">
            <AlertCircle class="h-5 w-5" />
            <AlertDescription class="text-base ml-2">
              {{ submitError }}
            </AlertDescription>
          </Alert>
        </form>
      </CardContent>

      <CardFooter class="flex justify-end items-center px-8 pb-8 pt-4">
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
  name: '',
  abn: ''
})

const logoFile = ref<File | null>(null)
const logoPreview = ref<string | null>(null)
const logoFilename = ref<string | null>(null)
const errors = ref<Record<string, string>>({})
const submitError = ref('')
const loading = ref(false)

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000'

onMounted(() => {
  // Load data from sessionStorage if returning to step 1
  const savedData = sessionStorage.getItem('wizardStep1')
  if (savedData) {
    const data = JSON.parse(savedData)
    formData.value.name = data.name || ''
    formData.value.abn = data.abn || ''
    if (data.logo) {
      logoPreview.value = `${API_URL}/uploads/${data.logo}`
      logoFilename.value = data.logoOriginalName || data.logo
    }
  }
})

const handleLogoChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  
  if (file) {
    logoFile.value = file
    logoFilename.value = file.name
    
    // Create preview
    const reader = new FileReader()
    reader.onload = (e) => {
      logoPreview.value = e.target?.result as string
    }
    reader.readAsDataURL(file)
  }
}

const clearLogo = () => {
  logoFile.value = null
  logoPreview.value = null
  logoFilename.value = null
  
  // Update sessionStorage
  const savedData = sessionStorage.getItem('wizardStep1')
  if (savedData) {
    const data = JSON.parse(savedData)
    data.logo = null
    data.logoOriginalName = null
    sessionStorage.setItem('wizardStep1', JSON.stringify(data))
  }
}

const validateForm = (): boolean => {
  errors.value = {}
  
  if (!formData.value.name.trim()) {
    errors.value.name = 'Business name is required'
  }
  
  if (!formData.value.abn.trim()) {
    errors.value.abn = 'ABN is required'
  } else if (!/^\d{11}$/.test(formData.value.abn)) {
    errors.value.abn = 'ABN must be 11 digits'
  }
  
  return Object.keys(errors.value).length === 0
}

const handleSubmit = async () => {
  submitError.value = ''
  
  if (!validateForm()) {
    return
  }
  
  loading.value = true
  
  try {
    // Get existing logo from sessionStorage if no new file uploaded
    const savedData = sessionStorage.getItem('wizardStep1')
    const existingLogo = savedData ? JSON.parse(savedData).logo : null
    
    let logoToSave = existingLogo
    
    // Only upload new logo if a file was selected
    if (logoFile.value) {
      const submitData = new FormData()
      submitData.append('name', formData.value.name)
      submitData.append('abn', formData.value.abn)
      submitData.append('logo', logoFile.value)
      
      const response = await axios.post(`${API_URL}/api/wizard/step1`, submitData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      
      if (response.data.success) {
        logoToSave = response.data.logo || existingLogo
      }
    } else {
      // Just validate without uploading
      const submitData = new FormData()
      submitData.append('name', formData.value.name)
      submitData.append('abn', formData.value.abn)
      
      await axios.post(`${API_URL}/api/wizard/step1`, submitData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
    }
    
    // Store step 1 data in sessionStorage
    const existingOriginalName = savedData ? JSON.parse(savedData).logoOriginalName : null
    sessionStorage.setItem('wizardStep1', JSON.stringify({
      name: formData.value.name,
      abn: formData.value.abn,
      logo: logoToSave,
      logoOriginalName: logoFile.value ? logoFile.value.name : existingOriginalName
    }))
    // Navigate to step 2 immediately
    router.push('/wizard/step2')
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
