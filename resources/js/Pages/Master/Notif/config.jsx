const notifListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Notif Alert",
    dataIndex: "name",
    key: "name",
  },
];

export { notifListColumn };
