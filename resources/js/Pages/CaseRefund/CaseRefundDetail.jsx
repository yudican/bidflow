import { CheckOutlined, CloseOutlined, PlusOutlined } from "@ant-design/icons"
import { Table } from "antd"
import React, { useEffect, useState } from "react"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../components/layout"
import LoadingFallback from "../../components/LoadingFallback"
import { getItem, inArray, subStr } from "../../helpers"
import { refundListItemColumn, refundListResiColumn } from "./config"
const CaseRefundDetail = () => {
  const navigate = useNavigate()
  const { uid_refund } = useParams()
  const [detail, setDetail] = useState(null)
  const [loading, setLoading] = useState(false)
  const loadDetailCaseRefund = () => {
    setLoading(true)
    axios.get(`/api/case/refund/detail/${uid_refund}`).then((res) => {
      const { data } = res.data
      setDetail(data)
      setLoading(false)
    })
  }

  useEffect(() => {
    loadDetailCaseRefund()
  }, [])

  const handleReject = () => {
    axios
      .post(`/api/case/refund/reject`, { uid_refund })
      .then((res) => {
        const { message } = res.data
        toast.success(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        loadDetailCaseRefund()
      })
      .catch((err) => {
        const { message } = err.response.data
        toast.error(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }

  const handleApprove = () => {
    axios
      .post(`/api/case/refund/approve`, { uid_refund })
      .then((res) => {
        const { message } = res.data
        toast.success(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        loadDetailCaseRefund()
      })
      .catch((err) => {
        const { message } = err.response.data
        toast.error(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }

  const show = !inArray(getItem("role"), ["adminsales", "leadwh", "leadsales"])

  const rightContent = (
    <div>
      {detail?.status === "0" && (
        <div>
          {show && (
            <button
              onClick={() => {
                handleReject()
              }}
              className="mr-4 text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
            >
              <CloseOutlined />
              <span className="ml-2">{`Tolak Pengajuan`}</span>
            </button>
          )}
          {show && (
            <button
              onClick={() => {
                handleApprove()
              }}
              className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
            >
              <CheckOutlined />
              <span className="ml-2">{`Terima Pengajuan`}</span>
            </button>
          )}
        </div>
      )}

      {detail?.status === "1" && (
        <button
          onClick={() => {
            navigate("/order/sales-refund/form/" + uid_refund)
          }}
          className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
        >
          <PlusOutlined />
          <span className="ml-2">{`Sales Refund`}</span>
        </button>
      )}
    </div>
  )

  const items = detail
    ? detail?.refund_items?.map((item, index) => {
        if (item?.product) {
          return {
            id: item.id,
            qty: item.qty,
            product_name: item?.product?.name || "-",
            product_photo: item.product_photo_url,
          }
        }
      })
    : []

  const newItems = items.filter((item) => item)

  if (loading) {
    return (
      <Layout title="Detail Case Refund" href="/case/refund">
        <LoadingFallback />
      </Layout>
    )
  }

  return (
    <Layout
      title="Detail Case Refund"
      rightContent={rightContent}
      href="/case/refund"
    >
      <div className="row">
        <div className="col-md-6">
          <div className="card">
            <div className="card-header">
              <div className="h1 card-title">Informasi Pribadi</div>
            </div>
            <div className="card-body">
              <table className="w-100 table-auto">
                <tbody>
                  <tr>
                    <td className="py-2">
                      <strong>Name</strong>
                    </td>
                    <td>:</td>
                    <td>{detail?.name}</td>
                  </tr>
                  <tr>
                    <td className="py-2">
                      <strong>Email</strong>
                    </td>
                    <td>:</td>
                    <td>{detail?.email}</td>
                  </tr>
                  <tr>
                    <td className="py-2">
                      <strong>Phone</strong>
                    </td>
                    <td>:</td>
                    <td>{detail?.handphone || "-"}</td>
                  </tr>
                  <tr>
                    <td className="py-2">
                      <strong>Status</strong>
                    </td>
                    <td>:</td>
                    <td>{detail?.status_refund || "-"}</td>
                  </tr>
                  <tr>
                    <td className="py-2">
                      <strong>Address</strong>
                    </td>
                    <td>:</td>
                    <td>{subStr(detail?.address)}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div className="col-md-6">
          <div className="card">
            <div className="card-header">
              <div className="h1 card-title">Informasi Pengajuan Komplain</div>
            </div>
            <div className="card-body">
              <table className="w-100">
                <tbody>
                  <tr>
                    <td className="py-2">
                      <strong>Complain Type</strong>
                    </td>
                    <td>: {detail?.type_case}</td>
                  </tr>
                  <tr>
                    <td className="py-2">
                      <strong>Transaction From</strong>
                    </td>
                    <td>: {detail?.transaction_from}</td>
                  </tr>
                  <tr>
                    <td className="py-2">
                      <strong>Transaction Id</strong>
                    </td>
                    <td>: {detail?.transaction_id || "-"}</td>
                  </tr>
                  <tr>
                    <td className="py-2">
                      <strong>Transfer Photo</strong>
                    </td>
                    <td>
                      :{" "}
                      <span>
                        {detail?.transfer_photo ? (
                          <a href={detail?.transfer_photo_url}>
                            Download Attachment
                          </a>
                        ) : (
                          "-"
                        )}
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <td className="py-2">
                      <strong>Alsan</strong>
                    </td>
                    <td>: {detail?.alasan || "-"}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        {newItems.length > 0 && (
          <div className="col-md-12">
            <div className="card">
              <div className="card-header">
                <div className="h1 card-title">Informasi Product</div>
              </div>
              <div className="card-body">
                <Table
                  dataSource={newItems}
                  columns={refundListItemColumn}
                  loading={loading}
                  pagination={false}
                  rowKey="id"
                  scroll={{ x: "max-content" }}
                  tableLayout={"auto"}
                />
              </div>
            </div>
          </div>
        )}

        <div className="col-md-12">
          <div className="card">
            <div className="card-header flex justify-between items-center">
              <div className="h1 card-title">Informasi Pengembalian Barang</div>
            </div>
            <div className="card-body">
              <Table
                dataSource={detail?.retur_resis || []}
                columns={refundListResiColumn}
                loading={loading}
                pagination={false}
                rowKey="id"
                scroll={{ x: "max-content" }}
                tableLayout={"auto"}
              />
            </div>
          </div>
        </div>
      </div>
    </Layout>
  )
}

export default CaseRefundDetail
