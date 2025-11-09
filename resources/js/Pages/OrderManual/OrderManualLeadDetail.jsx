import {
  CheckOutlined,
  CloseCircleOutlined,
  DownCircleFilled,
  FileExcelOutlined,
  LoadingOutlined,
  PrinterOutlined,
  RightOutlined,
} from "@ant-design/icons"
import {
  Dropdown,
  Form,
  Input,
  Menu,
  Select,
  Steps,
  Table,
  message,
} from "antd"
import TextArea from "antd/lib/input/TextArea"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { useParams } from "react-router-dom"
import LoadingFallback from "../../components/LoadingFallback"
import ModalBillingOrder from "../../components/Modal/ModalBillingOrder"
import ModalSplitDeliveryOrder from "../../components/Modal/ModalSplitDeliveryOrder"
import Button from "../../components/atoms/Button"
import Layout from "../../components/layout"
import {
  useGetAddressUserQuery,
  useGetTaxQuery,
  useGetUserWarehouseQuery,
} from "../../configs/Redux/Services/generalServices"
import {
  useAssignWarehouseMutation,
  useBillingOrderVerificationMutation,
  useCancelInvoiceDeliveryMutation,
  useGetSalesOrderBillingItemsDetailQuery,
  useGetSalesOrderDeliveryItemsDetailQuery,
  useGetSalesOrderDetailQuery,
  useGetSalesOrderItemsDetailQuery,
  useInsertInvoiceMutation,
  useUpdateOrderNoteMutation,
  useUpdatePICWarehouseMutation,
  useUpdateProductItemMutation,
  useUpdateShippingInfoMutation,
} from "../../configs/Redux/Services/salesOrderService"
import { RenderIf, formatNumber, getItem, inArray } from "../../helpers"
import ContactAddress from "../Contact/ContactAddress"
import ModalBillingReject from "../OrderLead/Components/ModalBillingReject"
import OrderDetailInfo from "./Components/OrderDetailInfo"
import {
  billingColumns,
  orderDeliveryColumns,
  productNeedListColumnDetail,
  productNeedListColumnInvoice,
  trackingListColumn,
} from "./config"
import UpdateUniqueCode from "../../components/UpdateUniqueCode"
import ModalOngkosKirim from "../../components/Modal/ModalOngkosKirim"

const { Step } = Steps

