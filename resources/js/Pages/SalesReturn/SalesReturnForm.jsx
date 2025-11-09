import { Card, Form, Input, Select } from "antd"
import TextArea from "antd/lib/input/TextArea"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import DebounceSelect from "../../components/atoms/DebounceSelect"
import Layout from "../../components/layout"
import LoadingFallback from "../../components/LoadingFallback"
import ModalOrderNumber from "./Components/ModalOrderNumber"
import ProductList from "./Components/ProductList"
import { searchContact, searchSales } from "./service"

const SalesReturnForm = () => {
  const navigate = useNavigate()
  const userData = JSON.parse(localStorage.getItem("user_data"))
  const role = localStorage.getItem("role")
  const [form] = Form.useForm()
  const { uid_return } = useParams()
  const defaultItems = [
    {
      product_id: null,
      price: null,
      qty: 1,
      tax_id: null,
      discount_id: null,
      total: null,
      uid_retur: uid_return,
      id: 0,
      key: 0,
    },
  ]
  const [warehouses, setWarehouses] = useState([])
  const [termOfPayments, setTermOfPayments] = useState([])
  const [brands, setBrands] = useState([])
  const [addressUsers, setAddressUsers] = useState([])
  const [type, setType] = useState("b2b")
  const [products, setProducts] = useState([])
  const [taxs, setTaxs] = useState([])
  const [discounts, setDiscounts] = useState([])
  const [productItems, setProductItems] = useState(defaultItems)
  const [productLoading, setProductLoading] = useState(false)
  const [contactList, setContactList] = useState([])
  const [salesList, setSalesList] = useState([])
  const [loading, setLoading] = useState(false)
  const loadBrand = () => {
    axios.get("/api/master/brand").then((res) => {
      setBrands(res.data.data)
    })
  }
  const loadWarehouse = () => {
    axios.get("/api/master/warehouse").then((res) => {
      setWarehouses(res.data.data)
    })
  }
  const loadTop = () => {
    axios.get("/api/master/top").then((res) => {
      setTermOfPayments(res.data.data)
    })
  }
  const loadAddress = (user_id) => {
    axios.get("/api/general/address-user/" + user_id).then((res) => {
      setAddressUsers(res.data.data)
    })
  }

  const loadProducts = (warehouse_id) => {
    axios.get("/api/master/products").then((res) => {
      const { data } = res.data
      const newData = data.map((item) => {
        const stock_warehouse =
          (item.stock_warehouse &&
            item.stock_warehouse.length > 0 &&
            item?.stock_warehouse) ||
          []
        const stock_off_market = stock_warehouse?.find(
          (item) => item.id == warehouse_id
        )
        return {
          ...item,
          stock_off_market: stock_off_market?.stock || 0,
        }
      })
      console.log(newData, "newData")
      setProducts(newData)
    })
  }

  const loadTaxs = () => {
    axios.get("/api/master/taxs").then((res) => {
      setTaxs(res.data.data)
    })
  }

  const loadDiscounts = () => {
    axios.get("/api/master/discounts").then((res) => {
      setDiscounts(res.data.data)
    })
  }

  const loadSalesReturnDetail = (refresh = false) => {
    setLoading(true)
    axios
      .get("/api/order/sales-return/detail/" + uid_return)
      .then((res) => {
        const { data } = res.data
        const forms = {
          ...data,
          contact: {
            label: data?.contact_user?.name,
            value: data?.contact_user?.id,
          },
          sales: {
            label: data?.sales_user?.name,
            value: data?.sales_user?.id,
          },
        }
        if (data?.return_items && data?.return_items?.length > 0) {
          const newData = data?.return_items?.map((item, index) => {
            return {
              product_id: item.product_id,
              price: item.price,
              qty: item.qty,
              tax_id: item.tax_id,
              discount_id: item.discount_id,
              total: item.total,
              uid_retur: item.uid_retur,
              id: item.id,
              key: index,
            }
          })
          setProductItems(newData)
        }
        if (refresh) {
          form.setFieldsValue(forms)
        }
        setLoading(false)
      })
      .catch((err) => setLoading(false))
  }

  useEffect(() => {
    loadBrand()
    loadWarehouse()
    loadTop()
    loadTaxs()
    loadDiscounts()
    loadSalesReturnDetail(true)
    handleGetContact()
    handleGetSales()
  }, [])

  const handleGetContact = () => {
    searchContact(null).then((results) => {
      const newResult = results.map((result) => {
        return { label: result.nama, value: result.id }
      })
      setContactList(newResult)
    })
  }

  const handleGetSales = () => {
    searchSales(null).then((results) => {
      const newResult = results.map((result) => {
        return { label: result.nama, value: result.id }
      })
      setSalesList(newResult)
    })
  }

  const handleSearchContact = async (e) => {
    return searchContact(e).then((results) => {
      const newResult = results.map((result) => {
        return { label: result.nama, value: result.id }
      })

      return newResult
    })
  }

  const handleSearchSales = async (e) => {
    return searchSales(e).then((results) => {
      const newResult = results.map((result) => {
        return { label: result.nama, value: result.id }
      })

      return newResult
    })
  }

  const handleChangeProductItem = ({ dataIndex, value, uid_retur, key }) => {
    const record = productItems.find((item) => item.id === key) || {}
    setProductLoading(true)
    axios
      .post("/api/order/sales-return/product-items", {
        ...record,
        [dataIndex]: value,
        uid_retur,
        key,
        item_id: key > 0 ? key : null,
      })
      .then((res) => {
        setProductLoading(false)
        loadSalesReturnDetail()
      })
  }

  const productItem = (value) => {
    const item = value.type === "add" ? defaultItems[0] : {}
    setProductLoading(true)
    axios
      .post(`/api/order/sales-return/product-items/${value.type}`, {
        ...value,
        ...item,
        item_id: value.key,
      })
      .then((res) => {
        const { message } = res.data
        setProductLoading(false)
        toast.success(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        loadSalesReturnDetail()
      })
  }
  const handleClickProductItem = (value) => {
    productItem({ ...value, newData: false })
    if (productItems.length === 1 && productItems[0].id === 0) {
      productItem({ ...value, newData: true })
    }
  }

  const getDueDate = (value = {}) => {
    const order_number =
      form.getFieldValue("order_number") || value?.order_number
    const payment_terms =
      form.getFieldValue("payment_terms") || value?.payment_terms
    axios
      .post("/api/order/sales-return/due-date", {
        order_number,
        payment_terms,
      })
      .then((res) => {
        const data = res.data
        form.setFieldsValue({ due_date: data.due_date })
      })
  }

  const onFinish = (values) => {
    axios
      .post("/api/order/sales-return/save", {
        ...values,
        uid_retur: uid_return,
        contact: values.contact.value,
        sales: values.sales.value,
        account_id: getItem("account_id"),
      })
      .then((res) => {
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        return navigate("/order/sales-return")
      })
      .catch((err) => {
        const { message } = err.response.data
        console.log(err.response, "err.response")
        toast.error(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }

  const rightContent = (
    <button
      onClick={() => {
        form.submit()
      }}
      className={`text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center`}
    >
      <span className="ml-2">Save Return</span>
    </button>
  )

  if (loading) {
    return (
      <Layout title="Detail" href="/order/sales-return">
        <LoadingFallback />
      </Layout>
    )
  }

  return (
    <Layout
      title="Sales Return"
      href="/order/sales-return"
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
        <Card title="Form Return">
          <div className="card-body row">
            <div className="col-md-4">
              <Form.Item
                label="Type Order"
                name="type"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Type Order!",
                  },
                ]}
              >
                <Select
                  placeholder="Pilih Type Order"
                  onChange={(val) => {
                    setType(val)
                  }}
                >
                  <Select.Option value={"b2b"} key={"b2b"}>
                    B2B
                  </Select.Option>
                  <Select.Option value={"b2c"} key={"b2c"}>
                    B2C
                  </Select.Option>
                  <Select.Option value={"manual"} key={"manual"}>
                    Manual
                  </Select.Option>
                </Select>
              </Form.Item>
              <Form.Item
                label="Payment Term"
                name="payment_terms"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Payment Term!",
                  },
                ]}
              >
                <Select
                  placeholder="Pilih Payment Term"
                  onChange={(e) => getDueDate({ payment_terms: e })}
                >
                  {termOfPayments.map((top) => (
                    <Select.Option value={top.id} key={top.id}>
                      {top.name}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>

              <Form.Item
                label="Sales"
                name="sales"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Sales!",
                  },
                ]}
              >
                <DebounceSelect
                  defaultOptions={
                    role === "sales"
                      ? [{ label: userData.name, value: userData.id }]
                      : salesList
                  }
                  showSearch
                  placeholder="Cari Sales"
                  fetchOptions={handleSearchSales}
                  filterOption={false}
                  className="w-full"
                />
              </Form.Item>
            </div>
            <div className="col-md-4">
              {type !== "manual" && (
                <Form.Item
                  label="Order Number"
                  name="order_number"
                  rules={[
                    {
                      required: true,
                      message: "Silakan pilih Order Number!",
                    },
                  ]}
                >
                  <ModalOrderNumber
                    url={
                      type === "b2c"
                        ? "/api/genie/order/list"
                        : "/api/order-lead"
                    }
                    form={form}
                    type={type}
                    getDueDate={getDueDate}
                  />
                </Form.Item>
              )}

              {type === "manual" && (
                <Form.Item
                  label="Order Number"
                  name="order_number"
                  rules={[
                    {
                      required: true,
                      message: "Silakan masukkan Order Number!",
                    },
                  ]}
                >
                  <Input />
                </Form.Item>
              )}
              <Form.Item label="Due Date" name="due_date">
                <Input readOnly />
              </Form.Item>

              <Form.Item label="Address" name="address_id">
                <Select placeholder="Pilih Address">
                  {addressUsers.map((address) => (
                    <Select.Option value={address.id} key={address.id}>
                      {address.alamat_detail}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>
            </div>

            <div className="col-md-4">
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
                <Select placeholder="Pilih Brand">
                  {brands.map((brand) => (
                    <Select.Option value={brand.id} key={brand.id}>
                      {brand.name}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>
              <Form.Item
                label="Contact"
                name="contact"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Contact!",
                  },
                ]}
              >
                <DebounceSelect
                  defaultOptions={contactList}
                  showSearch
                  placeholder="Cari Contact"
                  fetchOptions={handleSearchContact}
                  filterOption={false}
                  className="w-full"
                  onChange={(val) => {
                    loadAddress(val?.value)
                  }}
                />
              </Form.Item>
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
                  placeholder="Pilih Warehouse"
                  onChange={(e) => loadProducts(e)}
                >
                  {warehouses.map((warehouse) => (
                    <Select.Option value={warehouse.id} key={warehouse.id}>
                      {warehouse.name}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>
            </div>
            <div className="col-md-12">
              <Form.Item label="Notes" name="notes">
                <TextArea />
              </Form.Item>
            </div>
          </div>
        </Card>
      </Form>
      <Card title="Detail Product" className="mt-4">
        <ProductList
          data={productItems}
          products={products}
          taxs={taxs}
          discounts={discounts}
          handleChange={handleChangeProductItem}
          handleClick={handleClickProductItem}
          loading={productLoading}
        />
      </Card>

      <div className="float-right mt-6">
        <button
          onClick={() => {
            form.submit()
          }}
          className={`text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center`}
        >
          <span className="ml-2">Save Return</span>
        </button>
      </div>
    </Layout>
  )
}

export default SalesReturnForm
