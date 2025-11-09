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

export const searchUserApprovalPurchasing = (search, limit = 5) => {
  return axios
    .post(`/api/general/purchasing-user`, { search, limit })
    .then((res) => res.data.data)
}

export const searchProduct = (productId) => {
  return axios
    .get(`/api/master/product/${productId}`,)
    .then((res) => res.data.data)
}

export const searchProductCarton = (id) => {
  return axios
    .get(`/api/master/product-carton/${id}`,)
    .then((res) => res.data.data)
}