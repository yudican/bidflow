import { Tag } from "antd"
const productConvertListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "User",
    dataIndex: "user",
    key: "user",
  },
  {
    title: "Convert Date",
    dataIndex: "convert_date",
    key: "convert_date",
  },
]

const productConvertDetailListColumn = [
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
    title: "Produk Nama",
    dataIndex: "produk_nama",
    key: "produk_nama",
  },
  {
    title: "Harga Awal",
    dataIndex: "harga_awal",
    key: "harga_awal",
  },
  {
    title: "Harga Promo",
    dataIndex: "harga_promo",
    key: "harga_promo",
  },
  {
    title: "Qty",
    dataIndex: "qty",
    key: "qty",
  },
  {
    title: "Ongkir",
    dataIndex: "ongkir",
    key: "ongkir",
  },
  {
    title: "Diskon",
    dataIndex: "diskon",
    key: "diskon",
  },
  {
    title: "Status",
    dataIndex: "status_convert",
    key: "status_convert",
    render: (text, record, index) => {
      if (text == 1) {
        return <Tag color="green">Success</Tag>
      } else if (text === "failed") {
        return <Tag color="red">Failed</Tag>
      } else {
        return <Tag color="blue">Pending</Tag>
      }
    },
  },
  // {
  //   title: "Tanggal Transaksi",
  //   dataIndex: "tanggal_transaksi",
  //   key: "tanggal_transaksi",
  // },
]

export { productConvertListColumn, productConvertDetailListColumn }
