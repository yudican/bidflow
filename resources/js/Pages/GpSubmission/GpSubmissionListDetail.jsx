import { ExportOutlined } from "@ant-design/icons"
import { Alert, Pagination, Table } from "antd"
import React, { useEffect, useState } from "react"
import { useParams } from "react-router-dom"
import Layout from "../../components/layout"
import { formatNumber, getItem } from "../../helpers"
import { orderListDetailGpColumn } from "./config"

const GpSubmissionListDetail = () => {
  const { list_id } = useParams()
  const [loading, setLoading] = useState(false)
  const [datas, setDatas] = useState([])
  const [errorData, setErrorData] = useState([])
  const [total, setTotal] = useState(0)
  const [currentPage, setCurrentPage] = useState(1)
  const base_url = getItem("service_ginee_url")

  const loadData = (
    url = "/api/channel/gp/list/detail/" + list_id,
    perpage = 10,
    params = {}
  ) => {
    setLoading(true)
    axios
      .post(base_url + url, { perpage, ...params })
      .then((res) => {
        const { data, total, current_page } = res.data.data
        setTotal(total)
        setErrorData(res.data.errorLogs)
        setCurrentPage(current_page)

        const newData = data.map((item) => {
          return {
            so_number: item?.so_number,
            tanggal_transaksi: item?.order_ginee?.tanggal_transaksi,
            channel: item?.order_ginee?.channel,
            store: item?.order_ginee?.store,
            sku: item?.order_ginee?.sku,
            custommer_id: item?.order_ginee?.custommer_id,
            nama_produk: item?.order_ginee?.nama_produk,
            u_of_m: item.u_of_m,
            qty_total: item?.order_ginee?.qty,
            extended_price: item?.extended_price * item?.order_ginee?.qty,
            freight_amount: item?.freight_amount,
            miscellaneous: item?.miscellaneous,
            trade_discount: item?.total_discount,
            tax_amount: item?.tax_amount,
          }
        })

        const groupedData = newData.reduce((acc, curr) => {
          const key = curr.channel
          if (!acc[key]) {
            acc[key] = {
              so_number: curr?.so_number,
              tanggal_transaksi: curr?.tanggal_transaksi,
              channel: curr?.channel,
              store: curr?.store,
              sku: curr?.sku,
              custommer_id: curr?.custommer_id,
              nama_produk: curr?.nama_produk,
              u_of_m: curr.u_of_m,
              qty_total: curr?.qty_total,
              extended_price: curr?.extended_price,
              freight_amount: curr?.freight_amount,
              miscellaneous: curr?.miscellaneous,
              trade_discount: curr?.trade_discount,
              tax_amount: curr?.tax_amount,
            }
          } else {
            acc[key].qty_total += curr.qty_total
            acc[key].extended_price += curr.extended_price
            acc[key].freight_amount += curr.freight_amount
            acc[key].trade_discount += curr.trade_discount
            acc[key].tax_amount += curr.tax_amount
          }
          return acc
        }, {})

        const newListData = Object.values(groupedData)
        const finalData = newListData.map((item) => {
          return {
            ...item,
            extended_price: formatNumber(
              Math.round(item?.extended_price),
              "Rp"
            ),
            freight_amount: formatNumber(
              Math.round(item?.freight_amount),
              "Rp"
            ),
            miscellaneous: formatNumber(Math.round(item?.miscellaneous), "Rp"),
            trade_discount: formatNumber(
              Math.round(item?.trade_discount),
              "Rp"
            ),
            tax_amount: formatNumber(Math.round(item?.tax_amount), "Rp"),
          }
        })

        setDatas(finalData)
        setLoading(false)
      })
      .catch((err) => {
        setLoading(false)
      })
  }
  useEffect(() => {
    loadData()
  }, [])

  const handleChangePage = (page, pageSize = 10) => {
    loadData(`/api/channel/gp/list/detail/${list_id}/?page=${page}`, pageSize)
  }

  const rightContent = (
    <div className="flex justify-between items-center">
      <a
        href={`/gp-submission/export/${list_id}`}
        className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-4"
        target={"_blank"}
      >
        <ExportOutlined />
        <span className="ml-2">Export Data</span>
      </a>
    </div>
  )

  return (
    <Layout title="Submission Items" rightContent={rightContent}>
      <div className="card">
        <div className="card-body">
          <div>
            {errorData &&
              errorData.map((item, index) => (
                <Alert
                  description={item?.error_message}
                  type="error"
                  showIcon
                  className="mb-4"
                />
              ))}
          </div>
          <Table
            dataSource={datas}
            columns={orderListDetailGpColumn}
            loading={loading}
            pagination={false}
            rowKey="id"
            scroll={{ x: "max-content" }}
            tableLayout={"auto"}
          />
          <Pagination
            defaultCurrent={1}
            current={currentPage}
            total={total}
            className="mt-4 text-center"
            onChange={handleChangePage}
          />
        </div>
      </div>
    </Layout>
  )
}

export default GpSubmissionListDetail
