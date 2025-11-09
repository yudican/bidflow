import {
  DeleteFilled,
  InfoCircleFilled,
  InfoCircleOutlined,
  PlusOutlined,
  UploadOutlined,
} from "@ant-design/icons"
import {
  Button,
  Card,
  DatePicker,
  Form,
  Input,
  Modal,
  Select,
  Table,
  Tooltip,
  Upload,
  message,
  Popconfirm,
} from "antd"
import axios from "axios"
import moment from "moment"
import React, { useEffect, useState } from "react"
import { toast } from "react-toastify"
import { getItem, inArray, validatePhoneNumber } from "../../helpers"

const ModalSplitDeliveryOrder = ({
  onFinish,
  initialValues = {},
  fields = {},
  products = [],
}) => {
  const userData = getItem("user_data", true)
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [logistic, setLogistic] = useState([])
  const [form] = Form.useForm()
  const [productData, setProductData] = useState([])
  const [selectedRowKeys, setSelectedRowKeys] = useState([])
  // attachments
  const [loadingAtachment, setLoadingAtachment] = useState(false)

  const [fileList, setFileList] = useState([])

  const handleChange = ({ fileList: newFileList }) => {
    newFileList.map((file) => {
      const size = file.size / 1024
      if (size > 1024) {
        return message.error("Maksimum ukuran file adalah 1 MB")
      }
      setFileList([...fileList, file])
    })
  }

  const showModal = () => {
    setIsModalOpen(true)
  }
  const handleOk = () => {
    setIsModalOpen(false)
  }
  const handleCancel = () => {
    setIsModalOpen(false)
  }

  const loadLogistic = () => {
    axios
      .get("/api/master/logistic/offline")
      .then((res) => {
        const { data } = res.data
        setLogistic(data)
      })
      .catch((err) => {
        console.log(err)
      })
  }

  useEffect(() => {
    loadLogistic()
  }, [])

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
    getCheckboxProps: (record) => {
      if (record.stock < 1) {
        return {
          disabled: true,
        }
      }
      if (record.product_qty < 1) {
        return {
          disabled: true,
        }
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

  const attachments = fileList.map((item, index) => {
    return {
      id: index,
      attachment: item.name,
    }
  })

  const urls =
    (initialValues?.attachment_url &&
      initialValues?.attachment_url.length > 0 &&
      initialValues?.attachment_url) ||
    []
  const attachments2 = urls.map((item, index) => {
    return {
      id: index,
      attachment: item,
    }
  })

  const finalAttachments = attachments2.length > 0 ? attachments2 : attachments
  return (
    <div>
      <button
        onClick={() => {
          showModal()
          const newProduct = products.map((item) => {
            return {
              id: item.id,
              uid_lead: item.uid_lead,
              product_name: item.product_name,
              product_sku: item.sku,
              stock: item.stock,
              qty_delivery: item.qty_delivery || 0,
              product_qty: item.qty - item.qty_delivery || 0,
              qty: 0,
              selected: false,
            }
          })

          setProductData(newProduct)
        }}
        className={`text-white bg-blue-700 hover:bg-blue-800 focus:ring-4   focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center`}
      >
        <PlusOutlined />
        <span className="ml-2">Input Pengiriman</span>
      </button>

      <Modal
        title={"Input Informasi Pengiriman"}
        open={isModalOpen}
        onOk={() => {
          handleOk()
          form.submit()
          setIsModalOpen(false)
        }}
        cancelText={"Batal"}
        onCancel={handleCancel}
        okText={"Proses Data"}
        okButtonProps={{
          disabled:
            (selectedRowKeys && selectedRowKeys.length < 1) ||
            productData.every((item) => item.qty < 1),
        }}
        width={1000}
      >
        <div>
          <div className="card-body">
            <Form
              form={form}
              name="basic"
              layout="vertical"
              onFinish={(values) => {
                const formData = new FormData()
                for (let i = 0; i < fileList.length; i++) {
                  formData.append(`items[${i}]`, fileList[i].originFileObj)
                }
                const productsData = productData.filter((item) => item.selected)

                if (productsData.length < 1) {
                  return toast.error(
                    "Harap Pilih Salah Satu Produk Untuk Dikirim"
                  )
                }
                const checkProduct = productsData.every((item) => item.qty < 1)
                if (checkProduct) {
                  return toast.error("Harap Masukkan Qty Dikirim")
                }

                formData.append("products", JSON.stringify(productsData))

                {
                  fields?.uid_lead &&
                    formData.append("uid_lead", fields.uid_lead)
                }
                {
                  values.courier && formData.append("courier", values.courier)
                }

                {
                  values.sender_phone &&
                    formData.append("sender_phone", values.sender_phone)
                }

                {
                  values.resi && formData.append("resi", values.resi)
                }

                {
                  values.sender_name &&
                    formData.append("sender_name", values.sender_name)
                }

                formData.append(
                  "delivery_date",
                  values.delivery_date.format("YYYY-MM-DD")
                )
                // fields
                for (const [key, value] of Object.entries(fields)) {
                  formData.append(key, value)
                }
                onFinish(formData)
              }}
              // onFinishFailed={onFinishFailed}
              autoComplete="off"
              initialValues={{
                ...initialValues,
                created_by: userData?.id,
                user_created: userData?.name,
                delivery_date: moment(
                  initialValues?.delivery_date ?? new Date(),
                  "YYYY-MM-DD"
                ),
              }}
            >
              <div className="row">
                <div className="col-md-6">
                  <Form.Item
                    // label="User Created"
                    label="Created by"
                    name="user_created"
                  >
                    <Input disabled />
                  </Form.Item>
                  <Form.Item
                    label="Pilih Ekspedisi"
                    name="courier"
                    rules={[
                      {
                        required: false,
                        message: "Silakan masukkan Ekspedisi!",
                      },
                    ]}
                  >
                    <Select
                      allowClear
                      className="w-full"
                      placeholder="Pilih Exspedisi"
                    >
                      {logistic.map((item) => (
                        <Select.Option key={item.id} value={item.logistic_name}>
                          {item.logistic_name}
                        </Select.Option>
                      ))}
                    </Select>
                  </Form.Item>
                  <Form.Item
                    label="Telepon Pengirim"
                    name="sender_phone"
                    rules={[
                      {
                        required: false,
                        message: "Silakan masukkan Telepon Pengirim!",
                      },
                      {
                        validator: validatePhoneNumber,
                      },
                    ]}
                  >
                    <Input />
                  </Form.Item>
                </div>
                <div className="col-md-6">
                  <Form.Item
                    label="Delivery Date"
                    name="delivery_date"
                    rules={[
                      {
                        required: true,
                        message: "Silakan pilih Delivery Date!",
                      },
                    ]}
                  >
                    <DatePicker className="w-full" format="DD-MM-YYYY" />
                  </Form.Item>
                  <Form.Item
                    label="Nama Pengirim"
                    name="sender_name"
                    rules={[
                      {
                        required: false,
                        message: "Silakan masukkan Nama Pengirim!",
                      },
                    ]}
                  >
                    <Input />
                  </Form.Item>
                  <Form.Item
                    colon={"test"}
                    label={
                      <div className="flex items-center">
                        Resi
                        <Tooltip
                          title={
                            "Kamu dapat mengisi nomor resi ketika barang sudah dikirim"
                          }
                        >
                          <InfoCircleOutlined className="ml-1" />
                        </Tooltip>
                      </div>
                    }
                    name="resi"
                    rules={[
                      {
                        required: false,
                        message: "Silakan masukkan Resi!",
                      },
                    ]}
                  >
                    <Input />
                  </Form.Item>
                </div>
                <div className="col-md-12">
                  <Card title={"Attachment"} className="mb-2">
                    <Form.Item name="attachment">
                      <Upload
                        name="attachments"
                        showUploadList={false}
                        multiple={true}
                        fileList={fileList}
                        beforeUpload={() => false}
                        onChange={(e) => {
                          handleChange({
                            ...e,
                          })
                        }}
                        className="w-full mb-2"
                      >
                        <Button
                          className="mb-2"
                          icon={<UploadOutlined />}
                          loading={loadingAtachment}
                        >
                          Upload (Multiple)
                        </Button>
                      </Upload>

                      <AttachmentTable
                        attachments={finalAttachments}
                        onChange={(index) => {
                          const files = [...fileList]
                          setFileList(
                            files.filter((file, key) => key !== index)
                          )
                        }}
                      />
                    </Form.Item>
                  </Card>
                  <Card title={"Product"}>
                    <ProductTable
                      products={productData}
                      rowSelection={rowSelection}
                      onChange={(id, value) => handleProductChange(id, value)}
                    />
                  </Card>
                </div>
              </div>
            </Form>
          </div>
        </div>
      </Modal>
    </div>
  )
}

const AttachmentTable = ({ attachments, onChange }) => {
  return (
    <Table
      dataSource={attachments}
      rowKey={"id"}
      className="w-full"
      pagination={false}
      columns={[
        {
          title: "No",
          dataIndex: "id",
          key: "id",
          width: 10,
          render: (text, record, index) => index + 1,
        },
        {
          title: "Attachment",
          dataIndex: "attachment",
          key: "attachment",
        },
        {
          title: "",
          dataIndex: "action",
          key: "action",
          width: 10,
          render: (text, record, index) => {
            return (
              <Popconfirm
                title="Apakah anda yakin ingin menghapus attachment ini?"
                onConfirm={() => onChange(index)}
                // onCancel={cancel}
                okText="Ya, Hapus"
                cancelText="Batal"
              >
                <DeleteFilled></DeleteFilled>
              </Popconfirm>
            )
          },
        },
      ]}
    />
  )
}

const ProductTable = ({ products, onChange, rowSelection }) => {
  console.log(products, "products")
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
          title: "Stock",
          dataIndex: "stock",
          key: "stock",
        },
        {
          title: "Qty Order",
          dataIndex: "product_qty",
          key: "product_qty",
        },
        {
          title: "Qty Dikirim",
          dataIndex: "product_qty",
          key: "product_qty",
          render: (text, record) => {
            return (
              <Input
                type="number"
                disabled={!record.selected || record.stock < 1}
                value={record.qty}
                onChange={(e) => {
                  const { value } = e.target
                  if (value < 0) {
                    return onChange(record.id, 0)
                  }

                  if (value > text) {
                    return onChange(record.id, text)
                  }

                  if (value > record.stock) {
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

export default ModalSplitDeliveryOrder
