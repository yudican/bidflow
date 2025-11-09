import { Tag, Tooltip } from "antd"
import moment from "moment"
import React from "react"
import { capitalizeString, formatNumber } from "../../helpers"
import { CheckCircleOutlined, CloseCircleOutlined } from "@ant-design/icons"
import { formatDate } from "../../helpers"

const inventoryStockColumns = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Receiving Number",
    dataIndex: "received_number",
    key: "received_number",
    // render: (text, record) => {
    //   console.log(record, "record")
    //   return (
    //     <span>
    //       {text ||
    //         (record?.items?.length > 0 && record?.items[0]?.received_number)}
    //     </span>
    //   )
    // },
    render: (text, record) => {
      const items = record?.items || []
      const selectedPo =
        record?.selected_po?.items?.filter(
          (item) => item.ref === record.uid_inventory
        ) || []
      const selectedItem = selectedPo?.find(
        (item) => item.ref === record.uid_inventory
      )

      return (
        <Tooltip
          overlayStyle={{ maxWidth: 800 }}
          title={
            <div>
              {items &&
                items?.map((item, index) => {
                  return (
                    <span key={index}>
                      <span>{`${item.product_name} - ${item.qty} ${item.u_of_m}`}</span>
                      <br />
                    </span>
                  )
                })}
            </div>
          }
        >
          <span>{selectedItem?.received_number}</span>
        </Tooltip>
      )
    },
  },
  {
    title: "PO Number",
    dataIndex: "reference_number",
    key: "reference_number",
    // render: (text, record) => {
    //   const items = record?.selected_po?.items.filter(
    //     (item) => item.ref === record.uid_inventory
    //   )
    //   const isAllocated = record.inventory_status === "alocated"
    //   return (
    //     <Tooltip
    //       overlayStyle={{ maxWidth: 800 }}
    //       title={
    //         <div>
    //           {items &&
    //             items?.map((item, index) => {
    //               return (
    //                 <span key={index}>
    //                   <span>{`${item.product_name} - ${
    //                     isAllocated ? item.qty_diterima : item.qty
    //                   } ${item.uom}`}</span>
    //                   <br />
    //                 </span>
    //               )
    //             })}
    //         </div>
    //       }
    //     >
    //       <span>{text}</span>
    //     </Tooltip>
    //   )
    // },
  },
  {
    title: "Vendor Code",
    dataIndex: "vendor",
    key: "vendor",
    render: (text, record) => {
      const items = record?.selected_po?.items.filter(
        (item) => item.is_master > 0
      )
      return (
        <Tooltip
          overlayStyle={{ maxWidth: 800 }}
          title={
            <div>
              {record.vendor_name}
              {/* {items.map((item, index) => {
                return (
                  <span>
                    <span>{`${item.received_number || ""} - ${
                      item.notes || ""
                    }`}</span>
                    <br />
                  </span>
                )
              })} */}
            </div>
          }
        >
          <span>{text}</span>
        </Tooltip>
      )
    },
  },
  {
    title: "Received by",
    dataIndex: "received_by_name",
    key: "received_by_name",
  },
  {
    title: "Received Date",
    dataIndex: "received_date",
    key: "received_date",
    render: (text) => {
      return moment(text).format("DD-MM-YYYY")
    },
  },

  {
    title: "Status",
    dataIndex: "inventory_status",
    key: "inventory_status",
    render: (text) => {
      if (text === "received") {
        return <Tag color="yellow">Diterima</Tag>
      } else if (text === "alocated") {
        return <Tag color="green">Teralokasi</Tag>
      } else {
        return <Tag color="red">Canceled</Tag>
      }
    },
  },
  {
    title: "Received WH",
    dataIndex: "warehouse_name",
    key: "warehouse_name",
  },
  {
    title: "Company",
    dataIndex: "company_name",
    key: "company_name",
  },
]

