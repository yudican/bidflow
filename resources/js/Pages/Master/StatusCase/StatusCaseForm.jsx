import { CheckOutlined, LoadingOutlined } from "@ant-design/icons"
import { Card, Form, Input } from "antd"
import React, { useEffect, useState } from "react"
import "react-draft-wysiwyg/dist/react-draft-wysiwyg.css"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../../components/layout"
import "../../../index.css"

const StatusCaseForm = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const { status_case_id } = useParams()

  const [loadingSubmit, setLoadingSubmit] = useState(false)

  const loadDetailBrand = () => {
    axios.get(`/api/master/status-case/${status_case_id}`).then((res) => {
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

    formData.append("status_name", values.status_name)
    formData.append("notes", values.notes || "")

    const url = status_case_id
      ? `/api/master/status-case/save/${status_case_id}`
      : "/api/master/status-case/save"

    axios
      .post(url, formData)
      .then((res) => {
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        setLoadingSubmit(false)
        return navigate("/master/status-case")
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
      title="Tambah Data Status Case"
      href="/master/status-case"
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
        <Card title="Status Case Data">
          <div className="card-body row">
            <div className="col-md-6">
              <Form.Item
                label="Status Name"
                name="status_name"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Status Name!",
                  },
                ]}
              >
                <Input placeholder="Ketik Status Name" />
              </Form.Item>
            </div>

            <div className="col-md-6">
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

export default StatusCaseForm
