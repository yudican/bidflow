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
    // render: (text, record) => {
    //   // console.log(record, "record PO wh")
    //   if (record.items.length > 0) {
    //     return (
    //       <Tooltip
    //         overlayStyle={{ maxWidth: 800 }}
    //         title={
    //           <div>
    //             {record.items
    //               .filter((value) => value.is_master == 1) // fix redudant issue
    //               .map((item, index) => {
    //                 return (
    //                   <span key={index}>
    //                     <span>{`${item.product_name} - ${item.qty_alocation || item.qty
    //                       } ${item.uom}`}</span>{" "}
    //                     <br />
    //                   </span>
    //                 )
    //               })}
    //           </div>
    //         }
    //       >
    //         <span>{text}</span>
    //       </Tooltip>
    //     )
    //   } else {
    //     return (
    //       <Tooltip title={"-"}>
    //         <span>{text}</span>
    //       </Tooltip>
    //     )
    //   }
    // },
  },
  {
    title: "Vendor Code",
    dataIndex: "vendor_code",
    key: "vendor_code",
    // render: (text, record) => {
    //   return (
    //     <Tooltip
    //       overlayStyle={{ maxWidth: 800 }}
    //       title={
    //         <div>
    //           <span>{record.vendor_name} </span>
    //           {record.items.map((item, index) => {
    //             return (
    //               <span key={index}>
    //                 {/* <span>{`${item.received_number || ""} - ${
    //                   item.notes || ""
    //                 }`}</span>{" "} */}
    //                 <span>{`${item.received_number || "-"}`}</span> <br />
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
    title: "Vendor Name",
    dataIndex: "vendor_name",
    key: "vendor_name",
  },
  {
    title: "Branch Name",
    dataIndex: "branch_name",
    key: "branch_name",
  },
  {
    title: "Payment Term",
    dataIndex: "payment_term",
    key: "payment_term",
  },
  {
    title: "Total Amount",
    dataIndex: "amount",
    key: "amount",
  },
  {
    title: "Status",
    dataIndex: "status_po",
    key: "status_po",
  },
  {
    title: "Created By",
    dataIndex: "createdby",
    key: "createdby",
  },
  {
    title: "Created On",
    dataIndex: "created_date",
    key: "created_date",
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
  columns,
  getStatusItems,
  renderStatusComponent,
  getOrderStatus,
}
