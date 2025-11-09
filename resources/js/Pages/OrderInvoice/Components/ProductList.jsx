import {
  CloseOutlined,
  LockOutlined,
  PlusOutlined,
  SearchOutlined,
} from "@ant-design/icons"
import { Empty, Input, Modal, Select, Table, Tag, Tooltip } from "antd"
import React, { useState } from "react"
import { getItem, inArray } from "../../../helpers"
import { productListColumns } from "../config"

const ProductList = ({
  products = [],
  handleChange,
  handleClick,
  onChange,
  data = [],
  taxs = [],
  discounts = [],
  loading = false,
  disabled = false,
  summary = null,
}) => {
  const selectedProduct = data
    .map((product) => product.product_id)
    .filter((item) => item)

  const mergedColumns = productListColumns.map((col) => {
    return {
      ...col,
      onCell: (record) => ({
        record,
        dataIndex: col.dataIndex,
        products,
        taxs,
        discounts,
        selectedProduct,
        disabled,
        summary,
        data, // selected product list
        handleChange: (val) => handleChange(val),
        handleClick: (val) => handleClick(val),
        onChange: (val) => onChange(val),
      }),
    }
  })

  const currentUrl = new URL(window.location.href)
  const pathName = currentUrl?.pathname
  const parts = pathName?.split("/").filter(Boolean)
  const uidLead = parts[parts.length - 1]
  return (
    <div>
      <Table
        components={{
          body: {
            cell: EditableCell,
          },
        }}
        dataSource={data}
        columns={mergedColumns}
        // loading={loading}
        pagination={false}
        rowKey="id"
        scroll={{ x: "max-content" }}
        tableLayout={"auto"}
        summary={summary}
      />
      <div
        onClick={() => {
          if (disabled) {
            return null
          } else {
            return handleClick({
              type: "add",
              key: 1,
              uid_lead: uidLead,
            })
          }
        }}
        className={`
              w-full mt-4 cursor-pointer
              ${
                disabled
                  ? "text-gray-400 border-gray-400/70 bg-gray-400/5"
                  : " text-blue-600 hover:text-blue-800 bg-blue-500/20 border-blue-700/70 hover:border-blue-800"
              }
              border-2 border-dashed  focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 inline-flex items-center justify-center`}
      >
        <PlusOutlined style={{ marginRight: 10 }} />
        <strong>Add More</strong>
      </div>
    </div>
  )
}

