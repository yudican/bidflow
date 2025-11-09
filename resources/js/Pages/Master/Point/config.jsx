const pointListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Tipe",
    dataIndex: "type",
    key: "type",
    render: (text) => {
      if (text == "product") {
        return "Per Product"
      } else if (text == "transaction") {
        return "Per Transaction"
      } else if (text == "referral") {
        return "Referral"
      } else if (text == "barcode") {
        return "QR Code"
      }
      return text
    },
  },
  {
    title: "Poin",
    dataIndex: "point",
    key: "point",
  },
  {
    title: "Nama Produk",
    dataIndex: "product",
    key: "product_name",
    render: (product) => product?.name ?? "-",
  },
  {
    title: "Minimal Transaksi",
    dataIndex: "min_trans",
    key: "min_trans",
  },
  {
    title: "Maksimal Transaksi",
    dataIndex: "max_trans",
    key: "max_trans",
  },
  {
    title: "Brand",
    dataIndex: "brand_name",
    key: "brand_name",
  },
]

export { pointListColumn }
