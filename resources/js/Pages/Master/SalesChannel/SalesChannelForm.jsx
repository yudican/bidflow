import { CheckOutlined, LoadingOutlined } from "@ant-design/icons"
import { Card, Form, Input, Select } from "antd"
import React, { useEffect, useState } from "react"
import "react-draft-wysiwyg/dist/react-draft-wysiwyg.css"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../../components/layout"
import "../../../index.css"

const SalesChannelForm = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const { sales_channel_id } = useParams()

  const [loadingSubmit, setLoadingSubmit] = useState(false)
  const [warehouses, setWarehouses] = useState([])

  const loadDetailBrand = () => {
    axios.get(`/api/master/sales-channel/${sales_channel_id}`).then((res) => {
      const { data } = res.data
      form.setFieldsValue(data)
    })
  }

  const loadWarehouse = () => {
    axios.get("/api/master/warehouse").then((res) => {
      setWarehouses(res.data.data)
    })
  }

  useEffect(() => {
    loadDetailBrand()
    loadWarehouse()
  }, [])

  const onFinish = (values) => {
    setLoadingSubmit(true)

    const url = sales_channel_id
      ? `/api/master/sales-channel/save/${sales_channel_id}`
      : "/api/master/sales-channel/save"

    axios
      .post(url, values)
      .then((res) => {
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        setLoadingSubmit(false)
        return navigate("/master/sales-channel")
      })
      .catch((err) => {
        const { message } = err.response.data
        setLoadingSubmit(false)
        toast.error(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }

  const rightContent = (
    <div className="flex justify-between items-center">
      <button
        onClick={() => form.submit()}
        className="text-white bg-blueColor hover:bg-blueColor/90 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
      >
        {loadingSubmit ? <LoadingOutlined /> : <CheckOutlined />}
        <span className="ml-2">Simpan</span>
      </button>
    </div>
  )

  return (
    <>
      <Layout
        title="Sales Channel"
        href="/master/sales-channel"
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
          <Card title="Sales Channel Data">
            <div className="card-body row">
              <div className="col-md-6">
                <Form.Item
                  label="Channel Name"
                  name="channel_name"
                  rules={[
                    {
                      required: true,
                      message: "Silakan masukkan Channel Name!",
                    },
                  ]}
                >
                  <Input placeholder="Ketik Channel Name" />
                </Form.Item>
              </div>
              <div className="col-md-6">
                <Form.Item
                  label="Warehouse"
                  name="warehouse_id"
                  rules={[
                    {
                      required: true,
                      message: "Silakan pilih Warehouse!",
                    },
                  ]}
                >
                  <Select
                    allowClear
                    className="w-full mb-2"
                    placeholder="Pilih Warehouse"
                  >
                    <Select.Option value={0}>Semua Warehouse</Select.Option>
                    {warehouses.map((warehouse) => (
                      <Select.Option key={warehouse.id} value={warehouse.id}>
                        {warehouse.name}
                      </Select.Option>
                    ))}
                  </Select>
                </Form.Item>
              </div>
            </div>
          </Card>
        </Form>
      </Layout>

      <div className="card ">
        <div className="card-body flex justify-end">
          <button
            onClick={() => form.submit()}
            className="text-white bg-blueColor hover:bg-blueColor/90 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
          >
            {loadingSubmit ? <LoadingOutlined /> : <CheckOutlined />}
            <span className="ml-2">Simpan</span>
          </button>
        </div>
      </div>
    </>
  )
}

export default SalesChannelForm
