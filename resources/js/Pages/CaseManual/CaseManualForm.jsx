import { LoadingOutlined } from "@ant-design/icons"
import { Card, Form, Input, Select } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import LoadingFallback from "../../components/LoadingFallback"
import DebounceSelect from "../../components/atoms/DebounceSelect"
import Layout from "../../components/layout"
import { getItem } from "../../helpers"
import ProductList from "./Components/ProductList"
import { searchContact } from "./service"

const CaseManualForm = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const userData = getItem("user_data", true)
  const { uid_case } = useParams()
  const defaultItems = [
    {
      product_id: null,
      qty: 1,
      sku: null,
      u_of_m: null,
      id: 0,
      key: 0,
    },
  ]
  const [detail, setDetail] = useState(null)

  const [typeCase, setTypeCase] = useState([])
  const [sourceCase, setSourceCase] = useState([])
  const [categoryCase, setCategoryCase] = useState([])
  const [priorityCase, setPriorityCase] = useState([])
  const [statusCase, setStatusCase] = useState([])

  const [products, setProducts] = useState([])
  const [productItems, setProductItems] = useState(defaultItems)
  const [productLoading, setProductLoading] = useState(false)
  const [loading, setLoading] = useState(false)
  const [contactList, setContactList] = useState([])

  const loadCase = (type, setter) => {
    axios.get("/api/master/" + type).then((res) => {
      setter(res.data.data)
    })
  }

  const loadProducts = (warehouse_id) => {
    axios.get("/api/master/products/sales-offline").then((res) => {
      const { data } = res.data
      const newData = data.map((item) => {
        const stock_warehouse =
          (item.stock_warehouse &&
            item.stock_warehouse.length > 0 &&
            item?.stock_warehouse) ||
          []
        const stock_off_market = stock_warehouse.find(
          (item) => item.id == warehouse_id
        )
        return {
          ...item,
          stock_off_market: stock_off_market?.stock || 0,
        }
      })
      setProducts(newData)
    })
  }

  const loadProductDetail = () => {
    setLoading(true)
    axios
      .get(`/api/case/manual/detail/${uid_case}`)
      .then((res) => {
        const { data } = res.data
        setLoading(false)
        setDetail(data)
        const forms = {
          ...data,
          contact: {
            label: data?.contact_name,
            value: data?.contact_user?.id,
          },
          payment_terms: data?.payment_term?.id,
        }

        const newData = data?.items?.map((item, key) => {
          return {
            ...item,
            key,
          }
        })
        setProductItems(newData)
        form.setFieldsValue(forms)
      })
      .catch((e) => setLoading(false))
  }

  const handleGetContact = () => {
    searchContact(null).then((results) => {
      const newResult = results.map((result) => {
        return { label: result.nama, value: result.id }
      })
      setContactList(newResult)
    })
  }

  useEffect(() => {
    loadCase("type-case", setTypeCase)
    loadCase("category-case", setCategoryCase)
    loadCase("source-case", setSourceCase)
    loadCase("priority-case", setPriorityCase)
    loadCase("status-case", setStatusCase)
    loadProducts()
    if (uid_case) {
      loadProductDetail()
    }
    handleGetContact()
    form.setFieldValue("created_user", userData?.name)
  }, [])

  const handleSearchContact = async (e) => {
    return searchContact(e).then((results) => {
      const newResult = results.map((result) => {
        return { label: result.nama, value: result.id }
      })

      return newResult
    })
  }

  const handleChangeProductItem = ({ dataIndex, value, key }) => {
    const datas = [...productItems]
    const product = products.find((item) => item.id === value)
    datas[key][dataIndex] = value
    datas[key]["u_of_m"] = product?.u_of_m
    datas[key]["sku"] = product?.sku
    console.log(datas)
    setProductItems(datas)
  }

  const handleClickProductItem = ({ key, type }) => {
    const datas = [...productItems]
    if (type === "add") {
      const lastData = datas[datas.length - 1]
      datas.push({
        key: lastData.key + 1,
        id: null,
        product_id: null,
        sku: null,
        u_of_m: null,
        qty: 1,
      })

      return setProductItems(datas)
    }

    if (type === "add-qty") {
      const item = datas[key]
      const qty = item.qty + 1
      datas[key]["qty"] = qty
      return setProductItems(datas)
    }

    if (type === "remove-qty") {
      const item = datas[key]
      if (item.qty > 1) {
        const qty = item.qty - 1
        datas[key]["qty"] = qty
        return setProductItems(datas)
      }
      return setProductItems(datas)
    }

    const newData = datas.filter((item) => item.key !== key)
    return setProductItems(newData)
  }

  const onFinish = (values) => {
    const hasProduct = productItems.every((item) => item.product_id)
    if (!hasProduct) {
      toast.error("Product harus diisi", {
        position: toast.POSITION.TOP_RIGHT,
      })
      return
    }

    const params = uid_case ? `save/${uid_case}` : "save"

    axios
      .post("/api/case/manual/" + params, {
        ...values,
        contact: values.contact.value,
        product_items: productItems,
        account_id: getItem("account_id"),
      })
      .then((res) => {
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        return navigate("/case/manual")
      })
      .catch((err) => {
        const { message } = err.response.data
        toast.error(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }

  if (loading) {
    return (
      <Layout title="Detail" href="/case/manual">
        <LoadingFallback />
      </Layout>
    )
  }
  return (
    <Layout
      title="Order Manual Form"
      href="/case/manual"
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
        <Card title="Form Order">
          <div className="card-body row">
            <div className="col-md-4">
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
                  showSearch
                  placeholder="Cari Contact"
                  fetchOptions={handleSearchContact}
                  filterOption={false}
                  defaultOptions={contactList}
                  className="w-full"
                  onChange={(val) => {
                    loadAddress(val?.value)
                  }}
                />
              </Form.Item>
              <Form.Item
                label="Source Case"
                name="source_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Source Case!",
                  },
                ]}
              >
                <Select placeholder="Pilih Source Case">
                  {sourceCase.map((item) => (
                    <Select.Option value={item.id} key={item.id}>
                      {item.source_name}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>
              <Form.Item
                label="Created by"
                name="created_user"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Status Case!",
                  },
                ]}
              >
                <Input disabled />
              </Form.Item>
            </div>
            <div className="col-md-4">
              <Form.Item
                label="Type Case"
                name="type_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Type Case!",
                  },
                ]}
              >
                <Select placeholder="Pilih Type Case">
                  {typeCase.map((item) => (
                    <Select.Option value={item.id} key={item.id}>
                      {item.type_name}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>

              <Form.Item
                label="Category Case"
                name="category_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Category Case!",
                  },
                ]}
              >
                <Select placeholder="Pilih Category Case">
                  {categoryCase.map((item) => (
                    <Select.Option value={item.id} key={item.id}>
                      {item.category_name}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>
            </div>
            <div className="col-md-4">
              <Form.Item
                label="Priority Case"
                name="priority_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Priority Case!",
                  },
                ]}
              >
                <Select placeholder="Pilih Priority Case">
                  {priorityCase.map((item) => (
                    <Select.Option value={item.id} key={item.id}>
                      {item.priority_name}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>
              <Form.Item
                label="Status Case"
                name="status_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Status Case!",
                  },
                ]}
              >
                <Select placeholder="Pilih Status Case">
                  {statusCase.map((item) => (
                    <Select.Option value={item.id} key={item.id}>
                      {item.status_name}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>
            </div>
          </div>
        </Card>
      </Form>
      <Card title="Detail Product" className="mt-4">
        <ProductList
          data={productItems}
          products={products}
          handleChange={handleChangeProductItem}
          handleClick={handleClickProductItem}
          loading={productLoading}
        />
      </Card>

      <div className="card mt-6 p-4 items-end">
        <button
          onClick={() => {
            if (productLoading) {
              toast.error("Please wait for the product to load")
              return
            }
            form.submit()
          }}
          className={`text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center`}
        >
          {productLoading && <LoadingOutlined />}{" "}
          <span className="ml-2">Save Order</span>
        </button>
      </div>
    </Layout>
  )
}

export default CaseManualForm
