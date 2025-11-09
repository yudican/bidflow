import {
  CheckCircleOutlined,
  CloseCircleOutlined,
  InfoCircleFilled,
} from "@ant-design/icons"
import { Tag, Tooltip } from "antd"
import React from "react"
import { formatDate, formatNumber } from "../../../helpers"
import moment from "moment"

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
    render: (value, record) =>
      `Rp ${formatNumber(record?.price / record?.qty)}`,
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
    dataIndex: "title",
    key: "title",
    // render: (text, record) => {
    //   // console.log(record, "record")
    //   return (
    //     <Tooltip
    //       overlayStyle={{ maxWidth: 800 }}
    //       title={
    //         <div>
    //           {record.transaction_detail.map((item, index) => {
    //             return (
    //               <span key={index}>
    //                 <span>{`${item.product_name} - ${item.qty} ${
    //                   item?.u_of_m
    //                 } : Rp ${formatNumber(item.subtotal)}`}</span>{" "}
    //                 <br />
    //               </span>
    //             )
    //           })}
    //         </div>
    //       }
    //     >
    //       <span>{text}</span>
    //     </Tooltip>
    //   )
    // },
  },
  {
    title: "Nama Customer",
    dataIndex: "contact_name",
    key: "contact_name",
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
  },
  {
    title: "Diskon",
    dataIndex: "diskon",
    key: "diskon",
    render: (value) => `Rp ${formatNumber(value)}`,
  },
  // {
  //   title: "Ongkos Kirim",
  //   dataIndex: "ongkir",
  //   key: "ongkir",
  //   render: (value) => `Rp ${formatNumber(value)}`,
  // },
  {
    title: "Total Pembayaran",
    dataIndex: "total",
    key: "total",
    render: (value, record) => `Rp ${formatNumber(value)}`,
  },
  // {
  //   title: "Status",
  //   dataIndex: "final_status",
  //   key: "final_status",
  // },
  // {
  //   title: "Popaket Status",
  //   dataIndex: "awb_status",
  //   key: "awb_status",
  //   align: "center",
  //   render: (text, record) => {
  //     if (record.awb_status > 0) {
  //       return <CheckCircleOutlined style={{ color: "#7C9B3A" }} />
  //     }
  //     return <CloseCircleOutlined style={{ color: "#FE3A30" }} />
  //   },
  // },
]

