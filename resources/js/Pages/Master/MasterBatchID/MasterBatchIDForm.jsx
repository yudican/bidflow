import { CheckOutlined, LoadingOutlined } from "@ant-design/icons"
import { Card, Form, Input } from "antd"
import React, { useEffect, useState } from "react"
import "react-draft-wysiwyg/dist/react-draft-wysiwyg.css"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../../components/layout"
import "../../../index.css"

const MasterBatchIDForm = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const { master_batch_id } = useParams()

  const [loadingSubmit, setLoadingSubmit] = useState(false)

  const loadDetail = () => {
    axios.get(`/api/master/batchId/${master_batch_id}`).then((res) => {
      const { data } = res.data
      form.setFieldsValue(data)
    })
  }

  useEffect(() => {
    loadDetail()
  }, [])

  const onFinish = (values) => {
    setLoadingSubmit(true)
    let formData = new FormData()

    formData.append("batch_code", values.batch_code)
    formData.append("origin", values.origin)
    formData.append("status", values.status)
    formData.append("frequency", values.frequency)

    const url = master_batch_id
      ? `/api/master/batchId/save/${master_batch_id}`
      : "/api/master/batchId/save"

    axios
      .post(url, formData)
      .then((res) => {
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        setLoadingSubmit(false)
        return navigate("/master/batch-id")
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
      title="Tambah Batch ID"
      href="/master/batch-id"
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
        <Card title="Tax Data">
          <div className="card-body row">
            <div className="col-md-6">
              <Form.Item
                label="Batch ID"
                name="batch_code"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Batch ID!",
                  },
                ]}
              >
                <Input placeholder="Ketik Batch ID" />
              </Form.Item>
              <Form.Item
                label="Origin"
                name="origin"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Origin!",
                  },
                ]}
              >
                <Input placeholder="Ketik Origin" />
              </Form.Item>
            </div>
            <div className="col-md-6">
              <Form.Item
                label="Status"
                name="status"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Status!",
                  },
                ]}
              >
                <Input placeholder="Ketik Status" />
              </Form.Item>
              <Form.Item
                label="Frequency"
                name="frequency"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Frequency!",
                  },
                ]}
              >
                <Input placeholder="Ketik Frequency" />
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

export default MasterBatchIDForm
