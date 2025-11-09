import { FilterFilled, FilterOutlined } from "@ant-design/icons"
import { Form, Modal, Select, DatePicker } from "antd"
import React, { useState } from "react"

const { RangePicker } = DatePicker

const FilterModal = ({ handleOk }) => {
  const [form] = Form.useForm()
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [isFilter, setIsFilter] = useState(false)
  const showModal = () => {
    setIsModalOpen(true)
  }

  const handleCancel = () => {
    setIsModalOpen(false)
    setIsFilter(false)
  }

  const clearFilter = () => {
    form.resetFields()
    setIsModalOpen(false)
    setIsFilter(false)
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
        title="Filter URL Shortener"
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
          autoComplete="off"
        >
          <Form.Item label="Status" name="status">
            <Select
              mode="multiple"
              allowClear
              className="w-full mb-2"
              placeholder="Pilih Status"
            >
              <Select.Option key="active" value="active">
                Active
              </Select.Option>
              <Select.Option key="inactive" value="inactive">
                Inactive
              </Select.Option>
            </Select>
          </Form.Item>

          <Form.Item label="Tanggal Dibuat" name="created_at">
            <RangePicker
              className="w-full"
              placeholder={["Tanggal Mulai", "Tanggal Akhir"]}
              format="YYYY-MM-DD"
            />
          </Form.Item>

          <Form.Item label="Tanggal Kadaluarsa" name="expires_at">
            <RangePicker
              className="w-full"
              placeholder={["Tanggal Mulai", "Tanggal Akhir"]}
              format="YYYY-MM-DD"
              showTime
            />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

export default FilterModal