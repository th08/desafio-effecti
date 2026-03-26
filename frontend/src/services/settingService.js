import api from './api'

export default {
  list() {
    return api.get('/settings')
  },

  update(key, data) {
    return api.put(`/settings/${key}`, data)
  },
}
