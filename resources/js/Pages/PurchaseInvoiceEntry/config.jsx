import {
  CheckCircleOutlined,
  CloseCircleOutlined,
  CloseOutlined,
  EyeOutlined,
} from "@ant-design/icons"
import { Tag, Tooltip } from "antd"
import React from "react"
import { formatDate, formatNumber } from "../../helpers"
import moment from "moment"

const getStatusItems = (status) => {
  switch (status) {
    case "0":
      return [
        {
          label: "Detail",
          key: "detail",
          icon: <EyeOutlined />,
          children: [
            {
              label: "Open Directly",
              key: "detail",
              icon: <EyeOutlined />,
            },
            {
              label: "Open in New Tab",
              key: "detail_new_tab",
              icon: <EyeOutlined />,
            },
          ],
        },
        {
          label: "Cancel",
          key: "cancel",
          icon: <CloseOutlined />,
        },
      ]

    default:
      return [
        {
          label: "Detail",
          key: "detail",
          icon: <EyeOutlined />,
          children: [
            {
              label: "Open Directly",
              key: "detail",
              icon: <EyeOutlined />,
            },
            {
              label: "Open in New Tab",
              key: "detail_new_tab",
              icon: <EyeOutlined />,
            },
          ],
        },
      ]
  }
}

const renderStatusComponent = (status) => {
  switch (status) {
    case "0":
      return <Tag color="orange">UNPAID</Tag>
    case "1":
      return <Tag color="blue">PAID</Tag>
    case "2":
      return <Tag color="red">CANCELED</Tag>
    case "3":
      return <Tag color="purple">Partially Paid</Tag>
    default:
      return <Tag>DRAFT</Tag>
  }
}

const purchaseOrderListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (value, row, index) => index + 1,
  },
  {
    title: "FIS Number",
    dataIndex: "received_number",
    key: "received_number",
    // render: (value, row, index) => index + 1,
    render: (text, record) => {
      return (
        <Tooltip
          overlayStyle={{ maxWidth: 800 }}
          title={
            <div>
              {record.items.map((item, index) => {
                return (
                  <span key={index}>
                    <span>{`${item.po_number || ""}`}</span>
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
    title: "GP Number",
    dataIndex: "gp_invoice_number",
    key: "gp_invoice_number",
  },
  {
    title: "Doc Number",
    dataIndex: "vendor_doc_number",
    key: "vendor_doc_number",
  },
  {
    title: "Invoice Date",
    dataIndex: "invoice_date",
    key: "invoice_date",
    render: (text, record) => {
      return moment(text).format("DD-MM-YYYY")
    },
  },
  {
    title: "Created by",
    dataIndex: "created_by_name",
    key: "created_by_name",
  },
  {
    title: "Vendor",
    dataIndex: "vendor_name",
    key: "vendor_name",
  },
  {
    title: "Type Invoice",
    dataIndex: "type_invoice",
    key: "type_invoice",
  },
  {
    title: "Status",
    dataIndex: "status",
    key: "status",
    defaultSortOrder: "asc",
    align: "center",
    render: (_, { status }) => renderStatusComponent(status),
  },
  {
    title: "Created On",
    dataIndex: "created_at",
    key: "created_at",
    render: (text) => {
      return formatDate(text)
    },
  },
  {
    title: "Submit Invoice Entry GP",
    dataIndex: "status_gp",
    key: "status_gp",
    align: "center",
    render: (text) => {
      if (text == "submited") {
        return <CheckCircleOutlined style={{ color: "#7C9B3A" }} />
      }
      return <CloseCircleOutlined style={{ color: "#FE3A30" }} />
    },
  },
  {
    title: "Submit Manual Payment GP",
    dataIndex: "submit_payment_gp",
    key: "submit_payment_gp",
    align: "center",
    render: (text) => {
      if (text === "submited") {
        return <CheckCircleOutlined style={{ color: "#7C9B3A" }} />
      }
      return <CloseCircleOutlined style={{ color: "#FE3A30" }} />
    },
  },
]

const purchaseOrderWhListColumn = [
  {
    title: "No.",
    dataIndex: "",
    key: "id",
    render: (value, row, index) => index + 1,
  },
  {
    title: "No. PO",
    dataIndex: "po_number",
    key: "po_number",
    render: (text, record) => {
      return (
        <Tooltip
          overlayStyle={{ maxWidth: 800 }}
          title={
            <div>
              {record.items.map((item, index) => {
                return (
                  <span key={index}>
                    <span>{`${item.product_name} - ${item.qty} ${item.uom}`}</span>{" "}
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
    title: "Vendor Code",
    dataIndex: "vendor_code",
    key: "vendor_code",
    render: (text, record) => {
      return (
        <Tooltip
          overlayStyle={{ maxWidth: 800 }}
          title={
            <div>
              {record.items.map((item, index) => {
                return (
                  <span key={index}>
                    <span>{`${item.received_number || ""} - ${
                      item.notes || ""
                    }`}</span>{" "}
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
    title: "Created by",
    dataIndex: "created_by",
    key: "created_by",
  },
  {
    title: "Status",
    dataIndex: "status",
    key: "status",
    defaultSortOrder: "asc",
    align: "center",
    render: (_, { status }) => renderStatusComponent(status),
  },
  {
    title: "Warehouse",
    dataIndex: "warehouse_name",
    key: "warehouse_name",
  },
  {
    title: "PIC Warehouse",
    dataIndex: "warehouse_user_name",
    key: "warehouse_user_name",
  },
  {
    title: "Created On",
    dataIndex: "created_at",
    key: "created_at",
    render: (text) => {
      return formatDate(text)
    },
  },
]

const renderStatusRequisitionComponent = (status) => {
  switch (status) {
    case "0":
      return <Tag color="yellow">Waiting Approval</Tag>
    case "1":
      return <Tag color="blue">On Process</Tag>
    case "2":
      return <Tag color="green">Complete</Tag>
    case "3":
      return <Tag color="red">Rejected</Tag>
    default:
      return <Tag>DRAFT</Tag>
  }
}

const purchaseRequisitionListColumn = [
  {
    title: "No.",
    dataIndex: "",
    key: "id",
    render: (value, row, index) => index + 1,
  },
  {
    title: "No. PR",
    dataIndex: "pr_number",
    key: "pr_number",
    render: (text, record) => {
      return (
        <Tooltip
          overlayStyle={{ maxWidth: 800 }}
          title={
            <div>
              {record.items.map((item, index) => {
                return (
                  <span key={index}>
                    <span>{`${item.product_name} - ${item.qty} ${item.uom}`}</span>{" "}
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
    title: "Vendor Code",
    dataIndex: "vendor_code",
    key: "vendor_code",
    render: (text, record) => {
      return (
        <Tooltip
          overlayStyle={{ maxWidth: 800 }}
          title={
            <div>
              {record.items.map((item, index) => {
                return (
                  <span key={index}>
                    <span>{`${item.received_number || ""} - ${
                      item.notes || ""
                    }`}</span>{" "}
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
    title: "Request by",
    dataIndex: "request_by_name",
    key: "request_by_name",
  },
  {
    title: "Status",
    dataIndex: "request_status",
    key: "request_status",
    defaultSortOrder: "asc",
    align: "center",
    render: (text) => renderStatusRequisitionComponent(text),
  },
  {
    title: "Subtotal",
    dataIndex: "subtotal",
    key: "subtotal",
    align: "right",
    render: (text) => {
      return formatNumber(text, "Rp ")
    },
  },
  {
    title: " TAX (%)",
    dataIndex: "total_tax",
    key: "total_tax",
    align: "center",
    // render: (text) => {
    //   return formatNumber(text, "Rp ")
    // },
  },
  {
    title: "Total",
    align: "right",
    dataIndex: "total",
    key: "total",
    render: (text) => {
      return formatNumber(text, "Rp ")
    },
  },
  {
    title: "Created On",
    dataIndex: "created_at",
    key: "created_at",
    render: (text) => {
      return formatDate(text)
    },
  },
]

const productListColumns = [
  {
    title: "Product",
    dataIndex: "product_id",
    key: "product_id",
    width: 240,
  },
  {
    title: "SKU",
    dataIndex: "sku",
    key: "sku",
    align: "center",
  },
  {
    title: "UoM",
    dataIndex: "uom",
    key: "uom",
    align: "center",
  },
  {
    title: "Harga Satuan",
    dataIndex: "harga_satuan",
    key: "harga_satuan",
    align: "center",
    render: (value, row) => {
      return `Rp ${formatNumber(value)}`
    },
  },
  {
    title: "Qty",
    dataIndex: "qty",
    key: "qty",
    align: "center",
  },
  {
    title: "Total TAX (%)",
    dataIndex: "tax_id",
    key: "tax_id",

    align: "center",
  },
  {
    title: "Subtotal",
    dataIndex: "subtotal",
    key: "subtotal",
    align: "right",
    render: (value, row) => {
      return `Rp ${formatNumber(row.harga_satuan * row.qty)}`
    },
  },
  {
    title: "Total",
    dataIndex: "total",
    key: "total",
    align: "right",
    render: (value, row) => {
      return `Rp ${formatNumber(row.harga_satuan * row.qty)}`
    },
  },
  {
    title: "Action",
    dataIndex: "action",
    key: "action",
    // fixed: "right",
    // align: "center",
    // width: 100,
    // render: (value, row) => {
    //   return (
    //     <div className="cursor-pointer">
    //       <Tag color="blue">
    //         <strong>+</strong>
    //       </Tag>
    //     </div>
    //   )
    // },
  },
]

const purchaseBillingListColumn = [
  {
    title: "No.",
    dataIndex: "",
    key: "id",
    render: (value, row, index) => index + 1,
  },
  {
    title: "FIS Payment Number",
    dataIndex: "payment_number",
    key: "payment_number",
  },
  {
    title: "GP Payment Number",
    dataIndex: "gp_payment_number",
    key: "gp_payment_number",
  },
  {
    title: "Nama Bank",
    dataIndex: "nama_bank",
    key: "nama_bank",
  },
  {
    title: "Nama Pengirim",
    dataIndex: "nama_pengirim",
    key: "nama_pengirim",
  },
  {
    title: "No Rekening",
    dataIndex: "no_rekening",
    key: "no_rekening",
  },
  {
    title: "Nominal",
    dataIndex: "jumlah_transfer",
    key: "jumlah_transfer",
    render: (text) => {
      return formatNumber(text, "Rp ")
    },
  },
  {
    title: "Tax Amount",
    dataIndex: "tax_amount",
    key: "tax_amount",
    render: (text) => {
      return formatNumber(text, "Rp ")
    },
  },
  {
    title: "Submit GP",
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
  {
    title: "Created On",
    dataIndex: "created_at",
    key: "created_at",
    render: (text) => {
      return formatDate(text)
    },
  },
  {
    title: "Created by",
    dataIndex: "created_by_name",
    key: "created_by_name",
  },
  {
    title: "Struct Transfer",
    dataIndex: "bukti_transfer_url",
    key: "bukti_transfer_url",
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
]

const purchaseBillingFormListColumn = [
  {
    title: "No.",
    dataIndex: "",
    key: "id",
    render: (value, row, index) => index + 1,
  },
  {
    title: "Nama Bank",
    dataIndex: "nama_bank",
    key: "nama_bank",
  },
  {
    title: "Nama Pengirim",
    dataIndex: "nama_pengirim",
    key: "nama_pengirim",
  },
  {
    title: "No Rekening",
    dataIndex: "no_rekening",
    key: "no_rekening",
  },
  {
    title: "Nominal",
    dataIndex: "jumlah_transfer",
    key: "jumlah_transfer",
    render: (text) => {
      return formatNumber(text, "Rp ")
    },
  },
  {
    title: "Tax Amount",
    dataIndex: "tax_amount",
    key: "tax_amount",
    render: (text) => {
      return formatNumber(text, "Rp ")
    },
  },
  // {
  //   title: "Created by",
  //   dataIndex: "created_by_name",
  //   key: "created_by_name",
  // },
]

const columns = [
  {
    title: "No.",
    width: 100,
    render: (text, record, index) => index + 1,
    fixed: "left",
  },
  {
    title: "Nama Item",
    dataIndex: "item_name",
    key: "1",
  },
  {
    title: "Jumlah",
    dataIndex: "item_qty",
    key: "2",
  },
  {
    title: "UoM",
    dataIndex: "item_unit",
    key: "3",
  },
  {
    title: "Harga Satuan",
    dataIndex: "item_price",
    key: "4",
    render: (text) => formatNumber(text, "Rp "),
  },
  {
    title: "Total TAX (%)",
    dataIndex: "item_tax",
    key: "5",
  },
  {
    title: "Sub Total",
    dataIndex: "subtotal",
    key: "6",
  },
  {
    title: "Notes",
    dataIndex: "item_note",
    key: "7",
  },
]

export {
  columns,
  getStatusItems,
  productListColumns,
  purchaseBillingFormListColumn,
  purchaseBillingListColumn,
  purchaseOrderListColumn,
  purchaseOrderWhListColumn,
  purchaseRequisitionListColumn,
  renderStatusComponent,
  renderStatusRequisitionComponent,
}
