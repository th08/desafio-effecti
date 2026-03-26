<template>
  <div>
    <div class="d-flex align-center mb-4">
      <h1 class="text-h5">Configurações</h1>
      <v-spacer />
      <v-btn color="secondary" variant="outlined" prepend-icon="mdi-refresh" :loading="loading" @click="loadSettings">
        Atualizar
      </v-btn>
    </div>

    <v-progress-linear v-if="loading" indeterminate color="primary" />

    <v-row v-if="loading" class="mt-2">
      <v-col cols="12">
        <v-skeleton-loader type="article" />
      </v-col>
      <v-col cols="12">
        <v-skeleton-loader type="article, table-row-divider@4" />
      </v-col>
    </v-row>

    <template v-if="!loading">
      <!-- Regra de Desconto Ativa/Inativa -->
      <v-card variant="outlined" class="mb-4">
        <v-card-title class="text-subtitle-1">Desconto Progressivo</v-card-title>
        <v-card-text>
          <v-switch
            v-model="discountEnabled"
            :label="discountEnabled ? 'Ativado' : 'Desativado'"
            color="primary"
            hide-details
            :loading="savingEnabled"
            @update:model-value="saveDiscountEnabled"
          />
          <p class="text-caption text-medium-emphasis mt-2">
            Quando ativado, contratos com quantidade total de itens acima dos limites configurados
            recebem desconto progressivo automaticamente.
          </p>
        </v-card-text>
      </v-card>

      <!-- Faixas de Desconto -->
      <v-card variant="outlined">
        <v-card-title class="d-flex align-center text-subtitle-1">
          Faixas de Desconto
          <v-spacer />
          <v-btn
            color="primary"
            size="small"
            prepend-icon="mdi-plus"
            @click="addTier"
            :disabled="!discountEnabled"
          >
            Adicionar Faixa
          </v-btn>
        </v-card-title>

        <v-data-table
          :headers="tierHeaders"
          :items="discountTiers"
          :loading="loading"
          density="comfortable"
          :items-per-page="-1"
          hide-default-footer
        >
          <template #loading>
            <div class="d-flex flex-column align-center justify-center py-8">
              <v-progress-circular indeterminate color="primary" size="34" />
              <span class="text-body-2 text-medium-emphasis mt-3">Carregando configurações...</span>
            </div>
          </template>

          <template #item.min_quantity="{ item }">
            <div class="d-flex justify-center">
              <v-text-field
                v-model.number="item.min_quantity"
                type="number"
                min="1"
                density="compact"
                hide-details
                variant="outlined"
                :disabled="!discountEnabled"
                style="max-width: 120px"
              />
            </div>
          </template>

          <template #item.discount_percent="{ item }">
            <div class="d-flex justify-center">
              <v-text-field
                v-model.number="item.discount_percent"
                type="number"
                min="0"
                max="100"
                step="0.5"
                suffix="%"
                density="compact"
                hide-details
                variant="outlined"
                :disabled="!discountEnabled"
                style="max-width: 140px"
              />
            </div>
          </template>

          <template #item.actions="{ index }">
            <div class="d-flex justify-center">
              <v-btn
                icon="mdi-delete"
                size="small"
                variant="text"
                color="error"
                :disabled="!discountEnabled"
                @click="removeTier(index)"
              />
            </div>
          </template>

          <template #no-data>
            <div class="text-center py-4 text-medium-emphasis">
              Nenhuma faixa configurada.
            </div>
          </template>
        </v-data-table>

        <v-card-actions v-if="discountTiers.length > 0">
          <v-spacer />
          <v-btn
            color="primary"
            variant="flat"
            :loading="savingTiers"
            :disabled="!discountEnabled"
            @click="saveTiers"
          >
            Salvar Faixas
          </v-btn>
        </v-card-actions>
      </v-card>

      <!-- Prévia -->
      <v-card v-if="discountEnabled && discountTiers.length > 0" variant="outlined" class="mt-4">
        <v-card-title class="text-subtitle-1">Prévia das Regras</v-card-title>
        <v-card-text>
          <v-table density="compact">
            <thead>
              <tr>
                <th class="text-center">Quantidade Mínima de Itens</th>
                <th class="text-center">Desconto Aplicado</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(tier, i) in sortedTiers" :key="i">
                <td class="text-center">≥ {{ tier.min_quantity }} itens</td>
                <td class="text-center">
                  <v-chip color="success" size="small" label>{{ tier.discount_percent }}%</v-chip>
                </td>
              </tr>
            </tbody>
          </v-table>
        </v-card-text>
      </v-card>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import settingService from '@/services/settingService'

