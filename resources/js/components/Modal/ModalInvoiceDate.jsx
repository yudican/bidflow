import { EditOutlined } from "@ant-design/icons"
import { DatePicker, Form, message, Modal } from "antd"
import axios from "axios"
import moment from "moment"
import React, { useState } from "react"
import { useUpdateInvoiceDateMutation } from "../../configs/Redux/Services/salesOrderService"

const ModalInvoiceDate = ({
  url,
  refetch,
  initialValues = {},
  disabled = false,
  value = null,
}) => {
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [form] = Form.useForm()

  const [updateInvoiceDate, { isLoading: loading }] =
    useUpdateInvoiceDateMutation()

  const showModal = () => {
    setIsModalOpen(true)
  }

  const onFinish = (values) => {
    updateInvoiceDate({ url, body: { ...initialValues, ...values } }).then(
      ({ error }) => {
        if (error) {
          return message.error("Invoice Date gagal diupdate")
        }
        setIsModalOpen(false)
        refetch()
        return message.success("Invoice Date berhasil diupdate")
      }
    )
  }

  if (disabled) {
    return <strong>: -</strong>
  }

  return (
    <div>
      <div className="flex items-center">
        <strong className="mr-2">
          : {moment(value).format("DD-MM-YYYY") || "-"}
        </strong>
        <EditOutlined onClick={() => showModal()} />
      </div>

      <Modal
        title="Invoice Date"
        open={isModalOpen}
        onOk={() => {
          form.submit()
        }}
        cancelText={"Batal"}
        onCancel={() => setIsModalOpen(false)}
        okText={"Invoice Date"}
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
            label="Invoice Date"
            name="invoice_date"
            rules={[
              {
                required: false,
                message: "Silakan masukkan Invoice Date!",
              },
            ]}
          >
            <DatePicker className="w-full" />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

export default ModalInvoiceDate
