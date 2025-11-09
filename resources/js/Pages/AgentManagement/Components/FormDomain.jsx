import { LoadingOutlined, PlusOutlined } from "@ant-design/icons"
import { Form, Input, Modal, Upload, message } from "antd"
import TextArea from "antd/lib/input/TextArea"
import React, { useState } from "react"
import { toast } from "react-toastify"
import { getBase64 } from "../../../helpers"
import axios from "axios"

const FormDomain = ({ update = false, initialValues = {}, refetch }) => {
  const [form] = Form.useForm()
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [loading, setLoading] = useState(false)
  const [imageUrl, setImageUrl] = useState(null)
  const [fileList, setFileList] = useState(null)
  const handleSaveAddress = (value) => {
    let formData = new FormData()

    if (fileList) {
      formData.append("icon", fileList)
    }
    if (update) {
      formData.append("agent_domain_id", initialValues?.id)
    }
    formData.append("name", value.name)
    formData.append("url", value.url)
    formData.append("color", value.color)
    formData.append("back_color", value.back_color)
    formData.append("description", value.description)
    formData.append("status", value.status)
    formData.append("fb_pixel", value.fb_pixel)
    formData.append("status", update ? initialValues?.status : 1)
    axios
      .post(`/api/agent/domain/${update ? "update" : "save"}`, formData)
      .then((res) => {
        const { data } = res.data
        refetch()
        setFileList(null)
        setIsModalOpen(false)
        setImageUrl(url)
        toast.success("Domain berhasil disimpan", {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }

  const handleChange = ({ fileList }) => {
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

  const uploadButton = (
    <div>
      {loading ? <LoadingOutlined /> : <PlusOutlined />}
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
      {!update ? (
        <button
          onClick={() => setIsModalOpen(true)}
          className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
        >
          <PlusOutlined />
          <span className="ml-2">Tambah Domain</span>
        </button>
      ) : (
        <span onClick={() => setIsModalOpen(true)}>Update</span>
      )}

      <Modal
        title="Form Setting Domain"
        open={isModalOpen}
        onOk={() => {
          form.submit()
          // setIsModalOpen(false);
        }}
        cancelText={"Batal"}
        onCancel={() => setIsModalOpen(false)}
        okText={"Simpan"}
        width={1000}
      >
        <Form
          form={form}
          name="basic"
          layout="vertical"
          initialValues={initialValues}
          onFinish={handleSaveAddress}
          //   onFinishFailed={onFinishFailed}
          autoComplete="off"
        >
          <Form.Item
            label="Nama Domain"
            name="name"
            rules={[
              {
                required: true,
                message: "Data tidak boleh kosong",
              },
            ]}
          >
            <Input />
          </Form.Item>

          <Form.Item
            label="Url Domain"
            name="url"
            rules={[
              {
                required: true,
                message: "Data tidak boleh kosong",
              },
            ]}
          >
            <Input />
          </Form.Item>
          <Form.Item
            label="Color"
            name="color"
            rules={[
              {
                required: true,
                message: "Data tidak boleh kosong",
              },
            ]}
          >
            <Input />
          </Form.Item>
          <Form.Item
            label="Background Color"
            name="back_color"
            rules={[
              {
                required: true,
                message: "Data tidak boleh kosong",
              },
            ]}
          >
            <Input />
          </Form.Item>
          <Form.Item
            label="Fb Pixel"
            name="fb_pixel"
            placeholder="Ex: 123456789,123456789"
          >
            <Input />
          </Form.Item>
          <Form.Item
            label="Description"
            name="description"
            placeholder="Deskripsi"
          >
            <TextArea />
          </Form.Item>
          <Form.Item label="Icon" name="icon">
            <Upload
              name="icon"
              listType="picture-card"
              className="avatar-uploader"
              showUploadList={false}
              multiple={false}
              beforeUpload={() => false}
              onChange={handleChange}
            >
              {imageUrl ? (
                loading ? (
                  <LoadingOutlined />
                ) : (
                  <img
                    src={imageUrl}
                    alt="avatar"
                    style={{
                      width: "100%",
                    }}
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

export default FormDomain
