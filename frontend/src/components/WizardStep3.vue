<template>
  <div class="max-w-3xl mx-auto">
    <Card class="shadow-2xl border-2 dark:border-primary/20">
      <CardHeader class="space-y-6 pb-8">
        <div class="space-y-3">
          <div class="flex items-center justify-between text-sm text-muted-foreground mb-2">
            <span class="font-medium">Step 3 of 5</span>
            <span class="font-medium">60% Complete</span>
          </div>
          <Progress :model-value="60" class="h-3" />
        </div>
        <div class="space-y-3 pt-2">
          <CardTitle class="text-3xl">Team Members</CardTitle>
          <CardDescription class="text-base">List your employees or team members (optional)</CardDescription>
        </div>
      </CardHeader>
      
      <CardContent class="space-y-8 px-8 pb-8">
        <form @submit.prevent="handleSubmit" class="space-y-8">
          <div class="space-y-3">
            <Label class="text-base">Employee Names</Label>
            <div class="space-y-3">
              <div
                v-for="(employee, index) in employees"
                :key="index"
                class="flex items-center gap-3"
              >
                <Input
                  v-model="employees[index]"
                  type="text"
                  placeholder="e.g., John Smith"
                  class="h-12 text-base rounded-lg flex-1"
                />
                <Button
                  v-if="employees.length > 1"
                  type="button"
                  variant="ghost"
                  size="sm"
                  @click="removeEmployee(index)"
                  class="text-destructive hover:text-destructive"
                >
                  Remove
                </Button>
              </div>
            </div>
            <Button
              type="button"
              variant="outline"
              size="sm"
              @click="addEmployee"
              class="mt-2"
            >
              + Add Another Employee
            </Button>
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
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Progress } from '@/components/ui/progress'
import { Alert, AlertDescription } from '@/components/ui/alert'

const router = useRouter()

const employees = ref<string[]>([''])
const submitError = ref('')
const loading = ref(false)

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000'

onMounted(() => {
  // Check if previous steps are completed
  const step1Data = sessionStorage.getItem('wizardStep1')
  const step2Data = sessionStorage.getItem('wizardStep2')
  
  if (!step1Data || !step2Data) {
    router.push('/wizard/step1')
    return
  }
  
  // Load step 3 data if returning to this step
  const savedData = sessionStorage.getItem('wizardStep3')
  if (savedData) {
    const data = JSON.parse(savedData)
    if (data.employees && data.employees.length > 0) {
      employees.value = data.employees
    }
  }
})

const addEmployee = () => {
  employees.value.push('')
}

const removeEmployee = (index: number) => {
  employees.value.splice(index, 1)
}

const goToPrevious = () => {
  router.push('/wizard/step2')
}

const handleSubmit = async () => {
  submitError.value = ''
  loading.value = true
  
  try {
    // Filter out empty employee names
    const filteredEmployees = employees.value.filter(e => e.trim() !== '')
    
    const response = await axios.post(`${API_URL}/api/wizard/step3`, {
      employees: filteredEmployees
    })
    
    if (response.data.success) {
      // Store step 3 data in sessionStorage
      sessionStorage.setItem('wizardStep3', JSON.stringify({
        employees: filteredEmployees
      }))
      // Navigate to step 4 immediately
      router.push('/wizard/step4')
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
