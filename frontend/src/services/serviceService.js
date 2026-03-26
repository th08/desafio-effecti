import api from './api'

export default {
  list(params = {}) {
    return api.get('/services', { params })
  },

  get(id) {
    return api.get(`/services/${id}`)
  },

  create(data) {
    return api.post('/services', data)
  },

  update(id, data) {
    return api.put(`/services/${id}`, data)
  },

  delete(id) {
    return api.delete(`/services/${id}`)
  },
}
