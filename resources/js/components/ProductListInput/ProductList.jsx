import {
  CloseOutlined,
  LockOutlined,
  PlusOutlined,
  SearchOutlined,
} from "@ant-design/icons"
import {
  Empty,
  Input,
  Modal,
  Select,
  Skeleton,
  Table,
  Tag,
  Tooltip,
} from "antd"
import React, { useState } from "react"
import { formatNumber, inArray } from "../../helpers"
import { productListColumns } from "./config"

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
  orderType = null,
}) => {
  console.log("orderType 2:", orderType)
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
        loading,
        orderType,
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
        orderType={orderType}
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
              ${disabled
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
    loading,
    disabled,
    data,
    orderType,
  } = props
  if (record) {
    console.log("orderType 3:", orderType)
    if (dataIndex === "product_id") {
      return (
        <td>
          <ModalProductList
            style={{ marginBottom: 17 }}
            loading={loading}
            products={products}
            productSelecteds={selectedProduct}
            handleChange={(e) =>
              handleChange({
                value: e,
                dataIndex,
                key: record.key,
              })
            }
            disabled={disabled}
            value={record?.product_id}
            orderType={orderType}
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
                key: record.key,
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

    if (dataIndex === "discount") {
      return (
        <td>
          <Input
            disabled={disabled}
            value={formatNumber(record[dataIndex])}
            style={{ marginBottom: 17 }}
            // type={"number"}
            onChange={(event) => {
              let value = event.target.value.toString().replace(/\D/g, "")

              if (value == "") {
                return onChange({
                  value: 0,
                  dataIndex,
                  key: record.key,
                })
              }

              return onChange({
                value: value,
                dataIndex,
                key: record.key,
              })
            }}
            onBlur={() => {
              return handleChange({
                value: record[dataIndex],
                dataIndex,
                key: record.key,
              })
            }}
          />
        </td>
      )
    }

    if (dataIndex === "price_satuan") {
      return (
        <td>
          <Input
            disabled={disabled}
            value={formatNumber(record[dataIndex])}
            style={{ marginBottom: 17 }}
            // type={"number"}
            onChange={(event) => {
              let value = event.target.value.toString().replace(/\D/g, "")

              if (value == "") {
                return onChange({
                  value: 0,
                  dataIndex,
                  key: record.key,
                })
              }

              return onChange({
                value: value,
                dataIndex,
                key: record.key,
              })
            }}
            onBlur={() => {
              return handleChange({
                value: record[dataIndex],
                dataIndex,
                key: record.key,
              })
            }}
          />
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
                    key: record.key,
                    type: "remove-qty",
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
                  })
                }
                // if (value > -1) {
                //   if (parseInt(value) > parseInt(record.stock)) {
                //     return onChange({
                //       value: record.stock,
                //       dataIndex,
                //       key: record.key,
                //
                //     })
                //   }
                //   return onChange({
                //     value: parseInt(value),
                //     dataIndex,
                //     key: record.key,
                //
                //   })
                // }
                console.log("change qty : value :", parseInt(value))
                console.log("change qty : dataIndex :", dataIndex)
                console.log("change qty : key :", record.key)
                return onChange({
                  value: parseInt(value),
                  dataIndex,
                  key: record.key,
                })
              }}
              onBlur={(e) => {
                if (disabled) return

                handleChange({
                  value: record[dataIndex],
                  dataIndex,
                  key: record.key,
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
                    key: record.key,
                    type: "add-qty",
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
          <td className="ant-table-cell ant-table-cell-fix-right ant-table-cell-fix-right-first sticky right-0 text-center">
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
        <td className="ant-table-cell ant-table-cell-fix-right ant-table-cell-fix-right-first sticky right-0 text-center">
          <button
            disabled={data.length === 1}
            onClick={() =>
              handleClick({
                key: record.key,
                type: "delete",
              })
            }
            className={`text-white ${data.length === 1
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
      //             key: record.key,
      //           type: "add",
      //
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
          value={formatNumber(record[dataIndex])}
          disabled
          style={{
            marginBottom: 17,
          }}
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
  loading,
  products,
  productSelecteds = [],
  handleChange,
  value,
  disabled = false,
  style,
  orderType,
}) => {
  const [isModalProductListVisible, setIsModalProductListVisible] =
    useState(false)

  const [selectedProduct, setSelectedProduct] = useState(null)
  const [search, setSearch] = useState("")

  products.sort((a, b) => b.stock_off_market - a.stock_off_market)
  console.log(products, "products")
  const title = products?.find((product) => product?.id === value)?.name
  const filteredProducts =
    products.filter((value) =>
      value.name.toLowerCase().includes(search.toLowerCase())
    ) || products

  const WarehouseNotyetSelected =
    !filteredProducts.filter((item) => !inArray(item.id, productSelecteds))
      .length > 0

  // const allProductStockEmpty = filteredProducts.every(
  //   (value) => value.stock_off_market === 0
  // )

  return (
    <div style={style}>
      <Tooltip title={title}>
        {disabled ? (
          <div className="w-96 flex items-center bg-gray-100 border py-1 px-2 rounded-sm line-clamp-1 cursor-pointer">
            {loading ? (
              <Skeleton.Input active size={"default"} block={true} /> // ini buat detail page
            ) : (
              <>
                <SearchOutlined className="mr-2" />
                <span>{value ? title : "Pilih Produk"}</span>
              </>
            )}
          </div>
        ) : (
          <div
            className="w-96 flex items-center border py-1 px-2 rounded-sm line-clamp-1 cursor-pointer"
            onClick={() => setIsModalProductListVisible(true)}
          >
            {/* {loading || products?.length <= 0 ? ( */}
            {loading ? (
              <Skeleton.Input active size={"default"} block={true} />
            ) : (
              <>
                <SearchOutlined className="mr-2" />
                <span>{value ? title : "Pilih Produk"}</span>
              </>
            )}
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
        <Input
          placeholder="Cari produk disini.."
          size={"large"}
          className="rounded mb-4"
          suffix={<SearchOutlined />}
          value={search}
          onChange={(e) => setSearch(e.target.value)}
        />
        <div className="max-h-[60vh] overflow-y-auto">
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
                ${selectedProduct == product.id
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
                    className={`block ${product?.disabled ? "text-gray-500" : "text-red-500"
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
