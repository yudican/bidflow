import { CheckOutlined, LoadingOutlined } from "@ant-design/icons"
import { Card, Form, Input } from "antd"
import React, { useEffect, useState } from "react"
import "react-draft-wysiwyg/dist/react-draft-wysiwyg.css"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../../components/layout"
import "../../../index.css"

const PriorityCaseForm = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const { priority_case_id } = useParams()

  const [loadingSubmit, setLoadingSubmit] = useState(false)

  const loadDetailBrand = () => {
    axios.get(`/api/master/priority-case/${priority_case_id}`).then((res) => {
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

    formData.append("priority_name", values.priority_name)
    formData.append("priority_day", values.priority_day)
    formData.append("notes", values.notes)

    const url = priority_case_id
      ? `/api/master/priority-case/save/${priority_case_id}`
      : "/api/master/priority-case/save"

    axios
      .post(url, formData)
      .then((res) => {
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        setLoadingSubmit(false)
        return navigate("/master/priority-case")
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
      title="Tambah Data Priority Case"
      href="/master/priority-case"
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
        <Card title="Priority Case Data">
          <div className="card-body row">
            <div className="col-md-6">
              <Form.Item
                label="Priority Name"
                name="priority_name"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Priority Name!",
                  },
                ]}
              >
                <Input placeholder="Ketik Priority Name" />
              </Form.Item>
            </div>

            <div className="col-md-6">
              <Form.Item
                label="Priority Day"
                name="priority_day"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Priority Day!",
                  },
                  {
                    type: "number",
                    min: 1,
                    message: "Nilai yang dimasukkan harus lebih dari 0",
                  },
                ]}
              >
                <Input
                  placeholder="Ketik Priority Day"
                  type="number"
                  onChange={(e) => {
                    const inputValue = e.target.value
                    // Convert the input value to a number and ensure it's positive
                    const numericValue =
                      inputValue !== "" ? Math.abs(Number(inputValue)) : ""

                    form.setFieldsValue({ priority_day: numericValue })
                  }}
                />
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

export default PriorityCaseForm
