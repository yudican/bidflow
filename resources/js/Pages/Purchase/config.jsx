import {
  CheckCircleOutlined,
  CloseCircleOutlined,
  CloseOutlined,
  EyeOutlined,
} from "@ant-design/icons"
import { Tag, Tooltip } from "antd"
import React from "react"
import { formatDate, formatNumber } from "../../helpers"
//test
const getStatusItems = (status) => {
  switch (status) {
    case "0":
      return [
        {
          label: "Detail ",
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
    case "9":
      return <Tag color="magenta">Partial Received</Tag>

    default:
      return <Tag>Unknown</Tag>
  }
}

const getOrderStatus = (status) => {
  switch (status) {
    case "0":
      return 0
    case "1":
      return 2
    case "2":
      return 3
    case "3":
      return 7
    case "4":
      return 6
    case "5":
      return 1
    case "6":
      return 8
    case "7":
      return 5
    case "8":
      return 9
    case "9":
      return 4

    default:
      return 10
  }
}

const purchaseOrderListColumn = [
  {
    title: "No.",
    dataIndex: "number",
    key: "number",
    // render: (value, row, index) => index + 1,
  },
  {
    title: "No. PO",
    dataIndex: "po_number",
    key: "po_number",
    render: (text, record) => {
      // console.log(record, "record PO wh")
      if (record.items.length > 0) {
        return (
          <Tooltip
            overlayStyle={{ maxWidth: 800 }}
            title={
              <div>
                {record.items
                  .filter((value) => value.is_master == 1) // fix redudant issue
                  .map((item, index) => {
                    return (
                      <span key={index}>
                        <span>{`${item.product_name} - ${item.qty_alocation || item.qty
                          } ${item.uom}`}</span>{" "}
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
      } else {
        return (
          <Tooltip title={"-"}>
            <span>{text}</span>
          </Tooltip>
        )
      }
    },
  },
  {
    title: "GP PO number",
    dataIndex: "gp_po_number",
    key: "gp_po_number",
    render: (text, record) => {
      const receivedNumber = record?.items?.map((value) => (
        <>
          {value.received_number}
          <br />
        </>
      ))

      if (!text) {
        return "-"
      } else {
        return <Tooltip title={receivedNumber}>{text}</Tooltip>
      }
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
              <span>{record.vendor_name} </span>
              {record.items.map((item, index) => {
                return (
                  <span key={index}>
                    {/* <span>{`${item.received_number || ""} - ${
                      item.notes || ""
                    }`}</span>{" "} */}
                    <span>{`${item.received_number || "-"}`}</span> <br />
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
    title: "Vendor Name",
    dataIndex: "vendor_name",
    key: "vendor_name",
  },
  {
    title: "Created by",
    dataIndex: "created_by",
    key: "created_by",
  },
  {
    title: "Submit by",
    dataIndex: "submit_by_name",
    key: "submit_by_name",
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
    title: "Submit GP",
    dataIndex: "status_gp",
    key: "status_gp",
    align: "center",
    render: (text) => {
      if (!text) {
        return "-"
      } else if (text === "submited") {
        return <CheckCircleOutlined style={{ color: "#7C9B3A" }} />
      } else {
        return <CloseCircleOutlined style={{ color: "#FE3A30" }} />
      }
    },
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
    title: "Total TAX (%)",
    dataIndex: "total_tax",
    key: "total_tax",
    align: "center",
    render: (text) => {
      return formatNumber(text, "Rp ")
    },
  },
  {
    title: "Total",
    align: "right",
    dataIndex: "total",
    key: "total",
    render: (text, record) => {
      return formatNumber((record.subtotal + record.total_tax), "Rp ")
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
      // console.log(record, "record PO wh")
      if (record.items.length > 0) {
        return (
          <Tooltip
            overlayStyle={{ maxWidth: 800 }}
            title={
              <div>
                {record.items
                  .filter((value) => value.is_master == 1) // fix redudant issue
                  .map((item, index) => {
                    return (
                      <span key={index}>
                        <span>{`${item.product_name} - ${item.qty_alocation || item.qty
                          } ${item.uom}`}</span>{" "}
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
      } else {
        return (
          <Tooltip title={"-"}>
            <span>{text}</span>
          </Tooltip>
        )
      }
    },
  },
  {
    title: "Vendor Code",
    dataIndex: "vendor_code",
    key: "vendor_code",
    render: (text, record) => {
      console.log(record, "record wh vendor")
      if (record.items.length > 0) {
        return (
          <Tooltip
            overlayStyle={{ maxWidth: 800 }}
            title={
              <div>
                {record.items.map((item, index) => {
                  return (
                    <span key={index}>
                      <span>{item.received_number || "Not yet"}</span> <br />
                    </span>
                  )
                })}
              </div>
            }
          >
            <span>{text}</span>
          </Tooltip>
        )
      } else {
        return (
          <Tooltip title={"-"}>
            <span>{text}</span>
          </Tooltip>
        )
      }
    },
  },
  {
    title: "Vendor Name",
    dataIndex: "vendor_name",
    key: "vendor_name",
  },
  {
    title: "GP PO number",
    dataIndex: "gp_po_number",
    key: "gp_po_number",
    render: (text) => text || "-",
  },
  {
    title: "Created by",
    dataIndex: "created_by",
    key: "created_by",
  },
  {
    title: "Submit by",
    dataIndex: "submit_by_name",
    key: "submit_by_name",
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
    title: "Submit GP",
    dataIndex: "status_gp",
    key: "status_gp",
    align: "center",
    render: (text) => {
      if (!text) {
        return "-"
      } else if (text === "submited") {
        return <CheckCircleOutlined style={{ color: "#7C9B3A" }} />
      } else {
        return <CloseCircleOutlined style={{ color: "#FE3A30" }} />
      }
    },
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
      console.log("checcch")
      console.log(record)
      return (
        <Tooltip
          overlayStyle={{ maxWidth: 800 }}
          title={
            <div>
              {record.items.map((item, index) => {
                return (
                  <span key={index}>
                    <span>{`${item.item_name} - ${item.item_qty}`}</span> <br />
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
  // {
  //   title: "Vendor Code",
  //   dataIndex: "vendor_code",
  //   key: "vendor_code",
  //   render: (text, record) => {
  //     return (
  //       <Tooltip
  //         overlayStyle={{ maxWidth: 800 }}
  //         title={
  //           <div>
  //             {record.items.map((item, index) => {
  //               return (
  //                 // <span key={index}>
  //                 //   <span>{`${item.received_number || ""} - ${
  //                 //     item.notes || ""
  //                 //   }`}</span>{" "}
  //                 //   <br />
  //                 // </span>
  //                 <span key={index}>
  //                   <span>{item.received_number || "-"}</span> <br />
  //                 </span>
  //               )
  //             })}
  //           </div>
  //         }
  //       >
  //         <span>{text || "-"} </span>
  //       </Tooltip>
  //     )
  //   },
  // },
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
  // {
  //   title: "Subtotal",
  //   dataIndex: "subtotal",
  //   key: "subtotal",
  //   align: "right",
  //   render: (text) => {
  //     return formatNumber(text, "Rp ")
  //   },
  // },
  // {
  //   title: " TAX (%)",
  //   dataIndex: "total_tax",
  //   key: "total_tax",
  //   align: "center",
  //   // render: (text) => {
  //   //   return formatNumber(text, "Rp ")
  //   // },
  // },
  // {
  //   title: "Total",
  //   align: "right",
  //   dataIndex: "total",
  //   key: "total",
  //   render: (text) => {
  //     return formatNumber(text, "Rp ")
  //   },
  // },
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
  // {
  //   title: "Total TAX (%) c",
  //   dataIndex: "tax_id",
  //   key: "tax_id",

  //   align: "center",
  // },
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
    fixed: "right",
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

const ethixColumns = [
  {
    title: "No.",
    width: 100,
    render: (text, record, index) => index + 1,
    fixed: "left",
  },
  {
    title: "PO Number",
    dataIndex: "po_number",
    key: "1",
  },
  {
    title: "Qty",
    dataIndex: "qty",
    key: "2",
  },
  {
    title: "Qty Good",
    dataIndex: "qty_good",
    key: "3",
  },
  {
    title: "Qty Quarantine",
    dataIndex: "qty_quarantine",
    key: "4",
  },
  {
    title: "Total Qty",
    dataIndex: "qty_total",
    key: "5",
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
  purchaseOrderListColumn,
  purchaseOrderWhListColumn,
  productListColumns,
  purchaseBillingListColumn,
  purchaseRequisitionListColumn,
  columns,
  ethixColumns,
  getStatusItems,
  renderStatusComponent,
  renderStatusRequisitionComponent,
  getOrderStatus,
}
