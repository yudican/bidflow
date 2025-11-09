import { LoadingOutlined, PlusOutlined, SaveOutlined } from "@ant-design/icons"
import {
  Button,
  Card,
  Form,
  Input,
  Select,
  Skeleton,
  Divider,
  Space,
  InputNumber,
} from "antd"
import React, { useEffect, useState } from "react"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../../components/layout"
import { getBase64 } from "../../../helpers"
import axios from "axios"

const ProductCartonForm = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const { product_carton_id } = useParams()

  const [vendors, setVendors] = useState([])
  const [vendorCode, setVendorCode] = useState(null)
  const [dataSku, setDataSku] = useState([])
  const [packages, setDataPackages] = useState([])
  const [dataBrand, setDataBrand] = useState(null)

  // loading
  const [loading, setLoading] = useState(false)

  const loadDetailBrand = () => {
    axios.get(`/api/master/product-carton/${product_carton_id}`).then((res) => {
      const { data } = res.data

      setDataBrand(data)
      form.setFieldsValue({
        ...data,
        moq: data?.moq ? { value: data.moq, label: data.package } : undefined,
      })
    })
  }

  const loadVendors = () => {
    axios.get("/api/master/vendors").then((res) => {
      setVendors(res.data.data)
    })
  }

  const loadSku = () => {
    axios.get("/api/master/sku").then((res) => {
      const { data } = res.data
      setDataSku(data)
    })
  }

  const loadPackages = () => {
    axios.get("/api/master/package").then((res) => {
      const { data } = res.data
      setDataPackages(data)
    })
  }

  useEffect(() => {
    loadDetailBrand()
    loadVendors()
    loadSku()
    loadPackages()
  }, [])

  const onFinish = (values) => {
    setLoading(true)

    const url = product_carton_id ? `save/${product_carton_id}` : "save"
    console.log(values)
    axios
      .post(`/api/master/product-carton/${url}`, {
        ...values,
        moq: values?.moq?.value || values?.moq,
      })
      .then((res) => {
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        setLoading(false)
        return navigate("/master/produk-karton")
      })
      .catch((err) => {
        const { message } = err.response.data
        toast.error(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        setLoading(false)
      })
  }

  return (
    <Layout
      title={dataBrand ? "Detail Produk Karton Data" : "Create Produk Karton"}
      href="/master/produk-karton"
    >
      <Form
        form={form}
        name="basic"
        layout="vertical"
        onFinish={onFinish}
        autoComplete="off"
      >
        <Card title="Produk Karton Data">
          <div className="card-body row">
            <div className="col-md-6">
              <Form.Item
                label="Vendor"
                name="vendor_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Vendor Code!",
                  },
                ]}
              >
                <Select
                  showSearch
                  filterOption={(input, option) => {
                    return (option?.label ?? "")
                      .toLowerCase()
                      .includes(input.toLowerCase())
                  }}
                  className="w-full"
                  placeholder="Pilih vendor"
                  onChange={(value) => {
                    const vendor = vendors.find((item) => item.code === value)
                    form.setFieldsValue({
                      vendor_code: value,
                      vendor_name: vendor.name,
                    })
                    setShowSelect(false)
                  }}
                  options={vendors.map((vendor) => {
                    return {
                      value: vendor.code,
                      label: vendor.code,
                    }
                  })}
                />
              </Form.Item>

              <Form.Item
                label="Product Name"
                name="product_name"
                rules={[
                  {
                    required: true,
                    message: "Silahkan masukkan Product Name!",
                  },
                ]}
              >
                <Input placeholder="Ketik Product Name" />
              </Form.Item>
            </div>
            <div className="col-md-6">
              <Form.Item
                label="SKU"
                name="sku"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Sku!",
                  },
                ]}
              >
                {loading ? (
                  <Skeleton.Input active size={"default"} block />
                ) : (
                  <Select
                    // mode="multiple"
                    allowClear
                    className="w-full"
                    placeholder="Pilih SKU"
                    showSearch
                    filterOption={(input, option) => {
                      return (option?.children ?? "")
                        .toLowerCase()
                        .includes(input.toLowerCase())
                    }}
                  >
                    {dataSku.map((item) => (
                      <Select.Option key={item.id} value={item.sku}>
                        {`${item.sku}`}
                      </Select.Option>
                    ))}
                  </Select>
                )}
              </Form.Item>
              <Form.Item
                label="Qty"
                name="qty"
                rules={[
                  {
                    required: true,
                    message: "Silahkan masukkan qty!",
                  },
                  ({ getFieldValue }) => ({
                    validator(_, value) {
                      if (!value || Number(value) >= 1) {
                        return Promise.resolve()
                      }
                      return Promise.reject(
                        new Error("Qty tidak boleh kurang dari 1!")
                      )
                    },
                  }),
                ]}
              >
                <Input type="number" placeholder="Ketik Qty" />
              </Form.Item>
            </div>

            <div className="col-md-6">
              <Form.Item
                label="UoM"
                name="moq"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih UoM!",
                  },
                ]}
              >
                {loading ? (
                  <Skeleton.Input
                    active
                    size={"default"}
                    block={false}
                    style={{ width: 500 }}
                  />
                ) : (
                  <Select allowClear className="w-full" placeholder="Pilih UoM">
                    {packages.map((item) => (
                      <Select.Option key={item.id} value={item.id}>
                        {item.name}
                      </Select.Option>
                    ))}
                  </Select>
                )}
              </Form.Item>
            </div>
          </div>
        </Card>
      </Form>

      <div className="float-right mt-6">
        <button
          onClick={() => {
            loading ? null : form.submit()
          }}
          className="text-white bg-blueColor hover:bg-blueColor/90 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
          disabled={loading}
        >
          {loading ? <LoadingOutlined /> : <SaveOutlined />}
          <span className="ml-2">Simpan</span>
        </button>
      </div>
    </Layout>
  )
}

export default ProductCartonForm
