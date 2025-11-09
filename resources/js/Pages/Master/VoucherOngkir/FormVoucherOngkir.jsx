import { CheckOutlined, LoadingOutlined, PlusOutlined } from "@ant-design/icons"
import { Card, DatePicker, Form, Input, Select, Upload, message } from "antd"
import React, { useEffect, useState } from "react"
import "react-draft-wysiwyg/dist/react-draft-wysiwyg.css"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../../components/layout"
import RichtextEditor from "../../../components/RichtextEditor"
import { getBase64 } from "../../../helpers"
import "../../../index.css"

const FormVoucher = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const { voucher_id } = useParams()

  const [dataBrand, setDataBrand] = useState([])
  const [imageLoading, setImageLoading] = useState(false)
  const [imageUrl, setImageUrl] = useState(false)
  const [fileList, setFileList] = useState(false)

  const [loadingBrand, setLoadingBrand] = useState(false)
  const [loadingSubmit, setLoadingSubmit] = useState(false)

  const [typeVoucher, setTypeVoucher] = useState("general")

  const loadDetailBrand = () => {
    axios.get(`/api/master/voucher-ongkir/${voucher_id}`).then((res) => {
      const { data } = res.data
      setImageUrl(data.voucher_image)
      const newData = {
        ...data,
        start_date: moment(data.start_date || new Date(), "YYYY-MM-DD"),
        end_date: moment(data.end_date || new Date(), "YYYY-MM-DD"),
      }
      form.setFieldsValue(newData)
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

    formData.append("voucher_code", values.voucher_code)
    formData.append("title", values.title)
    formData.append("description", values.description)
    formData.append("nominal", values.nominal)
    formData.append("percentage", values.percentage)
    formData.append("total", values.total)
    formData.append("start_date", values.start_date.format("YYYY-MM-DD"))
    formData.append("end_date", values.end_date.format("YYYY-MM-DD"))
    formData.append("min", values.min)
    formData.append("type", values.type)
    formData.append("total_point", values.total_point)
    formData.append("usage_for", values.usage_for)
    formData.append("brand_id", JSON.stringify(values.brand_ids))
    formData.append("status", values.status)

    const url = voucher_id
      ? `/api/master/voucher-ongkir/save/${voucher_id}`
      : "/api/master/voucher-ongkir/save"

    return axios
      .post(url, formData)
      .then((res) => {
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        setLoadingSubmit(false)
        return navigate("/master/voucher-ongkir")
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

  return (
    <Layout
      title="Form Voucher"
      href="/master/voucher-ongkir"
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
        <Card title="Voucher Data">
          <div className="card-body row">
            <div className="col-md-6">
              <Form.Item
                label="Voucher Code"
                name="voucher_code"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Voucher Code!",
                  },
                ]}
              >
                <Input placeholder="Input Voucher Code" />
              </Form.Item>
              <Form.Item
                label="Nominal"
                name="nominal"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Nominal!",
                  },
                ]}
              >
                <Input placeholder="Input Nominal" type="number" />
              </Form.Item>
              <Form.Item
                label="Percentage"
                name="percentage"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Percentage!",
                  },
                ]}
              >
                <Input placeholder="Input Percentage 1-100 %" type="number" />
              </Form.Item>
              <Form.Item
                label="Qty Voucher"
                name="total"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Qty Voucher!",
                  },
                ]}
              >
                <Input placeholder="Input Qty Voucher" type="number" />
              </Form.Item>
              <Form.Item
                label="Start Date"
                name="start_date"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Start Date!",
                  },
                ]}
              >
                <DatePicker className="w-full" format="YYYY-MM-DD" />
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
            </div>
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
                label="Min Transaction"
                name="min"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Min Transaction!",
                  },
                ]}
              >
                <Input placeholder="Input Min Transaction" type="number" />
              </Form.Item>

              <Form.Item
                label="Type Voucher"
                name="type"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Type Voucher!",
                  },
                ]}
              >
                <Select
                  allowClear
                  className="w-full"
                  placeholder="Pilih Type Voucher"
                  onChange={(value) => setTypeVoucher(value)}
                >
                  <Select.Option key={"0"} value={"general"}>
                    General
                  </Select.Option>
                  <Select.Option key={"1"} value={"point"}>
                    Point
                  </Select.Option>
                </Select>
              </Form.Item>

              {typeVoucher === "point" && (
                <Form.Item
                  label="Total Point"
                  name="total_point"
                  rules={[
                    {
                      required: true,
                      message: "Silakan masukkan Total Point!",
                    },
                  ]}
                >
                  <Input placeholder="Input Total Point" />
                </Form.Item>
              )}
              <Form.Item
                label="Limit Usage"
                name="usage_for"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Limit Usage!",
                  },
                ]}
              >
                <Input placeholder="Input Limit Usage" />
              </Form.Item>
              <Form.Item
                label="End Date"
                name="end_date"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih End Date!",
                  },
                ]}
              >
                <DatePicker className="w-full" format="YYYY-MM-DD" />
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
                  className="w-full"
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
                label="Voucher Image"
                name="image"
                rules={[
                  {
                    required: voucher_id ? false : true,
                    message: "Silakan pilih Voucher Image!",
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

        <div className="float-right mt-6">
          <button className="text-white bg-blueColor hover:bg-blueColor/90 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2">
            {loadingSubmit ? <LoadingOutlined /> : <CheckOutlined />}
            <span className="ml-2">Simpan</span>
          </button>
        </div>
      </Form>
    </Layout>
  )
}

export default FormVoucher
