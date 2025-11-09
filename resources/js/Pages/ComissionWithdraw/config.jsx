import { formatDate, formatNumber } from "../../helpers"
const comissionWithdrawColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (value, row, index) => index + 1,
  },
  {
    title: "Date",
    dataIndex: "created_at",
    key: "created_at",
    render: (text) => {
      return formatDate(text)
    },
  },
  {
    title: "Nama",
    dataIndex: "user_name",
    key: "user_name",
  },
  {
    title: "Email",
    dataIndex: "email",
    key: "email",
  },
  {
    title: "No. HP",
    dataIndex: "phone",
    key: "phone",
  },
  {
    title: "Request by",
    dataIndex: "request_by_name",
    key: "request_by_name",
  },
  {
    title: "Tanggal Pengajuan",
    dataIndex: "created_at",
    key: "created_at",
    render: (text) => {
      return formatDate(text)
    },
  },
  {
    title: "Status",
    dataIndex: "status",
    key: "status",
  },
]

const comissionWithdrawApprovalColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (value, row, index) => index + 1,
  },
  {
    title: "Date",
    dataIndex: "created_at",
    key: "created_at",
    render: (text) => {
      return formatDate(text)
    },
  },
  {
    title: "Approved by",
    dataIndex: "approved_by_name",
    key: "approved_by_name",
  },
  // {
  //   title: "Role",
  //   dataIndex: "role",
  //   key: "role",
  // },
  {
    title: "Status",
    dataIndex: "status",
    key: "status",
    render: (text) => {
      return text > 1 ? "Rejected" : "Approved"
    },
  },
  {
    title: "Notes",
    dataIndex: "note",
    key: "note",
    render: (text) => {
      return text ? text : "-"
    },
  },
]

const comissionWithdrawDetailColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (value, row, index) => index + 1,
  },
  {
    title: "Nama Nutrisionis",
    dataIndex: "create_by_name",
    key: "create_by_name",
  },
  {
    title: "Nama Custommer",
    dataIndex: "user_name",
    key: "user_name",
  },
  {
    title: "Tanggal Transaksi",
    dataIndex: "updated_at",
    key: "updated_at",
    render: (text) => formatDate(text),
  },
  {
    title: "No. Invoice",
    dataIndex: "id_transaksi",
    key: "id_transaksi",
  },
  {
    title: "Jumlah Transaksi",
    dataIndex: "nominal",
    key: "nominal",
    render: (text, record) => {
      return formatNumber(text, "Rp ")
    },
  },
  {
    title: "DPP",
    dataIndex: "dpp",
    key: "dpp",
    render: (text, record) => {
      return formatNumber(record.bagi_hasil.dpp, "Rp ")
    },
  },
  {
    title: "PPN",
    dataIndex: "ppn",
    key: "ppn",
    render: (text, record) => {
      return formatNumber(record.bagi_hasil.ppn, "Rp ")
    },
  },
  {
    title: "Total Pembagian",
    dataIndex: "total_pembagian",
    key: "total_pembagian",
    render: (text, record) => {
      return formatNumber(record.bagi_hasil.total_pembagian, "Rp ")
    },
  },
  {
    title: "Akumulasi",
    dataIndex: "akumulasi",
    key: "akumulasi",
    render: (text, record) => {
      return formatNumber(text, "Rp ")
    },
  },
  {
    title: "Total Pembagian (IGN)",
    dataIndex: "ign",
    key: "ign",
    render: (text, record) => {
      return formatNumber(record.bagi_hasil.ign, "Rp ")
    },
  },
  {
    title: "PPH 21 POTONG",
    dataIndex: "pph21",
    key: "pph21",
    render: (text, record) => {
      return formatNumber(record.bagi_hasil.pph21, "Rp ")
    },
  },
  {
    title: "Total Pembayaran",
    dataIndex: "nutrisionist_amount",
    key: "nutrisionist_amount",
    render: (text, record) => {
      return formatNumber(record.bagi_hasil.nutrisionist_amount, "Rp ")
    },
  },
]

export {
  comissionWithdrawApprovalColumn,
  comissionWithdrawColumn,
  comissionWithdrawDetailColumn,
}
