const orderListGpColumn = [
  {
    title: "No.",
    key: "id",
    dataIndex: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Submit Date",
    key: "create_date",
    dataIndex: "create_date",
  },
  {
    title: "Submit by",
    key: "submit_by_name",
    dataIndex: "submit_by_name",
  },
  {
    title: "Success",
    key: "total_success",
    dataIndex: "total_success",
  },
  {
    title: " Error",
    key: "total_failed",
    dataIndex: "total_failed",
  },
]

const orderListDetailGpColumn = [
  {
    title: "No.",
    key: "id",
    dataIndex: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Document Number",
    key: "so_number",
    dataIndex: "so_number",
  },
  {
    title: "Document Date",
    key: "tanggal_transaksi",
    dataIndex: "tanggal_transaksi",
  },
  {
    title: "Customer ID",
    key: "custommer_id",
    dataIndex: "custommer_id",
  },
  {
    title: "Customer Name",
    key: "channel",
    dataIndex: "channel",
  },
  {
    title: "Item Number",
    key: "sku",
    dataIndex: "sku",
  },
  {
    title: "Item Description",
    key: "nama_produk",
    dataIndex: "nama_produk",
  },
  {
    title: "U of M",
    key: "u_of_m",
    dataIndex: "u_of_m",
  },
  {
    title: "Quantity",
    key: "qty_total",
    dataIndex: "qty_total",
  },
  {
    title: "Extended Price",
    key: "extended_price",
    dataIndex: "extended_price",
  },
  {
    title: "Freight Amount",
    key: "freight_amount",
    dataIndex: "freight_amount",
  },
  {
    title: "Miscellaneous",
    key: "miscellaneous",
    dataIndex: "miscellaneous",
  },
  {
    title: "Tax Amount",
    key: "tax_amount",
    dataIndex: "tax_amount",
  },
  {
    title: "Trade Discount",
    key: "trade_discount",
    dataIndex: "trade_discount",
  },
]

export { orderListGpColumn, orderListDetailGpColumn }
