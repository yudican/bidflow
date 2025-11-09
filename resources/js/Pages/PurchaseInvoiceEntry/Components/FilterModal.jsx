import { FilterFilled } from "@ant-design/icons"
import { DatePicker, Modal, Select } from "antd"
import React, { useState } from "react"

const { RangePicker } = DatePicker

const FilterModal = ({ handleOk }) => {
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [isFilter, setIsFilter] = useState(false)
  const [filter, setFilter] = useState({
    tanggal: null,
    status: null,
  })

  const showModal = () => {
    setIsModalOpen(true)
  }

  const handleCancel = () => {
    setIsModalOpen(false)
    setIsFilter(false)
    setFilter({
      tanggal: null,
      status: null,
    })
  }

  const handleChange = (value, field) => {
    if (field === "createdBy") {
      return setFilter({ ...filter, createdBy: value.value })
    }
    setFilter({ ...filter, [field]: value })
  }

  const clearFilter = () => {
    setIsModalOpen(false)
    setIsFilter(false)
    setFilter({
      tanggal: null,
      status: null,
    })
    handleOk({})
  }

  return (
    <div>
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
          <div>
            <label htmlFor="">Status</label>
            <Select
              mode="multiple"
              allowClear
              className="w-full mb-2"
              placeholder="Pilih Status"
              onChange={(e) => handleChange(e, "status")}
            >
              <Select.Option value={0}>UNPAID</Select.Option>
              <Select.Option value={1}>PAID</Select.Option>
            </Select>
          </div>
          <div className="w-full mb-2">
            <label htmlFor="">Range Date</label>
            <RangePicker
              className="w-full"
              format={"YYYY-MM-DD"}
              onChange={(e, dateString) => handleChange(dateString, "tanggal")}
            />
          </div>
        </div>
      </Modal>
    </div>
  )
}

export default FilterModal
