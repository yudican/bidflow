import { PlusOutlined } from "@ant-design/icons"
import { Form, Modal, Select, Table, message } from "antd"
import React, { useState } from "react"
import OrderNumberModal from "./OrderNumberModal"
import axios from "axios"
import { orderDeliveryColumns } from "../config"
import { inArray } from "../../../helpers"
import { toast } from "react-toastify"

const OrderInvoiceFormModal = ({ handleOk }) => {
  const [form] = Form.useForm()
  const userData = JSON.parse(localStorage.getItem("user_data"))
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [type, setType] = useState(null)
  const [orderDelivery, setOrderDelivery] = useState([])
  const [loadingDelivery, setLoadingDelivery] = useState(false)
  const [selectedRowKeys, setSelectedRowKeys] = useState(null)
  const [uidLead, setUidLead] = useState(null)

  const showModal = () => {
    setIsModalOpen(true)
  }

  const handleCancel = () => {
    setIsModalOpen(false)
  }

  const loadOrderDelivery = (uid_lead) => {
    setLoadingDelivery(true)
    axios
      .get("/api/order/invoice/delivery/" + uid_lead)
      .then((res) => {
        console.log(res.data.data, "res.data.data")
        setOrderDelivery(res.data.data)
        setLoadingDelivery(false)
      })
      .catch((err) => setLoadingDelivery(false))
  }

  const rowSelection = {
    selectedRowKeys,
    onChange: (newSelectedRowKeys) => {
      return setSelectedRowKeys(newSelectedRowKeys)
    },
    getCheckboxProps: (record) => ({
      disabled:
        inArray(record.is_invoice, [1]) || inArray(record?.status, ["cancel"]), // Column configuration not to be checked
    }),
  }

  const insertInvoice = (value) => {
    if (selectedRowKeys && selectedRowKeys.length < 1) {
      return toast.error("Silahkan pilih pengiriman dulu")
    }
    axios
      .post(`/api/order/invoice/submit`, {
        ...value,
        uid_lead: uidLead,
        is_invoice: 1,
        items: selectedRowKeys,
      })
      .then((res) => {
        handleOk()
        setIsModalOpen(false)
        return message.success("Data Invoice berhasil Diproses!")
      })
  }

  return (
    <div>
      <button
        onClick={() => showModal()}
        className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
      >
        <PlusOutlined />
        <span className="ml-2">Tambah Data</span>
      </button>

      <Modal
        maskClosable={false}
        title="Create Invoice"
        open={isModalOpen}
        okText="Pilih"
        cancelText="Batal"
        okButtonProps={{
          style: { width: "70px" },
          disabled: selectedRowKeys?.length < 1,
        }}
        cancelButtonProps={{ style: { width: "70px" } }}
        onOk={() => {
          form.submit()
          // setIsModalOpen(false)
        }}
        onCancel={handleCancel}
        width={1000}
      >
        <Form
          form={form}
          name="basic"
          layout="vertical"
          onFinish={(value) => insertInvoice(value)}
          autoComplete="off"
        >
          <Form.Item
            label="Type SO"
            name="type_so"
            rules={[
              {
                required: true,
                message: "Field Tidak Boleh Kosong!",
              },
            ]}
          >
            <Select
              onChange={(value) => {
                setType(value)
                form.setFieldValue("so_number", null)
              }}
              placeholder={"Pilih SO"}
            >
              <Select value={"order-manual"}>Order Manual</Select>
              <Select value={"order-lead"}>Order Lead</Select>
              <Select value={"freebies"}>Freebies</Select>
              <Select value={"order-konsinyasi"}>Konsinyasi</Select>
            </Select>
          </Form.Item>
          {type && (
            <Form.Item
              label="Pilih No SO"
              name="so_number"
              rules={[
                {
                  required: true,
                  message: "Field Tidak Boleh Kosong!",
                },
              ]}
            >
              <OrderNumberModal
                handleOk={(value) => {
                  form.setFieldValue("so_number", value?.value)
                  loadOrderDelivery(value?.uid_lead)
                  setUidLead(value?.uid_lead)
                }}
                type={type}
              />
            </Form.Item>
          )}

          {orderDelivery && orderDelivery.length > 0 && (
            <div>
              <h1 className="header-title">Informasi Pengiriman</h1>
              <Table
                scroll={{ x: "max-content" }}
                tableLayout={"auto"}
                className="mb-4"
                dataSource={orderDelivery}
                rowSelection={rowSelection}
                columns={orderDeliveryColumns}
                loading={loadingDelivery}
                pagination={false}
                rowKey="id"
              />
            </div>
          )}
        </Form>
      </Modal>
    </div>
  )
}

export default OrderInvoiceFormModal
