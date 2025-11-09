import { LoadingOutlined, PlusOutlined } from "@ant-design/icons"
import { DatePicker, Form, Input, Modal, Upload, message } from "antd"
import { useForm } from "antd/es/form/Form"
import TextArea from "antd/lib/input/TextArea"
import axios from "axios"
import React, { useState } from "react"
import { toast } from "react-toastify"
import { getBase64, getItem } from "../../../helpers"
import "../../../index.css"

const ModalBilling = ({ refetch, detail, user }) => {
  const [form] = useForm()
  const [loadingSubmit, setLoadingSubmit] = useState(false)
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

    formData.append("uid_lead", detail.uid_lead)
    formData.append("account_name", value.account_name)
    formData.append("account_bank", value.account_bank)
    formData.append("total_transfer", value.total_transfer)
    formData.append("notes", value.notes)
    formData.append("transfer_date", value.transfer_date.format("YYYY-MM-DD"))

    axios
      .post(`/api/order-manual/billing`, formData)
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
        toast.success(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        setIsModalOpen(false)
        setLoadingSubmit(false)
      })
      .catch((e) => setLoadingSubmit(false))
  }
  const isFinance = getItem("role") === "finance"
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
              {formFields.map((field, index) => (
                <Form.Item
                  key={index}
                  label={field.replace("_", " ").toUpperCase()}
                  name={field}
                  rules={[
                    {
                      required: true,
                      message: "Field Tidak Boleh Kosong!",
                    },
                  ]}
                >
                  <Input />
                </Form.Item>
              ))}
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
                    message: "Silahkan Masukkan notes!",
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
                    message: "Silahkan Masukkan Photo!",
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
                    message: "Silahkan Masukkan Photo!",
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
          </div>
        </Form>
      </Modal>
    </div>
  )
}

export default ModalBilling
