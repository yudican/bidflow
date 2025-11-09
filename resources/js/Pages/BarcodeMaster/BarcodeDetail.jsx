import { Card, Table, Tag } from "antd"
import React, { useEffect } from "react"
import { useNavigate, useParams } from "react-router-dom"
import Layout from "../../components/layout"
import { formatDate, formatNumber, handleString } from "../../helpers"
import {
  transactionProductListColumn,
  transactionUploadPaymentListColumn,
} from "./config"
import { CheckOutlined, CloseOutlined } from "@ant-design/icons"
import ModalCancelOrder from "../../components/Modal/Transaction/ModalCancelOrder"
import axios from "axios"
import { toast } from "react-toastify"

const TransactionDetail = ({ type = "agent" }) => {
  const navigate = useNavigate()
  const { barcode_id } = useParams()
  const [loading, setLoading] = React.useState(false)
  const [detail, setDetail] = React.useState({})
  const [products, setProducts] = React.useState([])
  const [histories, setHistories] = React.useState([])

  const loadDetail = () => {
    setLoading(true)
    const params = type === "agent" ? "detail/agent" : "detail"
    axios
      .get(`/api/barcode/${params}/${barcode_id}`)
      .then((res) => {
        const { data } = res.data
        console.log(data);
        setLoading(false)
        setDetail(data)
        const newProducts = data?.barcode_children?.map((item) => {
          return {
            barcode: item?.barcode,
            status: item?.status,
          }
        })

        const histories = data?.barcode_history?.map((item) => {
          return {
            activity: item?.activity,
            created_by: item?.created_by_name,
            created_at: item?.created_at,
          }
        })

        setProducts(newProducts)

        setHistories(histories)
      })
      .catch((e) => setLoading(false))
  }

  const updatePaymentStatus = (type, payment_id, value = {}) => {
    axios
      .post("/api/transaction/payment/" + type, {
        ...value,
        payment_id,
        transaction_id: detail?.id,
        type,
      })
      .then((res) => {
        const { message } = res.data
        toast.success(message)
        loadDetail()
      })
      .catch((e) => {
        const { message } = e.response.data
        toast.error(message)
      })
  }

  useEffect(() => {
    loadDetail()
  }, [])

  return (
    <Layout
      title="Barcode Detail"
      href="#"
      lastItemLabel={detail?.id_transaksi}
      // rightContent={rightContent}
    >
      <Card
        title={detail?.id_transaksi}
        extra={
          <div>
            <span className="mr-2">Status:</span>
            <Tag color={"blue"}>{detail?.status}</Tag>
          </div>
        }
      >
        <div className="row">
          <div className="col-md-4">
            <table className="w-100" style={{ width: "100%" }}>
              <tbody>
                <tr>
                  <td style={{ width: "35%" }} className="py-2">
                    <strong>Master Box ID</strong>
                  </td>
                  <td>: {detail?.product_id || "-"}</td>
                </tr>
                <tr>
                  <td style={{ width: "35%" }} className="py-2">
                    <strong>Location</strong>
                  </td>
                  <td>: {`${detail?.location}` || "-"}</td>
                </tr>
                <tr>
                  <td style={{ width: "35%" }} className="py-2">
                    <strong>MOQ</strong>
                  </td>
                  <td>: {detail?.moq || "-"}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div className="col-md-4">
            <table className="w-100" style={{ width: "100%" }}>
              <tbody>
                <tr>
                  <td style={{ width: "40%" }} className="py-2">
                    <strong>Batch ID</strong>
                  </td>
                  <td>: {detail?.batch_id || "-"}</td>
                </tr>
                <tr>
                  <td style={{ width: "40%" }} className="py-2">
                    <strong>Tipe PO</strong>
                  </td>
                  <td>: {detail?.tipe_po || "-"}</td>
                </tr>
                <tr>
                  <td style={{ width: "40%" }} className="py-2">
                    <strong>Qty</strong>
                  </td>
                  <td>: {detail?.qty || "-"}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div className="col-md-4">
            <table className="w-100" style={{ width: "100%" }}>
              <tbody>
                <tr>
                  <td style={{ width: "40%" }} className="py-2">
                    <strong>Created At</strong>
                  </td>
                  <td>
                    : {formatDate(detail?.created_at, "DD-MM-YYYY | HH:mm:ss")}
                  </td>
                </tr>
                <tr>
                  <td style={{ width: "40%" }} className="py-2">
                    <strong>Prefix</strong>
                  </td>
                  <td>: {detail?.prefixs || "-"} </td>
                </tr>
                <tr>
                  <td style={{ width: "40%" }} className="py-2">
                    <strong>Status</strong>
                  </td>
                  <td>: {detail?.status || "-"}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </Card>

      {/* barcode child */}
      <Card title={"Barcode Child"} className={"mt-4"}>
        <Table
          columns={transactionProductListColumn}
          dataSource={products}
          rowKey={"product_id"}
          pagination={false}
          summary={(pageData) => {
            return (
              <Table.Summary.Row>
                <Table.Summary.Cell index={0}></Table.Summary.Cell>
                <Table.Summary.Cell index={1}></Table.Summary.Cell>
                <Table.Summary.Cell index={2}></Table.Summary.Cell>
              </Table.Summary.Row>
            )
          }}
        />
      </Card>

      <Card title={"History Barcode"} className={"mt-4"}>
          <Table
            columns={[
              ...transactionUploadPaymentListColumn,
            ]}
            dataSource={
              histories
            }
            rowKey={"id"}
            pagination={false}
          />
        </Card>
    </Layout>
  )
}

export default TransactionDetail
