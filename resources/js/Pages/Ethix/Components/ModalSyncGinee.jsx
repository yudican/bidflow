import { SyncOutlined } from "@ant-design/icons"
import { DatePicker, Form, Input, Modal } from "antd"
import React, { useState } from "react"

const { RangePicker } = DatePicker
const ModalSyncGinee = ({ handleSubmit }) => {
  const [form] = Form.useForm()
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [dates, setDates] = useState(null)
  const [value, setValue] = useState(null)

  const showModal = () => {
    setIsModalOpen(true)
  }

  const handleCancel = () => {
    setIsModalOpen(false)
    form.resetFields()
  }

  const disabledDate = (current) => {
    return current && current > moment().endOf("day")
  }
  const onOpenChange = (open) => {
    if (open) {
      setDates([null, null])
    } else {
      setDates(null)
    }
  }

  return (
    <div>
      <button
        onClick={showModal}
        className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-4"
      >
        <SyncOutlined />
        <span className="ml-2">Sync Data</span>
      </button>

      <Modal
        title="Sync Ginee"
        open={isModalOpen}
        onOk={() => {
          setIsModalOpen(false)
          form.submit()
        }}
        onCancel={handleCancel}
      >
        {/* alert */}
        <div
          className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"
          role="alert"
        >
          <strong className="font-bold">Perhatian!</strong>
          <span className="block sm:inline">
            Maksimal sync data adalah 15 hari
          </span>
        </div>
        <Form
          form={form}
          name="basic"
          layout="vertical"
          onFinish={handleSubmit}
          autoComplete="off"
        >
          <Form.Item
            label="Start Date"
            name="start_date"
            rules={[
              {
                required: true,
                message: "Silakan masukkan Start Date!",
              },
            ]}
          >
            <RangePicker
              value={dates || value}
              disabledDate={disabledDate}
              onCalendarChange={(val) => setDates(val)}
              onChange={(val) => {
                setValue(val)
              }}
              onOpenChange={onOpenChange}
              className={"w-full"}
            />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

export default ModalSyncGinee
