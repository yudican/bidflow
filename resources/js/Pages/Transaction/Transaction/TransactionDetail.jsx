import { Button, Card, Table, Tag } from "antd"
import React, { useEffect } from "react"
import { useNavigate, useParams } from "react-router-dom"
import Layout from "../../../components/layout"
import {
  formatDate,
  formatNumber,
  getItem,
  handleString,
  inArray,
  maskingText,
} from "../../../helpers"
import {
  transactionProductListColumn,
  transactionUploadPaymentListColumn,
} from "./config"
import { CheckOutlined, CloseOutlined, EditOutlined } from "@ant-design/icons"
import ModalCancelOrder from "../../../components/Modal/Transaction/ModalCancelOrder"
import axios from "axios"
import { toast } from "react-toastify"
import ModalPriceRequisition from "../../../components/Modal/ModalPriceRequisition"
import ModalQrCode from "../../../components/Modal/Transaction/ModalQrCode"
import ModalTrackOrder from "../../../components/Modal/Transaction/ModalTrackOrder"
import ModalUpdateAlamat from "../../../components/Modal/Transaction/ModalUpdateAlamat"
import ModalUpdateResi from "../../../components/Modal/Transaction/ModalUpdateResi"

const TransactionDetail = ({ type = "agent" }) => {
  const navigate = useNavigate()
  const { transaction_id } = useParams()
  const [loading, setLoading] = React.useState(false)
  const [detail, setDetail] = React.useState({})
  const [products, setProducts] = React.useState([])

  const loadDetail = () => {
    setLoading(true)
    const params = type === "agent" ? "detail/agent" : "detail"
    axios
      .get(`/api/transaction/${params}/${transaction_id}`)
      .then((res) => {
        const { data } = res.data
        setLoading(false)
        setDetail(data)
        const newProducts = data?.transaction_detail?.map((item) => {
          return {
            product_id: item?.data_product?.id,
            product_name: item?.data_product?.product_name,
            sku: item?.data_product?.product_sku,
            price: item?.price,
            u_of_m: item?.data_product?.product_u_of_m,
            qty: item.qty,
            subtotal: item.subtotal,
          }
        })
        setProducts(newProducts)
      })
      .catch((e) => setLoading(false))
  }

  const formatPhoneNumber = (phone) => {
    if (!phone) return "-"
    return phone.startsWith("0") ? phone : `+${phone}`
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

  const isTelmart = inArray(getItem("role"), [
    "telmar",
    "agent-telmar",
    "telmark-supervisor",
    "agent-telmark",
  ])
  const isFinish = detail?.status_delivery == 4 && detail?.status == 7
  return (
    <Layout
      title="Detail Transaksi "
      onClick={() => navigate(-1)}
      lastItemLabel={detail?.id_transaksi}
    // rightContent={rightContent}
    >
      <Card
        title={detail?.id_transaksi}
        extra={
          <div>
            <span className="mr-2">Status:</span>
            <Tag color={"blue"}>{detail?.final_status}</Tag>
          </div>
        }
      >
        <div className="row">
          <div className="col-md-4">
            <table className="w-100" style={{ width: "100%" }}>
              <tbody>
                <tr>
                  <td style={{ width: "35%" }} className="py-2">
                    <strong>Nama Customer</strong>
                  </td>
                  <td>: {detail?.user_info?.name || "-"}</td>
                </tr>
                <tr>
                  <td style={{ width: "35%" }} className="py-2 ">
                    <strong>No. Handphone</strong>
                  </td>
                  <td>: {detail?.user_info?.phone || "-"}</td>
                </tr>
                <tr>
                  <td style={{ width: "35%" }} className="py-2">
                    <strong>Email</strong>
                  </td>
                  <td>: {detail?.user_info?.email || "-"}</td>
                </tr>
                <tr>
                  <td style={{ width: "40%" }} className="py-2">
                    <strong>Tanggal Transaksi</strong>
                  </td>
                  <td>
                    : {formatDate(detail?.created_at, "DD-MM-YYYY HH:mm:ss")}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div className="col-md-4">
            <table className="w-100" style={{ width: "100%" }}>
              <tbody>
                <tr>
                  <td style={{ width: "40%" }} className="py-2">
                    <strong>Metode Pembayaran</strong>
                  </td>
                  <td>: {detail?.data_payment_method?.bank_name || "-"}</td>
                </tr>
                {detail?.payment_qr_url ? (
                  <tr>
                    <td style={{ width: "40%" }} className="py-2">
                      <strong>QR Code</strong>
                    </td>
                    <td>
                      {detail?.payment_qr_url ? (
                        <ModalQrCode qr_url={detail?.payment_qr_url}>
                          <span className="">
                            :{" "}
                            <span className="text-blue-800">Tampilkan QR</span>
                          </span>
                        </ModalQrCode>
                      ) : (
                        <span className="ant-tag ant-tag-blue">
                          Belum Tersedia
                        </span>
                      )}
                    </td>
                  </tr>
                ) : (
                  <tr>
                    <td style={{ width: "40%" }} className="py-2">
                      <strong>Nomor V.A</strong>
                    </td>
                    <td>
                      :&nbsp;
                      {detail && detail.payment_va_number ? (
                        <span className="ant-tag ant-tag-blue">
                          {detail.payment_va_number}
                        </span>
                      ) : (
                        <span className="ant-tag ant-tag-blue">
                          Belum Tersedia
                        </span>
                      )}
                    </td>
                  </tr>
                )}

                <tr>
                  <td style={{ width: "40%" }} className="py-2">
                    <strong>Metode Pengiriman</strong>
                  </td>
                  <td>: {detail?.shipping_type_name || "-"}</td>
                </tr>
                {detail?.expire_payment && (
                  <tr>
                    <td style={{ width: "40%" }} className="py-2">
                      <strong>Batas Pembayaran</strong>
                    </td>
                    <td>
                      :{" "}
                      {formatDate(
                        detail?.expire_payment,
                        "DD-MM-YYYY HH:mm:ss"
                      )}
                    </td>
                  </tr>
                )}
                {detail?.paid_time && (
                  <tr>
                    <td style={{ width: "40%" }} className="py-2">
                      <strong>Tanggal Pembayaran</strong>
                    </td>
                    <td>
                      : {formatDate(detail?.paid_time, "DD-MM-YYYY HH:mm:ss")}
                    </td>
                  </tr>
                )}
                {detail?.cancel_time && (
                  <tr>
                    <td style={{ width: "40%" }} className="py-2">
                      <strong>Tanggal Pembatalan</strong>
                    </td>
                    <td>
                      : {formatDate(detail?.cancel_time, "DD-MM-YYYY HH:mm:ss")}
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
          <div className="col-md-4">
            <table className="w-100" style={{ width: "100%" }}>
              <tbody>
                {/* <tr>
                  <td style={{ width: "40%" }} className="py-2">
                    <strong>Created by</strong>
                  </td>
                  <td>: {handleString(detail?.create_by_name)}</td>
                </tr> */}
                {detail?.final_status == "Order Baru" && (
                  <tr>
                    <td style={{ width: "40%" }} className="py-2">
                      <strong>Link</strong>
                    </td>
                    <td>
                      :{" "}
                      <a href={detail?.transaction_url} target={"_blank"}>
                        Show Link
                      </a>
                    </td>
                  </tr>
                )}
                <tr>
                  <td style={{ width: "40%" }} className="py-2">
                    <strong>Resi</strong>
                  </td>
                  {detail?.resi ? (
                    <td>
                      {detail?.returned_at ? (
                        <ModalUpdateResi
                          onSuccess={() => loadDetail()}
                          id_transaksi={detail?.id}
                        >
                          <span>
                            : <Tag color="blue">Ubah No Resi</Tag>
                          </span>
                        </ModalUpdateResi>
                      ) : (
                        <ModalTrackOrder
                          resi={detail?.resi}
                          order_number={detail?.id_transaksi}
                        >
                          <span>: {detail?.resi} (Lacak)</span>
                        </ModalTrackOrder>
                      )}
                    </td>
                  ) : (
                    <td>: Belum Input Resi</td>
                  )}
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </Card>

      {/* products */}
      <Card title={"Detail Product"} className={"mt-4"}>
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
                <Table.Summary.Cell index={3}></Table.Summary.Cell>
                <Table.Summary.Cell index={5}>
                  <strong>Total Qty</strong>
                </Table.Summary.Cell>
                <Table.Summary.Cell index={3}>
                  {detail?.qty_total}
                </Table.Summary.Cell>
              </Table.Summary.Row>
            )
          }}
        />
      </Card>

      {detail?.status == 2 && (
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
      )}

      <div className="row mt-4">
        <div className="col-md-7">
          <Card
            title={"Detail Pengiriman"}
          // extra={
          //   detail?.status <= 3 && (
          //     <ModalUpdateAlamat
          //       value={{
          //         ...detail?.user_info,
          //         note: detail?.note,
          //         transaction_id: detail?.id,
          //         address_id: detail?.address_user_id,
          //       }}
          //       initialValues={detail?.address_user}
          //       onSuccess={() => loadDetail()}
          //     >
          //       <Button icon={<EditOutlined />} title="Ubah Alamat"></Button>
          //     </ModalUpdateAlamat>
          //   )
          // }
          >
            <div className="mt-4 p-2 rounded-md border-2 border-[#008BE1] bg-[#D8F0FF] text-[#004AA6]">
              <p> Detail Alamat Pengiriman Customer</p>
              <table width={"100%"}>
                <tbody>
                  <tr>
                    <td width={"20%"}>Nama</td>
                    <td>: {handleString(detail?.data_user_address?.nama)}</td>
                  </tr>
                  <tr>
                    <td width={"20%"}>No. Handphone</td>
                    <td>
                      :{" "}
                      {maskingText(
                        handleString(detail?.data_user_address?.telepon),
                        isTelmart
                      )}
                    </td>
                  </tr>
                  <tr>
                    <td width={"20%"}>Alamat Email</td>
                    <td>
                      :{" "}
                      {maskingText(
                        handleString(detail?.user_info?.email),
                        isTelmart
                      )}
                    </td>
                  </tr>

                  <tr>
                    <td width={"20%"}>Alamat Lengkap</td>
                    <td>
                      :{" "}
                      {maskingText(
                        handleString(detail?.data_user_address?.alamat_detail),
                        isTelmart
                      )}
                    </td>
                  </tr>
                  <tr>
                    <td width={"20%"}>Notes</td>
                    <td>
                      :{" "}
                      {handleString(
                        detail?.note || detail?.data_user_address?.catatan
                      )}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </Card>
        </div>
        <div className="col-md-5">
          <Card title={"Detail Pembayaran"}>
            <table className="w-100" style={{ width: "100%" }}>
              <tbody>
                <tr>
                  <td style={{ width: "35%" }} className="py-2">
                    <strong>Diskon</strong>
                  </td>
                  <td>: {formatNumber(detail?.diskon, "Rp. ", "0")}</td>
                </tr>
                {detail?.diskon_voucher > 0 && (
                  <tr>
                    <td style={{ width: "35%" }} className="py-2">
                      <strong>Diskon Voucher</strong>
                    </td>
                    <td>
                      : {formatNumber(detail?.diskon_voucher, "Rp. ", "0")}
                    </td>
                  </tr>
                )}
                <tr>
                  <td style={{ width: "35%" }} className="py-2">
                    <strong>Subtotal</strong>
                  </td>
                  <td>
                    :{" "}
                    {formatNumber(
                      detail?.subtotal -
                      (detail?.diskon || 0) -
                      (detail?.diskon_voucher || 0),
                      "Rp. ",
                      "0"
                    )}
                  </td>
                </tr>

                {detail?.diskon_ongkir > 0 && (
                  <tr>
                    <td style={{ width: "35%" }} className="py-2">
                      <strong>Diskon Ongkir</strong>
                    </td>
                    <td>
                      : {formatNumber(detail?.diskon_ongkir, "Rp. ", "0")}
                    </td>
                  </tr>
                )}
                <tr>
                  <td style={{ width: "35%" }} className="py-2">
                    <strong>Ongkos Kirim</strong>
                  </td>
                  <td>
                    :{" "}
                    {formatNumber(
                      detail?.shipping_type?.shipping_price,
                      "Rp. ",
                      "0"
                    )}
                  </td>
                </tr>

                {/* <tr>
                  <td style={{ width: "35%" }} className="py-2">
                    <strong>Biaya Admin</strong>
                  </td>
                  <td>
                    :
                    <ModalPriceRequisition
                      title="Ubah Biaya Admin"
                      value={formatNumber(detail?.admin_fee, "Rp. ")}
                      initialValues={{ item_price: detail?.admin_fee }}
                      url={`/api/transaction/update/admin-fee/${detail.id}`}
                      refetch={() => loadDetail()}
                    />
                  </td>
                </tr> */}

                {detail?.payment_unique_code > 0 && (
                  <tr>
                    <td style={{ width: "35%" }} className="py-2">
                      <strong>Kode Unik</strong>
                    </td>
                    <td>
                      : {formatNumber(detail?.payment_unique_code, "Rp. ", "0")}
                    </td>
                  </tr>
                )}

                <tr>
                  <td style={{ width: "35%" }} className="py-2">
                    <strong>Total Pembayaran</strong>
                  </td>
                  <td>: {formatNumber(detail?.nominal, "Rp. ", "0")}</td>
                </tr>
                <tr>
                  <td style={{ width: "35%" }} className="py-2">
                    <strong>PPN (11%)</strong>
                  </td>
                  <td>: {formatNumber(detail?.ppn, "Rp. ", "0")}</td>
                </tr>
                <tr>
                  <td style={{ width: "35%" }} className="py-2">
                    <strong>Total + PPN (11%)</strong>
                  </td>
                  <td>: {formatNumber(detail?.total, "Rp. ", "0")}</td>
                </tr>
                {isFinish && (
                  <tr>
                    <td style={{ width: "35%" }} className="py-2">
                      <strong>Deduction</strong>
                    </td>
                    <td>
                      <ModalPriceRequisition
                        prefix={": "}
                        title="Deduction"
                        value={formatNumber(detail?.deduction, "Rp. ")}
                        initialValues={{ item_price: detail?.deduction }}
                        url={`/api/transaction/update/deduction/${detail.id}`}
                        refetch={() => loadDetail()}
                      />
                    </td>
                  </tr>
                )}
                {isFinish && (
                  <tr>
                    <td style={{ width: "35%" }} className="py-2">
                      <strong>Administrasi Midtrans</strong>
                    </td>
                    <td>: -{formatNumber(detail?.admin_fee, "Rp. ")}</td>
                  </tr>
                )}
                {isFinish && (
                  <tr>
                    <td style={{ width: "35%" }} className="py-2">
                      <strong>Total Uang Masuk</strong>
                    </td>
                    <td>
                      :{" "}
                      {formatNumber(
                        detail?.total - detail?.deduction - detail?.admin_fee,
                        "Rp. ",
                        "0"
                      )}
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </Card>
        </div>
      </div>
    </Layout>
  )
}

export default TransactionDetail
