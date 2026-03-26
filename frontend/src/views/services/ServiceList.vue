<template>
  <div>
    <div class="d-flex align-center mb-4">
      <h1 class="text-h5">Serviços</h1>
      <v-spacer />
      <div class="d-flex align-center ga-2">
        <v-btn color="secondary" variant="outlined" prepend-icon="mdi-refresh" :loading="loading" @click="loadServices">
          Atualizar
        </v-btn>
        <v-btn color="primary" prepend-icon="mdi-plus" @click="openForm()">
          Novo Serviço
        </v-btn>
      </div>
    </div>

    <!-- Filtros -->
    <v-card class="mb-4" variant="outlined">
      <v-card-text>
        <v-row dense>
          <v-col cols="12" sm="6">
            <v-text-field
              v-model="filters.name"
              label="Buscar por nome"
              prepend-inner-icon="mdi-magnify"
              clearable
              density="compact"
              hide-details
              @update:model-value="debouncedLoad"
            />
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- Tabela -->
    <v-card>
      <v-data-table-server
        :headers="headers"
        :items="services"
        :items-length="total"
        :loading="loading"
        :items-per-page="perPage"
        :items-per-page-options="itemsPerPageOptions"
        :page="page"
        @update:page="page = $event; loadServices()"
        @update:items-per-page="handleItemsPerPageChange"
      >
        <template #loading>
          <div class="d-flex flex-column align-center justify-center py-8">
            <v-progress-circular indeterminate color="primary" size="38" />
            <span class="text-body-2 text-medium-emphasis mt-3">Carregando serviços...</span>
          </div>
        </template>

        <template #item.name="{ item }">
          <div class="text-center">{{ item.name }}</div>
        </template>

        <template #item.base_monthly_value="{ item }">
          <div class="text-center">R$ {{ formatCurrency(item.base_monthly_value) }}</div>
        </template>

        <template #item.actions="{ item }">
          <div class="d-flex justify-center">
            <v-btn icon="mdi-pencil" size="small" variant="text" color="primary" @click="openForm(item)" />
            <v-btn icon="mdi-delete" size="small" variant="text" color="error" @click="confirmDelete(item)" />
          </div>
        </template>

        <template #no-data>
          <div class="text-center py-4 text-medium-emphasis">
            Nenhum serviço encontrado.
          </div>
        </template>
      </v-data-table-server>
    </v-card>

    <!-- Dialog Formulário -->
    <ServiceForm
      v-model="showForm"
      :service="selectedService"
      @saved="onSaved"
    />

    <!-- Dialog Confirmação -->
    <v-dialog v-model="showDeleteDialog" max-width="400">
      <v-card>
        <v-card-title>Confirmar Exclusão</v-card-title>
        <v-card-text>
          Deseja realmente excluir o serviço <strong>{{ serviceToDelete?.name }}</strong>?
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="showDeleteDialog = false">Cancelar</v-btn>
          <v-btn color="error" variant="flat" :loading="deleting" @click="deleteService">Excluir</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import serviceService from '@/services/serviceService'
import ServiceForm from './ServiceForm.vue'

const toast = useToast()

const services = ref([])
const total = ref(0)
const page = ref(1)
const perPage = ref(10)
const itemsPerPageOptions = [10, 20, 50, { title: 'Todos', value: -1 }]
const loading = ref(false)
const showForm = ref(false)
const selectedService = ref(null)
const showDeleteDialog = ref(false)
const serviceToDelete = ref(null)
const deleting = ref(false)

const filters = ref({ name: '' })

const headers = [
  { title: 'Nome', key: 'name', sortable: false, align: 'center' },
  { title: 'Valor Base Mensal', key: 'base_monthly_value', sortable: false, width: '200px', align: 'center' },
  { title: 'Ações', key: 'actions', sortable: false, width: '120px', align: 'center' },
]

let debounceTimer = null
function debouncedLoad() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => loadServices(), 400)
}

function handleItemsPerPageChange(value) {
  perPage.value = Number(value)
  page.value = 1
  loadServices()
}

function formatCurrency(value) {
  return parseFloat(value).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

async function loadServices() {
  loading.value = true
  try {
    const params = { page: page.value, per_page: perPage.value }
    if (filters.value.name) params.name = filters.value.name

    const { data } = await serviceService.list(params)
    services.value = data.data
    total.value = data.meta.total
  } catch (error) {
    toast.error('Erro ao carregar serviços.')
  } finally {
    loading.value = false
  }
}

function openForm(service = null) {
  selectedService.value = service ? { ...service } : null
  showForm.value = true
}

function onSaved() {
  showForm.value = false
  loadServices()
}

function confirmDelete(service) {
  serviceToDelete.value = service
  showDeleteDialog.value = true
}

async function deleteService() {
  deleting.value = true
  try {
    await serviceService.delete(serviceToDelete.value.id)
    toast.success('Serviço excluído com sucesso.')
    showDeleteDialog.value = false
    loadServices()
  } catch (error) {
    const msg = error.response?.data?.message || 'Erro ao excluir serviço.'
    toast.error(msg)
  } finally {
    deleting.value = false
  }
}

onMounted(loadServices)
</script>
