<template>
  <v-dialog :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)" max-width="600" persistent>
    <v-card>
      <v-card-title>
        {{ isEditing ? 'Editar Contrato' : 'Novo Contrato' }}
      </v-card-title>

      <v-card-text>
        <v-form ref="formRef" @submit.prevent="save">
          <v-autocomplete
            v-model="form.client_id"
            :items="clients"
            item-title="name"
            item-value="id"
            label="Cliente *"
            :rules="[rules.required]"
            prepend-inner-icon="mdi-account"
            :loading="loadingClients"
            :disabled="isEditing"
            class="mb-2"
          >
            <template #item="{ item, props: itemProps }">
              <v-list-item v-bind="itemProps">
                <template #subtitle>
                  {{ formatDocument(item.raw.document) }} — {{ item.raw.email }}
                </template>
              </v-list-item>
            </template>
          </v-autocomplete>

          <v-row>
            <v-col cols="6">
              <v-text-field
                v-model="form.start_date"
                label="Data de Início *"
                :rules="[rules.required]"
                type="date"
                prepend-inner-icon="mdi-calendar"
              />
            </v-col>
            <v-col cols="6">
              <v-text-field
                v-model="form.end_date"
                label="Data de Término"
                type="date"
                prepend-inner-icon="mdi-calendar"
                clearable
                hint="Deixe vazio para contrato por tempo indeterminado"
              />
            </v-col>
          </v-row>
        </v-form>
      </v-card-text>

      <v-card-actions>
        <v-spacer />
        <v-btn variant="text" @click="close">Cancelar</v-btn>
        <v-btn color="primary" variant="flat" :loading="saving" @click="save">
          {{ isEditing ? 'Atualizar' : 'Salvar' }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import contractService from '@/services/contractService'
import clientService from '@/services/clientService'

const props = defineProps({
  modelValue: Boolean,
  contract: Object,
})

const emit = defineEmits(['update:modelValue', 'saved'])
const toast = useToast()

const formRef = ref(null)
const saving = ref(false)
const clients = ref([])
const loadingClients = ref(false)

const form = ref({
  client_id: null,
  start_date: '',
  end_date: '',
})

const isEditing = computed(() => !!props.contract?.id)

const rules = {
  required: (v) => !!v || 'Campo obrigatório.',
}

function formatDocument(doc) {
  if (!doc) return ''
  if (doc.length === 11) return doc.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4')
  if (doc.length === 14) return doc.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5')
  return doc
}

async function loadClients() {
  loadingClients.value = true
  try {
    const { data } = await clientService.list({ per_page: 100, status: 'A' })
    clients.value = data.data
  } catch (error) {
    toast.error('Erro ao carregar clientes.')
  } finally {
    loadingClients.value = false
  }
}

watch(
  () => props.modelValue,
  (open) => {
    if (open) {
      loadClients()
      if (props.contract) {
        form.value = {
          client_id: props.contract.client_id,
          start_date: props.contract.start_date?.substring(0, 10) || '',
          end_date: props.contract.end_date?.substring(0, 10) || '',
        }
      } else {
        form.value = { client_id: null, start_date: '', end_date: '' }
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
      client_id: form.value.client_id,
      start_date: form.value.start_date,
      end_date: form.value.end_date || null,
    }

    if (isEditing.value) {
      await contractService.update(props.contract.id, payload)
      toast.success('Contrato atualizado com sucesso.')
    } else {
      await contractService.create(payload)
      toast.success('Contrato criado com sucesso.')
    }

    emit('saved')
  } catch (error) {
    const data = error.response?.data
    if (data?.errors) {
      toast.error(Object.values(data.errors)[0])
    } else {
      toast.error(data?.message || 'Erro ao salvar contrato.')
    }
  } finally {
    saving.value = false
  }
}

function close() {
  emit('update:modelValue', false)
}
</script>
