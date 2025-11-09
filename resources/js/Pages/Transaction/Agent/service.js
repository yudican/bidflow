export const searchKecamatan = (search, limit = 5) => {
  return axios
    .post(`/api/master/address/search`, { search, limit })
    .then((res) => res.data.data)
}
