<template>
  <v-navigation-drawer v-model="drawer" app>
    <v-list-item
      prepend-icon="mdi-file-document-outline"
      title="ERP Contratos"
      subtitle="Gestão de Contratos"
    />
    <v-divider />
    <v-list density="compact" nav>
      <v-list-item
        v-for="item in menuItems"
        :key="item.title"
        :to="item.to"
        :prepend-icon="item.icon"
        :title="item.title"
        color="primary"
      />
    </v-list>
  </v-navigation-drawer>

  <v-app-bar app color="primary" density="comfortable">
    <v-app-bar-nav-icon @click="drawer = !drawer" />
    <v-spacer />
    <v-toolbar-title class="text-center">Desafio Effecti ERP - Contratos</v-toolbar-title>
    <v-spacer />
    <v-app-bar-nav-icon style="visibility: hidden" tabindex="-1" aria-hidden="true" />
  </v-app-bar>

  <v-main>
    <v-container fluid>
      <slot />
    </v-container>
  </v-main>
</template>

<script setup>
import { ref } from "vue";

const drawer = ref(true);

const menuItems = [
  { title: "Clientes", icon: "mdi-account-group", to: "/clients" },
  { title: "Serviços", icon: "mdi-cog-outline", to: "/services" },
  { title: "Contratos", icon: "mdi-file-sign", to: "/contracts" },
  { title: "Configurações", icon: "mdi-tune", to: "/settings" },
];
</script>
