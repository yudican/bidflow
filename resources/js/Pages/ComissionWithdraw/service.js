export const searchContact = (search, limit = 5) => {
  return axios
    .post(`/api/general/search-contact`, { search, limit })
    .then((res) => res.data.data)
}

export const loadUserById = (id, callback) => {
  return axios
    .post(`/api/general/user`, { user_id: id })
    .then((res) => {
      callback(res.data.data)
    })
    .catch((err) => {
      callback(null)
    })
}
