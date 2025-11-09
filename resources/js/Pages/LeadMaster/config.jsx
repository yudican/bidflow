import {
  CloseOutlined,
  EditFilled,
  EyeOutlined,
  RightOutlined,
} from "@ant-design/icons"
import { Dropdown, Menu, Tag } from "antd"
import moment from "moment"
import React from "react"
import { useNavigate } from "react-router-dom"
import { formatDate } from "../../helpers"

const getStatusItems = (status) => {
  switch (status) {
    case "New":
      return [
        {
          label: "Detail",
          key: "detail",
          icon: <EyeOutlined />,
        },
        {
          label: "Ubah",
          key: "update",
          icon: <EditFilled />,
        },
      ]
    case "Open":
      return [
        {
          label: "Detail",
          key: "detail",
          icon: <EyeOutlined />,
        },
        {
          label: "Ubah",
          key: "update",
          icon: <EditFilled />,
        },
        // {
        //     label: "Approve",
        //     key: "approve",
        //     icon: <CheckOutlined />,
        // },
        {
          label: "Cancel",
          key: "cancel",
          icon: <CloseOutlined />,
        },
      ]

    case "Closed":
      return [
        {
          label: "Detail",
          key: "detail",
          icon: <EyeOutlined />,
        },
      ]
    default:
      return [
        {
          label: "Detail",
          key: "detail",
          icon: <EyeOutlined />,
        },
        {
          label: "Ubah",
          key: "update",
          icon: <EditFilled />,
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
            navigate(`/lead-master/detail/${value}`)
            break
          case "update":
            navigate(`/lead-master/form/${value}`)
            break
        }
      }}
      itemIcon={<RightOutlined />}
      items={getStatusItems(status)}
    />
  )
}

const leadMasterListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (value, row, index) => index + 1,
  },
  {
    title: "SO Number",
    dataIndex: "title",
    key: "title",
  },
  {
    title: "Contact",
    dataIndex: "contact",
    key: "contact",
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
    title: "Brand",
    dataIndex: "brand",
    key: "brand",
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
  {
    title: "Status",
    dataIndex: "status",
    key: "status",
  },
  {
    title: "Action",
    key: "id",
    fixed: "right",
    align: "center",
    width: 100,
    render: (text) => (
      <Dropdown.Button
        style={{ left: -16 }}
        // icon={<DownOutlined />}
        overlay={<ActionMenu value={text.id} status={text.status} />}
      ></Dropdown.Button>
    ),
  },
]

const columns = ["Product", "Price", "Qty", "Total Price", "Final Price"]
const productNeedListColumn = [...columns, "Discount", "Tax"].map((column) => {
  return {
    title: column,
    dataIndex: column.replace(/\s/g, "_").toLowerCase(),
    key: column.replace(/\s/g, "_").toLowerCase(),
  }
})

const productNeedListColumnStep2 = columns.map((column) => {
  return {
    title: column,
    dataIndex: column.replace(/\s/g, "_").toLowerCase(),
    key: column.replace(/\s/g, "_").toLowerCase(),
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
        return moment(text).format("DD-MM-YYYY")
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
        return moment(text).format("DD-MM-YYYY")
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
    title: "Attachment",
    dataIndex: "attachment_url",
    key: "attachment_url",
    render: (text) => {
      if (text) {
        return (
          <a href={text} target="_blank" rel="noreferrer">
            Lihat foto
          </a>
        )
      }
      return "-"
    },
  },
]

const negotiationsColumns = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (value, row, index) => index + 1,
  },
  {
    title: "Notes",
    dataIndex: "notes",
    key: "notes",
  },
  {
    title: "Status",
    dataIndex: "status",
    key: "status",
    render: (text) => {
      if (text === "2") {
        return <Tag color="error">Rejected</Tag>
      } else if (text === "1") {
        return <Tag color="success">Approved</Tag>
      } else {
        return <Tag color="success">Approval Proccess</Tag>
      }
    },
  },
  {
    title: "Date",
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
    width: 300,
  },
  {
    title: "Price",
    dataIndex: "price",
    key: "price",
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
  },
  {
    title: "Total Price Nego",
    dataIndex: "price_nego",
    key: "price_nego",
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
  },
  {
    title: "Action",
    dataIndex: "action",
    key: "action",
  },
]

export {
  leadMasterListColumn,
  productNeedListColumn,
  productNeedListColumnStep2,
  billingColumns,
  activityColumns,
  negotiationsColumns,
  productListColumns,
}
