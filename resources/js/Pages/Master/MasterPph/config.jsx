import { formatNumber } from "../../../helpers"

const packageListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Title",
    dataIndex: "pph_title",
    key: "pph_title",
  },
  {
    title: "PPH Nominal",
    dataIndex: "pph_amount",
    key: "pph_amount",
    render: (text, record, index) => formatNumber(text, "Rp "),
  },
  {
    title: "PPH Percentage (%)",
    dataIndex: "pph_percentage",
    key: "pph_percentage",
  },
]

export { packageListColumn }
