import React from "react"
const paymentMethodListColumn = [
  {
    title: "No.",
    dataIndex: "no",
    key: "no",
    // dataIndex: "id",
    // key: "id",
    // render: (text, record, index) => index + 1,
  },
  {
    title: "Nama Pembayaran",
    dataIndex: "nama_bank",
    key: "nama_bank",
  },
  {
    title: "Tipe Pembayaran",
    dataIndex: "payment_type",
    key: "payment_type",
  },
  {
    title: "Channel Pembayaran",
    dataIndex: "payment_channel",
    key: "payment_channel",
    render: (text) => {
      // function to capital each work and remove _ to space
      return (
        text &&
        text
          .replace(/_/g, " ") // Replace underscores with spaces
          .replace(/Undefined/i, "")
          .replace(/\b\w/g, (char) => char.toUpperCase())
      ) // Capitalize the first letter
    },
  },
  {
    title: "Logo Bank",
    dataIndex: "logo",
    key: "logo",
    render: (text, record) => (
      <img src={record.logo} alt="logo" width="100" height="100" />
    ),
  },
]

export { paymentMethodListColumn }
