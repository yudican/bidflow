import { Select, Spin, Table } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { useParams } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../../components/layout"
import { formatNumber } from "../../../helpers"
import ModalTax from "../../Genie/Components/ModalTax"
import { itemDetails } from "./config"

const DetailOrderMP = () => {
  const params = useParams()
  const [detailOrderMpc, setDetailMpc] = useState(null)
  const [loading, setLoading] = useState(false)
  const [loadingSubmit, setLoadingSubmit] = useState(false)
  const [selectedProducts, setSelectedProducts] = useState([])
  const [warehouses, setWarehouses] = useState([])

  const loadDetailGinie = () => {
    setLoading(true)
    axios.get(`/api/marketplace/detail/${params.orderId}`).then((res) => {
      const { data } = res.data
      setDetailMpc(data)
      setLoading(false)
      const products =
        data?.items?.map((row, index) => {
          return {
            key: index,
            id: row.id,
            order_id: data.id,
            trx_id: data.trx_id,
            product_name: row.product_name,
            sku: row.sku,
          }
        }) || []

      setSelectedProducts(products)
    })
  }

  const loadWarehouse = () => {
    axios.get("/api/master/warehouse").then((res) => {
      setWarehouses(res.data.data)
    })
  }

  useEffect(() => {
    loadDetailGinie()
    loadWarehouse()
  }, [])

  const handleSubmitEthix = () => {
    setLoadingSubmit(true)
    axios
      .post(`/api/marketplace/submit/ethix`, { ids: [detailOrderMpc.id] })
      .then((res) => {
        const { data } = res.data
        setLoadingSubmit(false)
        toast.success("Data berhasil Disubmit Ke Ethix")
      })
      .catch((err) => {
        setLoadingSubmit(false)
        toast.success("Data gagal Disubmit Ke Ethix")
      })
  }

  const handleSubmitGp = (value) => {
    setLoadingSubmit(true)
    const hasLocNode = selectedProducts.every((item) => item.loc_node)
    if (!hasLocNode) {
      toast.error("Lokasi Site ID harus diisi")
      return setLoadingSubmit(false)
    }
    axios
      .post(`/api/marketplace/submit`, {
        ids: [detailOrderMpc.id],
        ...value,
        products: selectedProducts,
      })
      .then((res) => {
        const { data } = res.data
        toast.success("Order marketplace berhasil di submit")
        setLoadingSubmit(false)
      })
      .catch((e) => {
        setLoadingSubmit(false)
        toast.error("Error submitting order marketplace")
      })
  }

  const { items } = detailOrderMpc || {}

  const handleChangeProduct = (e, index) => {
    const data = [...selectedProducts]
    data[index].loc_node = e
    setSelectedProducts(data)
  }

  const updateWarehouse = (wh_id) => {
    axios
      .post(`/api/marketplace/update/warehouse`, {
        orderId: params.orderId,
        wh_id,
      })
      .then((res) => {
        const { data } = res.data
        toast.success("Warehouse berhasil di update")
        setLoadingSubmit(false)
        loadDetailGinie()
      })
      .catch((e) => {
        setLoadingSubmit(false)
        toast.error("Error update order Warehouse")
      })
  }

  const isNotSubmited =
    detailOrderMpc?.status_ethix == "notsubmited" &&
    detailOrderMpc?.status_gp == "notsubmited"
  return (
    <Layout title="Detail" href="/marketplace/list">
      <div className="row">
        <div className="col-md-6">
          <div className="card">
            <div className="card-header">
              <h3 className="card-title">Shipping Info</h3>
            </div>
            <div className="card-body">
              {loading ? (
                <div className="h-48 w-full flex justify-center items-center">
                  <Spin />
                </div>
              ) : (
                <table className="table-auto">
                  <tr className="h-8">
                    <td className="w-40">Nama Penerima</td>
                    <td>:</td>
                    <td className="text-neutralColor">
                      {detailOrderMpc?.customer_name}
                    </td>
                  </tr>
                  <tr className="h-8">
                    <td className="w-40">Marketplace Name</td>
                    <td>:</td>
                    <td className="text-neutralColor">
                      {detailOrderMpc?.channel}
                    </td>
                  </tr>
                  <tr className="h-8">
                    <td className="w-40">Shop Name</td>
                    <td>:</td>
                    <td className="text-neutralColor">
                      {detailOrderMpc?.store}
                    </td>
                  </tr>
                  <tr className="h-8">
                    <td className="w-40">Kurir</td>
                    <td>:</td>
                    <td className="text-neutralColor">
                      {detailOrderMpc?.courir}
                    </td>
                  </tr>
                  <tr className="h-8">
                    <td className="w-40">Nomor Resi</td>
                    <td>:</td>
                    <td className="text-neutralColor">{detailOrderMpc?.awb}</td>
                  </tr>
                  <tr className="h-8">
                    <td className="w-40">Status</td>
                    <td>:</td>
                    <td className="text-neutralColor">
                      {detailOrderMpc?.status}
                    </td>
                  </tr>
                  <tr className="h-8">
                    <td className="w-40">Shipping Status</td>
                    <td>:</td>
                    <td className="text-neutralColor">
                      {detailOrderMpc?.shipping_status}
                    </td>
                  </tr>
                </table>
              )}
            </div>
          </div>
        </div>
        <div className="col-md-6">
          <div className="card">
            <div className="card-header">
              <h3 className="card-title">Receipient Info</h3>
            </div>
            <div className="card-body">
              {loading ? (
                <div className="h-48 w-full flex justify-center items-center">
                  <Spin />
                </div>
              ) : (
                <table className="table-auto">
                  <tr className="h-8">
                    <td className="w-40">
                      <strong>TRX ID</strong>
                    </td>
                    <td>:</td>
                    <td className="text-neutralColor">
                      {detailOrderMpc?.trx_id}
                    </td>
                  </tr>
                  <tr className="h-8">
                    <td className="w-40">
                      <strong>GP Number</strong>
                    </td>
                    <td>:</td>
                    <td className="text-neutralColor">
                      {detailOrderMpc?.gp_number || "-"}
                    </td>
                  </tr>
                  <tr className="h-8">
                    <td className="w-40">
                      <strong>TRX Date</strong>
                    </td>
                    <td>:</td>
                    <td className="text-neutralColor">
                      {detailOrderMpc?.trx_date || "-"}
                    </td>
                  </tr>
                  <tr className="h-8">
                    <td className="w-40">
                      <strong>Warehouse</strong>
                    </td>
                    <td>:</td>
                    <td className="text-neutralColor">
                      {detailOrderMpc?.warehouse}
                    </td>
                  </tr>
                  <tr className="h-8">
                    <td className="w-40">
                      <strong>Payment Method</strong>
                    </td>
                    <td>:</td>
                    <td className="text-neutralColor">
                      {detailOrderMpc?.payment_method}
                    </td>
                  </tr>

                  <tr className="h-8">
                    <td className="w-40">
                      <strong>Status Ethix</strong>
                    </td>
                    <td>:</td>
                    <td className="text-neutralColor">
                      {detailOrderMpc?.status_ethix || "notsubmited"}
                    </td>
                  </tr>
                  <tr className="h-8">
                    <td className="w-40">
                      <strong>Status GP</strong>
                    </td>
                    <td>:</td>
                    <td className="text-neutralColor">
                      {detailOrderMpc?.status_gp || "notsubmited"}
                    </td>
                  </tr>
                </table>
              )}
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
                dataSource={items}
                columns={itemDetails}
                loading={loading}
                pagination={false}
                rowKey="id"
                scroll={{ x: "max-content" }}
                tableLayout={"auto"}
                summary={(currentData) => {
                  const total = 0
                  const price = currentData.reduce(
                    (acc, curr) => parseInt(acc) + parseInt(curr.final_price),
                    0
                  )
                  return (
                    <>
                      <Table.Summary.Row>
                        <Table.Summary.Cell colSpan={4} align="right">
                          Sub Total (Rp)
                        </Table.Summary.Cell>

                        <Table.Summary.Cell align="right">
                          {formatNumber(price, "Rp. ")}
                        </Table.Summary.Cell>
                        <Table.Summary.Cell />
                      </Table.Summary.Row>

                      <Table.Summary.Row>
                        <Table.Summary.Cell colSpan={4} align="right">
                          Discount (Rp)
                        </Table.Summary.Cell>

                        <Table.Summary.Cell align="right">
                          {formatNumber(detailOrderMpc?.discount, "Rp. ")}
                        </Table.Summary.Cell>
                        <Table.Summary.Cell />
                      </Table.Summary.Row>
                      <Table.Summary.Row>
                        <Table.Summary.Cell colSpan={4} align="right">
                          MP Fee (Rp)
                        </Table.Summary.Cell>

                        <Table.Summary.Cell align="right">
                          {formatNumber(detailOrderMpc?.mp_fee, "Rp. ")}
                        </Table.Summary.Cell>
                        <Table.Summary.Cell />
                      </Table.Summary.Row>
                      <Table.Summary.Row>
                        <Table.Summary.Cell colSpan={4} align="right">
                          Shipping Fee (Rp)
                        </Table.Summary.Cell>

                        <Table.Summary.Cell align="right">
                          {formatNumber(
                            parseInt(detailOrderMpc?.shipping_fee) +
                              parseInt(detailOrderMpc?.shipping_fee_deference),
                            "Rp. "
                          )}
                        </Table.Summary.Cell>
                        <Table.Summary.Cell />
                      </Table.Summary.Row>

                      <Table.Summary.Row>
                        <Table.Summary.Cell colSpan={4} align="right">
                          Total (Rp)
                        </Table.Summary.Cell>

                        <Table.Summary.Cell align="right">
                          {formatNumber(detailOrderMpc?.total_amount, "Rp. ")}
                        </Table.Summary.Cell>
                        <Table.Summary.Cell />
                      </Table.Summary.Row>
                    </>
                  )
                }}
              />
            </div>
          </div>

          {/* pengiriman */}
          {/* <div className="card">
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
                    {detailOrderMpc?.name}
                  </p>
                </div>
              </div>
              <p style={{ fontSize: 12 }} className="pt-2 mb-0">
                {detailOrderMpc?.phone}
              </p>
              <p style={{ fontSize: 12 }}>{detailOrderMpc?.full_address}</p>
            </div>
          </div> */}
        </div>

        {/* change warehouse */}
        {detailOrderMpc && (
          <div className="col-md-12">
            <div className="card ">
              <div className="card-body">
                <Select
                  value={detailOrderMpc?.warehouse}
                  className="w-full"
                  onChange={(e) => updateWarehouse(e)}
                >
                  {warehouses.map((item) => (
                    <Select.Option
                      key={item.id}
                      value={item.wh_id}
                      disabled={!item.wh_id}
                    >
                      {item.wh_id} - {item.name}
                    </Select.Option>
                  ))}
                </Select>
              </div>
            </div>
          </div>
        )}

        {detailOrderMpc && isNotSubmited && (
          <div className="col-md-12">
            <div className="card ">
              {loadingSubmit ? (
                <div className="card-body text-right">
                  <button
                    className="text-white bg-white  ring-2 outline-none ring-orange-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mr-3"
                    disabled
                  >
                    <Spin className="text-white" />
                    <span className="text-orange-500 ml-2"> ...Loading</span>
                  </button>
                  <button
                    className="text-white bg-white  ring-2 outline-none ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
                    disabled
                  >
                    <Spin className="text-white" />
                    <span className="text-blue-700 ml-2"> ...Loading</span>
                  </button>
                </div>
              ) : (
                <div className="card-body text-right flex items-center justify-end">
                  {detailOrderMpc?.status_ethix == "notsubmited" && (
                    <button
                      onClick={() => handleSubmitEthix()}
                      className="text-white bg-orange-500 hover:bg-orange-800 focus:ring-4 focus:outline-none focus:ring-orange-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mr-3"
                    >
                      Submit To Ethix
                    </button>
                  )}

                  {detailOrderMpc?.status_gp == "notsubmited" && (
                    <ModalTax
                      handleSubmit={(e) => handleSubmitGp(e)}
                      products={selectedProducts}
                      onChange={handleChangeProduct}
                      type={"marketplace"}
                      isAction
                    />
                  )}
                </div>
              )}
            </div>
          </div>
        )}
      </div>
    </Layout>
  )
}

export default DetailOrderMP
