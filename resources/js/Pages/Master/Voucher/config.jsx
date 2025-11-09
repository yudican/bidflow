const voucherListColumn = [
  {
    title: "No.",
    dataIndex: "number",
    key: "number",
  },
  {
    title: "Kode Voucher",
    dataIndex: "voucher_code",
    key: "voucher_code",
  },
  {
    title: "Judul",
    dataIndex: "title",
    key: "title",
  },
  {
    title: "Jumlah Voucher",
    dataIndex: "total",
    key: "total",
  },
  {
    title: "Sisa Voucher",
    dataIndex: "voucher_limit",
    key: "voucher_limit",
  },
  {
    title: "Brand",
    dataIndex: "brand_name",
    key: "brand_name",
  },
  {
    title: "Image",
    dataIndex: "voucher_image",
    key: "voucher_image",
    render: (text, record, index) => (
      <img
        src={record.voucher_image}
        alt="voucher_image"
        width="100"
        height="100"
      />
    ),
  },
]

export { voucherListColumn }
