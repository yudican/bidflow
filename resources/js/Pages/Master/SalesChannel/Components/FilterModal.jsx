import { FilterFilled, FilterOutlined } from "@ant-design/icons"
import { Modal, Select } from "antd"
import React, { useState } from "react"
const FilterModal = ({ handleOk, warehouses = [] }) => {
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [isFilter, setIsFilter] = useState(false)
  const [filter, setFilter] = useState({
    warehouse_id: null,
  })

  const showModal = () => {
    setIsModalOpen(true)
  }

  const handleCancel = () => {
    setIsModalOpen(false)
    setIsFilter(false)
    setFilter({
      warehouse_id: null,
    })
  }

  const handleChange = (value, field) => {
    setFilter((filters) => ({ ...filters, [field]: value }))
  }

  const clearFilter = () => {
    setIsModalOpen(false)
    setIsFilter(false)
    setFilter({
      warehouse_id: null,
    })
    handleOk({})
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
        title="Filter Variant"
        open={isModalOpen}
        onOk={() => {
          handleOk(filter)
          setIsFilter(true)
          setIsModalOpen(false)
        }}
        cancelText={isFilter ? "Clear Filter" : "Cancel"}
        onCancel={isFilter ? clearFilter : handleCancel}
        okText={"Apply Filter"}
      >
        <div>
          <div>
            <label htmlFor="">Warehouse</label>
            <Select
              mode="multiple"
              allowClear
              className="w-full mb-2"
              placeholder="Pilih Warehouse"
              onChange={(e) => handleChange(e, "warehouse_id")}
            >
              {warehouses.map((warehouse) => (
                <Select.Option key={warehouse.id} value={warehouse.id}>
                  {warehouse.name}
                </Select.Option>
              ))}
            </Select>
          </div>
        </div>
      </Modal>
    </div>
  )
}

export default FilterModal
