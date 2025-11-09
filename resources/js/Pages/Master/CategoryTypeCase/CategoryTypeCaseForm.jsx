import { CheckOutlined, LoadingOutlined } from "@ant-design/icons"
import { Card, Form, Input, Select } from "antd"
import React, { useEffect, useState } from "react"
import "react-draft-wysiwyg/dist/react-draft-wysiwyg.css"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../../components/layout"
import "../../../index.css"

const CategoryTypeCaseForm = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const { category_type_case_id } = useParams()

  const [loadingSubmit, setLoadingSubmit] = useState(false)
  const [typeCase, setTypeCase] = useState([])

  const loadDetailBrand = () => {
    axios
      .get(`/api/master/category-type-case/${category_type_case_id}`)
      .then((res) => {
        const { data } = res.data
        form.setFieldsValue(data)
      })
  }

  const loadTypeCase = () => {
    axios.get(`/api/master/type-case`).then((res) => {
      const { data } = res.data
      setTypeCase(data)
    })
  }

  useEffect(() => {
    loadDetailBrand()
    loadTypeCase()
  }, [])

  const onFinish = (values) => {
    setLoadingSubmit(true)
    let formData = new FormData()

    formData.append("type_id", values.type_id)
    formData.append("category_name", values.category_name)
    formData.append("notes", values.notes || "")

    const url = category_type_case_id
      ? `/api/master/category-type-case/save/${category_type_case_id}`
      : "/api/master/category-type-case/save"

    axios
      .post(url, formData)
      .then((res) => {
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        setLoadingSubmit(false)
        return navigate("/master/category-type-case")
      })
      .catch((err) => {
        const { message } = err.response.data
        setLoadingSubmit(false)
        toast.error(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }

  return (
    <Layout
      title="Category type case"
      href="/master/category-type-case"
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
        <Card title="Category type case Data">
          <div className="card-body row">
            <div className="col-md-6">
              <Form.Item
                label="Type Case Name"
                name="type_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Type Case Name!",
                  },
                ]}
              >
                <Select
                  allowClear
                  className="w-full"
                  placeholder="Pilih Type Case Name"
                >
                  {typeCase.map((item) => (
                    <Select.Option key={item.id} value={item.id}>
                      {item.type_name}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>
            </div>
            <div className="col-md-6">
              <Form.Item
                label="Category Name"
                name="category_name"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Category Name!",
                  },
                ]}
              >
                <Input placeholder="Ketik Category Name " />
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
                <Input placeholder="Ketik Notes " />
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

export default CategoryTypeCaseForm
