import React from "react"
import { Tag, Tooltip } from "antd"

const urlShortenerListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Original URL",
    dataIndex: "original_url",
    key: "original_url",
    render: (text) => (
      <Tooltip title={text}>
        <span
          style={{
            maxWidth: 200,
            display: "block",
            overflow: "hidden",
            textOverflow: "ellipsis",
            whiteSpace: "nowrap",
          }}
        >
          {text}
        </span>
      </Tooltip>
    ),
  },
  {
    title: "Short Code",
    dataIndex: "short_code",
    key: "short_code",
    render: (text) => <Tag color="blue">{text}</Tag>,
  },
  {
    title: "Title",
    dataIndex: "title",
    key: "title",
    render: (text) => text || "-",
  },
  {
    title: "Click Count",
    dataIndex: "click_count",
    key: "click_count",
    render: (text) => <Tag color="green">{text || 0}</Tag>,
  },
  {
    title: "Status",
    dataIndex: "status",
    key: "status",
    render: (text) => (
      <Tag color={text == 1 ? "green" : "red"}>
        {text === 1 ? "Aktif" : "Nonaktif"}
      </Tag>
    ),
  },
  {
    title: "Created At",
    dataIndex: "created_at",
    key: "created_at",
    render: (text) => {
      if (!text) return "-"
      const date = new Date(text)
      return date.toLocaleDateString("id-ID", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
      })
    },
  },
]

export { urlShortenerListColumn }
