import { CheckOutlined, LoadingOutlined } from "@ant-design/icons"
import { Card, Form, Input } from "antd"
import React, { useEffect, useState } from "react"
import "react-draft-wysiwyg/dist/react-draft-wysiwyg.css"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../../components/layout"
import "../../../index.css"

const MasterPphForm = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const { master_pph_id } = useParams()

  const [loadingSubmit, setLoadingSubmit] = useState(false)

  const loadDetailBrand = () => {
    axios.get(`/api/master/master-pph/${master_pph_id}`).then((res) => {
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

    formData.append("pph_title", values.pph_title)
    formData.append("pph_percentage", values.pph_percentage)
    formData.append("pph_amount", values.pph_amount)

    const url = master_pph_id
      ? `/api/master/master-pph/save/${master_pph_id}`
      : "/api/master/master-pph/save"

    axios
      .post(url, formData)
      .then((res) => {
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        setLoadingSubmit(false)
        return navigate("/master/master-pph")
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
      title="Tambah Data PPH"
      href="/master/master-pph"
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
        <Card title="Pph Data">
          <div className="card-body row">
            <div className="col-md-6">
              <Form.Item
                label="Title"
                name="pph_title"
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
                label="Condition"
                name="condition"
                rules={[
                  {
                    required: false,
                    message: "Silakan masukkan Condition!",
                  },
                ]}
              >
                <Input
                  placeholder="LessThanOrEqual"
                  defaultValue={"LessThanOrEqual"}
                  disabled
                />
              </Form.Item>
            </div>
            <div className="col-md-6">
              <Form.Item
                label="Percentage (%)"
                name="pph_percentage"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Percentage!",
                  },
                ]}
              >
                <Input placeholder="Ketik Percentage " type="number" />
              </Form.Item>

              <Form.Item
                label="Nominal (Rp)"
                name="pph_amount"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Nominal!",
                  },
                ]}
              >
                <Input placeholder="Ketik Nominal " type="number" />
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

export default MasterPphForm
