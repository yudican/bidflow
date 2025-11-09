import { formatPhone } from "../../../helpers"

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
    render: (text, record) => {
      return formatPhone(record?.phone)
    },
  },
]

export { brandListColumn }
