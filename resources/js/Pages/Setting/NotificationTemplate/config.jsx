import React from "react"
import { Tag } from "antd"

const notificationTemplateListColumn = [
  {
    title: "No.",
    dataIndex: "number",
    key: "number",
  },
  {
    title: "Kode Notifikasi",
    dataIndex: "notification_code",
    key: "notification_code",
  },
  {
    title: "Judul Notifikasi",
    dataIndex: "notification_title",
    key: "notification_title",
  },
  {
    title: "Tipe Notifikasi",
    dataIndex: "notification_type",
    key: "notification_type",
    render: (text, record) => {
      return text ? text.replace("amail", "email").replace("-", " & ") : " -"
    },
  },
  {
    title: "Role",
    dataIndex: "role_name",
    key: "role_name",
    render: (text, record) => {
      return (
        <div>
          {record.role_name?.map((item, index) => (
            <Tag color="blue" key={index}>
              {item}
            </Tag>
          ))}
        </div>
      )
    },
  },
]

const notificationTemplateGroupListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "ID Grup",
    dataIndex: "notification_code",
    key: "notification_code",
  },
  {
    title: "Nama Grup",
    dataIndex: "notification_title",
    key: "notification_title",
  },
  {
    title: "Total Notifikasi",
    dataIndex: "total",
    key: "total",
  },
]

export { notificationTemplateListColumn, notificationTemplateGroupListColumn }
