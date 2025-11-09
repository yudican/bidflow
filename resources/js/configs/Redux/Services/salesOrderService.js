import { createApi, fetchBaseQuery } from "@reduxjs/toolkit/query/react"
import { formatNumber, getItem, getStatusLeadOrder } from "../../../helpers"
import moment from "moment"
const currentUrl = new URL(window.location.href)
export const salesOrderService = createApi({
  reducerPath: "salesOrderService",
  baseQuery: fetchBaseQuery({ baseUrl: currentUrl?.origin }),
  endpoints: (builder) => ({
    // POST METHOD
    tagTypes: [
      "getSalesOrder",
      "getDetailSalesOrder",
      "getDetailSalesOrderItems",
      "useGetSalesOrderDeliveryItemsDetailQuery",
      "getSalesOrderBillingItemsDetail",
      "getDetailSalesOrderForm",
      "getSalesChannel",
      "getSalesOrderInvoices",
    ],
    getDetailSalesOrderForm: builder.query({
      query: (url) => url,
      transformResponse: (response) => {
        const data = response?.data || null

        const role = getItem("role")
        const userData = getItem("user_data", true)

        const product_needs = data.product_needs || []

        // nillings
        const dataBillings = data?.billings?.map((item) => {
          return {
            id: item.id,
            account_name: item.account_name,
            account_bank: item.account_bank,
            total_transfer: formatNumber(item.total_transfer),
            transfer_date: item.transfer_date,
            upload_billing_photo: item.upload_billing_photo_url,
            upload_transfer_photo: item.upload_transfer_photo_url,
            status: item.status,
            notes: item.notes ?? "-",
            approved_by_name: item.approved_by_name,
            approved_at: item.approved_at || "-",
            payment_number: item.payment_number || "-",
          }
        })

        // product needs
        const productNeeds = product_needs.map((item, index) => {
          const stock_off_market = item?.product?.stock_warehouse?.find(
            (item) => item.id == data.warehouse_id
          )
          let newData = {
            id: item.id,
            key: index,
            product_id: item.product_id,
            price: item.product?.price?.final_price,
            price_satuan: item?.price_nego / item?.qty,
            qty: item.qty,
            tax_id: item.tax_id || 1,
            tax_amount: item.tax_amount,
            tax_percentage: item.tax_percentage,
            discount_percentage: item.discount_percentage,
            discount: item.discount,
            discount_amount: item.discount_amount,
            subtotal: item.final_price,
            price_nego: item.price_nego,
            total: item.total,
            margin_price: 0,
            stock: 0,
            disabled: data?.status > 1 ? true : false,
            stock: stock_off_market?.stock || 0,
          }

          return newData
        })

        // form
        const forms = {
          ...data,
          contact: {
            label: data?.contact_name,
            value: data?.contact,
          },
          sales: {
            label: data?.sales_name,
            value: data?.sales,
          },
          payment_terms: data?.payment_term?.id,
          expired_at: moment(data?.expired_at ?? new Date(), "YYYY-MM-DD"),
          created_by: data?.created_by_name,
        }

        if (role === "sales") {
          forms.sales = {
            label: userData.name,
            value: userData.id,
          }
        }

        if (data) {
          return {
            ...data,
            billings: dataBillings,
            product_needs: productNeeds,
            forms,
          }
        } else {
          return null
        }
      },
      providesTags: ["getDetailSalesOrderForm"],
    }),

    // sales order
    getSalesOrderInvoices: builder.query({
      query: (url) => url,
      providesTags: ["getSalesOrderInvoices"],
      transformResponse: (response) => {
        const datas = response?.data?.data || []
        const newData = datas.map((item, index) => {
          const number = response?.data?.from + index
          return {
            number,
            ...item,
            // uid_delivery: item?.id,
            id: item?.uid_lead,
            amount_invoiced: item?.total,
          }
        })

        return {
          data: {
            ...response.data,
            data: newData,
          },
        }
      },
    }),
    getSalesOrderDetail: builder.query({
      query: (url) => url,
      providesTags: ["getDetailSalesOrder"],
      transformResponse: (response) => {
        const data = response?.data || {}
        return {
          ...data,
          printUrl: response?.print,
        } // Assuming the actual data is in response.data
      },
    }),
    getSalesOrderItemsDetail: builder.query({
      query: (url) => url,
      providesTags: ["getDetailSalesOrderItems"],
      transformResponse: (response) => {
        const data = response?.data || {}

        return data.map((item) => {
          // const amount_discount =
          //   item?.discount > 0 ? item?.discount * item?.qty : 0
          // const percent =
          //   item.price > 0 ? (amount_discount / item.price) * 100 : 0
          // const discount_percentage = item?.discount_id
          //   ? item?.discount_percentage
          //   : percent
          // const getPercent =
          //   item?.discount_id && item?.discount_percentage > 0
          //     ? item?.discount_percentage / 100
          //     : 0
          // const discount_amount = item?.discount_id
          //   ? getPercent * item.price
          //   : amount_discount

          // const tax = item.tax_percentage > 0 ? item.tax_percentage / 100 : 0
          // const subtotal = item.price * item.qty

          return {
            ...item,
            // discount_percentage,
            // discount_amount,
            // tax_amount: (subtotal - discount_amount) * tax,
            // product: item?.product_name,
            // sku: item?.product_sku,
            // stock: item?.stock || 0,
          }
        }) // Assuming the actual data is in response.data
      },
    }),
    getSalesOrderDeliveryItemsDetail: builder.query({
      query: (url) => url,
      providesTags: ["useGetSalesOrderDeliveryItemsDetailQuery"],
      transformResponse: (response) => {
        const data = response?.data || {}

        return data.map((item) => {
          // const subtotal_invoice = item.price_product * item.qty_delivered
          // const amount_discount =
          //   item?.discount > 0 ? item?.discount * item?.qty : 0
          // const percent =
          //   item.price > 0 ? (amount_discount / item.price) * 100 : 0
          // const discount_percentage = item?.discount_id
          //   ? item?.discount_percentage
          //   : percent
          // const getPercent =
          //   item?.discount_id && item?.discount_percentage > 0
          //     ? item?.discount_percentage / 100
          //     : 0
          // const discount_amount = item?.discount_id
          //   ? getPercent * item.price
          //   : amount_discount

          // const tax = item.tax_percentage > 0 ? item.tax_percentage / 100 : 0
          // const subtotal = item.price * item.qty
          // const tax_amount = (subtotal - discount_amount) * tax

          // const discount_amount_invoiced = discount_amount / item.qty
          // const tax_amount_invoiced = tax_amount / item.qty

          // const preTotal = subtotal_invoice - discount_amount
          return {
            ...item,
            // subtotal_invoice,
            // discount_percentage,
            // discount_amount: discount_amount_invoiced * item.qty_delivered,
            // tax_invoiced: tax_amount_invoiced * item.qty_delivered,
            // product: item?.product_name,
            // sku: item?.product_sku,
            // stock: item?.stock || 0,
            // total: preTotal + tax_amount,
          }
        }) // Assuming the actual data is in response.data
      },
    }),
    getSalesOrderBillingItemsDetail: builder.query({
      query: (url) => url,
      providesTags: ["getSalesOrderBillingItemsDetail"],
      transformResponse: (response) => {
        const data = response?.data || {}

        return data
      },
    }),
    // old sales order
    getSalesOrder: builder.query({
      query: (url) => url,
      transformResponse: (response) => {
        const datas = response?.data?.data || []
        console.log(datas, "cekkk")
        const newdata = datas.map((item, index) => {
          const number = response?.data?.from + index
          return {
            ...item,
            id: item.uid_lead,
            number,
            title: item.order_number,
            contact: `${item?.contact_name} - ${item?.role_name || ""}`,
            sales: item?.sales_name || "-",
            created_by: item?.created_by_name || "-",
            created_on: item?.created_at,
            amount_total: `Rp ${formatNumber(item?.total)}`,
            subtotal: `Rp ${formatNumber(item?.subtotal)}`,
            payment_term: item?.payment_term_name || "-",
            status: getStatusLeadOrder(item?.status),
            status_submit: item?.status_submit,
            print_status: item?.print_status,
            resi_status: item?.resi_status,
            gp_si_number: item?.gp_si_number,
            product_needs: item?.product_needs,
          }
        })
        return {
          data: {
            ...response.data,
            data: newdata,
          },
        } // Assuming the actual data is in response.data
      },
      providesTags: ["getSalesOrder"],
    }),
    getDetailSalesOrder: builder.query({
      query: (url) => url,
      transformResponse: (response) => {
        const data = response?.data || {}
        const product_needs = data.product_needs || []

        const orderDeliveryNew = data?.order_delivery.map((item) => {
          return {
            ...item,
            product: item?.product_name,
            qty_delivery: item?.qty_delivered,
          }
        })

        // nillings
        const dataBillings = data?.billings?.map((item) => {
          return {
            id: item.id,
            account_name: item.account_name,
            account_bank: item.account_bank,
            total_transfer: item.total_transfer,
            transfer_date: item.transfer_date,
            upload_billing_photo: item.upload_billing_photo_url,
            upload_transfer_photo: item.upload_transfer_photo_url,
            status: item.status,
            notes: item.notes ?? "-",
            approved_by_name: item.approved_by_name,
            approved_at: item.approved_at || "-",
            payment_number: item.payment_number || "-",
          }
        })

        // product needs
        const productNeeds = product_needs.map((item) => {
          // const stock_off_market = item?.product?.stock_warehouse.find(
          //   (item) => item.id == data.warehouse_id
          // )

          // const stock_bin = item?.product?.stock_bins?.find(
          //   (itemStock) => itemStock.id == data?.master_bin_id
          // )

          let newData = {
            ...item,
            // sku: item?.product?.sku,
            // product: item?.product?.name || "-",
            // product_id: item?.product_id,
            // price: item?.prices?.final_price,
            // stock: stock_off_market?.stock || stock_bin?.stock,
            disabled: data?.status > 1 ? true : false,
          }

          return newData
        })

        return {
          ...response.data,
          expired_at: moment(
            response.data?.expired_at ?? new Date(),
            "YYYY-MM-DD"
          ),
          order_delivery: orderDeliveryNew,
          billings: dataBillings,
          product_needs: productNeeds,
          printUrl: response?.print,
        } // Assuming the actual data is in response.data
      },
      providesTags: ["getDetailSalesOrder"],
    }),
    getSalesChannel: builder.query({
      query: (type) => ({
        url: `/api/sales-order/channel/${type}/${getItem("account_id")}`,
      }),
      transformResponse: (response) => {
        const data = response?.data || {}

        return data // Assuming the actual data is in response.data
      },
      providesTags: ["getSalesChannel"],
    }),

    updateProductItem: builder.mutation({
      query: ({ url, body }) => ({
        url,
        method: "POST",
        body,
      }),
      invalidatesTags: [
        "getSalesOrder",
        "getDetailSalesOrder",
        "useGetSalesOrderDeliveryItemsDetailQuery",
        "getDetailSalesOrderItems",
      ],
    }),

    assignWarehouse: builder.mutation({
      query: (url) => ({
        url,
        method: "GET",
      }),
      invalidatesTags: ["getDetailSalesOrder", "getSalesOrder"],
    }),

    updatePICWarehouse: builder.mutation({
      query: ({ url, body }) => ({
        url,
        method: "POST",
        body,
      }),
      invalidatesTags: ["getDetailSalesOrder"],
    }),

    updateShippingInfo: builder.mutation({
      query: ({ url, body }) => ({
        url,
        method: "POST",
        body,
      }),
      invalidatesTags: ["getDetailSalesOrder"],
    }),

    insertInvoice: builder.mutation({
      query: ({ url, body }) => ({
        url,
        method: "POST",
        body,
      }),
      invalidatesTags: [
        "getDetailSalesOrder",
        "useGetSalesOrderDeliveryItemsDetailQuery",
        "getDetailSalesOrderItems",
      ],
    }),

    changeAddress: builder.mutation({
      query: ({ url, body }) => ({
        url: "/api/sales-order/change-address",
        method: "POST",
        body,
      }),
      invalidatesTags: ["getDetailSalesOrder"],
    }),

    billingOrderVerification: builder.mutation({
      query: ({ url, body }) => ({
        url,
        method: "POST",
        body,
      }),
      invalidatesTags: [
        "getDetailSalesOrder",
        "getSalesOrderBillingItemsDetail",
      ],
    }),

    updateOrderNote: builder.mutation({
      query: ({ url, body }) => ({
        url,
        method: "POST",
        body,
      }),
      invalidatesTags: ["getDetailSalesOrder"],
    }),

    updateInvoiceDate: builder.mutation({
      query: ({ url, body }) => ({
        url,
        method: "POST",
        body,
      }),
      invalidatesTags: ["getDetailSalesOrder"],
    }),

    cancelInvoiceDelivery: builder.mutation({
      query: (url) => ({
        url,
        method: "GET",
      }),
      invalidatesTags: [
        "getDetailSalesOrder",
        "useGetSalesOrderDeliveryItemsDetailQuery",
        "getDetailSalesOrderItems",
      ],
    }),
    createSalesOrder: builder.mutation({
      query: ({ url, body }) => ({
        url,
        method: "POST",
        body,
      }),
      invalidatesTags: ["getSalesOrder"],
    }),
    submitSIToGp: builder.mutation({
      query: ({ url, body }) => ({
        url,
        method: "POST",
        body,
      }),
      invalidatesTags: ["getSalesOrder"],
    }),
    submitBillingToGp: builder.mutation({
      query: ({ url, body }) => ({
        url,
        method: "POST",
        body,
      }),
      invalidatesTags: ["getSalesOrder", "getSalesChannel"],
    }),
  }),
})

export const {
  useGetDetailSalesOrderFormQuery,
  useGetSalesOrderQuery,
  useGetSalesOrderDetailQuery,
  useGetSalesOrderItemsDetailQuery,
  useGetSalesOrderDeliveryItemsDetailQuery,
  useGetSalesOrderBillingItemsDetailQuery,
  useGetSalesOrderInvoicesQuery,
  useGetDetailSalesOrderQuery,
  useGetSalesChannelQuery,
  useUpdateProductItemMutation,
  useAssignWarehouseMutation,
  useUpdatePICWarehouseMutation,
  useUpdateShippingInfoMutation,
  useInsertInvoiceMutation,
  useBillingOrderVerificationMutation,
  useUpdateOrderNoteMutation,
  useUpdateInvoiceDateMutation,
  useCancelInvoiceDeliveryMutation,
  useCreateSalesOrderMutation,
  useSubmitSIToGpMutation,
  useSubmitBillingToGpMutation,
  useChangeAddressMutation,
} = salesOrderService
