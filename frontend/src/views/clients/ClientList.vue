<template>
  <div>
    <div class="d-flex align-center mb-4">
      <h1 class="text-h5">Clientes</h1>
      <v-spacer />
      <div class="d-flex align-center ga-2">
        <v-btn color="secondary" variant="outlined" prepend-icon="mdi-refresh" :loading="loading" @click="loadClients">
          Atualizar
        </v-btn>
        <v-btn color="primary" prepend-icon="mdi-plus" @click="openForm()">
          Novo Cliente
        </v-btn>
      </div>
    </div>

    <!-- Filtros -->
    <v-card class="mb-4" variant="outlined">
      <v-card-text>
        <v-row dense>
          <v-col cols="12" sm="4">
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
          <v-col cols="12" sm="4">
            <v-text-field
              v-model="filters.document"
              label="Buscar por CPF/CNPJ"
              prepend-inner-icon="mdi-card-account-details"
              clearable
              density="compact"
              hide-details
              @update:model-value="debouncedLoad"
            />
          </v-col>
          <v-col cols="12" sm="4">
            <v-select
              v-model="filters.status"
              :items="statusOptions"
              label="Status"
              clearable
              density="compact"
              hide-details
              @update:model-value="loadClients"
            />
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- Tabela -->
    <v-card>
      <v-data-table-server
        :headers="headers"
        :items="clients"
        :items-length="total"
        :loading="loading"
        :items-per-page="perPage"
        :items-per-page-options="itemsPerPageOptions"
        :page="page"
        @update:page="page = $event; loadClients()"
        @update:items-per-page="handleItemsPerPageChange"
      >
        <template #loading>
          <div class="d-flex flex-column align-center justify-center py-8">
            <v-progress-circular indeterminate color="primary" size="38" />
            <span class="text-body-2 text-medium-emphasis mt-3">Carregando clientes...</span>
          </div>
        </template>

        <template #item.document="{ item }">
          <div class="text-center">{{ formatDocument(item.document) }}</div>
        </template>

        <template #item.name="{ item }">
          <div class="text-center">{{ item.name }}</div>
        </template>

        <template #item.email="{ item }">
          <div class="text-center">{{ item.email }}</div>
        </template>

        <template #item.status="{ item }">
          <div class="d-flex justify-center">
            <v-chip
              :color="item.status === 'A' ? 'success' : 'error'"
              size="small"
              label
            >
              {{ item.status === 'A' ? 'Ativo' : 'Inativo' }}
            </v-chip>
          </div>
        </template>

        <template #item.actions="{ item }">
          <div class="d-flex justify-center">
            <v-btn icon="mdi-pencil" size="small" variant="text" color="primary" @click="openForm(item)" />
            <v-btn icon="mdi-delete" size="small" variant="text" color="error" @click="confirmDelete(item)" />
          </div>
        </template>

        <template #no-data>
          <div class="text-center py-4 text-medium-emphasis">
            Nenhum cliente encontrado.
          </div>
        </template>
      </v-data-table-server>
    </v-card>

    <!-- Dialog Formulário -->
    <ClientForm
      v-model="showForm"
      :client="selectedClient"
      @saved="onSaved"
    />

    <!-- Dialog Confirmação de Exclusão -->
    <v-dialog v-model="showDeleteDialog" max-width="400">
      <v-card>
        <v-card-title>Confirmar Exclusão</v-card-title>
        <v-card-text>
          Deseja realmente excluir o cliente <strong>{{ clientToDelete?.name }}</strong>?
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="showDeleteDialog = false">Cancelar</v-btn>
          <v-btn color="error" variant="flat" :loading="deleting" @click="deleteClient">Excluir</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import clientService from '@/services/clientService'
import ClientForm from './ClientForm.vue'

const toast = useToast()

const clients = ref([])
const total = ref(0)
const page = ref(1)
const perPage = ref(10)
const itemsPerPageOptions = [10, 20, 50, { title: 'Todos', value: -1 }]
const loading = ref(false)
const showForm = ref(false)
const selectedClient = ref(null)
const showDeleteDialog = ref(false)
const clientToDelete = ref(null)
const deleting = ref(false)

const filters = ref({
  name: '',
  document: '',
  status: null,
})

const statusOptions = [
  { title: 'Ativo', value: 'A' },
  { title: 'Inativo', value: 'I' },
]

const headers = [
  { title: 'Nome', key: 'name', sortable: false, align: 'center' },
  { title: 'CPF/CNPJ', key: 'document', sortable: false, align: 'center' },
  { title: 'Email', key: 'email', sortable: false, align: 'center' },
  { title: 'Status', key: 'status', sortable: false, width: '100px', align: 'center' },
  { title: 'Ações', key: 'actions', sortable: false, width: '120px', align: 'center' },
]

let debounceTimer = null
function debouncedLoad() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => loadClients(), 400)
}

function handleItemsPerPageChange(value) {
  perPage.value = Number(value)
  page.value = 1
  loadClients()
}

async function loadClients() {
  loading.value = true
  try {
    const params = {
      page: page.value,
      per_page: perPage.value,
      ...filters.value,
    }
    // Remove parâmetros vazios
    Object.keys(params).forEach((key) => {
      if (!params[key]) delete params[key]
    })

    const { data } = await clientService.list(params)
    clients.value = data.data
    total.value = data.meta.total
  } catch (error) {
    toast.error('Erro ao carregar clientes.')
  } finally {
    loading.value = false
  }
}

function formatDocument(doc) {
  if (!doc) return ''
  if (doc.length === 11) {
    return doc.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4')
  }
  if (doc.length === 14) {
    return doc.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5')
  }
  return doc
}

function openForm(client = null) {
  selectedClient.value = client ? { ...client } : null
  showForm.value = true
}

function onSaved() {
  showForm.value = false
  loadClients()
}

function confirmDelete(client) {
  clientToDelete.value = client
  showDeleteDialog.value = true
}

async function deleteClient() {
  deleting.value = true
  try {
    await clientService.delete(clientToDelete.value.id)
    toast.success('Cliente excluído com sucesso.')
    showDeleteDialog.value = false
    loadClients()
  } catch (error) {
    const msg = error.response?.data?.message || 'Erro ao excluir cliente.'
    toast.error(msg)
  } finally {
    deleting.value = false
  }
}

onMounted(loadClients)
</script>
