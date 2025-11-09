import { CheckOutlined, LoadingOutlined } from "@ant-design/icons"
import { Card, DatePicker, Form, Input, Select } from "antd"
import React, { useEffect, useState } from "react"
import "react-draft-wysiwyg/dist/react-draft-wysiwyg.css"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../../components/layout"
import "../../../index.css"
import moment from "moment"

const SkuForm = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const { sku_id } = useParams()

  const [loadingSubmit, setLoadingSubmit] = useState(false)
  const [packages, setPackages] = useState([])

  const loadDetailBrand = () => {
    axios.get(`/api/master/sku/${sku_id}`).then((res) => {
      const { data } = res.data
      const forms = {
        ...data,
        expired_at: moment(data?.expired_at ?? new Date(), "YYYY-MM-DD"),
      }
      form.setFieldsValue(forms)
    })
  }

  const loadPackages = () => {
    axios.get("/api/master/package").then((res) => {
      const { data } = res.data
      setPackages(data)
    })
  }

  useEffect(() => {
    loadDetailBrand()
    loadPackages()
  }, [])

  const onFinish = (values) => {
    setLoadingSubmit(true)
    let formData = new FormData()

    formData.append("sku", values.sku)
    formData.append("package_id", values.package_id)
    formData.append("expired_at", values.expired_at.format("YYYY-MM-DD"))

    const url = sku_id
      ? `/api/master/sku/save/${sku_id}`
      : "/api/master/sku/save"

    axios
      .post(url, formData)
      .then((res) => {
        toast.success("Data Sku Master berhasil disimpan", {
          position: toast.POSITION.TOP_RIGHT,
        })
        setLoadingSubmit(false)
        return navigate("/master/sku")
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
      title="Tambah Data SKU"
      href="/master/sku"
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
        <Card title="SKU Data">
          <div className="card-body row">
            <div className="col-md-4">
              <Form.Item
                label="SKU"
                name="sku"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Sku!",
                  },
                ]}
              >
                <Input placeholder="Ketik SKU" />
              </Form.Item>
            </div>
            <div className="col-md-4">
              <Form.Item
                label="Package"
                name="package_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Package!",
                  },
                ]}
              >
                <Select
                  allowClear
                  className="w-full"
                  placeholder="Pilih Package"
                >
                  {packages.map((item) => (
                    <Select.Option key={item.id} value={item.id}>
                      {item.name}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>
            </div>
            <div className="col-md-4">
              <Form.Item
                label="Expired At"
                name="expired_at"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan tanggal expired!",
                  },
                ]}
              >
                <DatePicker className="w-full" format={"DD-MM-YYYY"} />
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

export default SkuForm
