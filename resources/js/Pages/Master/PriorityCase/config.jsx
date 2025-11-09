const priorityCaseListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Priority Name",
    dataIndex: "priority_name",
    key: "priority_name",
  },
  {
    title: "Priority Day",
    dataIndex: "priority_day",
    key: "priority_day",
  },
];

export { priorityCaseListColumn };
