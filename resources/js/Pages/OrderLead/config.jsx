import {
  CheckCircleOutlined,
  CloseCircleOutlined,
  CloseOutlined,
  EditFilled,
  EyeOutlined,
  InfoCircleFilled,
  RightOutlined,
} from "@ant-design/icons"
import { Dropdown, Menu, Steps, Tag, Tooltip, message } from "antd"
import axios from "axios"
import moment from "moment"
import React from "react"
import { useNavigate } from "react-router-dom"
import { formatDate, formatNumber, inArray } from "../../helpers"

const getStatusItems = (status) => {
  switch (status) {
    case "Draft":
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
              label: "Open In New Tab",
              key: "detail_new_tab",
              icon: <EyeOutlined />,
            },
          ],
        },
        {
          label: "Ubah",
          key: "update",
          icon: <EditFilled />,
        },
      ]
    case "New":
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
              label: "Open In New Tab",
              key: "detail_new_tab",
              icon: <EyeOutlined />,
            },
          ],
        },
        {
          label: "Ubah",
          key: "update",
          icon: <EditFilled />,
        },
        {
          label: "Cancel",
          key: "cancel",
          icon: <CloseOutlined />,
        },
      ]
    case "Open":
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
              label: "Open In New Tab",
              key: "detail_new_tab",
              icon: <EyeOutlined />,
            },
          ],
        },
        // {
        //     label: "Ubah",
        //     key: "update",
        //     icon: <EditFilled />,
        // },
        // {
        //     label: "Approve",
        //     key: "approve",
        //     icon: <CheckOutlined />,
        // },
        // {
        //   label: "Cancel",
        //   key: "cancel",
        //   icon: <CloseOutlined />,
        // },
      ]

    case "Closed":
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
              label: "Open In New Tab",
              key: "detail_new_tab",
              icon: <EyeOutlined />,
            },
          ],
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
              label: "Open In New Tab",
              key: "detail_new_tab",
              icon: <EyeOutlined />,
            },
          ],
        },
      ]
  }
}

