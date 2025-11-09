import { CloseOutlined, EyeOutlined } from "@ant-design/icons"
import { Tag, Tooltip } from "antd"
import { divide } from "lodash"
import React from "react"
import { formatDate, formatNumber } from "../../helpers"

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
      return <Tag>Draft</Tag>
    case "1":
      return <Tag color="blue">On Process</Tag>
    case "2":
      return <Tag color="purple">Delivery</Tag>
    case "3":
      return <Tag color="gold">Stock Opname</Tag>
    case "4":
      return <Tag color="blue">Delivered</Tag>
    case "5":
      return <Tag color="orange">Waiting Approval</Tag>
    case "6":
      return <Tag color="red">Rejected</Tag>
    case "7":
      return <Tag color="green">Complete</Tag>
    case "8":
      return <Tag color="red">Canceled</Tag>

    default:
      return <Tag>Unknown</Tag>
  }
}

const purchaseOrderListColumn = [
  {
    title: "No.",
    dataIndex: "",
    key: "id",
    render: (value, row, index) => index + 1,
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
    title: "Package",
    dataIndex: "package_name",
    key: "package_name",
  },
  {
    title: "Brand",
    dataIndex: "brand",
    key: "brand",
  },
  {
    title: "Begin Stock",
    dataIndex: "begin_stock",
    key: "begin_stock",
    render: (text) => {
      if (!text) {
        return 0
      } else {
        return text
      }
    },
  },
  {
    title: "In. Purchase Delivered",
    dataIndex: "purchase_delivered",
    key: "purchase_delivered",
    render: (text) => {
      if (!text) {
        return 0
      } else {
        return text
      }
    },
  },
  {
    title: "In. Product Return",
    dataIndex: "product_return",
    key: "product_return",
    render: (text) => {
      if (!text) {
        return 0
      } else {
        return text
      }
    },
  },
  {
    title: "In. Sales Return",
    dataIndex: "sales_return",
    key: "sales_return",
    render: (text) => {
      if (!text) {
        return 0
      } else {
        return text
      }
    },
  },
  {
    title: "In. Transfer In",
    dataIndex: "transfer_in",
    key: "transfer_in",
    render: (text) => {
      if (!text) {
        return 0
      } else {
        return text
      }
    },
  },
  {
    title: "Out. Stock Order",
    dataIndex: "stock",
    key: "stock",
    render: (text) => {
      if (!text) {
        return 0
      } else {
        return text
      }
    },
  },
  {
    title: "Out. Return To Suplier",
    dataIndex: "return_suplier",
    key: "return_suplier",
    render: (text) => {
      if (!text) {
        return 0
      } else {
        return text
      }
    },
  },
  {
    title: "Out. Sales",
    dataIndex: "sales",
    key: "sales",
    render: (text) => {
      if (!text) {
        return 0
      } else {
        return text
      }
    },
  },
  {
    title: "Out. Transfer Out",
    dataIndex: "transfer_out",
    key: "transfer_out",
    render: (text) => {
      if (!text) {
        return 0
      } else {
        return text
      }
    },
  },
  {
    title: "End Stock",
    render: (text, record) => {
      var incoming =
        parseInt(!record.purchase_delivered ? 0 : record.purchase_delivered) +
        parseInt(!record.product_return ? 0 : record.product_return) +
        parseInt(
          !record.sales_return
            ? 0
            : record.sales_return +
                parseInt(!record.transfer_in ? 0 : record.transfer_in)
        )
      var outcoming =
        parseInt(!record.stock ? 0 : record.stock) +
        parseInt(!record.return_suplier ? 0 : record.return_suplier) +
        parseInt(!record.sales ? 0 : record.sales) +
        parseInt(!record.transfer_out ? 0 : record.transfer_out)
      return (
        parseInt(!record.begin_stock ? 0 : record.begin_stock) +
        incoming -
        outcoming
      )
    },
  },
  {
    title: "End Forecast",
    render: (text, record) => {
      return (
        parseInt(!record.stock ? 0 : record.stock) +
        parseInt(!record.sales ? 0 : record.sales)
      )
    },
  },
  //   {
  //     title: "Status",
  //     dataIndex: "status",
  //     key: "status",
  //     defaultSortOrder: "asc",
  //     align: "center",
  //     render: (_, { status }) => renderStatusComponent(status),
  //   },
  //   {
  //     title: "Subtotal",
  //     dataIndex: "subtotal",
  //     key: "subtotal",
  //     align: "right",
  //     render: (text) => {
  //       return formatNumber(text, "Rp ")
  //     },
  //   },
  //   {
  //     title: "Total TAX (%)",
  //     dataIndex: "total_tax",
  //     key: "total_tax",
  //     align: "center",
  //     render: (text) => {
  //       return formatNumber(text, "Rp ")
  //     },
  //   },
  //   {
  //     title: "Total",
  //     align: "right",
  //     dataIndex: "total",
  //     key: "total",
  //     render: (text) => {
  //       return formatNumber(text, "Rp ")
  //     },
  //   },
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
                  <span>
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
                  <span>
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
                  <span>
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
                  <span>
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
    // title: "Action",
    // dataIndex: "action",
    // key: "action",
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
  purchaseOrderListColumn,
  purchaseOrderWhListColumn,
  productListColumns,
  purchaseBillingListColumn,
  purchaseRequisitionListColumn,
  columns,
  getStatusItems,
  renderStatusComponent,
  renderStatusRequisitionComponent,
}
