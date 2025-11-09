import { CheckOutlined, DeleteOutlined } from "@ant-design/icons"
import { Popconfirm, Skeleton, Table, Tabs } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { useParams } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../components/layout"
import { RenderIf, formatNumber, getItem, inArray } from "../../helpers"
import ModalActivity from "./Components/ModalActivity"
import ModalReject from "./Components/ModalReject"
import OrderDetailInfo from "./Components/OrderDetailInfo"
import ProductList from "./Components/ProductList"
import { activityColumns, negotiationsColumns } from "./config"

const { TabPane } = Tabs

const LeadMasterDetail = () => {
  const role = localStorage.getItem("role")
  const { uid_lead } = useParams()
  const defaultItems = [
    {
      product_id: null,
      price: null,
      qty: 1,
      tax_id: null,
      discount: null,
      final_price: null,
      total_price: null,
      uid_lead,
      margin_price: 0,
      bundling: 1,
      price_product: 0,
      price_nego: 0,
      total_price_nego: 0,
      stock: 0,
      id: 0,
      key: 0,
    },
  ]
  const [orderDetail, setDetailOrder] = useState(null)
  const [productNeed, setProductNeed] = useState(defaultItems)
  const [activityData, setActivityData] = useState([])
  const [negotiationsData, setNegotiationsData] = useState([])
  const [activeTab, setActiveTab] = useState(1)
  const [productLoading, setProductLoading] = useState(false)
  const [products, setProducts] = useState([])
  const [taxs, setTaxs] = useState([])
  const [discounts, setDiscounts] = useState([])

  const [loading, setLoading] = useState(false)
  const loadDetailOrderLead = (loading = true) => {
    setLoading(loading)
    axios.get(`/api/lead-master/detail/${uid_lead}`).then((res) => {
      const { data } = res.data
      setDetailOrder(data)
      if (data.product_needs && data.product_needs.length > 0) {
        const productNeeds = data.product_needs.map((item, index) => {
          const stock_warehouse =
            (item?.product?.stock_warehouse &&
              item?.product?.stock_warehouse.length > 0 &&
              item?.product?.stock_warehouse) ||
            []
          const stock_off_market =
            stock_warehouse?.find((item) => item.id == data?.warehouse_id)
              ?.stock || item?.product?.stock_off_market
          const som = stock_off_market
          const bundling = item?.product?.qty_bundling
          return {
            key: index,
            id: item.id,
            product: item?.product?.name || "-",
            product_id: item?.product_id,
            price: formatNumber(item?.prices?.final_price),
            qty: item?.qty,
            total_price: formatNumber(item?.prices?.final_price * item?.qty),
            final_price: formatNumber(item?.total),
            margin_price: formatNumber(item?.margin_price),
            discount: item?.discount,
            bundling,
            tax_id: item?.tax_id,
            tax_amount: formatNumber(item?.tax_amount),
            uid_lead,
            stock: som,
            price_nego: item?.price_nego,
            price_product: formatNumber(item?.price),
            total_price_nego: formatNumber(item?.price_nego),
            // disabled_discount: item?.disabled_discount,
            // disabled_price_nego: item?.disabled_price_nego,
          }
        })
        setProductNeed(productNeeds)
      }

      setActivityData(data?.lead_activities)

      setNegotiationsData(data?.lead_negotiations)
      setLoading(false)
      loadProducts(data)
    })
  }

  const loadProducts = (orderDetail) => {
    axios.get("/api/master/products/sales-offline").then((res) => {
      const { data } = res.data
      const newProduct = data.map((item) => {
        const stock_warehouse =
          (item.stock_warehouse &&
            item.stock_warehouse.length > 0 &&
            item?.stock_warehouse) ||
          []
        const stock_off_market = stock_warehouse.find(
          (item) => item.id == orderDetail?.warehouse_id
        )

        const canBuy =
          stock_off_market?.stock < item?.qty_bundling ? true : false

        const stock = stock_off_market?.stock || 0
        return {
          ...item,
          stock_off_market: stock_off_market?.stock || 0,
          disabled: stock < 1 ? true : false,
        }
      })

      setProducts(
        newProduct
          .sort(function (a, b) {
            return b.stock_off_market - a.stock_off_market
          })
          .filter((item) => item)
      )
    })
  }

  const loadTaxs = () => {
    axios.get("/api/master/taxs").then((res) => {
      setTaxs(res.data.data)
    })
  }

  const loadDiscounts = () => {
    axios.get("/api/master/discounts/sales-offline").then((res) => {
      setDiscounts(res.data.data)
    })
  }

  useEffect(() => {
    loadDetailOrderLead()

    loadTaxs()
    loadDiscounts()
  }, [])

  const handleChangeTab = (key) => {
    setActiveTab(key)
  }

  const handleChangeProductPrice = ({ dataIndex, value, key }) => {
    console.log(dataIndex, value, "handle change product price")
    const data = [...productNeed]
    // if (dataIndex ==='qty'){}
    data[key][dataIndex] = value
    setProductNeed(data)
  }

  const handleChangeProductItem = ({ dataIndex, value, key }) => {
    console.log(dataIndex, value, "handle change product")
    const record = productNeed.find((item) => item.id === key) || {}
    setProductLoading(true)
    axios
      .post("/api/lead-master/product-needs", {
        ...record,
        [dataIndex]: parseInt(value),
        final_price: record?.price_nego,
        price: record?.price_product,
        qty: dataIndex == "product_id" ? 1 : record.qty,
        key,
        item_id: key > 0 ? key : null,
      })
      .then((res) => {
        setProductLoading(false)
        loadDetailOrderLead(false)
      })
  }

  const productItem = (value) => {
    const item = value.type === "add" ? defaultItems[0] : {}
    setProductLoading(true)
    axios
      .post(`/api/lead-master/product-needs/${value.type}`, {
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
        loadDetailOrderLead(false)
      })
  }
  const handleClickProductItem = (value) => {
    productItem({ ...value })
    if (productNeed.length === 1 && productNeed[0].id === 0) {
      productItem({ ...value })
    }
  }

  const handleAction = (value, params = {}) => {
    if (value === "save-negotiation") {
      const hasProduct = productNeed.every((item) => item.product_id)
      if (!hasProduct) {
        toast.error("Mohon Pilih Produk Terlebih Dahulu", {
          position: toast.POSITION.TOP_RIGHT,
        })
        return
      }
    }
    axios
      .post(`/api/lead-master/action/${value}`, { uid_lead, ...params })
      .then((res) => {
        const { message } = res.data
        toast.success(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        loadDetailOrderLead()
      })
      .catch((err) => {
        const { message } = err.response.data
        toast.error(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }

  const handleDeleteActivity = (activity_id) => {
    axios
      .post(`/api/lead-master/activity/delete/${activity_id}`, {
        _method: "DELETE",
      })
      .then((res) => {
        const { message } = res.data
        toast.success(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        loadDetailOrderLead()
      })
      .catch((err) => {
        const { message } = err.response.data
        toast.error(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }

  // if (loading) {
  //   return (
  //     <Layout title="Detail" href="/lead-master">
  //       <LoadingFallback />
  //     </Layout>
  //   )
  // }

  const canSave = orderDetail?.status === "0" || orderDetail?.status === "6"
  const show = !inArray(getItem("role"), ["adminsales"])

  return (
    <Layout
      title="Detail"
      href="/lead-master"
      // rightContent={rightContent}
    >
      <>
        <Tabs activeKey={`${activeTab}`} onChange={handleChangeTab}>
          <TabPane tab="Stage 1: Lead Info & Activity" key={1}>
            <div>
              <OrderDetailInfo order={orderDetail} loading={loading} />

              {/* lead activity */}
              <div className="card">
                <div className="card-header flex justify-between items-center">
                  <h1 className="header-title">Lead Activity</h1>

                  <RenderIf isTrue={orderDetail?.status_name !== "Qualified"}>
                    <ModalActivity
                      refetch={() => loadDetailOrderLead()}
                      detail={{ uid_lead }}
                    />
                  </RenderIf>
                </div>

                <div className="card-body">
                  <Table
                    scroll={{ x: "max-content" }}
                    tableLayout={"auto"}
                    dataSource={activityData}
                    columns={[
                      ...activityColumns,
                      {
                        title: "Action",
                        key: "id",
                        align: "center",
                        fixed: "right",
                        width: 100,
                        render: (text, record) => {
                          if (orderDetail?.status_name !== "Qualified") {
                            return (
                              <div className="flex justify-between items-center">
                                <ModalActivity
                                  refetch={() => loadDetailOrderLead()}
                                  detail={{ uid_lead }}
                                  initialValues={record}
                                  update
                                />
                                <Popconfirm
                                  title="Yakin Hapus Data Ini?"
                                  onConfirm={() =>
                                    handleDeleteActivity(record.id)
                                  }
                                  okText="Yes"
                                  cancelText="No"
                                >
                                  <button
                                    className="ml-2 text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
                                    title="Delete"
                                  >
                                    <DeleteOutlined />
                                  </button>
                                </Popconfirm>
                              </div>
                            )
                          }
                        },
                      },
                    ]}
                    loading={loading}
                    pagination={false}
                    rowKey="id"
                  />
                </div>
              </div>
            </div>
          </TabPane>
          <TabPane tab="Stage 2: Product Needs" key={2}>
            <div className="card">
              <div className="card-header flex justify-between items-center">
                <h1 className="header-title">Product Need</h1>
              </div>
              <div className="card-body">
                <ProductList
                  data={productNeed}
                  products={products}
                  taxs={taxs}
                  discounts={discounts}
                  onChange={handleChangeProductPrice}
                  handleChange={handleChangeProductItem}
                  handleClick={handleClickProductItem}
                  loading={productLoading}
                  disabled={
                    orderDetail?.status > "0" &&
                    orderDetail?.status !== "9" &&
                    orderDetail?.status !== "6"
                  }
                  isQualified={orderDetail?.status === "1"}
                  summary={(pageData) => {
                    if (productNeed.length > 0) {
                      return (
                        <>
                          <Table.Summary.Row>
                            <Table.Summary.Cell colSpan={7} align="right">
                              Subtotal
                            </Table.Summary.Cell>
                            {productLoading ? (
                              <Skeleton.Input
                                active
                                size={"default"}
                                block={false}
                                style={{ width: 250 }}
                              />
                            ) : (
                              <Table.Summary.Cell align="right">
                                {formatNumber(orderDetail?.subtotal, "Rp ")}
                              </Table.Summary.Cell>
                            )}
                            <Table.Summary.Cell />
                          </Table.Summary.Row>
                          <Table.Summary.Row>
                            <Table.Summary.Cell colSpan={7} align="right">
                              Discount
                            </Table.Summary.Cell>
                            {productLoading ? (
                              <Skeleton.Input
                                active
                                size={"default"}
                                block={false}
                                style={{ width: 250 }}
                              />
                            ) : (
                              <Table.Summary.Cell align="right">
                                {formatNumber(
                                  orderDetail?.discount_amount,
                                  "Rp "
                                )}
                              </Table.Summary.Cell>
                            )}
                            <Table.Summary.Cell />
                          </Table.Summary.Row>
                          <Table.Summary.Row>
                            <Table.Summary.Cell colSpan={7} align="right">
                              DPP
                            </Table.Summary.Cell>
                            {productLoading ? (
                              <Skeleton.Input
                                active
                                size={"default"}
                                block={false}
                                style={{ width: 250 }}
                              />
                            ) : (
                              <Table.Summary.Cell align="right">
                                {formatNumber(orderDetail?.amount, "Rp ")}
                              </Table.Summary.Cell>
                            )}

                            <Table.Summary.Cell />
                          </Table.Summary.Row>
                          <Table.Summary.Row>
                            <Table.Summary.Cell colSpan={7} align="right">
                              Tax
                            </Table.Summary.Cell>
                            {productLoading ? (
                              <Skeleton.Input
                                active
                                size={"default"}
                                block={false}
                                style={{ width: 250 }}
                              />
                            ) : (
                              <Table.Summary.Cell align="right">
                                {formatNumber(orderDetail?.amount_ppn, "Rp ")}
                              </Table.Summary.Cell>
                            )}
                            <Table.Summary.Cell />
                          </Table.Summary.Row>

                          <Table.Summary.Row>
                            <Table.Summary.Cell colSpan={7} align="right">
                              Total
                            </Table.Summary.Cell>
                            {productLoading ? (
                              <Skeleton.Input
                                active
                                size={"default"}
                                block={false}
                                style={{ width: 250 }}
                              />
                            ) : (
                              <Table.Summary.Cell align="right">
                                {formatNumber(orderDetail?.total, "Rp ")}
                              </Table.Summary.Cell>
                            )}

                            <Table.Summary.Cell />
                          </Table.Summary.Row>
                        </>
                      )
                    }
                  }}
                />
              </div>
            </div>
            <div className="card">
              <div className="card-header flex justify-between items-center">
                <h1 className="header-title">Negotiation</h1>
              </div>
              <div className="card-body">
                <Table
                  dataSource={negotiationsData}
                  columns={negotiationsColumns}
                  loading={loading}
                  pagination={false}
                  rowKey="id"
                  scroll={{ x: "max-content" }}
                  tableLayout={"auto"}
                />
              </div>
            </div>
          </TabPane>
          <TabPane tab="Stage 3: Summary Leads" key={3}>
            <div className="card">
              <div className="card-header flex justify-between items-center">
                <h1 className="header-title">Lead Activity</h1>
              </div>
              <div className="card-body">
                <Table
                  scroll={{ x: "max-content" }}
                  tableLayout={"auto"}
                  dataSource={activityData}
                  columns={[
                    ...activityColumns,
                    {
                      title: "Action",
                      key: "id",
                      align: "center",
                      fixed: "right",
                      width: 100,
                      render: (text, record) => (
                        <div className="flex justify-between items-center">
                          <ModalActivity
                            refetch={() => loadDetailOrderLead()}
                            detail={{ uid_lead }}
                            initialValues={record}
                            update
                          />
                          <Popconfirm
                            title="Yakin Hapus Data Ini?"
                            onConfirm={() => handleDeleteActivity(record.id)}
                            okText="Yes"
                            cancelText="No"
                          >
                            <button
                              className="ml-2 text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
                              title="Delete"
                            >
                              <DeleteOutlined />
                            </button>
                          </Popconfirm>
                        </div>
                      ),
                    },
                  ]}
                  loading={loading}
                  pagination={false}
                  rowKey="id"
                />
              </div>
            </div>
            <div className="card-body">
              <ProductList
                data={productNeed}
                products={products}
                taxs={taxs}
                discounts={discounts}
                onChange={handleChangeProductPrice}
                handleChange={handleChangeProductItem}
                handleClick={handleClickProductItem}
                loading={productLoading}
                disabled={
                  orderDetail?.status > "0" &&
                  orderDetail?.status !== "9" &&
                  orderDetail?.status !== "6"
                }
              />
            </div>
            <div className="card">
              <div className="card-header flex justify-between items-center">
                <h1 className="header-title">Negotiation</h1>
              </div>
              <div className="card-body">
                <Table
                  dataSource={negotiationsData}
                  columns={negotiationsColumns}
                  loading={loading}
                  pagination={false}
                  rowKey="id"
                  scroll={{ x: "max-content" }}
                  tableLayout={"auto"}
                />
              </div>
            </div>
          </TabPane>
        </Tabs>

        <RenderIf
          isTrue={
            (role !== "sales" && orderDetail?.status === "2") ||
            orderDetail?.status === "0"
          }
        >
          <div className="card ">
            <div className="card-body flex justify-end">
              {orderDetail?.status === "0" && activeTab == 2 && (
                <button
                  onClick={() => handleAction("save-negotiation")}
                  className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-4"
                >
                  <CheckOutlined />
                  <span className="ml-2">Simpan</span>
                </button>
              )}
              {role !== "sales" && orderDetail?.status === "2" && (
                <>
                  {show && (
                    <ModalReject
                      handleSubmit={(params) => handleAction("reject", params)}
                    />
                  )}
                  {show && (
                    <button
                      onClick={() => handleAction("approve")}
                      className="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-4"
                    >
                      <CheckOutlined />
                      <span className="ml-2">Approve</span>
                    </button>
                  )}
                </>
              )}
            </div>
          </div>
        </RenderIf>
      </>
    </Layout>
  )
}

export default LeadMasterDetail