const OrderManualLeadDetail = ({ type }) => {
  const [form] = Form.useForm()
  const params = useParams()
  const userData = getItem("user_data", true)
  // console.log(orderDetail?.ethix_items, "ethix")
  const [productNeed, setProductNeed] = useState([])

  const [notes, setNotes] = useState(null)
  const [loadingExport, setLoadingExport] = useState(false)
  const [selectedRowKeys, setSelectedRowKeys] = useState([])

  const {
    data: orderDetail,
    isLoading: detailSalesOrderLoading,
    isFetching: detailSalesOrderFetching,
    refetch,
  } = useGetSalesOrderDetailQuery(`/api/sales-order/detail/${params.uid_lead}`)

  // delivery
  const {
    data: productNeeds,
    isLoading: productNeedsLoading,
    isFetching: productNeedsFetching,
    refetch: refetchProductNeeds,
  } = useGetSalesOrderItemsDetailQuery(
    `/api/sales-order/items/${params.uid_lead}`
  )
  // billings
  const {
    data: orderBillings,
    isLoading: loadingBilling,
    isFetching: billingFetching,
    refetch: refetchOrderBillings,
  } = useGetSalesOrderBillingItemsDetailQuery(
    `/api/sales-order/billing/${params.uid_lead}`
  )

  const {
    data: orderDeliveries,
    isLoading: orderDeliveryLoading,
    isFetching: orderDeliveryFetching,
    refetch: refetchorderDelivery,
  } = useGetSalesOrderDeliveryItemsDetailQuery(
    `/api/sales-order/delivery/${params.uid_lead}`
  )
  // general
  const { data: taxs } = useGetTaxQuery()
  const { data: warehouse, isLoading: loadingWarehouse } =
    useGetUserWarehouseQuery()
  const {
    data: userAddressList,
    isLoading: loadingAddress,
    refetch: refetchAddress,
  } = useGetAddressUserQuery(orderDetail?.contact)

  // sales order
  const [updateProductItem] = useUpdateProductItemMutation()
  const [assignWarehouse, { isLoading: loadingAssignWarehouse }] =
    useAssignWarehouseMutation()
  const [changePICWarehouse] = useUpdatePICWarehouseMutation()
  const [updateShippingInfo, { isLoading: loadingUpdateShippingInfo }] =
    useUpdateShippingInfoMutation()
  const [insertInvoice, { isLoading: loadingInsertInvoice }] =
    useInsertInvoiceMutation()
  const [billingOrderVerification] = useBillingOrderVerificationMutation()
  const [updateOrderNote] = useUpdateOrderNoteMutation()
  const [cancelInvoiceDelivery, { isLoading: loadingCancelInvoiceDelivery }] =
    useCancelInvoiceDeliveryMutation()

  const loadDetailOrderLead = () => refetch()

  const handleChangeProductItem = ({ dataIndex, value, id }) => {
    updateProductItem({
      url: "/api/general/update-product-need",
      body: {
        value,
        field: dataIndex,
        uid_lead: params.uid_lead,
        item_id: id,
      },
    })
  }

  const handleAssignWarehouse = () => {
    assignWarehouse(
      `/api/order-manual/assign-warehouse/${params.uid_lead}`
    ).then(({ error }) => {
      if (error) {
        return message.error("Packing Proses Gagal")
      }
      loadDetailOrderLead()
      message.success("Packing Proses Success")
    })
  }

  useEffect(() => {
    if (orderDetail) {
      orderDetail?.notes && setNotes(orderDetail?.notes)
    }
    // loadDetailOrderLead()
  }, [orderDetail])

  const handleChangeKurir = (courier) => {
    if (orderDetail?.courier !== courier) {
      changePICWarehouse({
        url: `/api/order-manual/change-courier`,
        body: {
          courier,
          uid_lead: orderDetail?.uid_lead,
        },
      }).then(({ error, data }) => {
        if (error) {
          return message.error("gagal mengubah PIC Warehouse ")
        }
        loadDetailOrderLead()
        return message.success("Berhasil mengubah PIC Warehouse!")
      })
    }
  }

  const updateNotes = () => {
    updateOrderNote({
      url: `/api/general/order/update-notes`,
      body: {
        uid_lead: orderDetail?.uid_lead,
        notes,
        type: "manual",
      },
    }).then(({ error }) => {
      if (error) {
        return message.error("Notes gagal disimpan ")
      }
      loadDetailOrderLead()
      message.success("Notes berhasil disimpan")
    })
  }

  const handleVerifyBilling = (value, status) => {
    const msg = status === 1 ? "Approve" : "Reject"
    billingOrderVerification({
      url: `/api/order-manual/billing/verify`,
      body: { status, ...value },
    }).then(({ error }) => {
      if (error) {
        return message.error(`${msg} Billing Failed`)
      }
      refetchOrderBillings()
      loadDetailOrderLead()
      message.success(`${msg} Billing Success`)
    })
  }

  const onFinishSaveResi = (values) => {
    // split-delivery-order
    updateShippingInfo({
      url: "/api/order-manual/split-delivery-order",
      body: values,
    }).then(({ error }) => {
      if (error) {
        return message.error("Data input pengiriman gagal disimpan!")
      }
      refetchProductNeeds()
      refetchorderDelivery()
      loadDetailOrderLead()
      message.success("Data input pengiriman berhasil disimpan! ")
    })
  }

  const handleInsertInvoice = (id, multiple = false, invoice_id = null) => {
    if (multiple) {
      insertInvoice({
        url: `/api/order-manual/product-need/invoice`,
        body: {
          is_invoice: 1,
          items: selectedRowKeys,
        },
      }).then(({ error }) => {
        if (error) {
          return message.error("Data Invoice gagal Diproses!")
        }
        setSelectedRowKeys([])
        refetchProductNeeds()
        refetchorderDelivery()
        loadDetailOrderLead()
        return message.success("Data Invoice berhasil Diproses!")
      })
    } else {
      insertInvoice({
        url: `/api/order-manual/product-need/invoice/${id}`,
        body: {
          is_invoice: 1,
          invoice_id,
        },
      }).then(({ error }) => {
        if (error) {
          return message.error("Data Invoice gagal Diproses!")
        }
        setSelectedRowKeys([])
        refetchProductNeeds()
        refetchorderDelivery()
        loadDetailOrderLead()
        return message.success("Data Invoice berhasil Diproses!")
      })
    }
  }

  const handleCancelInvoiceDelivery = (invoice_id) => {
    console.log(invoice_id)
    cancelInvoiceDelivery(
      `/api/order-manual/delivery/cancel/${invoice_id}`
    ).then(({ error }) => {
      if (error) {
        console.log(error, "error")
        return message.error("Pengiriman gagal dibatalkan")
      }
      refetchProductNeeds()
      refetchorderDelivery()
      loadDetailOrderLead()
      return message.success("Pengiriman berhasil dibatalkan")
    })
  }
  console.log(orderDeliveries, "orderDeliveries")

  const show = !inArray(getItem("role"), [
    "adminsales",
    "leadwh",
    "leadsales",
    "leadcs",
    "warehouse",
  ])

  const billingActionColumn = [
    {
      title: "Action",
      dataIndex: "action",
      key: "action",
      render: (text, record, index) => {
        if (record.status == 0) {
          if (orderDetail?.amount_billing_approved > 0) {
            if (orderDetail?.amount_billing_approved > orderDetail?.amount) {
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
                    deposite: orderDetail?.amount_deposite,
                    billing_approved: orderDetail?.amount_billing_approved,
                    amount: orderDetail?.amount,
                  },
                  1
                )
              }
              className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
              title="Approve"
            >
              <CheckOutlined />
            </button>
          </div>
        )
      },
    },
  ]

  const productNeedColumns = [
    {
      title: "Tax",
      dataIndex: "tax",
      key: "tax",
      render: (text, record, index) => {
        if (record.disabled) {
          return (
            <Select
              showSearch
              disabled={record.disabled}
              placeholder="Pilih Tax"
              value={record.tax_id}
            >
              {taxs &&
                taxs.map((tax) => (
                  <Select.Option value={tax.id} key={tax.id}>
                    {tax.tax_code}
                  </Select.Option>
                ))}
            </Select>
          )
        }
        const tax_id = parseInt(record.tax_id)
        return (
          <Select
            showSearch
            filterOption={(input, option) => {
              return (option?.children ?? "")
                .toLowerCase()
                .includes(input.toLowerCase())
            }}
            placeholder="Pilih Tax"
            value={isNaN(tax_id) ? null : tax_id}
            onChange={(e) =>
              handleChangeProductItem({
                value: e,
                dataIndex: "tax_id",
                id: record.id,
                index,
              })
            }
          >
            {taxs &&
              taxs.map((tax) => (
                <Select.Option value={tax.id} key={tax.id}>
                  {tax.tax_code}
                </Select.Option>
              ))}
          </Select>
        )
      },
    },
    {
      title: "Disc (Rp) / Qty ",
      dataIndex: "discount",
      key: "discount",
      render: (text, record, index) => {
        if (record.disabled) {
          return (
            <Input
              disabled={record.disabled}
              value={record.discount}
              type={"number"}
            />
          )
        }
        const productNeedData = [...productNeed]
        return (
          <Input
            defaultValue={record.discount}
            type={"number"}
            // onChange={(e) => {
            //   const { value } = e.target
            //   productNeedData[index]["discount"] = value
            //   setProductNeed(productNeedData)
            // }}
            onBlur={(e) => {
              const { value } = e.target
              return handleChangeProductItem({
                dataIndex: "discount",
                value,
                id: record.id,
                index,
              })
            }}
          />
        )
      },
    },
    {
      title: "Harga Jual DPP",
      dataIndex: "final_price",
      key: "final_price",
      render: (text, record) =>
        formatNumber(record?.price - record?.discount_amount, "Rp "),
    },
    {
      title: "Discount (Total)",
      dataIndex: "discount_amount",
      key: "discount_amount",
      render: (text, record) => formatNumber(record?.discount_amount, "Rp "),
    },
    {
      title: "Tax Amount (Total)",
      dataIndex: "tax_amount",
      key: "tax_amount",
      render: (text, record) => formatNumber(record?.tax_amount, "Rp "),
    },
    {
      title: "Subtotal",
      dataIndex: "total",
      key: "total",
      render: (text, record) => {
        const subtotal =
          parseInt(record?.price) - parseInt(record?.discount_amount)
        const total = subtotal + parseInt(record?.tax_amount)
        return formatNumber(total, "Rp ")
      },
    },
  ]

  const handleExportContent = () => {
    setLoadingExport(true)
    axios
      .post(`/api/order-manual/export/detail/${params.uid_lead}`)
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

  const rowSelection = {
    selectedRowKeys,
    onChange: (newSelectedRowKeys) => {
      return setSelectedRowKeys(newSelectedRowKeys)
    },
    getCheckboxProps: (record) => ({
      disabled:
        inArray(record.is_invoice, [1]) || inArray(record?.status, ["cancel"]), // Column configuration not to be checked
    }),
  }

  if (detailSalesOrderLoading || detailSalesOrderFetching) {
    return (
      <Layout
        title="Detail"
        rightContent={rightContent}
        href={`/order/order-${type}`}
      >
        <LoadingFallback />
      </Layout>
    )
  }
  const address = userAddressList?.address?.find((item) => item.is_default == 1)
  return (
    <Layout
      title="Detail"
      rightContent={rightContent}
      href={`/order/order-${type}`}
      lastItemLabel={orderDetail?.order_number}
    >
      <Steps
        size="small"
        current={`${parseInt(orderDetail?.status) + 1}`}
        style={{ marginBottom: 16 }}
      >
        <Step title="Draft" />
        <Step title="New" />
        <Step title="Open" />
        <Step
          status={orderDetail?.status === "4" ? "error" : null}
          title={orderDetail?.status === "4" ? "Canceled" : "Closed"}
        />
      </Steps>

      {/* New */}
      <RenderIf isTrue={inArray(orderDetail?.status, ["1"])}>
        <div>
          <OrderDetailInfo
            order={{ ...orderDetail, order_delivery: orderDeliveries }}
            printUrl={orderDetail?.printUrl}
            refetch={() => loadDetailOrderLead()}
          />

          <ContactAddress
            title="Address Information"
            data={userAddressList?.address || []}
            loading={loadingAddress}
            contact={{
              id: userAddressList?.id,
              name: userAddressList?.name,
              email: userAddressList?.email,
              telepon: userAddressList?.phone,
            }}
            refetch={() => refetchAddress()}
          />

          <div className="card">
            <div className="card-header flex justify-between items-center">
              <h1 className="header-titl">Informasi Produk</h1>
              {/* <RenderIf isTrue={userData?.role.role_type !== "sales"}>
                <ModalInputResi
                  hasInputed={order_shipping}
                  onFinish={(values) => onFinishSaveResi(values)}
                  initialValues={order_shipping}
                  fields={{ uid_lead: orderDetail?.uid_lead }}
                />
              </RenderIf> */}
            </div>
            <div className="card-body">
              {/* <table>
                <tbody>
                  <tr>
                    <td className="w-32 md:w-56">Order No</td>
                    <td className="w-4">:</td>
                    <td>{orderDetail?.order_number}</td>
                  </tr>
                  <tr>
                    <td className="w-32 md:w-56">Alamat</td>
                    <td className="w-4">:</td>
                    <td>{address?.alamat_detail}</td>
                  </tr>
                </tbody>
              </table> */}
              <Table
                scroll={{ x: "max-content" }}
                tableLayout={"auto"}
                className="mb-4"
                dataSource={productNeeds}
                columns={[
                  ...productNeedListColumnDetail,
                  ...productNeedColumns,
                ]}
                loading={productNeedsLoading || productNeedsFetching}
                pagination={false}
                rowKey="id"
                summary={(productNeed) => {
                  if (productNeed && productNeed.length > 0) {
                    return (
                      <Table.Summary>
                        <Table.Summary.Row>
                          <Table.Summary.Cell align="right" colSpan={8}>
                            <strong>Subtotal (Sebelum Diskon) :</strong>
                          </Table.Summary.Cell>
                          <Table.Summary.Cell align="left" colSpan={1}>
                            <strong>
                              Rp. {formatNumber(orderDetail?.subtotal)}
                            </strong>
                          </Table.Summary.Cell>
                          <Table.Summary.Cell />
                        </Table.Summary.Row>
                        <Table.Summary.Row>
                          <Table.Summary.Cell align="right" colSpan={8}>
                            <strong>Discount :</strong>
                          </Table.Summary.Cell>
                          <Table.Summary.Cell align="left" colSpan={1}>
                            <strong>
                              Rp. {formatNumber(orderDetail?.diskon)}
                            </strong>
                          </Table.Summary.Cell>
                          <Table.Summary.Cell />
                        </Table.Summary.Row>
                        <Table.Summary.Row>
                          <Table.Summary.Cell align="right" colSpan={8}>
                            <strong>DPP :</strong>
                          </Table.Summary.Cell>
                          <Table.Summary.Cell align="left" colSpan={1}>
                            <strong>
                              Rp. {formatNumber(orderDetail?.dpp)}
                            </strong>
                          </Table.Summary.Cell>
                          <Table.Summary.Cell />
                        </Table.Summary.Row>
                        <Table.Summary.Row>
                          <Table.Summary.Cell align="right" colSpan={8}>
                            <strong>PPN :</strong>
                          </Table.Summary.Cell>
                          <Table.Summary.Cell align="left" colSpan={1}>
                            <strong>
                              Rp. {formatNumber(orderDetail?.ppn)}
                            </strong>
                          </Table.Summary.Cell>
                          <Table.Summary.Cell />
                        </Table.Summary.Row>
                        <Table.Summary.Row>
                          <Table.Summary.Cell align="right" colSpan={8}>
                            <strong>Kode Unik :</strong>
                          </Table.Summary.Cell>
                          <Table.Summary.Cell align="left" colSpan={1}>
                            <UpdateUniqueCode
                              item={{
                                label: orderDetail?.kode_unik,
                                value: orderDetail?.kode_unik || 0,
                              }}
                              order={orderDetail}
                              refetch={() => loadDetailOrderLead()}
                              url={"/api/order-manual/update/kode-unik"}
                              disabled={false}
                            />
                          </Table.Summary.Cell>
                          <Table.Summary.Cell />
                        </Table.Summary.Row>
                        <Table.Summary.Row>
                          <Table.Summary.Cell align="right" colSpan={8}>
                            <strong>Ongkir :</strong>
                          </Table.Summary.Cell>
                          <Table.Summary.Cell align="left" colSpan={1}>
                            <ModalOngkosKirim
                              disabled={false}
                              initialValues={{
                                ongkir: orderDetail?.ongkir,
                              }}
                              refetch={() => loadDetailOrderLead()}
                              url={`/api/order-manual/update/ongkir/${orderDetail?.uid_lead}`}
                            />
                          </Table.Summary.Cell>
                          <Table.Summary.Cell />
                        </Table.Summary.Row>

                        <Table.Summary.Row>
                          <Table.Summary.Cell align="right" colSpan={8}>
                            <strong>Total Amount :</strong>
                          </Table.Summary.Cell>
                          <Table.Summary.Cell align="left" colSpan={1}>
                            <strong>
                              Rp. {formatNumber(orderDetail?.total)}
                            </strong>
                          </Table.Summary.Cell>
                          <Table.Summary.Cell />
                        </Table.Summary.Row>
                      </Table.Summary>
                    )
                  }

                  return null
                }}
              />

              <RenderIf
                isTrue={
                  userData?.role.role_type !== "sales" &&
                  (userData?.role.role_type === "adminsales" ||
                    userData?.role.role_type === "adminwarehouse" ||
                    userData?.role.role_type === "leadwh" ||
                    userData?.role.role_type === "leadsales" ||
                    userData?.role.role_type === "superadmin" ||
                    userData?.role.role_type === "finance" ||
                    userData?.role.role_type === "lead_finance")
                }
              >
                <p>
                  Silahkan pilih PIC Warehouse untuk pengiriman sales order
                  dibawah ini:
                </p>
                <div>
                  <label htmlFor="" className="text-bold mb-2">
                    PIC Warehouse
                  </label>
                  <Select
                    loading={loadingWarehouse}
                    allowClear
                    className="w-full mb-2"
                    placeholder="Pilih PIC Warehouse"
                    onChange={(e) => handleChangeKurir(e)}
                    value={orderDetail?.courier}
                  >
                    {warehouse &&
                      warehouse.map((item) => (
                        <Select.Option key={item.id} value={item.id}>
                          {item?.name}
                        </Select.Option>
                      ))}
                  </Select>
                  <small>
                    <i>
                      Anda dapat melakukan perubahan saat data belum masuk ke
                      dalam proses Packing Proses
                    </i>
                  </small>
                </div>
              </RenderIf>
            </div>
          </div>

          {/* informasi pengiriman */}
          <div className="card">
            <div className="card-header flex justify-between items-center">
              <h1 className="header-titl">Informasi Pengiriman</h1>
              <RenderIf isTrue={userData?.role.role_type !== "sales"}>
                <ModalSplitDeliveryOrder
                  onFinish={(values) => onFinishSaveResi(values)}
                  fields={{ uid_lead: orderDetail?.uid_lead }}
                  products={productNeeds}
                />
              </RenderIf>
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
                    <td>Tipe Pengiriman</td>
                    <td>:</td>
                    <td>Normal</td>
                  </tr>
                  <tr>
                    <td>Alamat</td>
                    <td>:</td>
                    <td>{address?.alamat_detail}</td>
                  </tr>
                </tbody>
              </table>
              <Table
                scroll={{ x: "max-content" }}
                tableLayout={"auto"}
                className="mb-4"
                dataSource={orderDeliveries}
                columns={[
                  ...orderDeliveryColumns,
                  {
                    title: "Action",
                    key: "id",
                    align: "center",
                    fixed: "right",
                    width: 100,
                    render: (text, record) => (
                      <Dropdown.Button
                        style={{
                          width: 90,
                        }}
                        overlay={
                          <Menu
                            onClick={({ key }) => {
                              if (key === "print") {
                                return window.open(
                                  `/print/sj/${record.uid_lead}/${record.id}`
                                )
                              }

                              if (key === "print_si") {
                                return window.open(
                                  `/print/si/${record.uid_lead}/${record.id}`
                                )
                              }

                              if (key === "cancel") {
                                return handleCancelInvoiceDelivery(record.id)
                              }
                            }}
                            itemIcon={<RightOutlined className="ml-8" />}
                            items={[
                              {
                                label: "Print SJ",
                                key: "print",
                                icon: <PrinterOutlined />,
                                disabled: record?.status === "cancel",
                              },
                              // {
                              //   label: "Print SI",
                              //   key: "print_si",
                              //   icon: <PrinterOutlined />,
                              //   disabled:
                              //     record?.status === "cancel" ||
                              //     record?.is_invoice != 1,
                              // },
                              {
                                label: "Cancel",
                                key: "cancel",
                                icon: <CloseCircleOutlined />,
                                disabled: record?.status === "cancel",
                              },
                            ]}
                            // onContextMenu={(e) => {
                            //   console.log(e, "context menu");
                            //   console.log("Right Click", e.pageX, e.pageY);
                            // }}
                          />
                        }
                      ></Dropdown.Button>
                    ),
                  },
                ]}
                loading={
                  detailSalesOrderLoading ||
                  loadingCancelInvoiceDelivery ||
                  orderDeliveryLoading ||
                  orderDeliveryFetching
                }
                pagination={false}
                rowKey="id"
              />
            </div>
          </div>

          {/* <div className="card p-4">
            <Card title={"Ethix"}>
              <div className="row">
                <div className="col-md-12 mt-4">
                  <Table
                    dataSource={orderDetail?.ethix_items || []}
                    columns={ethixColumns}
                    loading={detailSalesOrderLoading}
                    pagination={false}
                    rowKey="id"
                    scroll={{ x: "max-content" }}
                    tableLayout={"auto"}
                  />
                </div>
              </div>
            </Card>
          </div> */}

          <RenderIf
            isTrue={
              userData?.role.role_type !== "sales" &&
              (userData?.role.role_type === "adminsales" ||
                userData?.role.role_type === "adminwarehouse" ||
                userData?.role.role_type === "leadwh" ||
                userData?.role.role_type === "leadsales" ||
                userData?.role.role_type === "superadmin" ||
                userData?.role.role_type === "finance" ||
                userData?.role.role_type === "lead_finance")
            }
          >
            <div className="card">
              <div className="card-body">
                <p>Notes</p>
                <TextArea
                  // autoSize={{
                  //   minRows: 2,
                  //   maxRows: 6,
                  // }}
                  placeholder="notes"
                  value={notes}
                  onChange={(e) => setNotes(e.target.value)}
                  onBlur={updateNotes}
                />
              </div>
            </div>
          </RenderIf>

          {/* payment info */}
          {/* <PaymentDetail order={orderDetail} /> */}

          {/* submit */}
          {orderDetail?.status == 1 && (
            <RenderIf
              isTrue={
                userData?.role.role_type !== "sales" &&
                (userData?.role.role_type === "adminsales" ||
                  userData?.role.role_type === "adminwarehouse" ||
                  userData?.role.role_type === "leadwh" ||
                  userData?.role.role_type === "leadsales" ||
                  userData?.role.role_type === "superadmin" ||
                  userData?.role.role_type === "finance" ||
                  userData?.role.role_type === "lead_finance")
              }
            >
              <button
                className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2 float-right"
                onClick={() => {
                  if (loadingAssignWarehouse) {
                    return null
                  }

                  if (!orderDetail?.courier) {
                    return message.error(
                      "Mohon Pilih PIC Warehouse Terlebih Dahulu"
                    )
                  }

                  return handleAssignWarehouse()
                }}
                disabled={loadingAssignWarehouse}
              >
                {loadingAssignWarehouse && <LoadingOutlined />}
                Packing Proses
              </button>
            </RenderIf>
          )}
        </div>
      </RenderIf>

      {/* Open */}
      <RenderIf isTrue={orderDetail?.status === "2"}>
        <div>
          <OrderDetailInfo
            order={{ ...orderDetail, order_delivery: orderDeliveries }}
            printUrl={orderDetail?.printUrl}
            refetch={() => loadDetailOrderLead()}
          />
          <div className="card">
            <div className="card-header flex justify-between items-center">
              <h1 className="header-titl">Informasi Produk</h1>
              {/* <RenderIf isTrue={userData?.role.role_type !== "sales"}>
                <ModalInputResi
                  hasInputed={order_shipping}
                  onFinish={(values) => onFinishSaveResi(values)}
                  initialValues={order_shipping}
                  fields={{ uid_lead: orderDetail?.uid_lead }}
                />
              </RenderIf> */}
            </div>
            <div className="card-body">
              {/* <table>
                <tbody>
                  <tr>
                    <td style={{ width: "20%" }} className="text-bold">
                      Order No
                    </td>
                    <td>: {orderDetail?.order_number}</td>
                  </tr>
                  <tr>
                    <td className="text-bold">Tipe Pengiriman</td>
                    <td>: Normal</td>
                  </tr>
                  <tr>
                    <td className="text-bold">Alamat</td>
                    <td>: {address?.alamat_detail || "-"}</td>
                  </tr>
                  {order_shipping && (
                    <>
                      <tr>
                        <td>Pengirim</td>
                        <td>: {order_shipping?.sender_name}</td>
                      </tr>
                      <tr>
                        <td>Telfon Pengirim</td>
                        <td>: {order_shipping?.sender_phone}</td>
                      </tr>
                      <tr>
                        <td>Nama Ekspedisi</td>
                        <td>: {order_shipping?.expedition_name}</td>
                      </tr>
                      <tr>
                        <td>Resi</td>
                        <td>: {order_shipping?.resi}</td>
                      </tr>
                      {order_shipping?.attachment_url?.length > 0 && (
                        <tr>
                          <td>Attachment</td>
                          <td>
                            <span>: </span>
                            <a href={order_shipping?.attachment_url[0]}>
                              <LinkOutlined />
                              <span>Attachment 1</span>
                            </a>
                          </td>
                        </tr>
                      )}
                      {order_shipping?.attachment_url?.map((item, index) => {
                        if (index > 0) {
                          return (
                            <tr key={index}>
                              <td></td>
                              <td>
                                <span>: </span>
                                <a href={item}>
                                  <LinkOutlined />
                                  <span>Attachment {index + 1}</span>
                                </a>
                              </td>
                            </tr>
                          )
                        }
                      })}
                    </>
                  )}
                </tbody>
              </table> */}
              <div className="mt-4">
                <Table
                  scroll={{ x: "max-content" }}
                  tableLayout={"auto"}
                  dataSource={productNeeds}
                  columns={[
                    ...productNeedListColumnDetail,
                    ...productNeedColumns,
                  ]}
                  loading={detailSalesOrderLoading}
                  pagination={false}
                  rowKey="id"
                  summary={(productNeed) => {
                    if (productNeed && productNeed.length > 0) {
                      return (
                        <Table.Summary>
                          <Table.Summary.Row>
                            <Table.Summary.Cell align="right" colSpan={8}>
                              <strong>Subtotal (Sebelum Diskon) :</strong>
                            </Table.Summary.Cell>
                            <Table.Summary.Cell align="left" colSpan={1}>
                              <strong>
                                Rp. {formatNumber(orderDetail?.subtotal)}
                              </strong>
                            </Table.Summary.Cell>
                            <Table.Summary.Cell />
                          </Table.Summary.Row>
                          <Table.Summary.Row>
                            <Table.Summary.Cell align="right" colSpan={8}>
                              <strong>Discount :</strong>
                            </Table.Summary.Cell>
                            <Table.Summary.Cell align="left" colSpan={1}>
                              <strong>
                                Rp. {formatNumber(orderDetail?.diskon)}
                              </strong>
                            </Table.Summary.Cell>
                            <Table.Summary.Cell />
                          </Table.Summary.Row>
                          <Table.Summary.Row>
                            <Table.Summary.Cell align="right" colSpan={8}>
                              <strong>DPP :</strong>
                            </Table.Summary.Cell>
                            <Table.Summary.Cell align="left" colSpan={1}>
                              <strong>
                                Rp. {formatNumber(orderDetail?.dpp)}
                              </strong>
                            </Table.Summary.Cell>
                            <Table.Summary.Cell />
                          </Table.Summary.Row>
                          <Table.Summary.Row>
                            <Table.Summary.Cell align="right" colSpan={8}>
                              <strong>PPN :</strong>
                            </Table.Summary.Cell>
                            <Table.Summary.Cell align="left" colSpan={1}>
                              <strong>
                                Rp. {formatNumber(orderDetail?.ppn)}
                              </strong>
                            </Table.Summary.Cell>
                            <Table.Summary.Cell />
                          </Table.Summary.Row>
                          <Table.Summary.Row>
                            <Table.Summary.Cell align="right" colSpan={8}>
                              <strong>Kode Unik :</strong>
                            </Table.Summary.Cell>
                            <Table.Summary.Cell align="left" colSpan={1}>
                              <strong>
                                Rp. {formatNumber(orderDetail?.kode_unik)}
                              </strong>
                            </Table.Summary.Cell>
                            <Table.Summary.Cell />
                          </Table.Summary.Row>

                          <Table.Summary.Row>
                            <Table.Summary.Cell align="right" colSpan={8}>
                              <strong>Ongkir :</strong>
                            </Table.Summary.Cell>
                            <Table.Summary.Cell align="left" colSpan={1}>
                              <strong>
                                Rp. {formatNumber(orderDetail?.ongkir)}
                              </strong>
                            </Table.Summary.Cell>
                            <Table.Summary.Cell />
                          </Table.Summary.Row>

                          <Table.Summary.Row>
                            <Table.Summary.Cell align="right" colSpan={8}>
                              <strong>Total Amount :</strong>
                            </Table.Summary.Cell>
                            <Table.Summary.Cell align="left" colSpan={1}>
                              <strong>
                                Rp. {formatNumber(orderDetail?.total)}
                              </strong>
                            </Table.Summary.Cell>
                            <Table.Summary.Cell />
                          </Table.Summary.Row>
                        </Table.Summary>
                      )
                    }

                    return null
                  }}
                />
              </div>
            </div>
          </div>

          {/* informasi pengiriman */}
          <div className="card">
            <div className="card-header flex justify-between items-center">
              <h1 className="header-titl">Informasi Pengiriman</h1>
              <div className=" flex items-center space-x-2">
                <Button
                  label="Insert Invoice"
                  className={"mr-4"}
                  disabled={selectedRowKeys.length < 1}
                  onClick={() => handleInsertInvoice(null, true)}
                />
                <RenderIf isTrue={userData?.role.role_type !== "sales"}>
                  <ModalSplitDeliveryOrder
                    onFinish={(values) => onFinishSaveResi(values)}
                    fields={{ uid_lead: orderDetail?.uid_lead }}
                    products={productNeeds}
                  />
                </RenderIf>
              </div>
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
                    <td>Tipe Pengiriman</td>
                    <td>:</td>
                    <td>Normal</td>
                  </tr>
                  <tr>
                    <td>Alamat</td>
                    <td>:</td>
                    <td>{address?.alamat_detail}</td>
                  </tr>
                </tbody>
              </table>
              <Table
                scroll={{ x: "max-content" }}
                tableLayout={"auto"}
                className="mb-4"
                dataSource={
                  orderDeliveries &&
                  orderDeliveries.map((item) => {
                    return {
                      ...item,
                    }
                  })
                }
                rowSelection={rowSelection}
                columns={[
                  ...orderDeliveryColumns,
                  {
                    title: "Action",
                    key: "id",
                    align: "center",
                    fixed: "right",
                    width: 100,
                    render: (text, record) => (
                      <Dropdown.Button
                        style={{
                          width: 90,
                        }}
                        overlay={
                          <Menu
                            onClick={({ key }) => {
                              if (key === "print") {
                                return window.open(
                                  `/print/sj/${record.uid_lead}/${record.id}`
                                )
                              }
                              if (key === "print_si") {
                                return window.open(
                                  `/print/si/${record.uid_lead}/${record.id}`
                                )
                              }
                              if (key === "invoice") {
                                return handleInsertInvoice(
                                  record.product_need_id,
                                  false,
                                  record?.id
                                )
                              }

                              if (key === "cancel") {
                                return handleCancelInvoiceDelivery(record.id)
                              }
                            }}
                            itemIcon={<RightOutlined className="ml-8" />}
                            items={[
                              {
                                label: "Print SJ",
                                key: "print",
                                icon: <PrinterOutlined />,
                                disabled: record?.status === "cancel",
                              },
                              // {
                              //   label: "Print SI",
                              //   key: "print_si",
                              //   icon: <PrinterOutlined />,
                              //   disabled:
                              //     record?.status === "cancel" ||
                              //     record?.is_invoice != 1,
                              // },
                              {
                                label: "Invoice",
                                key: "invoice",
                                icon: <DownCircleFilled />,
                                disabled:
                                  record?.status === "cancel" ||
                                  record?.is_invoice == 1,
                              },
                              {
                                label: "Cancel",
                                key: "cancel",
                                icon: <CloseCircleOutlined />,
                                disabled:
                                  record?.status === "cancel" ||
                                  record?.status == "packing",
                              },
                            ]}
                            // onContextMenu={(e) => {
                            //   console.log(e, "context menu");
                            //   console.log("Right Click", e.pageX, e.pageY);
                            // }}
                          />
                        }
                      ></Dropdown.Button>
                    ),
                  },
                ]}
                loading={
                  loadingUpdateShippingInfo ||
                  loadingInsertInvoice ||
                  loadingCancelInvoiceDelivery ||
                  orderDeliveryLoading ||
                  orderDeliveryFetching
                }
                pagination={false}
                rowKey="id"
              />
            </div>
          </div>

          <div className="card">
            <div className="card-header flex justify-between items-center">
              <h1 className="header-titl">Invoice</h1>
            </div>
            <div className="card-body">
              <Table
                scroll={{ x: "max-content" }}
                tableLayout={"auto"}
                className="mb-4"
                dataSource={
                  orderDeliveries &&
                  orderDeliveries?.filter((item) => {
                    return (
                      item?.gp_submit_number ||
                      (item.is_invoice == 1 && !item?.gp_submit_number)
                    )
                  })
                }
                columns={[
                  ...productNeedListColumnInvoice,
                  {
                    title: "Action",
                    key: "id",
                    align: "center",
                    fixed: "right",
                    width: 100,
                    render: (text, record) => (
                      <Dropdown.Button
                        style={{
                          width: 90,
                        }}
                        overlay={
                          <Menu
                            onClick={({ key }) => {
                              if (key === "print") {
                                return window.open(
                                  `/print/si/${record?.uid_lead}/${record?.uid_invoice}`
                                )
                              }
                            }}
                            itemIcon={<RightOutlined className="ml-8" />}
                            items={[
                              {
                                label: "Print SI",
                                key: "print",
                                icon: <PrinterOutlined />,
                                disabled: record?.status === "cancel",
                              },
                            ]}
                          />
                        }
                      ></Dropdown.Button>
                    ),
                  },
                ]}
                loading={
                  loadingInsertInvoice ||
                  orderDeliveryLoading ||
                  orderDeliveryFetching
                }
                pagination={false}
                rowKey="id"
                summary={(currentData) => {
                  if (currentData && currentData.length > 0) {
                    const subtotal = currentData.reduce(
                      (acc, curr) => parseInt(acc) + parseInt(curr.subtotal),
                      0
                    )

                    const discount_amount = currentData.reduce(
                      (acc, curr) =>
                        parseInt(acc) + parseInt(curr.discount_amount || 0),
                      0
                    )

                    const total = currentData.reduce(
                      (acc, curr) => parseInt(acc) + parseInt(curr.total || 0),
                      0
                    )

                    const ppn = currentData.reduce(
                      (acc, curr) =>
                        parseInt(acc) + parseInt(curr.tax_amount || 0),
                      0
                    )
                    const dpp = subtotal - discount_amount
                    const totalwithOngkir =
                      parseInt(total) +
                      parseInt(orderDetail?.kode_unik) +
                      parseInt(orderDetail?.ongkir)
                    return (
                      <Table.Summary>
                        <Table.Summary.Row>
                          <Table.Summary.Cell align="right" colSpan={9}>
                            <strong>Subtotal (Sebelum Diskon) :</strong>
                          </Table.Summary.Cell>
                          <Table.Summary.Cell align="left" colSpan={1}>
                            <strong>Rp. {formatNumber(subtotal)}</strong>
                          </Table.Summary.Cell>
                          <Table.Summary.Cell />
                        </Table.Summary.Row>
                        <Table.Summary.Row>
                          <Table.Summary.Cell align="right" colSpan={9}>
                            <strong>Discount :</strong>
                          </Table.Summary.Cell>
                          <Table.Summary.Cell align="left" colSpan={1}>
                            <strong>Rp. {formatNumber(discount_amount)}</strong>
                          </Table.Summary.Cell>
                          <Table.Summary.Cell />
                        </Table.Summary.Row>
                        <Table.Summary.Row>
                          <Table.Summary.Cell align="right" colSpan={9}>
                            <strong>DPP :</strong>
                          </Table.Summary.Cell>
                          <Table.Summary.Cell align="left" colSpan={1}>
                            <strong>Rp. {formatNumber(dpp)}</strong>
                          </Table.Summary.Cell>
                          <Table.Summary.Cell />
                        </Table.Summary.Row>
                        <Table.Summary.Row>
                          <Table.Summary.Cell align="right" colSpan={9}>
                            <strong>PPN :</strong>
                          </Table.Summary.Cell>
                          <Table.Summary.Cell align="left" colSpan={1}>
                            <strong>Rp. {formatNumber(ppn)}</strong>
                          </Table.Summary.Cell>
                          <Table.Summary.Cell />
                        </Table.Summary.Row>
                        <Table.Summary.Row>
                          <Table.Summary.Cell align="right" colSpan={9}>
                            <strong>Kode Unik :</strong>
                          </Table.Summary.Cell>
                          <Table.Summary.Cell align="left" colSpan={1}>
                            <strong>
                              Rp. {formatNumber(orderDetail?.kode_unik)}
                            </strong>
                          </Table.Summary.Cell>
                          <Table.Summary.Cell />
                        </Table.Summary.Row>

                        <Table.Summary.Row>
                          <Table.Summary.Cell align="right" colSpan={9}>
                            <strong>Total Amount :</strong>
                          </Table.Summary.Cell>
                          <Table.Summary.Cell align="left" colSpan={1}>
                            <strong>Rp. {formatNumber(totalwithOngkir)}</strong>
                          </Table.Summary.Cell>
                          <Table.Summary.Cell />
                        </Table.Summary.Row>
                      </Table.Summary>
                    )
                  }
                }}
              />
            </div>
          </div>

          {/* ethix */}
          {/* <div className="card p-4">
            <Card title={"Ethix"}>
              <div className="row">
                <div className="col-md-12 mt-4">
                  <Table
                    dataSource={orderDetail?.ethix_items || []}
                    columns={ethixColumns}
                    loading={detailSalesOrderLoading}
                    pagination={false}
                    rowKey="id"
                    scroll={{ x: "max-content" }}
                    tableLayout={"auto"}
                  />
                </div>
              </div>
            </Card>
          </div> */}

          {/* Informasi Tracking */}
          <div className="card">
            <div className="card-header flex justify-between items-center">
              <h1 className="header-titl">Informasi Tracking</h1>
            </div>

            <div className="card-body">
              {/* <Steps progressDot direction="vertical" size="small" current={0}>
                {orderDetail?.ethix_items.reverse().map((row, index) => {
                  return (
                    <Step
                      key={index}
                      title={moment(row.created_at).format(
                        "ddd, DD MMM YYYY - LT"
                      )}
                      subTitle={row.description}
                    />
                  )
                })}
              </Steps> */}

              <Table
                scroll={{ x: "max-content" }}
                tableLayout={"auto"}
                className="mb-4"
                dataSource={orderDetail?.ethix_items?.reverse()}
                columns={[...trackingListColumn]}
                loading={detailSalesOrderLoading}
                pagination={false}
                rowKey="id"
              />
            </div>
          </div>

          {/* payment info */}
          {/* <PaymentDetail order={orderDetail} /> */}

          {/* informasi penagihan */}
          <div className="card">
            <div className="card-header flex justify-between items-center">
              <h1 className="header-title">Informasi Penagihan</h1>
              {orderDetail && (
                <ModalBillingOrder
                  detail={{ ...orderDetail, product_needs: productNeeds }}
                  refetch={() => {
                    refetchOrderBillings()
                    loadDetailOrderLead()
                  }}
                  user={userData}
                />
              )}
            </div>
            <div className="card-body">
              <Table
                dataSource={orderBillings}
                columns={
                  userData?.role?.role_type !== "sales"
                    ? [...billingColumns, ...billingActionColumn]
                    : [...billingColumns]
                }
                loading={loadingBilling || billingFetching}
                pagination={false}
                rowKey="id"
                scroll={{ x: "max-content" }}
                tableLayout={"auto"}
              />
            </div>
          </div>

          {/* reminders */}
          {/* <Reminder
            handleChangeCell={handleChangeCell}
            handleClickCell={handleClickCell}
            dataSource={reminders}
          /> */}

          <div className="card">
            <div className="card-body">
              <p>Notes</p>
              <TextArea
                // autoSize={{
                //   minRows: 2,
                //   maxRows: 6,
                // }}
                placeholder="notes"
                value={notes}
                onChange={(e) => setNotes(e.target.value)}
                onBlur={updateNotes}
              />
            </div>
          </div>

          {/* <RenderIf
            isTrue={
              userData?.role.role_type !== "sales" &&
              (userData?.role.role_type === "finance" ||
                userData?.role.role_type === "superadmin")
            }
          >
            <div className="card">
              <div className="card-body">
                <div className="flex justify-between items-center">
                  <p style={{ width: "60%" }}>
                    {inArray(orderDetail?.status, ["2", "3"]) && (
                      <i>
                        Pastikan Anda telah mendownload surat jalan dan
                        melakukan pengemasan terlebih dahulu untuk melanjutkan
                        ke proses Pengiriman Product
                      </i>
                    )}
                  </p>
                  <button
                    className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
                    onClick={() => {
                      const hasInvoiced = productNeed.every(
                        (item) => item.is_invoice > 0
                      )
                      if (!hasInvoiced) {
                        return toast.error(
                          "Pastikan Semua Barang Sudah Invoiced"
                        )
                      }

                      if (parseInt(total_qty_delivery) < parseInt(total_qty)) {
                        return toast.error(
                          "Pastikan Semua Barang Sudah Dikirim"
                        )
                      }

                      if (parseInt(total_qty_payment) < parseInt(total_qty)) {
                        return toast.error(
                          "Pastikan Semua Barang Sudah Ditagih"
                        )
                      }

                      return setClosed()
                    }}
                  >
                    <CreditCardOutlined />
                    <span className="ml-2">Payment Proccess</span>
                  </button>
                </div>
              </div>
            </div>
          </RenderIf> */}
        </div>
      </RenderIf>

      {/* Closed */}
      <RenderIf isTrue={orderDetail?.status === "3"}>
        <div>
          <OrderDetailInfo
            order={{ ...orderDetail, order_delivery: orderDeliveries }}
            printUrl={orderDetail?.printUrl}
            refetch={() => loadDetailOrderLead()}
          />
          {/* <div className="card">
            <div className="card-header flex justify-between items-center">
              <h1 className="header-title">Lead Activity</h1>
              <div>
                <Dropdown.Button
                  style={{ borderRadius: 10 }}
                  icon={<PrinterTwoTone />}
                  overlay={
                    <Menu>
                      <Menu.Item className="flex justify-between items-center">
                        <PrinterTwoTone />{" "}
                        <a href={printUrl?.si} target="_blank">
                          <span>Print SI</span>
                        </a>
                      </Menu.Item>
                      <Menu.Item className="flex justify-between items-center">
                        <PrinterTwoTone />{" "}
                        <a href={printUrl?.so} target="_blank">
                          <span>Print SO</span>
                        </a>
                      </Menu.Item>
                      <Menu.Item className="flex justify-between items-center">
                        <PrinterTwoTone />{" "}
                        <a href={printUrl?.sj} target="_blank">
                          <span>Print SJ</span>
                        </a>
                      </Menu.Item>
                    </Menu>
                  }
                ></Dropdown.Button>
              </div>
            </div>
            <div className="card-body">
              <Table
                dataSource={activityData}
                columns={activityColumns}
                loading={detailSalesOrderLoading}
                pagination={false}
                rowKey="id"
                scroll={{ x: "max-content" }}
                tableLayout={"auto"}
              />
            </div>
          </div> */}

          <div className="card">
            <div className="card-header">
              <h1 className="header-titl">Informasi Produk</h1>
            </div>
            <div className="card-body">
              {/* <table>
                <tbody>
                  <tr>
                    <td style={{ width: "20%" }} className="text-bold">
                      Order No
                    </td>
                    <td>: {orderDetail?.order_number}</td>
                  </tr>
                  <tr>
                    <td className="text-bold">Tipe Pengiriman</td>
                    <td>: Normal</td>
                  </tr>
                  <tr>
                    <td className="text-bold">Alamat</td>
                    <td>: {address_user?.alamat_detail || "-"}</td>
                  </tr>
                  {order_shipping && (
                    <>
                      <tr>
                        <td>Pengirim</td>
                        <td>: {order_shipping?.sender_name}</td>
                      </tr>
                      <tr>
                        <td>Telfon Pengirim</td>
                        <td>: {order_shipping?.sender_phone}</td>
                      </tr>
                      <tr>
                        <td>Nama Ekspedisi</td>
                        <td>: {order_shipping?.expedition_name}</td>
                      </tr>
                      <tr>
                        <td>Resi</td>
                        <td>: {order_shipping?.resi}</td>
                      </tr>
                      {order_shipping?.attachment_url?.length > 0 && (
                        <tr>
                          <td>Attachment</td>
                          <td>
                            <span>: </span>
                            <a href={order_shipping?.attachment_url[0]}>
                              <LinkOutlined />
                              <span>Attachment 1</span>
                            </a>
                          </td>
                        </tr>
                      )}
                      {order_shipping?.attachment_url?.map((item, index) => {
                        if (index > 0) {
                          return (
                            <tr key={index}>
                              <td></td>
                              <td>
                                <span>: </span>
                                <a href={item}>
                                  <LinkOutlined />
                                  <span>Attachment {index + 1}</span>
                                </a>
                              </td>
                            </tr>
                          )
                        }
                      })}
                    </>
                  )}
                </tbody>
              </table> */}
              <div className="mt-4">
                <Table
                  scroll={{ x: "max-content" }}
                  tableLayout={"auto"}
                  dataSource={productNeeds}
                  columns={[
                    ...productNeedListColumnDetail,
                    ...productNeedColumns,
                  ]}
                  loading={detailSalesOrderLoading}
                  pagination={false}
                  rowKey="id"
                  summary={(productNeed) => {
                    if (productNeed && productNeed.length > 0) {
                      return (
                        <Table.Summary>
                          <Table.Summary.Row>
                            <Table.Summary.Cell align="right" colSpan={8}>
                              <strong>Subtotal (Sebelum Diskon) :</strong>
                            </Table.Summary.Cell>
                            <Table.Summary.Cell align="left" colSpan={1}>
                              <strong>
                                Rp. {formatNumber(orderDetail?.subtotal)}
                              </strong>
                            </Table.Summary.Cell>
                            <Table.Summary.Cell />
                          </Table.Summary.Row>
                          <Table.Summary.Row>
                            <Table.Summary.Cell align="right" colSpan={8}>
                              <strong>Discount :</strong>
                            </Table.Summary.Cell>
                            <Table.Summary.Cell align="left" colSpan={1}>
                              <strong>
                                Rp. {formatNumber(orderDetail?.diskon)}
                              </strong>
                            </Table.Summary.Cell>
                            <Table.Summary.Cell />
                          </Table.Summary.Row>
                          <Table.Summary.Row>
                            <Table.Summary.Cell align="right" colSpan={8}>
                              <strong>DPP :</strong>
                            </Table.Summary.Cell>
                            <Table.Summary.Cell align="left" colSpan={1}>
                              <strong>
                                Rp. {formatNumber(orderDetail?.dpp)}
                              </strong>
                            </Table.Summary.Cell>
                            <Table.Summary.Cell />
                          </Table.Summary.Row>
                          <Table.Summary.Row>
                            <Table.Summary.Cell align="right" colSpan={8}>
                              <strong>PPN :</strong>
                            </Table.Summary.Cell>
                            <Table.Summary.Cell align="left" colSpan={1}>
                              <strong>
                                Rp. {formatNumber(orderDetail?.ppn)}
                              </strong>
                            </Table.Summary.Cell>
                            <Table.Summary.Cell />
                          </Table.Summary.Row>
                          <Table.Summary.Row>
                            <Table.Summary.Cell align="right" colSpan={8}>
                              <strong>Kode Unik :</strong>
                            </Table.Summary.Cell>
                            <Table.Summary.Cell align="left" colSpan={1}>
                              <strong>
                                Rp. {formatNumber(orderDetail?.kode_unik)}
                              </strong>
                            </Table.Summary.Cell>
                            <Table.Summary.Cell />
                          </Table.Summary.Row>

                          <Table.Summary.Row>
                            <Table.Summary.Cell align="right" colSpan={8}>
                              <strong>Ongkir :</strong>
                            </Table.Summary.Cell>
                            <Table.Summary.Cell align="left" colSpan={1}>
                              <strong>
                                Rp. {formatNumber(orderDetail?.ongkir)}
                              </strong>
                            </Table.Summary.Cell>
                            <Table.Summary.Cell />
                          </Table.Summary.Row>

                          <Table.Summary.Row>
                            <Table.Summary.Cell align="right" colSpan={8}>
                              <strong>Total Amount :</strong>
                            </Table.Summary.Cell>
                            <Table.Summary.Cell align="left" colSpan={1}>
                              <strong>
                                Rp. {formatNumber(orderDetail?.total)}
                              </strong>
                            </Table.Summary.Cell>
                            <Table.Summary.Cell />
                          </Table.Summary.Row>
                        </Table.Summary>
                      )
                    }

                    return null
                  }}
                />
              </div>
            </div>
          </div>

          {/* informasi pengiriman */}
          <div className="card">
            <div className="card-header flex justify-between items-center bg-red-300">
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
                    <td>Tipe Pengiriman</td>
                    <td>:</td>
                    <td>Normal</td>
                  </tr>
                  <tr>
                    <td>Alamat</td>
                    <td>:</td>
                    <td>{address?.alamat_detail}</td>
                  </tr>
                </tbody>
              </table>
              <Table
                scroll={{ x: "max-content" }}
                tableLayout={"auto"}
                className="mb-4"
                dataSource={orderDeliveries}
                columns={[...orderDeliveryColumns]}
                loading={detailSalesOrderLoading}
                pagination={false}
                rowKey="id"
              />
            </div>
          </div>

          {/* ethix */}
          {/* <div className="card p-4">
            <Card title={"Ethix"}>
              <div className="row">
                <div className="col-md-12 mt-4">
                  <Table
                    dataSource={orderDetail?.ethix_items || []}
                    columns={ethixColumns}
                    loading={detailSalesOrderLoading}
                    pagination={false}
                    rowKey="id"
                    scroll={{ x: "max-content" }}
                    tableLayout={"auto"}
                  />
                </div>
              </div>
            </Card>
          </div> */}

          {/* Informasi Tracking */}
          <div className="card">
            <div className="card-header flex justify-between items-center">
              <h1 className="header-titl">Informasi Tracking</h1>
            </div>

            <div className="card-body">
              {/* <Steps progressDot direction="vertical" size="small" current={0}>
                {orderDetail?.ethix_items.reverse().map((row, index) => {
                  return (
                    <Step
                      key={index}
                      title={moment(row.created_at).format(
                        "ddd, DD MMM YYYY - LT"
                      )}
                      subTitle={row.description}
                    />
                  )
                })}
              </Steps> */}
              <Table
                scroll={{ x: "max-content" }}
                tableLayout={"auto"}
                className="mb-4"
                dataSource={orderDetail?.ethix_items?.reverse()}
                columns={[...trackingListColumn]}
                loading={detailSalesOrderLoading}
                pagination={false}
                rowKey="id"
              />
            </div>
          </div>

          {/* <div className="card">
            <div className="card-header flex justify-between items-center">
              <h1 className="header-title">Product Need</h1>
            </div>
            <div className="card-body">
              <Table
                dataSource={productNeeds}
                columns={productNeedListColumn}
                loading={detailSalesOrderLoading}
                pagination={false}
                rowKey="id"
                scroll={{ x: "max-content" }}
                tableLayout={"auto"}
              />
            </div>
          </div> */}

          <div className="card">
            <div className="card-header flex justify-between items-center">
              <h1 className="header-title">Informasi Penagihan</h1>
            </div>
            <div className="card-body">
              <Table
                dataSource={orderDetail?.billings}
                columns={
                  userData?.role?.role_type !== "sales"
                    ? [...billingColumns]
                    : [...billingColumns]
                }
                loading={detailSalesOrderLoading}
                pagination={false}
                rowKey="id"
                scroll={{ x: "max-content" }}
                tableLayout={"auto"}
              />
            </div>
          </div>

          {/* <div className="card">
            <div className="card-header flex justify-between items-center">
              <h1 className="header-title">Negotiation</h1>
            </div>
            <div className="card-body">
              <Table
                dataSource={negotiationsData}
                columns={negotiationsColumns}
                loading={detailSalesOrderLoading}
                pagination={false}
                rowKey="id"
                scroll={{ x: "max-content" }}
                tableLayout={"auto"}
              />
            </div>
          </div> */}
        </div>
      </RenderIf>

      {/* Canceled */}
      <RenderIf isTrue={inArray(orderDetail?.status, ["-1", "4"])}>
        <div>
          <OrderDetailInfo
            order={{ ...orderDetail, order_delivery: orderDeliveries }}
            printUrl={orderDetail?.printUrl}
            refetch={() => loadDetailOrderLead()}
          />
          <div className="card">
            <div className="card-header">
              <h1 className="header-titl">Informasi Produk</h1>
            </div>
            <div className="card-body">
              {/* <table>
                <tbody>
                  <tr>
                    <td className="w-32 md:w-56">Order No</td>
                    <td className="w-4">:</td>
                    <td>{orderDetail?.order_number}</td>
                  </tr>
                  <tr>
                    <td className="w-32 md:w-56">Tipe Pengiriman</td>
                    <td className="w-4">:</td>
                    <td>Normal</td>
                  </tr>
                  <tr>
                    <td className="w-32 md:w-56">Alamat</td>
                    <td className="w-4">:</td>
                    <td>{address?.alamat_detail}</td>
                  </tr>
                </tbody>
              </table> */}
              <Table
                scroll={{ x: "max-content" }}
                tableLayout={"auto"}
                className="mb-4"
                dataSource={productNeeds}
                columns={[
                  ...productNeedListColumnDetail,
                  ...productNeedColumns,
                ]}
                loading={detailSalesOrderLoading}
                pagination={false}
                rowKey="id"
                summary={(productNeed) => {
                  if (productNeed && productNeed.length > 0) {
                    return (
                      <Table.Summary>
                        <Table.Summary.Row>
                          <Table.Summary.Cell align="right" colSpan={8}>
                            <strong>Subtotal (Sebelum Diskon) :</strong>
                          </Table.Summary.Cell>
                          <Table.Summary.Cell align="left" colSpan={1}>
                            <strong>
                              Rp. {formatNumber(orderDetail?.subtotal)}
                            </strong>
                          </Table.Summary.Cell>
                          <Table.Summary.Cell />
                        </Table.Summary.Row>
                        <Table.Summary.Row>
                          <Table.Summary.Cell align="right" colSpan={8}>
                            <strong>Discount :</strong>
                          </Table.Summary.Cell>
                          <Table.Summary.Cell align="left" colSpan={1}>
                            <strong>
                              Rp. {formatNumber(orderDetail?.diskon)}
                            </strong>
                          </Table.Summary.Cell>
                          <Table.Summary.Cell />
                        </Table.Summary.Row>
                        <Table.Summary.Row>
                          <Table.Summary.Cell align="right" colSpan={8}>
                            <strong>DPP :</strong>
                          </Table.Summary.Cell>
                          <Table.Summary.Cell align="left" colSpan={1}>
                            <strong>
                              Rp. {formatNumber(orderDetail?.dpp)}
                            </strong>
                          </Table.Summary.Cell>
                          <Table.Summary.Cell />
                        </Table.Summary.Row>
                        <Table.Summary.Row>
                          <Table.Summary.Cell align="right" colSpan={8}>
                            <strong>PPN :</strong>
                          </Table.Summary.Cell>
                          <Table.Summary.Cell align="left" colSpan={1}>
                            <strong>
                              Rp. {formatNumber(orderDetail?.ppn)}
                            </strong>
                          </Table.Summary.Cell>
                          <Table.Summary.Cell />
                        </Table.Summary.Row>
                        <Table.Summary.Row>
                          <Table.Summary.Cell align="right" colSpan={8}>
                            <strong>Kode Unik :</strong>
                          </Table.Summary.Cell>
                          <Table.Summary.Cell align="left" colSpan={1}>
                            <strong>
                              Rp. {formatNumber(orderDetail?.kode_unik)}
                            </strong>
                          </Table.Summary.Cell>
                          <Table.Summary.Cell />
                        </Table.Summary.Row>

                        <Table.Summary.Row>
                          <Table.Summary.Cell align="right" colSpan={8}>
                            <strong>Ongkir :</strong>
                          </Table.Summary.Cell>
                          <Table.Summary.Cell align="left" colSpan={1}>
                            <strong>
                              Rp. {formatNumber(orderDetail?.ongkir)}
                            </strong>
                          </Table.Summary.Cell>
                          <Table.Summary.Cell />
                        </Table.Summary.Row>

                        <Table.Summary.Row>
                          <Table.Summary.Cell align="right" colSpan={8}>
                            <strong>Total Amount :</strong>
                          </Table.Summary.Cell>
                          <Table.Summary.Cell align="left" colSpan={1}>
                            <strong>
                              Rp. {formatNumber(orderDetail?.total)}
                            </strong>
                          </Table.Summary.Cell>
                          <Table.Summary.Cell />
                        </Table.Summary.Row>
                      </Table.Summary>
                    )
                  }

                  return null
                }}
              />
              {orderDetail?.status > 0 && (
                <div>
                  <p>
                    Silahkan pilih PIC Warehouse untuk pengiriman sales order
                    dibawah ini:
                  </p>
                  <div>
                    <label htmlFor="" className="text-bold mb-2">
                      PIC Warehouse
                    </label>
                    <Select
                      disabled={orderDetail?.status === "4"}
                      loading={loadingWarehouse}
                      allowClear
                      className="w-full mb-2"
                      placeholder="Pilih PIC Warehouse"
                      onChange={(e) => handleChangeKurir(e)}
                      value={orderDetail?.courier}
                    >
                      {warehouse &&
                        warehouse.map((item) => (
                          <Select.Option key={item.id} value={item.id}>
                            {item?.name}
                          </Select.Option>
                        ))}
                    </Select>
                    <small>
                      <i>
                        Anda dapat melakukan perubahan saat data belum masuk ke
                        dalam proses Packing Proses
                      </i>
                    </small>
                  </div>
                </div>
              )}
            </div>
          </div>

          {/* <div className="card p-4">
            <Card title={"Ethix"}>
              <div className="row">
                <div className="col-md-12 mt-4">
                  <Table
                    dataSource={orderDetail?.ethix_items || []}
                    columns={ethixColumns}
                    loading={detailSalesOrderLoading}
                    pagination={false}
                    rowKey="id"
                    scroll={{ x: "max-content" }}
                    tableLayout={"auto"}
                  />
                </div>
              </div>
            </Card>
          </div> */}

          {/* informasi pengiriman */}
          <div className="card">
            <div className="card-header flex justify-between items-center">
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
                    <td>Tipe Pengiriman</td>
                    <td>:</td>
                    <td>Normal</td>
                  </tr>
                  <tr>
                    <td>Alamat</td>
                    <td>:</td>
                    <td>{address?.alamat_detail}</td>
                  </tr>
                </tbody>
              </table>
              <Table
                scroll={{ x: "max-content" }}
                tableLayout={"auto"}
                className="mb-4"
                dataSource={orderDeliveries}
                columns={[...orderDeliveryColumns]}
                loading={detailSalesOrderLoading}
                pagination={false}
                rowKey="id"
              />
            </div>
          </div>

          {orderDetail?.status > 0 && (
            <div className="card">
              <div className="card-body">
                <p>Notes</p>
                <TextArea
                  disabled={orderDetail?.status === "4"}
                  // autoSize={{
                  //   minRows: 2,
                  //   maxRows: 6,
                  // }}
                  placeholder="notes"
                  value={notes}
                  onChange={(e) => setNotes(e.target.value)}
                  onBlur={updateNotes}
                />
              </div>
            </div>
          )}

          {orderDetail?.status == 1 && (
            <RenderIf
              isTrue={
                userData?.role.role_type !== "sales" &&
                (userData?.role.role_type === "adminsales" ||
                  userData?.role.role_type === "adminwarehouse" ||
                  userData?.role.role_type === "leadwh" ||
                  userData?.role.role_type === "leadsales" ||
                  userData?.role.role_type === "superadmin" ||
                  userData?.role.role_type === "finance" ||
                  userData?.role.role_type === "lead_finance")
              }
            >
              <button
                className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2 float-right"
                onClick={() => {
                  if (detailSalesOrderLoading) {
                    return null
                  }

                  if (!orderDetail?.courier) {
                    return message.error(
                      "Mohon Pilih PIC Warehouse Terlebih Dahuku"
                    )
                  }

                  assignWarehouse()
                }}
                disabled={detailSalesOrderLoading}
              >
                {detailSalesOrderLoading && <LoadingOutlined />}
                Packing Proses
              </button>
            </RenderIf>
          )}
        </div>
      </RenderIf>
    </Layout>
  )
}

export default OrderManualLeadDetail
