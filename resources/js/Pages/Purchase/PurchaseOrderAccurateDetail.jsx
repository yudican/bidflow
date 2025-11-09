import {
  CheckCircleOutlined,
  CheckOutlined,
  CloseCircleOutlined,
  CloseOutlined,
  DownOutlined,
  LoadingOutlined,
  PrinterTwoTone,
  WarningFilled,
} from "@ant-design/icons"
import {
  Button,
  Card,
  Checkbox,
  Dropdown,
  Form,
  Menu,
  Popconfirm,
  Table,
  Tag,
  Tooltip,
} from "antd"
import axios from "axios"
import moment from "moment"
import React, { useEffect, useState } from "react"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import ModalDoNumber from "../../components/Modal/ModalDoNumber"
import Layout from "../../components/layout"
import { formatDate, formatDateTime, formatNumber, getItem, inArray } from "../../helpers"
import ModalTax from "../Genie/Components/ModalTax"
import ModalNotes from "./Components/ModalNotes"
import ModalPenerimaan from "./Components/ModalPenerimaan"
import RejectModal from "./Components/RejectModal"
import TableAddProductWithSummary from "./Components/TableAddProductWithSummary"
import {
  ethixColumns,
  purchaseBillingListColumn,
  renderStatusComponent,
} from "./config"

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
  const [productNeed, setProductNeed] = useState([])
  const [loading, setLoading] = useState(false)
  const [loadingApprove, setLoadingApprove] = useState(false)
  const [detail, setDetail] = useState(null)
  const [stockOpname, setStockOpname] = useState(false)
  const [selectedRowKeys, setSelectedRowKeys] = useState([])
  const [loadingSubmit, setLoadingSubmit] = useState(false)
  const [loadingSite, setLoadingSite] = useState(false)

  const selectedProducts =
    detail?.items?.map((row, index) => {
      return {
        key: index,
        item_id: row.id,
        po_id: detail?.id,
        product_name: row.product_name,
        sku: row.sku,
        po_number: detail?.po_number,
        received_number: row.received_number,
        do_number: row.do_number,
        gp_received_number: row.gp_received_number,
      }
    }) || []

  const loadDetail = () => {
    setLoading(true);
    axios
      .get(`/api/purchase/purchase-order-accurate/${purchase_order_id}`)
      .then((res) => {
        const { data } = res.data || {};

        console.log(data);

        if (!data) {
          setLoading(false);
          return;
        }

        setDetail(data);

        const items = data?.items?.map((item, index) => ({
          ...item,
          key: index,
          id: item?.id || "-",
          product: item?.product || "-",
          sku: item?.sku || "-",
          satuan: item?.satuan_barang || "-",
          qty: item?.qty || 0,
        })) || [];

        setLoading(false);
      })
      .catch((e) => {
        console.error("Error fetching purchase order details:", e);
        setLoading(false);
      });
  };

  const approvePurchaseOrder = (stock_opname = false) => {
    setLoadingApprove(true)
    axios
      .post(`/api/purchase/purchase-order/approve/${purchase_order_id}`, {
        status: 1,
        stock_opname,
      })
      .then((res) => {
        setLoadingApprove(false)
        toast.success("Purchase Order berhasil di Approve!", {
          position: toast.POSITION.TOP_RIGHT,
        })
        loadDetail()
      })
      .catch((err) => {
        setLoadingApprove(false)

        const errorMessage = err.response?.data?.message || "Approve gagal"
        toast.error(errorMessage, {
          position: toast.POSITION.TOP_RIGHT,
        })
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

  const handleSubmitGp = (value) => {
    setLoadingSubmit(true)
    axios
      .post(`/api/po/order/submit`, {
        ids: [purchase_order_id],
        type: "purchase-order",
        ...value,
        products: selectedProducts,
      })
      .then((res) => {
        toast.success("Purchase Order berhasil di submit")
        setSelectedRowKeys([])
        setLoadingSubmit(false)
      })
      .catch((e) => {
        const { message } = e.response?.data
        setLoadingSubmit(false)
        toast.error(message || "Error submitting purchase order")
      })
  }

  const handleSubmitReceivingGp = (value) => {
    setLoadingSubmit(true)
    axios
      .post(`/api/receiving/po/submit`, {
        ids: [purchase_order_id],
        type: "receiving-purchase-order",
        ...value,
        products: selectedProducts,
      })
      .then((res) => {
        toast.success("Purchase Order berhasil di submit")
        setSelectedRowKeys([])
        setLoadingSubmit(false)
      })
      .catch((e) => {
        setLoadingSubmit(false)
        toast.error("Error submitting purchase order")
      })
  }

  const handleSubmitRetrigger = () => {
    setLoadingSubmit(true)
    axios
      .get(`/api/purchase-order/resubmit-ethix/${purchase_order_id}`)
      .then((res) => {
        const { data } = res.data
        setLoadingSubmit(false)
        toast.success("Purchase Order berhasil di submit ulang")
      })
      .catch((err) => {
        setLoadingSubmit(false)
        toast.success("Purchase Order gagal di submit ulang")
      })
  }

  const loadSite = () => {
    setLoadingSite(true)
    axios
      .get("/api/master/site")
      .then((res) => {
        setSite(res.data.data)
        setLoadingSite(false)
      })
      .catch((err) => setLoadingSite(false))
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
      disabled: inArray(record.status_gp, [1]), // Column configuration not to be checked
    }),
  }

  const role = getItem("role")
  const isWaitingApproval = detail?.status === "5"
  const isFinance = inArray(role, ["finance", "lead_finance", "superadmin", "purchasing"])
  const isWarehouse = inArray(role, ["warehouse"])
  const isDelivery = inArray(detail?.status, ["2", "3", "9"])
  const canAddPayment = isFinance && isDelivery
  const canApprove = isFinance && isWaitingApproval
  const canAcceptProduct =
    isWarehouse && inArray(detail?.status, ["1", "2", "3", "4", "9"])
  const canUpdateStatus = isFinance && isDelivery
  const isStockOpname = detail?.stock_opname > 0 ? true : false
  const canResubmitEthix =
    detail?.status_ethix_submit == "needsubmited" && detail?.status == "1"
  const canPrintPdf = inArray(role, [
    "finance",
    "purchasing",
    "superadmin",
    "lead_finance",
  ])

  const menu = (
    <Menu>
      <Menu.Item>
        <ModalTax
          handleSubmit={(e) => handleSubmitGp(e)}
          products={selectedProducts}
          onChange={() => console.log("ok")}
          type={"po"}
          title={"Submit PO"}
        />
      </Menu.Item>
      <Menu.Item>
        <ModalTax
          handleSubmit={(e) => handleSubmitReceivingGp(e)}
          products={selectedProducts.filter(
            (item) => item.do_number && !item.gp_received_number
          )}
          onChange={() => console.log("ok")}
          type={"receiving"}
          title={"Submit Receiving"}
        />
      </Menu.Item>
      {canResubmitEthix && (
        <Menu.Item>
          <ModalTax
            handleSubmit={(e) => handleSubmitRetrigger(e)}
            onChange={() => console.log("ok")}
            title={"Retrigger Submit PO to Ethix"}
          />
        </Menu.Item>
      )}
    </Menu>
  )

  const rightContent = (
    <div className="flex items-center">
      {!isWaitingApproval && (
        <Dropdown overlay={menu}>
          <button
            className="text-blue-700 border-[1px] border-blue-700 hover:bg-blue-800/10 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mx-2"
            onClick={(e) => e.preventDefault()}
          >
            <span className="mr-2">More Option</span>
            <DownOutlined />
          </button>
        </Dropdown>
      )}

      {canApprove ? (
        <>
          <RejectModal
            refetch={() => loadDetail()}
            url={`/api/purchase/purchase-order/reject/${purchase_order_id}`}
          />
          <Popconfirm
            title={
              detail?.type_po === "Perlengkapan"
                ? "Apakah anda yakin ingin approve data ini?"
                : "Terapkan stock opname?"
            }
            onConfirm={() => approvePurchaseOrder(true)}
            onCancel={() => {
              if (detail?.type_po === "Perlengkapan") {
                return null
              }

              return approvePurchaseOrder()
            }}
            okText="Ya"
            cancelText="Tidak"
          >
            <button
              className="text-white bg-greenApproveColor hover:bg-greenApproveColor/80 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
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

  console.log("product need : ", productNeed)

  const productNeedList = productNeed.filter((product) => product.status == 1) || []
  const productInvoiceEntrys = detail?.invoice_entrys || []
  const productBarcode = detail?.barcodes || []
  const requisition = detail?.requisitions || []
  const purchaseSubmit = detail?.barcodelogs || [];



  return (
    <Layout
      title={"Proses Data Purchase Order | " + detail?.pr_type}
      href="/purchase/purchase-order"
      rightContent={rightContent}
      lastItemLabel={detail?.po_number}
    >
      <Card
        title="Informasi Purchase Order"
        extra={
          <div className="flex items-center">
            <div className="flex justify-end items-center">
              <strong className="mr-2">Status :</strong>
              {renderStatusComponent(detail?.status)}
            </div>

            <Tooltip title={"Print PO"}>
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
            </Tooltip>
          </div>
        }
      >
        <div className="card-body grid md:grid-cols-3 gap-4">
          <TableInformation title="PO Number" value={detail?.po_number} />
          <TableInformation
            title="GP PO Number"
            value={detail?.gp_po_number || "-"}
          />
          <TableInformation title="Company" value={detail?.company_name} />
          <TableInformation title="Vendor Code" value={detail?.vendor_code} />
          <TableInformation title="Vendor Name" value={detail?.vendor_name} />
          <TableInformation title="Tipe PO" value={detail?.type_po} />
          <TableInformation
            title="Channel Dist"
            value={
              detail?.channel === "sales-offline"
                ? "Sales Offline"
                : detail?.channel === "sales-online"
                  ? "Sales Online"
                  : detail?.channel === "sales-offline,sales-online" || detail?.channel === "sales-online,sales-offline"
                    ? "Sales Offline, Sales Online"
                    : detail?.channel
            }
          />
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
            value={formatDate(detail?.created_at)}
          />
          <TableInformation
            title="Notes"
            value={<ModalNotes value={detail?.note_purchase || "-"} />}
          />
          <TableInformation
            title="Memiliki Barcode"
            value={detail?.has_barcode == 1 ? 'Ya' : 'Tidak'}
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

          {detail && (
            <div className="lg:col-span-3 mt-6 border-t-2 pt-5">
              <TableAddProductWithSummary
                detail={detail}
                data={productNeed.filter((item) => item.is_master > 0)}
                refetch={() => loadDetail()}
              />
            </div>
          )}
        </div>
      </Card>

      <div className="card mt-6 p-4">
        <Card
          title={
            <div>
              <span className="mr-4">
                Informasi Pengiriman Barang dari Supplier
              </span>
              {isStockOpname && <Tag color="gold">Stock Opname</Tag>}
            </div>
          }
          extra={
            <div className="flex justify-end items-center">
              {canAcceptProduct && (
                <ModalPenerimaan
                  refetch={() => loadDetail()}
                  products={
                    detail?.items?.filter((item) => item.is_master > 0) || []
                  }
                  url={`/api/purchase/purchase-order/product/add/${detail?.id}`}
                  detail={detail}
                />
              )}
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
            </div>
            <div className="col-md-6">
              {/* <TableInformation
                title="Status Ethix"
                value={
                  detail?.status_ethix_name
                    ? detail?.status_ethix_name
                    : "Item Belum Diterima"
                }
              />
              <TableInformation
                title="Status FIS"
                value={
                  detail?.total_qty_diterima == detail?.qty_total &&
                  detail?.total_qty_diterima > 0
                    ? "Item Sudah Diterima"
                    : "Item Belum Diterima"
                }
              /> */}
              <TableInformation
                title="Detail Warehouse"
                value={detail?.warehouse_address}
              />
            </div>
            <div className="col-md-12 mt-4">
              <Table
                dataSource={productNeedList}
                columns={[
                  {
                    title: "Received Number",
                    dataIndex: "received_number",
                    key: "received_number",
                  },
                  {
                    title: "GP Received Number",
                    dataIndex: "gp_received_number",
                    key: "gp_received_number",
                    render: (text) => text || "-",
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
                    title: "Item",
                    dataIndex: "product_name",
                    key: "product_name",
                  },
                  {
                    title: "Qty",
                    dataIndex: "qty",
                    key: "qty",
                  },
                  // {
                  //   title: "Total TAX",
                  //   dataIndex: "tax_product_received",
                  //   key: "tax_product_received",
                  //   render: (text) => {
                  //     return formatNumber(text, "Rp ")
                  //   },
                  // },
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
                    title: "Received by",
                    dataIndex: "received_by",
                    key: "received_by",
                    render: (text) => {
                      return detail?.received_by_name
                    },
                  },
                  {
                    title: "Received Date",
                    dataIndex: "received_date",
                    key: "received_date",
                    render: (text) => {
                      return moment(text).format("DD-MM-YYYY")
                    },
                  },
                  {
                    title: "Status Ethix",
                    dataIndex: "status_ethix",
                    key: "status_ethix",
                    align: "center",
                    render: (text, record) => {
                      if (detail?.status_ethix_name == "Sudah Diterima") {
                        return (
                          <CheckCircleOutlined style={{ color: "#7C9B3A" }} />
                        )
                      } else {
                        if (text) {
                          return (
                            <CheckCircleOutlined style={{ color: "#7C9B3A" }} />
                          )
                        } else {
                          return (
                            <CloseCircleOutlined style={{ color: "#FE3A30" }} />
                          )
                        }
                      }
                    },
                  },
                  {
                    title: "Status GP",
                    dataIndex: "status_gp",
                    key: "status_gp",
                    align: "center",
                    render: (text, record) => {
                      if (text == "submited") {
                        return (
                          <CheckCircleOutlined style={{ color: "#7C9B3A" }} />
                        )
                      } else {
                        return (
                          <CloseCircleOutlined style={{ color: "#FE3A30" }} />
                        )
                      }
                    },
                  },
                  {
                    title: "Status Alokasi",
                    dataIndex: "is_allocated",
                    key: "is_allocated",
                    align: "center",
                    render: (text, record) => {
                      if (text > 0) {
                        return (
                          <CheckCircleOutlined style={{ color: "#7C9B3A" }} />
                        )
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
                            <Table.Summary.Cell colSpan={4} align="right">
                              <strong>Total Qty</strong>
                            </Table.Summary.Cell>
                            <Table.Summary.Cell colSpan={2}>
                              <strong>{detail?.total_qty_diterima}</strong>
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
                          <Table.Summary.Cell align="right" colSpan={12}>
                            <strong>Total Qty</strong>
                          </Table.Summary.Cell>
                          <Table.Summary.Cell colSpan={2} align="right">
                            <strong>{detail?.total_qty_diterima}</strong>
                          </Table.Summary.Cell>
                        </Table.Summary.Row>
                        <Table.Summary.Row>
                          <Table.Summary.Cell align="right" colSpan={12}>
                            Total Tax
                          </Table.Summary.Cell>
                          <Table.Summary.Cell colSpan={2} align="right">
                            {formatNumber(detail?.tax_amount, "Rp. ")}
                          </Table.Summary.Cell>
                        </Table.Summary.Row>
                        <Table.Summary.Row>
                          <Table.Summary.Cell align="right" colSpan={12}>
                            Total
                          </Table.Summary.Cell>
                          <Table.Summary.Cell colSpan={2} align="right">
                            {formatNumber(detail?.total_amount, "Rp. ")}
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
      {(detail?.pr_type != 'PR' && detail?.has_barcode == 1) && (
        <div className="card mt-6 p-4">
          <Card title={"Log Submit Barcode"}>
            <div className="row">
              <div className="col-md-12 mt-4" style={{ maxHeight: "400px", overflowY: "auto" }}>
                <Table
                  dataSource={purchaseSubmit}
                  columns={[
                    {
                      title: "No.",
                      dataIndex: "number",
                      key: "number",
                      render: (value, row, index) => index + 1,
                    },
                    {
                      title: "Approved By",
                      dataIndex: "hit_user_name",
                      key: "hit_user_name",
                    },
                    {
                      title: "Hit Date",
                      dataIndex: "created_at",
                      key: "created_at",
                      render: (text) => {
                        return formatDateTime(text);
                      },
                    },
                    {
                      title: "Status",
                      dataIndex: "status",
                      key: "status",
                    },
                    {
                      title: "Description",
                      dataIndex: "description",
                      key: "description",
                    },
                  ]}
                  loading={loading}
                  pagination={false}
                  rowKey="id"
                  scroll={{ x: "max-content", y: 300 }} // Adjust the y value as needed
                  sticky
                  tableLayout={"auto"}
                />
              </div>
            </div>
          </Card>
        </div>
      )}
      {detail?.pr_type == 'PR' && (
        <div className="card mt-6 p-4">
          <Card title={"Log Barcode Purchase Requisition"}>
            <div className="row">
              <div className="col-md-12 mt-4" style={{ maxHeight: "400px", overflowY: "auto" }}>
                <Table
                  dataSource={productBarcode}
                  columns={[
                    {
                      title: "No.",
                      dataIndex: "number",
                      key: "number",
                      render: (value, row, index) => index + 1,
                    },
                    {
                      title: "Barcode",
                      dataIndex: "barcode",
                      key: "barcode",
                    },
                    {
                      title: "Item Name",
                      dataIndex: "item_name",
                      key: "item_name",
                    },
                    {
                      title: "Generate Date",
                      dataIndex: "generate_date",
                      key: "generate_date",
                      render: (text) => {
                        return formatDate(text);
                      },
                    },
                  ]}
                  loading={loading}
                  pagination={false}
                  rowKey="id"
                  scroll={{ x: "max-content", y: 300 }} // Adjust the y value as needed
                  sticky
                  tableLayout={"auto"}
                />
              </div>
            </div>
          </Card>
        </div>
      )}

      {detail?.pr_type == 'PR' && (
        <div className="card mt-6 p-4">
          <Card title={"Nomor Referensi Purchase Requisition"}>
            <div className="row">
              <div className="col-md-12 mt-4">
                <Table
                  dataSource={requisition}
                  columns={[
                    {
                      title: "No.",
                      dataIndex: "number",
                      key: "number",
                      render: (value, row, index) => index + 1,
                    },
                    {
                      title: "PR Number",
                      dataIndex: "pr_number",
                      key: "pr_number",
                    },
                  ]}
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
      )}


      {isFinance && (
        <div className="card mt-6 p-4">
          <Card title={"Log Invoice entry"}>
            <div className="row">
              <div className="col-md-12 mt-4">
                <Table
                  dataSource={productInvoiceEntrys}
                  columns={[
                    {
                      title: "Vendor Doc Number",
                      dataIndex: "vendor_doc_number",
                      key: "vendor_doc_number",
                      // render: (text, record, index) => {
                      //   const isInvoiced = record.invoice_entry == 1 && text
                      //   if (isInvoiced) {
                      //     return text
                      //   }
                      //   return (
                      //     <Input
                      //       onChange={(e) => {
                      //         const products = [...productInvoiceEntrys]
                      //         products[index]["vendor_doc_number"] =
                      //           e.target.value.replace(/ /g, "")

                      //         setProductNeed(products)
                      //       }}
                      //     />
                      //   )
                      // },
                    },
                    {
                      title: "Invoice Date",
                      dataIndex: "invoice_date",
                      key: "invoice_date",
                      // render: (text, record, index) => {
                      //   const isInvoiced = record.invoice_entry == 1 && text
                      //   if (isInvoiced) {
                      //     return formatDate(text)
                      //   }
                      //   return (
                      //     <DatePicker
                      //       onChange={(date, dateString) => {
                      //         const products = [...productInvoiceEntrys]
                      //         products[index]["invoice_date"] = dateString

                      //         setProductNeed(products)
                      //       }}
                      //     />
                      //   )
                      // },
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
                    // {
                    //   title: "Action",
                    //   dataIndex: "action",
                    //   key: "action",
                    //   render: (text, record) => {
                    //     if (record.invoice_entry == 2) {
                    //       return (
                    //         <button
                    //           onClick={() => {
                    //             if (!record.vendor_doc_number) {
                    //               return toast.error(
                    //                 "Masukkan Vendor Doc Number"
                    //               )
                    //             }
                    //             return handleInsertInvoice({
                    //               vendor_doc_number: record.vendor_doc_number,
                    //               invoice_date: record.invoice_date,
                    //               invoice_entry: 1,
                    //               item_id: [record.id],
                    //               uid_invoice: record.uid_invoice,
                    //             })
                    //           }}
                    //           className="mr-4 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
                    //           title="Reject"
                    //         >
                    //           <CheckOutlined />
                    //         </button>
                    //       )
                    //     }

                    //     if (record.invoice_entry == 1) {
                    //       return <Tag color="green">Invoiced</Tag>
                    //     }
                    //   },
                    // },
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
                            <Table.Summary.Cell colSpan={6} align="right">
                              <strong>Total Qty</strong>
                            </Table.Summary.Cell>
                            <Table.Summary.Cell>
                              <strong>{detail?.total_qty_invoice}</strong>
                            </Table.Summary.Cell>
                            <Table.Summary.Cell />
                          </Table.Summary.Row>
                          <Table.Summary.Row>
                            <Table.Summary.Cell colSpan={6} align="right">
                              <strong>Total</strong>
                            </Table.Summary.Cell>
                            <Table.Summary.Cell>
                              <strong>
                                {formatNumber(
                                  detail?.total_invoice_amount,
                                  "Rp "
                                )}
                              </strong>
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
            title="Log Informasi Pembayaran"
          // extra={
          //   <>
          //     <PurchaseBillingModal
          //       refetch={() => loadDetail()}
          //       detail={detail}
          //     />
          //   </>
          // }
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

      <div className="card mt-6 p-4">
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
    </Layout>
  )
}

export default PurchaseOrderDetail
