<template>
  <v-dialog :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)" max-width="500" persistent>
    <v-card>
      <v-card-title>
        {{ isEditing ? 'Editar Item' : 'Adicionar Item' }}
      </v-card-title>

      <v-card-text>
        <v-form ref="formRef" @submit.prevent="save">
          <v-autocomplete
            v-model="form.service_id"
            :items="services"
            item-title="name"
            item-value="id"
            label="Serviço *"
            :rules="[rules.required]"
            prepend-inner-icon="mdi-cog"
            :loading="loadingServices"
            :disabled="isEditing"
            class="mb-2"
            @update:model-value="onServiceSelected"
          >
            <template #item="{ item, props: itemProps }">
              <v-list-item v-bind="itemProps">
                <template #subtitle>
                  R$ {{ formatCurrency(item.raw.base_monthly_value) }} / mês
                </template>
              </v-list-item>
            </template>
          </v-autocomplete>

          <v-text-field
            v-model.number="form.quantity"
            label="Quantidade *"
            type="number"
            min="1"
            :rules="[rules.required, rules.minOne]"
            prepend-inner-icon="mdi-numeric"
            class="mb-2"
          />

          <v-text-field
            v-model.number="form.unit_value"
            label="Valor Unitário (R$) *"
            type="number"
            min="0"
            step="0.01"
            :rules="[rules.required, rules.minZero]"
            prepend-inner-icon="mdi-currency-brl"
          />

          <v-alert v-if="subtotal > 0" type="info" density="compact" variant="tonal" class="mt-2">
            Subtotal: R$ {{ formatCurrency(subtotal) }}
          </v-alert>
        </v-form>
      </v-card-text>

      <v-card-actions>
        <v-spacer />
        <v-btn variant="text" @click="close">Cancelar</v-btn>
        <v-btn color="primary" variant="flat" :loading="saving" @click="save">
          {{ isEditing ? 'Atualizar' : 'Adicionar' }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useToast } from 'vue-toastification'
import contractService from '@/services/contractService'
import serviceService from '@/services/serviceService'

const props = defineProps({
  modelValue: Boolean,
  contractId: [Number, String],
  item: Object,
})

const emit = defineEmits(['update:modelValue', 'saved'])
const toast = useToast()

const formRef = ref(null)
const saving = ref(false)
const services = ref([])
const loadingServices = ref(false)

const form = ref({
  service_id: null,
  quantity: 1,
  unit_value: 0,
})

const isEditing = computed(() => !!props.item?.id)

const subtotal = computed(() => {
  return (form.value.quantity || 0) * (form.value.unit_value || 0)
})

const rules = {
  required: (v) => (v !== null && v !== undefined && v !== '') || 'Campo obrigatório.',
  minOne: (v) => (v && v >= 1) || 'Mínimo 1.',
  minZero: (v) => (v !== null && v !== undefined && v >= 0) || 'Deve ser um valor positivo.',
}

function formatCurrency(value) {
  return parseFloat(value).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function onServiceSelected(serviceId) {
  const service = services.value.find((s) => s.id === serviceId)
  if (service && !isEditing.value) {
    form.value.unit_value = parseFloat(service.base_monthly_value)
  }
}

async function loadServices() {
  loadingServices.value = true
  try {
    const { data } = await serviceService.list({ per_page: 100 })
    services.value = data.data
  } catch (error) {
    toast.error('Erro ao carregar serviços.')
  } finally {
    loadingServices.value = false
  }
}

watch(
  () => props.modelValue,
  (open) => {
    if (open) {
      loadServices()
      if (props.item) {
        form.value = {
          service_id: props.item.service_id,
          quantity: props.item.quantity,
          unit_value: parseFloat(props.item.unit_value),
        }
      } else {
        form.value = { service_id: null, quantity: 1, unit_value: 0 }
      }
    }
  }
)

async function save() {
  const { valid } = await formRef.value.validate()
  if (!valid) return

  saving.value = true
  try {
    const payload = {
      service_id: form.value.service_id,
      quantity: form.value.quantity,
      unit_value: form.value.unit_value,
    }

    if (isEditing.value) {
      await contractService.updateItem(props.contractId, props.item.id, payload)
      toast.success('Item atualizado com sucesso.')
    } else {
      await contractService.addItem(props.contractId, payload)
      toast.success('Item adicionado com sucesso.')
    }

    emit('saved')
  } catch (error) {
    const data = error.response?.data
    if (data?.errors) {
      toast.error(Object.values(data.errors)[0])
    } else {
      toast.error(data?.message || 'Erro ao salvar item.')
    }
  } finally {
    saving.value = false
  }
}

function close() {
  emit('update:modelValue', false)
}
</script>
