import { CheckOutlined, LoadingOutlined } from "@ant-design/icons"
import { Card, Form, Input, Select } from "antd"
import React, { useEffect, useState } from "react"
import "react-draft-wysiwyg/dist/react-draft-wysiwyg.css"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../../components/layout"
import "../../../index.css"

const FormCategory = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const { category_id } = useParams()

  const [loadingSubmit, setLoadingSubmit] = useState(false)

  const loadDetailCategory = () => {
    axios.get(`/api/master/category/${category_id}`).then((res) => {
      const { data } = res.data
      form.setFieldsValue(data)
    })
  }

  useEffect(() => {
    loadDetailCategory()
  }, [])

  const onFinish = (values) => {
    setLoadingSubmit(true)
    let formData = new FormData()

    formData.append("name", values.name)
    formData.append("status", values.status)

    const url = category_id
      ? `/api/master/category/save/${category_id}`
      : "/api/master/category/save"

    axios
      .post(url, formData)
      .then((res) => {
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        setLoadingSubmit(false)
        return navigate("/master/category")
      })
      .catch((err) => {
        const { message } = err.response.data
        setLoadingSubmit(false)
        toast.error(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }

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
      title="Category"
      href="/master/category"
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
        <Card title="Category Data">
          <div className="card-body row">
            <div className="col-md-6">
              <Form.Item
                label="Nama Kategori"
                name="name"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Nama Kategori!",
                  },
                ]}
              >
                <Input placeholder="Ketik Nama Kategori" />
              </Form.Item>
            </div>
            <div className="col-md-6">
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
  )
}

export default FormCategory
