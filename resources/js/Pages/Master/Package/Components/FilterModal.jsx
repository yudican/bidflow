import { FilterFilled, FilterOutlined } from "@ant-design/icons"
import { Modal, Select } from "antd"
import React, { useState } from "react"
const FilterModal = ({ handleOk }) => {
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [isFilter, setIsFilter] = useState(false)
  const [filter, setFilter] = useState({
    status: null,
    status_sync: null,
  })

  const showModal = () => {
    setIsModalOpen(true)
  }

  const handleCancel = () => {
    setIsModalOpen(false)
    setIsFilter(false)
    setFilter({
      status: null,
      status_sync: null,
    })
  }

  const handleChange = (value, field) => {
    setFilter((filters) => ({ ...filters, [field]: value }))
  }

  const clearFilter = () => {
    setIsModalOpen(false)
    setIsFilter(false)
    setFilter({
      status: null,
      status_sync: null,
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
        title="Filter Package"
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
            <label htmlFor="">Status</label>
            <Select
              // mode="multiple"
              allowClear
              className="w-full mb-2"
              placeholder="Pilih Status"
              value={filter?.status}
              onChange={(e) => handleChange(e, "status")}
            >
              <Select.Option key={1} value={1}>
                Active
              </Select.Option>
              <Select.Option key={0} value={0}>
                Non Active
              </Select.Option>
            </Select>
          </div>
          <div>
            <label htmlFor="">Status Sync</label>
            <Select
              allowClear
              className="w-full mb-2"
              placeholder="Pilih Status Sync"
              value={filter?.status_sync}
              onChange={(e) => handleChange(e, "status_sync")}
            >
              <Select.Option key={1} value={"sync"}>
                Sync
              </Select.Option>
              <Select.Option key={0} value={"not sync"}>
                Not Sync
              </Select.Option>
            </Select>
          </div>
        </div>
      </Modal>
    </div>
  )
}

export default FilterModal
