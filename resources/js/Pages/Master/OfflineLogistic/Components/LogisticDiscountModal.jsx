import { TagOutlined } from "@ant-design/icons"
import { DatePicker, Form, Input, Modal, Select } from "antd"
import axios from "axios"
import moment from "moment"
import React, { useEffect, useState } from "react"
import { toast } from "react-toastify"

const LogisticDiscountModal = ({ logisticId }) => {
  const [form] = Form.useForm()
  const [open, setOpen] = useState(false)
  const [confirmLoading, setConfirmLoading] = useState(false)

  const getDiscountSet = () => {
    axios
      .get("/api/master/online-logistic/rates/discount/" + logisticId)
      .then((res) => {
        const { data } = res.data
        form.setFieldsValue({
          ...data,
          shipping_price_discount_start: moment(
            data.shipping_price_discount_start || new Date(),
            "YYYY-MM-DD HH:mm:ss"
          ),
          shipping_price_discount_end: moment(
            data.shipping_price_discount_end || new Date(),
            "YYYY-MM-DD HH:mm:ss"
          ),
        })
      })
  }

  const onFinish = (values) => {
    setConfirmLoading(true)
    axios
      .post("/api/master/online-logistic/rates/discount/save", {
        ...values,
        logistic_rate_id: logisticId,
        shipping_price_discount_start:
          values.shipping_price_discount_start.format("YYYY-MM-DD HH:mm:ss"),
        shipping_price_discount_end: values.shipping_price_discount_end.format(
          "YYYY-MM-DD HH:mm:ss"
        ),
      })
      .then((res) => {
        setConfirmLoading(false)
        form.resetFields()
        setOpen(false)
        toast.success("Data Diskon berhasil disimpan")
      })
      .catch((err) => {
        setConfirmLoading(false)
        toast.error("Diskon gagal Di simpan")
      })
  }

  return (
    <div>
      <button
        onClick={() => {
          setOpen(true)
          getDiscountSet()
        }}
        className="text-white bg-[#008BE1] hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
      >
        <TagOutlined />
        <span className="ml-2">Discount</span>
      </button>

      <Modal
        title="Set Diskon"
        visible={open}
        onOk={() => {
          form.submit()
        }}
        cancelText={"Cancel"}
        onCancel={() => setOpen(false)}
        okText={"Save"}
        confirmLoading={confirmLoading}
      >
        <Form
          form={form}
          name="basic"
          layout="vertical"
          onFinish={onFinish}
          autoComplete="off"
        >
          <Form.Item
            label="Discount Amount"
            name="shipping_price_discount"
            rules={[
              {
                required: true,
                message: "Silakan masukkan Discount Amount!",
              },
              {
                pattern: /^[0-9]+$/,
                message: "Discount Amount harus berupa angka",
              },
              {
                validator: (_, value) =>
                  value && value < 1
                    ? Promise.reject(
                        new Error("Discount Amount tidak boleh kurang dari 1")
                      )
                    : Promise.resolve(),
              },
            ]}
          >
            <Input placeholder="Ketik Discount Amount" />
          </Form.Item>
          <Form.Item
            label="Start Date"
            name="shipping_price_discount_start"
            rules={[
              {
                required: true,
                message: "Silakan pilih Start Date!",
              },
            ]}
          >
            <DatePicker
              className="w-full"
              showTime
              format="DD-MM-YYYY HH:mm:ss"
            />
          </Form.Item>
          <Form.Item
            label="End Date"
            name="shipping_price_discount_end"
            rules={[
              {
                required: true,
                message: "Silakan pilih End Date!",
              },
            ]}
          >
            <DatePicker
              className="w-full"
              showTime
              format="DD-MM-YYYY HH:mm:ss"
            />
          </Form.Item>
          <Form.Item
            label="Status"
            name="shipping_price_discount_status"
            rules={[
              {
                required: true,
                message: "Silakan pilih Status!",
              },
            ]}
          >
            <Select placeholder="Pilih Status">
              <Select.Option value={1}>Active</Select.Option>
              <Select.Option value={0}>Non Active</Select.Option>
            </Select>
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

export default LogisticDiscountModal
