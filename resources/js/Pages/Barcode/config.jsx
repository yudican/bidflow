import { Tag, Tooltip } from "antd"
import { truncateString } from "../../helpers"

const binListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Parent Barcode",
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
    title: "Child Barcode",
    dataIndex: "child_barcode",
    key: "child_barcode",
  },
  {
    title: "Type Product",
    dataIndex: "type_product",
    key: "type_product",
  },
  {
    title: "Created Date",
    dataIndex: "created_date",
    key: "created_date",
  },
  {
    title: "Created by",
    dataIndex: "created_by_name",
    key: "created_by_name",
  },
  {
    title: "Batch",
    dataIndex: "batch",
    key: "batch",
  },
  {
    title: "Keterangan",
    dataIndex: "note",
    key: "note",
  },
  // {
  //   title: "Stock",
  //   dataIndex: "stocks",
  //   key: "stocks",
  //   render: (text, record) => {
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
  //   },
  // },

  // {
  //   title: "Sales Channel",
  //   dataIndex: "sales_channel",
  //   key: "sales_channel",
  //   render: (text, record, index) => {
  //     if (text) {
  //       return record.sales_channels.map((item) => (
  //         <Tag key={item} color="green">
  //           {item.replace("-", " ")}
  //         </Tag>
  //       ))
  //     }

  //     return "-"
  //   },
  // },
  // {
  //   title: "Variant",
  //   dataIndex: "variant_name",
  //   key: "variant_name",
  // },
  // {
  //   title: "Final Price (B2B)",
  //   dataIndex: "final_price",
  //   key: "final_price",
  // },
]

const binDetailListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "So Number",
    dataIndex: "order_number",
    key: "order_number",
  },
  {
    title: "So Type",
    dataIndex: "so_type",
    key: "so_type",
  },
]

export { binListColumn, binDetailListColumn }
