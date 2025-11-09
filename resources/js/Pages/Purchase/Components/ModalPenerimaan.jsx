import { EditOutlined, PlusOutlined } from "@ant-design/icons"
import { Form, Input, Menu, Modal, Select } from "antd"
import React, { useState } from "react"
import { toast } from "react-toastify"
import { getItem } from "../../../helpers.jsx"
import axios from "axios"
const ModalPenerimaan = ({
  url,
  refetch,
  initialValues = {},
  products = [],
  update = false,
  detail,
}) => {
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [loading, setLoading] = useState(false)
  const [form] = Form.useForm()
  const { name } = getItem("user_data", true) || {}
  const [productData, setProduct] = useState({})
  const [qtyDiterima, setQtyDiterima] = useState(0)
  const showModal = () => {
    setQtyDiterima(0)
    setProduct({})
    setIsModalOpen(true)
    form.resetFields()
    form.setFieldsValue({ received_by: name })
  }
  const onFinish = (values) => {
    setLoading(true)
    axios
      .post(url, {
        ...productData,
        ...values,
        product_id: values.product_id,
        qty_diterima: values.qty_diterima,
        notes: values.notes,
        price: values.price,
        subtotal: values.subtotal,
      })
      .then((res) => {
        setLoading(false)
        setIsModalOpen(false)
        toast.success(res?.data?.message || "Data berhasil diupdate")
        refetch()
      })
      .catch((err) => {
        setLoading(false)
        toast.error("Data gagal diupdate")
      })
  }

  const productLists = products
  return (
    <div>
      {update ? (
        <Menu.Item icon={<EditOutlined />} onClick={() => showModal()}>
          Ubah
        </Menu.Item>
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
        title="Form Penerimaan Produk"
        open={isModalOpen}
        onOk={() => {
          if (qtyDiterima < 1) {
            return toast.error("Gagal, item yang dipesan sudah diterima")
          }
          return form.submit()
        }}
        cancelText={"Batal"}
        onCancel={() => setIsModalOpen(false)}
        okText={"Simpan"}
        confirmLoading={loading}
        width={1000}
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
          <div className="row">
            <div className="col-md-12">
              <Form.Item
                label={"Pilih Product"}
                name="product_id"
                rules={[
                  {
                    required: false,
                    message: "Silakan masukkan Qty!",
                  },
                ]}
              >
                <Select
                  allowClear
                  className="w-full"
                  placeholder="Pilih Product"
                  disabled={initialValues?.id}
                  onChange={(e) => {
                    const product = productLists.find(
                      (item) => item.product_id === e
                    )
                    const sumQtyDiterima = detail.items
                      .filter((item) => {
                        return (
                          item.product_id === e &&
                          item.purchase_order_id === detail?.id
                        )
                      })
                      .reduce(
                        (a, b) => parseInt(a) + parseInt(b.qty_diterima),
                        0
                      )

                    if (product) {
                      const qty_diterima = product?.qty - sumQtyDiterima
                      setQtyDiterima(qty_diterima)
                      setProduct(product)

                      form.setFieldsValue({
                        qty: product.qty,
                        subtotal: product.subtotal,
                        price: product.prices,
                        qty_diterima: qty_diterima > 0 ? qty_diterima : 0,
                      })
                    }
                  }}
                >
                  {productLists.map((product) => (
                    <Select.Option value={product.product_id}>
                      {product.product_name}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>
            </div>
            <div className="col-md-4">
              <Form.Item label="Qty Dikirim" name="qty">
                <Input type="number" disabled />
              </Form.Item>
              {/* <Form.Item label="Subtotal" name="subtotal"> */}
              <Input type="hidden" disabled />
              {/* </Form.Item> */}
            </div>
            <div className="col-md-4">
              {/* <Form.Item label="Harga Satuan" name="price"> */}
              <Input type="hidden" disabled />
              {/* </Form.Item> */}
              <Form.Item
                label="Qty Diterima"
                name="qty_diterima"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Qty!",
                  },
                ]}
              >
                <Input
                  type="number"
                  disabled={qtyDiterima < 1}
                  placeholder={0}
                  onChange={(e) => {
                    if (e.target.value > qtyDiterima) {
                      const qty = qtyDiterima > 0 ? qtyDiterima : 0
                      return form.setFieldValue("qty_diterima", qty)
                    }
                  }}
                />
              </Form.Item>
            </div>
            <div className="col-md-4">
              <Form.Item label="Received by" name="received_by">
                <Input disabled />
              </Form.Item>
            </div>
            <div className="col-md-12">
              <Form.Item label="Notes" name="notes">
                <Input />
              </Form.Item>
            </div>
          </div>
        </Form>
      </Modal>
    </div>
  )
}

export default ModalPenerimaan
