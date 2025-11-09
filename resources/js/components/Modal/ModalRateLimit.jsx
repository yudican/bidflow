import { EditOutlined } from "@ant-design/icons"
import { Form, Input, message, Modal } from "antd"
import React, { useState } from "react"
import { formatNumber } from "../../helpers"

const ModalRateLimit = ({
  url,
  refetch,
  initialValues = {},
  disabled = false,
  rateValue,
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
        setLoading(false)
        setIsModalOpen(false)
        message.success("Rate Limit berhasil disimpan")
        refetch()
      })
      .catch((err) => {
        setLoading(false)
        message.error("Rate Limit gagal disimpan")
      })
  }

  const deleteRateLimit = () => {
    setLoading(true)
    axios
      .post(url, { rate_limit: 0 })
      .then((res) => {
        setLoading(false)
        setIsModalOpen(false)
        message.success("Rate Limit berhasil Dihapus")
        refetch()
      })
      .catch((err) => {
        setLoading(false)
        message.error("Rate Limit gagal Dihapus")
      })
  }

  if (disabled) {
    return <strong>RateLimit</strong>
  }

  return (
    <div>
      <div className="list-group-item d-flex justify-content-between align-items-center ">
        Rate Limit
        <span>
          <span>{formatNumber(rateValue, "Rp ") || 0} </span>
          <EditOutlined onClick={() => showModal()} />
        </span>
      </div>

      <Modal
        title="Update Rate Limit"
        open={isModalOpen}
        onOk={() => {
          form.submit()
        }}
        cancelText={"Hapus Rate Limit"}
        onCancel={() => deleteRateLimit()}
        okText={"Update Rate Limit"}
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
            label="Rate Limit"
            name="rate_limit"
            rules={[
              {
                required: false,
                message: "Silakan masukkan Rate Limit!",
              },
            ]}
          >
            <Input type="number" />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

export default ModalRateLimit
