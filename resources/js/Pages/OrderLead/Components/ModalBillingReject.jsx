import { CloseOutlined } from "@ant-design/icons"
import { Form, Input, Modal } from "antd"
import { useForm } from "antd/es/form/Form"
import TextArea from "antd/lib/input/TextArea"
import React, { useState } from "react"
import { getItem } from "../../../helpers"
import "../../../index.css"
const ModalBillingReject = ({ handleClick, user }) => {
  const [form] = useForm()
  const [isModalOpen, setIsModalOpen] = useState(false)

  const showModal = () => {
    setIsModalOpen(true)
  }

  const handleCancel = () => {
    setIsModalOpen(false)
  }
  const isFinance = getItem("role") === "finance"
  return (
    <div>
      <button
        onClick={() => showModal()}
        className="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
        title="Reject"
      >
        <CloseOutlined />
      </button>

      <Modal
        title="Confirm Reject"
        open={isModalOpen}
        onOk={() => {
          form.submit()
        }}
        cancelText={"Cancel"}
        onCancel={handleCancel}
        okText={"Simpan"}
      >
        <Form
          form={form}
          name="basic"
          layout="vertical"
          onFinish={handleClick}
          initialValues={{
            user_approval: user?.name,
          }}
          autoComplete="off"
        >
          {isFinance && (
            <Form.Item label="User Approval" name="user_approval">
              <Input disabled />
            </Form.Item>
          )}
          <Form.Item
            label="Notes"
            name="notes"
            rules={[
              {
                required: true,
                message: "Field Tidak Boleh Kosong!",
              },
            ]}
          >
            <TextArea />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

export default ModalBillingReject
