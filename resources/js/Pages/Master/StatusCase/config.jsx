const statusCaseListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Status Name",
    dataIndex: "status_name",
    key: "status_name",
  },
];

export { statusCaseListColumn };
