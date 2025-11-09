import {
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

const purchaseOrderListColumn = [
  {
    title: "No.",
    dataIndex: "number",
    key: "number",
    // render: (value, row, index) => index + 1,
  },
  {
    title: "Barcode",
    dataIndex: "barcode",
    key: "barcode",
  },
  {
    title: "Item Name",
    dataIndex: "item_name",
    key: "item_name",
  },
  {
    title: "Brand Id",
    dataIndex: "brand_name",
    key: "brand_name",
  },
  {
    title: "Generate Date",
    dataIndex: "generate_date",
    key: "generate_date",
    render: (text) => {
      return formatDate(text)
    },
  },
  // {
  //     title: "Exp Date",
  //     dataIndex: "exp_date",
  //     key: "exp_date",
  //     render: (text) => {
  //       return formatDate(text)
  //     },
  // },
]


export {
  purchaseOrderListColumn,
  getStatusItems,
}
