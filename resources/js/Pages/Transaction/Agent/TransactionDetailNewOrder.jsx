import { EditOutlined, LoadingOutlined } from "@ant-design/icons"
import { Button, Card, Table, Tag } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { useParams } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../../components/layout"
import ModalDiscountItem from "../../../components/Modal/ModalDiscountItem"
import ModalPriceRequisition from "../../../components/Modal/ModalPriceRequisition"
import ModalQrCode from "../../../components/Modal/Transaction/ModalQrCode"
import ModalUpdateAlamat from "../../../components/Modal/Transaction/ModalUpdateAlamat"
import {
  formatDate,
  formatNumber,
  getItem,
  handleString,
  inArray,
} from "../../../helpers"

const TransactionDetailNewOrder = ({ type = "agent" }) => {
  const { transaction_id } = useParams()
  const [loading, setLoading] = useState(false)
  const [loadingVirtualAccount, setLoadingVirtualAccount] = useState(false)
  const [detail, setDetail] = useState({})
  const [products, setProducts] = useState([])
  const [discounts, setDiscounts] = useState([])

  const loadDetail = () => {
    setLoading(true)
    const params = "detail"
    axios
      .get(`/api/transaction/${params}/${transaction_id}`)
      .then((res) => {
        const { data } = res.data

        setLoading(false)
        setDetail(data)
        const newProducts = data?.transaction_detail?.map((item) => {
          return {
            id: item?.id,
            product_id: item?.product_variant?.product_id,
            discount_id: item?.discount_id ? parseInt(item?.discount_id) : null,
            product_name: item?.product_variant?.name,
            sku: item?.product_variant?.sku,
            price: item?.price,
            // price: item?.product_variant?.price["final_price"],
            u_of_m: item?.u_of_m,
            qty: item.qty,
            subtotal: item.subtotal,
            diskon: item?.diskon,
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

  const getVirtualAccount = () => {
    setLoadingVirtualAccount(true)
    axios
      .post(`/api/transaction/bank/payment/virtual-account`, {
        transaction_id,
        payment_method: detail?.payment_method,
        invoice_number: detail?.id_transaksi,
        amount_to_pay: detail?.amount_to_pay,
        products: detail?.transaction_detail.map((item) => {
          const diskon = item?.diskon > 0 ? item?.diskon / item?.qty : 0
          return {
            variant_id: item?.product_variant_id,
            product_id: item?.product_id,
            price: item?.price - diskon,
            qty: item?.qty,
            product_name: item?.product_name,
          }
        }),
        voucher_id: detail?.voucher_id,
        diskon: parseInt(detail?.diskon) + parseInt(detail?.diskon_voucher),
        ongkir: detail?.ongkir || detail?.shipping_type?.shipping_price,
      })
      .then((res) => {
        if (res?.data?.status == "error") {
          toast.error(
            "Data transaksi gagal diproses ke status Menunggu Pembayaran"
          )
        } else {
          toast.success("Data transaksi diproses ke status Menunggu Pembayaran")
        }
        setLoadingVirtualAccount(false)
        loadDetail()
      })
      .catch((e) => {
        toast.error(
          "Data transaksi gagal diproses ke status Menunggu Pembayaran"
        )
        setLoadingVirtualAccount(false)
      })
  }

  const handleGetDiscount = () => {
    return axios
      .get("/api/master/discounts")
      .then((res) => {
        const { data } = res.data
        setDiscounts(
          data
            ?.filter((item) => inArray("telmark", item.sales_channels))
            .map((row) => {
              const percentage = row?.percentage > 0 ? row?.percentage / 100 : 0
              return {
                ...row,
                percentage,
              }
            })
        )
      })
      .catch((e) => {
        console.log(e, "error get discount")
      })
  }

  useEffect(() => {
    loadDetail()
    handleGetDiscount()
  }, [])

  const canEditPrice = inArray(getItem("role"), ["superadmin", "finance"])
  const isFinish = detail?.status_delivery == 4 && detail?.status == 7
  const isNeedPayment = !detail?.payment_va_number || !detail?.payment_qr_url
  return (
    <Layout
      title="Detail Transaksi"
      href="/transaction/new-order"
      rightContent={
        <div>
          {isNeedPayment && (
            <button
              onClick={() => getVirtualAccount()}
              className={`text-white bg-blue-800 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mr-2`}
            >
              {loadingVirtualAccount ? (
                <LoadingOutlined />
              ) : inArray(detail?.payment_method?.payment_channel, [
                  "gopay",
                  "qris",
                ]) ? (
                <span className="ml-2">Get Barcode </span>
              ) : (
                <span className="ml-2">Get VA Number </span>
              )}
            </button>
          )}
        </div>
      }
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
                  <td>: {detail?.data_user_address?.nama || "-"}</td>
                </tr>
                <tr>
                  <td style={{ width: "35%" }} className="py-2">
                    <strong>No. Handphone</strong>
                  </td>
                  <td>: {detail?.data_user_address?.telepon || "-"}</td>
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
                  <td>: {formatDate(detail?.created_at)}</td>
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
                  <td>: {detail?.payment_method_name || "-"}</td>
                </tr>
                {inArray(detail?.payment_method?.payment_channel, [
                  "gopay",
                  "qris",
                ]) ? (
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
                {detail?.expire_payment && detail?.status == 0 && (
                  <tr>
                    <td style={{ width: "40%" }} className="py-2">
                      <strong>Batas Waktu Order</strong>
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
                {detail?.expire_payment && detail?.status > 0 && (
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
                <tr>
                  <td style={{ width: "40%" }} className="py-2">
                    <strong>Created by</strong>
                  </td>
                  <td>: {handleString(detail?.create_by_name)}</td>
                </tr>
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
              </tbody>
            </table>
          </div>
        </div>
      </Card>

      {/* products */}
      <Card title={"Detail Product"} className={"mt-4"}>
        <Table
          // rowSelection={rowSelection}
          dataSource={products}
          columns={[
            {
              title: "No.",
              dataIndex: "id",
              key: "id",
              render: (text, record, index) => index + 1,
            },
            {
              title: "Nama Product",
              dataIndex: "product_name",
              key: "product_name",
            },
            {
              title: "SKU",
              dataIndex: "sku",
              key: "sku",
            },

            {
              title: "Diskon",
              dataIndex: "diskon",
              key: "diskon",
              // render: (value) => `Rp ${formatNumber(value)}`,
              render: (text, record, index) => {
                if (canEditPrice) {
                  return (
                    <ModalDiscountItem
                      discounts={discounts}
                      value={formatNumber(text, "Rp. ")}
                      initialValues={{ discount_id: record?.discount_id }}
                      url={`/api/transaction/update/discount-item/${record.id}`}
                      refetch={() => loadDetail()}
                    />
                  )
                }
                return formatNumber(text, "Rp. ")
              },
            },
            {
              title: "Harga Satuan",
              dataIndex: "price",
              key: "price",
              // render: (value) => `Rp ${formatNumber(value)}`,
              render: (text, record, index) => {
                if (canEditPrice) {
                  return (
                    <ModalPriceRequisition
                      value={formatNumber(text, "Rp. ")}
                      initialValues={{ item_price: text }}
                      url={`/api/transaction/update/price-item/${record.id}`}
                      refetch={() => loadDetail()}
                    />
                  )
                }
                return formatNumber(text, "Rp. ")
              },
            },
            {
              title: "UoM",
              dataIndex: "u_of_m",
              key: "u_of_m",
            },
            {
              title: "Qty",
              dataIndex: "qty",
              key: "qty",
            },
            {
              title: "Subtotal",
              dataIndex: "subtotal",
              key: "subtotal",
              render: (value) => `Rp ${formatNumber(value)}`,
            },
          ]}
          loading={loading}
          pagination={false}
          rowKey="id"
          scroll={{ x: "max-content" }}
          tableLayout={"auto"}
        />
        {/* <Table
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
        /> */}
      </Card>

      <div className="row mt-4">
        <div className="col-md-7">
          <Card
            title={"Detail Pengiriman"}
            extra={
              <ModalUpdateAlamat
                value={{
                  ...detail?.user_info,
                  note: detail?.note,
                  transaction_id: detail?.id,
                  address_id: detail?.address_user_id,
                }}
                initialValues={detail?.address_user}
                onSuccess={() => loadDetail()}
              >
                <Button icon={<EditOutlined />} title="Ubah Alamat"></Button>
              </ModalUpdateAlamat>
            }
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
                      : {handleString(detail?.data_user_address?.telepon)}
                    </td>
                  </tr>
                  <tr>
                    <td width={"20%"}>Alamat Email</td>
                    <td>: {handleString(detail?.user_info?.email)}</td>
                  </tr>
                  {/* <tr>
                    <td width={"20%"}>Kecamatan</td>
                    <td>
                      : {handleString(detail?.data_user_address?.kecamatan)}
                    </td>
                  </tr> */}
                  <tr>
                    <td width={"20%"}>Alamat Lengkap</td>
                    <td>
                      :{" "}
                      {handleString(
                        detail?.data_user_address?.alamat_detail ||
                          detail?.final_address?.address
                      )}
                    </td>
                  </tr>
                  <tr>
                    <td width={"20%"}>Notes</td>
                    <td>: {handleString(detail?.note)}</td>
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
                  <td>: {formatNumber(detail?.subtotal, "Rp. ", "0")}</td>
                </tr>
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
                  {/* <td>: {formatNumber(detail?.ongkir , "Rp. ", "-")}</td> */}
                </tr>
                {/* <tr>
                  <td style={{ width: "35%" }} className="py-2">
                    <strong>Biaya Admin</strong>
                  </td>
                  <td className="flex items-center">
                    <span>: </span>
                    <ModalPriceRequisition
                      title="Ubah Biaya Admin"
                      value={formatNumber(detail?.admin_fee, "Rp. ")}
                      initialValues={{ item_price: detail?.admin_fee }}
                      url={`/api/transaction/update/admin-fee/${detail.id}`}
                      refetch={() => loadDetail()}
                    />
                  </td>
                </tr> */}
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
                        detail?.total - detail?.admin_fee,
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

export default TransactionDetailNewOrder
