import 'vuetify/styles'
import '@mdi/font/css/materialdesignicons.css'
import { createVuetify } from 'vuetify'
import { pt } from 'vuetify/locale'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

const vuetify = createVuetify({
  components,
  directives,
  locale: {
    locale: 'pt',
    fallback: 'pt',
    messages: {
      pt: {
        ...pt,
        noDataText: 'Nenhum registro encontrado.',
        loadingText: 'Carregando...',
        dataIterator: {
          ...pt.dataIterator,
          noResultsText: 'Nenhum registro encontrado.',
          loadingText: 'Carregando itens...',
        },
        dataTable: {
          ...pt.dataTable,
          itemsPerPageText: 'Itens por página:',
          ariaLabel: {
            ...pt.dataTable?.ariaLabel,
            sortDescending: 'Ordenado decrescente.',
            sortAscending: 'Ordenado crescente.',
            sortNone: 'Sem ordenação.',
            activateNone: 'Ativar para remover a ordenação.',
            activateDescending: 'Ativar para ordenar decrescentemente.',
            activateAscending: 'Ativar para ordenar crescentemente.',
          },
        },
        dataFooter: {
          ...pt.dataFooter,
          itemsPerPageText: 'Itens por página:',
          itemsPerPageAll: 'Todos',
          pageText: '{0}-{1} de {2}',
          firstPage: 'Primeira página',
          prevPage: 'Página anterior',
          nextPage: 'Próxima página',
          lastPage: 'Última página',
        },
      },
    },
  },
  theme: {
    defaultTheme: 'light',
    themes: {
      light: {
        colors: {
          primary: '#1976D2',
          secondary: '#424242',
          accent: '#82B1FF',
          error: '#FF5252',
          info: '#2196F3',
          success: '#4CAF50',
          warning: '#FB8C00',
        },
      },
    },
  },
  icons: {
    defaultSet: 'mdi',
  },
})

export default vuetify
