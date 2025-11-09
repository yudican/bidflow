import { EditOutlined } from "@ant-design/icons"
import { Form, Input, message, Modal } from "antd"
import axios from "axios"
import React, { useState } from "react"

const ModalDoNumber = ({
  url,
  refetch,
  initialValues = {},
  disabled = false,
}) => {
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [loading, setLoading] = useState(false)
  const [form] = Form.useForm()

  const showModal = () => {
    setIsModalOpen(true)
  }

  const onFinish = (values) => {
    setLoading(true)
    axios
      .post(url, values)
      .then((res) => {
        refetch()
        message.success("Doc Number berhasil diupdate")
        setLoading(false)
        setIsModalOpen(false)
      })
      .catch((err) => {
        setLoading(false)
        message.error("Doc Number gagal diupdate")
      })
  }

  return (
    <div>
      <div className="flex justify-between items-center">
        <strong>{initialValues?.do_number} </strong>
        <EditOutlined onClick={() => showModal()} />
      </div>

      <Modal
        title="Update Doc Number"
        open={isModalOpen}
        onOk={() => {
          form.submit()
        }}
        cancelText={"Cancel"}
        onCancel={() => setIsModalOpen(false)}
        okText={"Update Doc Number"}
        confirmLoading={loading}
      >
        <Form
          form={form}
          name="basic"
          layout="vertical"
          onFinish={onFinish}
          autoComplete="off"
          initialValues={{
            ...initialValues,
          }}
        >
          <Form.Item
            name="do_number_exist"
            hidden={true}
            rules={[
              {
                required: false,
                message: "Silakan masukkan Ekspedisi!",
              },
            ]}
          >
            <Input type="text" />
          </Form.Item>

          <Form.Item
            label="Do Number"
            name="do_number"
            rules={[
              {
                required: false,
                message: "Silakan masukkan Ekspedisi!",
              },
            ]}
          >
            <Input type="text" />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

export default ModalDoNumber