const ActionMenu = ({ value, status = 1 }) => {
  const navigate = useNavigate()

  return (
    <Menu
      onClick={({ key }) => {
        switch (key) {
          case "detail":
            navigate(`/order/order-lead/detail/${value}`)
            break
          case "detail_new_tab":
            window.open(`/order/order-lead/detail/${value}`)
            break
          case "update":
            navigate(`/order/order-lead/form/${value}`)
            break
          case "cancel":
            return axios.get(`/api/order-lead/cancel/${value}`).then((res) => {
              message.success("Data order berhasil dicancel!")
              window.location.reload()
            })
        }
      }}
      itemIcon={<RightOutlined />}
      items={getStatusItems(status)}
    />
  )
}

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
              {record?.product_needs?.map((item, index) => {
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
    dataIndex: "gp_si_number",
    key: "gp_si_number",
    render: (text) => text || "-",
  },
  {
    title: "Contact",
    dataIndex: "contact",
    key: "contact",
    render: (text, record) => {
      return `${text} - ${record?.contact_role || ""}`
    },
  },
  {
    title: "Sales",
    dataIndex: "sales",
    key: "sales",
  },
  {
    title: "Created by",
    dataIndex: "created_by",
    key: "created_by",
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
  // {
  //   title: "Due Date",
  //   dataIndex: "payment_due_date",
  //   key: "payment_due_date",
  //   render: (text) => {
  //     if (text) {
  //       return formatDate(text)
  //     }
  //     return "-"
  //   },
  // },
  {
    title: "Nominal",
    dataIndex: "amount_total",
    key: "amount_total",
  },
  {
    title: "Payment Term",
    dataIndex: "payment_term",
    key: "payment_term",
  },
  {
    title: "Status",
    dataIndex: "status",
    key: "status",
  },
  {
    title: "Submit GP",
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
    title: "Print Status",
    dataIndex: "print_status",
    key: "print_status",
    align: "center",
    render: (text, record) => {
      if (record.status === "Closed") {
        return <CheckCircleOutlined style={{ color: "#7C9B3A" }} />
      }
      if (text === "not yet") {
        return <CloseCircleOutlined style={{ color: "#FE3A30" }} />
      }
      return <CheckCircleOutlined style={{ color: "#7C9B3A" }} />
    },
  },
  {
    title: "Input Resi",
    dataIndex: "resi_status",
    key: "resi_status",
    align: "center",
    render: (text, record) => {
      if (record.status === "Closed") {
        return <CheckCircleOutlined style={{ color: "#7C9B3A" }} />
      }
      if (text === "not yet") {
        return <CloseCircleOutlined style={{ color: "#FE3A30" }} />
      }
      return <CheckCircleOutlined style={{ color: "#7C9B3A" }} />
    },
  },
  {
    title: "Action",
    key: "id",
    align: "center",
    fixed: "right",
    width: 100,
    render: (text) => (
      <Dropdown.Button
        style={{
          left: -16,
        }}
        // icon={<MoreOutlined />}
        overlay={<ActionMenu value={text.id} status={text.status} />}
      ></Dropdown.Button>
    ),
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
    dataIndex: "product",
    key: "product",
  },
  {
    title: "Tax Amount",
    dataIndex: "tax_amount",
    key: "tax_amount",
    render: (text) => formatNumber(text, "Rp"),
  },
  // {
  //   title: () => {
  //     return (
  //       <Tooltip
  //         className="cursor-help"
  //         overlayStyle={{ maxWidth: 800 }}
  //         title={
  //           "Tanggal jatuh tempo akan ditambah masa tenggang selama 15 hari kedepan"
  //         }
  //       >
  //         <div className="flex items-center">
  //           <InfoCircleFilled
  //             style={{
  //               marginRight: 4,
  //             }}
  //           />
  //           <span>Due Date</span>
  //         </div>
  //       </Tooltip>
  //     )
  //   },
  //   key: "due_date",
  //   index: "due_date",
  //   align: "center",
  //   render: (text, record) => {
  //     return moment(record.due_date).format("DD MMM YYYY") || "-"
  //   },
  // },
  {
    title: "Qty",
    dataIndex: "qty",
    key: "qty",
  },
  {
    title: "Normal Price",
    dataIndex: "price",
    key: "price",
    render: (text) => formatNumber(text, "Rp"),
  },
  {
    title: "Nego Price",
    dataIndex: "final_price",
    key: "final_price",
    render: (text) => formatNumber(text, "Rp"),
  },
]

const productNeedListColumnDetail = [
  {
    title: "Product",
    dataIndex: "product",
    key: "product",
  },
  // {
  //   title: "Tax Amount",
  //   dataIndex: "tax_invoiced",
  //   key: "tax_invoiced",
  //   render: (text) => formatNumber(text, "Rp"),
  // },
  // {
  //   title: () => {
  //     return (
  //       <Tooltip
  //         className="cursor-help"
  //         overlayStyle={{ maxWidth: 800 }}
  //         title={
  //           "Tanggal jatuh tempo akan ditambah masa tenggang selama 15 hari kedepan"
  //         }
  //       >
  //         <div className="flex items-center">
  //           <InfoCircleFilled
  //             style={{
  //               marginRight: 4,
  //             }}
  //           />
  //           <span>Due Date</span>
  //         </div>
  //       </Tooltip>
  //     )
  //   },
  //   key: "due_date",
  //   index: "due_date",
  //   align: "center",
  //   render: (text, record) => {
  //     return moment(record.due_date).format("DD MMM YYYY") || "-"
  //   },
  // },
  {
    title: "DPP",
    dataIndex: "price_nego",
    key: "price_nego",
  },
  {
    title: "Qty",
    dataIndex: "qty",
    key: "qty",
  },
  {
    title: "Disc (%)",
    dataIndex: "discount_percentage",
    key: "discount_percentage",
  },
  {
    title: "Tax (%)",
    dataIndex: "ppn",
    key: "ppn",
    render: (text, record) => (record?.ppn ? `${record?.ppn}%` : 0),
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
    dataIndex: "product",
    key: "product",
  },
  {
    title: "Tax Amount",
    dataIndex: "tax_invoiced",
    key: "tax_invoiced",
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
    dataIndex: "qty_delivery",
    key: "qty_delivery",
  },
  {
    title: "Normal Price",
    dataIndex: "price",
    key: "price",
    render: (text) => formatNumber(text, "Rp"),
  },
  {
    title: "Nego Price",
    dataIndex: "subtotal_invoice",
    key: "subtotal_invoice",
    render: (text) => formatNumber(text, "Rp"),
  },
  {
    title: "Total",
    dataIndex: "total",
    key: "total",
    render: (text) => formatNumber(text, "Rp"),
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
        return formatNumber(text, "Rp")
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
    render: (text) => `Rp ${formatNumber(text, "Rp")}`,
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
    render: (text) => formatNumber(text, "Rp"),
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
    dataIndex: "discount",
    key: "discount",
  },
  {
    title: "Subtotal",
    dataIndex: "total_price",
    key: "total_price",
    render: (text) => formatNumber(text, "Rp"),
  },
  {
    title: "Total Price Nego",
    dataIndex: "price_nego",
    key: "price_nego",
    render: (text) => formatNumber(text, "Rp"),
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
    render: (text) => formatNumber(text, "Rp"),
  },
  {
    title: "Action",
    dataIndex: "action",
    key: "action",
  },
]

const productListLocalColumns = [
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
    render: (text) => formatNumber(text, "Rp"),
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
    title: " Total Dpp + PPN",
    dataIndex: "total",
    key: "total",
    render: (text) => formatNumber(text, "Rp"),
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
  productNeedListColumnInvoice,
  productNeedListColumnDetail,
  productListLocalColumns,
}
