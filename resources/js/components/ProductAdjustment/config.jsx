import { formatNumber } from "../../helpers"

export const productListColumns = [
    {
        title: "Produk",
        dataIndex: "product_id",
        key: "product_id",
        width: 300,
    },
    {
        title: "Qty Sebelumnya",
        dataIndex: "stock",
        key: "stock",
    },
    {
        title: "Qty Penyesuaian",
        dataIndex: "qty",
        key: "qty",
    },
    {
        title: "Action",
        dataIndex: "action",
        key: "action",
        fixed: "right",
    },
]

export const productListDetColumns = [
    {
        title: "Produk",
        dataIndex: "product_id",
        key: "product_id",
        width: 300,
    },
    {
        title: "Qty Sebelumnya",
        dataIndex: "stock_awal",
        key: "stock_awal",
    },
    {
        title: "Qty Penyesuaian",
        dataIndex: "qty",
        key: "qty",
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
