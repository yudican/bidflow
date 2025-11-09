import { Tag } from "antd"
import { truncateString } from "../../../helpers"

const productImportListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Status",
    dataIndex: "status",
    key: "status",
    render: (text, record, index) => {
      if (text === "success") {
        return <Tag color="green">Success</Tag>
      } else if (text === "failed") {
        return <Tag color="red">Failed</Tag>
      } else {
        return <Tag color="blue">Pending</Tag>
      }
    },
  },
  {
    title: "TRX ID",
    dataIndex: "trx_id",
    key: "trx_id",
  },
  {
    title: "Store",
    dataIndex: "toko",
    key: "toko",
  },
  {
    title: "SKU",
    dataIndex: "sku",
    key: "sku",
  },
  {
    title: "Nama Produk",
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
    title: "Tanggal Transaksi",
    dataIndex: "tanggal_transaksi",
    key: "tanggal_transaksi",
  },
  {
    title: "Kurir",
    dataIndex: "kurir",
    key: "kurir",
  },
  {
    title: "Metode Pembayaran",
    dataIndex: "metode_pembayaran",
    key: "metode_pembayaran",
  },
  {
    title: "Resi",
    dataIndex: "resi",
    key: "resi",
  },
]

export { productImportListColumn }
