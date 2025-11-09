import { Table } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { useParams } from "react-router-dom"
import Layout from "../../components/layout"
import { formatNumber } from "../../helpers"
import { orderItemsColumn } from "./config"

const OrderListDetailMpc = () => {
  const params = useParams()
  const [detailOrderMpc, setDetailMpc] = useState(null)
  const [loading, setLoading] = useState(false)

  const loadDetailGinie = () => {
    setLoading(true)
    axios.get(`/api/ethix/detail/${params.orderId}`).then((res) => {
      const { data } = res.data
      setDetailMpc(data)
      setLoading(false)
    })
  }

  useEffect(() => {
    loadDetailGinie()
  }, [])

  // 'channel_origin',
  // 'shop_name',
  // 'name',
  // 'phone',
  // 'email',
  // 'district',
  // 'city',
  // 'province',
  // 'postal_code',
  // 'full_address',
  // 'shipping_price',
  // 'receipent_name',
  // 'receipent_phone',
  // 'receipent_address',
  // 'total_discount',
  // 'sku',
  // 'qty',
  // 'invoice_number',
  // 'status'

  const { items, logisticsInfos, paymentInfo, shippingAddress } =
    detailOrderMpc || {}
  return (
    <Layout title="Detail" href="/genie/order/list">
      <div className="grid grid-cols-2 gap-x-4">
        <div className="">
          <div className="card">
            <div className="card-header">
              <h3 className="card-title">Receipient Info</h3>
            </div>
            <div className="card-body">
              <table className="w-full">
                <tbody>
                  <tr>
                    <td className="font-normal text-sm w-1/4">
                      <span>Nama Customer</span>
                    </td>
                    <td>:</td>
                    <td className="text-neutralColor">
                      {detailOrderMpc?.name}
                    </td>
                  </tr>
                  <tr>
                    <td className="font-normal text-sm w-1/4">
                      <span>Email Customer</span>
                    </td>
                    <td>:</td>
                    <td className="text-neutralColor">
                      {detailOrderMpc?.email}
                    </td>
                  </tr>

                  <tr>
                    <td className="font-normal text-sm w-1/4">
                      <span>Phone</span>
                    </td>
                    <td>:</td>
                    <td className="text-neutralColor">
                      {detailOrderMpc?.phone}
                    </td>
                  </tr>

                  <tr>
                    <td className="font-normal text-sm w-1/4">
                      <span>District</span>
                    </td>
                    <td>:</td>
                    <td className="text-neutralColor">
                      {detailOrderMpc?.district}
                    </td>
                  </tr>

                  <tr>
                    <td className="font-normal text-sm w-1/4">
                      <span>City</span>
                    </td>
                    <td>:</td>
                    <td className="text-neutralColor">
                      {detailOrderMpc?.city}
                    </td>
                  </tr>

                  <tr>
                    <td className="font-normal text-sm w-1/4">
                      <span>Province</span>
                    </td>
                    <td>:</td>
                    <td className="text-neutralColor">
                      {detailOrderMpc?.province}
                    </td>
                  </tr>

                  <tr>
                    <td className="font-normal text-sm w-1/4">
                      <span>Full Address</span>
                    </td>
                    <td>:</td>
                    <td className="text-neutralColor">
                      {detailOrderMpc?.full_address}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div className="card">
          <div className="card-header">
            <h3 className="card-title">Order Info</h3>
          </div>
          <div className="card-body">
            <table className="table-auto w-full">
              <tr>
                <td className="font-normal text-sm w-2/5">Order Date</td>
                <td>:</td>
                <td className="text-neutralColor">
                  {/* {detailOrderMpc?.channel_origin} */}
                </td>
              </tr>
              <tr>
                <td className="font-normal text-sm w-2/5">Channel</td>
                <td>:</td>
                <td className="text-neutralColor">
                  {detailOrderMpc?.channel_origin}
                </td>
              </tr>

              <tr>
                <td className="font-normal text-sm w-2/5">Shop Name</td>
                <td>:</td>
                <td className="text-neutralColor">
                  {detailOrderMpc?.shop_name}
                </td>
              </tr>
              <tr>
                <td className="font-normal text-sm w-2/5">Sales Order ID</td>
                <td>:</td>
                <td className="text-neutralColor">
                  {detailOrderMpc?.so_number || "-"}
                </td>
              </tr>
              <tr>
                <td className="font-normal text-sm w-2/5">
                  Order Reference No.
                </td>
                <td>:</td>
                <td className="text-neutralColor">{"-"}</td>
              </tr>
              <tr>
                <td className="font-normal text-sm w-2/5">Payment Status</td>
                <td>:</td>
                <td className="text-neutralColor">{"-"}</td>
              </tr>
              <tr>
                <td className="font-normal text-sm w-2/5">
                  Fulfillment status
                </td>
                <td>:</td>
                <td className="text-neutralColor">{"-"}</td>
              </tr>

              <tr>
                <td className="font-normal text-sm w-2/5">
                  Shipping Label Printed Date
                </td>
                <td>:</td>
                <td className="text-neutralColor">{"-"}</td>
              </tr>
              <tr>
                <td className="font-normal text-sm w-2/5">
                  Ready to Ship Date
                </td>
                <td>:</td>
                <td className="text-neutralColor">{"-"}</td>
              </tr>
              <tr>
                <td className="font-normal text-sm w-2/5">Shipped Date</td>
                <td>:</td>
                <td className="text-neutralColor">{"-"}</td>
              </tr>
              <tr>
                <td className="font-normal text-sm w-2/5">Delivered Date</td>
                <td>:</td>
                <td className="text-neutralColor">{"-"}</td>
              </tr>
              <tr>
                <td className="font-normal text-sm w-2/5">Completed Date</td>
                <td>:</td>
                <td className="text-neutralColor">{"-"}</td>
              </tr>
              <tr>
                <td className="font-normal text-sm w-2/5">Cancelled Date</td>
                <td>:</td>
                <td className="text-neutralColor">{"-"}</td>
              </tr>
              <tr>
                <td className="font-normal text-sm w-2/5">Cancelled Reason</td>
                <td>:</td>
                <td className="text-neutralColor">{"-"}</td>
              </tr>
            </table>
          </div>
        </div>

        {/* items */}
        <div className="col-span-2">
          <div className="card">
            <div className="card-header">
              <h1 className="text-lg text-bold flex justify-content-between align-items-center">
                <span>Rincian Product</span>
              </h1>
            </div>
            <div className="card-body">
              <Table
                dataSource={items}
                columns={orderItemsColumn}
                loading={loading}
                pagination={false}
                rowKey="itemId"
                scroll={{ x: "max-content" }}
                tableLayout={"auto"}
                summary={(currentData) => {
                  const price = currentData.reduce(
                    (acc, curr) => parseInt(acc) + parseInt(curr.amount),
                    0
                  )
                  const discount = currentData.reduce(
                    (acc, curr) => parseInt(acc) + parseInt(curr.discount),
                    0
                  )
                  const total = price + discount

                  return (
                    <>
                      <Table.Summary.Row>
                        <Table.Summary.Cell colSpan={7} align="right">
                          Sub Total (Rp)
                        </Table.Summary.Cell>

                        <Table.Summary.Cell align="right">
                          {formatNumber(price, "Rp. ")}
                        </Table.Summary.Cell>
                        <Table.Summary.Cell />
                      </Table.Summary.Row>

                      <Table.Summary.Row>
                        <Table.Summary.Cell colSpan={7} align="right">
                          Discount (Rp)
                        </Table.Summary.Cell>

                        <Table.Summary.Cell align="right">
                          {formatNumber(discount, "Rp. ")}
                        </Table.Summary.Cell>
                        <Table.Summary.Cell />
                      </Table.Summary.Row>

                      <Table.Summary.Row>
                        <Table.Summary.Cell colSpan={7} align="right">
                          Total (Rp)
                        </Table.Summary.Cell>

                        <Table.Summary.Cell align="right">
                          {formatNumber(total, "Rp. ")}
                        </Table.Summary.Cell>
                        <Table.Summary.Cell />
                      </Table.Summary.Row>
                    </>
                  )
                }}
              />
            </div>
          </div>
        </div>
        {/* logistik */}
        <div className="">
          <div className="card">
            <div className="card-header">
              <h1 className="text-lg text-bold ">Rincian Pengiriman</h1>
            </div>
            <div className="card-body">
              <table className="w-100" style={{ width: "100%" }}>
                <tbody>
                  {/* <tr>
                    <td className="font-normal text-sm w-1/4">
                      <span>Type Pengiriman</span>
                    </td><td>:</td>
                    <td className="text-neutralColor">{detailOrderMpc?.shippingType}</td>
                  </tr> */}
                  <tr>
                    <td className="font-normal text-sm w-1/4">
                      <span>Shipping Courier</span>
                    </td>
                    <td>:</td>
                    <td className="text-neutralColor">
                      {detailOrderMpc?.logisticsProviderName}
                    </td>
                  </tr>
                  <tr>
                    <td className="font-normal text-sm w-1/4">
                      <span>Service Type</span>
                    </td>
                    <td>:</td>
                    <td className="text-neutralColor">{"-"}</td>
                  </tr>

                  <tr>
                    <td className="font-normal text-sm w-1/4">
                      <span>Nomor Resi</span>
                    </td>
                    <td>:</td>
                    <td className="text-neutralColor">
                      {detailOrderMpc?.logisticsTrackingNumber}
                    </td>
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
                    {shippingAddress?.name || "Dany Arkham"}
                  </p>
                </div>
              </div>
              <p style={{ fontSize: 12 }} className="pt-2 mb-0">
                {shippingAddress?.phoneNumber || "0857-****-****"}
              </p>
              <p style={{ fontSize: 12 }}>
                {shippingAddress?.fullAddress ||
                  "Pondok sukatani permai blok D1no16 RT/RW 06/03, KAB. TANGERANG, RAJEG, BANTEN, ID, 15540"}
              </p>
            </div>
          </div>
        </div>

        {/* pembayaran */}

        <div className="card">
          <div className="card-header">
            <h1 className="text-lg text-bold ">Rincian Pembayaran</h1>
          </div>
          <div className="card-body">
            <table className="w-full">
              <tbody>
                <tr>
                  <td className="font-normal text-sm w-2/5">Payment Type</td>
                  <td>:</td>
                  <td className="text-neutralColor">{"-"}</td>
                </tr>
                <tr>
                  <td className="font-normal text-sm w-2/5">Payment Date</td>
                  <td>:</td>
                  <td className="text-neutralColor">{"-"}</td>
                </tr>
                <tr>
                  <td className="font-normal text-sm w-2/5">
                    <span>Subtotal</span>
                  </td>
                  <td>:</td>
                  <td className="text-neutralColor">{paymentInfo?.subtotal}</td>
                </tr>

                <tr>
                  <td className="font-normal text-sm w-2/5">
                    <span>Diskon Pengiriman</span>
                  </td>
                  <td>:</td>
                  <td className="text-neutralColor">
                    {paymentInfo?.finalShippingFee}
                  </td>
                </tr>

                <tr>
                  <td className="font-normal text-sm w-2/5">
                    <span>Total Diskon</span>
                  </td>
                  <td>:</td>
                  <td className="text-neutralColor">
                    {paymentInfo?.totalDiscounts}
                  </td>
                </tr>
                <tr>
                  <td className="font-normal text-sm w-2/5">
                    <span>Ongkos Kirim</span>
                  </td>
                  <td>:</td>
                  <td className="text-neutralColor">
                    {paymentInfo?.totalShippingFee}
                  </td>
                </tr>
                <tr>
                  <td className="font-normal text-sm w-2/5">
                    <span>Pajak</span>
                  </td>
                  <td>:</td>
                  <td className="text-neutralColor">
                    {paymentInfo?.taxationFee}
                  </td>
                </tr>
                <tr>
                  <td className="font-normal text-sm w-2/5">
                    <span>Biaya Layanan</span>
                  </td>
                  <td>:</td>
                  <td className="text-neutralColor">
                    {paymentInfo?.serviceFee}
                  </td>
                </tr>
                <tr>
                  <td className="font-normal text-sm w-2/5">
                    <span>Biaya Asuransi</span>
                  </td>
                  <td>:</td>
                  <td className="text-neutralColor">
                    {paymentInfo?.insuranceFee}
                  </td>
                </tr>
                <tr>
                  <td className="font-normal text-sm w-2/5">
                    <span>Total Pembayaran</span>
                  </td>
                  <td>:</td>
                  <td className="text-neutralColor">
                    {paymentInfo?.sellerTotalAmount}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </Layout>
  )
}

export default OrderListDetailMpc
