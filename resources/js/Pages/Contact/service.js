export const searchUserCreated = (search) => {
  return axios
    .post(`/api/contact/service/search-user`, { search })
    .then((res) => res.data.data)
}

export const searchContactMember = (search, user_id) => {
  return axios
    .post(`/api/contact/downline/member/list/${user_id}`, { search })
    .then((res) => res.data.data)
}
