import { formatPhone, inArray } from "../../../helpers"

const brandListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Name",
    dataIndex: "name",
    key: "name",
  },
  {
    title: "Email",
    dataIndex: "email",
    key: "email",
  },
  {
    title: "Telepon",
    dataIndex: "phone",
    key: "phone",
    render: (text) => formatPhone(text),
  },
]

const custumerListColumn = [
  {
    title: "Type",
    dataIndex: "type",
    key: "type",
  },
  {
    title: "Value",
    dataIndex: "value",
    key: "value",
    render: (text, record) => {
      if (inArray(record.type, ["telepon", "whatsapp"])) {
        return formatPhone(text)
      }

      return text
    },
  },
  {
    title: "Status",
    dataIndex: "status",
    key: "status",
  },
  {
    title: "Action",
    dataIndex: "action",
    key: "action",
  },
]

export { brandListColumn, custumerListColumn }
