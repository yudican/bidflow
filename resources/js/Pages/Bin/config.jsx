import React from "react"
import { Tag, Tooltip } from "antd"
import { truncateString, formatNumber } from "../../helpers"

const binListColumn = [
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
    render: (text, record) => {
      return (
        <Tooltip overlayStyle={{ maxWidth: 800 }} title={record.name}>
          <div className="flex justify-start items-center">
            <img
              src={record.product_image}
              alt="product_image"
              width="30"
              height="30"
            />
            <p className="mb-0 ml-3">{truncateString(record.name, 50)}</p>
          </div>
        </Tooltip>
      )
    },
  },
  {
    title: "Package",
    dataIndex: "package_name",
    key: "package_name",
    sorter: (a, b) => a.package_name - b.package_name,
  },

  {
    title: "⁠Stock Aktual",
    dataIndex: "stocks",
    key: "stocks",
    sorter: (a, b) => a.stocks - b.stocks,
    // render: (text, record) => {
    //   if (record?.stock_bins?.length > 0) {
    //     return (
    //       <Tooltip
    //         overlayStyle={{ maxWidth: 800 }}
    //         title={
    //           <div>
    //             <p className="mb-2">Stock BIN:</p>
    //             {record?.stock_bins?.map((item, index) => {
    //               if (item.stock > 0) {
    //                 return (
    //                   <div key={index}>
    //                     <p className="mb-0">
    //                       {item.bin_name} - {item.stock}
    //                     </p>
    //                   </div>
    //                 )
    //               }

    //               return null
    //             })}
    //           </div>
    //         }
    //       >
    //         <span>{text}</span>
    //       </Tooltip>
    //     )
    //   }
    //   return text
    // },
  },
  {
    title: "Stock by Order",
    dataIndex: "realstocks",
    key: "realstocks",
    sorter: (a, b) => a.stocks - b.stocks,
    // render: (text, record) => {
    //   if (record?.stock_bins?.length > 0) {
    //     return (
    //       <Tooltip
    //         overlayStyle={{ maxWidth: 800 }}
    //         title={
    //           <div>
    //             <p className="mb-2">Stock BIN:</p>
    //             {record?.stock_bins?.map((item, index) => {
    //               if (item.stock > 0) {
    //                 return (
    //                   <div key={index}>
    //                     <p className="mb-0">
    //                       {item.bin_name} - {item.stock}
    //                     </p>
    //                   </div>
    //                 )
    //               }

    //               return null
    //             })}
    //           </div>
    //         }
    //       >
    //         <span>{text}</span>
    //       </Tooltip>
    //     )
    //   }
    //   return text
    // },
  },

  {
    title: "Sales Channel",
    dataIndex: "sales_channel",
    key: "sales_channel",
    sorter: (a, b) => a.sales_channel - b.sales_channel,
    render: (text, record, index) => {
      if (text) {
        return record.sales_channels.map((item) => (
          <Tag key={item} color="green">
            {item.replace("-", " ")}
          </Tag>
        ))
      }

      return "-"
    },
  },
  {
    title: "Variant",
    dataIndex: "variant_name",
    key: "variant_name",
    sorter: (a, b) => a.variant_name - b.variant_name,
  },
  {
    title: "Final Price (B2B)",
    dataIndex: "final_price",
    key: "final_price",
    sorter: (a, b) => a.final_price - b.final_price,
  },
]

const binDetailListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Sku",
    dataIndex: "sku",
    key: "sku",
  },
  {
    title: "Order Number",
    dataIndex: "order_number",
    key: "order_number",
  },
  {
    title: "Delivery Number",
    dataIndex: "invoice_number",
    key: "invoice_number",
  },
  {
    title: "Qty",
    dataIndex: "qty",
    key: "qty",
  },
  {
    title: "Price Nego",
    dataIndex: "price_nego",
    key: "price_nego",
    render: (text) => formatNumber(text, "Rp"),
  },
]

export { binListColumn, binDetailListColumn }
