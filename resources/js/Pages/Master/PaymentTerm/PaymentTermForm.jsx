import { CheckOutlined, LoadingOutlined } from "@ant-design/icons"
import { Card, Form, Input } from "antd"
import React, { useEffect, useState } from "react"
import "react-draft-wysiwyg/dist/react-draft-wysiwyg.css"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../../components/layout"
import RichtextEditor from "../../../components/RichtextEditor"
import "../../../index.css"

const PaymentTermForm = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const { payment_term_id } = useParams()

  const [loadingSubmit, setLoadingSubmit] = useState(false)

  const loadDetailBrand = () => {
    axios.get(`/api/master/payment-term/${payment_term_id}`).then((res) => {
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
    formData.append("days_of", values.days_of)
    formData.append("description", values.description)

    const url = payment_term_id
      ? `/api/master/payment-term/save/${payment_term_id}`
      : "/api/master/payment-term/save"

    axios
      .post(url, formData)
      .then((res) => {
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        setLoadingSubmit(false)
        return navigate("/master/payment-term")
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
      title="Tambah Data Payment Term"
      href="/master/payment-term"
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
        <Card title="Payment Term Data">
          <div className="card-body row">
            <div className="col-md-6">
              <Form.Item
                label="Name"
                name="name"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Name!",
                  },
                ]}
              >
                <Input placeholder="Ketik Name" />
              </Form.Item>
            </div>
            <div className="col-md-6">
              <Form.Item
                label="Days of"
                name="days_of"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Days of!",
                  },
                  {
                    type: "number",
                    min: 1,
                    message: "Nilai yang dimasukkan harus lebih dari 0",
                  },
                ]}
              >
                <Input
                  placeholder="Ketik Days of"
                  type="number"
                  onChange={(e) => {
                    const inputValue = e.target.value
                    // Convert the input value to a number and ensure it's positive
                    const numericValue =
                      inputValue !== "" ? Math.abs(Number(inputValue)) : ""

                    form.setFieldsValue({ days_of: numericValue })
                  }}
                />
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

export default PaymentTermForm
