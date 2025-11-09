import { CheckOutlined, LoadingOutlined, PlusOutlined } from "@ant-design/icons"
import { Card, Form, Input, Select, Upload, message } from "antd"
import React, { useEffect, useState } from "react"
import "react-draft-wysiwyg/dist/react-draft-wysiwyg.css"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../../components/layout"
import RichtextEditor from "../../../components/RichtextEditor"
import { getBase64 } from "../../../helpers"
import "../../../index.css"

const FormBanner = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const { banner_id } = useParams()

  const [dataBanner, setDataBanner] = useState(null)
  const [dataBrand, setDataBrand] = useState([])
  const [imageLoading, setImageLoading] = useState(false)
  const [imageUrl, setImageUrl] = useState(false)
  const [fileList, setFileList] = useState(false)

  const [loadingBrand, setLoadingBrand] = useState(false)
  const [loadingSubmit, setLoadingSubmit] = useState(false)

  const loadDetailBrand = () => {
    axios.get(`/api/master/banner/${banner_id}`).then((res) => {
      const { data } = res.data
      setImageUrl(data.banner_image)
      setDataBanner(data)
      form.setFieldsValue(data)
    })
  }

  const loadBrand = () => {
    setLoadingBrand(true)
    axios
      .get("/api/master/brand")
      .then((res) => {
        setDataBrand(res.data.data)
        setLoadingBrand(false)
      })
      .catch((err) => setLoadingBrand(false))
  }

  useEffect(() => {
    loadBrand()
    loadDetailBrand()
  }, [])

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
    setLoadingSubmit(true)
    let formData = new FormData()
    if (fileList) {
      formData.append("image", fileList)
    }

    formData.append("title", values.title)
    formData.append("description", values.description)
    formData.append("url", values.url)
    formData.append("sales_channel", JSON.stringify(values.sales_channel))
    formData.append("brand_id", JSON.stringify(values.brand_ids))
    formData.append("status", values.status)

    const url = banner_id
      ? `/api/master/banner/save/${banner_id}`
      : "/api/master/banner/save"

    axios
      .post(url, formData)
      .then((res) => {
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        setLoadingSubmit(false)
        return navigate("/master/banner")
      })
      .catch((err) => {
        const { message } = err.response.data
        setLoadingSubmit(false)
        toast.error(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }

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

  const rightContent = (
    <div className="flex justify-between items-center">
      <button
        onClick={() => form.submit()}
        className="text-white bg-blueColor hover:bg-blueColor/90 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
      >
        {loadingSubmit ? <LoadingOutlined /> : <CheckOutlined />}
        <span className="ml-2">Simpan</span>
      </button>
    </div>
  )

  return (
    <Layout
      title="Tambah Data Banner"
      href="/master/banner"
      // rightContent={rightContent}
    >
      <Form
        form={form}
        name="basic"
        layout="vertical"
        onFinish={onFinish}
        //   onFinishFailed={onFinishFailed}
        autoComplete="off"
      >
        <Card title="Banner Data">
          <div className="card-body row">
            <div className="col-md-6">
              <Form.Item
                label="Title"
                name="title"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Title!",
                  },
                ]}
              >
                <Input placeholder="Ketik Title" />
              </Form.Item>

              <Form.Item
                label="Brand"
                name="brand_ids"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Brand!",
                  },
                ]}
              >
                <Select
                  mode="multiple"
                  allowClear
                  className="w-full mb-2"
                  placeholder="Pilih Brand"
                  loading={loadingBrand}
                >
                  {dataBrand.map((item) => (
                    <Select.Option key={item.id} value={item.id}>
                      {item.name}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>

              <Form.Item
                label="Sales Channel"
                name="sales_channel"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Sales Channel!",
                  },
                ]}
              >
                <Select
                  mode="multiple"
                  allowClear
                  className="w-full mb-2"
                  placeholder="Pilih Sales Channel"
                >
                  <Select.Option value={"customer-portal"}>
                    Customer Portal
                  </Select.Option>
                  <Select.Option value={"agent-portal"}>
                    Agent Portal
                  </Select.Option>
                  <Select.Option value={"sales-offline"}>
                    Sales Offline
                  </Select.Option>
                  <Select.Option value={"marketplace"}>
                    Marketplace
                  </Select.Option>
                  <Select.Option value={"telmark"}>Telmark</Select.Option>
                  <Select.Option value={"lms"}>LMS</Select.Option>
                  <Select.Option value={"flimapp"}>FlimApp</Select.Option>
                </Select>
              </Form.Item>
            </div>
            <div className="col-md-6">
              <Form.Item
                label="Url"
                name="url"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Url!",
                  },
                  { type: "url", message: "URL tidak valid!" },
                ]}
              >
                <Input placeholder="Ketik Url" />
              </Form.Item>

              <Form.Item
                label="Status"
                name="status"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Status!",
                  },
                ]}
              >
                <Select
                  allowClear
                  className="w-full mb-2"
                  placeholder="Pilih Status"
                >
                  <Select.Option key={"1"} value={"1"}>
                    Active
                  </Select.Option>
                  <Select.Option key={"0"} value={"0"}>
                    Non Active
                  </Select.Option>
                </Select>
              </Form.Item>
            </div>

            <div className="col-md-12">
              <Form.Item
                label="Description"
                name="description"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Description!",
                  },
                ]}
              >
                <RichtextEditor
                  value={form.getFieldValue("description")}
                  form={form}
                  name={"description"}
                />
              </Form.Item>
            </div>

            <div className="col-md-2">
              <Form.Item
                label="Banner Image"
                name="image"
                rules={[
                  {
                    required: banner_id ? false : true,
                    message: "Silakan pilih Banner Image!",
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
            </div>
          </div>
        </Card>

        <div className="float-right mt-6"></div>
      </Form>

      <div className="card mt-4">
        <div className="card-body flex justify-end">
          <button
            onClick={() => form.submit()}
            className="text-white bg-blueColor hover:bg-blueColor/90 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
          >
            {loadingSubmit ? <LoadingOutlined /> : <CheckOutlined />}
            <span className="ml-2">Simpan</span>
          </button>
        </div>
      </div>
    </Layout>
  )
}

export default FormBanner
