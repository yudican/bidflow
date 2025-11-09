import { Table } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { useParams } from "react-router-dom"
import Layout from "../../components/layout"
import { getItem } from "../../helpers"
import { orderItemsColumn } from "./config"

const OrderListDetail = () => {
  const params = useParams()
  const [detailOrderGinie, setDetailGinie] = useState(null)
  const [loading, setLoading] = useState(false)

  const base_url = getItem("service_ginee_url")
  const loadDetailGinie = () => {
    setLoading(true)
    axios
      .get(`${base_url}/api/genie/order/detail/${params.orderId}`)
      .then((res) => {
        const { data } = res.data
        setDetailGinie(data)
        setLoading(false)
      })
  }

  useEffect(() => {
    loadDetailGinie()
  }, [])
  const { orderItems, logisticsInfos, paymentInfo, shippingAddress } =
    detailOrderGinie || {}
  return (
    <Layout title="Detail" href="/genie/order/list">
      <div className="card">
        <div className="card-body">
          <div className="row card-body  ">
            <div className="col-md-6">
              <div className="card">
                <div className="card-header">
                  <h3 className="card-title">Custommer Info</h3>
                </div>
                <div className="card-body">
                  <table className="w-100">
                    <tbody>
                      <tr>
                        <td className="py-2">
                          <strong>Nama Custommer</strong>
                        </td>
                        <td>: {detailOrderGinie?.customerName}</td>
                      </tr>
                      <tr>
                        <td className="py-2">
                          <strong>Email Custommer</strong>
                        </td>
                        <td>: {detailOrderGinie?.customerEmail}</td>
                      </tr>

                      <tr>
                        <td className="py-2">
                          <strong>Telepon</strong>
                        </td>
                        <td>: {detailOrderGinie?.customerMobile}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <div className="col-md-6">
              <div className="card">
                <div className="card-header">
                  <h3 className="card-title">Rincian Pembelian</h3>
                </div>
                <div className="card-body">
                  <table className="w-100">
                    <tbody>
                      <tr>
                        <td className="py-2">
                          <strong>TRX ID</strong>
                        </td>
                        <td>: {detailOrderGinie?.externalOrderId}</td>
                      </tr>
                      <tr>
                        <td className="py-2">
                          <strong>Channel</strong>
                        </td>
                        <td>: {detailOrderGinie?.channelName}</td>
                      </tr>

                      <tr>
                        <td className="py-2">
                          <strong>Payment Method</strong>
                        </td>
                        <td>: {detailOrderGinie?.paymentMethod}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            {/* items */}
            <div className="col-md-12">
              <div className="card">
                <div className="card-header">
                  <h1 className="text-lg text-bold flex justify-content-between align-items-center">
                    <span>Rincian Product</span>
                  </h1>
                </div>
                <div className="card-body">
                  <Table
                    dataSource={orderItems}
                    columns={orderItemsColumn}
                    loading={loading}
                    pagination={false}
                    rowKey="itemId"
                    scroll={{ x: "max-content" }}
                    tableLayout={"auto"}
                  />
                </div>
              </div>
            </div>

            {/* logistik */}
            <div className="col-md-6">
              <div className="card">
                <div className="card-header">
                  <h1 className="text-lg text-bold ">Rincian Pengiriman</h1>
                </div>
                <div className="card-body">
                  <table className="w-100" style={{ width: "100%" }}>
                    <tbody>
                      {/* <tr>
                    <td style={{ width: "50%" }} className="py-2">
                      <strong>Type Pengiriman</strong>
                    </td>
                    <td>: {detailOrderGinie?.shippingType}</td>
                  </tr> */}
                      <tr>
                        <td style={{ width: "50%" }} className="py-2">
                          <strong>Kurir</strong>
                        </td>
                        <td>: {detailOrderGinie?.logisticsProviderName}</td>
                      </tr>

                      <tr>
                        <td style={{ width: "50%" }} className="py-2">
                          <strong>Nomor Resi</strong>
                        </td>
                        <td>: {detailOrderGinie?.logisticsTrackingNumber}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <div className="card">
                <div className="card-header">
                  <h1 className="text-lg text-bold ">Detail Pengiriman</h1>
                </div>
                <div className="card-body">
                  <div className="d-flex flex-row justify-between">
                    <div>
                      <p style={{ fontSize: 12 }} className="mb-0">
                        Nama Penerima
                      </p>
                      <p
                        style={{
                          fontSize: 14,
                          fontWeight: 600,
                        }}
                        className="mb-0"
                      >
                        {shippingAddress?.name}
                      </p>
                    </div>
                  </div>
                  <p style={{ fontSize: 12 }} className="pt-2 mb-0">
                    {shippingAddress?.phoneNumber}
                  </p>
                  <p style={{ fontSize: 12 }}>{shippingAddress?.fullAddress}</p>
                </div>
              </div>
            </div>

            {/* pembayaran */}
            <div className="col-md-6">
              <div className="card">
                <div className="card-header">
                  <h1 className="text-lg text-bold ">Rincian Pembayaran</h1>
                </div>
                <div className="card-body">
                  <table className="w-100" style={{ width: "100%" }}>
                    <tbody>
                      <tr>
                        <td style={{ width: "50%" }} className="py-2">
                          <strong>Subtotal</strong>
                        </td>
                        <td>: {paymentInfo?.subtotal}</td>
                      </tr>

                      <tr>
                        <td style={{ width: "50%" }} className="py-2">
                          <strong>Diskon Pengiriman</strong>
                        </td>
                        <td>: {paymentInfo?.finalShippingFee}</td>
                      </tr>

                      <tr>
                        <td style={{ width: "50%" }} className="py-2">
                          <strong>Total Diskon</strong>
                        </td>
                        <td>: {paymentInfo?.totalDiscounts}</td>
                      </tr>
                      <tr>
                        <td style={{ width: "50%" }} className="py-2">
                          <strong>Ongkos Kirim</strong>
                        </td>
                        <td>: {paymentInfo?.totalShippingFee}</td>
                      </tr>
                      <tr>
                        <td style={{ width: "50%" }} className="py-2">
                          <strong>Pajak</strong>
                        </td>
                        <td>: {paymentInfo?.taxationFee}</td>
                      </tr>
                      <tr>
                        <td style={{ width: "50%" }} className="py-2">
                          <strong>Biaya Layanan</strong>
                        </td>
                        <td>: {paymentInfo?.serviceFee}</td>
                      </tr>
                      <tr>
                        <td style={{ width: "50%" }} className="py-2">
                          <strong>Biaya Asuransi</strong>
                        </td>
                        <td>: {paymentInfo?.insuranceFee}</td>
                      </tr>
                      <tr>
                        <td style={{ width: "50%" }} className="py-2">
                          <strong>Total Pembayaran</strong>
                        </td>
                        <td>: {paymentInfo?.sellerTotalAmount}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            {/* address */}
            {/* <div className="col-md-12">
                    <div className="card">
                        <div className="card-header">
                            <h1 className="text-lg text-bold flex justify-content-between align-items-center">
                                <span>Contact Address</span>
                                <button className="btn btn-primary btn-sm">
                                    Tambah Data
                                </button>
                            </h1>
                        </div>
                        <div className="card-body">
                            <ContactAddress />
                        </div>
                    </div>
                </div> */}
          </div>
        </div>
      </div>
    </Layout>
  )
}

export default OrderListDetail
