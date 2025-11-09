import { Form, Input, Modal, Select, Table } from "antd"
import React, { useState } from "react"

const productSubmitColumns = [
  {
    title: "No",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Nama Produk",
    dataIndex: "product_name",
    key: "product_name",
  },

  {
    title: "SKU",
    dataIndex: "sku",
    key: "sku",
  },
]

const ModalTax = ({ handleSubmit, products = [], onChange }) => {
  const [form] = Form.useForm()
  const [isModalOpen, setIsModalOpen] = useState(false)

  const showModal = () => {
    setIsModalOpen(true)
    form.setFieldsValue({
      vat_value: 0,
      tax_value: 0,
    })
  }

  const handleCancel = () => {
    setIsModalOpen(false)
    form.resetFields()
  }

  return (
    <div>
      <button
        className="text-white bg-mainColor hover:bg-mainColor/90 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mr-4"
        onClick={showModal}
      >
        <span className="">Submit to GP</span>
      </button>

      <Modal
        title="Input VAT"
        open={isModalOpen}
        onOk={() => {
          setIsModalOpen(false)
          form.submit()
        }}
        onCancel={handleCancel}
        width={800}
      >
        <Form
          form={form}
          name="basic"
          layout="vertical"
          onFinish={handleSubmit}
          autoComplete="off"
        >
          <div>
            {/* <Form.Item
              label="Tax Name"
              name="tax_name"
              rules={[
                {
                  required: true,
                  message: "Silakan masukkan Tax Name!",
                },
              ]}
            >
              <Input placeholder="PPN" />
            </Form.Item> */}
            <Form.Item
              label="Tax Value (%)"
              name="tax_value"
              rules={[
                {
                  required: true,
                  message: "Silakan masukkan Tax Value!",
                },
              ]}
            >
              <Input placeholder="10" />
            </Form.Item>
            <Form.Item
              label="VAT Value (%)"
              name="vat_value"
              rules={[
                {
                  required: true,
                  message: "Silakan masukkan VAT Value!",
                },
              ]}
            >
              <Input placeholder="1.11" />
            </Form.Item>
          </div>
        </Form>

        <Table
          dataSource={products}
          columns={[
            ...productSubmitColumns,
            {
              title: "Warehouse",
              dataIndex: "loc_node",
              key: "loc_node",
              render: (text, record, index) => {
                return (
                  <Select
                    onChange={(e) => onChange(e, index)}
                    placeholder="Pilih Site ID"
                  >
                    <Select.Option value="WH01">WH01</Select.Option>
                    <Select.Option value="WH02">WH02</Select.Option>
                    <Select.Option value="WH03">WH03</Select.Option>
                    <Select.Option value="WH04">WH04</Select.Option>
                    <Select.Option value="WH05">WH05</Select.Option>
                    <Select.Option value="WH06">WH06</Select.Option>
                    <Select.Option value="WH07">WH07</Select.Option>
                  </Select>
                )
              },
            },
          ]}
          pagination={false}
        />
      </Modal>
    </div>
  )
}

export default ModalTax
