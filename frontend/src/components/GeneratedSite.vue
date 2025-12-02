<template>
  <div class="min-h-screen bg-background">
    <div v-if="loading" class="flex items-center justify-center min-h-screen">
      <div class="text-center">
        <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-primary border-r-transparent"></div>
        <p class="mt-4 text-muted-foreground">Loading site...</p>
      </div>
    </div>

    <div v-else-if="error" class="flex items-center justify-center min-h-screen">
      <Alert variant="destructive" class="max-w-md">
        <AlertCircle class="h-5 w-5" />
        <AlertDescription class="ml-2">
          {{ error }}
        </AlertDescription>
      </Alert>
    </div>

    <div v-else-if="site" class="container mx-auto px-4 py-12">
      <!-- Header -->
      <header class="text-center mb-16">
        <div v-if="site.logo" class="mb-6">
          <img 
            :src="`${API_URL}/uploads/${site.logo}`" 
            :alt="`${site.name} logo`"
            class="h-32 mx-auto object-contain"
          />
        </div>
        <h1 class="text-5xl font-bold mb-4">{{ site.name }}</h1>
        
        <!-- Contact Details -->
        <div class="flex items-center justify-center gap-6 text-muted-foreground">
          <a 
            v-if="site.mobile"
            :href="`tel:${site.mobile}`" 
            class="flex items-center gap-2 hover:text-primary transition-colors"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
            </svg>
            <span>{{ site.mobile }}</span>
          </a>
          
          <a 
            v-if="site.email"
            :href="`mailto:${site.email}`" 
            class="flex items-center gap-2 hover:text-primary transition-colors"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
            <span>{{ site.email }}</span>
          </a>
        </div>
      </header>

      <!-- Services Section -->
      <section class="max-w-4xl mx-auto mb-16">
        <Card class="shadow-xl">
          <CardHeader>
            <CardTitle class="text-3xl">Our Services</CardTitle>
            <CardDescription class="text-base">
              We specialize in the following trades
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <div
                v-for="trade in site.trades"
                :key="trade"
                class="p-4 rounded-lg bg-primary/10 border border-primary/20 text-center"
              >
                <span class="font-medium text-lg">{{ trade }}</span>
              </div>
            </div>
          </CardContent>
        </Card>
      </section>

      <!-- Team Section -->
      <section v-if="site.employees && site.employees.length > 0" class="max-w-6xl mx-auto mb-16">
        <Card class="shadow-xl">
          <CardHeader>
            <CardTitle class="text-3xl">Our Team</CardTitle>
            <CardDescription class="text-base">
              Meet our skilled professionals
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <div
                v-for="employee in site.employees"
                :key="employee.id"
                class="p-6 rounded-lg border bg-card relative"
                :class="employee.isMainContact ? 'border-primary border-2 bg-primary/5' : ''"
              >
                <!-- Main Contact Badge -->
                <div v-if="employee.isMainContact" class="absolute top-2 right-2">
                  <span class="text-xs px-2 py-1 rounded-full bg-primary text-primary-foreground font-medium">
                    Main Contact
                  </span>
                </div>

                <!-- Profile Picture -->
                <div class="flex justify-center mb-4">
                  <div v-if="employee.profilePicture" class="w-24 h-24 rounded-full overflow-hidden border-2 border-primary/20">
                    <img 
                      :src="`${API_URL}/uploads/profiles/${employee.profilePicture}`" 
                      :alt="employee.name"
                      class="w-full h-full object-cover"
                    />
                  </div>
                  <div v-else class="w-24 h-24 rounded-full bg-muted flex items-center justify-center border-2 border-primary/20">
                    <svg class="w-12 h-12 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                  </div>
                </div>

                <!-- Employee Info -->
                <div class="text-center space-y-2">
                  <h3 class="font-semibold text-lg">{{ employee.name }}</h3>
                  <p v-if="employee.jobTitle" class="text-sm text-muted-foreground">
                    {{ employee.jobTitle }}
                  </p>
                </div>

                <!-- Contact Info -->
                <div v-if="employee.mobile || employee.email" class="mt-4 pt-4 border-t space-y-2">
                  <a 
                    v-if="employee.mobile"
                    :href="`tel:${employee.mobile}`" 
                    class="flex items-center gap-2 text-sm hover:text-primary transition-colors justify-center"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                    <span>{{ employee.mobile }}</span>
                  </a>
                  
                  <a 
                    v-if="employee.email"
                    :href="`mailto:${employee.email}`" 
                    class="flex items-center gap-2 text-sm hover:text-primary transition-colors justify-center"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <span class="truncate">{{ employee.email }}</span>
                  </a>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </section>

      <!-- Documents Section -->
      <section v-if="site.documents && site.documents.length > 0" class="max-w-4xl mx-auto mb-16">
        <Card class="shadow-xl">
          <CardHeader>
            <CardTitle class="text-3xl">Compliance Documents</CardTitle>
            <CardDescription class="text-base">
              Certificates, insurance, and compliance documentation
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
              <a
                v-for="(doc, index) in site.documents"
                :key="index"
                :href="`${API_URL}/uploads/documents/${doc.filename}`"
                target="_blank"
                class="flex items-center gap-3 p-4 border rounded-lg hover:bg-accent transition-colors"
              >
                <svg class="w-6 h-6 text-primary flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium truncate">{{ doc.originalName }}</p>
                  <p class="text-xs text-muted-foreground">Click to download</p>
                </div>
                <svg class="w-5 h-5 text-muted-foreground flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
              </a>
            </div>
          </CardContent>
        </Card>
      </section>

      <!-- Footer -->
      <footer class="text-center mt-16">
        <div class="flex items-center justify-center gap-2 text-sm text-muted-foreground">
          <span>Powered by</span>
          <img 
            src="/logo.png" 
            alt="SubbieHub"
            class="h-6 object-contain"
          />
        </div>
      </footer>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
import { AlertCircle } from 'lucide-vue-next'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Alert, AlertDescription } from '@/components/ui/alert'

interface Document {
  filename: string
  originalName: string
}

interface Employee {
  id: number
  name: string
  jobTitle: string | null
  mobile: string | null
  email: string | null
  profilePicture: string | null
  isMainContact: boolean
}

interface Site {
  name: string
  logo: string | null
  mobile: string | null
  email: string | null
  trades: string[]
  employees: Employee[]
  documents: Document[]
}

const route = useRoute()
const site = ref<Site | null>(null)
const loading = ref(true)
const error = ref('')

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000'

onMounted(async () => {
  const siteId = route.params.id
  
  try {
    const response = await axios.get(`${API_URL}/api/site/${siteId}`)
    if (response.data.success) {
      site.value = response.data.site
    }
  } catch (err: any) {
    if (err.response?.data?.error) {
      error.value = err.response.data.error
    } else {
      error.value = 'Failed to load site. Please check the URL and try again.'
    }
  } finally {
    loading.value = false
  }
})

// Update page title when site data loads
watch(site, (newSite) => {
  if (newSite?.name) {
    document.title = newSite.name
  }
})
</script>
