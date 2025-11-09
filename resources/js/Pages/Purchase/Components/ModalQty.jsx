import { Form, Input, message, Modal } from "antd"
import React, { useState } from "react"

const ModalQty = ({ url, refetch, initialValues = {} }) => {
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [loading, setLoading] = useState(false)
  const [form] = Form.useForm()

  const showModal = () => {
    setIsModalOpen(true)
  }

  const onFinish = (values) => {
    setLoading(true)
    axios
      .post(url, {
        field: "qty_diterima",
        value: values.qty_diterima,
      })
      .then((res) => {
        setLoading(false)
        setIsModalOpen(false)
        message.success("Data berhasil diupdate")
        refetch()
      })
      .catch((err) => {
        setLoading(false)
        message.error("Data gagal diupdate")
      })
  }

  return (
    <div>
      <Input
        onClick={() => showModal()}
        value={initialValues?.qty_diterima}
        readOnly
      />

      <Modal
        title="Update Qty Diterima"
        open={isModalOpen}
        onOk={() => {
          form.submit()
        }}
        cancelText={"Batal"}
        onCancel={() => setIsModalOpen(false)}
        okText={"Simpan"}
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
            name="qty_diterima"
            rules={[
              {
                required: false,
                message: "Silakan masukkan Qty!",
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

export default ModalQty
