import { FilterFilled, FilterOutlined } from "@ant-design/icons"
import { Modal, Select } from "antd"
import React, { useEffect, useState } from "react"
const FilterModal = ({ handleOk }) => {
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [isFilter, setIsFilter] = useState(false)
  const [filter, setFilter] = useState({
    status: null,
    package_id: null,
    variant_id: null,
    sku: null,
  })
  const [skus, setSkus] = useState([])

  const [packages, setPackages] = useState([])
  const [variants, setDataVariants] = useState([])

  const loadPackages = () => {
    axios.get("/api/master/package").then((res) => {
      const { data } = res.data
      setPackages(data)
    })
  }

  const loadVariants = () => {
    axios.get("/api/master/variant").then((res) => {
      const { data } = res.data
      setDataVariants(data)
    })
  }

  const loadSku = () => {
    axios.get("/api/master/sku").then((res) => {
      setSkus(res.data.data)
    })
  }

  useEffect(() => {
    loadPackages()
    loadVariants()
    loadSku()
  }, [])

  const showModal = () => {
    setIsModalOpen(true)
  }

  const handleCancel = () => {
    setIsModalOpen(false)
    setIsFilter(false)
    setFilter({
      status: null,
      package_id: null,
      variant_id: null,
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
      package_id: null,
      variant_id: null,
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
            <label htmlFor="">Status</label>
            <Select
              mode="multiple"
              allowClear
              className="w-full mb-2"
              placeholder="Pilih Status"
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
            <label htmlFor="">Package</label>
            <Select
              mode="multiple"
              allowClear
              className="w-full mb-2"
              placeholder="Pilih Package"
              onChange={(e) => handleChange(e, "package_id")}
            >
              {packages.map((item) => (
                <Select.Option key={item.id} value={item.id}>
                  {item.name}
                </Select.Option>
              ))}
            </Select>
          </div>

          <div>
            <label htmlFor="">Variant</label>
            <Select
              mode="multiple"
              allowClear
              className="w-full mb-2"
              placeholder="Pilih Variant"
              onChange={(e) => handleChange(e, "variant_id")}
            >
              {variants.map((item) => (
                <Select.Option key={item.id} value={item.id}>
                  {item.name}
                </Select.Option>
              ))}
            </Select>
          </div>

          <div>
            <label htmlFor="">SKU</label>
            <Select
              mode="multiple"
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
            <label htmlFor="">Sales Channel</label>
            <Select
              allowClear
              className="w-full mb-2"
              placeholder="Pilih Sales Channel"
              onChange={(e) => handleChange(e, "sales_channel")}
            >
              <Select.Option value={"customer-portal"}>
                Customer Portal
              </Select.Option>
              <Select.Option value={"agent-portal"}>Agent Portal</Select.Option>
              <Select.Option value={"sales-offline"}>
                Sales Offline
              </Select.Option>
              <Select.Option value={"marketplace"}>Marketplace</Select.Option>
            </Select>
          </div>
        </div>
      </Modal>
    </div>
  )
}

export default FilterModal
