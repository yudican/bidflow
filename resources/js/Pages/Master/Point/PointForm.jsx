import { CheckOutlined, LoadingOutlined } from "@ant-design/icons"
import { Card, Form, Input, Select, Table, Skeleton } from "antd"
import React, { useEffect, useState } from "react"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../../components/layout"
import { getBase64, getItem, inArray, isEqual } from "../../../helpers"
import { loadProductMaster } from "./services"

const PointForm = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const { master_point_id } = useParams()

  const [dataBrand, setDataBrand] = useState([])
  const [typePoint, setTypePoint] = useState("product")
  const [productMasters, setProductMasters] = useState([])
  const [selectedProduct, setSelectedProduct] = useState(null)
  const [loadingProductMaster, setLoadingProductMaster] = useState(false)
  const [loadingBrand, setLoadingBrand] = useState(false)
  const [loadingDetailBrand, setLoadingDetailBrand] = useState(false)
  const [loadingSubmit, setLoadingSubmit] = useState(false)
  const [productBundling, setProductBundling] = useState({}) // Updated to object
  const [dataSku, setDataSku] = useState([])
  const [detail, setDetail] = useState(null)

  useEffect(() => {
    loadBrand()
    loadProductMaster(
      (data) => setProductMasters(data),
      setLoadingProductMaster
    )
    if (master_point_id) {
      loadDetailBrand()
    }
  }, [master_point_id])

  const loadDetailBrand = () => {
    setLoadingDetailBrand(true)
    axios
      .get(`/api/master/point/${master_point_id}`)
      .then((res) => {
        const { data } = res.data

        if (!data) {
          form.setFieldValue("type", "product")
        }
        form.setFieldsValue(data)

        if (data.type) {
          setTypePoint(data.type)
        }

        if (data.product_id) {
          const product = productMasters.find(
            (item) => item.id === data.product_id
          )
          setSelectedProduct(product)
          console.log(product)
          // Ensure dataSku is populated here
          if (product) {
            setDataSku([{ sku: product.sku, name: product.name }]) // Set SKU data based on product
          }
          setTypePoint(data.type)
          console.log(data, "data")
          // Update productBundling state with existing product details
          setProductBundling({
            product_id: data.product_id,
            sku: data?.product_sku,
            uom_master: data?.product_uom,
          })

          console.log(productBundling, "productBundling")
        }
      })
      .finally(() => setLoadingDetailBrand(false))
  }

  const loadBrand = () => {
    setLoadingBrand(true)
    axios
      .get("/api/master/brand")
      .then((res) => {
        setDataBrand(res.data.data)
        setLoadingBrand(false)
      })
      .catch(() => setLoadingBrand(false))
  }

  const onFinish = (values) => {
    setLoadingSubmit(true)
    const formData = new FormData()
    formData.append("type", values.type)
    formData.append("point", values.point)
    formData.append("min_trans", values.min_trans)
    formData.append("max_trans", values.max_trans)
    formData.append("brand_id", JSON.stringify(values.brand_ids))
    formData.append("product_id", productBundling.product_id || "")
    formData.append("product_sku", productBundling.sku || "")
    formData.append("product_uom", productBundling.uom_master || "")

    const url = master_point_id
      ? `/api/master/point/save/${master_point_id}`
      : "/api/master/point/save"

    axios
      .post(url, formData)
      .then((res) => {
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        setLoadingSubmit(false)
        navigate("/master/point")
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
      title="Form Master Poin"
      href="/master/point"
      rightContent={
        <button
          onClick={() => form.submit()}
          className="text-white bg-blueColor hover:bg-blueColor/90 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
        >
          {loadingSubmit ? <LoadingOutlined /> : <CheckOutlined />}
          <span className="ml-2">Simpan</span>
        </button>
      }
    >
      <Form
        form={form}
        name="basic"
        layout="vertical"
        onFinish={onFinish}
        autoComplete="off"
      >
        <Card title="Poin Data">
          <div className="card-body ">
            <Form.Item
              label="Tipe"
              name="type"
              rules={[{ required: true, message: "Silakan pilih Tipe!" }]}
            >
              <Select
                allowClear
                className="w-full mb-2"
                placeholder="Pilih Tipe"
                onChange={(value) => setTypePoint(value)}
              >
                <Select.Option value="product">Per Product</Select.Option>
                <Select.Option value="transaction">
                  Per Transaction
                </Select.Option>
                <Select.Option value="referral">Referral</Select.Option>
                <Select.Option value="barcode">QR Code</Select.Option>
                <Select.Option value="lms">LMS</Select.Option>
              </Select>
            </Form.Item>

            <Form.Item
              label="Poin"
              name="point"
              rules={[
                { required: true, message: "Silakan masukkan Poin!" },
                {
                  pattern: /^[0-9]+$/,
                  message: "Poin harus berupa angka",
                },
                {
                  validator: (_, value) =>
                    value && value < 1
                      ? Promise.reject(
                        new Error("Poin tidak boleh kurang dari 1")
                      )
                      : Promise.resolve(),
                },
              ]}
            >
              <Input type="number" placeholder="Masukkan Poin" />
            </Form.Item>

            {typePoint === "transaction" && (
              <>
                <Form.Item
                  label="Minimal Transaksi"
                  name="min_trans"
                  rules={[
                    {
                      required: true,
                      message: "Silakan masukkan Minimal Transaksi!",
                    },
                    {
                      pattern: /^[0-9]+$/,
                      message: "Minimal Transaksi harus berupa angka",
                    },
                    {
                      validator: (_, value) =>
                        value && value < 1
                          ? Promise.reject(
                            new Error(
                              "Minimal Transaksi tidak boleh kurang dari 1"
                            )
                          )
                          : Promise.resolve(),
                    },
                  ]}
                >
                  <Input
                    type="number"
                    placeholder="Masukkan Minimal Transaksi"
                  />
                </Form.Item>
                <Form.Item
                  label="Maksimal Transaksi"
                  name="max_trans"
                  rules={[
                    {
                      required: true,
                      message: "Silakan masukkan Maksimal Transaksi!",
                    },
                    {
                      pattern: /^[0-9]+$/,
                      message: "Maksimal Transaksi harus berupa angka",
                    },
                    {
                      validator: (_, value) =>
                        value && value < 1
                          ? Promise.reject(
                            new Error(
                              "Maksimal Transaksi tidak boleh kurang dari 1"
                            )
                          )
                          : Promise.resolve(),
                    },
                  ]}
                >
                  <Input
                    type="number"
                    placeholder="Masukkan Maksimal Transaksi"
                  />
                </Form.Item>
              </>
            )}

            <Form.Item
              label="Brand"
              name="brand_ids"
              rules={[{ required: true, message: "Silakan pilih Brand!" }]}
            >
              <Select
                mode="multiple"
                allowClear
                className="w-full mb-2"
                placeholder="Pilih Brand"
                loading={loadingBrand}
              >
                {dataBrand.map((item) => (
                  <Select.Option key={item.id} value={item.id}>
                    {item.name}
                  </Select.Option>
                ))}
              </Select>
            </Form.Item>
          </div>
        </Card>

        {typePoint === "barcode" && (
          <Card title="Referensi Produk" className="mt-4">
            <div className="card-body">
              <Table
                scroll={{ x: "max-content" }}
                tableLayout={"auto"}
                dataSource={[productBundling]} // Use single item array
                columns={[
                  {
                    title: "Produk Master",
                    key: "product_id",
                    dataIndex: "product_id",
                    width: 400,
                    render: (text, record, index) => {
                      if (loadingProductMaster) {
                        return (
                          <Skeleton.Input
                            active
                            size={"default"}
                            block={false}
                            style={{ width: 400 }}
                          />
                        )
                      } else {
                        return (
                          <Select
                            className="w-full"
                            onChange={(e) => {
                              const product = productMasters.find(
                                (item) => item.id == e
                              )
                              let data = { ...productBundling }
                              data.product_id = product.id
                              data.uom_master = product.u_of_m
                              data.sku = product.sku
                              setProductBundling(data)
                              setDetail({
                                ...detail,
                                product_id: e,
                              })
                            }}
                            placeholder={"Cari Produk"}
                            value={productBundling?.product_id}
                            showSearch
                            optionFilterProp="children"
                            filterOption={(input, option) =>
                              (option?.children?.toLowerCase() ?? "").includes(
                                input.toLowerCase()
                              )
                            }
                          >
                            {productMasters.map((item) => (
                              <Select.Option
                                key={item?.id}
                                value={item?.id}
                                disabled={inArray(item.id, [
                                  productBundling.product_id,
                                ])}
                              >
                                {item?.name}
                              </Select.Option>
                            ))}
                          </Select>
                        )
                      }
                    },
                  },
                  {
                    title: "SKU",
                    key: "product_sku",
                    dataIndex: "product_sku",
                    width: 200,
                    render: (text, record) => (
                      <Input
                        className="w-full"
                        type="text"
                        value={productBundling?.sku}
                        disabled
                      />
                    ),
                  },
                  {
                    title: "UOM Master",
                    key: "uom_master",
                    dataIndex: "uom_master",
                    render: (text) => text,
                  },
                ]}
                pagination={false}
                rowKey="id"
              />
            </div>
          </Card>
        )}
      </Form>
      {/* <div className="card">
        <div className="card-body flex justify-end">
          <button
            onClick={() => form.submit()}
            className="text-white bg-blueColor hover:bg-blueColor/90 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
          >
            {loadingSubmit ? <LoadingOutlined /> : <CheckOutlined />}
            <span className="ml-2">Simpan</span>
          </button>
        </div>
      </div> */}
    </Layout>
  )
}

export default PointForm
