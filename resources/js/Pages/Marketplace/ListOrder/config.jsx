import { CheckCircleOutlined, CloseCircleOutlined } from "@ant-design/icons"
import React from "react"
import { formatNumber } from "../../../helpers"

const packageListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "GP Number",
    dataIndex: "gp_number",
    key: "trx_id",
    render: (text) => text || "-",
  },
  {
    title: "TRX ID",
    dataIndex: "trx_id",
    key: "trx_id",
  },
  {
    title: "CUSTOMER CODE",
    dataIndex: "customer_code",
    key: "customer_code",
  },
  {
    title: "CUSTOMER NAME",
    dataIndex: "customer_name",
    key: "customer_name",
  },
  {
    title: "CHANNEL",
    dataIndex: "channel",
    key: "channel",
  },
  {
    title: "STORE NAME",
    dataIndex: "store",
    key: "store",
  },
  {
    title: "AMOUNT",
    dataIndex: "amount",
    key: "amount",
    render: (text) => formatNumber(text, "Rp. "),
  },
  {
    title: "PAYMENT METHOD",
    dataIndex: "payment_method",
    key: "payment_method",
  },
  {
    title: "WAREHOUSE",
    dataIndex: "warehouse",
    key: "warehouse",
  },
  {
    title: "TRANSACTION DATE",
    dataIndex: "trx_date",
    key: "trx_date",
  },
  {
    title: "COURIR",
    dataIndex: "courir",
    key: "courir",
  },
  {
    title: "Shipping Fee (Non-Cashless)",
    dataIndex: "shipping_fee_non_cashlesh",
    key: "shipping_fee_non_cashlesh",
    render: (text) => formatNumber(text, "Rp. "),
  },
  {
    title: "Platform Rebate",
    dataIndex: "platform_rebate",
    key: "platform_rebate",
    render: (text) => formatNumber(text, "Rp. "),
  },
  {
    title: "Voucher Seller",
    dataIndex: "voucher_seller",
    key: "voucher_seller",
    render: (text) => formatNumber(text, "Rp. "),
  },
  {
    title: "Shipping Fee Difference",
    dataIndex: "shipping_fee_deference",
    key: "shipping_fee_deference",
    render: (text) => formatNumber(text, "Rp. "),
  },
  {
    title: "Platform Fulfilment Fee",
    dataIndex: "platform_fulfilment",
    key: "platform_fulfilment",
    render: (text) => formatNumber(text, "Rp. "),
  },
  {
    title: "Service Fee",
    dataIndex: "service_fee",
    key: "service_fee",
    render: (text) => formatNumber(text, "Rp. "),
  },
  {
    title: "Total Amount",
    dataIndex: "total_amount",
    key: "total_amount",
  },
  {
    title: "Balance Due",
    dataIndex: "balance_due",
    key: "balance_due",
  },
  {
    title: "VAT IN",
    dataIndex: "vat_in",
    key: "vat_in",
  },
  {
    title: "VAT OUT",
    dataIndex: "vat_out",
    key: "vat_out",
  },
  {
    title: "AWB NUMBER",
    dataIndex: "awb",
    key: "awb",
  },
  {
    title: "STATUS",
    dataIndex: "status",
    key: "status",
  },
  {
    title: "SHIPPING STATUS",
    dataIndex: "shipping_status",
    key: "shipping_status",
  },
  {
    title: "STATUS ETHIX",
    dataIndex: "status_ethix",
    key: "status_ethix",
    align: "center",
    render: (text) => {
      if (text === "submited") {
        return <CheckCircleOutlined style={{ color: "#7C9B3A" }} />
      }
      return <CloseCircleOutlined style={{ color: "#FE3A30" }} />
    },
  },
  {
    title: "STATUS GP",
    dataIndex: "status_gp",
    key: "status_gp",
    align: "center",
    render: (text) => {
      if (text === "submited") {
        return <CheckCircleOutlined style={{ color: "#7C9B3A" }} />
      }
      return <CloseCircleOutlined style={{ color: "#FE3A30" }} />
    },
  },
]

const itemDetails = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Produk",
    dataIndex: "product_name",
    key: "product_name",
  },
  {
    title: "SKU",
    dataIndex: "sku",
    key: "sku",
  },
  {
    title: "QTY",
    dataIndex: "qty",
    key: "qty",
  },
  {
    title: "Price",
    dataIndex: "final_price",
    key: "final_price",
    render: (text) => formatNumber(text, "Rp. "),
  },
  {
    title: "Subtotal",
    dataIndex: "subtotal",
    key: "subtotal",
    render: (text, record) => record.qty * record.final_price,
  },
]

export { itemDetails, packageListColumn }