const inventoryTransferStockColumns = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  // {
  //   title: "Product",
  //   dataIndex: "product_name",
  //   key: "product_name",
  //   render: (text, record) => {
  //     console.log(record, "record")
  //     return (
  //       <Tooltip
  //         overlayStyle={{ maxWidth: 800 }}
  //         title={`Qty: ${record.total_qty}`}
  //       >
  //         <span>{text}</span>
  //       </Tooltip>
  //     )
  //   },
  // },
  {
    title: "TRF ID",
    dataIndex: "so_ethix",
    key: "so_ethix",
    render: (text, record) => {
      return (
        <Tooltip
          overlayStyle={{ maxWidth: 800 }}
          title={
            <div>
              {record?.detail_items?.map((item, index) => {
                return (
                  <span key={index}>
                    <span>{`${item.product_name} - ${item.qty_alocation} ${item.u_of_m}`}</span>{" "}
                    <br />
                  </span>
                )
              })}
            </div>
          }
        >
          <span>{text}</span>
        </Tooltip>
      )
    },
  },
  {
    title: "Contact",
    dataIndex: "contact_name",
    key: "contact_name",
  },
  {
    title: "Warehouse",
    dataIndex: "warehouse_name",
    key: "warehouse_name",
  },
  {
    title: "Destination Warehouse",
    dataIndex: "warehouse_destination_name",
    key: "warehouse_destination_name",
    render: (text, record) => {
      if (record.inventory_type == "transfer") {
        return text
      } else {
        return record?.bin_destination_name
      }
    },
  },
  {
    title: "Alokasi by",
    dataIndex: "allocated_by_name",
    key: "allocated_by_name",
  },
  {
    title: "Created On",
    dataIndex: "created_on",
    key: "created_on",
  },
  {
    title: "Status Ethix",
    dataIndex: "status_ethix",
    key: "status_ethix",
    align: "center",
    render: (text) => {
      if (text === "submited") {
        return <CheckCircleOutlined style={{ color: "#7C9B3A" }} />
      }
      return <CloseCircleOutlined style={{ color: "#FE3A30" }} />
    },
  },
  {
    title: "Status",
    dataIndex: "status_name",
    key: "status_name",
    align: "center",
    render: (text) => {
      const textColor = (text) => {
        switch (text) {
          case "Success":
            return "text-green-400"

          case "Draft":
            return "text-yellow-400"

          default:
            return "text-red-400"
        }
      }
      const tagColor = (text) => {
        switch (text) {
          case "Success":
            return "green"

          case "Draft":
            return "gold"

          default:
            return "red"
        }
      }
      return (
        <Tag color={tagColor(text)}>
          <span className={`${textColor(text)}`}>
            {capitalizeString(text || "Draft")}
          </span>
        </Tag>
      )
    },
  },
]

const inventoryTransferStockKonsinyasiColumns = (currentPage, perpage) => [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    // render: (text, record, index) => index + 1,
    render: (text, record, index) => (currentPage - 1) * perpage + index + 1,
  },
  {
    title: "TRF ID",
    dataIndex: "so_ethix",
    key: "so_ethix",
    render: (text, record) => {
      return (
        <Tooltip
          overlayStyle={{ maxWidth: 800 }}
          title={
            <div>
              {record?.detail_items?.map((item, index) => {
                return (
                  <span key={index}>
                    <span>{`${item.product_name} - ${item.qty_alocation} ${item.u_of_m}`}</span>{" "}
                    <br />
                  </span>
                )
              })}
            </div>
          }
        >
          <span>{text}</span>
        </Tooltip>
      )
    },
  },
  {
    title: "Nomor SO",
    dataIndex: "order_number",
    key: "order_number",
  },
  {
    title: "Asal Warehouse",
    dataIndex: "warehouse_name",
    key: "warehouse_name",
  },
  {
    title: "Destinasi BIN",
    dataIndex: "warehouse_destination_name",
    key: "warehouse_destination_name",
    render: (text, record) => {
      if (record.inventory_type == "transfer") {
        return text
      } else {
        return record?.bin_destination_name
      }
    },
  },
  {
    title: "Alokasi by",
    dataIndex: "allocated_by_name",
    key: "allocated_by_name",
  },
  {
    title: "Created On",
    dataIndex: "created_on",
    key: "created_on",
    render: (text) => formatDate(text, "DD-MM-YYYY"),
  },
  {
    title: "Kategori Data",
    dataIndex: "transfer_category",
    key: "transfer_category",
    render: (text) => {
      if (text == "new") {
        return "Data Baru"
      }

      if (text == "old") {
        return "Data Lama"
      }

      return "Data Baru"
    },
  },
  // {
  //   title: "Status Ethix",
  //   dataIndex: "status_ethix",
  //   key: "status_ethix",
  //   align: "center",
  //   render: (text) => {
  //     if (text === "submited") {
  //       return <CheckCircleOutlined style={{ color: "#7C9B3A" }} />;
  //     }
  //     return <CloseCircleOutlined style={{ color: "#FE3A30" }} />;
  //   },
  // },
  {
    title: "Status",
    dataIndex: "status_name",
    key: "status_name",
    align: "center",
    render: (text) => {
      const textColor = (text) => {
        switch (text) {
          case "Success":
            return "text-green-400"

          case "Draft":
            return "text-yellow-400"

          default:
            return "text-red-400"
        }
      }
      const tagColor = (text) => {
        switch (text) {
          case "Success":
            return "green"

          case "Draft":
            return "gold"

          default:
            return "red"
        }
      }
      return (
        <Tag color={tagColor(text)}>
          <span className={`${textColor(text)}`}>
            {capitalizeString(text || "Draft")}
          </span>
        </Tag>
      )
    },
  },
]

