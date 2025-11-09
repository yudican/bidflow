import { CheckCircleOutlined, CloseCircleOutlined } from "@ant-design/icons"
import { Tooltip } from "antd"
import React from "react"
import { formatDate, formatNumber } from "../../../helpers"

const transactionProductListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Nama Product",
    dataIndex: "product_name",
    key: "product_name",
  },
  {
    title: "SKU",
    dataIndex: "sku",
    key: "sku",
  },

  {
    title: "Harga Satuan",
    dataIndex: "price",
    key: "price",
    render: (value) => `Rp ${formatNumber(value)}`,
  },
  {
    title: "UoM",
    dataIndex: "u_of_m",
    key: "u_of_m",
  },
  {
    title: "Qty",
    dataIndex: "qty",
    key: "qty",
  },
  {
    title: "Subtotal",
    dataIndex: "subtotal",
    key: "subtotal",
    render: (value) => `Rp ${formatNumber(value)}`,
  },
]

const transactionListColumn = [
  {
    title: "TRX ID",
    dataIndex: "id_transaksi",
    key: "id_transaksi",
    render: (text, record) => {
      // console.log(record, "record")
      return (
        <Tooltip
          overlayStyle={{ maxWidth: 800 }}
          title={
            <div>
              {record.transaction_detail.map((item, index) => {
                return (
                  <span key={index}>
                    <span>{`${item.product_name} - ${item.qty} ${item?.u_of_m
                      } : Rp ${formatNumber(item.subtotal)}`}</span>{" "}
                    <br />
                  </span>
                )
              })}
            </div>
          }
        >
          <span>{text}</span>
        </Tooltip>
      )
    },
  },
  {
    title: "Nama Customer",
    dataIndex: "user_name",
    key: "user_name",
  },
  {
    title: "Tanggal Transaksi",
    dataIndex: "created_at",
    key: "created_at",
    render: (text, record) => {
      if (text) {
        return formatDate(text)
      }
      return "-"
    },
  },
  {
    title: "Subtotal",
    dataIndex: "subtotal",
    key: "subtotal",
    render: (value) => `Rp ${formatNumber(value)}`,
  },
  {
    title: "Diskon",
    dataIndex: "diskon",
    key: "diskon",
    render: (value) => `Rp ${formatNumber(value)}`,
  },
  {
    title: "Ongkos Kirim",
    dataIndex: "ongkir",
    key: "ongkir",
    render: (value) => `Rp ${formatNumber(value)}`,
  },
  {
    title: "Total Pembayaran",
    dataIndex: "nominal",
    key: "nominal",
    render: (value, record) => `Rp ${formatNumber(value)}`,
  },
  {
    title: "Status",
    dataIndex: "final_status",
    key: "final_status",
  },
  {
    title: "Popaket Status",
    dataIndex: "awb_status",
    key: "awb_status",
    align: "center",
    render: (text, record) => {
      if (record.awb_status > 0) {
        return <CheckCircleOutlined style={{ color: "#7C9B3A" }} />
      }
      return <CloseCircleOutlined style={{ color: "#FE3A30" }} />
    },
  },
  {
    title: "Status Submit GP",
    dataIndex: "gp_submit_number",
    key: "gp_submit_number",
    align: "center",
    render: (text, record) => {
      if (record.gp_submit_number) {
        return <CheckCircleOutlined style={{ color: "#7C9B3A" }} />
      }
      return <CloseCircleOutlined style={{ color: "#FE3A30" }} />
    },
  },
]

const transactionMealPlanListColumn = [
  {
    title: "TRX ID",
    dataIndex: "id_transaksi",
    key: "id_transaksi",
  },
  {
    title: "Nama Customer",
    dataIndex: "user_name",
    key: "user_name",
  },
  {
    title: "Email",
    dataIndex: "user_email",
    key: "user_email",
  },
  {
    title: "No. Handphone",
    dataIndex: "user_phone",
    key: "user_phone",
  },
  {
    title: "Metode Pengiriman",
    dataIndex: "shipping_type_name",
    key: "shipping_type_name",
    render: (text, record) => {
      // if (text) {
      //   return formatDate(text)
      // }
      return text || "-"
    },
  },
  {
    title: "Metode Pembayaran",
    dataIndex: "payment_method_name",
    key: "payment_method_name",
    render: (text, record) => {
      // if (text) {
      //   return formatDate(text)
      // }
      return text || "-"
    },
  },
  {
    title: "Transaction Date",
    dataIndex: "created_at",
    key: "created_at",
    render: (text, record) => {
      if (text) {
        return formatDate(text)
      }
      return "-"
    },
  },
  {
    title: "Batas Waktu Order",
    dataIndex: "expire_payment",
    key: "expire_payment",
    render: (text, record) => {
      if (text) {
        return formatDate(text)
      }
      return "-"
    },
  },
  {
    title: "Subtotal",
    dataIndex: "subtotal",
    key: "subtotal",
    render: (value) => `Rp ${formatNumber(value)}`,
  },
  {
    title: "Diskon",
    dataIndex: "diskon",
    key: "diskon",
    render: (value) => `Rp ${formatNumber(value)}`,
  },
  {
    title: "Ongkos Kirim",
    dataIndex: "ongkir",
    key: "ongkir",
    render: (value) => `Rp ${formatNumber(value)}`,
  },
  {
    title: "Total Pembayaran",
    dataIndex: "nominal",
    key: "nominal",
    render: (value) => `Rp ${formatNumber(value)}`,
  },
]

const transactionUploadPaymentListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Nama Rekening",
    dataIndex: "nama_rekening",
    key: "nama_rekening",
  },
  {
    title: "Nama Bank",
    dataIndex: "bank_dari",
    key: "bank_dari",
  },
  {
    title: "Bank Tujuan",
    dataIndex: "bank_tujuan",
    key: "bank_tujuan",
  },
  {
    title: "Tanggal Upload",
    dataIndex: "tanggal_bayar",
    key: "tanggal_bayar",
  },
  {
    title: "Bukti Transfer",
    dataIndex: "foto_struk",
    key: "foto_struk",
  },

  {
    title: "Jumlah Transfer",
    dataIndex: "jumlah_bayar",
    key: "jumlah_bayar",
    render: (value) => `Rp ${formatNumber(value)}`,
  },
]

export {
  transactionListColumn,
  transactionMealPlanListColumn,
  transactionProductListColumn,
  transactionUploadPaymentListColumn,
}
