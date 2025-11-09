import { FilterFilled, FilterOutlined } from "@ant-design/icons"
import { DatePicker, Form, Modal, Select } from "antd"
import React, { useEffect, useState } from "react"
import { formatDate } from "../../../helpers"

const { RangePicker } = DatePicker

const FilterModal = ({ handleOk, isFiltered }) => {
  const [form] = Form.useForm()
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [isFilter, setIsFilter] = useState(isFiltered)

  useEffect(() => {
    setIsFilter(isFiltered)

    return () => {
      setIsFilter(false)
    }
  }, [isFiltered])

  const showModal = () => {
    setIsModalOpen(true)
  }

  const handleCancel = () => {
    handleOk({})
    form.resetFields()
    setIsModalOpen(false)
    setIsFilter(false)
  }

  const clearFilter = () => {
    handleOk({})
    form.resetFields()
    setIsModalOpen(false)
    setIsFilter(false)
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
        maskClosable={false}
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
            handleOk({
              ...value,
              created_at:
                value?.created_at?.map((item) => formatDate(item)) || null,
            })
            setIsFilter(true)
            setIsModalOpen(false)
          }}
          // onFinishFailed={onFinishFailed}
          autoComplete="off"
        >
          <Form.Item label="Tanggal" name="created_at">
            <RangePicker
              className="w-full"
              // format={"YYYY-MM-DD"}
              format={"DD-MM-YYYY"}
            />
          </Form.Item>
          <Form.Item label="Status" name="status">
            <Select
              mode="multiple"
              allowClear
              className="w-full mb-2"
              placeholder="Pilih Status"
              // onChange={(e) => handleChange(e, "payment_term")}
            >
              <Select.Option value={"success"}>Success</Select.Option>
              <Select.Option value={"failed"}>Failed</Select.Option>
            </Select>
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

export default FilterModal
