import { formatDate } from "../../../helpers"

const skuListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "SKU",
    dataIndex: "sku",
    key: "sku",
  },
  {
    title: "Package",
    dataIndex: "package_name",
    key: "package_name",
  },
  {
    title: "Expired SO",
    dataIndex: "expired_at",
    key: "expired_at",
    render: (text, record) => {
      if (text) {
        return formatDate(text)
      }
      return "-"
    },
  },
]

export { skuListColumn }
