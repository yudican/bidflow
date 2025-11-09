export const searchContact = (search) => {
    return axios
        .post(`/api/general/search-contact`, { search })
        .then((res) => res.data.data);
};
export const searchSales = (search) => {
    return axios
        .post(`/api/general/search-sales`, { search })
        .then((res) => res.data.data);
};
