import {
  CheckOutlined,
  CloseOutlined,
  DeleteOutlined,
  LoadingOutlined,
  PrinterTwoTone,
  RightOutlined,
  SaveOutlined,
  WarningFilled,
  EditOutlined,
} from "@ant-design/icons"

import {
  Button,
  Card,
  Checkbox,
  DatePicker,
  Dropdown,
  Form,
  Input,
  Menu,
  Popconfirm,
  Table,
  Tag,
} from "antd"
import React, { useEffect } from "react"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../components/layout"
import { formatDate, formatNumber, getItem, inArray } from "../../helpers"
import ModalNotes from "./Components/ModalNotes"
import ModalPenerimaan from "./Components/ModalPenerimaan"
import ModalDoNumber from "../../components/Modal/ModalDoNumber"
import PurchaseBillingModal from "./Components/PurchaseBillingModal"
import RejectModal from "./Components/RejectModal"
import TableAddProductWithSummary from "./Components/TableAddProductWithSummary"
import { purchaseBillingListColumn, renderStatusComponent } from "./config"
import { ethixColumns } from "../Purchase/config"

const TableInformation = ({ title = "Company", value = "PT AIMI Group" }) => {
  return (
    <div>
      <tr>
        <td className="w-28 lg:w-36 ">
          <h3 className="font-semibold">{title}</h3>
        </td>
        <td className="w-4">:</td>
        <td>
          <h3>{value}</h3>
        </td>
      </tr>
    </div>
  )
}

