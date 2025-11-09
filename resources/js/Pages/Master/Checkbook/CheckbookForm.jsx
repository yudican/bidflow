import { CheckOutlined, LoadingOutlined } from "@ant-design/icons"
import { Card, Form, Input, Select } from "antd"
import React, { useEffect, useState } from "react"
import "react-draft-wysiwyg/dist/react-draft-wysiwyg.css"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../../components/layout"
import "../../../index.css"

const CheckbookForm = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const { id } = useParams()

  const [loadingSubmit, setLoadingSubmit] = useState(false)

  const loadDetailCheckbook = () => {
    axios.get(`/api/master/checkbook/${id}`).then((res) => {
      const { data } = res.data
      form.setFieldsValue(data)
    })
  }

  useEffect(() => {
    loadDetailCheckbook()
  }, [])

  const onFinish = (values) => {
    setLoadingSubmit(true)
    let formData = new FormData()

    formData.append("bank_name", values.bank_name)
    formData.append("description", values.description)
    formData.append("company_address", values.company_address)
    formData.append("bank_account", values.bank_account)
    formData.append("currency_id", values.currency_id)
    formData.append("status", values.status)

    const url = id
      ? `/api/master/checkbook/save/${id}`
      : "/api/master/checkbook/save"

    axios
      .post(url, formData)
      .then((res) => {
        toast.success("Data Checkbook berhasil disimpan", {
          position: toast.POSITION.TOP_RIGHT,
        })
        setLoadingSubmit(false)
        return navigate("/master/checkbook")
      })
      .catch((err) => {
        const { message } = err.response.data
        setLoadingSubmit(false)
        toast.error("Data Checkbook gagal disimpan", {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }

  return (
    <Layout
      title="Tambah Data Checkbook"
      href="/master/checkbook"
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
        <Card title="Checkbook Data">
          <div className="card-body row">
            <div className="col-md-6">
              <Form.Item
                label="Nama Bank"
                name="bank_name"
                rules={[
                  {
                    required: id ? false : true,
                    message: "Silakan masukkan Nama Bank!",
                  },
                ]}
              >
                <Input placeholder="Ketik Nama Bank" disabled={id} />
              </Form.Item>
            </div>
            <div className="col-md-6">
              <Form.Item
                label="Nomor Rekening"
                name="bank_account"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Nomor Rekening!",
                  },
                ]}
              >
                <Input placeholder="Ketik Nomor Rekening" />
              </Form.Item>
            </div>
            <div className="col-md-6">
              <Form.Item
                label="Currency"
                name="currency_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Currency!",
                  },
                ]}
              >
                <Input placeholder="Ketik Currency" type="text" />
              </Form.Item>
            </div>
            <div className="col-md-6">
              <Form.Item
                label="Company Address"
                name="company_address"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Company Address!",
                  },
                ]}
              >
                <Input placeholder="Ketik Company Address" />
              </Form.Item>
            </div>
            <div className="col-md-6">
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
                <Input placeholder="Ketik Description" type="text" />
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
                  <Select.Option value={"Active"}>Active</Select.Option>
                  <Select.Option value={"InActive"}>Non Active</Select.Option>
                </Select>
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

export default CheckbookForm
