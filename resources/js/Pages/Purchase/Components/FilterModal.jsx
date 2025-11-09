import { FilterFilled, FilterOutlined } from "@ant-design/icons"
import { DatePicker, Form, Modal, Select } from "antd"
import moment from "moment"
import React, { useState } from "react"

const { RangePicker } = DatePicker

const FilterModal = ({ handleOk, type = "po" }) => {
  const [form] = Form.useForm()
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [isFilter, setIsFilter] = useState(false)
  const [filter, setFilter] = useState({
    tanggal_transaksi: null,
    status: null,
  })

  const showModal = () => {
    setIsModalOpen(true)
  }

  const handleCancel = () => {
    setIsModalOpen(false)
    setIsFilter(false)
    setFilter({
      tanggal_transaksi: null,
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
    form.resetFields()
    setIsModalOpen(false)
    setIsFilter(false)
    setFilter({
      tanggal_transaksi: null,
      status: null,
    })
    handleOk({})
  }

  return (
    <div>
      {isFilter ? (
        <button
          onClick={() => showModal()}
          className="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center "
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
          {type == "po" && (
            <Form.Item label="Status" name="status">
              <Select
                allowClear
                className="w-full mb-2"
                placeholder="Pilih Status"
              >
                <Select.Option value={10}>Draft</Select.Option>
                <Select.Option value={1}>On Process</Select.Option>
                <Select.Option value={5}>Waiting Approval</Select.Option>
                <Select.Option value={9}>Partial Received</Select.Option>
                <Select.Option value={2}>Delivery</Select.Option>
                <Select.Option value={7}>Complete</Select.Option>
                <Select.Option value={8}>Canceled</Select.Option>
              </Select>
            </Form.Item>
          )}
          {type == "pr" && (
            <Form.Item label="Status" name="status">
              <Select
                allowClear
                className="w-full mb-2"
                placeholder="Pilih Status"
              >
                <Select.Option value={5}>Draft</Select.Option>
                <Select.Option value={10}>Waiting Approval</Select.Option>
                <Select.Option value={1}>On Process</Select.Option>
                <Select.Option value={2}>Complete</Select.Option>
                <Select.Option value={3}>Rejected</Select.Option>
              </Select>
            </Form.Item>
          )}
          {type == "po" && (
            <Form.Item label="Type Pr" name="pr_type">
              <Select
                allowClear
                className="w-full mb-2"
                placeholder="Pilih Type Pr"
              >
                <Select.Option value={"PR"}>PR</Select.Option>
                <Select.Option value={"Non PR"}>Non PR</Select.Option>
              </Select>
            </Form.Item>
          )}

          <Form.Item label="Created Date" name="tanggal_transaksi">
            <RangePicker className="w-full" format={"DD-MM-YYYY"} />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

export default FilterModal
