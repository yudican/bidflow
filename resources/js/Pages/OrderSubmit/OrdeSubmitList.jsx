import { CloseCircleFilled, SearchOutlined } from "@ant-design/icons"
import { Input, Pagination, Table } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { useNavigate } from "react-router-dom"
import Layout from "../../components/layout"
import { getItem } from "../../helpers"
import FilterModal from "./Components/FilterModal"
import { orderSubmitColumn } from "./config"
import { toast } from "react-toastify"

const OrdeSubmitList = ({
  type = ["order-lead", "order-manual", "freebies", "invoice-so"],
  action = "order",
  columns = orderSubmitColumn,
  title = "History Submit",
}) => {
  const url = new URL(window.location.href)
  const pathName = url?.pathname
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
    url = "/api/order/submit/history",
    perpage = 10,
    params = { page: currentPage }
  ) => {
    setLoading(true)
    axios
      .post(url, {
        perpage,
        account_id: getItem("account_id"),
        type,
        ...params,
      })
      .then((res) => {
        const { data, total, current_page, from } = res.data.data
        setTotal(total)
        setCurrentPage(current_page)

        setOrderLead(
          data.map((item, index) => ({ ...item, number: from + index }))
        )
        setLoading(false)
      })
      .catch((err) => {
        const { message } = err.response.data
        toast.error(message)
        setLoading(false)
      })
  }
  useEffect(() => {
    loadData()
  }, [])

  const handleChange = (page, pageSize = 10) => {
    loadData(`/api/order/submit/history/?page=${page}`, pageSize, {
      search,
      page,
      ...filterData,
    })
  }

  const handleChangeSearch = () => {
    setIsSearch(true)
    loadData(`/api/order/submit/history`, 10, { search })
  }

  const handleFilter = (data) => {
    setFilterData(data)
    loadData(`/api/order/submit/history`, 10, data)
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
      title={title}
      onClick={() => navigate(-1)}
    >
      <div className="card">
        <div className="card-body">
          <div className="row card-body   mb-4">
            <div className="col-md-12"></div>
            <div className="col-md-4 col-sm-6 col-12">
              <Input
                placeholder="Cari berdasarkan submited by"
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
          <Table
            dataSource={orderLead}
            columns={
              // pathName !== "/order/submit/history"
              //   ?
              [
                ...columns,
                // {
                //   title: "Action",
                //   dataIndex: "action",
                //   key: "action",
                //   fixed: "right",
                //   align: "center",
                //   render: (text, record) => {
                //     if (action === "purchase") {
                //       return (
                //         <button
                //           className="text-white bg-blue-800 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mr-2"
                //           onClick={() =>
                //             navigate(`/${action}/history-submit/${record.id}`)
                //           }
                //         >
                //           <span>Detail</span>
                //         </button>
                //       )
                //     }
                //     return (
                //       <button
                //         className="text-white bg-blue-800 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mr-2"
                //         onClick={() =>
                //           navigate(`/${action}/submit/history/${record.id}`)
                //         }
                //       >
                //         <span>Detail</span>
                //       </button>
                //     )
                //   },
                // },
              ]
              // : [...columns]
            }
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

export default OrdeSubmitList
