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
            navigate(`/ticket/detail/${value}`)
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

const ticketListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Ticket No",
    dataIndex: "ticket_number",
    key: "ticket_number",
  },
  {
    title: "Customer Name",
    dataIndex: "customer_name",
    key: "customer_name",
  },
  {
    title: "Assign to",
    dataIndex: "agent_name",
    key: "agent_name",
  },
  {
    title: "Assign Date",
    dataIndex: "assign_date",
    key: "assign_date",
    render: (text, record) => {
      if (text) {
        return formatDate(text)
      }
      return "-"
    },
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

export { ticketListColumn }
