import { EditOutlined, PlusOutlined } from "@ant-design/icons"
import { Form, Input, Modal } from "antd"
import axios from "axios"
import React, { useState } from "react"
import { toast } from "react-toastify"
const GpCustomerForm = ({
  refetch,
  initialValues = {},
  update = false,
  url,
}) => {
  const [form] = Form.useForm()
  const [isModalOpen, setIsModalOpen] = useState(false)

  const showModal = () => {
    setIsModalOpen(true)
  }

  const handleSubmit = (value) => {
    axios
      .post(url, { ...initialValues, ...value })
      .then((res) => {
        const { message } = res.data
        form.resetFields()
        setIsModalOpen(false)
        refetch()
        toast.success(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
      .catch((err) => {
        const { message } = err.response.data
      })
  }

  return (
    <div>
      {update ? (
        <button
          onClick={() => showModal()}
          className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mr-2"
        >
          <EditOutlined />
        </button>
      ) : (
        <button
          onClick={() => showModal()}
          className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
        >
          <PlusOutlined />
          <span className="ml-2">Tambah Data</span>
        </button>
      )}

      <Modal
        title={update ? "Edit GP Customer" : "Tambah Data Gp Customer"}
        open={isModalOpen}
        cancelText={"Batal"}
        okText={"Simpan"}
        onCancel={() => setIsModalOpen(false)}
        onOk={() => form.submit()}
        width={1000}
      >
        <Form
          form={form}
          name="basic"
          layout="vertical"
          initialValues={{
            ...initialValues,
          }}
          onFinish={handleSubmit}
          //   onFinishFailed={onFinishFailed}
          autoComplete="off"
        >
          <Form.Item label="Customer ID" name="customer_id">
            <Input />
          </Form.Item>

          <Form.Item label="Customer Name" name="customer_name">
            <Input />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

export default GpCustomerForm
