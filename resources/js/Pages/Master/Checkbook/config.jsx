const checkbookListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Nama Bank",
    dataIndex: "bank_name",
    key: "bank_name",
  },
  {
    title: "Description",
    dataIndex: "description",
    key: "description",
  },
  {
    title: "Address",
    dataIndex: "company_address",
    key: "company_address",
  },
  {
    title: "Nomor Rekening",
    dataIndex: "bank_account",
    key: "bank_account",
  },
  {
    title: "Currency",
    dataIndex: "currency_id",
    key: "currency_id",
  },
  {
    title: "Status",
    dataIndex: "status",
    key: "status",
    render: (text) => {
      if (text == "InActive") {
        return "Non Active"
      }
      return text
    },
  },
]

export { checkbookListColumn }
