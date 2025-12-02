<template>
  <div class="max-w-3xl mx-auto">
    <Card class="shadow-2xl border-2 dark:border-primary/20">
      <CardHeader class="space-y-6 pb-8">
        <div class="space-y-3">
          <div class="flex items-center justify-between text-sm text-muted-foreground mb-2">
            <span class="font-medium">Step 3 of 4</span>
            <span class="font-medium">75% Complete</span>
          </div>
          <Progress :model-value="75" class="h-3" />
        </div>
        <div class="space-y-3 pt-2">
          <CardTitle class="text-3xl">Team Members</CardTitle>
          <CardDescription class="text-base">Add your employees with contact information. At least one must be the main contact.</CardDescription>
        </div>
      </CardHeader>
      
      <CardContent class="space-y-8 px-8 pb-8">
        <form @submit.prevent="handleSubmit" class="space-y-8">
          <div class="space-y-6">
            <div
              v-for="(employee, index) in employees"
              :key="index"
              class="p-6 border-2 rounded-lg space-y-4"
              :class="employee.isMainContact ? 'border-primary bg-primary/5' : 'border-muted'"
            >
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold">Employee {{ index + 1 }}</h3>
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

              <div class="space-y-4">
                <!-- Profile Picture -->
                <div class="space-y-2">
                  <Label class="text-sm">Profile Picture (optional)</Label>
                  <div v-if="employee.profilePicturePreview" class="mb-2 p-3 border rounded-lg bg-muted/20">
                    <div class="flex items-center justify-between">
                      <div class="flex items-center gap-3">
                        <img :src="employee.profilePicturePreview" alt="Profile" class="h-12 w-12 rounded-full object-cover" />
                        <span class="text-sm text-muted-foreground">{{ employee.profilePictureFilename }}</span>
                      </div>
                      <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        @click="clearProfilePicture(index)"
                        class="text-destructive hover:text-destructive"
                      >
                        Remove
                      </Button>
                    </div>
                  </div>
                  <Input
                    v-if="!employee.profilePicturePreview"
                    type="file"
                    accept="image/*"
                    @change="(e) => handleProfilePictureChange(e, index)"
                    class="h-10 text-sm rounded-lg cursor-pointer"
                  />
                </div>

                <!-- Name -->
                <div class="space-y-2">
                  <Label class="text-sm">Name *</Label>
                  <Input
                    v-model="employee.name"
                    type="text"
                    placeholder="e.g., John Smith"
                    required
                    class="h-10 text-sm rounded-lg"
                  />
                </div>

                <!-- Job Title -->
                <div class="space-y-2">
                  <Label class="text-sm">Job Title</Label>
                  <Input
                    v-model="employee.jobTitle"
                    type="text"
                    placeholder="e.g., Project Manager"
                    class="h-10 text-sm rounded-lg"
                  />
                </div>

                <!-- Mobile -->
                <div class="space-y-2">
                  <Label class="text-sm">Mobile</Label>
                  <Input
                    v-model="employee.mobile"
                    type="tel"
                    placeholder="e.g., 0412 345 678"
                    class="h-10 text-sm rounded-lg"
                  />
                </div>

                <!-- Email -->
                <div class="space-y-2">
                  <Label class="text-sm">Email</Label>
                  <Input
                    v-model="employee.email"
                    type="email"
                    placeholder="e.g., john@example.com"
                    class="h-10 text-sm rounded-lg"
                  />
                </div>

                <!-- Main Contact -->
                <div class="flex items-center gap-2 pt-2">
                  <input
                    type="radio"
                    :id="`main-contact-${index}`"
                    :checked="employee.isMainContact"
                    @change="setMainContact(index)"
                    class="w-4 h-4 text-primary"
                  />
                  <Label :for="`main-contact-${index}`" class="text-sm font-medium cursor-pointer">
                    Main Point of Contact
                  </Label>
                </div>
              </div>
            </div>

            <Button
              type="button"
              variant="outline"
              size="sm"
              @click="addEmployee"
              class="w-full"
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

interface Employee {
  name: string
  jobTitle: string
  mobile: string
  email: string
  profilePicture: File | null
  profilePicturePreview: string | null
  profilePictureFilename: string | null
  isMainContact: boolean
}

