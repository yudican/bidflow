import { CheckOutlined, LoadingOutlined, PlusOutlined } from "@ant-design/icons"
import {
  Button,
  Card,
  Form,
  Input,
  Modal,
  Popconfirm,
  Select,
  Skeleton,
  Upload,
  message,
  Table,
} from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import "react-draft-wysiwyg/dist/react-draft-wysiwyg.css"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import RichtextEditor from "../../../components/RichtextEditor"
import Layout from "../../../components/layout"
import { getBase64, isEqual, inArray } from "../../../helpers"
import { loadProductMaster } from "./services"
// import "../../../index.css";

const ProductMasterForm = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const { product_id } = useParams()

  const [dataBrand, setDataBrand] = useState([])
  const [dataCategories, setDataCategories] = useState([])
  const [dataSku, setDataSku] = useState([])
  const [dataProductCarton, setDataProductCarton] = useState([])
  const [dataProductCarton2, setDataProductCarton2] = useState([])
  const [imageLoading, setImageLoading] = useState(false)
  const [imageUrl, setImageUrl] = useState(false)
  const [fileList, setFileList] = useState(false)
  const [fileListMultiple, setFileListMultiple] = useState([])
  const [productBundling, setProductBundling] = useState({})
  const [productMasters, setProductMasters] = useState([])
  const [detail, setDetail] = useState(null)

  const [loading, setLoading] = useState(false)
  const [loadingBrand, setLoadingBrand] = useState(false)
  const [loadingCategories, setLoadingCategories] = useState(false)
  const [loadingSubmit, setLoadingSubmit] = useState(false)
  const [loadingProductCarton, setLoadingProductCarton] = useState(false)
  const [compareObjectProductDetail, setCompareObjectProductDetail] =
    useState(null)
  const loadDetailProduct = async () => {
    if (product_id) {
      setLoading(true)

      try {
        const [productRes, cartonData] = await Promise.all([
          axios.get(`/api/product-management/product/${product_id}`),
          loadProductCartonEdit(), // Wait for carton data to load
        ])
        const { data } = productRes.data
        console.log(data, "product data")
        console.log(cartonData, "dataProductCarton")

        const selectedCarton = cartonData.find(
          (carton) => carton.id == data.product_carton_id
        )
        console.log(selectedCarton, "selectedCarton")

        setProductBundling({
          product_carton_id: selectedCarton?.id,
          product_carton_name: selectedCarton?.product_carton_name,
          moq: selectedCarton?.package,
          sku: selectedCarton?.sku,
        })

        setImageUrl(data.image_url)

        const images = data.product_images.map((item) => ({
          uid: item.id,
          name: item.name,
          status: "done",
          url: item.image_url,
        }))
        console.log(data, "dataaa")
        setFileListMultiple(images)
        form.setFieldsValue({
          ...data,
          stock: data.final_stock,
          product_carton_id: data.product_carton_id,
        })

        setCompareObjectProductDetail({
          ...data,
          stock: data.final_stock,
          product_carton_id: selectedCarton?.id,
        })
      } catch (error) {
        console.error("Error loading product or carton data", error)
      } finally {
        setLoading(false)
      }
    }
  }

  const loadBrand = () => {
    setLoadingBrand(true)
    axios
      .get("/api/master/brand")
      .then((res) => {
        setDataBrand(res.data.data)
        setLoadingBrand(false)
      })
      .catch((err) => setLoadingBrand(false))
  }

  const loadCategories = () => {
    setLoadingCategories(true)
    axios
      .get("/api/master/categories")
      .then((res) => {
        setDataCategories(res.data.data)
        setLoadingCategories(false)
      })
      .catch((err) => setLoadingCategories(false))
  }

  const loadSku = () => {
    axios.get("/api/master/sku").then((res) => {
      const { data } = res.data
      setDataSku(data)
    })
  }

  const loadProductCartonEdit = () => {
    return axios.get("/api/master/product-carton").then((res) => {
      const { data } = res.data
      setDataProductCarton2(data)
      return data // Return the data to be used later
    })
  }

  const loadProductCarton = () => {
    setLoadingProductCarton(true)
    axios
      .get("/api/master/product-carton")
      .then((res) => {
        const { data } = res.data
        setDataProductCarton(data)
      })
      .finally(() => setLoadingProductCarton(false))
  }

  useEffect(() => {
    loadSku()
    loadBrand()
    loadCategories()
    loadProductCarton()
    product_id && loadDetailProduct()
    loadProductMaster(
      (data) => setProductMasters(data),
      setLoadingProductCarton
    )
  }, [])

  const handleChange = ({ fileList }) => {
    const list = fileList.pop()
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
    compareObjects(compareObjectProductDetail, form.getFieldValue())
      .then(() => {
        console.log("Modified value found")
        // Perform desired function here
        const handleOK = () => {
          setLoadingSubmit(true)
          let formData = new FormData()
          if (fileList) {
            formData.append("image", fileList)
          }

          fileListMultiple.forEach((file) => {
            if (file.originFileObj) {
              formData.append("images[]", file.originFileObj)
            }
          })

          formData.append("name", values.name)
          formData.append("description", values.description)
          formData.append("weight", values.weight)
          // formData.append("stock", values.stock)
          formData.append("sku", values.sku)
          formData.append("product_like", values.product_like)
          formData.append("brand_id", values.brand_id)
          formData.append("category_id", JSON.stringify(values.category_ids))
          formData.append("status", values.status)
          // formData.append("product_carton_id", values.product_carton_id)
          formData.append(
            "product_carton_id",
            productBundling.product_carton_id || ""
          )

          const url = product_id
            ? `/api/product-management/product/save/${product_id}`
            : "/api/product-management/product/save"

          axios
            .post(url, formData)
            .then((res) => {
              toast.success("Data Produk Master berhasil disimpan", {
                position: toast.POSITION.TOP_RIGHT,
              })
              setLoadingSubmit(false)
              return navigate("/product-management/product")
            })
            .catch((err) => {
              const { message } = err.response.data
              setLoadingSubmit(false)
              toast.error(message, {
                position: toast.POSITION.TOP_RIGHT,
              })
            })
        }

        if (product_id) {
          return Modal.confirm({
            title: "Confirm",
            content: product_id
              ? "Menyimpan perubahan akan memberikan dampak kesemua data transaksi terkait produk ini, anda yakin ingin menyimpan perubahan?"
              : "Simpan data produk?",
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

  return (
    <Layout
      // title={product_id ? "Detail Produk Master" : "Form Produk Master"}
      title={"Form Produk Master"}
      href="/product-management/product"
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
        <Card title="Data Produk Master">
          <div className="card-body row">
            <div className="col-md-6">
              <Form.Item
                label="Nama Produk"
                name="name"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Nama Produk!",
                  },
                ]}
              >
                {loading ? (
                  <Skeleton.Input active size={"default"} block />
                ) : (
                  <Input placeholder="Ketik Nama Produk" />
                )}
              </Form.Item>
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
                  <Skeleton.Input active size={"default"} block />
                ) : (
                  <Input
                    placeholder="Ketik Berat Produk (gram)"
                    type="number"
                    step="0.01"
                    min="0.01"
                  />
                )}
              </Form.Item>
              <Form.Item
                label="Ulasan Produk"
                name="product_like"
                rules={[
                  {
                    required: false,
                    message: "Silakan masukkan Ulasan Produk!",
                  },
                  {
                    pattern: /^[0-9]+$/,
                    message: "Ulasan Produk harus berupa angka",
                  },
                  {
                    validator: (_, value) =>
                      value && value < 1
                        ? Promise.reject(
                            new Error("Ulasan Produk tidak boleh kurang dari 1")
                          )
                        : Promise.resolve(),
                  },
                ]}
              >
                {loading ? (
                  <Skeleton.Input active size={"default"} block />
                ) : (
                  <Input placeholder="Ketik Ulasan Produk" type="number" />
                )}
              </Form.Item>
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
                  <Skeleton.Input active size={"default"} block />
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
            <div className="col-md-6">
              <Form.Item
                label="Brand"
                name="brand_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Brand!",
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
                    placeholder="Pilih Brand"
                    loading={loadingBrand}
                  >
                    {dataBrand.map((item) => (
                      <Select.Option key={item.id} value={item.id}>
                        {item.name}
                      </Select.Option>
                    ))}
                  </Select>
                )}
              </Form.Item>

              <Form.Item
                label="Kategori"
                name="category_ids"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Kategori!",
                  },
                ]}
              >
                {loading ? (
                  <Skeleton.Input active size={"default"} block />
                ) : (
                  <Select
                    mode="multiple"
                    allowClear
                    className="w-full"
                    placeholder="Pilih Kategori"
                    loading={loadingCategories}
                  >
                    {dataCategories.map((item) => (
                      <Select.Option key={item.id} value={item.id}>
                        {item.name}
                      </Select.Option>
                    ))}
                  </Select>
                )}
              </Form.Item>

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
                        {`${item.sku} - ${item.package_name}`}
                      </Select.Option>
                    ))}
                  </Select>
                )}
              </Form.Item>

              {/* <Form.Item
                label="Produk Karton"
                name="product_carton_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Produk Karton!",
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
                    placeholder="Pilih Produk Karton"
                    showSearch
                    filterOption={(input, option) => {
                      return (option?.children ?? "")
                        .toLowerCase()
                        .includes(input.toLowerCase())
                    }}
                  >
                    {dataProductCarton.map((item) => (
                      <Select.Option key={item.id} value={item.id}>
                        {`${item.product_name} - qty : ${item.qty}`}
                      </Select.Option>
                    ))}
                  </Select>
                )}
              </Form.Item> */}
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
          </div>
        </Card>

        <Card title="Referensi Produk Karton" className="mt-4">
          <div className="card-body">
            <Table
              scroll={{ x: "max-content" }}
              tableLayout={"auto"}
              dataSource={[productBundling]} // Use single item array
              columns={[
                {
                  title: "Produk Karton",
                  key: "product_carton_id",
                  dataIndex: "product_carton_id",
                  width: 400,
                  render: (text, record, index) => {
                    if (loadingProductCarton) {
                      return <Skeleton.Input active size={"default"} block />
                    } else {
                      return (
                        <Select
                          className="w-full"
                          onChange={(e) => {
                            const product = dataProductCarton.find(
                              (item) => item.id == e
                            )
                            console.log(product, "prod")
                            let data = { ...productBundling }
                            data.product_carton_id = product.id
                            data.package = product.package
                            data.sku = product.sku
                            setProductBundling(data)
                            setDetail({
                              ...detail,
                              product_id: e,
                            })
                          }}
                          placeholder={"Cari Produk"}
                          value={productBundling?.product_carton_id}
                          showSearch
                          optionFilterProp="children"
                          filterOption={(input, option) =>
                            (option?.children?.toLowerCase() ?? "").includes(
                              input.toLowerCase()
                            )
                          }
                        >
                          {dataProductCarton.map((item) => (
                            <Select.Option
                              key={item?.id}
                              value={item?.id}
                              disabled={inArray(item.id, [productBundling.id])}
                            >
                              {item?.product_name || item?.name}
                            </Select.Option>
                          ))}
                        </Select>
                      )
                    }
                  },
                },
                {
                  title: "SKU",
                  key: "sku",
                  dataIndex: "sku",
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
                  title: "MOQ",
                  key: "package",
                  dataIndex: "package",
                  render: (text) => productBundling?.moq || text,
                },
              ]}
              pagination={false}
              rowKey="id"
            />
          </div>
        </Card>

        <Card title="Gambar Produk" className="mt-4">
          <div className="card-body row">
            <div className="col-md-2">
              <Form.Item
                label="Sampul Produk"
                name="image"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Sampul Produk!",
                  },
                ]}
              >
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

export default ProductMasterForm
