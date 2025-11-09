import { EditOutlined } from "@ant-design/icons"
import { Form, Input, message, Modal } from "antd"
import React, { useState } from "react"

const ModalPriceRequisition = ({
  url,
  refetch,
  initialValues = {},
  disabled = false,
  value = 0,
  title = "Update Item Price",
  prefix = false,
}) => {
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [loading, setLoading] = useState(false)
  const [form] = Form.useForm()

  const showModal = () => {
    setIsModalOpen(true)
  }

  const onFinish = (values) => {
    if (!values?.item_price || values?.item_price == "") {
      return message.error("Harga tidak sesuai")
    }
    const price = parseInt(values?.item_price?.match(/^[0-9]*$/))
    if (price < 1) {
      return message.error("Harga tidak sesuai")
    }

    if (parseInt(values?.item_price) == 0 || isNaN(price)) {
      return message.error("Harga tidak sesuai")
    }

    setLoading(true)
    axios
      .post(url, values)
      .then((res) => {
        setLoading(false)
        setIsModalOpen(false)
        message.success("Price Item berhasil diupdate")
        refetch()
      })
      .catch((err) => {
        console.log(err)
        setLoading(false)
        message.error("Price Item gagal diupdate")
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
          initialValues={{
            ...initialValues,
          }}
        >
          <Form.Item
            label="Price"
            name="item_price"
            rules={[
              {
                required: false,
                message: "Silakan masukkan Price!",
              },
            ]}
          >
            <Input
              onChange={(e) => {
                const { value } = e.target
                // check if value is 0-9
                if (value === "" || !value) {
                  return null
                }

                if (parseInt(value) < 1) {
                  return null
                }
                form.setFieldValue(value.match(/^[0-9]*$/))
              }}
            />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

export default ModalPriceRequisition
