import { DownOutlined } from "@ant-design/icons"
import {
  DatePicker,
  Dropdown,
  Form,
  Input,
  Menu,
  Popconfirm,
  Select,
  Skeleton,
  message,
} from "antd"
import TextArea from "antd/lib/input/TextArea"
import axios from "axios"
import moment from "moment"
import React, { useEffect, useState } from "react"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../components/layout"
import { getItem, inArray } from "../../helpers"
import ProductList from "./Components/ProductList"
import {
  productListAllocationHistoryColumns,
  productListColumns,
  barcodeListColumns,
} from "./config"

const InventoryAddProducts = () => {
  const [form] = Form.useForm()
  const { inventory_id } = useParams()
  // hooks
  const navigate = useNavigate()
  // state
  const intialProduct = {
    key: 0,
    id: null,
    product_id: null,
    price: 0,
    qty: 1,
    qty_alocation: 0,
    sub_total: 0,
    from_warehouse_id: null,
    to_warehouse_id: null,
    sku: null,
    u_of_m: null,
  }
  const [productData, setProductData] = useState([intialProduct])
  const [historyAllocation, setHistoryAllocation] = useState([])
  const [products, setProducts] = useState([])
  const [warehouses, setWarehouses] = useState([])
  const [detail, setDetail] = useState({})
  const [loadingAlocated, handleLoadingAlocated] = useState(false)
  const [loading, setLoading] = useState(false)
  const [loadingProduct, setLoadingProduct] = useState(false)
  const [openPopAlokasi, setOpenPopAlokasi] = useState(false)

  // api
  const loadProducts = () => {
    setLoadingProduct(true)
    axios.get("/api/master/product-lists").then((res) => {
      setProducts(res.data.data)
      setLoadingProduct(false)
    })
  }

  const loadWarehouse = () => {
    setLoading(true)
    axios.get("/api/master/warehouse").then((res) => {
      setWarehouses(res.data.data)
      setLoading(false)
    })
  }

  const loadInventoryDetail = () => {
    setLoading(true)
    axios.get(`/api/inventory/product/detail/${inventory_id}`).then((res) => {
      const { data } = res.data
      // console.log(data, "data")
      setDetail(data)
      setLoading(false)
      form.setFieldsValue({
        ...data,
        received_date: moment(data.received_date || new Date(), "YYYY-MM-DD"),
      })

      const products = data.selected_po?.items.filter(
        (item) => item.ref === inventory_id
      )
      const newProducts = products?.map((item, index) => {
        return {
          key: index,
          id: item.id,
          product_id: item.product_id,
          price: item.price,
          qty: item.qty_can_allocated,
          qty_alocation: item.qty_can_allocated,
          sub_total: item.subtotal,
          from_warehouse_id: data.warehouse_id,
          to_warehouse_id: data.warehouse_id,
          sku: item.sku,
          u_of_m: item.uom,
          // is_allocated: item.is_allocated,
        }
      })

      const newhistory = data.history_allocations?.map((row, index) => {
        return {
          ...row,
          key: index,
          qty: row.quantity,
        }
      })
      setHistoryAllocation(newhistory)
      setProductData(newProducts)
    })
  }

  const getCreatedInfo = () => {
    setLoading(true)
    axios.get("/api/inventory/info/created").then((res) => {
      form.setFieldsValue(res.data)
      setLoading(false)
    })
  }

  // cycle
  useEffect(() => {
    loadProducts()
    loadWarehouse()
    if (inventory_id) {
      loadInventoryDetail()
    } else {
      getCreatedInfo()
    }
  }, [])

  const handleChangeProductItem = ({ dataIndex, value, key }) => {
    const datas = [...productData]
    const from_warehouse_id = datas[key].from_warehouse_id
    const to_warehouse_id = datas[key].to_warehouse_id

    if (value === null) {
      datas[key][dataIndex] = null
      return setProductData(datas)
    }

    if (dataIndex === "from_warehouse_id") {
      if (to_warehouse_id === value) {
        return message.error(
          "From warehouse tidak boleh sama dengan To warehouse"
        )
      }
      datas[key][dataIndex] = value
    }

    if (dataIndex === "to_warehouse_id") {
      if (from_warehouse_id === value) {
        return message.error(
          "From warehouse tidak boleh sama dengan To warehouse"
        )
      }
      const exist = datas.find((item) => item.to_warehouse_id === value)
      if (exist) {
        const newData = datas.filter((item) => item.key !== key)
        return setProductData(newData)
      } else {
        datas[key][dataIndex] = value
      }
    }

    if (dataIndex === "qty_alocation") {
      datas[key][dataIndex] = value
    }

    setProductData(datas)
  }

  const handleClickProductItem = ({ key, type }) => {
    const datas = [...productData]
    if (type === "add") {
      const lastData = datas[datas.length - 1]
      datas.push({
        key: lastData.key + 1,
        id: 0,
        product_id: lastData.product_id,
        price: 0,
        qty: 1,
        qty_alocation: 0,
        sub_total: 0,
        from_warehouse_id: lastData.from_warehouse_id,
        to_warehouse_id: null,
        sku: lastData.sku,
        u_of_m: lastData.u_of_m,
      })
      return setProductData(datas)
    }

    if (type === "add-qty") {
      const item = datas[key]
      if (item.qty_alocation + 1 <= item.qty) {
        const qty_alocation = item.qty_alocation + 1
        datas[key]["qty_alocation"] = qty_alocation
        return setProductData(datas)
      }

      return null
    }

    if (type === "remove-qty") {
      const item = datas[key]
      if (item.qty_alocation > 1) {
        const qty_alocation = item.qty_alocation - 1
        datas[key]["qty_alocation"] = qty_alocation
        return setProductData(datas)
      }
      return setProductData(datas)
    }

    const newData = datas.filter((item) => item.key !== key)
    return setProductData(newData)
  }

  const onFinish = (values) => {
    const productItem = productData.every((item) => item.product_id)
    if (!productItem) {
      return message.error("Please select product")
    }

    const data = {
      ...values,
      items: productData,
      account_id: getItem("account_id"),
    }
    let url = "/api/inventory/product/stock/save"
    if (inventory_id) {
      data.inventory_id = inventory_id
      url = `/api/inventory/product/stock/update/${inventory_id}`
    }
    axios
      .post(url, data)
      .then((res) => {
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        return navigate(-1)
      })
      .catch((err) => {
        const { message } = err.response.data
        toast.error(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }

  const handleAllocateProduct = () => {
    handleLoadingAlocated(true)
    const product = productData.every((item) => item.to_warehouse_id)
    if (!product) {
      return toast.error("Periksa kembali inputan anda")
    }

    axios
      .post(`/api/inventory/product/stock/allocated/${inventory_id}`, {
        items: productData,
        account_id: getItem("account_id"),
      })
      .then((res) => {
        handleLoadingAlocated(false)
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        return navigate(-1)
      })
      .catch(() => handleLoadingAlocated(false))
  }

  const handleSubmitRetrigger = () => {
    handleLoadingAlocated(true)
    const product = productData
    if (product[0].qty > 0) {
      return toast.error("Mohon maaf stok sudah pernah dialokasikan")
    }

    axios
      .post(`/api/inventory/product/stock/retrigger-stock/${inventory_id}`, {
        items: productData,
        account_id: getItem("account_id"),
      })
      .then(() => {
        handleLoadingAlocated(false)
        toast.success("Retrigger Stock berhasil Diproses", {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
      .catch(() => handleLoadingAlocated(false))
  }

  const menu = (
    <Menu>
      <Menu.Item onClick={(e) => handleSubmitRetrigger(e)}>
        Retrigger Stock
      </Menu.Item>
    </Menu>
  )

  const rightContent = (
    <div className="flex items-center">
      <Dropdown overlay={menu}>
        <button
          className="text-blue-700 border-[1px] border-blue-700 hover:bg-blue-800/10 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mx-2"
          onClick={(e) => e.preventDefault()}
        >
          <span className="mr-2">More Option</span>
          <DownOutlined />
        </button>
      </Dropdown>
    </div>
  )

  return (
    <Layout
      onClick={() => navigate(-1)}
      title="Detail Stock Product Received"
      rightContent={rightContent}
    >
      <div className="card">
        <div className="card-header">
          <div className="header-titl">
            <strong>Informasi Penerimaan Produk</strong>
          </div>
        </div>
        <div className="card-body">
          <Form
            form={form}
            name="basic"
            layout="vertical"
            onFinish={onFinish}
            //   onFinishFailed={onFinishFailed}
            autoComplete="off"
          >
            <div className="row">
              <div className="col-md-6">
                <Form.Item
                  label="Company"
                  name="company_name"
                  rules={[
                    {
                      required: true,
                      message: "Silakan masukkan Status!",
                    },
                  ]}
                >
                  {loading ? (
                    <Skeleton.Input
                      active
                      size={"default"}
                      block={false}
                      style={{ width: 400 }}
                    />
                  ) : (
                    <Input disabled />
                  )}
                </Form.Item>

                <Form.Item
                  label="Vendor Code"
                  name="vendor"
                  rules={[
                    {
                      required: true,
                      message: "Silakan masukkan Vendor!",
                    },
                  ]}
                >
                  {loading ? (
                    <Skeleton.Input
                      active
                      size={"default"}
                      block={false}
                      style={{ width: 400 }}
                    />
                  ) : (
                    <Input
                      placeholder="Input Vendor"
                      disabled
                      // bordered={false}
                    />
                  )}
                </Form.Item>

                <Form.Item label="Created by" name="created_by_name">
                  {loading ? (
                    <Skeleton.Input
                      active
                      size={"default"}
                      block={false}
                      style={{ width: 400 }}
                    />
                  ) : (
                    <Input
                      placeholder=" Created by"
                      disabled
                      // bordered={false}
                    />
                  )}
                </Form.Item>

                <Form.Item label="Created On" name="created_on">
                  {loading ? (
                    <Skeleton.Input
                      active
                      size={"default"}
                      block={false}
                      style={{ width: 400 }}
                    />
                  ) : (
                    <Input
                      placeholder=" Created On"
                      disabled
                      // bordered={false}
                    />
                  )}
                </Form.Item>
              </div>

              <div className="col-md-6">
                <Form.Item
                  label="Warehouse"
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
                      style={{ width: 400 }}
                    />
                  ) : (
                    <Select placeholder="Pilih Warehouse" disabled>
                      {warehouses?.map((warehouse) => (
                        <Select.Option value={warehouse.id} key={warehouse.id}>
                          {warehouse.name}
                        </Select.Option>
                      ))}
                    </Select>
                  )}
                </Form.Item>

                <Form.Item
                  label="Vendor Name"
                  name="vendor_name"
                  rules={[
                    {
                      required: true,
                      message: "Silakan masukkan Vendor!",
                    },
                  ]}
                >
                  {loading ? (
                    <Skeleton.Input
                      active
                      size={"default"}
                      block={false}
                      style={{ width: 400 }}
                    />
                  ) : (
                    <Input
                      placeholder="Input Vendor"
                      disabled
                      // bordered={false}
                    />
                  )}
                </Form.Item>

                <Form.Item
                  label="Received Date"
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
                      style={{ width: 400 }}
                    />
                  ) : (
                    <DatePicker className="w-full" disabled />
                  )}
                </Form.Item>

                <Form.Item label="Notes" name="note">
                  {loading ? (
                    <Skeleton.Input
                      active
                      size={"default"}
                      block={false}
                      style={{ width: 400 }}
                    />
                  ) : (
                    <TextArea placeholder=" Notes" disabled />
                  )}
                </Form.Item>
              </div>
            </div>
          </Form>
        </div>
      </div>

      <div className="card">
        <div className="card-header">
          <div className="header-titl">
            <strong>Detail Item</strong>
          </div>
        </div>
        <div className="card-body">
          <ProductList
            loading={loading}
            loadingProduct={loadingProduct}
            data={productData}
            products={products || []}
            warehouses={warehouses}
            disabled={{
              from_warehouse_id: true,
              qty: true,
              product_id: true,
              to_warehouse_id: true,
              action: true,
            }}
            columns={
              productListColumns?.filter(
                (item) =>
                  !inArray(item.dataIndex, [
                    "action",
                    "from_warehouse_id",
                    "qty_alocation",
                  ])
              ) || []
            }
            handleChange={handleChangeProductItem}
            handleClick={handleClickProductItem}
            multiple={false}
            // summary={(pageData) => {
            //   if (productData.length > 0) {
            //     return (
            //       <>
            //         <Table.Summary.Row>
            //           <Table.Summary.Cell index={0}></Table.Summary.Cell>
            //           <Table.Summary.Cell index={1}></Table.Summary.Cell>
            //           <Table.Summary.Cell index={2}></Table.Summary.Cell>
            //           <Table.Summary.Cell index={3}></Table.Summary.Cell>
            //           <Table.Summary.Cell index={4}>
            //             <strong>Total Qty</strong>
            //           </Table.Summary.Cell>
            //           <Table.Summary.Cell index={5}>
            //             <strong>{detail?.total_qty}</strong>
            //           </Table.Summary.Cell>
            //         </Table.Summary.Row>
            //       </>
            //     )
            //   }
            // }}
          />
        </div>
      </div>
      <div className="card">
        <div className="card-header">
          <div className="header-titl">
            <strong>List Barcode</strong>
          </div>
        </div>
        <div className="card-body">
          <ProductList
            loading={loading}
            loadingProduct={loadingProduct}
            // data={historyAllocation}
            products={products || []}
            warehouses={warehouses}
            columns={
              barcodeListColumns?.filter(
                (item) => item.dataIndex !== "from_warehouse_id"
              ) || []
            }
            handleChange={handleChangeProductItem}
            multiple={false}
            showAdmore={false}
          />
        </div>
      </div>
      <div className="card">
        <div className="card-header">
          <div className="header-titl">
            <strong>History Alokasi Item</strong>
          </div>
        </div>
        <div className="card-body">
          <ProductList
            loading={loading}
            loadingProduct={loadingProduct}
            data={historyAllocation}
            products={products || []}
            warehouses={warehouses}
            disabled={{
              from_warehouse_id: true,
              qty: true,
              product_id: true,
              to_warehouse_id: true,
            }}
            columns={
              productListAllocationHistoryColumns?.filter(
                (item) => item.dataIndex !== "from_warehouse_id"
              ) || []
            }
            handleChange={handleChangeProductItem}
            multiple={false}
            showAdmore={false}
            // summary={(pageData) => {
            //   if (historyAllocation.length > 0) {
            //     return (
            //       <>
            //         <Table.Summary.Row>
            //           <Table.Summary.Cell index={0}></Table.Summary.Cell>
            //           <Table.Summary.Cell index={1}></Table.Summary.Cell>
            //           <Table.Summary.Cell index={2}></Table.Summary.Cell>
            //           <Table.Summary.Cell index={3}></Table.Summary.Cell>
            //           <Table.Summary.Cell index={4}>
            //             <strong>Total Qty</strong>
            //           </Table.Summary.Cell>
            //           <Table.Summary.Cell index={5}>
            //             <strong>{detail?.total_qty}</strong>
            //           </Table.Summary.Cell>
            //         </Table.Summary.Row>
            //       </>
            //     )
            //   }
            // }}
          />
        </div>
      </div>

      {inArray(detail?.inventory_status, ["received"]) && (
        <div className="card p-6 ">
          <div className="flex justify-end">
            <Popconfirm
              open={openPopAlokasi}
              title="Apakah Anda yakin ingin mengalokasikan item ini?"
              okText="Ya"
              cancelText="Batal"
              onConfirm={handleAllocateProduct}
              onCancel={() => setOpenPopAlokasi(false)}
              okButtonProps={{
                style: { width: "70px" },
                loading: loadingAlocated,
              }}
              cancelButtonProps={{ style: { width: "70px" } }}
            >
              <button
                className={`text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center`}
                // style={{
                //   backgroundColor: "#1A56DC",
                //   borderColor: "#1A56DC",
                //   color: "white",
                // }}
                loading={loadingAlocated}
                // onClick={() => handleAllocateProduct()}
                onClick={(e) => {
                  e.preventDefault()
                  setOpenPopAlokasi(true)
                }}
              >
                Alokasi Stok
              </button>
            </Popconfirm>
          </div>
        </div>
      )}
    </Layout>
  )
}

export default InventoryAddProducts
