import {
  CloseCircleOutlined,
  LoadingOutlined,
  PrinterOutlined,
  RightOutlined,
} from "@ant-design/icons"
import { Card, Dropdown, Menu, message, Select, Table } from "antd"
import React, { useState } from "react"
import { useNavigate, useParams } from "react-router-dom"
import Layout from "../../../components/layout"
import ModalOngkosKirim from "../../../components/Modal/ModalOngkosKirim"
import ModalSplitDeliveryOrder from "../../../components/Modal/ModalSplitDeliveryOrder"
import UpdateUniqueCode from "../../../components/UpdateUniqueCode"
import { useGetUserWarehouseQuery } from "../../../configs/Redux/Services/generalServices"
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
} from "../../../configs/Redux/Services/salesOrderService"
import { formatNumber, getItem, inArray, RenderIf } from "../../../helpers"
import {
  orderDeliveryColumns,
  productNeedListColumnInvoice,
  transactionProductListColumn,
} from "./config"
import Button from "../../../components/atoms/Button"
import axios from "axios"
import OrderDetailInfo from "./Components/OrderDetailInfo"

const TransactionAgentDetail = ({ type = "agent" }) => {
  const navigate = useNavigate()
  const { transaction_id } = useParams()
  const [loading, setLoading] = React.useState(false)
  const [detail, setDetail] = React.useState({})
  const [products, setProducts] = React.useState([])
  const [selectedRowKeys, setSelectedRowKeys] = useState([])
  const userData = getItem("user_data", true)

  const {
    data: orderDetail,
    isLoading: detailSalesOrderLoading,
    isFetching: detailSalesOrderFetching,
    refetch,
  } = useGetSalesOrderDetailQuery(`/api/sales-order/detail/${transaction_id}`)

  // delivery
  const {
    data: productNeeds,
    isLoading: productNeedsLoading,
    isFetching: productNeedsFetching,
    refetch: refetchProductNeeds,
  } = useGetSalesOrderItemsDetailQuery(
    `/api/sales-order/items/${transaction_id}`
  )
  // billings
  const {
    data: orderBillings,
    isLoading: loadingBilling,
    isFetching: billingFetching,
    refetch: refetchOrderBillings,
  } = useGetSalesOrderBillingItemsDetailQuery(
    `/api/sales-order/billing/${transaction_id}`
  )

  const {
    data: orderDeliveries,
    isLoading: orderDeliveryLoading,
    isFetching: orderDeliveryFetching,
    refetch: refetchorderDelivery,
  } = useGetSalesOrderDeliveryItemsDetailQuery(
    `/api/sales-order/delivery/${transaction_id}`
  )

  // general
  // const { data: taxs } = useGetTaxQuery()
  const { data: warehouse, isLoading: loadingWarehouse } =
    useGetUserWarehouseQuery()
  // const { data: userAddressList, isLoading: loadingAddress } =
  //   useGetAddressUserQuery(orderDetail?.contact)

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

  const loadDetail = () => refetch()

  // const handleChangeProductItem = ({ dataIndex, value, id }) => {
  //   updateProductItem({
  //     url: "/api/general/update-product-need",
  //     body: {
  //       value,
  //       field: dataIndex,
  //       uid_lead: transaction_id,
  //       item_id: id,
  //     },
  //   })
  // }

  const handleAssignWarehouse = () => {
    assignWarehouse(
      `/api/order-manual/assign-warehouse/${transaction_id}`
    ).then(({ error }) => {
      if (error) {
        return message.error("Packing Proses Gagal")
      }
      loadDetailOrderLead()
      message.success("Packing Proses Success")
    })
  }

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
      message.success("Data input pengiriman berhasil disimpan!")
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
        refetchProductNeeds()
        setSelectedRowKeys([])
        refetchorderDelivery()
        loadDetailOrderLead()
        updateOrderStatus(5)
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
        refetchProductNeeds()
        setSelectedRowKeys([])
        refetchorderDelivery()
        loadDetailOrderLead()
        updateOrderStatus(5)
        return message.success("Data Invoice berhasil Diproses!")
      })
    }
  }

  const handleCancelInvoiceDelivery = (invoice_id) => {
    cancelInvoiceDelivery(
      `/api/order-manual/delivery/cancel/${invoice_id}`
    ).then(({ error }) => {
      if (error) {
        return message.error("Pengiriman gagal dibatalkan")
      }
      refetchProductNeeds()
      refetchorderDelivery()
      loadDetailOrderLead()
      return message.success("Pengiriman berhasil dibatalkan")
    })
  }

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

  const updateOrderStatus = (status = 1) => {
    axios
      .post("/api/sales-order/update/status", {
        uid_lead: transaction_id,
        status,
      })
      .then((res) => {
        message.success(res.data?.message)
        loadDetail()
      })
      .catch((e) => {
        const data = e.response.data
        message.error(data?.message)
      })
  }

  return (
    <Layout
      title="Detail Transaksi "
      onClick={() => navigate(-1)}
      lastItemLabel={orderDetail?.order_number}
      rightContent={
        <div>
          {orderDetail?.status == 0 && (
            <button
              onClick={() => updateOrderStatus()}
              className={`text-white bg-blue-800 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mr-2`}
            >
              Proses Pesanan
            </button>
          )}
        </div>
      }
    >
      <OrderDetailInfo
        order={{ ...orderDetail, order_delivery: orderDeliveries }}
        printUrl={orderDetail?.printUrl}
        refetch={() => refetch()}
      />

      {/* products */}
      <Card title={"Detail Product"} className={"mt-4"}>
        <Table
          scroll={{ x: "max-content" }}
          tableLayout={"auto"}
          className="mb-4"
          dataSource={productNeeds}
          columns={[...transactionProductListColumn]}
          loading={productNeedsLoading || productNeedsFetching}
          pagination={false}
          rowKey="id"
          summary={(productNeed) => {
            if (productNeed && productNeed.length > 0) {
              return (
                <Table.Summary>
                  <Table.Summary.Row>
                    <Table.Summary.Cell align="right" colSpan={6}>
                      <strong>Subtotal (Sebelum Diskon) :</strong>
                    </Table.Summary.Cell>
                    <Table.Summary.Cell align="left" colSpan={1}>
                      <strong>Rp. {formatNumber(orderDetail?.subtotal)}</strong>
                    </Table.Summary.Cell>
                    <Table.Summary.Cell />
                  </Table.Summary.Row>
                  <Table.Summary.Row>
                    <Table.Summary.Cell align="right" colSpan={6}>
                      <strong>Discount :</strong>
                    </Table.Summary.Cell>
                    <Table.Summary.Cell align="left" colSpan={1}>
                      <strong>Rp. {formatNumber(orderDetail?.diskon)}</strong>
                    </Table.Summary.Cell>
                    <Table.Summary.Cell />
                  </Table.Summary.Row>
                  <Table.Summary.Row>
                    <Table.Summary.Cell align="right" colSpan={6}>
                      <strong>DPP :</strong>
                    </Table.Summary.Cell>
                    <Table.Summary.Cell align="left" colSpan={1}>
                      <strong>Rp. {formatNumber(orderDetail?.dpp)}</strong>
                    </Table.Summary.Cell>
                    <Table.Summary.Cell />
                  </Table.Summary.Row>
                  <Table.Summary.Row>
                    <Table.Summary.Cell align="right" colSpan={6}>
                      <strong>PPN :</strong>
                    </Table.Summary.Cell>
                    <Table.Summary.Cell align="left" colSpan={1}>
                      <strong>Rp. {formatNumber(orderDetail?.ppn)}</strong>
                    </Table.Summary.Cell>
                    <Table.Summary.Cell />
                  </Table.Summary.Row>
                  <Table.Summary.Row>
                    <Table.Summary.Cell align="right" colSpan={6}>
                      <strong>Kode Unik :</strong>
                    </Table.Summary.Cell>
                    <Table.Summary.Cell align="left" colSpan={1}>
                      {inArray(getItem("role"), ["agent", "subagent"]) ? (
                        <strong>
                          Rp. {formatNumber(orderDetail?.kode_unik)}
                        </strong>
                      ) : (
                        <UpdateUniqueCode
                          item={{
                            label: orderDetail?.kode_unik,
                            value: orderDetail?.kode_unik || 0,
                          }}
                          order={orderDetail}
                          refetch={() => loadDetail()}
                          url={"/api/order-manual/update/kode-unik"}
                          disabled={false}
                        />
                      )}
                    </Table.Summary.Cell>
                    <Table.Summary.Cell />
                  </Table.Summary.Row>
                  <Table.Summary.Row>
                    <Table.Summary.Cell align="right" colSpan={6}>
                      <strong>Ongkir :</strong>
                    </Table.Summary.Cell>
                    <Table.Summary.Cell align="left" colSpan={1}>
                      {inArray(getItem("role"), ["agent", "subagent"]) ? (
                        <strong>Rp. {formatNumber(orderDetail?.ongkir)}</strong>
                      ) : (
                        <ModalOngkosKirim
                          disabled={false}
                          initialValues={{
                            ongkir: orderDetail?.ongkir,
                          }}
                          refetch={() => loadDetail()}
                          url={`/api/order-manual/update/ongkir/${orderDetail?.uid_lead}`}
                        />
                      )}
                    </Table.Summary.Cell>
                    <Table.Summary.Cell />
                  </Table.Summary.Row>

                  <Table.Summary.Row>
                    <Table.Summary.Cell align="right" colSpan={6}>
                      <strong>Total Amount :</strong>
                    </Table.Summary.Cell>
                    <Table.Summary.Cell align="left" colSpan={1}>
                      <strong>Rp. {formatNumber(orderDetail?.total)}</strong>
                    </Table.Summary.Cell>
                    <Table.Summary.Cell />
                  </Table.Summary.Row>
                </Table.Summary>
              )
            }

            return null
          }}
        />
      </Card>

      <div className="card mt-4">
        <div className="card-header flex justify-between items-center">
          <h1 className="header-titl">Informasi Pengiriman</h1>
          <RenderIf
            isTrue={
              getItem("role") !== "sales" &&
              (getItem("role") === "adminsales" ||
                getItem("role") === "adminwarehouse" ||
                getItem("role") === "leadwh" ||
                getItem("role") === "leadsales" ||
                getItem("role") === "superadmin" ||
                getItem("role") === "finance" ||
                getItem("role") === "lead_finance")
            }
          >
            <div>
              {orderDetail?.status == 1 && (
                <ModalSplitDeliveryOrder
                  onFinish={(values) => onFinishSaveResi(values)}
                  fields={{ uid_lead: orderDetail?.uid_lead }}
                  products={productNeeds}
                />
              )}

              {orderDetail?.status == 2 && (
                <div className=" flex items-center space-x-2">
                  <Button
                    label="Insert Invoice"
                    className={"mr-4"}
                    disabled={selectedRowKeys.length < 1}
                    onClick={() => handleInsertInvoice(null, true)}
                  />
                </div>
              )}
            </div>
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
                <td>{orderDetail?.selected_address}</td>
              </tr>
            </tbody>
          </table>
          <Table
            scroll={{ x: "max-content" }}
            tableLayout={"auto"}
            className="mb-4"
            dataSource={orderDeliveries}
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

      {orderDetail?.status >= 2 && (
        <div className="card mt-4">
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
      )}

      {/* {detail?.status == 2 && (
        <Card title={"Informasi Upload Pembayaran"} className={"mt-4"}>
          <Table
            columns={[
              ...transactionUploadPaymentListColumn,
              {
                title: "Action",
                dataIndex: "action",
                key: "action",
                render: (value, record) => {
                  return (
                    <div className="flex">
                      <button
                        onClick={() =>
                          updatePaymentStatus("approve", record.id)
                        }
                        className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mr-2"
                      >
                        <CheckOutlined />
                      </button>
                      <ModalCancelOrder
                        transactions_id={[detail.id]}
                        type={type}
                        refetch={() => loadDetail()}
                        onConfirm={(value) =>
                          updatePaymentStatus("reject", record.id, value)
                        }
                      >
                        <button className="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2">
                          <CloseOutlined />
                        </button>
                      </ModalCancelOrder>
                    </div>
                  )
                },
              },
            ]}
            dataSource={
              detail?.confirm_payment ? [detail?.confirm_payment] : []
            }
            rowKey={"id"}
            pagination={false}
          />
        </Card>
      )} */}

      {orderDetail?.status == 1 && (
        <RenderIf
          isTrue={
            getItem("role") !== "sales" &&
            (getItem("role") === "adminsales" ||
              getItem("role") === "adminwarehouse" ||
              getItem("role") === "leadwh" ||
              getItem("role") === "leadsales" ||
              getItem("role") === "superadmin" ||
              getItem("role") === "finance" ||
              getItem("role") === "lead_finance")
          }
        >
          <Card className={"mt-4"}>
            <p>
              Silakan pilih PIC Warehouse untuk pengiriman sales order dibawah
              ini:
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
                  Anda dapat melakukan perubahan saat data belum masuk ke dalam
                  proses Packing Proses
                </i>
              </small>
            </div>
          </Card>
        </RenderIf>
      )}

      {orderDetail?.status == 1 && (
        <RenderIf
          isTrue={
            getItem("role") !== "sales" &&
            (getItem("role") === "adminsales" ||
              getItem("role") === "adminwarehouse" ||
              getItem("role") === "leadwh" ||
              getItem("role") === "leadsales" ||
              getItem("role") === "superadmin" ||
              getItem("role") === "finance" ||
              getItem("role") === "lead_finance")
          }
        >
          <Card className="mt-4 rounded-lg">
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
          </Card>
        </RenderIf>
      )}
    </Layout>
  )
}

export default TransactionAgentDetail
