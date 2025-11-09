const returnListColumn = [
  {
    title: "No.",
    dataIndex: "uid_retur",
    key: "uid_retur",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Title",
    dataIndex: "title",
    key: "title",
  },
  {
    title: "Contact",
    dataIndex: "contact_name",
    key: "contact_name",
  },
  {
    title: "Type Case",
    dataIndex: "type_name",
    key: "type_name",
  },
  {
    title: "Category",
    dataIndex: "category_name",
    key: "category_name",
  },
  {
    title: "Priority",
    dataIndex: "priority_name",
    key: "priority_name",
  },
  {
    title: "Source",
    dataIndex: "source_name",
    key: "source_name",
  },
  {
    title: "Status",
    dataIndex: "status_name",
    key: "status_name",
  },
  {
    title: "Created by",
    dataIndex: "created_by_name",
    key: "created_by_name",
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

const productListColumns = [
  {
    title: "Product",
    dataIndex: "product_id",
    key: "product_id",
    width: 300,
  },

  {
    title: "Qty",
    dataIndex: "qty",
    key: "qty",
  },
  {
    title: "SKU",
    dataIndex: "sku",
    key: "sku",
  },
  {
    title: "UofM",
    dataIndex: "u_of_m",
    key: "u_of_m",
  },
  {
    title: "Action",
    dataIndex: "action",
    key: "action",
  },
]

export {
  returnListColumn,
  returnListItemColumn,
  returnListResiColumn,
  productListColumns,
}
