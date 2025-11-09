import {
  Button,
  Card,
  DatePicker,
  Form,
  Input,
  message,
  Select,
  Skeleton,
} from "antd"
import TextArea from "antd/lib/input/TextArea"
import axios from "axios"
import moment from "moment"
import React, { useEffect, useState } from "react"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../components/layout"
import { getItem, useDebounce } from "../../helpers"
import ProductList from "./Components/ProductList"
import { productListReturnColumns } from "./config"
import OrderNumberModal from "../OrderInvoice/Components/OrderNumberModal"
import { LoadingOutlined } from "@ant-design/icons"

const InventoryProductReturnForm = () => {
  const debounce = useDebounce()
  const [form] = Form.useForm()
  const { inventory_id } = useParams()
  // hooks
  const navigate = useNavigate()
  // state
  const intialProduct = {
    key: 0,
    product_id: null,
    u_of_m: null,
    sku: null,
    case_return: null,
    qty: 1,
    qty_alocation: 1,
  }
  const [loading, setLoading] = useState(false)
  const [loadingSubmit, setLoadingSubmit] = useState(false)
  const [productData, setProductData] = useState([intialProduct])
  const [caseLists, setCaseLists] = useState([])
  const [products, setProducts] = useState([])
  const [warehouses, setWarehouses] = useState([])
  const [companies, setCompanies] = useState([])
  const [vendors, setVendors] = useState([])
  const [disabled, setDisabled] = useState(false)
  const [typeReturn, setTypeReturn] = useState(null)
  const [status, setStatus] = useState("draft")
  const [selectedCase, setSelectedCase] = useState(null)
  const [caseTitle, setCaseTitle] = useState(null)
  const [newCaselists, setNewCaselists] = useState([])
  const [caseProducts, setCaseProducts] = useState([])

  // api
  const loadProducts = (type = "variant") => {
    setLoading(true)
    axios
      .get(`/api/master/${type === "variant" ? "products" : "product-lists"}`)
      .then((res) => {
        setProducts(res.data.data)
        setLoading(false)
      })
  }
  const loadProductCase = (type) => {
    setLoading(true)
    const title = type === "po" ? "purchase-order" : `sales-order/${type}`
    axios.get(`/api/general/${title}`).then((res) => {
      const productData = res.data.data
      form.setFieldsValue({ case_title: null })
      // form.setFieldsValue({ case_type: null })
      setCaseProducts(productData)
      setLoading(false)
    })
  }
  const loadProductCaseItem = (uid_lead, type = "so") => {
    setLoading(true)
    const title = type === "so" ? "sales-order" : "purchase-order"
    axios.get(`/api/general/${title}/items/${uid_lead}`).then((res) => {
      const productData = res.data.data
      if (productData.length > 0) {
        if (type === "po") {
          const newProductData = productData
            .filter((item) => item.is_master > 0 && item.is_allocated === "0")
            .map((item, index) => {
              const totalQty = item.qty - item.qty_diterima
              if (totalQty > 0) {
                return {
                  key: index,
                  product_id: item.product_id,
                  u_of_m: item.u_of_m,
                  sku: item.product?.sku || item?.sku,
                  case_return: selectedCase,
                  qty: item.qty - item.qty_diterima,
                  qty_alocation: 1,
                }
              }
            })
            .filter((row) => row)
          setProductData(newProductData)
        } else {
          const newProductData = productData
            .filter((row) => row.is_invoice == 1)
            .map((item, index) => {
              return {
                key: index,
                product_id: item?.product_need.product_id,
                u_of_m: item?.product_need.u_of_m,
                sku: item.product_need?.sku || item?.sku,
                case_return: selectedCase,
                qty: item.qty_delivered,
                qty_alocation: item.qty_delivered,
              }
            })
          setProductData(newProductData)
        }

        setLoading(false)
      }
    })
  }

  const loadWarehouse = () => {
    setLoading(true)
    axios.get("/api/master/warehouse").then((res) => {
      setWarehouses(res.data.data)
      setLoading(false)
    })
  }

  const loadVendor = () => {
    setLoading(true)
    axios.get("/api/master/vendors").then((res) => {
      setVendors(res.data.data)
      setLoading(false)
    })
  }

  const loadCompanies = () => {
    setLoading(true)
    axios.get("/api/master/company-account").then((res) => {
      setCompanies(res.data.data)
      setLoading(false)
    })
  }

  const loadInventoryDetail = () => {
    setLoading(true)
    axios
      .get(`/api/inventory/product/return/detail/${inventory_id}`)
      .then((res) => {
        const { data } = res.data
        setDisabled(data?.status !== "draft")
        setStatus(data?.status)
        form.setFieldsValue({
          ...data,
          received_date: moment(data.received_date || new Date(), "YYYY-MM-DD"),
          expired_date: moment(data.expired_date || new Date(), "YYYY-MM-DD"),
        })
        const newProducts = data.items.map((item, index) => {
          return {
            key: index,
            product_id: item.product_id,
            price: item.price,
            qty: item.stock_off_market,
            sub_total: item.subtotal,
            sku: item.sku,
            u_of_m: item.u_of_m,
          }
        })
        setProductData(newProducts)
        setLoading(false)
      })
  }

  const getCreatedInfo = () => {
    setLoading(true)
    axios.get("/api/inventory/info/created").then((res) => {
      form.setFieldsValue(res.data)
      setLoading(false)
    })
  }

  const loadCaseLists = () => {
    setLoading(true)
    axios.get("/api/master/list-case").then((res) => {
      const { data } = res.data
      setCaseLists(data)
      setLoading(false)
    })
  }

  // cycle
  useEffect(() => {
    loadWarehouse()
    loadVendor()
    loadCompanies()
    loadCaseLists()
    if (inventory_id) {
      loadInventoryDetail()
    } else {
      getCreatedInfo()
    }

    form.setFieldsValue({
      company_account_id: parseInt(getItem("account_id")),
    })
  }, [])

  const handleChangeProductItem = ({
    dataIndex,
    value,
    key,
    product_id,
    type,
  }) => {
    console.log(dataIndex, value, key, product_id, type)
    const datas = [...productData]
    console.log(datas[key], key, "datas[key]")
    datas[key][dataIndex] = value
    if (type === "change-product") {
      const product = products.find((item) => item.id === product_id)
      datas[key]["qty"] = product?.stock_off_market
      datas[key]["u_of_m"] = product?.package_name
      datas[key]["sku"] = product?.sku
    }

    setProductData(datas.map((item, index) => ({ ...item, key: index })))
  }

  const handleClickProductItem = ({ key, type }) => {
    const datas = [...productData]
    if (type === "add") {
      const lastData = datas[datas.length - 1]
      datas.push({
        key: lastData.key + 1,
        product_id: null,
        u_of_m: null,
        sku: null,
        case_return: null,
        qty: 1,
        qty_alocation: 1,
      })
      return setProductData(
        datas.map((item, index) => ({
          ...item,
          key: index,
        }))
      )
    }

    if (type === "add-qty") {
      const item = datas[key]
      const qty = parseInt(item.qty_alocation) + 1
      datas[key]["qty_alocation"] = qty
      return setProductData(datas)
    }

    if (type === "remove-qty") {
      const item = datas[key]
      if (item.qty_alocation > 1) {
        const qty = item.qty_alocation - 1
        datas[key]["qty_alocation"] = qty
        return setProductData(datas)
      }
      return setProductData(datas)
    }

    const newData = datas.filter((item) => item.key !== key)
    return setProductData(
      newData.map((item, index) => ({
        ...item,
        key: index,
      }))
    )
  }
  console.log(productData, "productData")
  const onFinish = (values) => {
    const productItem = productData.every((item) => item.product_id)
    if (!productItem) {
      return message.error("Please select product")
    }

    const productQty = productData.every((item) => item.qty_alocation > 0)
    if (!productQty) {
      return message.error("Silakan masukkan product qty")
    }
    const data = {
      ...values,
      items: productData,
      account_id: getItem("account_id"),
    }
    let url = "/api/inventory/product/return/save"
    if (inventory_id) {
      data.inventory_id = inventory_id
      url = `/api/inventory/product/return/update/${inventory_id}`
    }
    setLoadingSubmit(true)
    axios
      .post(url, data)
      .then((res) => {
        setLoadingSubmit(false)
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        return navigate(-1)
      })
      .catch((err) => {
        setLoadingSubmit(false)
        const { message } = err.response.data
        toast.error(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }
  const invoiceNumber =
    typeReturn == "so"
      ? {
          title: "Invoice Number",
          dataIndex: "invoice_number",
          key: "invoice_number",
        }
      : {}
  return (
    <Layout onClick={() => navigate(-1)} title="Form Data Retur Produk">
      <div>
        <div className="flex justify-end items-center">
          <strong>Status :</strong>
          <Button
            type="primary"
            size={"middle"}
            style={{
              marginLeft: 10,
              backgroundColor: "#E3A008",
              borderColor: "#E3A008",
            }}
          >
            {status.toUpperCase()}
          </Button>
        </div>
        <Form
          form={form}
          name="basic"
          layout="vertical"
          onFinish={onFinish}
          //   onFinishFailed={onFinishFailed}
          autoComplete="off"
        >
          <Card className="mt-4">
            <div className="row">
              <div className="col-md-6">
                <Form.Item label="Created by" name="created_by_name">
                  {loading ? (
                    <Skeleton.Input
                      active
                      size={"default"}
                      block={false}
                      style={{ width: 500 }}
                    />
                  ) : (
                    <Input disabled placeholder=" Created by" readOnly={true} />
                  )}
                </Form.Item>
              </div>
              <div className="col-md-6">
                <Form.Item label="Created On" name="created_on">
                  {loading ? (
                    <Skeleton.Input
                      active
                      size={"default"}
                      block={false}
                      style={{ width: 500 }}
                    />
                  ) : (
                    <Input disabled placeholder=" Created On" readOnly={true} />
                  )}
                </Form.Item>
              </div>
              <div className="col-md-6">
                <Form.Item label="Nomor SR" name="nomor_sr">
                  {loading ? (
                    <Skeleton.Input
                      active
                      size={"default"}
                      block={false}
                      style={{ width: 500 }}
                    />
                  ) : (
                    <Input placeholder="Input Nomor SR" disabled />
                  )}
                </Form.Item>

                <Form.Item
                  label="Sales Channel"
                  name="transaction_channel"
                  rules={[
                    {
                      required: true,
                      message: "Silakan masukkan Sales Channel!",
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
                      showSearch
                      filterOption={(input, option) => {
                        return (option?.children ?? "")
                          .toLowerCase()
                          .includes(input.toLowerCase())
                      }}
                      placeholder="Pilih Sales Channel"
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
                    </Select>
                  )}
                </Form.Item>

                <Form.Item
                  label="Warehouse Destination"
                  name="warehouse_id"
                  rules={[
                    {
                      required: true,
                      message: "Silakan masukkan Warehouse!",
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
                      showSearch
                      filterOption={(input, option) => {
                        return (option?.children ?? "")
                          .toLowerCase()
                          .includes(input.toLowerCase())
                      }}
                      placeholder="Pilih Warehouse"
                    >
                      {warehouses.map((warehouse) => (
                        <Select.Option value={warehouse.id} key={warehouse.id}>
                          {warehouse.name}
                        </Select.Option>
                      ))}
                    </Select>
                  )}
                </Form.Item>
              </div>
              <div className="col-md-6">
                {/* <Form.Item
                label="Barcode"
                name="barcode"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Barcode!",
                  },
                ]}
              >
                <Input placeholder="Input Barcode" />
              </Form.Item> */}
                <Form.Item label="Company" name="company_account_id">
                  {loading ? (
                    <Skeleton.Input
                      active
                      size={"default"}
                      block={false}
                      style={{ width: 500 }}
                    />
                  ) : (
                    <Select placeholder="Pilih Company" disabled>
                      {companies.map((company) => (
                        <Select.Option value={company.id} key={company.id}>
                          {company.account_name}
                        </Select.Option>
                      ))}
                    </Select>
                  )}
                </Form.Item>
                <Form.Item
                  label="Request Date"
                  name="received_date"
                  rules={[
                    {
                      required: true,
                      message: "Silakan masukkan Received Date!",
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
                    <DatePicker className="w-full" />
                  )}
                </Form.Item>

                {/* <Form.Item
                label="Expired Date"
                name="expired_date"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Expired Date!",
                  },
                ]}
              >
                <DatePicker className="w-full" />
              </Form.Item> */}
              </div>

              <div className="col-md-12">
                <Form.Item label="Notes" name="note">
                  {loading ? (
                    <Skeleton.Input
                      active
                      size={"default"}
                      block={false}
                      style={{ width: 500 }}
                    />
                  ) : (
                    <TextArea placeholder=" Notes" />
                  )}
                </Form.Item>
              </div>
              <div className="col-md-6">
                <Form.Item
                  label="Return Type"
                  name="type_return"
                  rules={[
                    {
                      required: true,
                      message: "Silakan masukkan Return Type!",
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
                      placeholder="Pilih Return Type"
                      className="w-full"
                      onChange={(e) => {
                        setTypeReturn(e)
                        setProductData([intialProduct])
                        if (e != "return") {
                          loadProductCase("po")
                          loadProducts(e === "po" ? "master" : "variant")
                        } else {
                          loadProducts("variant")
                        }
                      }}
                    >
                      <Select.Option value={"so"}>Sales Order</Select.Option>
                      <Select.Option value={"po"}>Purchase Order</Select.Option>
                      <Select.Option value={"return"}>
                        Return Only
                      </Select.Option>
                    </Select>
                  )}
                </Form.Item>
              </div>
              {typeReturn === "po" && (
                <div className="col-md-6">
                  <Form.Item
                    label="Return to Vendor"
                    name="vendor"
                    rules={[
                      {
                        required: false,
                        message: "Please Return vendor!",
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
                        showSearch
                        filterOption={(input, option) => {
                          return (option?.children ?? "")
                            .toLowerCase()
                            .includes(input.toLowerCase())
                        }}
                        placeholder="Pilih Return vendor"
                      >
                        {vendors.map((vendor) => (
                          <Select.Option value={vendor.code} key={vendor.id}>
                            {vendor.name}
                          </Select.Option>
                        ))}
                      </Select>
                    )}
                  </Form.Item>
                </div>
              )}
              {typeReturn === "so" && (
                <div className="col-md-6">
                  <Form.Item
                    label="SI Type"
                    name="case_type"
                    rules={[
                      {
                        required: true,
                        message: "Silakan masukkan SI Type!",
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
                        placeholder="Pilih SI Type"
                        className="w-full"
                        onChange={(e) => {
                          setSelectedCase(e)
                          setNewCaselists(
                            caseLists.filter((item) => item.type === e)
                          )
                          form.setFieldsValue({ case_title: null })
                          loadProductCase(e)
                        }}
                      >
                        <Select.Option value={"order-manual"}>
                          Manual
                        </Select.Option>
                        <Select.Option value={"order-lead"}>Lead</Select.Option>
                        <Select.Option value={"freebies"}>
                          Freebies
                        </Select.Option>
                        <Select.Option value={"konsinyasi"}>
                          Konsinyasi
                        </Select.Option>
                      </Select>
                    )}
                  </Form.Item>
                </div>
              )}

              {typeReturn === "so" && (
                <div className="col-md-6">
                  <Form.Item
                    label="SO Number"
                    name="case_title"
                    rules={[
                      {
                        required: false,
                        message: "Silakan masukkan SI Number!",
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
                      <OrderNumberModal
                        placeholder="Pilih SI Number"
                        handleOk={(value) => {
                          form.setFieldValue("case_title", value?.value)
                          const caseSelected = caseProducts.find(
                            (item) => item.uid_lead === value?.uid_lead
                          )
                          loadProductCaseItem(value?.uid_lead)
                          setCaseTitle(caseSelected?.invoice_number)
                        }}
                        type={selectedCase}
                        isReturn={true}
                      />
                    )}
                  </Form.Item>
                </div>
              )}

              {typeReturn === "po" && (
                <div className="col-md-6">
                  <Form.Item
                    label="PO Number"
                    name="case_title"
                    rules={[
                      {
                        required: false,
                        message: "Silakan masukkan PO Number!",
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
                        placeholder="Pilih PO Number"
                        className="w-full"
                        // disabled={!selectedCase}
                        onChange={(e) => {
                          const caseSelected = caseProducts.find(
                            (item) => item.id === e
                          )
                          loadProductCaseItem(e, "po")
                          setCaseTitle(caseSelected?.po_number)
                        }}
                      >
                        {caseProducts.map((caseList) => (
                          <Select.Option value={caseList.id} key={caseList.id}>
                            {caseList.po_number}
                          </Select.Option>
                        ))}
                      </Select>
                    )}
                  </Form.Item>
                </div>
              )}
            </div>
          </Card>
        </Form>
      </div>

      <div className="card mt-8">
        <div className="card-header">
          <div className="header-titl">
            <strong>Informasi Product Return Product</strong>
          </div>
        </div>

        <div className="card-body">
          <ProductList
            loading={loading}
            data={productData}
            products={products}
            cases={caseLists}
            disabled={{
              product_id: false,
              qty_alocation: false,
            }}
            columns={[...productListReturnColumns]}
            handleChange={handleChangeProductItem}
            handleClick={handleClickProductItem}
            type={typeReturn}
          />
        </div>
      </div>

      {/* <div className="card p-6 ">
        <table width={"20%"} className="table-auto">
          <tr>
            <td>Total Qty</td>
            <td>:</td>
            <td>{productData.reduce((prev, curr) => prev + curr.qty, 0)}</td>
          </tr>
          <tr>
            <td>Sub Total</td>
            <td>:</td>
            <td>{subTotal}</td>
          </tr>
          <tr>
            <td>
              <strong>Total Price</strong>
            </td>
            <td>:</td>
            <td>
              <strong>{totalPrice}</strong>
            </td>
          </tr>
        </table>
      </div> */}

      <div className="card p-6 ">
        <div className="flex justify-end">
          {/* <Button color={"success"} onClick={() => form.submit()}>
            Simpan
          </Button> */}
          <button
            disabled={loadingSubmit}
            onClick={() => {
              debounce(form.submit)
            }}
            className={`text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center`}
          >
            {loadingSubmit && <LoadingOutlined className="mr-2" />}
            Simpan
          </button>
        </div>
      </div>
    </Layout>
  )
}

export default InventoryProductReturnForm
