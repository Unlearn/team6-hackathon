<template>
  <div class="max-w-3xl mx-auto">
    <Card class="shadow-2xl border-2 dark:border-primary/20">
      <CardHeader class="space-y-6 pb-8">
        <div class="space-y-3">
          <div class="flex items-center justify-between text-sm text-muted-foreground mb-2">
            <span class="font-medium">Step 4 of 4</span>
            <span class="font-medium">100% Complete</span>
          </div>
          <Progress :model-value="100" class="h-3" />
        </div>
        <div class="space-y-3 pt-2">
          <CardTitle class="text-3xl">Compliance Documents</CardTitle>
          <CardDescription class="text-base">Upload certificates, insurance, or compliance documents (optional)</CardDescription>
        </div>
      </CardHeader>
      
      <CardContent class="space-y-8 px-8 pb-8">
        <form @submit.prevent="handleSubmit" class="space-y-8">
          <div class="space-y-3">
            <Label class="text-base">Documents & Certificates</Label>
            
            <div
              @drop.prevent="handleDrop"
              @dragover.prevent="isDragging = true"
              @dragleave.prevent="isDragging = false"
              :class="[
                'border-2 border-dashed rounded-lg p-8 text-center transition-colors cursor-pointer',
                isDragging ? 'border-primary bg-primary/5' : 'border-muted-foreground/25 hover:border-primary/50'
              ]"
              @click="$refs.fileInput.click()"
            >
              <div class="flex flex-col items-center gap-3">
                <svg class="w-12 h-12 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                <div>
                  <p class="text-base font-medium">Drop files here or click to browse</p>
                  <p class="text-sm text-muted-foreground mt-1">Supports PDF, images, and ZIP files</p>
                </div>
              </div>
              <input
                ref="fileInput"
                type="file"
                multiple
                accept=".pdf,.jpg,.jpeg,.png,.zip"
                @change="handleFileSelect"
                class="hidden"
              />
            </div>

            <div v-if="selectedFiles.length > 0" class="space-y-2 mt-4">
              <div
                v-for="(file, index) in selectedFiles"
                :key="index"
                class="flex items-center justify-between p-3 border rounded-lg bg-muted/20"
              >
                <div class="flex items-center gap-3">
                  <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                  </svg>
                  <div>
                    <p class="text-sm font-medium">{{ file.name }}</p>
                    <p class="text-xs text-muted-foreground">{{ formatFileSize(file.size) }}</p>
                  </div>
                </div>
                <Button
                  type="button"
                  variant="ghost"
                  size="sm"
                  @click="removeFile(index)"
                  class="text-destructive hover:text-destructive"
                >
                  Remove
                </Button>
              </div>
            </div>
          </div>

          <Alert v-if="submitError" variant="destructive" class="rounded-lg">
            <AlertCircle class="h-5 w-5" />
            <AlertDescription class="text-base ml-2">
              {{ submitError }}
            </AlertDescription>
          </Alert>

          <Alert v-if="siteUrl" class="rounded-lg border-primary bg-primary/5">
            <AlertDescription class="text-base">
              <div class="space-y-3">
                <p class="font-medium">Your website is ready!</p>
                <a 
                  :href="siteUrl" 
                  target="_blank"
                  class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors"
                >
                  View Your Website
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                  </svg>
                </a>
              </div>
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
          {{ loading ? 'Submitting...' : 'Complete' }}
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

const router = useRouter()

const selectedFiles = ref<File[]>([])
const isDragging = ref(false)
const submitError = ref('')
const siteUrl = ref('')
const loading = ref(false)

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000'

onMounted(() => {
  const step1Data = sessionStorage.getItem('wizardStep1')
  const step2Data = sessionStorage.getItem('wizardStep2')
  const step3Data = sessionStorage.getItem('wizardStep3')
  
  if (!step1Data || !step2Data || !step3Data) {
    router.push('/wizard/step1')
  }
})

const handleDrop = (e: DragEvent) => {
  isDragging.value = false
  const files = Array.from(e.dataTransfer?.files || [])
  selectedFiles.value = [...selectedFiles.value, ...files]
}

const handleFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement
  const files = Array.from(target.files || [])
  selectedFiles.value = [...selectedFiles.value, ...files]
}

const removeFile = (index: number) => {
  selectedFiles.value.splice(index, 1)
}

const formatFileSize = (bytes: number): string => {
  if (bytes < 1024) return bytes + ' B'
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB'
  return (bytes / (1024 * 1024)).toFixed(1) + ' MB'
}

const goToPrevious = () => {
  router.push('/wizard/step3')
}

const handleSubmit = async () => {
  submitError.value = ''
  siteUrl.value = ''
  loading.value = true
  
  try {
    const step1Data = JSON.parse(sessionStorage.getItem('wizardStep1') || '{}')
    const step2Data = JSON.parse(sessionStorage.getItem('wizardStep2') || '{}')
    const step3Data = JSON.parse(sessionStorage.getItem('wizardStep3') || '{}')
    
    const formData = new FormData()
    formData.append('name', step1Data.name)
    formData.append('abn', step1Data.abn)
    formData.append('logo', step1Data.logo || '')
    formData.append('employees', JSON.stringify(step3Data.employees || []))
    formData.append('tradeIds', JSON.stringify(step2Data.tradeIds))
    
    selectedFiles.value.forEach((file) => {
      formData.append('documents[]', file)
    })
    
    const response = await axios.post(`${API_URL}/api/wizard/step5`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    
    if (response.data.success) {
      sessionStorage.removeItem('wizardStep1')
      sessionStorage.removeItem('wizardStep2')
      sessionStorage.removeItem('wizardStep3')
      
      // Redirect to the generated site
      window.location.href = `/site/${response.data.slug}`
    }
  } catch (error: any) {
    if (error.response?.data?.errors) {
      const apiErrors = error.response.data.errors
      submitError.value = Array.isArray(apiErrors) ? apiErrors.join(', ') : JSON.stringify(apiErrors)
    } else {
      submitError.value = 'Failed to submit form. Please try again.'
    }
  } finally {
    loading.value = false
  }
}
</script>
