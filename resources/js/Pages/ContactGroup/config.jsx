import { Badge } from "antd"
import React from "react"
import { formatDate } from "../../helpers"

const contactGroupListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Group Code",
    dataIndex: "code",
    key: "code",
  },
  {
    title: "Group Name",
    dataIndex: "name",
    key: "name",
  },
  {
    title: "Created By",
    dataIndex: "created_by_name",
    key: "created_by_name",
  },
  {
    title: "Created On",
    dataIndex: "created_at",
    key: "created_at",
    render: (text) => formatDate(text, "DD-MM-YYYY"),
  },
]

const contactAddressListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Name",
    dataIndex: "nama",
    key: "nama",
  },
  {
    title: "Telepon",
    dataIndex: "telepon",
    key: "telepon",
  },
  {
    title: "Alamat",
    dataIndex: "alamat",
    key: "alamat",
    width: 500,
  },
]

export { contactGroupListColumn, contactAddressListColumn }
