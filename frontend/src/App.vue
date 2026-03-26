<template>
  <v-app>
    <v-progress-linear
      v-if="routeLoading"
      indeterminate
      color="primary"
      height="3"
      location="top"
      absolute
    />
    <DefaultLayout>
      <router-view />
    </DefaultLayout>
  </v-app>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import DefaultLayout from './layouts/DefaultLayout.vue'

const router = useRouter()
const routeLoading = ref(false)

let removeBeforeHook = null
let removeAfterHook = null

onMounted(() => {
  removeBeforeHook = router.beforeEach((to, from, next) => {
    routeLoading.value = true
    next()
  })

  removeAfterHook = router.afterEach(() => {
    routeLoading.value = false
  })
})

onBeforeUnmount(() => {
  if (removeBeforeHook) removeBeforeHook()
  if (removeAfterHook) removeAfterHook()
})
</script>
