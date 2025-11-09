const sourceCaseListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Source Name",
    dataIndex: "source_name",
    key: "source_name",
  },
  {
    title: "Type",
    dataIndex: "type",
    key: "type",
  },
];

export { sourceCaseListColumn };
