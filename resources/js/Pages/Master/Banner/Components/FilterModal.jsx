import { FilterFilled, FilterOutlined } from "@ant-design/icons"
import { Form, Modal, Select } from "antd"
import React, { useState } from "react"
const FilterModal = ({ handleOk }) => {
  const [form] = Form.useForm()
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [isFilter, setIsFilter] = useState(false)
  const [filter, setFilter] = useState({
    status: null,
  })

  const showModal = () => {
    setIsModalOpen(true)
  }

  const handleCancel = () => {
    setIsModalOpen(false)
    setIsFilter(false)
    setFilter({
      status: null,
    })
  }

  const handleChange = (value, field) => {
    setFilter((filters) => ({ ...filters, [field]: value }))
  }

  const clearFilter = () => {
    form.resetFields()
    setIsModalOpen(false)
    setIsFilter(false)
    setFilter({
      status: null,
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
        title="Filter Banner"
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
          <Form.Item label="Status" name="status">
            <Select
              mode="multiple"
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
        </Form>
      </Modal>
    </div>
  )
}

export default FilterModal
