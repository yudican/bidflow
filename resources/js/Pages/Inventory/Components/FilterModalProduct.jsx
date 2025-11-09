import { FilterFilled, FilterOutlined } from "@ant-design/icons"
import { Modal, Select, Form } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { inArray } from "../../../helpers"

const FilterModalProduct = ({ handleOk, type = "stock" }) => {
  const [form] = Form.useForm()
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [isFilter, setIsFilter] = useState(false)
  const [warehouses, setWarehouses] = useState([])

  const showModal = () => {
    setIsModalOpen(true)
  }

  const loadWarehouse = () => {
    axios.get("/api/master/warehouse").then((res) => {
      setWarehouses(res.data.data)
    })
  }

  useEffect(() => {
    loadWarehouse()
  }, [])

  const handleCancel = () => {
    setIsModalOpen(false)
    setIsFilter(false)
    form.resetFields()
  }

  const clearFilter = () => {
    setIsModalOpen(false)
    setIsFilter(false)
    form.resetFields()
    handleOk({})
  }

  const titles = {
    received: "Filter Penerimaan Stock",
    transfer: "Filter Transfer Warehouse",
    konsinyasi: "Filter Transfer Konsinyasi",
  }

  return (
    <div>
      {isFilter ? (
        <button
          onClick={() => showModal()}
          className="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
        >
          <FilterOutlined />
          <span className="ml-2">Show Filter</span>
        </button>
      ) : (
        <button
          onClick={() => showModal()}
          className="
          bg-white border 
          text-blue-700 hover:text-blue-700/90
          delay-100 ease-in-out
          focus:ring-4 focus:outline-none focus:ring-blue-300 
          font-medium rounded-lg 
          text-sm px-4 py-2 text-center inline-flex items-center
        "
        >
          <FilterFilled />
          <span className="ml-2">Filter</span>
        </button>
      )}

      <Modal
        title={titles[type]}
        open={isModalOpen}
        onOk={() => {
          form.validateFields().then((values) => {
            handleOk(values)
            setIsFilter(true)
            setIsModalOpen(false)
          })
        }}
        cancelText={isFilter ? "Clear Filter" : "Cancel"}
        onCancel={isFilter ? clearFilter : handleCancel}
        okText={"Apply Filter"}
      >
        <Form
          form={form}
          layout="vertical"
          initialValues={{
            warehouse_id: undefined,
            transfer_category: undefined,
            status: undefined,
          }}
        >
          <Form.Item label="Destinasi Warehouse" name="warehouse_id">
            <Select allowClear placeholder="Pilih Destinasi Warehouse">
              {warehouses.map((item) => (
                <Select.Option key={item.id} value={item.id}>
                  {item.name}
                </Select.Option>
              ))}
            </Select>
          </Form.Item>

          {inArray(type, ["konsinyasi"]) && (
            <Form.Item label="Kategori Data" name="transfer_category">
              <Select allowClear placeholder="Pilih Kategori Data">
                <Select.Option value="new">Data Baru</Select.Option>
                <Select.Option value="old">Data Lama</Select.Option>
              </Select>
            </Form.Item>
          )}

          {inArray(type, ["received"]) && (
            <Form.Item label="Status" name="status">
              <Select mode="multiple" allowClear placeholder="Pilih Status">
                <Select.Option value="received">Received</Select.Option>
                <Select.Option value="alocated">Teralokasi</Select.Option>
                <Select.Option value="canceled">Canceled</Select.Option>
              </Select>
            </Form.Item>
          )}
        </Form>
      </Modal>
    </div>
  )
}

export default FilterModalProduct
