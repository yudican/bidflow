import { Tag } from "antd"
import { useNavigate } from "react-router-dom"

const columns = [
  "status_submit",
  "user",
  "phone",
  "email",
  "trx_id",
  "channel",
  "store",
  "sku",
  "nama_produk",
  "harga_awal",
  "harga_promo",
  "qty",
  "nominal",
  "ongkir",
  "pajak",
  "asuransi",
  "total_diskon",
  "biaya_komisi",
  "biaya_layanan",
  "ongkir_dibayar_sistem",
  "potongan_harga",
  "subsidi_angkutan",
  "koin",
  "loin_cashback",
  "jumlah_pengambalian_dana",
  "voucher_channel",
  "diskon_penjual",
  "biaya_lacanan_kartu_kredit",
  "metode_pembayaran",
  "diskon",
  "tanggal_transaksi",
  "kurir",
  "resi",
  "status",
  "status_pengiriman",
  "address",
  "is_webhook",
]

const orderItemsColumn = [
  {
    title: "image",
    key: "productImage",
    dataIndex: "productImage",
    render: (text) => {
      if (!text) {
        return (
          <img
            src="https://i.ibb.co/vqqy3pr/404.jpg"
            alt="404"
            border="0"
            style={{ height: 50 }}
          />
        )
      }
      return <img src={text} alt="404" border="0" style={{ height: 50 }} />
    },
  },
  {
    title: "Product Name",
    key: "productName",
    dataIndex: "productName",
  },
  {
    title: "variasi",
    key: "variationName",
    dataIndex: "variationName",
  },
  {
    title: "quantity",
    key: "quantity",
    dataIndex: "quantity",
  },
  {
    title: "sku",
    key: "sku",
    dataIndex: "sku",
  },
  {
    title: " Price",
    key: "actualPrice",
    dataIndex: "actualPrice",
  },
  {
    title: "Subtotal",
    key: "actualTotalPrice",
    dataIndex: "actualTotalPrice",
  },
]

const newColumns = columns.map((column) => {
  if (column === "loin_cashback") {
    return {
      title: "Koin Cashback",
      dataIndex: column,
      key: column,
    }
  }
  if (column === "trx_id") {
    return {
      title: "TRX ID",
      dataIndex: column,
      key: column,
    }
  }
  if (column === "user") {
    return {
      title: "Nama",
      dataIndex: column,
      key: column,
    }
  }
  if (column === "phone") {
    return {
      title: "Nomor Hp",
      dataIndex: column,
      key: column,
    }
  }
  if (column === "is_webhook") {
    return {
      title: "Source",
      dataIndex: column,
      key: column,
      render: (text) => {
        if (text > 0) {
          return <Tag color="green">Auto</Tag>
        }
        return <Tag color="yellow">Manual</Tag>
      },
    }
  }
  return {
    title: column.replace(/_/g, " ").toUpperCase(),
    dataIndex: column,
    key: column,
  }
})

const orderListColumn = [
  {
    title: "No.",
    key: "id",
    fixed: "left",
    render: (text, record, index) => index + 1,
  },
  ...newColumns,
  {
    title: "Action",
    key: "id",
    fixed: "right",
    width: 100,
    render: (text) => <ActionButton value={text} />,
  },
]

const ActionButton = ({ value }) => {
  const navigate = useNavigate()
  return (
    <button
      onClick={() => navigate(`/genie/order/detail/${value.trx_id}`)}
      className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
    >
      Detail
    </button>
  )
}

const channelId = [
  "SHOPEE_ID",
  "LAZADA_ID",
  "TOKOPEDIA_ID",
  "BUKALAPAK_ID",
  "BLIBLI_ID",
  "JD_ID",
  "JD_V2",
  "SHOPIFY_ID",
  "WOO_COMMERCE_ID",
  "GENIE_MASTER",
]

const orderStatus = [
  {
    label: "ALL",
    value: "All orders",
  },
  {
    label: "PENDING_PAYMENT",
    value: "Unpaid order",
  },
  {
    label: "PARTIALLY_PAID",
    value: "Partially paid order",
  },
  {
    label: "PAID",
    value: "Totally paid order",
  },
  {
    label: "READY_TO_SHIP",
    value: "Ready to ship order",
  },
  {
    label: "SHIPPING",
    value: "Shipping order",
  },
  {
    label: "DELIVERED",
    value: "Delivered order",
  },
  {
    label: "CANCELLED",
    value: "Cancelled order",
  },
  {
    label: "RETURNED",
    value: "Returned order",
  },
]

export { orderListColumn, orderItemsColumn, channelId, orderStatus, newColumns }
