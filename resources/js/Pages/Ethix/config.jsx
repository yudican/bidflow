import { Input, InputNumber } from "antd"
import React from "react"
import { useNavigate } from "react-router-dom"
import { formatNumber } from "../../helpers"

const columns = [
  "invoice_number",
  "channel_origin",
  "shop_name",
  "name",
  "total_discount",
  // "sku",
  // "qty",
  "status",
  "status_submit",
]

const newColumns = columns.map((column) => {
  return {
    title: column.replace(/_/g, " ").toUpperCase(),
    dataIndex: column,
    key: column,
  }
})

const orderListColumn = [
  {
    title: "No.",
    key: "id",
    fixed: "left",
    render: (text, record, index) => index + 1,
  },
  ...newColumns,
  {
    title: "Action",
    key: "id",
    fixed: "right",
    width: 100,
    render: (text) => <ActionButton value={text} />,
  },
]

const orderItemsColumn = [
  {
    title: "No.",
    dataIndex: "key",
    key: "key",
    render: (value, row, index) => index + 1,
  },
  {
    title: "Product",
    dataIndex: "product_name",
    key: "product_name",
  },
  {
    title: "SKU",
    dataIndex: "sku",
    key: "sku",
  },
  {
    title: "Qty",
    dataIndex: "qty",
    key: "qty",
    render: (text) => {
      return <InputNumber value={text} />
    },
  },
  {
    title: "Regular Price (Rp)",
    dataIndex: "regular_price",
    key: "regular_price",
    // render: (text) => `Rp. ${formatNumber(text)}`,
    render: (text) => <Input value={formatNumber(text, "Rp. ")} />,
  },
  {
    title: "Selling Price (Rp)",
    dataIndex: "selling_price",
    key: "selling_price",
    // render: (text) => `Rp. ${formatNumber(text)}`,
    render: (text) => <Input value={formatNumber(text, "Rp. ")} />,
  },
  {
    title: "Amount",
    dataIndex: "amount",
    key: "amount",
    // render: (text) => `Rp. ${formatNumber(text)}`,
    render: (text) => <Input value={formatNumber(text, "Rp. ")} />,
  },
]

const orderStatus = [
  {
    label: "ALL",
    value: "All orders",
  },
  {
    label: "PENDING_PAYMENT",
    value: "Unpaid order",
  },
  {
    label: "PARTIALLY_PAID",
    value: "Partially paid order",
  },
  {
    label: "PAID",
    value: "Totally paid order",
  },
  {
    label: "READY_TO_SHIP",
    value: "Ready to ship order",
  },
  {
    label: "SHIPPING",
    value: "Shipping order",
  },
  {
    label: "DELIVERED",
    value: "Delivered order",
  },
  {
    label: "CANCELLED",
    value: "Cancelled order",
  },
  {
    label: "RETURNED",
    value: "Returned order",
  },
]

const ActionButton = ({ value }) => {
  const navigate = useNavigate()
  return (
    <button
      onClick={() => navigate(`/mp-ethix/detail/${value.id}`)}
      className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
    >
      Detail
    </button>
  )
}

export { orderItemsColumn, orderListColumn, orderStatus }
