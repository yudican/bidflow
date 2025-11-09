const roleListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Role Name",
    dataIndex: "role_name",
    key: "role_name",
  },
]

export { roleListColumn }
