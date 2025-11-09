import { FilterFilled, FilterOutlined } from "@ant-design/icons"
import { Form, Modal, Select } from "antd"
import { useForm } from "antd/lib/form/Form"
import React, { useEffect, useState } from "react"
const filterData = {
  type: null,
  brand_id: null,
}
const FilterModal = ({ handleOk }) => {
  const [form] = Form.useForm()
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [isFilter, setIsFilter] = useState(false)
  const [filter, setFilter] = useState(filterData)
  const [dataBrand, setDataBrand] = useState([])
  const [loadingBrand, setLoadingBrand] = useState(false)

  const loadBrand = () => {
    setLoadingBrand(true)
    axios
      .get("/api/master/brand")
      .then((res) => {
        setDataBrand(res.data.data)
        setLoadingBrand(false)
      })
      .catch((err) => setLoadingBrand(false))
  }

  useEffect(() => {
    loadBrand()
  }, [])

  const showModal = () => {
    setIsModalOpen(true)
  }

  const handleCancel = () => {
    setIsModalOpen(false)
    setIsFilter(false)
    setFilter(filterData)
  }

  const handleChange = (value, field) => {
    setFilter((filters) => ({ ...filters, [field]: value }))
  }

  const clearFilter = () => {
    form.resetFields()
    setIsModalOpen(false)
    setIsFilter(false)
    setFilter(filterData)
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
          }}
          // onFinishFailed={onFinishFailed}
          autoComplete="off"
        >
          <Form.Item label="Brand" name="brand_id">
            <Select
              mode="multiple"
              allowClear
              className="w-full mb-2"
              placeholder="Pilih Brand"
              loading={loadingBrand}
              onChange={(e) => handleChange(e, "brand_id")}
            >
              {dataBrand.map((item) => (
                <Select.Option key={item.id} value={item.id}>
                  {item.name}
                </Select.Option>
              ))}
            </Select>
          </Form.Item>
          <Form.Item label="Tipe" name="type">
            <Select
              allowClear
              className="w-full mb-2"
              placeholder="Pilih Tipe"
              onChange={(e) => handleChange(e, "type")}
            >
              <Select.Option key={"product"} value={"product"}>
                Per Product
              </Select.Option>
              <Select.Option key={"transaction"} value={"transaction"}>
                Per Transaction
              </Select.Option>
              <Select.Option key={"referral"} value={"referral"}>
                Referral
              </Select.Option>
              <Select.Option key={"barcode"} value={"barcode"}>
                QR Code
              </Select.Option>
            </Select>
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

export default FilterModal
