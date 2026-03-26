<template>
  <div>
    <div class="d-flex align-center mb-4">
      <h1 class="text-h5">Contratos</h1>
      <v-spacer />
      <div class="d-flex align-center ga-2">
        <v-btn color="secondary" variant="outlined" prepend-icon="mdi-refresh" :loading="loading" @click="loadContracts">
          Atualizar
        </v-btn>
        <v-btn color="primary" prepend-icon="mdi-plus" @click="openForm()">
          Novo Contrato
        </v-btn>
      </div>
    </div>

    <!-- Filtros -->
    <v-card class="mb-4" variant="outlined">
      <v-card-text>
        <v-row dense>
          <v-col cols="12" sm="4">
            <v-text-field
              v-model="filters.client_name"
              label="Buscar por cliente"
              prepend-inner-icon="mdi-magnify"
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
              @update:model-value="loadContracts"
            />
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- Tabela -->
    <v-card>
      <v-data-table-server
        :headers="headers"
        :items="contracts"
        :items-length="total"
        :loading="loading"
        :items-per-page="perPage"
        :items-per-page-options="itemsPerPageOptions"
        :page="page"
        @update:page="page = $event; loadContracts()"
        @update:items-per-page="handleItemsPerPageChange"
      >
        <template #loading>
          <div class="d-flex flex-column align-center justify-center py-8">
            <v-progress-circular indeterminate color="primary" size="38" />
            <span class="text-body-2 text-medium-emphasis mt-3">Carregando contratos...</span>
          </div>
        </template>

        <template #item.client="{ item }">
          <div class="text-center">{{ item.client?.name || '-' }}</div>
        </template>

        <template #item.id="{ item }">
          <div class="text-center">{{ item.id }}</div>
        </template>

        <template #item.start_date="{ item }">
          <div class="text-center">{{ formatDate(item.start_date) }}</div>
        </template>

        <template #item.end_date="{ item }">
          <div class="text-center">{{ item.end_date ? formatDate(item.end_date) : 'Indeterminado' }}</div>
        </template>

        <template #item.total="{ item }">
          <div class="text-center">
            <strong>R$ {{ formatCurrency(item.calculation?.total || 0) }}</strong>
            <div v-if="item.calculation?.discount_value > 0" class="text-caption text-success">
              -R$ {{ formatCurrency(item.calculation.discount_value) }} ({{ item.calculation.discount_percent }}%)
            </div>
          </div>
        </template>

        <template #item.status="{ item }">
          <div class="d-flex justify-center">
            <v-chip
              :color="item.status === 'A' ? 'success' : 'error'"
              size="small"
              label
            >
              {{ item.status === 'A' ? 'Ativo' : 'Cancelado' }}
            </v-chip>
          </div>
        </template>

        <template #item.actions="{ item }">
          <div class="d-flex justify-center">
            <v-btn icon="mdi-eye" size="small" variant="text" color="info" @click="viewContract(item)" />
            <v-btn icon="mdi-pencil" size="small" variant="text" color="primary" @click="openForm(item)" :disabled="item.status === 'C'" />
            <v-btn icon="mdi-delete" size="small" variant="text" color="error" @click="confirmDelete(item)" />
          </div>
        </template>

        <template #no-data>
          <div class="text-center py-4 text-medium-emphasis">
            Nenhum contrato encontrado.
          </div>
        </template>
      </v-data-table-server>
    </v-card>

    <!-- Dialog Formulário -->
    <ContractForm
      v-model="showForm"
      :contract="selectedContract"
      @saved="onSaved"
    />

    <!-- Dialog Confirmação de Exclusão -->
    <v-dialog v-model="showDeleteDialog" max-width="400">
      <v-card>
        <v-card-title>Confirmar Exclusão</v-card-title>
        <v-card-text>Deseja realmente excluir este contrato?</v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="showDeleteDialog = false">Cancelar</v-btn>
          <v-btn color="error" variant="flat" :loading="deleting" @click="deleteContract">Excluir</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'
import contractService from '@/services/contractService'
import ContractForm from './ContractForm.vue'

const toast = useToast()
const router = useRouter()

const contracts = ref([])
const total = ref(0)
const page = ref(1)
const perPage = ref(10)
const itemsPerPageOptions = [10, 20, 50, { title: 'Todos', value: -1 }]
const loading = ref(false)
const showForm = ref(false)
const selectedContract = ref(null)
const showDeleteDialog = ref(false)
const contractToDelete = ref(null)
const deleting = ref(false)

const filters = ref({
  client_name: '',
  status: null,
})

const statusOptions = [
  { title: 'Ativo', value: 'A' },
  { title: 'Cancelado', value: 'C' },
]

const headers = [
  { title: 'ID', key: 'id', sortable: false, width: '70px', align: 'center' },
  { title: 'Cliente', key: 'client', sortable: false, align: 'center' },
  { title: 'Data Início', key: 'start_date', sortable: false, width: '130px', align: 'center' },
  { title: 'Data Término', key: 'end_date', sortable: false, width: '130px', align: 'center' },
  { title: 'Valor Mensal', key: 'total', sortable: false, width: '180px', align: 'center' },
  { title: 'Status', key: 'status', sortable: false, width: '110px', align: 'center' },
  { title: 'Ações', key: 'actions', sortable: false, width: '140px', align: 'center' },
]

let debounceTimer = null
function debouncedLoad() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => loadContracts(), 400)
}

function handleItemsPerPageChange(value) {
  perPage.value = Number(value)
  page.value = 1
  loadContracts()
}

function formatCurrency(value) {
  return parseFloat(value).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  const date = new Date(dateStr + 'T00:00:00')
  return date.toLocaleDateString('pt-BR')
}

async function loadContracts() {
  loading.value = true
  try {
    const params = { page: page.value, per_page: perPage.value }
    if (filters.value.client_name) params.client_name = filters.value.client_name
    if (filters.value.status) params.status = filters.value.status

    const { data } = await contractService.list(params)
    contracts.value = data.data
    total.value = data.meta.total
  } catch (error) {
    toast.error('Erro ao carregar contratos.')
  } finally {
    loading.value = false
  }
}

function viewContract(contract) {
  router.push({ name: 'contract-detail', params: { id: contract.id } })
}

function openForm(contract = null) {
  selectedContract.value = contract ? { ...contract } : null
  showForm.value = true
}

function onSaved() {
  showForm.value = false
  loadContracts()
}

function confirmDelete(contract) {
  contractToDelete.value = contract
  showDeleteDialog.value = true
}

async function deleteContract() {
  deleting.value = true
  try {
    await contractService.delete(contractToDelete.value.id)
    toast.success('Contrato excluído com sucesso.')
    showDeleteDialog.value = false
    loadContracts()
  } catch (error) {
    toast.error(error.response?.data?.message || 'Erro ao excluir contrato.')
  } finally {
    deleting.value = false
  }
}

onMounted(loadContracts)
</script>