const EditableCell = (props) => {
  const {
    dataIndex,
    handleChange,
    handleClick,
    onChange,
    record,
    products,
    selectedProduct,
    taxs,
    discounts,
    disabled,
    data,
  } = props

  if (record) {
    if (dataIndex === "product_id") {
      return (
        <td>
          <ModalProductList
            style={{ marginBottom: 17 }}
            products={products}
            productSelecteds={selectedProduct}
            handleChange={(e) =>
              handleChange({
                value: e,
                dataIndex,
                key: record.id,
                uid_lead: record.uid_lead,
              })
            }
            disabled={disabled}
            value={record?.product_id}
          />
        </td>
      )
    }

    if (dataIndex === "tax_id") {
      return (
        <td>
          <Select
            style={{ marginBottom: 17 }}
            disabled={disabled || !record?.product_id}
            placeholder="Pilih Tax"
            value={record.tax_id}
            onChange={(e) => {
              if (disabled) return
              handleChange({
                value: e,
                dataIndex,
                key: record.id,
                uid_lead: record.uid_lead,
              })
            }}
          >
            {taxs.map((tax) => (
              <Select.Option value={tax.id} key={tax.id}>
                {tax.tax_code}
              </Select.Option>
            ))}
          </Select>
        </td>
      )
    }

    if (dataIndex === "discount_id") {
      return (
        <td>
          <Select
            style={{ marginBottom: 17 }}
            placeholder="Pilih Discount"
            value={record.discount_id}
            disabled={
              disabled || record.disabled_discount || !record?.product_id
            }
            onChange={(e) => {
              if (disabled) return
              handleChange({
                value: e,
                dataIndex,
                key: record.id,
                uid_lead: record.uid_lead,
              })
            }}
          >
            {discounts.map((discount) => (
              <Select.Option value={discount.id} key={discount.id}>
                {discount.title}
              </Select.Option>
            ))}
          </Select>
        </td>
      )
    }

    if (dataIndex === "final_price") {
      return (
        <td>
          <Input
            disabled={true}
            value={record[dataIndex]}
            style={{ marginBottom: 17 }}
            // type={"number"}
            // onChange={(e) =>
            //   onChange({
            //     value: e.target.value,
            //     dataIndex,
            //     key: record.key,
            //     uid_lead: record.uid_lead,
            //   })
            // }
            // onBlur={() => {
            //   if (disabled) return

            //   handleChange({
            //     value: record[dataIndex],
            //     dataIndex,
            //     key: record.id,
            //     uid_lead: record.uid_lead,
            //   })
            // }}
          />
        </td>
      )
    }

    if (dataIndex === "price_nego") {
      return (
        <td>
          <Input
            style={{ marginBottom: 17 }}
            disabled={
              disabled || record?.disabled_price_nego || !record?.product_id
            }
            value={record[dataIndex]}
            type={"number"}
            onChange={(e) => {
              // only accept number only
              let value = e.target.value.toString().replace(/\D/g, "")

              return onChange({
                value: value,
                dataIndex,
                key: record.key,
                uid_lead: record.uid_lead,
              })
            }}
            onBlur={() => {
              if (disabled) return

              handleChange({
                value: record[dataIndex],
                dataIndex,
                key: record.id,
                uid_lead: record.uid_lead,
              })
            }}
            onPressEnter={() => {
              if (disabled) return

              handleChange({
                value: record[dataIndex],
                dataIndex,
                key: record.id,
                uid_lead: record.uid_lead,
              })
            }}
          />
          {inArray(getItem("role"), ["admin", "finance", "superadmin"]) && (
            <span className="text-red-500">Margin: {record?.margin_price}</span>
          )}
        </td>
      )
    }

    if (dataIndex === "qty") {
      if (disabled) {
        return (
          <td>
            <div
              className="input-group input-spinner mr-3"
              style={{ marginBottom: 17 }}
            >
              <button
                className="btn btn-light btn-xs border"
                type="button"
                disabled={disabled}
              >
                <i className="fas fa-minus"></i>
              </button>
              <button className="btn btn-light btn-xs border" type="button">
                {record[dataIndex]}
              </button>
              <button
                className="btn btn-light btn-xs border"
                type="button"
                disabled={disabled}
              >
                <i className="fas fa-plus"></i>
              </button>
            </div>
          </td>
        )
      }
      return (
        <td>
          <div
            className="input-group input-spinner mr-3"
            style={{ marginBottom: 17 }}
          >
            <button
              className="btn btn-light btn-xs border"
              type="button"
              disabled={!record?.product_id}
              onClick={() => {
                if (record?.product_id) {
                  return handleClick({
                    key: record.id,
                    type: "remove-qty",
                    uid_lead: record.uid_lead,
                  })
                }

                return false
              }}
            >
              <i className="fas fa-minus"></i>
            </button>

            <Input
              disabled={disabled || !record?.product_id}
              value={record[dataIndex]}
              onChange={(e) => {
                const { value } = e.target
                if (value == "") {
                  return onChange({
                    value: 0,
                    dataIndex,
                    key: record.key,
                    uid_lead: record.uid_lead,
                  })
                }
                // if (value > -1) {
                //   if (parseInt(value) > parseInt(record.stock)) {
                //     return onChange({
                //       value: record.stock,
                //       dataIndex,
                //       key: record.key,
                //       uid_lead: record.uid_lead,
                //     })
                //   }
                //   return onChange({
                //     value: parseInt(value),
                //     dataIndex,
                //     key: record.key,
                //     uid_lead: record.uid_lead,
                //   })
                // }

                return onChange({
                  value: parseInt(value),
                  dataIndex,
                  key: record.key,
                  uid_lead: record.uid_lead,
                })
              }}
              onBlur={(e) => {
                if (disabled) return

                handleChange({
                  value: record[dataIndex],
                  dataIndex,
                  key: record.id,
                  uid_lead: record.uid_lead,
                })
              }}
              style={{ width: "100px" }}
              controls={false}
            />

            <button
              className="btn btn-light btn-xs border"
              type="button"
              disabled={!record?.product_id}
              onClick={() => {
                if (record?.product_id) {
                  return handleClick({
                    key: record.id,
                    type: "add-qty",
                    uid_lead: record.uid_lead,
                  })
                }

                return false
              }}
            >
              <i className="fas fa-plus"></i>
            </button>
          </div>
        </td>
      )
    }

    if (dataIndex === "action") {
      if (disabled) {
        return (
          <td>
            <button
              className="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
              style={{ marginBottom: 17 }}
            >
              <LockOutlined />
            </button>
          </td>
        )
      }
      // if (record.key > 0) {
      return (
        <td>
          <button
            disabled={data.length === 1}
            onClick={() =>
              handleClick({
                key: record.id,
                type: "delete",
                uid_lead: record.uid_lead,
              })
            }
            className={`text-white ${
              data.length === 1
                ? "bg-gray-400 hover:bg-gray-800"
                : "bg-red-700 hover:bg-red-800"
            }  focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center`}
            style={{ marginBottom: 17 }}
          >
            <CloseOutlined />
          </button>
        </td>
      )
      // }
      // return (
      //   <td>
      //     <button
      //       onClick={() =>
      //         handleClick({
      //           key: record.id,
      //           type: "add",
      //           uid_lead: record.uid_lead,
      //         })
      //       }
      //       className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
      //       style={{ marginBottom: 17 }}
      //     >
      //       <PlusOutlined />
      //     </button>
      //   </td>
      // )
    }

    return (
      <td>
        <Input
          value={record[dataIndex]}
          disabled={!record?.product_id}
          style={{
            marginBottom: 17,
          }}
          readOnly
        />
      </td>
    )
  }
  return (
    <td colSpan={6}>
      <span>Tidak Ada Data</span>
    </td>
  )
}

