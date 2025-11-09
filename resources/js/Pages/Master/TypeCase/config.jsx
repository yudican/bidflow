const typeCaseListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Type Name",
    dataIndex: "type_name",
    key: "type_name",
  },
  {
    title: "Code",
    dataIndex: "code",
    key: "code",
  },
];

export { typeCaseListColumn };
