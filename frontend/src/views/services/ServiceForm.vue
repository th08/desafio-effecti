<template>
  <v-dialog :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)" max-width="500" persistent>
    <v-card>
      <v-card-title>
        {{ isEditing ? 'Editar Serviço' : 'Novo Serviço' }}
      </v-card-title>

      <v-card-text>
        <v-form ref="formRef" @submit.prevent="save">
          <v-text-field
            v-model="form.name"
            label="Nome do Serviço *"
            :rules="[rules.required]"
            prepend-inner-icon="mdi-cog"
            class="mb-2"
          />

          <v-text-field
            v-model="form.base_monthly_value"
            label="Valor Base Mensal (R$) *"
            :rules="[rules.required, rules.positiveNumber]"
            prepend-inner-icon="mdi-currency-brl"
            type="number"
            step="0.01"
            min="0.01"
          />
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
import { ref, computed, watch } from 'vue'
import { useToast } from 'vue-toastification'
import serviceService from '@/services/serviceService'

const props = defineProps({
  modelValue: Boolean,
  service: Object,
})

const emit = defineEmits(['update:modelValue', 'saved'])
const toast = useToast()

const formRef = ref(null)
const saving = ref(false)

const form = ref({
  name: '',
  base_monthly_value: '',
})

const isEditing = computed(() => !!props.service?.id)

const rules = {
  required: (v) => !!v || v === 0 || 'Campo obrigatório.',
  positiveNumber: (v) => (v && parseFloat(v) > 0) || 'Deve ser maior que zero.',
}

watch(
  () => props.modelValue,
  (open) => {
    if (open) {
      if (props.service) {
        form.value = {
          name: props.service.name || '',
          base_monthly_value: props.service.base_monthly_value || '',
        }
      } else {
        form.value = { name: '', base_monthly_value: '' }
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
      name: form.value.name,
      base_monthly_value: parseFloat(form.value.base_monthly_value),
    }

    if (isEditing.value) {
      await serviceService.update(props.service.id, payload)
      toast.success('Serviço atualizado com sucesso.')
    } else {
      await serviceService.create(payload)
      toast.success('Serviço criado com sucesso.')
    }

    emit('saved')
  } catch (error) {
    const data = error.response?.data
    if (data?.errors) {
      toast.error(Object.values(data.errors)[0])
    } else {
      toast.error(data?.message || 'Erro ao salvar serviço.')
    }
  } finally {
    saving.value = false
  }
}

function close() {
  emit('update:modelValue', false)
}
</script>
