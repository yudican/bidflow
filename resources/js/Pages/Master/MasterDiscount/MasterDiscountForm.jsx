import { CheckOutlined, LoadingOutlined } from "@ant-design/icons"
import { Card, Form, Input, Select } from "antd"
import React, { useEffect, useState } from "react"
import "react-draft-wysiwyg/dist/react-draft-wysiwyg.css"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../../components/layout"
import "../../../index.css"

const MasterDiscountForm = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const { master_discount_id } = useParams()

  const [loadingSubmit, setLoadingSubmit] = useState(false)

  const loadDetailBrand = () => {
    axios
      .get(`/api/master/master-discount/${master_discount_id}`)
      .then((res) => {
        const { data } = res.data
        console.log(data, "data")
        form.setFieldsValue(data)
      })
  }

  useEffect(() => {
    loadDetailBrand()
  }, [])

  const onFinish = (values) => {
    setLoadingSubmit(true)
    let formData = new FormData()

    formData.append("title", values.title)
    formData.append("percentage", values.percentage)
    formData.append("sales_tag", values.sales_tag)
    formData.append("sales_channel", JSON.stringify(values.sales_channels))

    const url = master_discount_id
      ? `/api/master/master-discount/save/${master_discount_id}`
      : "/api/master/master-discount/save"

    axios
      .post(url, formData)
      .then((res) => {
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        setLoadingSubmit(false)
        return navigate("/master/master-discount")
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
      title="Tambah Data Discount"
      href="/master/master-discount"
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
        <Card title="Discount Data">
          <div className="card-body row">
            <div className="col-md-6">
              <Form.Item
                label="Title"
                name="title"
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
                label="Sales Tag"
                name="sales_tag"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Sales Tag!",
                  },
                ]}
              >
                <Select
                  allowClear
                  className="w-full mb-2"
                  placeholder="Pilih Sales Tag"
                >
                  <Select.Option value={"corner"}>Corner</Select.Option>
                  <Select.Option value={"agent-portal"}>
                    Agent Portal
                  </Select.Option>
                  <Select.Option value={"distributor"}>
                    Distributor
                  </Select.Option>
                  <Select.Option value={"super-agent"}>
                    Super Agent
                  </Select.Option>
                  <Select.Option value={"modern-store"}>
                    Modern Store
                  </Select.Option>
                  <Select.Option value={"e-store"}>E-Store</Select.Option>
                </Select>
              </Form.Item>
            </div>
            <div className="col-md-6">
              <Form.Item
                label="Discount Percentage"
                name="percentage"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Discount Percentage!",
                  },
                  {
                    pattern: /^[0-9]+$/,
                    message: "Discount Percentage harus berupa angka",
                  },
                  {
                    validator: (_, value) => {
                      if (value) {
                        if (value < 1) {
                          return Promise.reject(
                            new Error(
                              "Discount Percentage tidak boleh kurang dari 1"
                            )
                          )
                        }

                        if (value > 100) {
                          return Promise.reject(
                            new Error(
                              "Discount Percentage tidak boleh lebih dari 100"
                            )
                          )
                        }
                      }

                      return Promise.resolve()
                    },
                  },
                ]}
              >
                <Input
                  placeholder="Ketik Discount Percentage 1-100%"
                  type="number"
                />
              </Form.Item>

              <Form.Item
                label="Sales Channel"
                name="sales_channels"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Sales Channel!",
                  },
                ]}
              >
                <Select
                  mode="multiple"
                  allowClear
                  className="w-full mb-2"
                  placeholder="Pilih Sales Channel"
                >
                  <Select.Option value={"customer-portal"}>
                    Customer Portal
                  </Select.Option>
                  <Select.Option value={"agent-portal"}>
                    Agent Portal
                  </Select.Option>
                  <Select.Option value={"sales-offline"}>
                    Sales Offline
                  </Select.Option>
                  <Select.Option value={"marketplace"}>
                    Marketplace
                  </Select.Option>
                  <Select.Option value={"telmark"}>Telmark</Select.Option>
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

export default MasterDiscountForm
