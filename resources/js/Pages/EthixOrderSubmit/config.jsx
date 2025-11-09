import { formatDate } from "../../helpers"

const orderSubmitColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (value, row, index) => index + 1,
  },
  {
    title: "Submited by",
    dataIndex: "submited_by_name",
    key: "submited_by_name",
  },
  {
    title: "Type SI",
    dataIndex: "type_si",
    key: "type_si",
  },
  {
    title: "Success",
    dataIndex: "success",
    key: "success",
  },
  {
    title: "Failed",
    dataIndex: "failed",
    key: "failed",
  },
  {
    title: "Submited On",
    dataIndex: "created_at",
    key: "created_at",
    render: (text) => formatDate(text),
  },
]

const orderSubmitDetailColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (value, row, index) => index + 1,
  },
  {
    title: "SI Number",
    dataIndex: "si_number",
    key: "si_number",
  },
  {
    title: "Extended Price",
    dataIndex: "extended_price",
    key: "extended_price",
  },
  {
    title: "Discount",
    dataIndex: "discount_amount",
    key: "discount_amount",
  },
  {
    title: "Tax",
    dataIndex: "tax_amount",
    key: "tax_amount",
  },
  {
    title: "Miscellaneous",
    dataIndex: "misc_amount",
    key: "misc_amount",
  },
  {
    title: "Freight Amount",
    dataIndex: "freight",
    key: "freight",
  },
]

export { orderSubmitColumn, orderSubmitDetailColumn }
