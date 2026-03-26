<template>
  <v-dialog :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)" max-width="600" persistent>
    <v-card>
      <v-card-title>
        {{ isEditing ? 'Editar Cliente' : 'Novo Cliente' }}
      </v-card-title>

      <v-card-text>
        <v-form ref="formRef" @submit.prevent="save">
          <v-text-field
            v-model="form.name"
            label="Nome *"
            :rules="[rules.required]"
            prepend-inner-icon="mdi-account"
            class="mb-2"
          />

          <v-text-field
            v-model="form.document"
            label="CPF/CNPJ *"
            :rules="[rules.required, rules.document]"
            prepend-inner-icon="mdi-card-account-details"
            :placeholder="form.document && form.document.replace(/\D/g, '').length > 11 ? '00.000.000/0000-00' : '000.000.000-00'"
            hint="Digite apenas números — o sistema formata automaticamente"
            class="mb-2"
            @update:model-value="onDocumentInput"
          />

          <v-text-field
            v-model="form.email"
            label="Email *"
            :rules="[rules.required, rules.email]"
            prepend-inner-icon="mdi-email"
            type="email"
            class="mb-2"
          />

          <v-switch
            v-model="statusActive"
            :label="statusActive ? 'Ativo' : 'Inativo'"
            color="success"
            hide-details
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
import clientService from '@/services/clientService'

const props = defineProps({
  modelValue: Boolean,
  client: Object,
})

const emit = defineEmits(['update:modelValue', 'saved'])
const toast = useToast()

const formRef = ref(null)
const saving = ref(false)
const statusActive = ref(true)

const form = ref({
  name: '',
  document: '',
  email: '',
})

const isEditing = computed(() => !!props.client?.id)

const rules = {
  required: (v) => !!v || 'Campo obrigatório.',
  email: (v) => !v || /.+@.+\..+/.test(v) || 'Email inválido.',
  document: (v) => {
    if (!v) return true
    const clean = v.replace(/\D/g, '')
    if (clean.length !== 11 && clean.length !== 14) {
      return 'CPF deve ter 11 dígitos ou CNPJ 14 dígitos.'
    }
    return true
  },
}

// Formata CPF/CNPJ enquanto digita
function onDocumentInput(value) {
  if (!value) return
  const clean = value.replace(/\D/g, '')

  if (clean.length <= 11) {
    // Formato CPF: 000.000.000-00
    form.value.document = clean
      .replace(/(\d{3})(\d)/, '$1.$2')
      .replace(/(\d{3})(\d)/, '$1.$2')
      .replace(/(\d{3})(\d{1,2})$/, '$1-$2')
  } else {
    // Formato CNPJ: 00.000.000/0000-00
    form.value.document = clean
      .substring(0, 14)
      .replace(/(\d{2})(\d)/, '$1.$2')
      .replace(/(\d{3})(\d)/, '$1.$2')
      .replace(/(\d{3})(\d)/, '$1/$2')
      .replace(/(\d{4})(\d{1,2})$/, '$1-$2')
  }
}

watch(
  () => props.modelValue,
  (open) => {
    if (open) {
      if (props.client) {
        form.value = {
          name: props.client.name || '',
          document: props.client.document || '',
          email: props.client.email || '',
        }
        statusActive.value = props.client.status === 'A'
        // Formata o documento existente
        if (form.value.document) {
          onDocumentInput(form.value.document)
        }
      } else {
        form.value = { name: '', document: '', email: '' }
        statusActive.value = true
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
      ...form.value,
      document: form.value.document.replace(/\D/g, ''),
      status: statusActive.value ? 'A' : 'I',
    }

    if (isEditing.value) {
      await clientService.update(props.client.id, payload)
      toast.success('Cliente atualizado com sucesso.')
    } else {
      await clientService.create(payload)
      toast.success('Cliente criado com sucesso.')
    }

    emit('saved')
  } catch (error) {
    const data = error.response?.data
    if (data?.errors) {
      const firstError = Object.values(data.errors)[0]
      toast.error(firstError)
    } else {
      toast.error(data?.message || 'Erro ao salvar cliente.')
    }
  } finally {
    saving.value = false
  }
}

function close() {
  emit('update:modelValue', false)
}
</script>
