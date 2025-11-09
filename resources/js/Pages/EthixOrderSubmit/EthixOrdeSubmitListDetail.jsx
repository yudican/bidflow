import { CloseCircleFilled, SearchOutlined } from "@ant-design/icons"
import { Alert, Input, Pagination, Table } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { useNavigate, useParams } from "react-router-dom"
import Layout from "../../components/layout"
import { formatNumber, getItem } from "../../helpers"
import FilterModal from "./Components/FilterModal"
import { orderSubmitDetailColumn } from "./config"

const EthixOrdeSubmitListDetail = () => {
  const { submit_id } = useParams()
  const navigate = useNavigate()
  const [loading, setLoading] = useState(false)
  const [orderLead, setOrderLead] = useState([])
  const [total, setTotal] = useState(0)
  const [search, setSearch] = useState("")
  const [isSearch, setIsSearch] = useState(false)
  const [currentPage, setCurrentPage] = useState(1)
  const [filterData, setFilterData] = useState({})
  const [loadingExport, setLoadingExport] = useState(false)
  const [selectedRowKeys, setSelectedRowKeys] = useState([])
  const [readyToSubmit, setReadyToSubmit] = useState(false)

  const loadData = (
    url = "/api/order/submit/history/" + submit_id,
    perpage = 10,
    params = { page: currentPage }
  ) => {
    setLoading(true)
    axios
      .post(url, { perpage, account_id: getItem("account_id"), ...params })
      .then((res) => {
        const { data, total, current_page } = res.data.data
        setTotal(total)
        setCurrentPage(current_page)
        const newData = data.map((item) => {
          return {
            ...item,
            si_number: item.order.invoice_number,
            extended_price: formatNumber(
              Math.round(item?.extended_price),
              "Rp "
            ),
            discount_amount: formatNumber(
              Math.round(item?.discount_amount),
              "Rp "
            ),
            tax_amount: formatNumber(Math.round(item?.tax_amount), "Rp "),
            freight: formatNumber(Math.round(item?.freight), "Rp "),
            misc_amount: formatNumber(Math.round(item?.misc_amount), "Rp "),
          }
        })
        setOrderLead(newData)
        setLoading(false)
      })
  }
  useEffect(() => {
    loadData()
  }, [])

  const handleChange = (page, pageSize = 10) => {
    loadData(`/api/order/submit/history/${submit_id}/?page=${page}`, pageSize, {
      search,
      page,
      ...filterData,
    })
  }

  const handleChangeSearch = () => {
    setIsSearch(true)
    loadData(`/api/order/submit/history/${submit_id}`, 10, { search })
  }

  const handleFilter = (data) => {
    setFilterData(data)
    loadData(`/api/order/submit/history/${submit_id}`, 10, data)
  }

  // const handleExportContent = () => {
  //   setLoadingExport(true)
  //   axios
  //     // .post(`/api/order/submit/history/export/`, {id})
  //     .post(`/api/order/submit/history/export/detail/1`)
  //     .then((res) => {
  //       const { data } = res.data
  //       window.open(data)
  //       setLoadingExport(false)
  //     })
  //     .catch((e) => setLoadingExport(false))
  // }

  const rightContent = (
    <div className="flex justify-between items-center">
      <FilterModal handleOk={handleFilter} />
      {/* <button
        className="ml-3 text-white bg-green-800 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mr-2"
        onClick={() => handleExportContent()}
      >
        {loadingExport ? <LoadingOutlined /> : <FileExcelOutlined />}
        <span className="ml-2">Export</span>
      </button> */}
    </div>
  )

  // selected row handler
  const rowSelection = {
    selectedRowKeys,
    onChange: (e) => setSelectedRowKeys(e),
    getCheckboxProps: (record) => ({
      disabled: record.status !== "failed", // Column configuration not to be checked
    }),
  }

  return (
    <Layout
      rightContent={rightContent}
      title="Detail Submit SI"
      href="/ethix/submit/history"
    >
      <div className="card">
        <div className="card-body">
          <div className="row mb-4">
            <div className="col-md-4 col-sm-6 col-12">
              <Input
                placeholder="Cari disini"
                size={"large"}
                className="rounded"
                onPressEnter={() => handleChangeSearch()}
                suffix={
                  isSearch ? (
                    <CloseCircleFilled
                      onClick={() => {
                        loadData()
                        setSearch(null)
                        setIsSearch(false)
                      }}
                    />
                  ) : (
                    <SearchOutlined onClick={() => handleChangeSearch()} />
                  )
                }
                value={search}
                onChange={(e) => setSearch(e.target.value)}
              />
            </div>
            <div className="col-md-8">
              <strong className="float-right mt-3 text-red-400">
                Total Data: {total}
              </strong>
            </div>
          </div>
          {orderLead &&
            orderLead
              .filter((order) => order.status === "failed")
              .map((item, index) => (
                <Alert
                  key={index}
                  description={`${item.si_number} -  ${item?.error_message}`}
                  type="error"
                  showIcon
                  className="mb-4"
                />
              ))}
          <Table
            dataSource={orderLead}
            columns={[...orderSubmitDetailColumn]}
            loading={loading}
            pagination={false}
            rowKey="id"
            scroll={{ x: "max-content" }}
            tableLayout={"auto"}
            rowSelection={readyToSubmit ? rowSelection : null}
          />
          <Pagination
            defaultCurrent={1}
            current={currentPage}
            total={total}
            className="mt-4 text-center"
            onChange={handleChange}
            pageSizeOptions={["10", "20", "50", "100", "200", "500"]}
          />
        </div>
      </div>
    </Layout>
  )
}

export default EthixOrdeSubmitListDetail