const PurchaseOrderDetail = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const { purchase_order_id } = useParams()
  const [productNeed, setProductNeed] = React.useState([])
  const [loading, setLoading] = React.useState(false)
  const [loadingApprove, setLoadingApprove] = React.useState(false)
  const [detail, setDetail] = React.useState({})
  const [stockOpname, setStockOpname] = React.useState(false)
  const [selectedRowKeys, setSelectedRowKeys] = React.useState([])

  const showModal = () => {
    setIsModalOpen(true)
  }

  const loadDetail = () => {
    setLoading(true)
    axios
      .get(`/api/purchase/purchase-order/${purchase_order_id}`)
      .then((res) => {
        const { data } = res.data
        setLoading(false)
        setDetail(data)
        if (data.type_po == "product") {
          const newData = data?.items.map((item, index) => {
            return {
              ...item,
              key: index,
              id: item.id,
              product_name: item?.product_name || "-",
              sku: item.sku,
              uom: item.u_of_m,
              harga_satuan: item.price,
              qty: item.qty,
              tax_id: item.tax_id,
              subtotal: item.subtotal,
              total: item.total_amount,
              tax_total: formatNumber(item.tax_amount, "Rp. "),
              qty_diterima: item.qty_diterima,
              subtotal_qty_diterima: item.subtotal_qty_diterima,
              invoice_entry: item.invoice_entry,
              received_number: item.received_number,
              vendor_doc_number: item.vendor_doc_number,
              confirm_by_name: item.confirm_by_name,
              invoice_date: item.invoice_date,
              notes: item.notes || "-",
              ethix_items: item?.ethix_items || [],
            }
          })
          setProductNeed(newData)
        } else {
          const items = data?.items.map((item, index) => {
            return {
              ...item,
              key: index,
              id: item.id,
              product_name: item?.product_name || "-",
              sku: item.sku,
              uom: item.u_of_m,
              harga_satuan: item.price,
              qty: item.qty,
              tax_id: item.tax_id,
              subtotal: item.subtotal,
              total: item.total_amount,
              tax_total: formatNumber(item.tax_amount, "Rp. "),
              qty_diterima: item.qty_diterima,
              subtotal_qty_diterima: item.subtotal_qty_diterima,
              invoice_entry: item.invoice_entry,
              received_number: item.received_number,
              vendor_doc_number: item.vendor_doc_number,
              confirm_by_name: item.confirm_by_name,
              invoice_date: item.invoice_date,
              notes: item.notes || "-",
            }
          })

          setProductNeed(items)
        }
      })
      .catch((e) => setLoading(false))
  }
  // console.log(productNeed, "productNeed")
  const approvePurchaseOrder = (stock_opname = false) => {
    setLoadingApprove(true)
    axios
      .post(`/api/purchase/purchase-order/approve/${purchase_order_id}`, {
        status: 1,
        stock_opname,
      })
      .then((res) => {
        setLoadingApprove(false)
        toast.success("Approve berhasil", {
          position: toast.POSITION.TOP_RIGHT,
        })
        loadDetail()
      })
      .catch((err) => {
        setLoadingApprove(false)
        toast.error("Approve gagal", {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }

  const assignToWarehouse = () => {
    setLoadingApprove(true)
    axios
      .post(
        `/api/purchase/purchase-order/assign-warehouse/${purchase_order_id}`,
        { status: stockOpname ? 3 : 2, stock_opname: stockOpname }
      )
      .then((res) => {
        setLoadingApprove(false)
        toast.success("Assign warehouse berhasil", {
          position: toast.POSITION.TOP_RIGHT,
        })
        loadDetail()
      })
      .catch((err) => {
        setLoadingApprove(false)
        toast.error("Assign warehouse gagal", {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }

  const updateStatus = (status) => {
    axios
      .post(`/api/purchase/purchase-order/status/update/${purchase_order_id}`, {
        status,
      })
      .then((res) => {
        setLoadingApprove(false)
        toast.success("Status berhasil diupdate")
        loadDetail()
      })
      .catch((err) => {
        setLoadingApprove(false)
        toast.error("Status gagal diupdate")
      })
  }

  const handleUpdateBilling = (billing_id, type) => {
    axios
      .post(`/api/purchase/purchase-order/billing/${type}/${billing_id}`)
      .then((res) => {
        setLoadingApprove(false)
        toast.success("Billing berhasil " + type)
        loadDetail()
      })
      .catch((err) => {
        setLoadingApprove(false)
        toast.error("Billing gagal " + type)
      })
  }

  const handleComplete = () => {
    axios
      .post(`/api/purchase/purchase-order/complete/${purchase_order_id}`)
      .then((res) => {
        setLoadingApprove(false)
        toast.success("Status berhasil diupdate")
        loadDetail()
      })
      .catch((err) => {
        setLoadingApprove(false)
        toast.error("Status gagal diupdate")
      })
  }

  const handleInsertInvoice = (values) => {
    axios
      .post(`/api/purchase/purchase-order/product/invoice`, values)
      .then((res) => {
        toast.success("Data berhasil diupdate")
        setSelectedRowKeys([])
        loadDetail()
      })
      .catch((err) => {
        toast.error("Data gagal diupdate")
      })
  }

  const deleteProductItem = (product_id) => {
    axios
      .post(`/api/purchase/purchase-order/product/delete/${product_id}`, {
        _method: "DELETE",
      })
      .then((res) => {
        toast.success("Data berhasil diupdate")
        loadDetail()
      })
      .catch((err) => {
        toast.error("Data gagal diupdate")
      })
  }

  useEffect(() => {
    loadDetail()
  }, [])

  // multiple checkbox
  const rowSelection = {
    selectedRowKeys,
    onChange: (newSelectedRowKeys) => {
      return setSelectedRowKeys(newSelectedRowKeys)
    },
    getCheckboxProps: (record) => ({
      disabled: inArray(record.invoice_entry, [1, 2]), // Column configuration not to be checked
    }),
  }

  const role = getItem("role")
  const isWaitingApproval = detail?.status === "5"
  const isFinance = inArray(role, ["finance", "lead_finance"])
  const isWarehouse = inArray(role, ["warehouse"])
  const isDelivery = inArray(detail?.status, ["2", "3"])
  const canAddPayment = isFinance && isDelivery
  const canApprove = isFinance && isWaitingApproval
  const canAcceptProduct =
    isWarehouse && inArray(detail?.status, ["1", "2", "3", "4"])
  const canUpdateStatus = isFinance && isDelivery
  const isStockOpname = detail?.stock_opname > 0 ? true : false
  const canPrintPdf = inArray(role, [
    "finance",
    "purchasing",
    "superadmin",
    "lead_finance",
  ])

  const rightContent = (
    <div className="flex items-center">
      {canApprove ? (
        <>
          <RejectModal
            refetch={() => loadDetail()}
            url={`/api/purchase/purchase-order/reject/${purchase_order_id}`}
          />
          <Popconfirm
            title="Terapkan stock opname?"
            onConfirm={() => approvePurchaseOrder(true)}
            onCancel={() => approvePurchaseOrder()}
            okText="Ya"
            cancelText="Tidak"
          >
            <button
              className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
              title="Reject"
            >
              {loadingApprove ? (
                <LoadingOutlined />
              ) : (
                <CheckOutlined className="md:mr-2" />
              )}
              <span className="hidden md:block">Approve</span>
            </button>
          </Popconfirm>
        </>
      ) : null}
    </div>
  )

  const productNeedList =
    productNeed.filter((product) => product.status == 1) || []
  const productInvoiceEntrys = detail.invoice_entrys || []

  return (
    <>
      <Layout
        title="Proses Data Purchase Order"
        href="/purchase/purchase-order"
        rightContent={rightContent}
      >
        <Card
          title="Informasi Purchase Order"
          extra={
            <div className="flex items-center">
              <div className="flex justify-end items-center">
                <strong className="mr-2">Status :</strong>
                {renderStatusComponent(detail?.status)}
              </div>
              {canPrintPdf && (
                <a
                  href={"/purchase/purchase-order/print/" + detail?.id}
                  target="_blank"
                >
                  <Button className="ml-4" title="Reject">
                    <PrinterTwoTone />
                  </Button>
                </a>
              )}
            </div>
          }
        >
          <div className="card-body grid md:grid-cols-3 gap-4">
            <TableInformation title="PO Number" value={detail?.po_number} />
            <TableInformation title="Company" value={detail?.company_name} />
            <TableInformation title="Vendor Code" value={detail?.vendor_code} />
            <TableInformation title="Vendor Name" value={detail?.vendor_name} />
            <TableInformation title="Tipe PO" value={detail?.type_po} />
            <TableInformation title="Channel Dist" value={detail?.channel} />
            <TableInformation
              title="Payment Term"
              value={detail?.payment_term_name}
            />
            <TableInformation title="Currency ID" value={"Rp (Rupiah)"} />
            <TableInformation
              title="Created by"
              value={detail?.created_by_name}
            />
            <TableInformation
              title="Created Date"
              value={detail?.created_date}
            />
            <TableInformation
              title="Notes"
              value={<ModalNotes value={detail?.note_purchase || "-"} />}
            />

            {detail?.rejected_reason && (
              <div className="md:col-span-3 bg-red-50">
                <Tag
                  className="p-2 w-full"
                  icon={<WarningFilled />}
                  color="warning"
                >
                  Reject Reason : {detail?.rejected_reason}
                </Tag>
              </div>
            )}

            <div className="lg:col-span-3 mt-6 border-t-2 pt-5">
              <TableAddProductWithSummary
                detail={detail}
                data={productNeed.filter((item) => item.is_master > 0)}
              />
            </div>
          </div>
        </Card>
      </Layout>

      <div className="card p-4">
        <Card
          title={
            <div>
              <span className="mr-4">Informasi Pengiriman Barang</span>
              {isStockOpname && <Tag color="gold">Stock Opname</Tag>}
            </div>
          }
          extra={
            <div className="flex justify-end items-center">
              {canAcceptProduct && (
                <ModalPenerimaan
                  refetch={() => loadDetail()}
                  products={
                    detail.items?.filter((item) => item.is_master > 0) || []
                  }
                  url={`/api/purchase/purchase-order/product/add/${detail.id}`}
                  detail={detail}
                />
              )}
              {/* {isWarehouse && ( */}
              {inArray(getItem("role"), [
                "finance",
                "purchasing",
                "superadmin",
              ]) && (
                  <>
                    {selectedRowKeys.length > 0 ? (
                      <button
                        className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
                        title="Reject"
                        onClick={() =>
                          handleInsertInvoice({
                            invoice_entry: 2,
                            item_id: selectedRowKeys,
                          })
                        }
                      >
                        <span className="hidden md:block">Insert Invoice</span>
                      </button>
                    ) : (
                      <button
                        className="text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
                        title="Reject"
                        disabled={true}
                      >
                        <span className="hidden md:block">Insert Invoice</span>
                      </button>
                    )}
                  </>
                )}

              {/* )} */}
            </div>
          }
        >
          <div className="row">
            <div className="col-md-6">
              <TableInformation
                title="Warehouse"
                value={detail?.warehouse_name}
              />
              <TableInformation
                title="PIC Warehouse"
                value={detail?.warehouse_user_name}
              />
              <TableInformation
                title="Status Ethix"
                value={detail?.status_ethix}
              />
            </div>
            <div className="col-md-6">
              <TableInformation
                title="Detail Warehouse"
                value={detail?.warehouse_address}
              />
              <TableInformation
                title="Received by"
                value={detail?.received_by_name}
              />
            </div>
            <div className="col-md-12 mt-4">
              <Table
                rowSelection={rowSelection}
                dataSource={productNeedList}
                columns={[
                  {
                    title: "Received Number",
                    dataIndex: "received_number",
                    key: "received_number",
                  },
                  {
                    title: "Doc Number",
                    dataIndex: "do_number",
                    key: "do_number",
                    render: (text) => {
                      return (
                        <ModalDoNumber
                          initialValues={{
                            do_number_exist: text,
                            do_number: text,
                          }}
                          url={`/api/purchase/purchase-order/update/do_number/${detail?.id}`}
                          refetch={() => loadDetail()}
                        />
                      )
                    },
                  },
                  {
                    title: "Product",
                    dataIndex: "product_name",
                    key: "product_name",
                  },
                  {
                    title: "Qty",
                    dataIndex: "qty",
                    key: "qty",
                  },
                  {
                    title: "Total TAX",
                    dataIndex: "tax_product_received",
                    key: "tax_product_received",
                    render: (text) => {
                      return formatNumber(text, "Rp ")
                    },
                  },
                  {
                    title: "Harga Satuan",
                    dataIndex: "harga_satuan",
                    key: "harga_satuan",
                    render: (text) => {
                      return formatNumber(text, "Rp ")
                    },
                  },
                  {
                    title: "Qty Diterima",
                    dataIndex: "qty_diterima",
                    key: "qty_diterima",
                  },
                  {
                    title: "Notes",
                    dataIndex: "notes",
                    key: "notes",
                  },
                  {
                    title: "Status Ethix",
                    dataIndex: "status_ethix",
                    key: "status_ethix",
                    render: (text, record) => {
                      if (text == 1) {
                        return <i class="fa fa-check"></i>
                      } else {
                        return (
                          <CloseCircleOutlined style={{ color: "#FE3A30" }} />
                        )
                      }
                    },
                  },
                  inArray(getItem("role"), [
                    "admin",
                    "adminsales",
                    "finance",
                    "superadmin",
                  ]) && {
                    title: "Subtotal",
                    dataIndex: "subtotal_product_received",
                    key: "subtotal_product_received",
                    render: (text, record) => {
                      return formatNumber(text, "Rp ")
                    },
                  },
                  {
                    title: "Action",
                    dataIndex: "action",
                    key: "action",
                    render: (text, record) => {
                      const isInvoiced = record.invoice_entry == 1
                      const isInvoice = record.invoice_entry > 0
                      if (isInvoiced) {
                        return <Tag color="green">Invoice</Tag>
                      }
                      return (
                        <Dropdown.Button
                          style={{
                            left: -16,
                          }}
                          // icon={<MoreOutlined />}
                          overlay={
                            <Menu itemIcon={<RightOutlined />}>
                              <ModalPenerimaan
                                refetch={() => loadDetail()}
                                products={detail.items}
                                url={`/api/purchase/purchase-order/product/update/${record.id}`}
                                initialValues={record}
                                update
                                detail={detail}
                              />

                              {inArray(getItem("role"), [
                                "finance",
                                "purchasing",
                                "superadmin",
                              ]) &&
                                (isInvoice ? (
                                  inArray(getItem("role"), [
                                    "lead_finance",
                                  ]) && (
                                    <Menu.Item
                                      icon={<SaveOutlined />}
                                      onClick={() =>
                                        handleInsertInvoice({
                                          invoice_entry: 0,
                                          item_id: [record.id],
                                        })
                                      }
                                    >
                                      Cancel Invoice
                                    </Menu.Item>
                                  )
                                ) : (
                                  <Menu.Item
                                    icon={<SaveOutlined />}
                                    onClick={() =>
                                      handleInsertInvoice({
                                        invoice_entry: 2,
                                        item_id: [record.id],
                                      })
                                    }
                                  >
                                    Insert Invoice
                                  </Menu.Item>
                                ))}
                              {record?.can_cancel && (
                                <Popconfirm
                                  title="Yakin hapus data ini?"
                                  onConfirm={() => deleteProductItem(record.id)}
                                  // onCancel={cancel}
                                  okText="Ya, Hapus"
                                  cancelText="Batal"
                                >
                                  <Menu.Item icon={<DeleteOutlined />}>
                                    <span>Hapus</span>
                                  </Menu.Item>
                                </Popconfirm>
                              )}
                            </Menu>
                          }
                        ></Dropdown.Button>
                      )
                    },
                  },
                ].filter((column) => {
                  if (column.dataIndex) {
                    if (isWarehouse) {
                      return !inArray(column.dataIndex, [
                        "tax_invoice",
                        "harga_satuan",
                        "subtotal_qty_diterima",
                        "action",
                      ])
                    }
                  }

                  return column
                })}
                loading={loading}
                pagination={false}
                rowKey="id"
                scroll={{ x: "max-content" }}
                tableLayout={"auto"}
                summary={(pageData) => {
                  if (isWarehouse) {
                    if (productNeedList.length > 0) {
                      return (
                        <>
                          <Table.Summary.Row>
                            <Table.Summary.Cell />

                            <Table.Summary.Cell />
                            <Table.Summary.Cell />

                            <Table.Summary.Cell>Total Qty</Table.Summary.Cell>
                            <Table.Summary.Cell>
                              {detail?.total_qty_diterima}
                            </Table.Summary.Cell>
                          </Table.Summary.Row>
                        </>
                      )
                    }
                  }
                  if (productNeedList.length > 0) {
                    return (
                      <>
                        <Table.Summary.Row>
                          <Table.Summary.Cell />
                          <Table.Summary.Cell />
                          <Table.Summary.Cell />
                          <Table.Summary.Cell />
                          <Table.Summary.Cell />
                          <Table.Summary.Cell>Total Qty</Table.Summary.Cell>
                          <Table.Summary.Cell>
                            {detail?.total_qty_diterima}
                          </Table.Summary.Cell>
                        </Table.Summary.Row>
                      </>
                    )
                  }
                }}
              />
            </div>
          </div>
        </Card>
      </div>

      {isFinance && (
        <div className="card p-4">
          <Card title={"Invoice entry"}>
            <div className="row">
              <div className="col-md-12 mt-4">
                <Table
                  dataSource={productInvoiceEntrys}
                  columns={[
                    {
                      title: "Vendor Doc Number",
                      dataIndex: "vendor_doc_number",
                      key: "vendor_doc_number",
                      render: (text, record, index) => {
                        const isInvoiced = record.invoice_entry == 1 && text
                        if (isInvoiced) {
                          return text
                        }
                        return (
                          <Input
                            onChange={(e) => {
                              const products = [...productInvoiceEntrys]
                              products[index]["vendor_doc_number"] =
                                e.target.value.replace(/ /g, "")

                              setProductNeed(products)
                            }}
                          />
                        )
                      },
                    },
                    {
                      title: "Invoice Date",
                      dataIndex: "invoice_date",
                      key: "invoice_date",
                      render: (text, record, index) => {
                        const isInvoiced = record.invoice_entry == 1 && text
                        if (isInvoiced) {
                          return formatDate(text)
                        }
                        return (
                          <DatePicker
                            onChange={(date, dateString) => {
                              const products = [...productInvoiceEntrys]
                              products[index]["invoice_date"] = dateString

                              setProductNeed(products)
                            }}
                          />
                        )
                      },
                    },
                    {
                      title: "Due Date",
                      dataIndex: "due_date",
                      key: "due_date",
                      render: (text) => formatDate(text),
                    },
                    {
                      title: "Confirm by",
                      dataIndex: "confirm_by_name",
                      key: "confirm_by_name",
                    },
                    {
                      title: "Product",
                      dataIndex: "product_name",
                      key: "product_name",
                    },
                    {
                      title: "Qty",
                      dataIndex: "qty_diterima",
                      key: "qty_diterima",
                    },
                    // {
                    //   title: "Harga Satuan",
                    //   dataIndex: "prices",
                    //   key: "prices",
                    //   render: (text) => {
                    //     return formatNumber(text, "Rp ")
                    //   },
                    // },
                    // {
                    //   title: "Total TAX",
                    //   dataIndex: "tax_subtotal",
                    //   key: "tax_subtotal",
                    //   render: (text) => {
                    //     return formatNumber(text, "Rp ")
                    //   },
                    // },
                    {
                      title: "Subtotal",
                      dataIndex: "total_invoice",
                      key: "total_invoice",
                      render: (text, record) => {
                        return formatNumber(text, "Rp ")
                      },
                    },
                    {
                      title: "Action",
                      dataIndex: "action",
                      key: "action",
                      render: (text, record) => {
                        if (record.invoice_entry == 2) {
                          return (
                            <button
                              onClick={() => {
                                if (!record.vendor_doc_number) {
                                  return toast.error(
                                    "Masukkan Vendor Doc Number"
                                  )
                                }
                                return handleInsertInvoice({
                                  vendor_doc_number: record.vendor_doc_number,
                                  invoice_date: record.invoice_date,
                                  invoice_entry: 1,
                                  item_id: [record.id],
                                  uid_invoice: record.uid_invoice,
                                })
                              }}
                              className="mr-4 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
                              title="Reject"
                            >
                              <CheckOutlined />
                            </button>
                          )
                        }

                        if (record.invoice_entry == 1) {
                          return <Tag color="green">Invoiced</Tag>
                        }
                      },
                    },
                  ]}
                  loading={loading}
                  pagination={false}
                  rowKey="id"
                  scroll={{ x: "max-content" }}
                  tableLayout={"auto"}
                  summary={(pageData) => {
                    if (productInvoiceEntrys.length > 0) {
                      return (
                        <>
                          <Table.Summary.Row>
                            <Table.Summary.Cell index={0}></Table.Summary.Cell>
                            <Table.Summary.Cell index={1}></Table.Summary.Cell>
                            <Table.Summary.Cell index={2}></Table.Summary.Cell>
                            <Table.Summary.Cell index={3}></Table.Summary.Cell>
                            <Table.Summary.Cell index={4}></Table.Summary.Cell>
                            <Table.Summary.Cell index={5}></Table.Summary.Cell>
                            <Table.Summary.Cell index={6}>
                              Total Qty
                            </Table.Summary.Cell>
                            <Table.Summary.Cell index={7}>
                              {detail?.total_qty_invoice}
                            </Table.Summary.Cell>
                          </Table.Summary.Row>
                          <Table.Summary.Row>
                            <Table.Summary.Cell index={0}></Table.Summary.Cell>
                            <Table.Summary.Cell index={1}></Table.Summary.Cell>
                            <Table.Summary.Cell index={2}></Table.Summary.Cell>
                            <Table.Summary.Cell index={3}></Table.Summary.Cell>
                            <Table.Summary.Cell index={4}></Table.Summary.Cell>
                            <Table.Summary.Cell index={5}></Table.Summary.Cell>
                            <Table.Summary.Cell index={6}>
                              Total
                            </Table.Summary.Cell>
                            <Table.Summary.Cell index={7}>
                              {formatNumber(
                                detail?.total_invoice_amount,
                                "Rp "
                              )}
                            </Table.Summary.Cell>
                          </Table.Summary.Row>
                        </>
                      )
                    }
                  }}
                />
              </div>
            </div>
          </Card>
        </div>
      )}

      {canAddPayment && (
        <div className="card mt-6 p-4">
          <Card
            title="Informasi Pembayaran"
            extra={
              <>
                <PurchaseBillingModal
                  refetch={() => loadDetail()}
                  detail={detail}
                />
              </>
            }
          >
            <Table
              dataSource={detail?.billings}
              columns={[
                ...purchaseBillingListColumn,
                {
                  title: "Action",
                  dataIndex: "action",
                  key: "action",
                  render: (text, record) => {
                    if (record.status === "0") {
                      return (
                        <div>
                          <Popconfirm
                            title="Yakin akan reject data ini?"
                            onConfirm={() =>
                              handleUpdateBilling(record.id, "reject")
                            }
                            okText="Ya, Reject"
                            cancelText="Batal"
                          >
                            <button className="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mr-2">
                              <CloseOutlined />
                            </button>
                          </Popconfirm>

                          <button
                            onClick={() =>
                              handleUpdateBilling(record.id, "approve")
                            }
                            className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
                          >
                            <CheckOutlined />
                          </button>
                        </div>
                      )
                    } else if (record.status === "1") {
                      return <Tag color="green">Approved</Tag>
                    } else if (record.status === "2") {
                      return <Tag color="red">Rejected</Tag>
                    }
                  },
                },
              ]}
              loading={loading}
              pagination={false}
              rowKey="id"
              scroll={{ x: "max-content" }}
              tableLayout={"auto"}
              summary={(pageData) => {
                if (detail?.billings?.length > 0) {
                  return (
                    <>
                      <Table.Summary.Row>
                        <Table.Summary.Cell index={0}></Table.Summary.Cell>
                        <Table.Summary.Cell index={1}></Table.Summary.Cell>
                        <Table.Summary.Cell index={2}></Table.Summary.Cell>
                        <Table.Summary.Cell index={3}></Table.Summary.Cell>
                        <Table.Summary.Cell index={4}></Table.Summary.Cell>
                        <Table.Summary.Cell index={5}></Table.Summary.Cell>
                        <Table.Summary.Cell index={6}></Table.Summary.Cell>
                        <Table.Summary.Cell index={7}></Table.Summary.Cell>
                        <Table.Summary.Cell index={7}></Table.Summary.Cell>
                        <Table.Summary.Cell index={8}>
                          Total Tax
                        </Table.Summary.Cell>
                        <Table.Summary.Cell index={9}>
                          {detail?.total_tax}
                        </Table.Summary.Cell>
                      </Table.Summary.Row>
                      <Table.Summary.Row>
                        <Table.Summary.Cell index={0}></Table.Summary.Cell>
                        <Table.Summary.Cell index={1}></Table.Summary.Cell>
                        <Table.Summary.Cell index={2}></Table.Summary.Cell>
                        <Table.Summary.Cell index={3}></Table.Summary.Cell>
                        <Table.Summary.Cell index={4}></Table.Summary.Cell>
                        <Table.Summary.Cell index={5}></Table.Summary.Cell>
                        <Table.Summary.Cell index={6}></Table.Summary.Cell>
                        <Table.Summary.Cell index={7}></Table.Summary.Cell>
                        <Table.Summary.Cell index={7}></Table.Summary.Cell>
                        <Table.Summary.Cell index={8}>Total</Table.Summary.Cell>
                        <Table.Summary.Cell index={9}>
                          {formatNumber(detail?.total_approved)}
                        </Table.Summary.Cell>
                      </Table.Summary.Row>
                    </>
                  )
                }
              }}
            />
          </Card>
        </div>
      )}

      <div className="card p-4">
        <Card title={"Ethix"}>
          <div className="row">
            <div className="col-md-12 mt-4">
              <Table
                dataSource={detail?.ethix_items || []}
                columns={ethixColumns}
                loading={loading}
                pagination={false}
                rowKey="id"
                scroll={{ x: "max-content" }}
                tableLayout={"auto"}
              />
            </div>
          </div>
        </Card>
      </div>

      {isWarehouse && (
        <div className="card flex-row p-3 justify-between  ">
          <div>
            <div className="flex items-center md:col-start-1 md:col-end-3">
              <Checkbox
                type={"checkbox"}
                className="accent-mainColor mr-2"
                checked={isStockOpname}
              />
              <span className="font-light">
                Purchase order ini menerapkan stock opname
              </span>
            </div>
          </div>
          <div></div>
        </div>
      )}

      {canUpdateStatus && (
        <div className="card items-end p-3">
          <div>
            <button
              onClick={() => {
                if (detail?.total_approved < detail?.subtotal) {
                  return toast.error("Nominal pembayaran belum sesuai")
                }
                return handleComplete()
              }}
              className={`ml-4 w-30 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center`}
            >
              <span>Complete</span>
            </button>
          </div>
        </div>
      )}
    </>
  )
}

export default PurchaseOrderDetail
