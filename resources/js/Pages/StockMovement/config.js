const purchaseOrderListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Product Name",
    dataIndex: "product_name",
    key: "product_name",
    sorter: (a, b) => a.product_name - b.product_name,
  },
  {
    title: "SKU",
    dataIndex: "sku",
    key: "sku",
    sorter: (a, b) => a.sku - b.sku,
  },
  // {
  //   title: "UoM",
  //   dataIndex: "uom",
  //   key: "uom",
  //   align: "center",
  //   sorter: (a, b) => a.uom - b.uom,
  // },
  {
    title: "Begin Stock",
    dataIndex: "begin_stock",
    key: "begin_stock",
    sorter: (a, b) => a.begin_stock - b.begin_stock,
  },
  {
    title: "In. Purchase Receiving",
    dataIndex: "in_purchase_order",
    key: "in_purchase_order",
    sorter: (a, b) => a.in_purchase_order - b.in_purchase_order,
  },
  // {
  //   title: "In. Product Return",
  //   dataIndex: "qty_product_return",
  //   key: "qty_product_return",
  // },
  {
    title: "In. Sales Return",
    dataIndex: "in_sales_return",
    key: "in_sales_return",
    sorter: (a, b) => a.in_sales_return - b.in_sales_return,
  },
  {
    title: "In. Transfer In",
    dataIndex: "in_transfer",
    key: "in_transfer",
    sorter: (a, b) => a.in_transfer - b.in_transfer,
  },
  // {
  //   title: "In. Transfer BIN",
  //   dataIndex: "qty_product_transfer_in_bin",
  //   key: "qty_product_transfer_in_bin",
  //   sorter: (a, b) =>
  //     a.qty_product_transfer_in_bin - b.qty_product_transfer_in_bin,
  // },
  // {
  //   title: "Out. Stock Order",
  //   dataIndex: "qty_stock",
  //   key: "qty_stock",
  //   sorter: (a, b) => a.qty_stock - b.qty_stock,
  // },
  {
    title: "Out. Return To Suplier",
    dataIndex: "out_return_suplier",
    key: "out_return_suplier",
    sorter: (a, b) => a.out_return_suplier - b.out_return_suplier,
  },
  {
    title: "Out. Sales Order",
    dataIndex: "out_sales_order",
    key: "out_sales_order",
    sorter: (a, b) => a.out_sales_order - b.out_sales_order,
  },
  {
    title: "Out. Transfer Out",
    dataIndex: "out_transfer",
    key: "out_transfer",
    sorter: (a, b) => a.out_transfer - b.out_transfer,
  },
  // {
  //   title: "End Stock",
  //   dataIndex: "qty_end_stock",
  //   key: "qty_end_stock",
  //   sorter: (a, b) => a.qty_end_stock - b.qty_end_stock,
  // },
  // {
  //   title: "End Forecast",
  //   dataIndex: "qty_end_forecast",
  //   key: "qty_end_forecast",
  //   sorter: (a, b) => a.qty_end_forecast - b.qty_end_forecast,
  // },
]

export { purchaseOrderListColumn }
