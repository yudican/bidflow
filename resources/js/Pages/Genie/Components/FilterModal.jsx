import {
  CloseCircleFilled,
  FilterFilled,
  FilterOutlined,
} from "@ant-design/icons"
import { DatePicker, Modal, Select, TimePicker } from "antd"
import moment from "moment"
import React, { useEffect, useState } from "react"
import { channelId, orderStatus } from "../config"
const { RangePicker } = DatePicker
const FilterModal = ({ handleOk }) => {
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [isFilter, setIsFilter] = useState(false)
  const [skus, setSkus] = useState([])

  const [filter, setFilter] = useState({
    channel: null,
    sku: null,
    tanggal_transaksi: null,
    status: null,
  })

  const showModal = () => {
    setIsModalOpen(true)
  }

  const loadSku = () => {
    axios.get("/api/master/sku").then((res) => {
      setSkus(res.data.data)
    })
  }

  useEffect(() => {
    loadSku()
  }, [])

  const handleCancel = () => {
    setIsModalOpen(false)
    setIsFilter(false)
    setFilter({
      channel: null,
      sku: null,
      tanggal_transaksi: null,
      status: null,
    })
  }

  const handleChange = (value, field) => {
    setIsFilter(true)
    setFilter({ ...filter, [field]: value })
  }

  const clearFilter = () => {
    setIsModalOpen(false)
    setIsFilter(false)
    setFilter({
      channel: null,
      sku: null,
      tanggal_transaksi: null,
      status: null,
    })
    handleOk({})
  }

  return (
    <div>
      {isFilter ? (
        <button
          onClick={() => clearFilter()}
          className="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
        >
          <CloseCircleFilled />
          <span className="ml-2">Clear Filter</span>
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
          setIsModalOpen(false)
        }}
        onCancel={handleCancel}
      >
        <div>
          <div>
            <label htmlFor="">SKU</label>
            <Select
              allowClear
              className="w-full mb-2"
              placeholder="Pilih SKU"
              onChange={(e) => handleChange(e, "sku")}
            >
              {skus.map((item) => (
                <Select.Option key={item.sku} value={item.sku}>
                  {item.sku}
                </Select.Option>
              ))}
            </Select>
          </div>
          <div>
            <label htmlFor="">Channel</label>
            <Select
              mode="multiple"
              allowClear
              className="w-full mb-2"
              placeholder="Pilih Channel"
              onChange={(e) => handleChange(e, "channel")}
            >
              {channelId.map((item) => (
                <Select.Option key={item} value={item}>
                  {item}
                </Select.Option>
              ))}
            </Select>
          </div>
          {/* <Input placeholder="Store" className="mb-2" /> */}

          <div>
            <label htmlFor="">Status</label>
            <Select
              mode="multiple"
              allowClear
              className="w-full mb-2"
              placeholder="Pilih Order Status"
              onChange={(e) => handleChange(e, "status")}
            >
              {orderStatus.map((item) => (
                <Select.Option key={item.value} value={item.value}>
                  {item.name}
                </Select.Option>
              ))}
            </Select>
          </div>
          <div className="w-full mb-2">
            <label htmlFor="">Tanggal Transaksi</label>
            <RangePicker
              className="w-full"
              format={"YYYY-MM-DD"}
              onChange={(e, dateString) =>
                handleChange(dateString, "tanggal_transaksi")
              }
            />
          </div>
          <div className="w-full mb-2">
            <label htmlFor="">Status Submit</label>
            <Select
              allowClear
              className="w-full mb-2"
              placeholder="Pilih Status Submit"
              onChange={(e) => handleChange(e, "status_submit")}
            >
              <Select.Option value={"submited"}>Submitted</Select.Option>
              <Select.Option value={"notsubmited"}>Not Submitted</Select.Option>
            </Select>
          </div>
          <div className="w-full mb-2">
            <label htmlFor="">Waktu Transaksi</label>
            <TimePicker.RangePicker
              defaultValue={moment(new Date(), "h:mm A")}
              className="w-full"
              format={"h:mm A"}
              onChange={(e, dateString) =>
                handleChange(dateString, "waktu_transaksi")
              }
            />
          </div>
        </div>
      </Modal>
    </div>
  )
}

export default FilterModal