const transactionMealPlanListColumn = [
  {
    title: "TRX ID",
    dataIndex: "title",
    key: "title",
  },
  {
    title: "Nama Customer",
    dataIndex: "contact",
    key: "contact",
  },
  {
    title: "Email",
    dataIndex: "contact_email",
    key: "contact_email",
  },
  {
    title: "No. Handphone",
    dataIndex: "contact_telepon",
    key: "contact_telepon",
  },
  {
    title: "Metode Pengiriman",
    dataIndex: "shipping_method_name",
    key: "shipping_method_name",
    render: (text, record) => {
      // if (text) {
      //   return formatDate(text)
      // }
      return text || "-"
    },
  },
  {
    title: "Metode Pembayaran",
    dataIndex: "payment_term",
    key: "payment_term",
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
  // {
  //   title: "Batas Waktu Order",
  //   dataIndex: "expire_payment",
  //   key: "expire_payment",
  //   render: (text, record) => {
  //     if (text) {
  //       return formatDate(text)
  //     }
  //     return "-"
  //   },
  // },
  {
    title: "Subtotal",
    dataIndex: "subtotal",
    key: "subtotal",
  },
  {
    title: "Diskon",
    dataIndex: "diskon",
    key: "diskon",
    render: (value) => `Rp ${formatNumber(value)}`,
  },
  // {
  //   title: "Ongkos Kirim",
  //   dataIndex: "ongkir",
  //   key: "ongkir",
  //   render: (value) => `Rp ${formatNumber(value)}`,
  // },
  {
    title: "Total Pembayaran",
    dataIndex: "total",
    key: "total",
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

const orderDeliveryColumns = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "SKU",
    dataIndex: "sku",
    key: "sku",
  },
  {
    title: "Product Name",
    dataIndex: "product_name",
    key: "product_name",
  },
  {
    title: "Qty",
    dataIndex: "qty_delivered",
    key: "qty_delivered",
  },
  {
    title: "No Resi",
    dataIndex: "resi",
    key: "resi",
    render: (text) => text || "-",
  },
  {
    title: "Ekspedisi",
    dataIndex: "courier",
    key: "courier",
    render: (text) => text || "-",
  },
  {
    title: "Nama Pengirim",
    dataIndex: "sender_name",
    key: "sender_name",
    render: (text) => text || "-",
  },
  {
    title: "Telepon Pengirim",
    dataIndex: "sender_phone",
    key: "sender_phone",
    render: (text) => text || "-",
  },
  {
    title: "Status",
    dataIndex: "status",
    key: "status",
    render: (text) => text || <Tag color="red">Not Yet</Tag>,
  },
  {
    title: "Invoice Status",
    dataIndex: "status",
    key: "status",
    render: (text, record) => {
      if (record?.is_invoice == 1) {
        return <Tag color="green">Invoiced</Tag>
      }

      return <Tag color="red">Not Invoiced</Tag>
    },
  },
  {
    title: "Submit Klikpajak",
    dataIndex: "submit_klikpajak",
    key: "submit_klikpajak",
    render: (text, record) => {
      if (text == "submitted") {
        return <Tag color="green">Submitted</Tag>
      }

      return <Tag color="red">Not Yet</Tag>
    },
  },
  {
    title: "No. Faktur",
    dataIndex: "no_faktur",
    key: "no_faktur",
    render: (text, record) => {
      if (text) {
        return text
      } else {
        return <Tag color="red">Not Yet</Tag>
      }
    },
  },
]

const productNeedListColumnInvoice = [
  {
    title: "Invoice Number",
    dataIndex: "invoice_number",
    key: "invoice_number",
    render: (text) => text || "-",
  },
  {
    title: "Delivery Number",
    dataIndex: "delivery_number",
    key: "delivery_number",
    render: (text) => text || "-",
  },
  {
    title: "Product",
    dataIndex: "product_name",
    key: "product_name",
  },
  {
    title: "Tax Amount",
    dataIndex: "tax_amount",
    key: "tax_amount",
    render: (text) => formatNumber(text, "Rp"),
  },
  {
    title: "Harga Satuan",
    dataIndex: "price_item",
    key: "price_item",
    render: (text) => formatNumber(text, "Rp"),
  },
  {
    title: "Discount Amount",
    dataIndex: "discount_amount",
    key: "discount_amount",
    render: (text) => formatNumber(text, "Rp"),
  },
  {
    title: () => {
      return (
        <Tooltip
          className="cursor-help"
          overlayStyle={{ maxWidth: 800 }}
          title={
            "Tanggal jatuh tempo akan ditambah masa tenggang selama 15 hari kedepan"
          }
        >
          <div className="flex items-center">
            <InfoCircleFilled
              style={{
                marginRight: 4,
              }}
            />
            <span>Due Date</span>
          </div>
        </Tooltip>
      )
    },
    key: "due_date",
    index: "due_date",
    align: "center",
    render: (text, record) => {
      return moment(record.due_date).format("DD MMM YYYY") || "-"
    },
  },
  {
    title: "Qty",
    dataIndex: "qty_delivered",
    key: "qty_delivered",
  },
  // {
  //   title: "Normal Price",
  //   dataIndex: "price",
  //   key: "price",
  //   render: (text) => formatNumber(text, "Rp"),
  // },
  {
    title: "Nego Price",
    dataIndex: "subtotal",
    key: "subtotal",
    render: (text) => formatNumber(text, "Rp"),
  },
  {
    title: "Total",
    dataIndex: "total",
    key: "total",
    render: (text) => formatNumber(text, "Rp"),
  },
]

export {
  transactionListColumn,
  transactionMealPlanListColumn,
  transactionProductListColumn,
  transactionUploadPaymentListColumn,
  orderDeliveryColumns,
  productNeedListColumnInvoice,
}
