import { Form, Input, Modal } from "antd"
import axios from "axios"
import React, { useState } from "react"
import { toast } from "react-toastify"

const ModalCancelOrder = ({
  title = "Konfirmasi Pembatalan",
  children,
  transactions_id = null,
  refetch,
  onConfirm,
}) => {
  const [form] = Form.useForm()
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [loading, setLoading] = useState(false)

  const showModal = () => {
    setIsModalOpen(true)
  }

  const onFinish = (value) => {
    setLoading(true)
    const body = {
      ...value,
      transactions_id,
    }

    if (onConfirm) {
      setLoading(false)
      setIsModalOpen(false)
      return onConfirm(body)
    }

    axios
      .post("/api/transaction/cancel", body)
      .then((res) => {
        const { message } = res.data
        setLoading(false)
        setIsModalOpen(false)
        toast.success(message)
        refetch()
      })
      .catch((e) => {
        const { message } = e.response.data
        toast.error(message)
        setLoading(false)
      })
  }

  return (
    <div>
      <div className="cursor-pointer" onClick={() => showModal()}>
        {children}
      </div>

      <Modal
        title={title}
        open={isModalOpen}
        onOk={() => {
          form.submit()
        }}
        cancelText={"Tutup"}
        onCancel={() => setIsModalOpen(false)}
        okText={"Konfirmasi"}
        confirmLoading={loading}
      >
        <Form
          form={form}
          name="basic"
          layout="vertical"
          onFinish={onFinish}
          // onFinishFailed={onFinishFailed}
          autoComplete="off"
        >
          {/* note pembatalan */}
          <Form.Item
            label="Alasan Pembatalan"
            name="cancel_note"
            rules={[
              {
                required: true,
                message: "Silakan masukkan Alasan Pembatalan!",
              },
            ]}
          >
            <Input placeholder="Silakan input Alasan Pembatalan.." />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

export default ModalCancelOrder
