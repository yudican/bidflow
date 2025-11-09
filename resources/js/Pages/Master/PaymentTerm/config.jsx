import { Badge } from "antd"
import React from "react"

const packageListColumn = [
  // {
  //   title: "No.",
  //   dataIndex: "id",
  //   key: "id",
  //   render: (text, record, index) => index + 1,
  // },
  {
    title: "No.",
    dataIndex: "number",
    key: "number",
  },
  {
    title: "Name",
    dataIndex: "name",
    key: "name",
  },
  {
    title: "Days Of",
    dataIndex: "days_of",
    key: "days_of",
  },
  {
    title: "Status Sync",
    dataIndex: "status_gp",
    key: "status_gp",
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
