import { CloseOutlined, PlusOutlined } from "@ant-design/icons"
import { Input, Select, Table } from "antd"
import React from "react"
import ModalProduct from "../../../components/Modal/ModalProduct"
import { productListColumns } from "../config"

const ProductList = ({
  products = [],
  handleChange,
  handleClick,
  data = [],
  taxs = [],
  loading = false,
  type = "Product",
}) => {
  const mergedColumns = productListColumns.map((col) => {
    return {
      ...col,
      onCell: (record) => ({
        record,
        dataIndex: col.dataIndex,
        products,
        taxs,
        type,
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
        rowKey="key"
        scroll={{ x: "max-content" }}
        tableLayout={"auto"}
      />
    </div>
  )
}

const EditableCell = (props) => {
  const { dataIndex, handleChange, handleClick, record, products, taxs, type } =
    props

  if (dataIndex === "product_id") {
    return (
      <td>
        <ModalProduct
          products={products}
          type={type}
          handleChange={(e) =>
            handleChange({
              value: e,
              dataIndex,
              key: record.key,
              item_id: record.id,
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
              key: record.key,
              item_id: record.id,
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

  if (dataIndex === "qty") {
    return (
      <td>
        <div className="input-group input-spinner mr-3">
          <button
            className="btn btn-light btn-xs border"
            type="button"
            onClick={() =>
              handleClick({
                type: "remove-qty",
                key: record.key,
                item_id: record.id,
              })
            }
          >
            <i className="fas fa-minus"></i>
          </button>

          <Input
            // disabled={disabled}
            value={record[dataIndex]}
            onChange={(e) => {
              if (e.target.value > -1) {
                return handleChange({
                  value: e.target.value,
                  dataIndex,
                  key: record.key,
                  item_id: record.id,
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
                type: "add-qty",
                key: record.key,
                item_id: record.id,
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
                type: "delete",
                key: record.key,
                item_id: record.id,
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
              type: "add",
              key: record.key,
              item_id: record.id,
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

export default ProductList
