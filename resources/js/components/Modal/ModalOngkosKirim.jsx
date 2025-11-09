import { EditOutlined } from "@ant-design/icons"
import { Form, Input, message, Modal } from "antd"
import React, { useState } from "react"
import { formatNumber } from "../../helpers"

const ModalOngkosKirim = ({
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
        setLoading(false)
        setIsModalOpen(false)
        message.success("Ongkos kirim berhasil diupdate")
        refetch()
      })
      .catch((err) => {
        setLoading(false)
        message.error("Ongkos kirim gagal diupdate")
      })
  }

  const deleteOngkir = () => {
    setLoading(true)
    axios
      .post(url, { ongkir: 0 })
      .then((res) => {
        setLoading(false)
        setIsModalOpen(false)
        message.success("Ongkos kirim berhasil diupdate")
        refetch()
      })
      .catch((err) => {
        setLoading(false)
        message.error("Ongkos kirim gagal diupdate")
      })
  }

  if (disabled) {
    return <strong>{initialValues?.ongkir}</strong>
  }

  return (
    <div>
      <div className="flex justify-between items-center">
        <strong>{formatNumber(initialValues?.ongkir, "Rp ")}</strong>
        <EditOutlined onClick={() => showModal()} />
      </div>

      <Modal
        title="Update Ongkir"
        open={isModalOpen}
        onOk={() => {
          form.submit()
        }}
        cancelText={"Hapus Ongkir"}
        onCancel={() => deleteOngkir()}
        okText={"Update Ongkir"}
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
            label="Ongkir"
            name="ongkir"
            rules={[
              {
                required: false,
                message: "Silakan masukkan Ongkos Kirim!",
              },
              {
                pattern: /^[0-9]+$/,
                message: "Ongkos Kirim harus berupa angka",
              },
              {
                validator: (_, value) =>
                  value && value < 0
                    ? Promise.reject(
                        new Error("Ongkos Kirim tidak boleh kurang dari 0")
                      )
                    : Promise.resolve(),
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
                form.setFieldValue("ongkir", value.match(/^[0-9]*$/))
              }}
            />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

export default ModalOngkosKirim
