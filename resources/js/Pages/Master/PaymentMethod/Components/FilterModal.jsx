import { FilterFilled, FilterOutlined } from "@ant-design/icons"
import { Modal, Select } from "antd"
import React, { useState } from "react"

const filterdata = {
  status: null,
  payment_channel: null,
  payment_type: null,
}
const FilterModal = ({ handleOk }) => {
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [isFilter, setIsFilter] = useState(false)
  const [filter, setFilter] = useState(filterdata)

  const showModal = () => {
    setIsModalOpen(true)
  }

  const handleCancel = () => {
    setIsModalOpen(false)
    setIsFilter(false)
    setFilter(filterdata)
  }

  const handleChange = (value, field) => {
    setFilter((filters) => ({ ...filters, [field]: value }))
  }

  const clearFilter = () => {
    setIsModalOpen(false)
    setIsFilter(false)
    setFilter(filterdata)
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
        title="Filter Data"
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
            <label htmlFor="">Tipe Pembayaran</label>
            <Select
              defaultValue={filter.payment_type}
              value={filter.payment_type}
              allowClear
              className="w-full mb-2"
              placeholder="Pilih Tipe Pembayaran"
              onChange={(e) => handleChange(e, "payment_type")}
            >
              <Select.Option key={1} value={"otomatis"}>
                Otomatis
              </Select.Option>
              <Select.Option key={0} value={"manual"}>
                Manual
              </Select.Option>
            </Select>
          </div>
          <div>
            <label htmlFor="">Channel Pembayaran</label>
            <Select
              defaultValue={filter.payment_channel}
              value={filter.payment_channel}
              allowClear
              className="w-full mb-2"
              placeholder="Pilih Tipe Pembayaran"
              onChange={(e) => handleChange(e, "payment_channel")}
            >
              <Select.Option value="">Select Tipe Pembayaran</Select.Option>
              <Select.Option value="bank_transfer">Bank Transfer</Select.Option>
              <Select.Option value="echannel">Echannel</Select.Option>
              <Select.Option value="bca_klikpay">Bca Klikpay</Select.Option>
              <Select.Option value="bca_klikbca">Bca Klikbca</Select.Option>
              <Select.Option value="bri_epay">BRI Epay</Select.Option>
              <Select.Option value="gopay">Gopay</Select.Option>
              <Select.Option value="shopeepay">Shopeepay</Select.Option>
              <Select.Option value="qris">Qris</Select.Option>
              <Select.Option value="mandiri_clickpay">
                Mandiri Clickpay
              </Select.Option>
              <Select.Option value="cimb_clicks">Cimb Clicks</Select.Option>
              <Select.Option value="danamon_online">
                Danamon Online
              </Select.Option>
              <Select.Option value="cstore">Cstore</Select.Option>
              <Select.Option value="cod_jne">Cod Jne</Select.Option>
              <Select.Option value="cod_jxe">Cod Jxe</Select.Option>
            </Select>
          </div>
          <div>
            <label htmlFor="">Status</label>
            <Select
              defaultValue={filter.status}
              value={filter.status}
              // mode="multiple"
              allowClear
              className="w-full mb-2"
              placeholder="Pilih Status"
              onChange={(e) => handleChange(e, "status")}
            >
              <Select.Option key={1} value={1}>
                Active
              </Select.Option>
              <Select.Option key={10} value={10}>
                Non Active
              </Select.Option>
            </Select>
          </div>
        </div>
      </Modal>
    </div>
  )
}

export default FilterModal
