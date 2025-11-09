import { CloseOutlined, PlusOutlined, SearchOutlined } from "@ant-design/icons"
import { Empty, Input, Modal, Select, Table, Tag, Tooltip } from "antd"
import React, { useState } from "react"
import { productListColumns } from "../config"

const ProductList = ({
  products = [],
  handleChange,
  handleClick,
  data = [],
  taxs = [],
  discounts = [],
  loading = false,
}) => {
  const mergedColumns = productListColumns.map((col) => {
    return {
      ...col,
      onCell: (record) => ({
        record,
        dataIndex: col.dataIndex,
        products,
        taxs,
        discounts,
        handleChange: (val) => handleChange(val),
        handleClick: (val) => handleClick(val),
      }),
    }
  })
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
        loading={loading}
        pagination={false}
        rowKey="id"
        scroll={{ x: "max-content" }}
        tableLayout={"auto"}
      />
    </div>
  )
}

const EditableCell = (props) => {
  const {
    dataIndex,
    handleChange,
    handleClick,
    record,
    products,
    taxs,
    discounts,
  } = props

  if (dataIndex === "product_id") {
    return (
      <td>
        <ModalProductList
          products={products}
          handleChange={(e) =>
            handleChange({
              value: e,
              dataIndex,
              key: record.id,
              uid_retur: record.uid_retur,
            })
          }
          value={record?.product_id}
        />
      </td>
    )
  }
  if (dataIndex === "tax_id") {
    return (
      <td>
        <Select
          placeholder="Pilih Tax"
          value={record.tax_id}
          onChange={(e) =>
            handleChange({
              value: e,
              dataIndex,
              key: record.id,
              uid_retur: record.uid_retur,
            })
          }
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
          placeholder="Pilih Discount"
          value={record.discount_id}
          onChange={(e) =>
            handleChange({
              value: e,
              dataIndex,
              key: record.id,
              uid_retur: record.uid_retur,
            })
          }
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
  if (dataIndex === "qty") {
    return (
      <td>
        <div className="input-group input-spinner mr-3">
          <button
            className="btn btn-light btn-xs border"
            type="button"
            onClick={() =>
              handleClick({
                key: record.id,
                type: "remove-qty",
                uid_retur: record.uid_retur,
              })
            }
          >
            <i className="fas fa-minus"></i>
          </button>

          {/* <button className="btn btn-light btn-xs border" type="button">
            {record[dataIndex]}
          </button> */}

          <Input
            // disabled={disabled}
            value={record[dataIndex]}
            onChange={(e) => {
              if (e.target.value > -1) {
                return handleChange({
                  value: e.target.value,
                  dataIndex,
                  key: record.id,
                  uid_retur: record.uid_retur,
                })
              }
              return null
            }}
            // onBlur={(e) => {
            //   console.log(e, "onblur")

            //   // if (disabled) return

            //   return handleChange({
            //     value: record[dataIndex],
            //     dataIndex,
            //     key: record.id,
            //     uid_lead: record.uid_lead,
            //   })
            // }}
            style={{ width: "100px" }}
            controls={false}
          />

          <button
            className="btn btn-light btn-xs border"
            type="button"
            onClick={() =>
              handleClick({
                key: record.id,
                type: "add-qty",
                uid_retur: record.uid_retur,
              })
            }
          >
            <i className="fas fa-plus"></i>
          </button>
        </div>
      </td>
    )
  }

  if (dataIndex === "action") {
    if (record.key > 0) {
      return (
        <td>
          <button
            onClick={() =>
              handleClick({
                key: record.id,
                type: "delete",
                uid_retur: record.uid_retur,
              })
            }
            className="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
          >
            <CloseOutlined />
          </button>
        </td>
      )
    }
    return (
      <td>
        <button
          onClick={() =>
            handleClick({
              key: record.id,
              type: "add",
              uid_retur: record.uid_retur,
            })
          }
          className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
        >
          <PlusOutlined />
        </button>
      </td>
    )
  }

  return (
    <td>
      <Input value={record[dataIndex]} readOnly />
    </td>
  )
}

const ModalProductList = ({ products, handleChange, value }) => {
  const [isModalProductListVisible, setIsModalProductListVisible] =
    useState(false)

  const [selectedProduct, setSelectedProduct] = useState(null)
  const [search, setSearch] = useState("")

  products.sort((a, b) => b.stock_off_market - a.stock_off_market)

  const title = products?.find((product) => product?.id === value)?.name
  const filteredProducts =
    products.filter((value) => value.name.toLowerCase().includes(search)) ||
    products

  const WarehouseNotyetSelected = !filteredProducts.length > 0

  const allProductStockEmpty = filteredProducts.every(
    (value) => value.stock_off_market === 0
  )

  return (
    <div>
      <Tooltip title={title}>
        <div
          className="w-96 flex items-center border py-1 px-2 rounded-sm line-clamp-1 cursor-pointer"
          onClick={() => setIsModalProductListVisible(true)}
        >
          <SearchOutlined className="mr-2" />
          <span>{value ? title : "Select Product"}</span>
        </div>
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
        okButtonProps={{
          disabled: WarehouseNotyetSelected || allProductStockEmpty,
        }}
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

          {filteredProducts.map((product) => (
            <div
              key={product.id}
              className={`
                mb-4 shadow-none rounded-md p-2 cursor-pointer bg-white
                ${
                  selectedProduct == product.id
                    ? "border-[1px] border-blue-400 drop-shadow-md ring-blue-500"
                    : "border border-gray-400"
                }
              `}
              onClick={() => {
                setSelectedProduct(product.id)
              }}
              // disabled={product.stock === 0}
            >
              <div className="flex max-w-[800px] justify-between items-center">
                <div className="flex items-center">
                  <img
                    src={product.image_url}
                    alt="product_photo"
                    className={`mr-4 w-20 h-20 rounded-md border`}
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

                <div className={`block text-red-500`}>
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
