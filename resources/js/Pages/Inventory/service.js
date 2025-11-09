import axios from "axios";

export const searchUserCreated = async (search) => {
    return await axios
        .post(`/api/contact/service/search-user`, { search })
        .then((res) => res.data.data);
};

export const searchContact = async (search, limit = 5) => {
    return await axios
        .post(`/api/general/search-contact`, { search, limit })
        .then((res) => res.data.data);
};

export const searchSales = async (search, limit = 5) => {
    return await axios
        .post(`/api/general/search-sales`, { search, limit })
        .then((res) => res.data.data);
};

export const handleSearchContact = async (e) => {
    return searchContact(e).then((results) => {
        const newResult = results.map((result) => {
            return { label: result.nama, value: result.id };
        });

        return newResult;
    });
};

export const handleSearchSales = async (e) => {
    return searchSales(e).then((results) => {
        const newResult = results.map((result) => {
            return { label: result.nama, value: result.id };
        });

        return newResult;
    });
};
