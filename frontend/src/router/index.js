import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/',
    redirect: '/clients',
  },
  {
    path: '/clients',
    name: 'clients',
    component: () => import('../views/clients/ClientList.vue'),
  },
  {
    path: '/services',
    name: 'services',
    component: () => import('../views/services/ServiceList.vue'),
  },
  {
    path: '/contracts',
    name: 'contracts',
    component: () => import('../views/contracts/ContractList.vue'),
  },
  {
    path: '/contracts/:id',
    name: 'contract-detail',
    component: () => import('../views/contracts/ContractDetail.vue'),
    props: true,
  },
  {
    path: '/settings',
    name: 'settings',
    component: () => import('../views/settings/SettingsList.vue'),
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
