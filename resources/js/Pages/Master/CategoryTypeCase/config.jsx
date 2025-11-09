const typeCaseListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Type Name",
    dataIndex: "type_case_name",
    key: "type_case_name",
  },
  {
    title: "Category Name",
    dataIndex: "category_name",
    key: "category_name",
  },
];

export { typeCaseListColumn };
