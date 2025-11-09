import { formatNumber } from "../../helpers"

const salesReturnListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "SR Nuber",
    dataIndex: "sr_number",
    key: "sr_number",
  },
  {
    title: "Order Number",
    dataIndex: "order_number",
    key: "order_number",
  },
  {
    title: "Contact",
    dataIndex: "contact",
    key: "contact",
  },
  {
    title: "Sales",
    dataIndex: "sales",
    key: "sales",
  },
  {
    title: "Status",
    dataIndex: "status",
    key: "status",
  },
  {
    title: "Created On",
    dataIndex: "created_on",
    key: "created_on",
  },
]

const orderNumberColumns = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Order Number",
    dataIndex: "value",
    key: "value",
  },
  {
    title: "Nama Customer",
    dataIndex: "label",
    key: "label",
  },
]

const productListColumns = [
  {
    title: "Product",
    dataIndex: "product_id",
    key: "product_id",
    width: 300,
  },
  {
    title: "Price",
    dataIndex: "price",
    key: "price",
  },

  {
    title: "Qty",
    dataIndex: "qty",
    key: "qty",
  },
  {
    title: "Tax",
    dataIndex: "tax_id",
    key: "tax_id",
  },
  {
    title: "Discount",
    dataIndex: "discount_id",
    key: "discount_id",
  },
  {
    title: "Total Price",
    dataIndex: "total",
    key: "total",
  },
  {
    title: "Action",
    dataIndex: "action",
    key: "action",
  },
]

const columns = ["Product", "Price", "Qty", "Total Price"]
const productNeedListColumn = [
  "Product",
  "Price",
  "Qty",
  "Discount",
  "Tax",
  "Total Price",
].map((column) => {
  return {
    title: column,
    dataIndex: column.replace(/\s/g, "_").toLowerCase(),
    key: column.replace(/\s/g, "_").toLowerCase(),
  }
})

const productNeedListColumnStep2 = columns.map((column) => {
  return {
    title: column,
    dataIndex: column.replace(/\s/g, "_").toLowerCase(),
    key: column.replace(/\s/g, "_").toLowerCase(),
  }
})

const billingColumns = [
  {
    title: "Name",
    dataIndex: "account_name",
    key: "account_name",
  },
  {
    title: "Bank",
    dataIndex: "account_bank",
    key: "account_bank",
  },
  {
    title: "Nominal",
    dataIndex: "total_transfer",
    key: "total_transfer",
    render: (text) => `Rp ${formatNumber(text)}`,
  },
  {
    title: "Tanggal Transfer",
    dataIndex: "transfer_date",
    key: "transfer_date",
  },
  {
    title: "Attachment",
    dataIndex: "upload_billing_photo",
    key: "upload_billing_photo",
    render: (text) => {
      if (text) {
        return (
          <a href={text} target="_blank" rel="noreferrer">
            Lihat Bukti
          </a>
        )
      }
      return "-"
    },
  },
  {
    title: "Struct Transfer",
    dataIndex: "upload_transfer_photo",
    key: "upload_transfer_photo",
    render: (text) => {
      if (text) {
        return (
          <a href={text} target="_blank" rel="noreferrer">
            Lihat Bukti
          </a>
        )
      }
      return "-"
    },
  },
  {
    title: "Approved by",
    dataIndex: "approved_by_name",
    key: "approved_by_name",
  },
  {
    title: "Approved At",
    dataIndex: "approved_at",
    key: "approved_at",
  },
  {
    title: "Payment Number",
    dataIndex: "payment_number",
    key: "payment_number",
  },
]

const activityColumns = [
  {
    title: "Title",
    dataIndex: "title",
    key: "title",
  },
  {
    title: "Start Date",
    dataIndex: "start_date",
    key: "start_date",
  },
  {
    title: "End Date",
    dataIndex: "end_date",
    key: "end_date",
  },
  {
    title: "Description",
    dataIndex: "description",
    key: "description",
  },
  {
    title: "Result",
    dataIndex: "result",
    key: "result",
  },
]

export {
  salesReturnListColumn,
  orderNumberColumns,
  productListColumns,
  productNeedListColumn,
  productNeedListColumnStep2,
  billingColumns,
  activityColumns,
}
