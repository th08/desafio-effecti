<template>
  <div>
    <div class="d-flex align-center mb-4">
      <v-btn icon="mdi-arrow-left" variant="text" @click="$router.push('/contracts')" />
      <h1 class="text-h5 ml-2">Detalhes do Contrato #{{ contract?.id }}</h1>
      <v-spacer />
      <v-btn class="mr-2" color="secondary" variant="outlined" prepend-icon="mdi-refresh" :loading="loading" @click="loadContract">
        Atualizar
      </v-btn>
      <v-chip
        v-if="contract"
        :color="contract.status === 'A' ? 'success' : 'error'"
        label
        class="mr-2"
      >
        {{ contract.status === 'A' ? 'Ativo' : 'Cancelado' }}
      </v-chip>
      <v-btn
        v-if="contract?.status === 'A'"
        color="error"
        variant="outlined"
        prepend-icon="mdi-cancel"
        @click="showCancelDialog = true"
      >
        Cancelar Contrato
      </v-btn>
    </div>

    <v-progress-linear v-if="loading" indeterminate color="primary" />

    <v-row v-if="loading" class="mt-2">
      <v-col cols="12" md="6">
        <v-skeleton-loader type="article, list-item-three-line, list-item-three-line" />
      </v-col>
      <v-col cols="12" md="6">
        <v-skeleton-loader type="article, list-item-three-line, list-item-three-line" />
      </v-col>
      <v-col cols="12">
        <v-skeleton-loader type="article, table-row-divider@4" />
      </v-col>
      <v-col cols="12">
        <v-skeleton-loader type="article, paragraph" />
      </v-col>
    </v-row>

    <template v-if="contract && !loading">
      <!-- Informações do Contrato -->
      <v-row>
        <v-col cols="12" md="6">
          <v-card variant="outlined">
            <v-card-title class="text-subtitle-1">Informações do Contrato</v-card-title>
            <v-card-text>
              <v-list density="compact">
                <v-list-item>
                  <template #prepend><v-icon icon="mdi-account" color="primary" size="small" class="mr-2" /></template>
                  <v-list-item-title>Cliente</v-list-item-title>
                  <v-list-item-subtitle>{{ contract.client?.name }}</v-list-item-subtitle>
                </v-list-item>
                <v-list-item>
                  <template #prepend><v-icon icon="mdi-email" color="primary" size="small" class="mr-2" /></template>
                  <v-list-item-title>Email</v-list-item-title>
                  <v-list-item-subtitle>{{ contract.client?.email }}</v-list-item-subtitle>
                </v-list-item>
                <v-list-item>
                  <template #prepend><v-icon icon="mdi-calendar-start" color="primary" size="small" class="mr-2" /></template>
                  <v-list-item-title>Data de Início</v-list-item-title>
                  <v-list-item-subtitle>{{ formatDate(contract.start_date) }}</v-list-item-subtitle>
                </v-list-item>
                <v-list-item>
                  <template #prepend><v-icon icon="mdi-calendar-end" color="primary" size="small" class="mr-2" /></template>
                  <v-list-item-title>Data de Término</v-list-item-title>
                  <v-list-item-subtitle>{{ contract.end_date ? formatDate(contract.end_date) : 'Indeterminado' }}</v-list-item-subtitle>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="6">
          <v-card variant="outlined">
            <v-card-title class="text-subtitle-1">Resumo Financeiro</v-card-title>
            <v-card-text>
              <v-list density="compact">
                <v-list-item>
                  <v-list-item-title>Subtotal</v-list-item-title>
                  <template #append>
                    <span class="text-body-1">R$ {{ formatCurrency(contract.calculation?.subtotal || 0) }}</span>
                  </template>
                </v-list-item>
                <v-list-item v-if="contract.calculation?.discount_value > 0">
                  <v-list-item-title class="text-success">
                    Desconto ({{ contract.calculation.discount_percent }}%)
                  </v-list-item-title>
                  <template #append>
                    <span class="text-body-1 text-success">-R$ {{ formatCurrency(contract.calculation.discount_value) }}</span>
                  </template>
                </v-list-item>
                <v-divider class="my-2" />
                <v-list-item>
                  <v-list-item-title class="text-h6">Total Mensal</v-list-item-title>
                  <template #append>
                    <span class="text-h6 text-primary">R$ {{ formatCurrency(contract.calculation?.total || 0) }}</span>
                  </template>
                </v-list-item>
              </v-list>

              <div v-if="contract.calculation?.applied_rules?.length" class="mt-2">
                <v-chip
                  v-for="(rule, i) in contract.calculation.applied_rules"
                  :key="i"
                  color="success"
                  size="small"
                  label
                  class="mr-1 mb-1"
                >
                  {{ rule.rule_description || rule.rule_name || rule }}
                </v-chip>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Itens do Contrato -->
      <v-card class="mt-4" variant="outlined">
        <v-card-title class="d-flex align-center text-subtitle-1">
          Itens do Contrato
          <v-spacer />
          <v-btn
            v-if="contract.status === 'A'"
            color="primary"
            size="small"
            prepend-icon="mdi-plus"
            @click="openItemForm()"
          >
            Adicionar Item
          </v-btn>
        </v-card-title>

        <v-data-table
          :headers="itemHeaders"
          :items="contract.items || []"
          :loading="loading"
          density="comfortable"
          :items-per-page="-1"
          hide-default-footer
        >
          <template #loading>
            <div class="d-flex flex-column align-center justify-center py-8">
              <v-progress-circular indeterminate color="primary" size="34" />
              <span class="text-body-2 text-medium-emphasis mt-3">Carregando itens do contrato...</span>
            </div>
          </template>

          <template #item.service="{ item }">
            <div class="text-center">{{ item.service?.name || '-' }}</div>
          </template>

          <template #item.quantity="{ item }">
            <div class="text-center">{{ item.quantity }}</div>
          </template>

          <template #item.unit_value="{ item }">
            <div class="text-center">R$ {{ formatCurrency(item.unit_value) }}</div>
          </template>

          <template #item.subtotal="{ item }">
            <div class="text-center">
              <strong>R$ {{ formatCurrency(item.subtotal) }}</strong>
            </div>
          </template>

          <template #item.actions="{ item }">
            <div class="d-flex justify-center">
              <template v-if="contract.status === 'A'">
                <v-btn icon="mdi-pencil" size="small" variant="text" color="primary" @click="openItemForm(item)" />
                <v-btn icon="mdi-delete" size="small" variant="text" color="error" @click="confirmRemoveItem(item)" />
              </template>
            </div>
          </template>

          <template #no-data>
            <div class="text-center py-4 text-medium-emphasis">
              Nenhum item adicionado a este contrato.
            </div>
          </template>
        </v-data-table>
      </v-card>

      <!-- Histórico -->
      <v-card class="mt-4" variant="outlined">
        <v-card-title class="text-subtitle-1">Histórico de Alterações</v-card-title>
        <v-card-text>
          <v-timeline v-if="history.length" density="compact" side="end">
            <v-timeline-item
              v-for="entry in history"
              :key="entry.id"
              :dot-color="getHistoryColor(entry.action)"
              size="small"
            >
              <div class="d-flex justify-space-between align-center mb-1">
                <v-chip :color="getHistoryColor(entry.action)" size="x-small" label>
                  {{ translateAction(entry.action) }}
                </v-chip>
                <span class="text-caption text-medium-emphasis">{{ formatDateTime(entry.created_at) }}</span>
              </div>
              <div class="text-body-2">{{ entry.description }}</div>
            </v-timeline-item>
          </v-timeline>
          <div v-else class="text-center text-medium-emphasis py-4">
            Nenhum registro de alteração.
          </div>
        </v-card-text>
      </v-card>
    </template>

    <!-- Dialog Item -->
    <ContractItemForm
      v-model="showItemForm"
      :contract-id="contractId"
      :item="selectedItem"
      @saved="onItemSaved"
    />

    <!-- Dialog Remover Item -->
    <v-dialog v-model="showRemoveItemDialog" max-width="400">
      <v-card>
        <v-card-title>Confirmar Remoção</v-card-title>
        <v-card-text>Remover o item "{{ itemToRemove?.service?.name }}" deste contrato?</v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="showRemoveItemDialog = false">Cancelar</v-btn>
          <v-btn color="error" variant="flat" :loading="removingItem" @click="removeItem">Remover</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Dialog Cancelar Contrato -->
    <v-dialog v-model="showCancelDialog" max-width="400">
      <v-card>
        <v-card-title>Cancelar Contrato</v-card-title>
        <v-card-text>
          <p class="mb-2">Tem certeza que deseja cancelar este contrato? Esta ação não pode ser desfeita.</p>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="showCancelDialog = false">Voltar</v-btn>
          <v-btn color="error" variant="flat" :loading="cancelling" @click="cancelContract">Confirmar Cancelamento</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'
