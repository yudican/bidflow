import React from "react"
import { truncateString } from "../../../helpers"

const productCommentRatingColumn = [
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
    render: (text, record, index) => {
      return (
        <div className="flex justify-start items-center">
          <img
            // src={record.product_image}
            src={record.product_variant.image_url}
            alt="product_image"
            width="30"
            height="30"
          />
          <p className="mb-0 ml-3">{truncateString(record.product_name, 50)}</p>
        </div>
      )
    },
  },
  {
    title: "User",
    dataIndex: "user_name",
    key: "user_name",
  },
  {
    title: "Transaction",
    dataIndex: "invoice_id",
    key: "invoice_id",
  },
  {
    title: "Comment",
    dataIndex: "comment",
    key: "comment",
    render: (text, record, index) => {
      return truncateString(text || "-", 50)
    },
  },
  {
    title: "Rate",
    dataIndex: "rate",
    key: "rate",
  },
]
export { productCommentRatingColumn }