const inventoryStockAdjustmentColumns = (currentPage, perpage) => [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => (currentPage - 1) * perpage + index + 1,
  },
  {
    title: "Destinasi Warehouse",
    dataIndex: "warehouse_name",
    key: "warehouse_name",
  },
  {
    title: "Destinasi BIN",
    dataIndex: "master_bin_name",
    key: "master_bin_name",
  },
  {
    title: "Alokasi by",
    dataIndex: "allocated_by_name",
    key: "allocated_by_name",
  },
  {
    title: "Created On",
    dataIndex: "created_on",
    key: "created_on",
    render: (text) => formatDate(text, "DD-MM-YYYY"),
  },
  {
    title: "Status",
    dataIndex: "status_name",
    key: "status_name",
    align: "center",
    render: (text) => {
      console.log("text cek", text)
      const textColor = (text) => {
        switch (text) {
          case "Success":
            return "text-green-400"

          case "Draft":
            return "text-yellow-400"

          case "Waiting Approval":
            return "text-blue-400"

          default:
            return "text-red-400"
        }
      }
      const tagColor = (text) => {
        switch (text) {
          case "Success":
            return "green"

          case "Draft":
            return "gold"

          case "Waiting Approval":
            return "blue"

          default:
            return "red"
        }
      }
      return (
        <Tag color={tagColor(text)}>
          <span className={`${textColor(text)}`}>
            {capitalizeString(text || "Draft")}
          </span>
        </Tag>
      )
    },
  },
]

const inventoryReturnStatus = (status) => {
  {
    if (status === "0") {
      return <Tag color="yellow">Waiting Approval</Tag>
    } else if (status === "2") {
      return <Tag color="yellow">On Proccess</Tag>
    } else if (status === "3") {
      return <Tag color="blue">Received</Tag>
    } else if (status === "4") {
      return <Tag color="red">Rejected</Tag>
    } else {
      return <Tag color="green">Completed</Tag>
    }
  }
}
const inventoryReturnColumns = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "SR Number",
    dataIndex: "nomor_sr",
    key: "nomor_sr",
    render: (text, record) => {
      return (
        <Tooltip
          overlayStyle={{ maxWidth: 800 }}
          title={
            <div>
              {record?.items &&
                record?.items?.map((item, index) => {
                  return (
                    <span key={index}>
                      <span>
                        {`${item?.product_name || ""} - Qty ${item?.qty || ""
                          } ${item?.u_of_m || ""}`}{" "}
                      </span>{" "}
                      <br />
                    </span>
                  )
                })}
            </div>
          }
        >
          <span>{text}</span>
        </Tooltip>
      )
    },
  },
  {
    title: "Vendor code",
    dataIndex: "vendor_code",
    key: "vendor_code",
    render: (text, record) => {
      return (
        <Tooltip
          overlayStyle={{ maxWidth: 800 }}
          title={<div>{record.vendor || "-"}</div>}
        >
          <span>{text}</span>
        </Tooltip>
      )
    },
  },
  {
    title: "Created by",
    dataIndex: "created_by_name",
    key: "created_by_name",
  },
  {
    title: "Status",
    dataIndex: "status",
    key: "status",
    render: (text) => inventoryReturnStatus(text),
  },
  {
    title: "Received Date",
    dataIndex: "received_date",
    key: "received_date",
    render: (text) => {
      return moment(text).format("DD-MM-YYYY")
    },
  },
  {
    title: "Received WH",
    dataIndex: "warehouse_name",
    key: "warehouse_name",
  },
  {
    title: "Company",
    dataIndex: "company_account_name",
    key: "company_account_name",
  },
]

const productListColumns = [
  {
    title: "Item",
    dataIndex: "product_id",
    key: "product_id",
    width: 300,
  },
  {
    title: "From WH",
    dataIndex: "from_warehouse_id",
    key: "from_warehouse_id",
  },
  {
    title: "To WH",
    dataIndex: "to_warehouse_id",
    key: "to_warehouse_id",
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
    title: "Qty Diterima",
    dataIndex: "qty",
    key: "qty",
  },
  {
    title: "Qty",
    dataIndex: "qty_alocation",
    key: "qty_alocation",
  },
  {
    title: "Action",
    dataIndex: "action",
    key: "action",
  },
]

