import { FilterFilled, FilterOutlined } from "@ant-design/icons"
import { DatePicker, Modal, Input } from "antd"
import React, { useState } from "react"

const { RangePicker } = DatePicker

const FilterModal = ({ handleOk }) => {
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [isFilter, setIsFilter] = useState(false)
  const [filter, setFilter] = useState({
    generate_date: null,
    asset_name: ""
  })

  const showModal = () => {
    setIsModalOpen(true)
  }

  const handleCancel = () => {
    setIsModalOpen(false)
    setIsFilter(false)
    setFilter({
      generate_date: null,
      asset_name: ""
    })
  }

  const handleChange = (value, field) => {
    setFilter({ ...filter, [field]: value })
  }

  const clearFilter = () => {
    setIsModalOpen(false)
    setIsFilter(false)
    setFilter({
      generate_date: null,
      asset_name: ""
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
        title="Filter"
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
          <div className="w-full mb-2">
            <label htmlFor="asset_name">Asset Name</label>
            <Input
              placeholder="Enter asset name"
              value={filter.asset_name}
              onChange={(e) => handleChange(e.target.value, "asset_name")}
            />
          </div>
          <div className="w-full mb-2">
            <label htmlFor="generate_date">Generate Date</label>
            <RangePicker
              className="w-full"
              format={"DD-MM-YYYY"}
              onChange={(e, dateString) => {
                handleChange(dateString, "generate_date")
              }}
            />
          </div>
        </div>
      </Modal>
    </div>
  )
}

export default FilterModal
