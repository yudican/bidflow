import { Form, Input, Modal } from "antd"
import axios from "axios"
import React, { useState } from "react"
import { toast } from "react-toastify"

const ModalUpdateResi = ({ id_transaksi, onSuccess, children }) => {
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
      transaction_id: id_transaksi,
    }

    axios
      .post("/api/transaction/update/resi", body)
      .then((res) => {
        const { message } = res.data
        setLoading(false)
        setIsModalOpen(false)
        toast.success(message)
        onSuccess()
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
        title={"Ubah No Resi"}
        open={isModalOpen}
        onCancel={() => setIsModalOpen(false)}
        onOk={() => form.submit()}
        okText={"Simpan"}
        cancelText={"Batal"}
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
            label="Nomor Resi"
            name="resi"
            rules={[
              {
                required: true,
                message: "Silakan masukkan Nomor Resi!",
              },
            ]}
          >
            <Input placeholder="Silakan input Nomor Resi.." />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

export default ModalUpdateResi
