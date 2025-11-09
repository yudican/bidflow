import { FilterFilled, FilterOutlined } from "@ant-design/icons"
import { Form, Modal, Select } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { getItem } from "../../../../helpers"
const FilterModal = ({ handleOk }) => {
  const [form] = Form.useForm()
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [isFilter, setIsFilter] = useState(false)
  const [filter, setFilter] = useState({
    status: [],
    package_id: [],
    variant_id: [],
    sku: [],
    sales_channel: null,
  })
  const [skus, setSkus] = useState([])
  const [loading, setLoading] = useState(false)
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
    const filterItem = getItem("variantFilter", true)
    if (filterItem) {
      setIsFilter(true)
      setFilter(filterItem)
    }
  }, [])

  // console.log(filter)

  const showModal = () => {
    setIsModalOpen(true)
  }

  const handleCancel = () => {
    setIsModalOpen(false)
    setIsFilter(false)
    setFilter({
      status: [],
      package_id: [],
      variant_id: [],
      sku: [],
      sales_channel: null,
    })
  }

  const handleChange = (value, field) => {
    setFilter((filters) => ({ ...filters, [field]: value }))
  }

  const clearFilter = () => {
    setIsModalOpen(false)
    setIsFilter(false)
    setFilter({
      status: [],
      package_id: [],
      variant_id: [],
      sku: [],
      sales_channel: null,
    })
    localStorage.removeItem("variantFilter")
    handleOk({})
    form.resetFields()
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
          form.submit()
        }}
        cancelText={isFilter ? "Clear Filter" : "Cancel"}
        onCancel={isFilter ? clearFilter : handleCancel}
        okText={"Apply Filter"}
      >
        <Form
          form={form}
          name="basic"
          layout="vertical"
          onFinish={(value) => {
            handleOk(value)
            setIsFilter(true)
            setIsModalOpen(false)
            localStorage.setItem("variantFilter", JSON.stringify(value))
          }}
          // onFinishFailed={onFinishFailed}
          autoComplete="off"
        >
          <Form.Item label="Status" name="status">
            <Select
              allowClear
              className="w-full mb-2"
              placeholder="Pilih Status"
            >
              <Select.Option key={1} value={1}>
                Active
              </Select.Option>
              <Select.Option key={0} value={0}>
                Non Active
              </Select.Option>
            </Select>
          </Form.Item>

          <Form.Item label="Package" name="package_id">
            <Select
              allowClear
              className="w-full mb-2"
              placeholder="Pilih Package"
            >
              {packages.map((item) => (
                <Select.Option key={item.id} value={item.id}>
                  {item.name}
                </Select.Option>
              ))}
            </Select>
          </Form.Item>

          <Form.Item label="Variant" name="variant_id">
            <Select
              allowClear
              className="w-full mb-2"
              placeholder="Pilih Variant"
            >
              {variants.map((item) => (
                <Select.Option key={item.id} value={item.id}>
                  {item.name}
                </Select.Option>
              ))}
            </Select>
          </Form.Item>

          {/* <Form.Item label="SKU Master" name="sku">
            <Select
              allowClear
              className="w-full mb-2"
              placeholder="Pilih SKU Master"
            >
              {skus.map((item) => (
                <Select.Option key={item.sku} value={item.sku}>
                  {item.sku}
                </Select.Option>
              ))}
            </Select>
          </Form.Item> */}

          <Form.Item label="SKU Master" name="sku">
            {loading ? (
              <Skeleton.Input active size="default" block />
            ) : (
              <Select
                allowClear
                className="w-full"
                placeholder="Pilih SKU"
                showSearch
                filterOption={(input, option) => {
                  return (option?.children ?? "")
                    .toLowerCase()
                    .includes(input.toLowerCase())
                }}
                onChange={(e) => {
                  const packageSelected = skus.find((item) => item.sku === e)
                  form.setFieldsValue({
                    package_id: packageSelected?.package_id,
                  })
                }}
              >
                {skus.map((item) => (
                  <Select.Option key={item.id} value={item.sku}>
                    {`${item.sku}`}
                  </Select.Option>
                ))}
              </Select>
            )}
          </Form.Item>

          <Form.Item label="Sales Channel" name="sales_channel">
            <Select
              allowClear
              className="w-full mb-2"
              placeholder="Pilih Sales Channel"
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
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

export default FilterModal
