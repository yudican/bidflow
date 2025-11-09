import {
  CloseCircleFilled,
  DeleteOutlined,
  EditOutlined,
  FileExcelOutlined,
  LoadingOutlined,
  PlusOutlined,
  RightOutlined,
  SearchOutlined,
} from "@ant-design/icons"
import { Dropdown, Input, Menu, Pagination, Popconfirm, Table } from "antd"
import React, { useEffect, useState } from "react"
import { useNavigate } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../components/layout"
import { getItem, inArray } from "../../helpers"
import FilterModal from "./Components/FilterModal"
import { comissionWithdrawDetailColumn } from "./config"

const ComissionWithdrawDetail = () => {
  const navigate = useNavigate()
  const [loading, setLoading] = useState(false)
  const [datas, setDatas] = useState([])
  const [total, setTotal] = useState(0)
  const [search, setSearch] = useState("")
  const [isSearch, setIsSearch] = useState(false)
  const [currentPage, setCurrentPage] = useState(1)
  const [filterData, setFilterData] = useState({})
  const [loadingExport, setLoadingExport] = useState(false)

  const loadData = (
    url = "/api/transaction/list",
    perpage = 10,
    params = { page: currentPage }
  ) => {
    setLoading(true)
    axios
      .post(url, {
        perpage,
        account_id: getItem("account_id"),
        action: "withdrawal",
        ...params,
      })
      .then((res) => {
        const { data, total, current_page } = res.data.data
        setTotal(total)
        setCurrentPage(current_page)
        let akumulasi = 0
        const newData = data.map((item) => {
          akumulasi += item.bagi_hasil.total_pembagian
          return {
            ...item,
            akumulasi,
          }
        })

        setDatas(newData)
        setLoading(false)
      })
  }
  useEffect(() => {
    loadData()
  }, [])

  const handleChange = (page, pageSize = 10) => {
    loadData(`/api/transaction/list/?page=${page}`, pageSize, {
      search,
      page,
      ...filterData,
    })
  }

  const handleChangeSearch = () => {
    setIsSearch(true)
    loadData(`/api/transaction/list`, 10, { search })
  }

  const handleFilter = (data) => {
    setFilterData(data)
    loadData(`/api/transaction/list`, 10, data)
  }

  const handleExportContent = () => {
    setLoadingExport(true)
    axios
      .post(`/api/transaction/withdraw/bagi-hasil/export`)
      .then((res) => {
        const { data } = res.data
        window.open(data)
        setLoadingExport(false)
      })
      .catch((e) => setLoadingExport(false))
  }

  const rightContent = (
    <div className="flex justify-between items-center">
      <button
        className="ml-3 text-white bg-green-500 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mr-2"
        onClick={() => handleExportContent()}
      >
        {loadingExport ? <LoadingOutlined /> : <FileExcelOutlined />}
        <span className="ml-2">Export to Excel</span>
      </button>

      <FilterModal handleOk={handleFilter} />
    </div>
  )

  return (
    <Layout
      // onClick={() => navigate("/")}
      rightContent={rightContent}
      title="Comission Withdraw"
      href="/comission-withdraw"
    >
      <div className="card">
        <div className="card-body">
          <div className=" mb-4">
            {/* <h1 className="text-xl font-bold px-3">Data Bagi Hasil</h1> */}
            <div className="row">
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
                  Total yang dapat dibayarkan : {total}
                </strong>
              </div>
            </div>
          </div>
          <Table
            dataSource={datas}
            columns={[...comissionWithdrawDetailColumn]}
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
            onChange={handleChange}
            pageSizeOptions={["10", "20", "50", "100"]}
          />
        </div>
      </div>
    </Layout>
  )
}

export default ComissionWithdrawDetail
