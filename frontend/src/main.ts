import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import './assets/index.css'
import App from './App.vue'
import WizardStep1 from './components/WizardStep1.vue'
import WizardStep2 from './components/WizardStep2.vue'
import WizardStep3 from './components/WizardStep3.vue'
import WizardStep4 from './components/WizardStep4.vue'
import GeneratedSite from './components/GeneratedSite.vue'
import SubcontractorSearch from './components/SubcontractorSearch.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      redirect: '/search'
    },
    {
      path: '/wizard',
      redirect: '/wizard/step1'
    },
    {
      path: '/wizard/step1',
      name: 'step1',
      component: WizardStep1
    },
    {
      path: '/wizard/step2',
      name: 'step2',
      component: WizardStep2  // Trades
    },
    {
      path: '/wizard/step3',
      name: 'step3',
      component: WizardStep3  // Team Members
    },
    {
      path: '/wizard/step4',
      name: 'step4',
      component: WizardStep4  // Documents
    },
    {
      path: '/site/:id',
      name: 'site',
      component: GeneratedSite
    },
    {
      path: '/search',
      name: 'search',
      component: SubcontractorSearch
    }
  ]
})

const app = createApp(App)
app.use(router)
app.mount('#app')
