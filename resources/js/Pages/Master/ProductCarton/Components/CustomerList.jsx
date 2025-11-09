import { CloseOutlined, PlusOutlined } from "@ant-design/icons"
import { Input, Select, Switch, Table } from "antd"
import React from "react"
import { custumerListColumn } from "../config"

const CustomerList = ({
  handleChange,
  handleClick,
  data = [],
  loading = false,
}) => {
  const mergedColumns = custumerListColumn.map((col) => {
    return {
      ...col,
      onCell: (record) => ({
        record,
        dataIndex: col.dataIndex,
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
  const { dataIndex, handleChange, handleClick, record } = props
  if (record) {
    if (dataIndex === "status") {
      return (
        <td>
          <Switch
            checked={record[dataIndex]}
            onChange={(e) =>
              handleChange({
                value: e,
                dataIndex,
                key: record.key,
              })
            }
          />
        </td>
      )
    }
    if (dataIndex === "type") {
      return (
        <td>
          <Select
            placeholder="Pilih Type"
            value={record[dataIndex]}
            className="w-full"
            onChange={(e) =>
              handleChange({
                value: e,
                dataIndex,
                key: record.key,
              })
            }
          >
            <Select.Option value={"email"}>Email</Select.Option>
            <Select.Option value={"telepon"}>Telepon</Select.Option>
            <Select.Option value={"whatsapp"}>Whatsapp</Select.Option>
          </Select>
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
                  key: record.key,
                  type: "delete",
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
                key: record.key,
                type: "add",
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
        <Input
          value={record[dataIndex]}
          onChange={(e) =>
            handleChange({
              value: e.target.value,
              dataIndex,
              key: record.key,
            })
          }
        />
      </td>
    )
  }
  return (
    <td colSpan={6} className="text-center">
      Tidak Ada Data
    </td>
  )
}

export default CustomerList
