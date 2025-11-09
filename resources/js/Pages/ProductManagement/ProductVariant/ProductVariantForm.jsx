import {
  CheckOutlined,
  DeleteFilled,
  DeleteOutlined,
  LoadingOutlined,
  PlusOutlined,
} from "@ant-design/icons"
import {
  Card,
  Form,
  Input,
  Modal,
  Select,
  Skeleton,
  Table,
  Upload,
  message,
} from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import "react-draft-wysiwyg/dist/react-draft-wysiwyg.css"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import RichtextEditor from "../../../components/RichtextEditor"
import Layout from "../../../components/layout"
import { getBase64, getItem, inArray, isEqual } from "../../../helpers"
import {
  deleteProductVariantBundling,
  loadDetailProduct,
  loadPackages,
  loadProductMaster,
  loadProductVariant,
  loadSku,
  loadVariant,
} from "./services"
// import "../../../index.css";

const bundlingItems = [
  {
    key: 0,
    id: 0,
    product_id: null,
    product_variant_id: null,
    package_id: null,
    stock_master: 0,
    uom_master: null,
    qty_variant: 0,
    uom: null,
    sku: null,
  },
]

const ProductVariantForm = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const { product_variant_id } = useParams()

  const [pricesData, setPricesData] = useState([])
  const [tmpPrices, setTmpPrices] = useState([])
  const [packages, setDataPackages] = useState([])
  const [productVariants, setProductVariants] = useState([])
  const [productMasters, setProductMasters] = useState([])
  const [productVariantOriginals, setProductVariantOriginals] = useState([])
  const [productBundlings, setProductBundlings] = useState(bundlingItems)
  const [variants, setDataVariants] = useState([])
  const [dataSku, setDataSku] = useState([])

  const [imageLoading, setImageLoading] = useState(false)
  const [imageUrl, setImageUrl] = useState(false)
  const [fileList, setFileList] = useState(false)

  const [loading, setLoading] = useState(false)
  const [loadingProductMaster, setLoadingProductMaster] = useState(false)
  const [loadingSubmit, setLoadingSubmit] = useState(false)

  const [isBundling, setIsBundling] = useState(null)
  const [detail, setDetail] = useState(null)
  const [fileListMultiple, setFileListMultiple] = useState([])
  useEffect(() => {
    loadProductMaster(
      (data) => setProductMasters(data),
      setLoadingProductMaster
    )
  }, [])

  useEffect(() => {
    if (productMasters.length > 0) {
      loadPackages((data) => setDataPackages(data))
      loadSku((data) => setDataSku(data))
      loadVariant((data) => setDataVariants(data))
      loadProductVariant((data) => setProductVariants(data))

      product_variant_id && setLoading(true)

      loadDetailProduct(product_variant_id, (data) => {
        const { product, prices } = data

        setPricesData(prices)

        setTmpPrices(
          prices.map((item) => {
            return {
              ...item,
              type: "tmp",
            }
          })
        )

        if (product) {
          setDetail(product)
          setImageUrl(product?.image_url)
          // setSelectedProduct(product?.product)
          form.setFieldsValue(product)
          setIsBundling(product?.is_bundling)
          setLoading(false)
          const productStock = productMasters.find(
            (item) => item.id == product?.product_id
          )
          const images = product.product_images.map((item) => ({
            uid: item.id,
            name: item.name,
            status: "done",
            url: item.image_url,
          }))
          setFileListMultiple(images)

          if (product?.is_bundling > 0) {
            if (product?.bundlings && product?.bundlings.length > 0) {
              const bundlings = product?.bundlings.map((item, index) => {
                return {
                  key: index,
                  id: item.id,
                  product_id: item.product_id,
                  product_variant_id: item.product_variant_id,
                  package_id: item.package_id,
                  stock_master: item?.product?.final_stock,
                  uom_master: item?.package?.name,
                  qty_variant: item.product_qty,
                  uom: item.package_id,
                  sku: item.sku,
                }
              })

              setProductBundlings(bundlings)
              setProductVariantOriginals(
                product?.bundlings.map((item) => item.product_id)
              )
            } else {
              const newProduct = {
                key: 0,
                id: 0,
                product_id: product.product_id,
                product_variant_id: product.id,
                package_id: product.package_id,
                stock_master: product?.product?.final_stock,
                uom_master: product?.package?.name,
                qty_variant: product.qty_bundling,
                uom: product.package_id,
                sku: product.sku,
              }

              setProductBundlings([newProduct])
            }
          } else {
            const newProduct = {
              key: 0,
              id: 0,
              product_id: product.product_id,
              product_variant_id: product.id,
              package_id: product.package_id,
              stock_master: productStock?.stock_by_warehouse,
              uom_master: product?.package?.name,
              qty_variant: product.qty_bundling,
              uom: product.package_id,
              sku: product.sku,
            }

            setProductBundlings([newProduct])
          }
        }
      })
    }
  }, [productMasters])

  const handleChange = ({ fileList }) => {
    const list = fileList.pop()
    console.log(list)
    setImageLoading(true)
    setTimeout(() => {
      const size = list.size / 1024
      if (size > 205) {
        setImageLoading(false)
        return message.error("Maksimum ukuran file adalah 200 KB")
      }
      getBase64(list.originFileObj, (url) => {
        setImageLoading(false)
        setImageUrl(url)
      })
      setFileList(list.originFileObj)
    }, 1000)
  }

  function compareObjects(obj1, obj2) {
    return new Promise((resolve, reject) => {
      if (!isEqual(obj1, obj2)) {
        resolve()
      } else {
        reject("Data sama, Tidak ada perubahan yang tersimpan")
      }
    })
  }

  const onFinish = (values) => {
    const checkData = productBundlings.every(
      (item) => item.product_id && item.sku && item.uom
    )

    const checkDataUpdate = productBundlings.every((item) =>
      productVariantOriginals.includes(item.product_id)
    )

    // if (product_variant_id) {
    //   if (!checkDataUpdate) {
    //     setLoadingSubmit(false)
    //     return toast.error("Anda tidak boleh mengubah data ini", {
    //       position: toast.POSITION.TOP_RIGHT,
    //     })
    //   }
    // }

    if (!checkData) {
      setLoadingSubmit(false)
      return toast.error("Lengkapi Form Produk anda", {
        position: toast.POSITION.TOP_RIGHT,
      })
    }

    let updatedTmpPrices = tmpPrices.map((obj) => {
      let newObj = { ...obj } // Create a shallow copy of the object
      delete newObj["type"] // Delete the key
      return newObj
    })

    compareObjects(
      { ...detail, ...updatedTmpPrices },
      { ...form.getFieldValue(), ...pricesData }
    )
      .then(() => {
        console.log("Modified value found")

        const handleOK = () => {
          setLoadingSubmit(true)
          let formData = new FormData()
          if (fileList) {
            formData.append("image", fileList)
          }
          const bundling = productBundlings[0]
          formData.append("name", values.name)
          formData.append("description", values.description)
          formData.append(
            "sales_channel",
            JSON.stringify(values.sales_channels)
          )
          if (isBundling) {
            formData.append("items", JSON.stringify(productBundlings))
          }

          fileListMultiple.forEach((file) => {
            if (file.originFileObj) {
              formData.append("images[]", file.originFileObj)
            }
          })

          formData.append("weight", values.weight)
          formData.append("product_id", bundling.product_id)
          formData.append("sku", values.sku)
          formData.append("package_id", values.package_id)
          formData.append("variant_id", values.variant_id)
          formData.append("sku", isBundling > 0 ? values.sku : bundling.sku)
          formData.append("sku_variant", values.sku_variant)
          formData.append("sku_marketplace", values.sku_marketplace)
          formData.append("is_bundling", values.is_bundling || 0)
          formData.append("qty_bundling", bundling.qty_variant)
          formData.append("status", values.status)
          formData.append("account_id", getItem("account_id"))
          formData.append("prices", JSON.stringify(pricesData))

          const url = product_variant_id
            ? `/api/product-management/product-variant/save/${product_variant_id}`
            : "/api/product-management/product-variant/save"

          axios
            .post(url, formData)
            .then((res) => {
              toast.success(res.data.message, {
                position: toast.POSITION.TOP_RIGHT,
              })
              setLoadingSubmit(false)
              return navigate("/product-management/product-variant")
            })
            .catch((err) => {
              const { message } = err.response.data
              setLoadingSubmit(false)
              toast.error(message, {
                position: toast.POSITION.TOP_RIGHT,
              })
            })
        }

        if (product_variant_id) {
          // Perform desired function here
          return Modal.confirm({
            title: "Confirm",
            content:
              "Menyimpan perubahan akan memberikan dampak kesemua data transaksi terkait produk variant ini, anda yakin ingin menyimpan perubahan?",
            cancelText: "Batal",
            okText: "Konfirmasi",
            okButtonProps: {
              style: { width: loadingSubmit ? "120px" : "100px" },
              loading: loadingSubmit,
            },
            cancelButtonProps: { style: { width: "100px" } },
            onOk: handleOK,
          })
        } else {
          return handleOK()
        }
      })
      .catch((error) => {
        console.error(error)
        toast.success(error, {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }

  const uploadButton = (
    <div>
      {imageLoading ? <LoadingOutlined /> : <PlusOutlined />}
      <div
        style={{
          marginTop: 8,
        }}
      >
        Upload
      </div>
    </div>
  )

  // if (loading) {
  //   return (
  //     <Layout
  //       title={
  //         product_variant_id ? "Produk Varian Detail" : "Add Produk Varian"
  //       }
  //       href="/product-management/product-variant"
  //     >
  //       <LoadingFallback />
  //     </Layout>
  //   )
  // }
  console.log(fileListMultiple, "image_url")
  const handleChangeMultiple = ({ fileList: newFileList }) => {
    // setFileListMultiple(newFileList)
    const validFileList = newFileList.filter((file) => {
      const size = file.size / 1024
      if (size > 200) {
        message.error(`${file.name} Maksimum ukuran file adalah 200 KB`)
        return false
      }
      return true
    })

    setFileListMultiple(validFileList)
  }
  const handleRemoveMultiple = ({ uid }) => {
    axios
      .post(`/api/product-management/product/images/delete/${uid}`, {
        uid,
        _method: "DELETE",
      })
      .then((res) => {
        toast.success("Product Image berhasil di hapus")
        loadDetailProduct()
      })
      .catch((err) => {
        toast.error("Product Image gagal di hapus")
      })
  }

  return (
    <Layout
      // title={product_variant_id ? "Produk Varian Detail" : "Add Produk Varian"}
      title="Form Produk Varian"
      href="/product-management/product-variant"
      // rightContent={rightContent}
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
        //   onFinishFailed={onFinishFailed}
        autoComplete="off"
      >
        <Card title="Data Produk Varian">
          <div className="grid lg:grid-cols-2 gap-x-4">
            <Form.Item
              label="Nama Produk Varian"
              name="name"
              rules={[
                {
                  required: true,
                  message: "Silakan masukkan Nama Produk Varian!",
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
                <Input placeholder="Ketik Nama Produk" />
              )}
            </Form.Item>

            <Form.Item
              label="Bundling"
              name="is_bundling"
              rules={[
                {
                  required: true,
                  message: "Silakan pilih Bundling!",
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
                <Select
                  allowClear
                  className="w-full"
                  placeholder="Pilih Bundling"
                  onChange={(e) => setIsBundling(e)}
                  // disabled={!isBundling}
                >
                  <Select.Option value={1}>Ya</Select.Option>
                  <Select.Option value={0}>Tidak</Select.Option>
                </Select>
              )}
            </Form.Item>

            <Form.Item
              label="Sku Varian"
              name="sku_variant"
              rules={[
                {
                  required: true,
                  message: "Silakan masukkan Sku Varian!",
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
                <Input placeholder="Ketik Sku Varian" />
              )}
            </Form.Item>
            <Form.Item label="Sku Marketplace" name="sku_marketplace">
              {loading ? (
                <Skeleton.Input
                  active
                  size={"default"}
                  block={false}
                  style={{ width: 500 }}
                />
              ) : (
                <Input placeholder="Ketik Sku Marketplace" />
              )}
            </Form.Item>

            {isBundling > 0 && (
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
                  <Skeleton.Input active size="default" block />
                ) : (
                  <Select
                    allowClear
                    className="w-full"
                    placeholder="Pilih SKU"
                    showSearch
                    filterOption={(input, option) => {
                      return (option?.children ?? "")
                        .toLowerCase()
                        .includes(input.toLowerCase())
                    }}
                    onChange={(e) => {
                      const packageSelected = dataSku.find(
                        (item) => item.sku === e
                      )
                      form.setFieldsValue({
                        package_id: packageSelected?.package_id,
                      })
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
            )}
          </div>

          <div className="grid lg:grid-cols-2 gap-x-4">
            <Form.Item
              label="Varian"
              name="variant_id"
              rules={[
                {
                  required: true,
                  message: "Silakan pilih Varian!",
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
                <Select
                  allowClear
                  className="w-full"
                  placeholder="Pilih Varian"
                >
                  {variants.map((item) => (
                    <Select.Option key={item.id} value={item.id}>
                      {item.name}
                    </Select.Option>
                  ))}
                </Select>
              )}
            </Form.Item>

            <Form.Item
              label="Kemasan"
              name="package_id"
              rules={[
                {
                  required: true,
                  message: "Silakan pilih Kemasan!",
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
                <Select
                  allowClear
                  className="w-full"
                  placeholder="Pilih Kemasan"
                >
                  {packages.map((item) => (
                    <Select.Option key={item.id} value={item.id}>
                      {item.name}
                    </Select.Option>
                  ))}
                </Select>
              )}
            </Form.Item>
          </div>
          {/* <Form.Item
                label="Qty Bundling"
                name="qty_bundling"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Qty Bundling!",
                  },
                ]}
              >
                <Input placeholder="Ketik Qty Bundling" type="number" />
              </Form.Item> */}
          {/* <Form.Item
                label="Pilih Produk"
                name="product_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Pilih Produk!",
                  },
                ]}
              >
                <ProductModal
                  handleSelect={(e) => {
                    setSelectedProduct(e)
                    console.log(e, "setSelectedProduct(e)")
                  }}
                  selectedProduct={selectedProduct}
                  form={form}
                />
              </Form.Item> */}

          <div className="grid lg:grid-cols-2 gap-x-4">
            <Form.Item
              label="Berat Produk (gram)"
              name="weight"
              rules={[
                {
                  required: true,
                  message: "Silakan masukkan Berat Produk (gram)!",
                },
                {
                  pattern: /^[0-9]*[.]?[0-9]+$/,
                  message: "Berat Produk harus berupa angka atau desimal",
                },
                {
                  validator: (_, value) =>
                    value && value < 1
                      ? Promise.reject(
                          new Error("Berat Produk tidak boleh kurang dari 1")
                        )
                      : Promise.resolve(),
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
                <Input placeholder="Ketik Berat Produk (gram)" type="number" />
              )}
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
              {loading ? (
                <Skeleton.Input
                  active
                  size={"default"}
                  block={false}
                  style={{ width: 500 }}
                />
              ) : (
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
                  <Select.Option value={"lms"}>LMS</Select.Option>
                </Select>
              )}
            </Form.Item>
          </div>

          <div className="w-full">
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
              {loading ? (
                <Skeleton.Input
                  active
                  size={"default"}
                  block={false}
                  style={{ width: 500 }}
                />
              ) : (
                <Select
                  allowClear
                  className="w-full mb-2"
                  placeholder="Pilih Status"
                >
                  <Select.Option key={"1"} value={"1"}>
                    Active
                  </Select.Option>
                  <Select.Option key={"0"} value={"0"}>
                    Non Active
                  </Select.Option>
                </Select>
              )}
            </Form.Item>
          </div>

          <div className="row w-full">
            <div className="col-md-12">
              <Form.Item
                label="Sampul Produk"
                name="image"
                rules={[
                  {
                    required: product_variant_id ? false : true,
                    message: "Silakan pilih Sampul Produk!",
                  },
                ]}
              >
                {loading ? (
                  <Skeleton.Input
                    active
                    size={"default"}
                    block={false}
                    style={{ width: 160, height: 160 }}
                  />
                ) : (
                  <Upload
                    name="image"
                    listType="picture-card"
                    className="avatar-uploader"
                    showUploadList={false}
                    multiple={true}
                    beforeUpload={() => false}
                    onChange={handleChange}
                    accept="image/*" // Accepts all image types
                  >
                    {imageUrl ? (
                      imageLoading ? (
                        <LoadingOutlined />
                      ) : (
                        <img
                          src={imageUrl}
                          alt="avatar"
                          className="max-h-[100px] h-28 w-28 aspect-square"
                        />
                      )
                    ) : (
                      uploadButton
                    )}
                  </Upload>
                )}
              </Form.Item>
            </div>
            <div className="col-md-12">
              <Form.Item
                label={
                  <span>
                    <span style={{ color: "red", fontSize: "12px" }}> * </span>
                    Gambar Produk
                  </span>
                }
                name="images"
                rules={[
                  {
                    validator: (_, value) => {
                      if (fileListMultiple.length > 0) {
                        return Promise.resolve() // Validation passes
                      }
                      return Promise.reject(
                        new Error("Silakan pilih Gambar Produk!")
                      ) // Validation fails if no images
                    },
                  },
                ]}
              >
                <Upload
                  name="images"
                  className="avatar-uploader"
                  multiple={true}
                  beforeUpload={() => false}
                  listType="picture-card"
                  fileList={fileListMultiple}
                  onChange={handleChangeMultiple}
                  onRemove={handleRemoveMultiple}
                  accept="image/*" // Accepts all image types
                >
                  {fileListMultiple.length >= 8 ? null : uploadButton}
                </Upload>
              </Form.Item>
            </div>
          </div>
          <div className="col-md-12">
            <Form.Item
              label="Deskripsi"
              name="description"
              rules={[
                {
                  required: true,
                  message: "Silakan masukkan Deskripsi!",
                },
              ]}
            >
              <RichtextEditor
                value={form.getFieldValue("description")}
                form={form}
                name={"description"}
              />
            </Form.Item>
          </div>
        </Card>

        {/* bundling */}
        <Card title="Referensi Produk" className="mt-4">
          <div className="card-body">
            <Table
              scroll={{ x: "max-content" }}
              tableLayout={"auto"}
              dataSource={productBundlings}
              columns={[
                {
                  title: "Produk Master",
                  key: "product_variant_id",
                  dataIndex: "product_variant_id",
                  width: 400,
                  render: (text, record, index) => {
                    const product_variant_ids = productBundlings.map(
                      (item) => item.product_id
                    )

                    if (loading || loadingProductMaster) {
                      return <Skeleton.Input active size={"default"} block />
                    } else {
                      return (
                        <Select
                          className="w-full"
                          onChange={(e) => {
                            const product = productMasters.find(
                              (item) => item.id == e
                            )

                            let data = [...productBundlings]
                            data[index].product_id = product.id
                            data[index].product_variant_id = detail?.id
                            data[index].stock_master = parseInt(
                              product?.stock_by_warehouse ||
                                product?.final_stock ||
                                0
                            )
                            data[index].qty_variant = product?.qty_bundling || 1
                            data[index].uom_master = product.u_of_m
                            data[index].sku = product.sku
                            setProductBundlings(data)
                            setDetail({
                              ...detail,
                              product_id: e,
                            })
                          }}
                          placeholder={"Cari Produk"}
                          value={record?.product_id}
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
                              disabled={inArray(item.id, product_variant_ids)}
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
                  title: "Produk SKU",
                  key: "sku",
                  dataIndex: "sku",
                  width: 200,
                  render: (text, record, index) => {
                    return (
                      <Select
                        className="w-full"
                        onChange={(e) => {
                          let data = [...productBundlings]
                          data[index].sku = e

                          setProductBundlings(data)
                        }}
                        value={record?.sku}
                        disabled
                      >
                        {dataSku.map((item) => (
                          <Select.Option key={item?.sku} value={item?.sku}>
                            {item?.name}
                          </Select.Option>
                        ))}
                      </Select>
                    )
                  },
                },
                {
                  title: "Total Qty Master",
                  key: "stock_master",
                  dataIndex: "stock_master",
                  render: (text) => text,
                },
                {
                  title: "UOM Master",
                  key: "uom_master",
                  dataIndex: "uom_master",
                  render: (text) => text,
                },
                {
                  title: "QTY Varian",
                  key: "qty_variant",
                  dataIndex: "qty_variant",
                  render: (text, record, index) => (
                    <Input
                      value={text}
                      onChange={(e) => {
                        const { value } = e.target
                        let data = [...productBundlings]
                        if (value == "") {
                          data[index].qty_variant = 0
                          return setProductBundlings(data)
                        }

                        if (value > record.stock_of_market) {
                          data[index].qty_variant = record.stock_of_market
                          return setProductBundlings(data)
                        }

                        data[index].qty_variant = parseInt(value)
                        setProductBundlings(data)
                      }}
                      type={"number"}
                    />
                  ),
                },
                {
                  title: "UOM Varian",
                  key: "uom",
                  dataIndex: "uom",
                  render: (text, record, index) => {
                    return (
                      <Select
                        // disabled={!isBundling}
                        className="w-full"
                        onChange={(e) => {
                          const product = packages.find((item) => item.id == e)

                          let data = [...productBundlings]
                          data[index].uom = product.id
                          data[index].package_id = product.id
                          setProductBundlings(data)
                          setDetail({
                            ...detail,
                            package_id: e,
                          })
                        }}
                        value={record?.package_id}
                      >
                        {packages &&
                          packages.map((item) => (
                            <Select.Option key={item?.id} value={item?.id} dis>
                              {item?.name}
                            </Select.Option>
                          ))}
                      </Select>
                    )
                  },
                },
                {
                  title: "Aksi",
                  key: "action",
                  dataIndex: "action",
                  fixed: true,
                  align: "center",
                  render: (text, record, index) => {
                    let data = [...productBundlings]
                    if (data.length > 1) {
                      return (
                        <DeleteOutlined
                          className={
                            data.length > 0
                              ? "cursor-pointer"
                              : "cursor-not-alowed"
                          }
                          style={{ color: data.length > 0 ? "red" : "#000" }}
                          onClick={() => {
                            if (data.length > 0) {
                              let data = [...productBundlings]
                              data.splice(index, 1)
                              setProductBundlings(data)
                              if (record.id > 0) {
                                deleteProductVariantBundling(record.id, () =>
                                  loadDetailProduct()
                                )
                              }
                            }
                          }}
                        />
                      )
                    }

                    return (
                      <DeleteFilled
                        className={
                          index > 0 ? "cursor-pointer" : "cursor-not-alowed"
                        }
                        style={{ color: index > 0 ? "red" : "gray" }}
                        onClick={() => {
                          if (index > 0) {
                            let data = [...productBundlings]

                            data.splice(index, 1)
                            setProductBundlings(data)
                            if (record.id > 0) {
                              deleteProductVariantBundling(record.id, () =>
                                loadDetailProduct()
                              )
                            }
                          }
                        }}
                      />
                    )
                  },
                },
              ]}
              pagination={false}
              rowKey="id"
            />
            {isBundling > 0 && (
              <div
                onClick={() => {
                  let data = [...productBundlings]
                  const product = productBundlings.pop()
                  data.push({
                    key: product.key + 1,
                    id: 0,
                    product_id: null,
                    product_variant_id: null,
                    stock_master: 0,
                    package_id: null,
                    uom_master: null,
                    qty_variant: 0,
                    uom: null,
                    sku: null,
                  })

                  setProductBundlings(data)
                }}
                className="
              w-full mt-4 cursor-pointer
              text-blue-600 hover:text-blue-800
              bg-blue-500/20 border-2 border-blue-700/70 hover:border-blue-800 border-dashed  focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 inline-flex items-center justify-center"
              >
                <PlusOutlined style={{ marginRight: 10 }} />
                <strong>Add More</strong>
              </div>
            )}
          </div>
        </Card>

        <Card title="Harga Produk" className="mt-4">
          <div className="card-body">
            <Table
              scroll={{ x: "max-content" }}
              tableLayout={"auto"}
              dataSource={pricesData}
              columns={[
                {
                  title: "Nama Level",
                  key: "name",
                  dataIndex: "name",
                },
                {
                  title: "Harga Dasar",
                  key: "basic_price",
                  dataIndex: "basic_price",
                  render: (text, record, index) => (
                    <Input
                      value={text}
                      onChange={(e) => {
                        let data = [...pricesData]
                        data[index].basic_price = parseInt(e.target.value)
                        setPricesData(data)
                      }}
                      type={"number"}
                    />
                  ),
                },
                {
                  title: "Harga Akhir",
                  key: "final_price",
                  dataIndex: "final_price",
                  render: (text, record, index) => (
                    <Input
                      value={text}
                      onChange={(e) => {
                        let data = [...pricesData]
                        data[index].final_price = parseInt(e.target.value)
                        setPricesData(data)
                      }}
                      type={"number"}
                    />
                  ),
                },
              ]}
              pagination={false}
              rowKey="id"
            />
          </div>
        </Card>

        {/* <div className="float-right mt-6">
          <button className="text-white bg-blueColor hover:bg-blueColor/90 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2">
            {loadingSubmit ? <LoadingOutlined /> : <CheckOutlined />}
            <span className="ml-2">Simpan</span>
          </button>
        </div> */}
      </Form>
    </Layout>
  )
}

export default ProductVariantForm
