const gpCustomerColumns = [
  {
    title: "#",
    key: "id",
    dataIndex: "id",
    render: (text, record, index) => {
      return index + 1;
    },
  },
  {
    title: "Customer ID",
    key: "customer_id",
    dataIndex: "customer_id",
  },
  {
    title: "Customer Name",
    key: "customer_name",
    dataIndex: "customer_name",
  },
];

export { gpCustomerColumns };
