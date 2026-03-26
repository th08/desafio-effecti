import axios from 'axios'

const api = axios.create({
  baseURL: 'http://localhost:8080/api',
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
  timeout: 15000,
})

// Interceptor para tratar erros de forma centralizada
api.interceptors.response.use(
  (response) => response,
  (error) => {
    // Retorna o erro para tratamento no componente
    return Promise.reject(error)
  }
)

export default api