import contractService from '@/services/contractService'
import ContractItemForm from './ContractItemForm.vue'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const contractId = route.params.id
const contract = ref(null)
const history = ref([])
const loading = ref(false)
const showItemForm = ref(false)
const selectedItem = ref(null)
const showRemoveItemDialog = ref(false)
const itemToRemove = ref(null)
const removingItem = ref(false)
const showCancelDialog = ref(false)
const cancelling = ref(false)

const itemHeaders = [
  { title: 'Serviço', key: 'service', sortable: false, align: 'center' },
  { title: 'Quantidade', key: 'quantity', sortable: false, width: '120px', align: 'center' },
  { title: 'Valor Unitário', key: 'unit_value', sortable: false, width: '150px', align: 'center' },
  { title: 'Subtotal', key: 'subtotal', sortable: false, width: '150px', align: 'center' },
  { title: 'Ações', key: 'actions', sortable: false, width: '120px', align: 'center' },
]

function formatCurrency(value) {
  return parseFloat(value).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  const date = new Date(dateStr + 'T00:00:00')
  return date.toLocaleDateString('pt-BR')
}

function formatDateTime(dateStr) {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  return date.toLocaleString('pt-BR')
}

function getHistoryColor(action) {
  const colors = {
    created: 'success',
    updated: 'info',
    item_added: 'primary',
    item_updated: 'warning',
    item_removed: 'error',
    cancelled: 'error',
  }
  return colors[action] || 'grey'
}

