import { CloseCircleOutlined, CloseOutlined } from "@ant-design/icons"
import { Form, message, Modal } from "antd"
import TextArea from "antd/lib/input/TextArea"
import React, { useState } from "react"

const RejectModal = ({ url, refetch, initialValues = {} }) => {
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [loading, setLoading] = useState(false)
  const [form] = Form.useForm()

  const showModal = () => {
    setIsModalOpen(true)
  }

  const onFinish = (values) => {
    setLoading(true)
    axios
      .post(url, { ...values, ...initialValues })
      .then((res) => {
        setLoading(false)
        setIsModalOpen(false)
        message.success("Reject berhasil")
        refetch()
      })
      .catch((err) => {
        setLoading(false)
        message.error("Reject gagal")
      })
  }

  return (
    <div>
      <button
        onClick={() => showModal()}
        className="mr-4 text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
        title="Reject"
      >
        <CloseCircleOutlined className="md:mr-2" />
        <span className="hidden md:block">Reject</span>
      </button>

      <Modal
        title="Alasan Penolakan"
        open={isModalOpen}
        onOk={() => {
          form.submit()
        }}
        cancelText={"Batal"}
        onCancel={() => setIsModalOpen(false)}
        okText={"Reject"}
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
            label="Anda harus menuliskan alasan penolakan"
            name="note"
            rules={[
              {
                required: false,
                message: "Anda harus menuliskan alasan penolakan!",
              },
            ]}
          >
            <TextArea placeholder="Silakan tulis alasan disini.." />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

export default RejectModal
