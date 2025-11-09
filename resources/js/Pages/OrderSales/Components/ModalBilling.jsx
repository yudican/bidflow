import { LoadingOutlined, PlusOutlined } from "@ant-design/icons"
import {
  Card,
  DatePicker,
  Form,
  Input,
  Modal,
  Table,
  Upload,
  message,
} from "antd"
import { useForm } from "antd/es/form/Form"
import TextArea from "antd/lib/input/TextArea"
import axios from "axios"
import React, { useState } from "react"
import { toast } from "react-toastify"
import { getBase64, getItem, inArray } from "../../../helpers"
import "../../../index.css"

const ModalBilling = ({
  refetch,
  detail,
  user,
  url = "/api/order-manual/billing",
}) => {
  const [form] = useForm()
  const [loadingSubmit, setLoadingSubmit] = useState(false)
  const [productData, setProductData] = useState([])
  const [selectedRowKeys, setSelectedRowKeys] = useState([])
  const [loading, setLoading] = useState({
    attachment: false,
    struct: false,
  })

  const [imageUrl, setImageUrl] = useState({
    attachment: null,
    struct: null,
  })

  const [fileList, setFileList] = useState({
    attachment: null,
    struct: null,
  })

  const [isModalOpen, setIsModalOpen] = useState(false)

  const showModal = () => {
    setIsModalOpen(true)
  }

  const handleCancel = () => {
    setIsModalOpen(false)
  }
  const formFields = ["account_name", "account_bank", "total_transfer"]

  const handleChange = ({ fileList, field }) => {
    const list = fileList.pop()
    setLoading((loading) => ({ ...loading, [field]: true }))
    setTimeout(() => {
      const size = list.size / 1024
      if (size > 1024) {
        setLoading((loading) => ({ ...loading, [field]: false }))
        return message.error("Maksimum ukuran file adalah 1 MB")
      }
      getBase64(list.originFileObj, (url) => {
        setLoading((loading) => ({ ...loading, [field]: false }))
        setImageUrl((imageUrl) => ({ ...imageUrl, [field]: url }))
      })
      setFileList((fileList) => ({ ...fileList, [field]: list.originFileObj }))
    }, 1000)
  }

  const onFinish = (value) => {
    setLoadingSubmit(true)
    let formData = new FormData()

    if (fileList.attachment) {
      formData.append("upload_billing_photo", fileList.attachment)
    }

    if (fileList.struct) {
      formData.append("upload_transfer_photo", fileList.struct)
    }

    const productsData = productData.filter((item) => item.selected)

    if (productsData.length < 1) {
      return toast.error("Harap Masukkan Qty Dibayar")
    }

    formData.append("products", JSON.stringify(productsData))

    formData.append("uid_lead", detail.uid_lead)
    formData.append("account_name", value.account_name)
    formData.append("account_bank", value.account_bank)
    formData.append("total_transfer", value.total_transfer)
    formData.append("notes", value.notes)
    formData.append("transfer_date", value.transfer_date.format("YYYY-MM-DD"))

    axios
      .post(url, formData)
      .then((res) => {
        const { message } = res.data
        refetch()
        setFileList({
          attachment: null,
          struct: null,
        })
        setImageUrl({
          attachment: null,
          struct: null,
        })
        setProductData([])
        setSelectedRowKeys([])
        toast.success(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        setIsModalOpen(false)
        setLoadingSubmit(false)
      })
      .catch((e) => setLoadingSubmit(false))
  }

  const rowSelection = {
    selectedRowKeys,
    onChange: (e) => {
      setSelectedRowKeys(e)
      if (e.length > 0) {
        const newData = productData.map((item) => {
          if (inArray(item.id, e)) {
            return {
              ...item,
              selected: true,
            }
          }
          return {
            ...item,
            selected: false,
          }
        })
        return setProductData(newData)
      } else {
        const newData = productData.map((item) => {
          return {
            ...item,
            selected: false,
          }
        })
        return setProductData(newData)
      }
    },
  }
  const handleProductChange = (product_id, value) => {
    const newData = productData.map((item) => {
      if (item.id === product_id) {
        return {
          ...item,
          qty: value == "" ? 0 : parseInt(value),
        }
      }
      return item
    })
    return setProductData(newData)
  }

  const isFinance = getItem("role") === "finance"
  return (
    <div>
      <button
        onClick={() => {
          showModal()
          const newProduct =
            detail?.product_needs
              // ?.filter((item) => item.qty_dibayar > 0)
              ?.map((item) => {
                return {
                  id: item.id,
                  uid_lead: item.uid_lead,
                  product_name: item.product_name,
                  product_sku: item?.product?.sku,
                  qty_dibayar: item.qty_dibayar || 0,
                  product_qty: item.qty - item.qty_dibayar || 0,
                  qty: 0,
                  selected: false,
                }
              }) || []

          setProductData(newProduct)
        }}
        className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
      >
        <PlusOutlined />
        <span className="ml-2">Tambah Data</span>
      </button>

      <Modal
        title="Penerimaan Uang Masuk"
        open={isModalOpen}
        onOk={() => {
          form.submit()
        }}
        cancelText={"Cancel"}
        onCancel={handleCancel}
        okText={"Simpan"}
        width={1000}
      >
        <Form
          form={form}
          name="basic"
          layout="vertical"
          initialValues={{
            user_approval: user?.name,
          }}
          onFinish={onFinish}
          //   onFinishFailed={onFinishFailed}
          autoComplete="off"
        >
          <div className="row">
            <div className="col-md-12">
              {isFinance && (
                <Form.Item label="User Approval" name="user_approval">
                  <Input disabled />
                </Form.Item>
              )}
            </div>
            <div className="col-md-6">
              <Form.Item
                label="Nama Rekening"
                name="account_name"
                rules={[
                  {
                    required: true,
                    message: "Field Tidak Boleh Kosong!",
                  },
                ]}
              >
                <Input />
              </Form.Item>
              <Form.Item
                label="Nama Bank"
                name="account_bank"
                rules={[
                  {
                    required: true,
                    message: "Field Tidak Boleh Kosong!",
                  },
                ]}
              >
                <Input />
              </Form.Item>
            </div>
            <div className="col-md-6">
              <Form.Item
                label="Total Transfer"
                name="total_transfer"
                rules={[
                  {
                    required: true,
                    message: "Field Tidak Boleh Kosong!",
                  },
                ]}
              >
                <Input />
              </Form.Item>
              <Form.Item
                label="Tanggal Transfer"
                name="transfer_date"
                rules={[
                  {
                    required: true,
                    message: "Field Tidak Boleh Kosong!",
                  },
                ]}
              >
                <DatePicker className="w-full" />
              </Form.Item>
            </div>

            <div className="col-md-12">
              <Form.Item
                label="Notes"
                name="notes"
                rules={[
                  {
                    required: false,
                    message: "Silakan masukkan notes!",
                  },
                ]}
              >
                <TextArea />
              </Form.Item>
            </div>

            {/* <div className="col-md-6">
              <Form.Item
                label="Billing Photo"
                name="upload_billing_photo"
                rules={[
                  {
                    required: false,
                    message: "Silakan masukkan Photo!",
                  },
                ]}
              >
                <Upload
                  name="attachment"
                  listType="picture-card"
                  className="avatar-uploader w-100"
                  showUploadList={false}
                  multiple={false}
                  beforeUpload={() => false}
                  onChange={(e) =>
                    handleChange({
                      ...e,
                      field: "attachment",
                    })
                  }
                >
                  {imageUrl.attachment ? (
                    loading.attachment ? (
                      <LoadingOutlined />
                    ) : (
                      <img
                        src={imageUrl.attachment}
                        alt="avatar"
                        style={{
                          height: 104,
                        }}
                      />
                    )
                  ) : (
                    <div style={{ width: "100%" }}>
                      {loading.attachment ? (
                        <LoadingOutlined />
                      ) : (
                        <PlusOutlined />
                      )}
                      <div
                        style={{
                          marginTop: 8,
                          width: "100%",
                        }}
                      >
                        Upload
                      </div>
                    </div>
                  )}
                </Upload>
              </Form.Item>
            </div> */}
            <div className="col-md-6">
              <Form.Item
                label="Transfer Photo"
                name="upload_transfer_photo"
                rules={[
                  {
                    required: false,
                    message: "Silakan masukkan Photo!",
                  },
                ]}
              >
                <Upload
                  name="struct"
                  listType="picture-card"
                  className="avatar-uploader w-100"
                  showUploadList={false}
                  multiple={false}
                  beforeUpload={() => false}
                  onChange={(e) => handleChange({ ...e, field: "struct" })}
                >
                  {imageUrl.struct ? (
                    loading.struct ? (
                      <LoadingOutlined />
                    ) : (
                      <img
                        src={imageUrl.struct}
                        alt="avatar"
                        style={{
                          height: 104,
                        }}
                      />
                    )
                  ) : (
                    <div className="w-100">
                      {loading.struct ? <LoadingOutlined /> : <PlusOutlined />}
                      <div
                        style={{
                          marginTop: 8,
                          width: "100%",
                        }}
                      >
                        Upload
                      </div>
                    </div>
                  )}
                </Upload>
              </Form.Item>
            </div>
            <div className="col-md-12">
              <Card>
                <ProductTable
                  rowSelection={rowSelection}
                  products={productData}
                  onChange={handleProductChange}
                />
              </Card>
            </div>
          </div>
        </Form>
      </Modal>
    </div>
  )
}

const ProductTable = ({ products, onChange, rowSelection }) => {
  return (
    <Table
      rowSelection={rowSelection}
      dataSource={products}
      rowKey={"id"}
      columns={[
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
          dataIndex: "product_sku",
          key: "product_sku",
        },
        {
          title: "Qty",
          dataIndex: "product_qty",
          key: "product_qty",
        },
        {
          title: "Qty Dibayar",
          dataIndex: "product_qty",
          key: "product_qty",
          render: (text, record) => {
            return (
              <Input
                type="number"
                disabled={!record.selected}
                value={record.qty}
                onChange={(e) => {
                  const { value } = e.target
                  if (value < 0) {
                    return onChange(record.id, 0)
                  }
                  if (value > text) {
                    return onChange(record.id, text)
                  }
                  onChange(record.id, value)
                }}
              />
            )
          },
        },
      ]}
    />
  )
}

export default ModalBilling
