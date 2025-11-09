import { CheckOutlined, CloseOutlined } from "@ant-design/icons"
import { Button, Card, Popconfirm, Table, Tag } from "antd"
import axios from "axios"
import moment from "moment"
import React, { useEffect, useState } from "react"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../components/layout"
import { formatNumber, getItem, inArray } from "../../helpers"
import PurchaseBillingModal from "../Purchase/Components/PurchaseBillingModal"
import { purchaseBillingListColumn, renderStatusComponent } from "./config"

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

const PurchaseInvoiceEntryDetail = () => {
  const navigate = useNavigate()
  const { purchase_invoice_entry_id } = useParams()
  const [loading, setLoading] = useState(false)
  const [detail, setDetail] = useState({})
  const [loadingSubmit, setLoadingSubmit] = useState(false)
  const [loadingApprove, setLoadingApprove] = useState(false)
  const [selectedPoNumber, setSelectedPoNumber] = useState(null)
  const [checkBooks, setCheckbooks] = useState([])

  const loadDetail = () => {
    setLoading(true)
    axios
      .get(`/api/purchase/invoice-entry/${purchase_invoice_entry_id}`)
      .then((res) => {
        const { data } = res.data
        setLoading(false)
        setDetail(data)
        console.log(data)
      })
      .catch((e) => setLoading(false))
  }

  const loadCheckbooks = () => {
    axios.get("/api/general/checkbook").then((res) => {
      setCheckbooks(res.data.data)
    })
  }

  useEffect(() => {
    loadDetail()
    loadCheckbooks()
  }, [])

  const updateStatusEntry = () => {
    axios
      .post("/api/purchase/invoice-entry/update/status", {
        status: 1,
        purchase_invoice_entry_id,
      })
      .then((res) => {
        loadDetail()
        setLoadingSubmit(false)
        toast.success("Data berhasil disimpan")
      })
      .catch((err) => {
        setLoadingSubmit(false)
        // need detailing alert
        toast.error("Data gagal disimpan")
      })
  }

  const handleUpdateBilling = (billing_id, type) => {
    setLoadingApprove(true)
    axios
      .post(`/api/purchase/purchase-order/billing/${type}/${billing_id}`, {
        purchase_invoice_entry_id,
      })
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

  const handleSaveBilling = (value) => {
    setLoadingApprove(true)
    axios
      .post(`/api/purchase/invoice-entry/add-billing`, {
        ...value,
        purchase_invoice_entry_id,
        received_number: detail?.received_number,
        purchase_order_id: selectedPoNumber,
      })
      .then((res) => {
        setLoadingApprove(false)
        toast.success("Billing berhasil disimpan")
        loadDetail()
      })
      .catch((err) => {
        setLoadingApprove(false)
        toast.error("Billing gagal disimpan")
      })
  }

  const rightContent = null

  const role = getItem("role")
  const canCreate =
    inArray(detail?.status, ["-", "0"]) &&
    inArray(role, ["finance", "admin", "superadmin", "adminsales"])

  return (
    <>
      <Layout
        title="Detail Data Invoice Entry"
        href="/purchase/invoice-entry"
        rightContent={rightContent}
      >
        <Card
          title="Informasi Data Invoice Entry"
          extra={
            <div className="flex items-center">
              <div className="flex justify-end items-center">
                <strong className="mr-2">Status :</strong>
                {renderStatusComponent(detail?.status)}
              </div>
            </div>
          }
        >
          <div className="card-body grid md:grid-cols-3 gap-4 p-0">
            <TableInformation
              title="Receipt Number"
              value={detail?.received_number}
            />

            <TableInformation
              title="Vendor Doc Number"
              value={detail?.vendor_doc_number}
            />
            <TableInformation
              title="Invoice Date"
              value={detail?.invoice_date}
            />
            <TableInformation
              title="GP Number"
              value={detail?.gp_invoice_number}
            />
            <TableInformation
              title="User Created"
              value={detail?.created_by_name}
            />
            <TableInformation title="Vendor Name" value={detail?.vendor_name} />
            <TableInformation title="Batch ID" value={detail?.batch_id} />
            <TableInformation
              title="Payment Term"
              value={detail?.payment_term_name}
            />
          </div>
        </Card>
      </Layout>

      <div className="card mt-6 p-4">
        <Card title="Detail Item">
          <Table
            dataSource={detail?.items}
            columns={[
              {
                title: "PO Number",
                dataIndex: "po_number",
                key: "po_number",
              },
              {
                title: "Received Number",
                dataIndex: "received_number",
                key: "received_number",
              },
              {
                title: "GP PO Number",
                dataIndex: "gp_po_number",
                key: "gp_po_number",
                render: (text) => {
                  return text || "-"
                },
              },
              {
                title: "GP Received Number",
                dataIndex: "gp_received_number",
                key: "gp_received_number",
                render: (text) => {
                  return text || "-"
                },
              },
              {
                title: "GP Payable Number",
                dataIndex: "gp_payable_number",
                key: "gp_payable_number",
                render: (text) => {
                  return text || "-"
                },
              },
              {
                title: "Status Payable GP",
                dataIndex: "status_payable_gp",
                key: "status_payable_gp",
                render: (text) => {
                  return text || "-"
                },
              },
              {
                title: "Received Date",
                dataIndex: "received_date",
                key: "received_date",
                render: (text, record) => {
                  return moment(text || record?.created_at).format("DD-MM-YYYY")
                },
              },
              {
                title: "Product Name",
                dataIndex: "product_name",
                key: "product_name",
              },
              {
                title: "UOM",
                dataIndex: "uom",
                key: "uom",
              },
              {
                title: "SKU",
                dataIndex: "sku",
                key: "sku",
              },
              {
                title: "Qty",
                dataIndex: "qty",
                key: "qty",
              },
              {
                title: "Amount",
                dataIndex: "extended_cost",
                key: "extended_cost",
                align: "right",
                render: (text, record) => {
                  return formatNumber(text, "Rp ")
                },
              },
              // {
              //   title: "Total Amount",
              //   dataIndex: "extended_cost",
              //   key: "extended_cost",
              //   render: (text, record) => {
              //     let totalAmount = 0
              //     if (detail?.type_invoice == "jasa") {
              //       totalAmount = text * record.qty
              //     } else {
              //       totalAmount = record.qty * record.price
              //     }

              //     return formatNumber(totalAmount, "Rp ")
              //   },
              // },
            ]
              .filter((item) => {
                if (detail?.type_invoice == "jasa") {
                  return !inArray(item.key, ["po_number", "received_number"])
                }

                return item
              })
              .filter((item) => item)}
            loading={loading}
            pagination={false}
            rowKey="id"
            scroll={{ x: "max-content" }}
            tableLayout={"auto"}
            summary={(currentData) => {
              console.log(currentData, "current")
              let price = 0
              let tax = 0
              let total = 0
              if (detail?.type_invoice == "jasa") {
                price = currentData.reduce(
                  (acc, curr) =>
                    Number(acc) + Number(curr.extended_cost) * Number(curr.qty),
                  0
                )
                tax = currentData.reduce(
                  (acc, curr) => Number(acc) + Number(curr.ppn),
                  0
                )
                total = price + tax
              } else {
                price = currentData.reduce(
                  (acc, curr) =>
                    Number(acc) + Number(curr.price) * Number(curr.qty),
                  0
                )
                tax = currentData.reduce(
                  (acc, curr) => Number(acc) + Number(curr.ppn),
                  0
                )
                total = price + tax
              }

              return (
                <Table.Summary>
                  <Table.Summary.Row>
                    <Table.Summary.Cell colSpan={8} align="right">
                      Sub Total (Rp)
                    </Table.Summary.Cell>

                    <Table.Summary.Cell align="right">
                      {formatNumber(price, "Rp. ")}
                    </Table.Summary.Cell>
                    <Table.Summary.Cell />
                  </Table.Summary.Row>

                  <Table.Summary.Row>
                    <Table.Summary.Cell colSpan={8} align="right">
                      Tax (Rp)
                    </Table.Summary.Cell>

                    <Table.Summary.Cell align="right">
                      {formatNumber(tax, "Rp. ")}
                    </Table.Summary.Cell>
                    <Table.Summary.Cell />
                  </Table.Summary.Row>

                  <Table.Summary.Row>
                    <Table.Summary.Cell colSpan={8} align="right">
                      Total (Rp)
                    </Table.Summary.Cell>

                    <Table.Summary.Cell align="right">
                      {formatNumber(total, "Rp. ")}
                    </Table.Summary.Cell>
                    <Table.Summary.Cell />
                  </Table.Summary.Row>
                </Table.Summary>
              )
            }}
          />
        </Card>
      </div>

      <div className="card mt-6 p-4">
        <Card
          title="Informasi Pembayaran"
          extra={
            <>
              <PurchaseBillingModal
                // refetch={() => loadDetail()}
                detail={detail}
                handleFinish={(value) => handleSaveBilling(value)}
                receivedNumbers={detail?.items || []}
                type={detail?.type_invoice}
                onChangePoNumber={(value) => setSelectedPoNumber(value)}
                checkBooks={checkBooks}
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
                  if (record.status == "0") {
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
                          className="text-white bg-greenCheckColor hover:bg-greenCheckColor/80 focus:ring-4 focus:outline-none focus:ring-greenCheckbg-greenCheckColor/30 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
                        >
                          <CheckOutlined />
                        </button>
                      </div>
                    )
                  } else if (record.status == "1") {
                    return <Tag color="green">Approved</Tag>
                  } else if (record.status == "2") {
                    return <Tag color="red">Rejected</Tag>
                  }
                },
              },
            ]}
            loading={loading || loadingApprove}
            pagination={false}
            rowKey="id"
            scroll={{ x: "max-content" }}
            tableLayout={"auto"}
            // summary={(pageData) => {
            //   if (detail?.billings?.length > 0) {
            //     return (
            //       <>
            //         <Table.Summary.Row>
            //           <Table.Summary.Cell index={0}></Table.Summary.Cell>
            //           <Table.Summary.Cell index={1}></Table.Summary.Cell>
            //           <Table.Summary.Cell index={2}></Table.Summary.Cell>
            //           <Table.Summary.Cell index={3}></Table.Summary.Cell>
            //           <Table.Summary.Cell index={4}></Table.Summary.Cell>
            //           <Table.Summary.Cell index={5}></Table.Summary.Cell>
            //           <Table.Summary.Cell index={6}></Table.Summary.Cell>
            //           <Table.Summary.Cell index={7}></Table.Summary.Cell>
            //           <Table.Summary.Cell index={7}></Table.Summary.Cell>
            //           <Table.Summary.Cell index={8}>
            //             Total Tax
            //           </Table.Summary.Cell>
            //           <Table.Summary.Cell index={9}>
            //             {detail?.total_tax}
            //           </Table.Summary.Cell>
            //         </Table.Summary.Row>
            //         <Table.Summary.Row>
            //           <Table.Summary.Cell index={0}></Table.Summary.Cell>
            //           <Table.Summary.Cell index={1}></Table.Summary.Cell>
            //           <Table.Summary.Cell index={2}></Table.Summary.Cell>
            //           <Table.Summary.Cell index={3}></Table.Summary.Cell>
            //           <Table.Summary.Cell index={4}></Table.Summary.Cell>
            //           <Table.Summary.Cell index={5}></Table.Summary.Cell>
            //           <Table.Summary.Cell index={6}></Table.Summary.Cell>
            //           <Table.Summary.Cell index={7}></Table.Summary.Cell>
            //           <Table.Summary.Cell index={7}></Table.Summary.Cell>
            //           <Table.Summary.Cell index={8}>Total</Table.Summary.Cell>
            //           <Table.Summary.Cell index={9}>
            //             {formatNumber(detail?.total_approved)}
            //           </Table.Summary.Cell>
            //         </Table.Summary.Row>
            //       </>
            //     )
            //   }
            // }}
          />
          {canCreate && (
            <div className="col-md-12 mt-8">
              <div className="float-right">
                <button
                  className={`text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center`}
                  loading={loadingSubmit}
                  type="primary"
                  onClick={() => {
                    updateStatusEntry()
                  }}
                >
                  Simpan
                </button>
              </div>
            </div>
          )}
        </Card>
      </div>
    </>
  )
}

export default PurchaseInvoiceEntryDetail
