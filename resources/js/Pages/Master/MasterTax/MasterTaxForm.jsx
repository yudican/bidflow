import { CheckOutlined, LoadingOutlined } from "@ant-design/icons"
import { Card, Form, Input } from "antd"
import React, { useEffect, useState } from "react"
import "react-draft-wysiwyg/dist/react-draft-wysiwyg.css"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../../components/layout"
import "../../../index.css"
import axios from "axios"
const MasterTaxForm = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const { master_tax_id } = useParams()

  const [loadingSubmit, setLoadingSubmit] = useState(false)

  const loadDetailBrand = () => {
    axios.get(`/api/master/master-tax/${master_tax_id}`).then((res) => {
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

    formData.append("tax_code", values.tax_code)
    formData.append("tax_percentage", values.tax_percentage)

    const url = master_tax_id
      ? `/api/master/master-tax/save/${master_tax_id}`
      : "/api/master/master-tax/save"

    axios
      .post(url, formData)
      .then((res) => {
        toast.success("Data Master Tax berhasil disimpan", {
          position: toast.POSITION.TOP_RIGHT,
        })
        setLoadingSubmit(false)
        return navigate("/master/tax")
      })
      .catch((err) => {
        const { message } = err.response.data
        setLoadingSubmit(false)
        toast.error("Data Master Tax gagal disimpan", {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }

  return (
    <Layout
      title="Tambah Data Tax"
      href="/master/tax"
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
                label="Tax Code"
                name="tax_code"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Tax Code!",
                  },
                ]}
              >
                <Input placeholder="Ketik Tax Code" />
              </Form.Item>
            </div>
            <div className="col-md-6">
              <Form.Item
                label="Tax Percentage"
                name="tax_percentage"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Tax Percentage!",
                  },
                  {
                    type: "number",
                    min: 0,
                    message: "Tax Percentage tidak boleh kurang dari 0",
                    transform: (value) => {
                      if (value === "" || value === null) return null
                      return Number(value)
                    },
                  },
                ]}
              >
                <Input
                  placeholder="Ketik Tax Percentage "
                  type="number"
                  min="0"
                  onChange={(e) => {
                    const inputValue = e.target.value
                    form.setFieldsValue({ tax_percentage: inputValue })
                  }}
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

export default MasterTaxForm
