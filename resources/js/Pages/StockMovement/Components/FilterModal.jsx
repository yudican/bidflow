import { FilterFilled } from "@ant-design/icons"
import { DatePicker, Modal, Select } from "antd"
import React, { useEffect, useState } from "react"

const { RangePicker } = DatePicker

const FilterModal = ({ handleOk }) => {
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [isFilter, setIsFilter] = useState(false)
  const [warehouses, setWarehouses] = useState([])
  const [filter, setFilter] = useState({
    created_at: null,
    warehouse_id: null,
  })

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
    setFilter({
      created_at: null,
      warehouse_id: null,
    })
  }

  const handleChange = (value, field) => {
    setFilter({ ...filter, [field]: value })
  }

  const clearFilter = () => {
    setIsModalOpen(false)
    setIsFilter(false)
    setFilter({
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
          <div className="w-full mb-2">
            <label htmlFor="">Range Date</label>
            <RangePicker
              className="w-full"
              format={"YYYY-MM-DD"}
              onChange={(e, dateString) =>
                handleChange(dateString, "created_at")
              }
            />
          </div>
          <div>
            <label htmlFor="">Warehouse</label>
            <Select
              allowClear
              className="w-full mb-2"
              placeholder="Pilih Warehouse"
              onChange={(e) => handleChange(e, "warehouse_id")}
            >
              <Select.Option value="">Semua Warehouse</Select.Option>
              {warehouses.map((item) => (
                <Select.Option key={item.id} value={item.id}>
                  {item.name}
                </Select.Option>
              ))}
            </Select>
          </div>
          <div>
            <label htmlFor="">Data Stock</label>
            <Select
              allowClear
              className="w-full mb-2"
              placeholder="Pilih Stock"
              onChange={(e) => handleChange(e, "warehouse_id")}
            >
              <Select.Option value="">All Stock</Select.Option>
              <Select.Option value="">Order</Select.Option>
              <Select.Option value="">Freebies</Select.Option>
            </Select>
          </div>
        </div>
      </Modal>
    </div>
  )
}

export default FilterModal