const employees = ref<Employee[]>([
  {
    name: '',
    jobTitle: '',
    mobile: '',
    email: '',
    profilePicture: null,
    profilePicturePreview: null,
    profilePictureFilename: null,
    isMainContact: true
  }
])
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
      employees.value = data.employees.map((emp: any) => ({
        name: emp.name || '',
        jobTitle: emp.jobTitle || '',
        mobile: emp.mobile || '',
        email: emp.email || '',
        profilePicture: null,
        profilePicturePreview: emp.profilePicturePreview || null,
        profilePictureFilename: emp.profilePictureFilename || null,
        isMainContact: emp.isMainContact || false
      }))
    }
  }
})

const addEmployee = () => {
  employees.value.push({
    name: '',
    jobTitle: '',
    mobile: '',
    email: '',
    profilePicture: null,
    profilePicturePreview: null,
    profilePictureFilename: null,
    isMainContact: false
  })
}

const removeEmployee = (index: number) => {
  employees.value.splice(index, 1)
  // If we removed the main contact, set the first employee as main contact
  if (!employees.value.some(e => e.isMainContact) && employees.value.length > 0) {
    employees.value[0].isMainContact = true
  }
}

const setMainContact = (index: number) => {
  employees.value.forEach((emp, i) => {
    emp.isMainContact = i === index
  })
}

const handleProfilePictureChange = (event: Event, index: number) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  
  if (file) {
    employees.value[index].profilePicture = file
    employees.value[index].profilePictureFilename = file.name
    
    const reader = new FileReader()
    reader.onload = (e) => {
      employees.value[index].profilePicturePreview = e.target?.result as string
    }
    reader.readAsDataURL(file)
  }
}

const clearProfilePicture = (index: number) => {
  employees.value[index].profilePicture = null
  employees.value[index].profilePicturePreview = null
  employees.value[index].profilePictureFilename = null
}

const goToPrevious = () => {
  router.push('/wizard/step2')
}

const validateForm = (): boolean => {
  // Check at least one employee has a name
  const hasValidEmployee = employees.value.some(e => e.name.trim() !== '')
  if (!hasValidEmployee) {
    submitError.value = 'At least one employee with a name is required'
    return false
  }
  
  // Check that at least one is marked as main contact
  const hasMainContact = employees.value.some(e => e.isMainContact)
  if (!hasMainContact) {
    submitError.value = 'Please select one employee as the main contact'
    return false
  }
  
  return true
}

const handleSubmit = async () => {
  submitError.value = ''
  
  if (!validateForm()) {
    return
  }
  
  loading.value = true
  
  try {
    // Filter out employees without names
    const validEmployees = employees.value.filter(e => e.name.trim() !== '')
    
    const formData = new FormData()
    
    // Prepare employee data
    const employeesData = validEmployees.map((emp, index) => ({
      name: emp.name,
      jobTitle: emp.jobTitle,
      mobile: emp.mobile,
      email: emp.email,
      isMainContact: emp.isMainContact,
      profilePictureIndex: emp.profilePicture ? index : null
    }))
    
    formData.append('employees', JSON.stringify(employeesData))
    
    // Append profile pictures
    validEmployees.forEach((emp, index) => {
      if (emp.profilePicture) {
        formData.append(`profilePicture_${index}`, emp.profilePicture)
      }
    })
    
    const response = await axios.post(`${API_URL}/api/wizard/step3`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    
    if (response.data.success) {
      // Update employee data with uploaded profile picture filenames
      const profilePictures = response.data.profilePictures || {}
      const employeesToSave = validEmployees.map((emp, index) => ({
        name: emp.name,
        jobTitle: emp.jobTitle,
        mobile: emp.mobile,
        email: emp.email,
        isMainContact: emp.isMainContact,
        profilePicture: profilePictures[index] || null,
        profilePicturePreview: emp.profilePicturePreview,
        profilePictureFilename: emp.profilePictureFilename
      }))
      
      // Store step 3 data in sessionStorage
      sessionStorage.setItem('wizardStep3', JSON.stringify({
        employees: employeesToSave
      }))
      
      // Navigate to step 4
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
