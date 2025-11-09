import {
  CreditCardOutlined,
  FileExcelOutlined,
  LoadingOutlined,
  PrinterTwoTone,
} from "@ant-design/icons"
import { Dropdown, Form, Menu, Steps, Table } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { useParams } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../components/layout"
import LoadingFallback from "../../components/LoadingFallback"
import ModalInputResi from "../../components/Modal/ModalInputResi"
import ModalOngkosKirim from "../../components/Modal/ModalOngkosKirim"
import UpdateUniqueCode from "../../components/UpdateUniqueCode"
import { formatNumber, getItem, inArray, RenderIf } from "../../helpers"
import ModalBilling from "./Components/ModalBilling"
import OrderDetailInfo from "./Components/OrderDetailInfo"
import { billingColumns, productNeedListColumn } from "./config"

const { Step } = Steps

const SalesReturnDetail = () => {
  const [form] = Form.useForm()
  const { uid_retur } = useParams()
  const [orderDetail, setDetailOrder] = useState(null)
  const userData = getItem("user_data", true)
  const [productNeed, setProductNeed] = useState([])
  const [billingData, setBilingData] = useState([])
  const [activityData, setActivityData] = useState([])
  const [loadingExport, setLoadingExport] = useState(false)

  const [loading, setLoading] = useState(false)
  const loadDetailOrderLead = () => {
    setLoading(true)
    axios.get(`/api/order/sales-return/detail/${uid_retur}`).then((res) => {
      const { data } = res.data
      setDetailOrder(data)

      const dataBillings = data?.billings?.map((item) => {
        return {
          id: item.id,
          account_name: item.account_name,
          account_bank: item.account_bank,
          total_transfer: item.total_transfer,
          transfer_date: item.transfer_date,
          upload_billing_photo: item.upload_billing_photo_url,
          upload_transfer_photo: item.upload_transfer_photo_url,
          status: item.status,
          approved_by_name: item.approved_by_name,
          approved_at: item.approved_at || "-",
          payment_number: item.payment_number || "-",
        }
      })
      const productNeeds =
        data.return_items &&
        data.return_items.map((item) => {
          let newData = {
            id: data.id,
            product: item?.product?.name,
            price: formatNumber(item?.price),
            qty: item?.qty,
            total_price: formatNumber(item?.total),
            discount: item?.discount_amount,
            tax: item?.tax_amount,
          }

          return newData
        })
      setProductNeed(productNeeds)
      setBilingData(dataBillings)
      setActivityData(data?.lead_activities)
      setLoading(false)
    })
  }

  useEffect(() => {
    loadDetailOrderLead()
  }, [])

  const actionReturn = (type, status) => {
    axios
      .post(`/api/order/sales-return/${type}`, { uid_retur, status })
      .then((res) => {
        const { message } = res.data
        loadDetailOrderLead()
        toast.success(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
      .catch((err) => {
        const { message } = err.response.data
        toast.error(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }

  const onFinish = (values) => {
    axios
      .post(`/api/order/sales-return/save-resi`, { ...values, uid_retur })
      .then((res) => {
        const { message } = res.data
        loadDetailOrderLead()
        toast.success(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
      .catch((err) => {
        const { message } = err.response.data
        toast.error(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }

  const handleVerifyBilling = (value, status) => {
    const statusNote = status > 1 ? "Reject" : "Approve"
    axios
      .post(`/api/order/sales-return/billing/verify`, { status, ...value })
      .then((res) => {
        loadDetailOrderLead()
        message.success(statusNote + " Billing Success")
      })
      .catch((err) => {
        message.error(statusNote + " Billing Failed")
      })
  }

  const summaries = [
    {
      label: "Sub Total",
      value: formatNumber(parseInt(orderDetail?.subtotal)),
    },
    {
      label: "Kode Unik",
      value: orderDetail?.kode_unik,
    },
    {
      label: "Ongkir",
      value: orderDetail?.ongkir,
    },
    {
      label: "Tax Total",
      value: formatNumber(parseInt(orderDetail?.tax_amount)),
    },
    {
      label: "Diskon",
      value: formatNumber(parseInt(orderDetail?.discount_amount)),
    },
    {
      label: "Total",
      value: formatNumber(parseInt(orderDetail?.amount)),
    },
  ]

  const SummaryItem = ({ item, disabled = false }) => {
    return (
      <Table.Summary.Row>
        <Table.Summary.Cell index={0}></Table.Summary.Cell>
        <Table.Summary.Cell index={1}></Table.Summary.Cell>
        <Table.Summary.Cell index={2}></Table.Summary.Cell>
        <Table.Summary.Cell index={3}></Table.Summary.Cell>
        <RenderIf isTrue={item.label === "Kode Unik"}>
          <Table.Summary.Cell index={4}>
            <UpdateUniqueCode
              item={item}
              order={orderDetail}
              refetch={loadDetailOrderLead}
              url={"/api/order/sales-return/update/kode-unik"}
              disabled={disabled}
              field={"uid_retur"}
            />
          </Table.Summary.Cell>
        </RenderIf>
        <RenderIf isTrue={item.label === "Ongkir"}>
          <Table.Summary.Cell index={5}>
            <ModalOngkosKirim
              disabled={disabled}
              initialValues={{
                ongkir: orderDetail.ongkir,
              }}
              refetch={loadDetailOrderLead}
              url={`/api/order/sales-return/update/ongkir/${orderDetail.uid_retur}`}
            />
          </Table.Summary.Cell>
        </RenderIf>
        <RenderIf isTrue={!inArray(item.label, ["Kode Unik", "Ongkir"])}>
          <Table.Summary.Cell index={6}>
            <strong>{item.label}</strong>
          </Table.Summary.Cell>
        </RenderIf>
        <Table.Summary.Cell index={7}>{item.value}</Table.Summary.Cell>
      </Table.Summary.Row>
    )
  }

  const role = getItem("role")
  const isWarehouse = role === "warehouse"
  const isAdminSales = role === "adminsales"
  const isLeadWh = role === "leadwh"
  const isSuperAdmin = role === "superadmin"
  const superadmin = isSuperAdmin || isAdminSales || isLeadWh

  const show = !inArray(role, [
    "adminsales",
    "leadwh",
    "leadsales",
    "leadcs",
    "warehouse",
  ])

  const handleExportContent = () => {
    setLoadingExport(true)
    axios
      .post(`/api/sales-return/export/detail/${uid_retur}`)
      .then((res) => {
        const { data } = res.data
        window.open(data)
        setLoadingExport(false)
      })
      .catch((e) => setLoadingExport(false))
  }

  const rightContent = (
    <div className="flex justify-between items-center">
      <button
        className="ml-3 text-white bg-green-800 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mr-2"
        onClick={() => handleExportContent()}
      >
        {loadingExport ? <LoadingOutlined /> : <FileExcelOutlined />}
        <span className="ml-2">Export</span>
      </button>
    </div>
  )

  if (loading) {
    return (
      <Layout
        title="Detail"
        rightContent={rightContent}
        href="/order/sales-return"
      >
        <LoadingFallback />
      </Layout>
    )
  }

  const billingActionColumn = [
    {
      title: "Action",
      dataIndex: "action",
      key: "action",
      render: (text, record, index) => {
        if (record.status == 0) {
          if (orderDetail.amount_billing_approved > 0) {
            if (orderDetail.amount_billing_approved < orderDetail.amount) {
              return "-"
            }
          }
        }

        if (record.status == 2) {
          return (
            <div className="flex items-center justify-around">
              <button
                className="text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
                title="Approve"
              >
                Rejected
              </button>
            </div>
          )
        }
        if (record.status == 1) {
          return (
            <div className="flex items-center justify-around">
              <button
                className="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
                title="Approve"
              >
                Approved
              </button>
            </div>
          )
        }
        if (!show) return null
        return (
          <div className="flex items-center justify-around">
            <ModalBillingReject
              handleClick={(value) =>
                handleVerifyBilling({ id: record.id, ...value }, 2)
              }
              user={userData}
            />
            <button
              onClick={() =>
                handleVerifyBilling(
                  {
                    id: record.id,
                    deposite: orderDetail.amount_deposite,
                    billing_approved: orderDetail.amount_billing_approved,
                    amount: orderDetail.amount,
                  },
                  1
                )
              }
              className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
              title="Approve"
            >
              <CheckOutlined />
            </button>
          </div>
        )
      },
    },
  ]

  return (
    <Layout
      title="Detail"
      rightContent={rightContent}
      href="/order/sales-return"
    >
      <Steps
        size="small"
        current={parseInt(orderDetail?.status)}
        style={{ marginBottom: 16, width: "90%" }}
      >
        <Step title="Draft" />
        <Step title="Verified" />
        <Step title="Delivery" />
        <Step title="To Invoice" />
        <Step title="Completed" />
      </Steps>

      {/* Draft */}
      <RenderIf isTrue={orderDetail?.status === "0"}>
        <div>
          <OrderDetailInfo order={orderDetail} />

          <div className="card">
            <div className="card-header">
              <h1 className="header-titl">Informasi Pengiriman</h1>
            </div>
            <div className="card-body">
              <table className="mb-4">
                <tbody>
                  <tr>
                    <td style={{ width: "20%" }} className="text-bold">
                      Order No
                    </td>
                    <td>: {orderDetail?.order_number}</td>
                  </tr>
                  <tr>
                    <td className="text-bold">Alamat</td>
                    <td>: {orderDetail?.shipping_address || "-"}</td>
                  </tr>
                </tbody>
              </table>
              <Table
                scroll={{ x: "max-content" }}
                tableLayout={"auto"}
                className="mb-4"
                dataSource={productNeed}
                columns={productNeedListColumn}
                loading={loading}
                pagination={false}
                rowKey="id"
                summary={() => {
                  if (productNeed.length > 0) {
                    return (
                      <>
                        {summaries.map((item, index) => (
                          <SummaryItem item={item} key={index} />
                        ))}
                      </>
                    )
                  }

                  return null
                }}
              />
            </div>
          </div>

          {/* submit */}
          {superadmin && (
            <div className="float-right">
              {orderDetail?.status < 2 && (
                <button
                  className="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2 "
                  onClick={() => actionReturn("cancel", 5)}
                >
                  Cancel
                </button>
              )}

              {orderDetail?.status == 0 && (
                <button
                  className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2 "
                  onClick={() => actionReturn("assign-warehouse", 1)}
                >
                  Assign to Warehouse
                </button>
              )}
            </div>
          )}
        </div>
      </RenderIf>

      {/* Verified */}
      <RenderIf isTrue={orderDetail?.status === "1"}>
        {" "}
        <div>
          <OrderDetailInfo order={orderDetail} />
          <div className="card">
            <div className="card-header flex justify-between items-center">
              <h1 className="header-titl">Informasi Pengiriman</h1>
              {isWarehouse && (
                <ModalInputResi
                  onFinish={onFinish}
                  initialValues={orderDetail?.retur_resi}
                />
              )}
            </div>
            <div className="card-body">
              <table className="mb-4">
                <tbody>
                  <tr>
                    <td style={{ width: "20%" }} className="text-bold">
                      Order No
                    </td>
                    <td>: {orderDetail?.order_number}</td>
                  </tr>
                  <tr>
                    <td className="text-bold">Alamat</td>
                    <td>: {orderDetail?.shipping_address || "-"}</td>
                  </tr>
                  <tr>
                    <td className="text-bold">Warehouse</td>
                    <td>: {orderDetail?.warehouse_address || "-"}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div className="card">
            <div className="card-header">
              <h1 className="header-titl">Detail Product</h1>
            </div>
            <div className="card-body">
              <div className="mt-4">
                <Table
                  scroll={{ x: "max-content" }}
                  tableLayout={"auto"}
                  dataSource={productNeed}
                  columns={productNeedListColumn}
                  loading={loading}
                  pagination={false}
                  rowKey="id"
                  summary={() => {
                    if (productNeed.length > 0) {
                      return (
                        <>
                          {summaries.map((item, index) => (
                            <SummaryItem item={item} key={index} />
                          ))}
                        </>
                      )
                    }

                    return null
                  }}
                />
              </div>
            </div>
          </div>
        </div>
      </RenderIf>

      {/* Delivery */}
      <RenderIf isTrue={orderDetail?.status === "2"}>
        <div>
          <OrderDetailInfo order={orderDetail} />
          <div className="card">
            <div className="card-header">
              <h1 className="header-titl">Informasi Pengiriman</h1>
            </div>
            <div className="card-body">
              <table className="mb-4">
                <tbody>
                  <tr>
                    <td className="w-32 md:w-56">Order No</td>
                    <td className="w-4">:</td>
                    <td>{orderDetail?.order_number}</td>
                  </tr>
                  <tr>
                    <td className="w-32 md:w-56">Alamat</td>
                    <td className="w-4">:</td>
                    <td>{orderDetail?.shipping_address || "-"}</td>
                  </tr>
                  <tr>
                    <td className="w-32 md:w-56">Ekspedisi</td>
                    <td className="w-4">:</td>
                    <td>{orderDetail?.retur_resi?.expedition_name || "-"}</td>
                  </tr>
                  <tr>
                    <td className="w-32 md:w-56">Resi</td>
                    <td className="w-4">:</td>
                    <td>{orderDetail?.retur_resi?.resi || "-"}</td>
                  </tr>
                  <tr>
                    <td className="w-32 md:w-56">Nama Pengirim</td>
                    <td className="w-4">:</td>
                    <td>{orderDetail?.retur_resi?.sender_name || "-"}</td>
                  </tr>
                  <tr>
                    <td className="w-32 md:w-56">Telepon Pengirim</td>
                    <td className="w-4">:</td>
                    <td>{orderDetail?.retur_resi?.sender_phone || "-"}</td>
                  </tr>
                </tbody>
              </table>
              <div className="mt-4">
                <Table
                  scroll={{ x: "max-content" }}
                  tableLayout={"auto"}
                  dataSource={productNeed}
                  columns={productNeedListColumn}
                  loading={loading}
                  pagination={false}
                  rowKey="id"
                  summary={() => {
                    if (productNeed.length > 0) {
                      return (
                        <>
                          {summaries.map((item, index) => (
                            <SummaryItem item={item} key={index} />
                          ))}
                        </>
                      )
                    }

                    return null
                  }}
                />
              </div>
            </div>
          </div>

          {isAdminSales && (
            <div className="card">
              <div className="card-body">
                <div className="flex justify-between items-center">
                  <button
                    className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
                    onClick={() => actionReturn("payment-proccess", 3)}
                  >
                    <CreditCardOutlined />
                    <span className="ml-2">Payment Proccess</span>
                  </button>
                </div>
              </div>
            </div>
          )}

          {isLeadWh && (
            <div className="card">
              <div className="card-body">
                <div className="flex justify-between items-center">
                  <button
                    className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
                    onClick={() => actionReturn("payment-proccess", 3)}
                  >
                    <CreditCardOutlined />
                    <span className="ml-2">Payment Proccess</span>
                  </button>
                </div>
              </div>
            </div>
          )}
        </div>
      </RenderIf>

      {/* To Invoice */}
      <RenderIf isTrue={orderDetail?.status === "3"}>
        {" "}
        <div>
          {/* order detail info */}
          <OrderDetailInfo order={orderDetail} />

          {/* informasi pengiriman */}
          <div className="card">
            <div className="card-header">
              <h1 className="header-titl">Informasi Pengiriman</h1>
            </div>
            <div className="card-body">
              <table className="mb-4">
                <tbody>
                  <tr>
                    <td className="w-32 md:w-56">Order No</td>
                    <td className="w-4">:</td>
                    <td>{orderDetail?.order_number}</td>
                  </tr>
                  <tr>
                    <td className="w-32 md:w-56">Alamat</td>
                    <td className="w-4">:</td>
                    <td>{orderDetail?.shipping_address || "-"}</td>
                  </tr>
                  <tr>
                    <td className="w-32 md:w-56">Ekspedisi</td>
                    <td className="w-4">:</td>
                    <td>{orderDetail?.retur_resi?.expedition_name || "-"}</td>
                  </tr>
                  <tr>
                    <td className="w-32 md:w-56">Resi</td>
                    <td className="w-4">:</td>
                    <td>{orderDetail?.retur_resi?.resi || "-"}</td>
                  </tr>
                  <tr>
                    <td className="w-32 md:w-56">Nama Pengirim</td>
                    <td className="w-4">:</td>
                    <td>{orderDetail?.retur_resi?.sender_name || "-"}</td>
                  </tr>
                  <tr>
                    <td className="w-32 md:w-56">Telepon Pengirim</td>
                    <td className="w-4">:</td>
                    <td>{orderDetail?.retur_resi?.sender_phone || "-"}</td>
                  </tr>
                </tbody>
              </table>
              <Table
                className="mb-4"
                dataSource={productNeed}
                columns={productNeedListColumn}
                loading={loading}
                pagination={false}
                rowKey="id"
                summary={() => {
                  if (productNeed.length > 0) {
                    return (
                      <>
                        {summaries.map((item, index) => (
                          <SummaryItem item={item} key={index} />
                        ))}
                      </>
                    )
                  }

                  return null
                }}
              />
            </div>
          </div>

          {/* informasi penagihan */}
          <div className="card">
            <div className="card-header flex justify-between items-center">
              <h1 className="header-title">Informasi Penagihan</h1>
              <ModalBilling
                detail={orderDetail}
                refetch={loadDetailOrderLead}
              />
            </div>
            <div className="card-body">
              <Table
                dataSource={billingData}
                columns={[...billingColumns, ...billingActionColumn]}
                loading={loading}
                pagination={false}
                rowKey="id"
                scroll={{ x: "max-content" }}
                tableLayout={"auto"}
              />
            </div>
          </div>
          <div className="card">
            <div className="card-header flex justify-between items-center">
              <h1 className="header-title">Product Detail</h1>
            </div>
            <div className="card-body">
              <Table
                scroll={{ x: "max-content" }}
                tableLayout={"auto"}
                dataSource={productNeed}
                columns={productNeedListColumn}
                loading={loading}
                pagination={false}
                rowKey="id"
                summary={() => {
                  if (productNeed.length > 0) {
                    return (
                      <>
                        {summaries.map((item, index) => (
                          <SummaryItem item={item} key={index} />
                        ))}
                      </>
                    )
                  }

                  return null
                }}
              />
            </div>
          </div>
          {isAdminSales && (
            <button
              className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2 float-right"
              onClick={() => actionReturn("completed", 4)}
            >
              Completed
            </button>
          )}

          {isLeadWh && (
            <button
              className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2 float-right"
              onClick={() => actionReturn("completed", 4)}
            >
              Completed
            </button>
          )}
        </div>
      </RenderIf>

      {/* Completed */}
      <RenderIf isTrue={orderDetail?.status === "4"}>
        <div>
          <div className="card">
            <div className="card-header flex justify-between items-center">
              <h1 className="header-title">Informasi Pengiriman</h1>
            </div>
            <div className="card-body">
              <table>
                <tbody>
                  <tr>
                    <td className="w-32 md:w-56">Order No</td>
                    <td className="w-4">:</td>
                    <td>{orderDetail?.order_number}</td>
                  </tr>
                  <tr>
                    <td className="w-32 md:w-56">Alamat</td>
                    <td className="w-4">:</td>
                    <td>{orderDetail?.shipping_address || "-"}</td>
                  </tr>
                  <tr>
                    <td className="w-32 md:w-56">Ekspedisi</td>
                    <td className="w-4">:</td>
                    <td>{orderDetail?.retur_resi?.expedition_name || "-"}</td>
                  </tr>
                  <tr>
                    <td className="w-32 md:w-56">Resi</td>
                    <td className="w-4">:</td>
                    <td>{orderDetail?.retur_resi?.resi || "-"}</td>
                  </tr>
                  <tr>
                    <td className="w-32 md:w-56">Nama Pengirim</td>
                    <td className="w-4">:</td>
                    <td>{orderDetail?.retur_resi?.sender_name || "-"}</td>
                  </tr>
                  <tr>
                    <td className="w-32 md:w-56">Telepon Pengirim</td>
                    <td className="w-4">:</td>
                    <td>{orderDetail?.retur_resi?.sender_phone || "-"}</td>
                  </tr>

                  <tr>
                    <td className="text-bold">Warehouse</td>
                    <td className="w-4">:</td>
                    <td>{orderDetail?.warehouse_address || "-"}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div className="card">
            <div className="card-header flex justify-between items-center">
              <h1 className="header-title">Product Detail</h1>
            </div>
            <div className="card-body">
              <Table
                scroll={{ x: "max-content" }}
                tableLayout={"auto"}
                dataSource={productNeed}
                columns={productNeedListColumn}
                loading={loading}
                pagination={false}
                rowKey="id"
                summary={() => {
                  if (productNeed.length > 0) {
                    return (
                      <>
                        {summaries.map((item, index) => (
                          <SummaryItem item={item} key={index} disabled />
                        ))}
                      </>
                    )
                  }

                  return null
                }}
              />
            </div>
          </div>
        </div>
      </RenderIf>

      {/* print container */}
      <div className="absolute top-5 right-5">
        <Dropdown.Button
          style={{ borderRadius: 10 }}
          icon={<PrinterTwoTone />}
          overlay={
            <Menu>
              {orderDetail?.status > 1 && (
                <Menu.Item className="flex justify-between items-center">
                  <PrinterTwoTone />{" "}
                  <a
                    // href={printUrl?.si}
                    target="_blank"
                  >
                    <span>Print SI</span>
                  </a>
                </Menu.Item>
              )}
              <Menu.Item className="flex justify-between items-center">
                <PrinterTwoTone />{" "}
                <a
                  // href={printUrl?.so}
                  target="_blank"
                >
                  <span>Print SO</span>
                </a>
              </Menu.Item>
              <Menu.Item className="flex justify-between items-center">
                <PrinterTwoTone />{" "}
                <a
                  // href={printUrl?.sj}
                  target="_blank"
                >
                  <span>Print SJ</span>
                </a>
              </Menu.Item>
            </Menu>
          }
        ></Dropdown.Button>
      </div>
    </Layout>
  )
}

export default SalesReturnDetail
