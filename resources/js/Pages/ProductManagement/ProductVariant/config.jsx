import { Tag, Tooltip } from "antd"
import { truncateString } from "../../../helpers"
import React from "react"

const productVariantListColumn = [
  {
    title: "No.",
    dataIndex: "number",
    key: "number",
    // render: (text, record, index) => index + 1,
  },
  {
    title: "Nama Produk Varian",
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
    title: "SKU Master",
    dataIndex: "sku",
    key: "sku",
  },
  {
    title: "SKU Varian",
    dataIndex: "sku_variant",
    key: "sku_variant",
  },
  {
    title: "SKU Marketplace",
    dataIndex: "sku_marketplace",
    key: "sku_marketplace",
  },
  {
    title: "Kemasan",
    dataIndex: "package_name",
    key: "package_name",
  },
  {
    title: "Stok",
    dataIndex: "stock_of_market",
    key: "stock_of_market",
    render: (text, record) => {
      const curStock = parseInt(record?.stock_of_market)
      const bunStock = parseInt(record?.stock_bundling_total)
      return (
        <Tooltip
          overlayStyle={{ maxWidth: 800 }}
          title={
            <div>
              <p className="mb-2">Stok: {curStock}</p>
              {/* {bunStock > 0 && (
                <p className="mb-2">Stok Bundling: {bunStock}</p>
              )} */}
            </div>
          }
        >
          <span>{curStock}</span>
        </Tooltip>
      )
    },
  },
  {
    title: "Stok Warehouse",
    dataIndex: "stock_off_market",
    key: "stock_off_market",
    render: (text, record) => {
      const curStock = parseInt(record?.stock_off_market)
      const bunStock = parseInt(record?.stock_of_market_bundling)
      return (
        <Tooltip
          overlayStyle={{ maxWidth: 800 }}
          title={
            <div>
              <div>
                <p className="mb-2">Stok Warehouse:</p>
                <div>
                  {record?.stock_warehouse?.map((item, index) => {
                    if (item.stock > 0) {
                      return (
                        <div key={index}>
                          <p className="mb-0">
                            {item.warehouse_name} - {item.stock}
                          </p>
                        </div>
                      )
                    }
                    return null
                  })}
                </div>
              </div>
              {/* <br />
              {record?.stock_bundling.length > 0 && (
                <div>
                  <p className="mb-2">Stok Bundling:</p>
                  <div>
                    {record?.stock_bundling?.map((item, index) => {
                      if (item.stock > 0) {
                        return (
                          <div key={index}>
                            <p className="mb-0">
                              {item.warehouse_name} - {item.stock}
                            </p>
                          </div>
                        )
                      }
                      return null
                    })}
                  </div>
                </div>
              )} */}
            </div>
          }
        >
          <span>{curStock}</span>
        </Tooltip>
      )
    },
  },
  {
    title: "Sales Channel",
    dataIndex: "sales_channel",
    key: "sales_channel",
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
    title: "Varian",
    dataIndex: "variant_name",
    key: "variant_name",
  },
  {
    title: "Final Price (B2B)",
    dataIndex: "final_price",
    key: "final_price",
  },
]

const productListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Produk",
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
        <p className="mb-0 ml-3">{truncateString(record.name, 50)}</p>
      </div>
    ),
  },
]

export { productVariantListColumn, productListColumn }