const toast = useToast()

const loading = ref(true)
const discountEnabled = ref(false)
const discountTiers = ref([])
const savingEnabled = ref(false)
const savingTiers = ref(false)

const tierHeaders = [
  { title: 'Quantidade Mínima', key: 'min_quantity', sortable: false, align: 'center' },
  { title: 'Percentual de Desconto', key: 'discount_percent', sortable: false, align: 'center' },
  { title: 'Ações', key: 'actions', sortable: false, width: '80px', align: 'center' },
]

const sortedTiers = computed(() => {
  return [...discountTiers.value].sort((a, b) => a.min_quantity - b.min_quantity)
})

async function loadSettings() {
  loading.value = true
  try {
    const { data } = await settingService.list()
    const settings = data.data

    // Busca configuração de desconto habilitado
    const enabledSetting = settings.find((s) => s.key === 'discount_enabled')
    if (enabledSetting) {
      discountEnabled.value = enabledSetting.typed_value === true || enabledSetting.value === 'true'
    }

    // Busca faixas de desconto
    const rulesSetting = settings.find((s) => s.key === 'discount_rules')
    if (rulesSetting) {
      const parsed = typeof rulesSetting.typed_value === 'string'
        ? JSON.parse(rulesSetting.typed_value)
        : rulesSetting.typed_value
      // Suporta formato { tiers: [...] } ou array direto
      const tiers = Array.isArray(parsed) ? parsed : (parsed?.tiers || [])
      discountTiers.value = tiers
    }
  } catch (error) {
    toast.error('Erro ao carregar configurações.')
  } finally {
    loading.value = false
  }
}

async function saveDiscountEnabled(value) {
  savingEnabled.value = true
  try {
    await settingService.update('discount_enabled', { value: String(value) })
    toast.success(`Desconto progressivo ${value ? 'ativado' : 'desativado'}.`)
  } catch (error) {
    toast.error('Erro ao salvar configuração.')
    discountEnabled.value = !value
  } finally {
    savingEnabled.value = false
  }
}

function addTier() {
  discountTiers.value.push({ min_quantity: 1, discount_percent: 5 })
}

function removeTier(index) {
  discountTiers.value.splice(index, 1)
}

async function saveTiers() {
  // Validação básica
  for (const tier of discountTiers.value) {
    if (!tier.min_quantity || tier.min_quantity < 1) {
      toast.error('Quantidade mínima deve ser pelo menos 1.')
      return
    }
    if (tier.discount_percent === undefined || tier.discount_percent < 0 || tier.discount_percent > 100) {
      toast.error('Percentual deve estar entre 0 e 100.')
      return
    }
  }

  savingTiers.value = true
  try {
    const sorted = [...discountTiers.value].sort((a, b) => a.min_quantity - b.min_quantity)
    await settingService.update('discount_rules', { value: JSON.stringify({ tiers: sorted }) })
    discountTiers.value = sorted
    toast.success('Faixas de desconto atualizadas.')
  } catch (error) {
    toast.error('Erro ao salvar faixas de desconto.')
  } finally {
    savingTiers.value = false
  }
}

onMounted(loadSettings)
</script>
