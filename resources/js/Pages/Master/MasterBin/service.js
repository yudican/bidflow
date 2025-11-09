export const searchContact = (search, limit = 5) => {
  return axios
    .post(`/api/general/search-contact`, { search, limit })
    .then((res) => res.data.data)
}
