import { CloseOutlined, PlusOutlined, SearchOutlined } from "@ant-design/icons"
import { Input, Modal, Table, Tag, Tooltip } from "antd"
import React, { useState } from "react"
import { productListColumns } from "../config"

const ProductList = ({
  products = [],
  handleChange,
  handleClick,
  data = [],
  loading = false,
  disabled = false,
}) => {
  const mergedColumns = productListColumns.map((col) => {
    return {
      ...col,
      onCell: (record) => ({
        record,
        dataIndex: col.dataIndex,
        products,
        data,
        handleChange: (val) => handleChange(val),
        handleClick: (val) => handleClick(val),
      }),
    }
  })

  const currentUrl = new URL(window.location.href)
  const pathName = currentUrl?.pathname
  const parts = pathName?.split("/").filter(Boolean)
  const uidRetur = parts[parts.length - 1]
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
      <div
        onClick={() => {
          if (disabled) {
            return null
          } else {
            return handleClick({
              type: "add",
              key: 1,
              uid_retur: uidRetur,
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
  const { dataIndex, handleChange, handleClick, record, products, data } = props

  if (dataIndex === "product_id") {
    return (
      <td>
        <ModalProductList
          products={products}
          handleChange={(e) =>
            handleChange({
              value: e,
              dataIndex,
              key: record.key,
              uid_retur: record.uid_retur,
            })
          }
          value={record?.product_id}
        />
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
                key: record.key,
                type: "remove-qty",
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
                  key: record.key,
                })
              }
              return null
            }}
            style={{ width: "100px" }}
            controls={false}
          />

          <button
            className="btn btn-light btn-xs border"
            type="button"
            onClick={() =>
              handleClick({
                key: record.key,
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
    // if (record.key > 0) {
    return (
      <td>
        <button
          disabled={data.length === 1}
          onClick={() =>
            handleClick({
              key: record.key,
              type: "delete",
              uid_retur: record.uid_retur,
            })
          }
          className={`text-white ${
            data.length === 1
              ? "bg-gray-400 hover:bg-gray-800"
              : "bg-red-700 hover:bg-red-800"
          }  focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center`}
        >
          <CloseOutlined />
        </button>
      </td>
    )
  }
  //   return (
  //     <td>
  //       <button
  //         onClick={() =>
  //           handleClick({
  //             key: record.key,
  //             type: "add",
  //             uid_retur: record.uid_retur,
  //           })
  //         }
  //         className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
  //       >
  //         <PlusOutlined />
  //       </button>
  //     </td>
  //   )
  // }

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
        title="Daftar Product"
        open={isModalProductListVisible}
        cancelText={"Batal"}
        okText={"Pilih"}
        onOk={() => {
          handleChange(selectedProduct)
          setIsModalProductListVisible(false)
        }}
        onCancel={() => setIsModalProductListVisible(false)}
        width={900}
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
                ${
                  product.stock_off_market <= 0 &&
                  "border bg-gray-400 text-gray-400"
                }
              `}
              onClick={() => {
                product.stock_off_market > 0 && setSelectedProduct(product.id)
              }}
              // disabled={product.stock === 0}
            >
              <div className="flex max-w-[800px] justify-between items-center">
                <div className="flex items-center">
                  <img
                    src={product.image_url}
                    alt="product_photo"
                    className={`
                    ${product.stock_off_market <= 0 && "grayscale"}
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
                    product.stock_off_market <= 0
                      ? "text-gray-500"
                      : "text-red-500"
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
