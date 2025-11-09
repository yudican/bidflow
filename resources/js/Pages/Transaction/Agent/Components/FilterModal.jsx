import { FilterFilled, FilterOutlined } from "@ant-design/icons"
import { DatePicker, Form, Modal, Select } from "antd"
import axios from "axios"
import React, { useState } from "react"
import { getItem } from "../../../../helpers"

const { RangePicker } = DatePicker
const FilterModal = ({ handleOk, showCreatedBy }) => {
  const [form] = Form.useForm()
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [isFilter, setIsFilter] = useState(false)
  const [logistics, setLogistics] = useState([])
  const [loadingLogistic, setLoadingLogistic] = useState(false)
  const [telmarkUsers, setTelmarkUsers] = useState([])
  const [paymentMethod, setPaymentMethod] = useState([])
  const [loadingPaymentMethod, setLoadingPaymentMethod] = useState(false)
  const [loadingTelmarkUsers, setLoadingTelmarkUsers] = useState(false)

  const showModal = () => {
    setIsModalOpen(true)
    loadEkspedisi()
    loadTelmarkUser()
    loadPaymentMethod()
  }

  const handleCancel = () => {
    setIsModalOpen(false)
    setIsFilter(false)
  }

  const loadEkspedisi = () => {
    setLoadingLogistic(true)
    axios
      .get("/api/master/logistic")
      .then((res) => {
        setLoadingLogistic(false)
        setLogistics(res.data.data)
      })
      .catch((error) => setLoadingLogistic(false))
  }

  const loadTelmarkUser = () => {
    setLoadingTelmarkUsers(true)
    axios
      .post("/api/general/search-telmark-user")
      .then((res) => {
        setLoadingTelmarkUsers(false)
        setTelmarkUsers(res?.data?.data || [])
      })
      .catch((error) => setLoadingTelmarkUsers(false))
  }

  const loadPaymentMethod = () => {
    setLoadingPaymentMethod(true)
    axios
      .get("/api/master/payment-method-list")
      .then((res) => {
        setLoadingPaymentMethod(false)
        setPaymentMethod(res?.data?.data || [])
      })
      .catch((error) => setLoadingPaymentMethod(false))
  }

  const handleChange = (value, field) => {
    // setFilter((filters) => ({ ...filters, [field]: value }))
  }

  const clearFilter = () => {
    setIsModalOpen(false)
    setIsFilter(false)
    form.resetFields()
    handleOk({})
  }
  return (
    <div>
      {isFilter ? (
        <button
          onClick={() => showModal()}
          className="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
        >
          <FilterOutlined />
          <span className="ml-2">Show Filter</span>
        </button>
      ) : (
        <button
          onClick={() => showModal()}
          className="
          bg-white border 
          text-blue-700 hover:text-blue-700/90
          delay-100 ease-in-out
          focus:ring-4 focus:outline-none focus:ring-blue-300 
          font-medium rounded-lg 
          text-sm px-4 py-2 text-center inline-flex items-center
        "
        >
          <FilterFilled />
          <span className="ml-2">Filter</span>
        </button>
      )}

      <Modal
        title="Filter Transaksi"
        open={isModalOpen}
        onOk={() => {
          form.submit()
        }}
        cancelText={isFilter ? "Clear Filter" : "Cancel"}
        onCancel={isFilter ? clearFilter : handleCancel}
        okText={"Apply Filter"}
      >
        <div>
          <Form
            form={form}
            name="basic"
            layout="vertical"
            onFinish={(value) => {
              handleOk(value)
              setIsFilter(true)
              setIsModalOpen(false)
            }}
            // onFinishFailed={onFinishFailed}
            autoComplete="off"
          >
            <Form.Item label="Status Print Label" name="status_label">
              <Select allowClear className="w-full" placeholder="Pilih Status">
                <Select.Option value={10}>Belum Print</Select.Option>
                <Select.Option value={1}>Sudah Print</Select.Option>
              </Select>
            </Form.Item>
            <Form.Item label="Ekspedisi" name="ekspedisi">
              <Select
                allowClear
                className="w-full"
                placeholder="Pilih Ekspedisi"
                loading={loadingLogistic}
              >
                {logistics.map((item) => (
                  <Select.Option value={item?.logistic_name}>
                    {item?.logistic_name}
                  </Select.Option>
                ))}
              </Select>
            </Form.Item>
            <Form.Item label="Metode Pembayaran" name="payment_method_id">
              <Select
                allowClear
                className="w-full"
                placeholder="Pilih Metode Pembayaran"
                loading={loadingPaymentMethod}
                options={paymentMethod.map((item) => {
                  return {
                    label: item?.nama_bank,
                    title: item?.nama_bank,
                    options: item.children.map((row) => {
                      return {
                        label: row.nama_bank,
                        value: row.id,
                      }
                    }),
                  }
                })}
              />
            </Form.Item>

            {showCreatedBy && (
              <Form.Item label="Created By" name="user_create">
                <Select
                  allowClear
                  className="w-full"
                  placeholder="Pilih Created By "
                  loading={loadingTelmarkUsers}
                >
                  {telmarkUsers.map((item) => (
                    <Select.Option value={item?.id}>{item?.name}</Select.Option>
                  ))}
                </Select>
              </Form.Item>
            )}
            <Form.Item label="Created Date" name="created_at">
              <RangePicker className="w-full" format={"DD-MM-YYYY"} />
            </Form.Item>
          </Form>
        </div>
      </Modal>
    </div>
  )
}

export default FilterModal
