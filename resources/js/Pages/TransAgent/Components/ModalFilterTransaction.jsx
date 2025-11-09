import { FilterFilled, FilterOutlined } from "@ant-design/icons"
import { DatePicker, Input, Modal } from "antd"
import React, { useState } from "react"
const { RangePicker } = DatePicker
const ModalFilterTransaction = ({ handleOk }) => {
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [isFilter, setIsFilter] = useState(false)
  const [filter, setFilter] = useState({
    tanggal_transaksi: null,
    id_transaksi: null,
  })

  const showModal = () => {
    setIsModalOpen(true)
  }

  const handleCancel = () => {
    setIsModalOpen(false)
    setIsFilter(false)
    setFilter({
      tanggal_transaksi: null,
      id_transaksi: null,
    })
  }

  const handleChange = (value, field) => {
    setFilter({ ...filter, [field]: value })
  }

  const clearFilter = () => {
    setIsModalOpen(false)
    setIsFilter(false)
    setFilter({
      tanggal_transaksi: null,
      id_transaksi: null,
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
          <div className="mb-2">
            <label htmlFor="">ID Transaksi</label>
            <Input
              onChange={(e) => handleChange(e.target.value, "id_transaksi")}
            />
          </div>
          <div className="mb-2">
            <label htmlFor="">Tanggal Transaksi</label>
            <RangePicker
              className="w-full"
              format={"YYYY-MM-DD"}
              onChange={(e, dateString) =>
                handleChange(dateString, "tanggal_transaksi")
              }
            />
          </div>
        </div>
      </Modal>
    </div>
  )
}

export default ModalFilterTransaction