const productListKonsColumns = [
  {
    title: "Product",
    dataIndex: "product_id",
    key: "product_id",
    width: 300,
  },
  {
    title: "Price",
    dataIndex: "price",
    key: "price",
    render: (text) => formatNumber(text, "Rp"),
  },

  {
    title: "Qty",
    dataIndex: "qty",
    key: "qty",
  },
  {
    title: "Tax",
    dataIndex: "tax_id",
    key: "tax_id",
  },
  {
    title: "Discount",
    dataIndex: "discount",
    key: "discount",
  },
  {
    title: "Subtotal",
    dataIndex: "subtotal",
    key: "subtotal",
    render: (text) => formatNumber(text, "Rp"),
  },
  {
    title: "Total Price Nego",
    dataIndex: "price_nego",
    key: "price_nego",
    render: (text) => formatNumber(text, "Rp"),
  },
  {
    title: " Total Dpp + PPN",
    dataIndex: "total",
    key: "total",
    render: (text) => formatNumber(text, "Rp"),
  },
  {
    title: "Action",
    dataIndex: "action",
    key: "action",
  },
]

const productListAllocationHistoryColumns = [
  {
    title: "Item",
    dataIndex: "product_id",
    key: "product_id",
    width: 300,
  },
  {
    title: "From WH",
    dataIndex: "from_warehouse_id",
    key: "from_warehouse_id",
  },
  {
    title: "To WH",
    dataIndex: "to_warehouse_id",
    key: "to_warehouse_id",
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
    title: "Qty Diterima",
    dataIndex: "qty",
    key: "qty",
  },
]

const barcodeListColumns = [
  {
    title: "No",
    dataIndex: "no",
    key: "no",
    width: 300,
  },
  {
    title: "Barcode SKU",
    dataIndex: "barcode_child",
    key: "barcode_child",
  },
  {
    title: "Barcode Master Karton",
    dataIndex: "barcode_master",
    key: "barcode_master",
  },
  {
    title: "Batch ID",
    dataIndex: "batch_id",
    key: "batch_id",
  },
  {
    title: "Status",
    dataIndex: "status",
    key: "status",
  },
]

const productListReturnColumns = [
  // {
  //   title: "Case Return",
  //   dataIndex: "case_return",
  //   key: "case_return",
  //   width: 300,
  // },
  {
    title: "Product",
    dataIndex: "product_id",
    key: "product_id",
    width: 300,
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
    title: "Qty",
    dataIndex: "qty_alocation",
    key: "qty_alocation",
  },
  {
    title: "Notes",
    dataIndex: "notes",
    key: "notes",
  },
  {
    title: "Action",
    dataIndex: "action",
    key: "action",
  },
]

const inventoryReturnStockColumns = [
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
    render: (text, record) => {
      console.log(record, "record")
      return (
        <Tooltip
          overlayStyle={{ maxWidth: 800 }}
          title={`Qty: ${record.total_qty}`}
        >
          <span>{text}</span>
        </Tooltip>
      )
    },
  },
  {
    title: "Warehouse",
    dataIndex: "warehouse_name",
    key: "warehouse_name",
  },
  {
    title: "Destination Warehouse",
    dataIndex: "warehouse_destination_name",
    key: "warehouse_destination_name",
  },
  {
    title: "Alokasi By",
    dataIndex: "allocated_by_name",
    key: "allocated_by_name",
  },
  {
    title: "Created On",
    dataIndex: "created_on",
    key: "created_on",
  },
  {
    title: "Status Ethix",
    dataIndex: "status_ethix",
    key: "status_ethix",
    align: "center",
    render: (text) => {
      const textColor = (text) => {
        switch (text) {
          case "success":
            return "text-green-400 "

          case "waiting":
            return "text-yellow-400"

          default:
            return "text-red-400"
        }
      }
      return (
        <span className={`${textColor(text)}`}>{capitalizeString(text)}</span>
      )
    },
  },
]

export {
  inventoryStockColumns,
  inventoryReturnColumns,
  productListColumns,
  productListKonsColumns,
  productListAllocationHistoryColumns,
  inventoryTransferStockColumns,
  productListReturnColumns,
  inventoryReturnStockColumns,
  inventoryTransferStockKonsinyasiColumns,
  inventoryStockAdjustmentColumns,
  inventoryReturnStatus,
  barcodeListColumns,
}
