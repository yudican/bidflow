const returnListColumn = [
  {
    title: "No.",
    dataIndex: "uid_retur",
    key: "uid_retur",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Name",
    dataIndex: "name",
    key: "name",
  },
  {
    title: "Email",
    dataIndex: "email",
    key: "email",
  },
  {
    title: "Telepon",
    dataIndex: "handphone",
    key: "handphone",
  },
  {
    title: "Custommer Type",
    dataIndex: "type",
    key: "type",
  },
  {
    title: "Status",
    dataIndex: "status_return",
    key: "status_return",
  },
  {
    title: "Created On",
    dataIndex: "created_at",
    key: "created_at",
    render: (text) => {
      return moment(text).format("DD MMM YYYY")
    },
  },
]

const returnListItemColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Product",
    dataIndex: "product_name",
    key: "product_name",
  },
  {
    title: "Qty",
    dataIndex: "qty",
    key: "qty",
  },
  {
    title: "Attachment",
    dataIndex: "product_photo",
    key: "product_photo",
    render: (text) => {
      return (
        <a href={text} target="_blank">
          Download Attachment
        </a>
      )
    },
  },
]

const returnListResiColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Ekspedisi",
    dataIndex: "expedition_name",
    key: "expedition_name",
  },
  {
    title: "Resi",
    dataIndex: "resi",
    key: "resi",
  },
  {
    title: "Sender Name",
    dataIndex: "sender_name",
    key: "sender_name",
  },
  {
    title: "Sender Phone",
    dataIndex: "sender_phone",
    key: "sender_phone",
  },
]

export { returnListColumn, returnListItemColumn, returnListResiColumn }
