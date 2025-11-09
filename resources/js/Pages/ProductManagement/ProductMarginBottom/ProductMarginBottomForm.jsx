import { CheckOutlined, LoadingOutlined } from "@ant-design/icons"
import { Card, Form, Input, Select } from "antd"
import TextArea from "antd/lib/input/TextArea"
import React, { useEffect, useState } from "react"
import "react-draft-wysiwyg/dist/react-draft-wysiwyg.css"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../../components/layout"
import "../../../index.css"
import ProductModal from "./Components/ProductModal"

const ProductMarginBottomForm = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const { product_margin_id } = useParams()

  const [roles, setRoles] = useState([])
  const [roleId, setRoleId] = useState(null)

  const [loadingSubmit, setLoadingSubmit] = useState(false)
  const [selectedProduct, setSelectedProduct] = useState(null)

  const loadDetailCategory = () => {
    const params = product_margin_id ? `/${product_margin_id}` : ""
    axios
      .get(`/api/product-management/margin-bottom/detail${params}`)
      .then((res) => {
        const { margin, roles } = res.data.data
        setRoles(roles)
        setSelectedProduct({
          id: margin.product_variant_id,
          name: margin.product_name,
          product_image: margin.product_image,
          final_price: margin.basic_price,
        })
        setRoleId(margin.role_id)
        form.setFieldsValue(margin)
      })
  }

  useEffect(() => {
    loadDetailCategory()
  }, [])

  const onFinish = (values) => {
    setLoadingSubmit(true)
    let formData = new FormData()
    if (values.margin > values.basic_price) {
      form.setFields([
        {
          name: "margin",
          errors: ["Margin tidak boleh lebih besar dari harga product"],
        },
      ])
      setLoadingSubmit(false)
      return
    }
    formData.append("product_variant_id", values.product_variant_id)
    formData.append("role_id", values.role_id)
    formData.append("margin", values.margin)
    formData.append("basic_price", values.basic_price)
    formData.append("description", values.description || "-")

    const url = product_margin_id
      ? `/api/product-management/margin-bottom/save/${product_margin_id}`
      : "/api/product-management/margin-bottom/save"

    axios
      .post(url, formData)
      .then((res) => {
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        setLoadingSubmit(false)
        return navigate("/product-management/margin-bottom")
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
    <>
      <Layout
        title="Margin Bottom"
        href="/product-management/margin-bottom"
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
          <Card title="Category Data">
            <div className="card-body row">
              <div className="col-md-6">
                <Form.Item
                  label="Role"
                  name="role_id"
                  rules={[
                    {
                      required: true,
                      message: "Silakan pilih Role!",
                    },
                  ]}
                >
                  <Select
                    allowClear
                    className="w-full"
                    placeholder="Pilih Role"
                    onChange={(e) => setRoleId(e)}
                  >
                    {roles.map((role) => (
                      <Select.Option key={role.id} value={role.id}>
                        {role.role_name}
                      </Select.Option>
                    ))}
                  </Select>
                </Form.Item>

                <Form.Item label="Final Price" name="basic_price">
                  <Input type="number" readOnly disabled />
                </Form.Item>
              </div>
              <div className="col-md-6">
                <Form.Item
                  label="Pilih Product"
                  name="product_variant_id"
                  rules={[
                    {
                      required: true,
                      message: "Silakan pilih Product!",
                    },
                  ]}
                >
                  <ProductModal
                    handleSelect={(e) => setSelectedProduct(e)}
                    selectedProduct={selectedProduct}
                    form={form}
                    paramsData={{ role_id: roleId }}
                  />
                </Form.Item>
                <Form.Item
                  label="Margin Price"
                  name="margin"
                  rules={[
                    {
                      required: true,
                      message: "Silakan masukkan Margin Price!",
                    },
                  ]}
                >
                  <Input type="number" />
                </Form.Item>
              </div>
              <div className="col-md-12">
                <Form.Item label="Description" name="description">
                  <TextArea rows={5} />
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

export default ProductMarginBottomForm