function translateAction(action) {
  const translations = {
    created: 'Criado',
    updated: 'Atualizado',
    item_added: 'Item Adicionado',
    item_updated: 'Item Atualizado',
    item_removed: 'Item Removido',
    cancelled: 'Cancelado',
  }
  return translations[action] || action
}

async function loadContract() {
  loading.value = true
  try {
    const { data } = await contractService.get(contractId)
    contract.value = data.data
    await loadHistory()
  } catch (error) {
    toast.error('Erro ao carregar contrato.')
    router.push('/contracts')
  } finally {
    loading.value = false
  }
}

async function loadHistory() {
  try {
    const { data } = await contractService.getHistory(contractId)
    history.value = data.data
  } catch (error) {
    // Histórico é secundário, não bloquear a tela
  }
}

function openItemForm(item = null) {
  selectedItem.value = item ? { ...item } : null
  showItemForm.value = true
}

function onItemSaved() {
  showItemForm.value = false
  loadContract()
}

function confirmRemoveItem(item) {
  itemToRemove.value = item
  showRemoveItemDialog.value = true
}

async function removeItem() {
  removingItem.value = true
  try {
    await contractService.removeItem(contractId, itemToRemove.value.id)
    toast.success('Item removido com sucesso.')
    showRemoveItemDialog.value = false
    loadContract()
  } catch (error) {
    toast.error(error.response?.data?.message || 'Erro ao remover item.')
  } finally {
    removingItem.value = false
  }
}

async function cancelContract() {
  cancelling.value = true
  try {
    await contractService.cancel(contractId)
    toast.success('Contrato cancelado com sucesso.')
    showCancelDialog.value = false
    loadContract()
  } catch (error) {
    toast.error(error.response?.data?.message || 'Erro ao cancelar contrato.')
  } finally {
    cancelling.value = false
  }
}

onMounted(loadContract)
</script>
