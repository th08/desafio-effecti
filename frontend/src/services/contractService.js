import api from './api'

export default {
  list(params = {}) {
    return api.get('/contracts', { params })
  },

  get(id) {
    return api.get(`/contracts/${id}`)
  },

  create(data) {
    return api.post('/contracts', data)
  },

  update(id, data) {
    return api.put(`/contracts/${id}`, data)
  },

  delete(id) {
    return api.delete(`/contracts/${id}`)
  },

  addItem(contractId, data) {
    return api.post(`/contracts/${contractId}/items`, data)
  },

  updateItem(contractId, itemId, data) {
    return api.put(`/contracts/${contractId}/items/${itemId}`, data)
  },

  removeItem(contractId, itemId) {
    return api.delete(`/contracts/${contractId}/items/${itemId}`)
  },

  cancel(contractId) {
    return api.patch(`/contracts/${contractId}/cancel`)
  },

  getHistory(contractId) {
    return api.get(`/contracts/${contractId}/history`)
  },
}
