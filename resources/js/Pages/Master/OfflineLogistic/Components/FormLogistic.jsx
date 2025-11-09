import { EditOutlined, LoadingOutlined, PlusOutlined } from "@ant-design/icons"
import { Form, Input, Modal, Upload, message } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { toast } from "react-toastify"
import { getBase64 } from "../../../../helpers"

const FormLogistic = ({ refetch, record = {}, update = false }) => {
  const [form] = Form.useForm()
  const [open, setOpen] = useState(false)
  const [confirmLoading, setConfirmLoading] = useState(false)
  const [imageLoading, setImageLoading] = useState(false)

  const [imageUrl, setImageUrl] = useState(null)
  const [fileList, setFileList] = useState(null)

  const handleChange = ({ fileList }) => {
    const list = fileList.pop()
    setImageLoading(true)
    setTimeout(() => {
      const size = list.size / 1024
      if (size > 1024) {
        setImageLoading(false)
        return message.error("Maksimum ukuran file adalah 1 MB")
      }
      getBase64(list.originFileObj, (url) => {
        setImageLoading(false)
        setImageUrl(url)
      })
      setFileList(list.originFileObj)
    }, 1000)
  }

  const onFinish = (values) => {
    setConfirmLoading(true)
    let formData = new FormData()
    if (fileList) {
      formData.append("image", fileList)
    }

    formData.append("logistic_name", values.logistic_name)
    formData.append("logistic_type", "offline")

    const url = update ? `save/${record.id}` : "save"
    axios
      .post(`/api/master/offline-logistic/${url}`, formData)
      .then((res) => {
        setConfirmLoading(false)
        form.resetFields()
        setImageUrl(null)
        setFileList(null)
        setOpen(false)
        toast.success("Data Offline Logistic berhasil disimpan")
        refetch()
      })
      .catch((err) => {
        setConfirmLoading(false)
        toast.error("Data Offline Logistic gagal disimpan")
      })
  }

  useEffect(() => {
    if (update) {
      setImageUrl(record.logistic_url_logo)
      form.setFieldsValue(record)
    }
  }, [update])

  const uploadButton = (
    <div>
      {imageLoading ? <LoadingOutlined /> : <PlusOutlined />}
      <div
        style={{
          marginTop: 8,
        }}
      >
        Upload
      </div>
    </div>
  )

  return (
    <div>
      {
        // Button Tambah Data
        update ? (
          <button
            onClick={() => {
              setOpen(true)
            }}
            className="text-white bg-[#008BE1] hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
          >
            <EditOutlined />
          </button>
        ) : (
          <button
            onClick={() => {
              setOpen(true)
            }}
            className="text-white bg-[#008BE1] hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
          >
            <PlusOutlined />
            <span className="ml-2">Tambah Data</span>
          </button>
        )
      }

      <Modal
        title="Form Logistic"
        open={open}
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
            label="Nama Jasa Pengiriman"
            name="logistic_name"
            rules={[
              {
                required: true,
                message: "Silakan masukkan Jasa Pengiriman!",
              },
            ]}
          >
            <Input placeholder="Ketik Nama Jasa Pengiriman" />
          </Form.Item>
          <Form.Item
            label="Logistic Image"
            name="image"
            rules={[
              {
                required: !update,
                message: "Silakan pilih Logistic Image!",
              },
            ]}
          >
            <Upload
              name="image"
              listType="picture-card"
              className="avatar-uploader"
              showUploadList={false}
              multiple={false}
              beforeUpload={() => false}
              onChange={handleChange}
              accept="image/*" // Accepts all image types
            >
              {imageUrl ? (
                imageLoading ? (
                  <LoadingOutlined />
                ) : (
                  <img
                    src={imageUrl}
                    alt="avatar"
                    className="max-h-[100px] h-28 w-28 aspect-square"
                  />
                )
              ) : (
                uploadButton
              )}
            </Upload>
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

export default FormLogistic
