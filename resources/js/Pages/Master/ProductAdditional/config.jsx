const productListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Name",
    dataIndex: "name",
    key: "name",
  },
  {
    title: "SKU",
    dataIndex: "sku",
    key: "sku",
  },
]

export { productListColumn }
