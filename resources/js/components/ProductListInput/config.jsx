import { formatNumber } from "../../helpers"

export const productListColumns = [
  {
    title: "Produk",
    dataIndex: "product_id",
    key: "product_id",
    width: 300,
  },
  {
    title: "Price",
    dataIndex: "price",
    key: "price",
    render: (text) => formatNumber(text, "Rp"),
  },
  {
    title: "Harga Satuan",
    dataIndex: "price_satuan",
    key: "price_satuan",
    render: (text) => formatNumber(text, "Rp"),
  },

  {
    title: "Qty ",
    dataIndex: "qty",
    key: "qty",
  },
  {
    title: "Tax",
    dataIndex: "tax_id",
    key: "tax_id",
  },
  {
    title: "Diskon (Rp)",
    dataIndex: "discount",
    key: "discount",
  },
  {
    title: "Subtotal",
    dataIndex: "subtotal",
    key: "subtotal",
    render: (text) => formatNumber(text, "Rp"),
  },
  {
    title: "Total Price Nego",
    dataIndex: "price_nego",
    key: "price_nego",
    render: (text) => formatNumber(text, "Rp"),
  },
  {
    title: " Total Dpp",
    dataIndex: "total",
    key: "total",
    render: (text) => formatNumber(text, "Rp"),
  },
  {
    title: "Action",
    dataIndex: "action",
    key: "action",
    fixed: "right",
  },
]

export const defaultItems = [
  {
    id: null,
    key: 0,
    product_id: null,
    price: null,
    price_satuan: 0,
    qty: 1,
    tax_id: null,
    tax_amount: 0,
    tax_percentage: 0,
    discount_percentage: 0,
    discount: 0,
    discount_amount: 0,
    subtotal: null,
    price_nego: null,
    total: 0,
    margin_price: 0,
    stock: 0,
  },
]
