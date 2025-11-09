import { CheckOutlined, LoadingOutlined } from "@ant-design/icons"
import { Card, Form, Input, Select } from "antd"
import React, { useEffect, useState } from "react"
import "react-draft-wysiwyg/dist/react-draft-wysiwyg.css"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../../components/layout"
import "../../../index.css"

const ProductAdditionalForm = ({ type = "pengememasan" }) => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const { product_additional_id } = useParams()

  const [loadingSubmit, setLoadingSubmit] = useState(false)

  const loadDetailBrand = () => {
    axios.get(`/api/master/${type}/${product_additional_id}`).then((res) => {
      const { data } = res.data
      form.setFieldsValue(data)
    })
  }

  useEffect(() => {
    loadDetailBrand()
  }, [])

  const onFinish = (values) => {
    setLoadingSubmit(true)
    let formData = new FormData()

    formData.append("name", values.name)
    formData.append("sku", values.sku)
    formData.append("status", values.status)
    formData.append("notes", values.notes)
    formData.append("type", type)

    const url = product_additional_id ? `save/${product_additional_id}` : "save"

    axios
      .post(`/api/master/${type}/${url}`, formData)
      .then((res) => {
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        setLoadingSubmit(false)
        return navigate(`/master/${type}`)
      })
      .catch((err) => {
        const { message } = err.response.data
        setLoadingSubmit(false)
        toast.error(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }

  const currentUrl = new URL(window.location.href)
  const pathName = currentUrl?.pathname
  const parts = pathName?.split("/").filter(Boolean)
  const MainUrl = parts[1]
  const capitalizedMainUrl = MainUrl.charAt(0).toUpperCase() + MainUrl.slice(1)
  return (
    <>
      <Layout title={MainUrl} href={`/master/${type}`}>
        <Form
          form={form}
          name="basic"
          layout="vertical"
          onFinish={onFinish}
          autoComplete="off"
        >
          <Card title={`${capitalizedMainUrl} Data`}>
            <div className="card-body row">
              <div className="col-md-4">
                <Form.Item
                  label="Product Name"
                  name="name"
                  rules={[
                    {
                      required: true,
                      message: "Silakan masukkan Product Name!",
                    },
                  ]}
                >
                  <Input placeholder="Ketik Product Name" />
                </Form.Item>
              </div>
              <div className="col-md-4">
                <Form.Item
                  label="SKU"
                  name="sku"
                  rules={[
                    {
                      required: true,
                      message: "Silakan masukkan SKU!",
                    },
                  ]}
                >
                  <Input placeholder="Ketik SKU" />
                </Form.Item>
              </div>
              <div className="col-md-4">
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
                    <Select.Option key={"1"} value={1}>
                      Active
                    </Select.Option>
                    <Select.Option key={"0"} value={0}>
                      Non Active
                    </Select.Option>
                  </Select>
                </Form.Item>
              </div>
              <div className="col-md-12">
                <Form.Item
                  label="Notes"
                  name="notes"
                  rules={[
                    {
                      required: false,
                      message: "Silakan masukkan Notes!",
                    },
                  ]}
                >
                  <Input placeholder="Ketik Notes" />
                </Form.Item>
              </div>
            </div>
          </Card>
        </Form>
        <div className="card ">
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
    </>
  )
}

export default ProductAdditionalForm
