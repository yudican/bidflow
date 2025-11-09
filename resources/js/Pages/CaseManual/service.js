export const searchUserCreated = (search) => {
  return axios
    .post(`/api/contact/service/search-user`, { search })
    .then((res) => res.data.data)
}

export const searchContact = (search, limit = 5) => {
  return axios
    .post(`/api/general/search-contact`, { search, limit })
    .then((res) => res.data.data)
}
