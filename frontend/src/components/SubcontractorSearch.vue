<template>
  <div class="max-w-4xl mx-auto">
    <Card class="shadow-xl">
      <CardHeader>
        <CardTitle class="text-3xl">Find Subcontractors</CardTitle>
        <CardDescription class="text-base">
          Search for subcontractors by name, ABN, or filter by trade
        </CardDescription>
      </CardHeader>
      <CardContent>
        <!-- Search Filters -->
        <div class="space-y-4 mb-6">
          <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
              <Label for="search" class="mb-2 block">Search</Label>
              <div class="relative">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                <Input
                  id="search"
                  v-model="searchQuery"
                  placeholder="Search by name or ABN..."
                  class="pl-10"
                  @input="debouncedSearch"
                />
              </div>
            </div>
          </div>
          
          <div>
            <Label class="mb-2 block">Filter by Trade</Label>
            <MultiSelect
              v-model="selectedTradeIds"
              :options="tradeOptions"
              placeholder="Select trades to filter..."
              @update:modelValue="handleSearch"
            />
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-12">
          <div class="text-center">
            <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-primary border-r-transparent"></div>
            <p class="mt-4 text-muted-foreground">Searching...</p>
          </div>
        </div>

        <!-- Error State -->
        <Alert v-else-if="error" variant="destructive" class="mb-4">
          <AlertCircle class="h-5 w-5" />
          <AlertDescription class="ml-2">{{ error }}</AlertDescription>
        </Alert>

        <!-- Results -->
        <div v-else>
          <!-- Results Count -->
          <p class="text-sm text-muted-foreground mb-4">
            {{ pagination.total }} subcontractor{{ pagination.total !== 1 ? 's' : '' }} found
          </p>

          <!-- Empty State -->
          <div v-if="subcontractors.length === 0" class="text-center py-12">
            <Users class="h-12 w-12 mx-auto text-muted-foreground mb-4" />
            <p class="text-lg font-medium text-muted-foreground">No subcontractors found</p>
            <p class="text-sm text-muted-foreground mt-1">Try adjusting your search or filters</p>
          </div>

          <!-- Results Grid -->
          <div v-else class="grid gap-4">
            <a
              v-for="sub in subcontractors"
              :key="sub.id"
              :href="`/site/${sub.slug}`"
              class="block p-4 border rounded-lg hover:bg-accent transition-colors"
            >
              <div class="flex items-start gap-4">
                <div v-if="sub.logo" class="flex-shrink-0">
                  <img
                    :src="`${API_URL}/uploads/${sub.logo}`"
                    :alt="`${sub.name} logo`"
                    class="h-16 w-16 object-contain rounded-lg bg-muted"
                  />
                </div>
                <div v-else class="flex-shrink-0 h-16 w-16 rounded-lg bg-muted flex items-center justify-center">
                  <Building2 class="h-8 w-8 text-muted-foreground" />
                </div>
                
                <div class="flex-1 min-w-0">
                  <h3 class="font-semibold text-lg truncate">{{ sub.name }}</h3>
                  <p class="text-sm text-muted-foreground">ABN: {{ formatAbn(sub.abn) }}</p>
                  
                  <div class="flex flex-wrap gap-1 mt-2">
                    <span
                      v-for="trade in sub.trades"
                      :key="trade.id"
                      class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-primary"
                    >
                      {{ trade.name }}
                    </span>
                  </div>
                </div>

                <div class="flex-shrink-0 text-muted-foreground">
                  <ExternalLink class="h-5 w-5" />
                </div>
              </div>
            </a>
          </div>

          <!-- Pagination -->
          <div v-if="pagination.totalPages > 1" class="flex items-center justify-center gap-2 mt-6">
            <Button
              variant="outline"
              size="sm"
              :disabled="pagination.page <= 1"
              @click="goToPage(pagination.page - 1)"
            >
              <ChevronLeft class="h-4 w-4" />
              Previous
            </Button>
            
            <span class="text-sm text-muted-foreground px-4">
              Page {{ pagination.page }} of {{ pagination.totalPages }}
            </span>
            
            <Button
              variant="outline"
              size="sm"
              :disabled="pagination.page >= pagination.totalPages"
              @click="goToPage(pagination.page + 1)"
            >
              Next
              <ChevronRight class="h-4 w-4" />
            </Button>
          </div>
        </div>
      </CardContent>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { Search, AlertCircle, Users, Building2, ExternalLink, ChevronLeft, ChevronRight } from 'lucide-vue-next'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Alert, AlertDescription } from '@/components/ui/alert'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import MultiSelect from '@/components/ui/MultiSelect.vue'

interface Trade {
  id: number
  name: string
}

interface Subcontractor {
  id: number
  name: string
  abn: string
  slug: string
  mobile: string | null
  email: string | null
  logo: string | null
  trades: Trade[]
}

interface Pagination {
  page: number
  limit: number
  total: number
  totalPages: number
}

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000'

const searchQuery = ref('')
const selectedTradeIds = ref<number[]>([])
const subcontractors = ref<Subcontractor[]>([])
const loading = ref(false)
const error = ref('')
const tradeOptions = ref<Trade[]>([])
const pagination = ref<Pagination>({
  page: 1,
  limit: 10,
  total: 0,
  totalPages: 0
})

// Debounce timer
let debounceTimer: ReturnType<typeof setTimeout> | null = null

const debouncedSearch = () => {
  if (debounceTimer) {
    clearTimeout(debounceTimer)
  }
  debounceTimer = setTimeout(() => {
    pagination.value.page = 1
    handleSearch()
  }, 300)
}

const handleSearch = async () => {
  loading.value = true
  error.value = ''

  try {
    const params = new URLSearchParams()
    if (searchQuery.value) {
      params.append('q', searchQuery.value)
    }
    if (selectedTradeIds.value.length > 0) {
      params.append('tradeIds', selectedTradeIds.value.join(','))
    }
    params.append('page', pagination.value.page.toString())
    params.append('limit', pagination.value.limit.toString())

    const response = await axios.get(`${API_URL}/api/search/subcontractors?${params.toString()}`)
    
    if (response.data.success) {
      subcontractors.value = response.data.subcontractors
      pagination.value = response.data.pagination
    }
  } catch (err: any) {
    error.value = err.response?.data?.error || 'Failed to search subcontractors'
  } finally {
    loading.value = false
  }
}

const goToPage = (page: number) => {
  pagination.value.page = page
  handleSearch()
}

const formatAbn = (abn: string): string => {
  // Format ABN as XX XXX XXX XXX
  return abn.replace(/(\d{2})(\d{3})(\d{3})(\d{3})/, '$1 $2 $3 $4')
}

const loadTrades = async () => {
  try {
    const response = await axios.get(`${API_URL}/api/wizard/trades`)
    if (response.data.success) {
      tradeOptions.value = response.data.trades
    }
  } catch (err) {
    console.error('Failed to load trades:', err)
  }
}

onMounted(async () => {
  await loadTrades()
  await handleSearch()
})
</script>
