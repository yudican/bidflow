const salesChannelListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Channel Name",
    dataIndex: "channel_name",
    key: "channel_name",
  },
  {
    title: "Warehouse",
    dataIndex: "warehouse_name",
    key: "warehouse_name",
  },
]

export { salesChannelListColumn }
