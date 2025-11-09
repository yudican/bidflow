const warehouseListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => {
      return index + 1
    },
  },
  {
    title: "ID Warehouse",
    dataIndex: "wh_id",
    key: "wh_id",
    align: "center",
    render: (text, record, index) => {
      return text || "-"
    },
  },
  {
    title: "Nama",
    dataIndex: "name",
    key: "name",
  },
  {
    title: "Lokasi",
    dataIndex: "location",
    key: "location",
  },
  {
    title: "Alamat",
    dataIndex: "alamat",
    key: "alamat",
  },
]

export { warehouseListColumn }
