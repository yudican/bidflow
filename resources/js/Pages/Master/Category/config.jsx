const categoryListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Category Name",
    dataIndex: "name",
    key: "name",
  },
  {
    title: "Slug",
    dataIndex: "slug",
    key: "slug",
  },
];

export { categoryListColumn };
