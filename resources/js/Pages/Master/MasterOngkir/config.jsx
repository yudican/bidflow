import { formatDate, formatNumber } from "../../../helpers"

const ongkirListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Nama Ongkir",
    dataIndex: "nama_ogkir",
    key: "nama_ogkir",
  },
  {
    title: "Kode Ongkir",
    dataIndex: "kode_ongkir",
    key: "kode_ongkir",
  },
  {
    title: "Harga",
    dataIndex: "harga_ongkir",
    key: "harga_ongkir",
    render: (text) => formatNumber(text, "Rp. "),
  },

  {
    title: "Start Date",
    dataIndex: "start_date",
    key: "start_date",
    render: (text) => formatDate(text),
  },
  {
    title: "End Date",
    dataIndex: "end_date",
    key: "end_date",
    render: (text) => formatDate(text),
  },
]

export { ongkirListColumn }
