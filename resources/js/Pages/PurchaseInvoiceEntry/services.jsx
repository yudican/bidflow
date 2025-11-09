export const searchContact = (search, limit = 5) => {
  return axios
    .post(`/api/general/search-contact-warehouse`, { search, limit })
    .then((res) => res.data.data)
}
export const searchUserApproval = (search, limit = 5) => {
  return axios
    .post(`/api/general/approval-user`, { search, limit })
    .then((res) => res.data.data)
}
export const searchCompany = (search, limit = 5) => {
  return axios
    .post(`/api/general/search-company`, { search, limit })
    .then((res) => res.data.data)
}
