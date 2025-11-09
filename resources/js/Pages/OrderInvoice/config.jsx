import {
  CheckCircleOutlined,
  CloseCircleOutlined,
  InfoCircleFilled,
} from "@ant-design/icons"
import { Steps, Tag, Tooltip } from "antd"
import moment from "moment"
import React from "react"
import { formatDate, formatNumber, formatPhone, inArray } from "../../helpers"

const orderLeadListColumn = [
  {
    title: "No.",
    dataIndex: "number",
    key: "number",
    // render: (value, row, index) => index + 1,
  },
  {
    title: "Title",
    dataIndex: "title",
    key: "title",
    render: (text, record) => {
      // console.log(record, "record order manual")
      return (
        <Tooltip
          overlayStyle={{ maxWidth: 800 }}
          title={
            <div>
              {record?.order_delivery?.map((item, index, array) => {
                // console.log(array, "order_delivery")
                if (array.length > 0) {
                  return (
                    <span key={index}>
                      <span>{`${item.product_name} - ${item.qty} ${item.u_of_m}`}</span>{" "}
                      <br />
                    </span>
                  )
                } else {
                  return "-"
                }
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
    title: "Contact",
    dataIndex: "contact",
    key: "contact",
  },
  {
    title: "Created On",
    dataIndex: "created_on",
    key: "created_on",
    render: (text) => {
      if (text) {
        return formatDate(text)
      }
      return "-"
    },
  },
]

const columns = [
  "Product",
  "Price",
  "Qty",
  "Discount",
  "Tax",
  "Total Price",
  "Final Price",
]
const productNeedListColumn = [
  {
    title: "Invoice Number",
    dataIndex: "invoice_number",
    key: "invoice_number",
    render: (text, record) => {
      // console.log(record, "record order manual")
      return (
        <Tooltip
          overlayStyle={{ maxWidth: 800 }}
          title={
            <div>
              {record?.deliveries?.map((item, index) => {
                // console.log(array, "order_delivery")
                return (
                  <span key={index}>
                    <span>{`${item.product_name} - ${item.qty_delivered} ${item.u_of_m}`}</span>{" "}
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
    title: "GP SI Number",
    dataIndex: "gp_submit_number",
    key: "gp_submit_number",
    render: (text, record) => {
      console.log(record, "record")
      if (text) {
        return text
      }
      return "-"
    },
  },
  {
    title: "No. Faktur Pajak",
    dataIndex: "no_faktur",
    key: "no_faktur",
    render: (text) => text || "-",
  },
  {
    title: "Contact",
    dataIndex: "contact_name",
    key: "contact_name",
    render: (text) => text || "-",
  },
  {
    title: "Sales",
    dataIndex: "sales_name",
    key: "sales_name",
    render: (text) => text || "-",
  },
  {
    title: "Created by",
    dataIndex: "created_by_name",
    key: "created_by_name",
    render: (text) => text || "-",
  },
  {
    title: "Created on",
    dataIndex: "created_at",
    key: "created_at",
    render: (text) => {
      return moment(text).format("DD MMM YYYY")
    },
  },

  {
    title: "Payment Term",
    dataIndex: "payment_term_name",
    key: "payment_term_name",
    render: (text) => text || "-",
  },
  // {
  //   title: "Status",
  //   dataIndex: "status_name",
  //   key: "status_name",
  //   render: (text) => {
  //     switch (text) {
  //       case "1":
  //         return "New"
  //       case "2":
  //         return "Open"
  //       case "3":
  //         return "Close"

  //       default:
  //         return "Cancel"
  //     }
  //   },
  // },
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
    render: (text, record) => {
      console.log(record, "record")
      if (record.due_date) {
        return moment(record.due_date).format("DD MMM YYYY") || "-"
      }

      return "-"
    },
  },
  {
    title: "Invoice Date",
    key: "invoice_date",
    index: "invoice_date",
    render: (text, record) => {
      if (record.invoice_date) {
        return moment(record.invoice_date).format("DD MMM YYYY") || "-"
      }

      return "-"
    },
  },
  {
    title: "Type SO",
    key: "type_so",
    index: "type_so",
    render: (text, record) => record?.type_so,
  },
  {
    title: "Status Submit GP",
    dataIndex: "status_submit",
    key: "status_submit",
    align: "center",
    render: (text) => {
      if (text === "submited") {
        return <CheckCircleOutlined style={{ color: "#7C9B3A" }} />
      }
      return <CloseCircleOutlined style={{ color: "#FE3A30" }} />
    },
  },
  {
    title: "Nominal",
    dataIndex: "amount_invoiced",
    key: "amount_invoiced",
    // fixed: "right",
    render: (text) => formatNumber(text, "Rp ") || 0,
  },
]

const productNeedListColumnDetail = [
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
    title: "Created on",
    dataIndex: "created_at",
    key: "created_at",
    render: (text) => {
      return moment(text).format("DD MMM YYYY")
    },
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
    render: (text, record) => {
      console.log(record, "record")
      if (record.due_date) {
        return moment(record.due_date).format("DD MMM YYYY") || "-"
      }

      return "-"
    },
  },
  {
    title: "Invoice Date",
    key: "invoice_date",
    index: "invoice_date",
    render: (text, record) => {
      if (record.invoice_date) {
        return moment(record.invoice_date).format("DD MMM YYYY") || "-"
      }

      return "-"
    },
  },
  {
    title: "Qty",
    dataIndex: "qty_delivered",
    key: "qty_delivered",
    render: (text) => formatNumber(text) || 0,
  },
  {
    title: "Harga Satuan",
    dataIndex: "price_item",
    key: "price_item",
    render: (text) => formatNumber(text) || 0,
  },
  // {
  //   title: "Tax Amount",
  //   dataIndex: "tax_amount",
  //   key: "tax_amount",
  //   // fixed: "right",
  //   render: (text) => formatNumber(text, "Rp ") || 0,
  // },
  {
    title: "Price Nego",
    dataIndex: "subtotal",
    key: "subtotal",
    // fixed: "right",
    render: (text) => formatNumber(text, "Rp ") || 0,
  },
  {
    title: "Subtotal",
    dataIndex: "total",
    key: "total",
    render: (text) => formatNumber(text, "Rp ") || 0,
  },
]

const trackingListColumn = [
  {
    title: "Time",
    dataIndex: "created_at",
    key: "created_at",
    render: (text) => moment(text).format("ddd, DD MMM YYYY - LT"),
  },
  {
    title: "Description",
    dataIndex: "description",
    key: "description",
  },
  {
    title: "Status",
    dataIndex: "status",
    key: "status",
    render: (text, record) => {
      let dummyTrack = [record, record, record]
      return (
        <Tooltip
          color="white"
          overlayStyle={{ maxWidth: 800 }}
          title={
            <div>
              <Steps progressDot direction="vertical" size="small" current={0}>
                {dummyTrack.reverse().map((row, index) => {
                  return (
                    <Steps.Step
                      style={{ color: "white" }}
                      key={index}
                      title={moment(row.created_at).format(
                        "ddd, DD MMM YYYY - LT"
                      )}
                      subTitle={row.description}
                    />
                  )
                })}
              </Steps>
            </div>
          }
        >
          <span>{text}</span>
        </Tooltip>
      )
    },
  },
]

const productNeedListColumnStep2 = columns.map((column) => {
  return {
    title: column,
    dataIndex: column.replace(/\s/g, "_").toLowerCase(),
    key: column.replace(/\s/g, "_").toLowerCase(),
    render: (text) => {
      if (inArray(column, ["Price", "Total Price", "Final Price"])) {
        return formatNumber(text)
      }
      return text
    },
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
    render: (text) => {
      if (text) {
        return formatDate(text)
      }
      return "-"
    },
  },
  {
    title: "Notes",
    dataIndex: "notes",
    key: "notes",
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

const historyColumns = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    fixed: "left",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Reset By",
    dataIndex: "submitted_by",
    key: "submitted_by",
  },
  {
    title: "Reset Date",
    dataIndex: "created_at",
    key: "created_at",
    render: (text) => {
      if (text) {
        return formatDate(text)
      }
      return "-"
    },
  },
  {
    title: "Number GP Before Submit",
    dataIndex: "ref_number",
    key: "ref_number",
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
    render: (text) => {
      if (text) {
        return formatDate(text)
      }
      return "-"
    },
  },
  {
    title: "End Date",
    dataIndex: "end_date",
    key: "end_date",
    render: (text) => {
      if (text) {
        return formatDate(text)
      }
      return "-"
    },
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

const negotiationsColumns = [
  {
    title: "Notes",
    dataIndex: "notes",
    key: "notes",
  },
  {
    title: "Status",
    dataIndex: "status",
    key: "status",
  },
  {
    title: "Date",
    dataIndex: "created_at",
    key: "created_at",
    render: (text) => {
      if (text) {
        return formatDate(text)
      }
      return "-"
    },
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
    render: (text) => formatNumber(text),
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
    title: "Subtotal",
    dataIndex: "total_price",
    key: "total_price",
    render: (text) => formatNumber(text),
  },
  {
    title: "Total Price Nego",
    dataIndex: "price_nego",
    key: "price_nego",
    render: (text) => formatNumber(text),
  },
  // {
  //   title: "Total Price Nego",
  //   dataIndex: "total_price_nego",
  //   key: "total_price_nego",
  // },
  {
    title: " Total Dpp + PPN",
    dataIndex: "final_price",
    key: "final_price",
    render: (text) => formatNumber(text),
  },
  {
    title: "Action",
    dataIndex: "action",
    key: "action",
  },
]

const orderDeliveryColumns = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    fixed: "left",
    render: (text, record, index) => index + 1,
  },
  {
    title: "SKU",
    dataIndex: "sku",
    key: "sku",
  },
  {
    title: "Product Name",
    dataIndex: "product",
    key: "product",
    render: (_, record) =>
      record?.product_need?.product_name || record?.product_name,
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
    render: (text) => formatPhone(text) || "-",
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
    fixed: "right",
    render: (text, record) => {
      if (record?.is_invoice == 1) {
        return <Tag color="green">Invoiced</Tag>
      }

      return <Tag color="red">Not Invoiced</Tag>
    },
  },
]

const ethixColumns = [
  {
    title: "No.",
    width: 100,
    render: (text, record, index) => index + 1,
    fixed: "left",
  },
  {
    title: "SO Number",
    dataIndex: "so_number",
    key: "1",
  },
  {
    title: "SKU",
    dataIndex: "sku",
    key: "2",
  },
  {
    title: "Resi",
    dataIndex: "awb_number",
    key: "3",
  },
  {
    title: "Status",
    dataIndex: "status",
    key: "4",
  },
  {
    title: "Created On",
    dataIndex: "created_at",
    render: (text) => {
      return formatDate(text)
    },
  },
]

export {
  activityColumns,
  billingColumns,
  ethixColumns,
  negotiationsColumns,
  orderDeliveryColumns,
  orderLeadListColumn,
  productListColumns,
  productNeedListColumn,
  productNeedListColumnStep2,
  trackingListColumn,
  productNeedListColumnDetail,
  historyColumns,
}
