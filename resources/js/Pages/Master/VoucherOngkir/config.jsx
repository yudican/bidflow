const voucherListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Kode Voucher",
    dataIndex: "title",
    key: "title",
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
];

export { voucherListColumn };
