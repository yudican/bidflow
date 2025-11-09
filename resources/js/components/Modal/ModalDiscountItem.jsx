import { EditOutlined } from "@ant-design/icons"
import { Form, message, Modal, Select } from "antd"
import React, { useState } from "react"

const ModalDiscountItem = ({
  url,
  refetch,
  initialValues = {},
  disabled = false,
  value = 0,
  title = "Update Discount",
  prefix = false,
  discounts = [],
}) => {
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [loading, setLoading] = useState(false)
  const [form] = Form.useForm()

  const showModal = () => {
    form.setFieldsValue(initialValues)
    setIsModalOpen(true)
  }

  const onFinish = (values) => {
    setLoading(true)
    axios
      .post(url, values)
      .then((res) => {
        setLoading(false)
        setIsModalOpen(false)
        message.success("Diskon berhasil diupdate")
        refetch()
      })
      .catch((err) => {
        setLoading(false)
        message.error("Diskon gagal diupdate")
      })
  }

  if (disabled) {
    return <strong>{value}</strong>
  }

  return (
    <div>
      <div className="flex items-center">
        <strong className="mr-2">
          {prefix}
          {value}
        </strong>
        <EditOutlined onClick={() => showModal()} />
      </div>

      <Modal
        title={title}
        open={isModalOpen}
        onOk={() => {
          form.submit()
        }}
        cancelText={"Cancel"}
        onCancel={() => setIsModalOpen(false)}
        okText={title}
        confirmLoading={loading}
      >
        <Form
          form={form}
          name="basic"
          layout="vertical"
          onFinish={onFinish}
          autoComplete="off"
        >
          <Form.Item
            label="Diskon"
            name="discount_id"
            rules={[
              {
                required: true,
                message: "Silakan Pilih Diskon!",
              },
            ]}
          >
            <Select
              allowClear
              className="w-full mt-4"
              placeholder="Pilih Discount"
            >
              {discounts.map((item) => (
                <Select.Option value={item?.id}>{item?.title}</Select.Option>
              ))}
            </Select>
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

export default ModalDiscountItem