const ModalProductList = ({
  products,
  productSelecteds = [],
  handleChange,
  value,
  disabled = false,
  style,
}) => {
  const [isModalProductListVisible, setIsModalProductListVisible] =
    useState(false)

  const [selectedProduct, setSelectedProduct] = useState(null)
  const [search, setSearch] = useState("")

  products.sort((a, b) => b.stock_off_market - a.stock_off_market)

  const title = products?.find((product) => product?.id === value)?.name
  const filteredProducts =
    products.filter((value) => value.name.toLowerCase().includes(search)) ||
    products

  const WarehouseNotyetSelected =
    !filteredProducts.filter((item) => !inArray(item.id, productSelecteds))
      .length > 0

  const allProductStockEmpty = filteredProducts.every(
    (value) => value.stock_off_market === 0
  )

  return (
    <div style={style}>
      <Tooltip title={title}>
        {disabled ? (
          <div className="w-96 flex items-center bg-gray-100 border py-1 px-2 rounded-sm line-clamp-1 cursor-pointer">
            <SearchOutlined className="mr-2" />
            <span>{value ? title : "Select Product"}</span>
          </div>
        ) : (
          <div
            className="w-96 flex items-center border py-1 px-2 rounded-sm line-clamp-1 cursor-pointer"
            onClick={() => setIsModalProductListVisible(true)}
          >
            <SearchOutlined className="mr-2" />
            <span>{value ? title : "Select Product"}</span>
          </div>
        )}
      </Tooltip>
      <Modal
        title="List Product"
        open={isModalProductListVisible}
        cancelText={"Batal"}
        okText={"Pilih"}
        onOk={() => {
          handleChange(selectedProduct)
          setIsModalProductListVisible(false)
        }}
        onCancel={() => setIsModalProductListVisible(false)}
        width={900}
        // okButtonProps={{
        //   disabled: WarehouseNotyetSelected || allProductStockEmpty,
        // }}
      >
        <div>
          <Input
            placeholder="Cari produk disini.."
            size={"large"}
            className="rounded mb-4"
            suffix={<SearchOutlined />}
            value={search}
            onChange={(e) => setSearch(e.target.value)}
          />

          {WarehouseNotyetSelected && (
            <Empty description="Data tidak tersedia, silahkan pilih Warehouse terlebih dahulu" />
          )}

          {filteredProducts
            .filter((item) => !inArray(item.id, productSelecteds))
            .map((product) => (
              <div
                key={product.id}
                className={`
                mb-4 shadow-none rounded-md p-2 cursor-pointer bg-white
                ${
                  selectedProduct == product.id
                    ? "border-[1px] border-blue-400 drop-shadow-md ring-blue-500"
                    : "border border-gray-400"
                }
                ${product?.disabled && "border bg-gray-400 text-gray-400"}
              `}
                onClick={() => {
                  // product.stock_off_market > 0 && setSelectedProduct(product.id)
                  setSelectedProduct(product.id)
                }}
                // disabled={product.stock === 0}
              >
                <div className="flex max-w-[800px] justify-between items-center">
                  <div className="flex items-center">
                    <img
                      src={product.image_url}
                      alt="product_photo"
                      className={`
                    ${product?.disabled && "grayscale"}
                     mr-4 w-20 h-20 rounded-md border`}
                    />
                    <div>
                      <Tooltip title={product.name}>
                        <div className="block text-lg line-clamp-1 font-medium max-w-2xl">
                          {product.name}{" "}
                        </div>
                      </Tooltip>
                      <br />
                      <div className="block">
                        Tersedia di :{" "}
                        {product?.sales_channels?.map((value, index) => (
                          <Tag key={index} color="lime">
                            {value}
                          </Tag>
                        ))}
                      </div>
                    </div>
                  </div>

                  <div
                    className={`block ${
                      product?.disabled ? "text-gray-500" : "text-red-500"
                    }`}
                  >
                    Stock Tersedia: {product.stock_off_market}
                  </div>
                </div>
              </div>
            ))}
        </div>
      </Modal>
    </div>
  )
}

export default ProductList
