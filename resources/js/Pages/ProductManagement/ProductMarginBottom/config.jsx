import { truncateString } from "../../../helpers"

const productMarginListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Product",
    dataIndex: "product_image",
    key: "product_image",
    render: (text, record, index) => (
      <div className="flex justify-start items-center">
        <img
          src={record.product_image}
          alt="product_image"
          width="30"
          height="30"
        />
        <p className="mb-0 ml-3">{truncateString(record.product_name, 50)}</p>
      </div>
    ),
  },
  {
    title: "Role",
    dataIndex: "role_name",
    key: "role_name",
  },
  {
    title: "Final Price",
    dataIndex: "final_price",
    key: "final_price",
  },
  {
    title: "Margin Price",
    dataIndex: "margin",
    key: "margin",
  },
]

export { productMarginListColumn }
