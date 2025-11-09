import { LoadingOutlined, PlusOutlined } from "@ant-design/icons"
import { Form, Input, Modal, Upload, message } from "antd"
import { useForm } from "antd/es/form/Form"
import axios from "axios"
import React, { useState } from "react"
import { toast } from "react-toastify"
import { getBase64, getItem } from "../../../helpers"

const PurchaseBillingModal = ({ refetch, detail }) => {
  const [form] = useForm()
  const { name } = getItem("user_data", true) || {}
  const [loadingSubmit, setLoadingSubmit] = useState(false)
  const [loading, setLoading] = useState(false)

  const [imageUrl, setImageUrl] = useState(null)

  const [fileList, setFileList] = useState(null)

  const [isModalOpen, setIsModalOpen] = useState(false)

  const showModal = () => {
    setIsModalOpen(true)
    form.setFieldsValue({
      upload_by: name,
      jumlah_transfer: detail?.amount_to_pay,
      tax_amount: detail?.amount_to_pay * 0.11,
    })
  }

  const handleCancel = () => {
    setIsModalOpen(false)
  }
  const handleChange = ({ fileList, field }) => {
    const list = fileList.pop()
    setLoading(true)
    setTimeout(() => {
      const size = list.size / 1024
      if (size > 1024) {
        setLoading(false)
        return message.error("Maksimum ukuran file adalah 1 MB")
      }
      getBase64(list.originFileObj, (url) => {
        setLoading(false)
        setImageUrl(url)
      })
      setFileList(list.originFileObj)
    }, 1000)
  }

  const onFinish = (value) => {
    setLoadingSubmit(true)
    let formData = new FormData()

    if (fileList) {
      formData.append("bukti_transfer", fileList)
    }

    formData.append("purchase_order_id", detail.id)
    formData.append("nama_bank", value.nama_bank)
    formData.append("nama_pengirim", value.nama_pengirim)
    formData.append("no_rekening", value.no_rekening)
    formData.append("jumlah_transfer", value.jumlah_transfer)
    formData.append("tax_amount", value.tax_amount)
    formData.append("sumberdana", value.sumberdana)
    formData.append("no_rekening_sumberdana", value.no_rekening_sumberdana)

    axios
      .post(`/api/purchase/purchase-order/billing/save/${detail.id}`, formData)
      .then((res) => {
        const { message } = res.data
        refetch()
        setFileList(null)
        setImageUrl(null)
        toast.success(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        setIsModalOpen(false)
        setLoadingSubmit(false)
      })
      .catch((e) => setLoadingSubmit(false))
  }
  return (
    <div>
      <button
        onClick={() => showModal()}
        className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
      >
        <PlusOutlined />
        <span className="ml-2">Tambah Data</span>
      </button>

      <Modal
        title="Informasi Penagihan"
        open={isModalOpen}
        onOk={() => {
          form.submit()
        }}
        cancelText={"Cancel"}
        onCancel={handleCancel}
        okText={"Simpan"}
        confirmLoading={loadingSubmit}
      >
        <Form
          form={form}
          name="basic"
          layout="vertical"
          onFinish={onFinish}
          autoComplete="off"
        >
          <div className="row">
            <div className="col-md-6">
              <Form.Item
                label={"Nama Bank"}
                name={"nama_bank"}
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
                label={"Nama Pengirim"}
                name={"nama_pengirim"}
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
                label={"Sumber Dana"}
                name={"sumberdana"}
                rules={[
                  {
                    required: true,
                    message: "Field Tidak Boleh Kosong!",
                  },
                ]}
              >
                <Input />
              </Form.Item>
              <Form.Item label={"Upload by"} name={"upload_by"}>
                <Input disabled />
              </Form.Item>
            </div>
            <div className="col-md-6">
              <Form.Item
                label={"No. Rekening"}
                name={"no_rekening"}
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
                label={"No. Rekening Sumber Dana"}
                name={"no_rekening_sumberdana"}
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
                label={"Jumlah Transfer (Rp.)"}
                name={"jumlah_transfer"}
                rules={[
                  {
                    required: true,
                    message: "Field Tidak Boleh Kosong!",
                  },
                ]}
              >
                <Input type="number" />
              </Form.Item>
              <Form.Item label={"Tax (Rp.)"} name={"tax_amount"}>
                <Input type="number" allowClear />
              </Form.Item>
            </div>

            <div className="col-md-12">
              <Form.Item
                label="Bukti Transfer"
                name="bukti_transfer"
                rules={[
                  {
                    required: false,
                    message: "Silakan masukkan Bukti Transfer!",
                  },
                ]}
              >
                <Upload
                  name="bukti_transfer"
                  listType="picture-card"
                  className="avatar-uploader w-100"
                  showUploadList={false}
                  multiple={false}
                  beforeUpload={() => false}
                  onChange={(e) =>
                    handleChange({
                      ...e,
                      field: "bukti_transfer",
                    })
                  }
                >
                  {imageUrl ? (
                    loading ? (
                      <LoadingOutlined />
                    ) : (
                      <img
                        src={imageUrl}
                        alt="avatar"
                        style={{
                          height: 104,
                        }}
                      />
                    )
                  ) : (
                    <div style={{ width: "100%" }}>
                      {loading ? <LoadingOutlined /> : <PlusOutlined />}
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
          </div>
        </Form>
      </Modal>
    </div>
  )
}

export default PurchaseBillingModal
