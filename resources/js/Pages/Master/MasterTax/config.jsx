import { Badge } from "antd"
import React from "react"

const packageListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Tax Code",
    dataIndex: "tax_code",
    key: "tax_code",
  },
  {
    title: "Tax Percentage",
    dataIndex: "tax_percentage",
    key: "tax_percentage",
  },
  {
    title: "Status Sync",
    dataIndex: "gp_status",
    key: "gp_status",
    render: (text) =>
      text > 0 ? (
        <Badge
          style={{
            backgroundColor: "#52c41a",
          }}
          count={"sync"}
        />
      ) : (
        <Badge count={"not sync"} />
      ),
  },
]

export { packageListColumn }
