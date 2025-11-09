import { createApi, fetchBaseQuery } from "@reduxjs/toolkit/query/react"
const currentUrl = new URL(window.location.href)
export const generalService = createApi({
  reducerPath: "generalService",
  baseQuery: fetchBaseQuery({ baseUrl: currentUrl?.origin }),
  endpoints: (builder) => ({
    // POST METHOD
    tagTypes: [
      "getUserLogin",
      "getTax",
      "getUserWarehouse",
      "getAddressUser",
      "getBrand",
      "getWarehouse",
      "getMasterBin",
      "getTop",
      "getRole",
      "getSidebarMenu",
      "getMenuList",
      "getNotifications",
    ],
    getSidebarMenu: builder.query({
      query: (url) => "/api/general/load-user-menu",
      transformResponse: (response) => {
        const datas = response?.data || []

        return datas // Assuming the actual data is in response.data
      },
      providesTags: ["getSidebarMenu"],
    }),
    // menu
    getMenuList: builder.query({
      query: (url) => "/api/menu/list",
      transformResponse: (response) => {
        const datas = response?.data || []

        return datas // Assuming the actual data is in response.data
      },
      providesTags: ["getMenuList"],
    }),
    createMenu: builder.mutation({
      query: (body) => ({
        url: `/api/menu/save`,
        method: "POST",
        body,
      }),
      invalidatesTags: ["getMenuList", "getSidebarMenu"],
    }),
    updateMenu: builder.mutation({
      query: (body) => ({
        url: `/api/menu/update/${body.id}`,
        method: "POST",
        body,
      }),
      invalidatesTags: ["getMenuList", "getSidebarMenu"],
    }),
    updateRoleMenu: builder.mutation({
      query: ({ body, menu_id }) => ({
        url: `/api/menu/role/update/${menu_id}`,
        method: "POST",
        body,
      }),
      invalidatesTags: ["getMenuList", "getSidebarMenu"],
    }),
    copyMenu: builder.mutation({
      query: (body) => ({
        url: `/api/menu/copy/${body.id}`,
        method: "POST",
        body,
      }),
      invalidatesTags: ["getMenuList", "getSidebarMenu"],
    }),
    deleteMenu: builder.mutation({
      query: ({ body, menu_id }) => ({
        url: `/api/menu/delete/${menu_id}`,
        method: "POST",
        body,
      }),
      invalidatesTags: ["getMenuList", "getSidebarMenu"],
    }),
    // end menu
    getUserLogin: builder.query({
      query: (url) => "/api/general/load-user",
      providesTags: ["getUserLogin"],
      transformResponse: (response) => {
        const datas = response?.data || []
        localStorage.setItem("account_id", datas.company_id)
        localStorage.setItem("user_data", JSON.stringify(datas))
        localStorage.setItem("role", datas?.role?.role_type)
        localStorage.setItem("service_ginee_url", datas?.service_ginee_url)
        return datas // Assuming the actual data is in response.data
      },
    }),
    getNotifications: builder.query({
      query: (url = "/api/general/notifications") => url,
      transformResponse: (response) => {
        return {
          ...response?.data,
        } // Assuming the actual data is in response.data
      },
      providesTags: ["getNotifications"],
    }),
    readNotification: builder.mutation({
      query: (notification_id) => ({
        url: `/api/general/notification/read`,
        method: "POST",
        body: {
          notification_id,
        },
      }),
      invalidatesTags: ["getNotifications"],
    }),
    readAllNotification: builder.mutation({
      query: () => ({
        url: `/api/general/notification/read`,
        method: "POST",
        body: {
          read_all: true,
        },
      }),
      invalidatesTags: ["getNotifications"],
    }),
    getTax: builder.query({
      query: (url) => "/api/master/taxs",
      transformResponse: (response) => {
        const datas = response?.data || []

        return datas // Assuming the actual data is in response.data
      },
      providesTags: ["getTax"],
    }),
    getRole: builder.query({
      query: (url) => "/api/master/role",
      transformResponse: (response) => {
        const datas = response?.data || []

        return datas // Assuming the actual data is in response.data
      },
      providesTags: ["getRole"],
    }),
    getBrand: builder.query({
      query: (url) => "/api/master/brand",
      transformResponse: (response) => {
        const datas = response?.data || []

        return datas // Assuming the actual data is in response.data
      },
      providesTags: ["getBrand"],
    }),
    getWarehouse: builder.query({
      query: (url) => "/api/master/warehouse",
      transformResponse: (response) => {
        const datas = response?.data || []

        return datas // Assuming the actual data is in response.data
      },
      providesTags: ["getWarehouse"],
    }),
    getMasterBin: builder.query({
      query: (url) => "/api/master/bin",
      transformResponse: (response) => {
        const datas = response?.data || []

        return datas // Assuming the actual data is in response.data
      },
      providesTags: ["getMasterBin"],
    }),
    getTop: builder.query({
      query: (url) => "/api/master/top",
      transformResponse: (response) => {
        const datas = response?.data || []

        return datas // Assuming the actual data is in response.data
      },
      providesTags: ["getTop"],
    }),
    getProductVariantByTag: builder.mutation({
      query: (tag = "sales-offline") => ({
        url: `/api/master/products/${tag}`,
        method: "GET",
      }),
      transformResponse: (response) => {
        const datas = response?.data || []

        return datas // Assuming the actual data is in response.data
      },
    }),
    getUserWarehouse: builder.query({
      query: (url) => "/api/general/warehouse-user",
      transformResponse: (response) => {
        const datas = response?.data || []

        return datas // Assuming the actual data is in response.data
      },
      providesTags: ["getUserWarehouse"],
    }),
    getAddressUser: builder.query({
      query: (user_id) => "/api/general/user-with-address/" + user_id,
      transformResponse: (response) => {
        const datas = response?.data || []

        return datas // Assuming the actual data is in response.data
      },
      providesTags: ["getAddressUser"],
    }),
    switchAccount: builder.mutation({
      query: (body) => ({
        url: `/api/general/swith-account`,
        method: "POST",
        body,
      }),
      invalidatesTags: ["getUserLogin"],
    }),
    logout: builder.mutation({
      query: (body) => ({
        url: `/logout`,
        method: "POST",
        body,
      }),
    }),
  }),
})

export const {
  useGetUserLoginQuery,
  useGetSidebarMenuQuery,
  // menu
  useGetMenuListQuery,
  useCreateMenuMutation,
  useUpdateMenuMutation,
  useUpdateRoleMenuMutation,
  useCopyMenuMutation,
  useDeleteMenuMutation,
  // menu
  useGetNotificationsQuery,
  useReadNotificationMutation,
  useReadAllNotificationMutation,
  useGetTaxQuery,
  useGetRoleQuery,
  useGetUserWarehouseQuery,
  useGetAddressUserQuery,
  useGetBrandQuery,
  useGetWarehouseQuery,
  useGetMasterBinQuery,
  useGetTopQuery,
  useGetProductVariantByTagMutation,
  useSwitchAccountMutation,
  useLogoutMutation,
} = generalService
