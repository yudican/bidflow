import {
  CloseCircleFilled,
  DownCircleFilled,
  FileExcelOutlined,
  LoadingOutlined,
  SearchOutlined,
} from "@ant-design/icons"
import { Input, Pagination, Table } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { useNavigate, useParams } from "react-router-dom"
import Layout from "../../../components/layout"
import { productConvertDetailListColumn } from "./config"

const ConvertProductDetailList = () => {
  const navigate = useNavigate()
  const { convert_id } = useParams()
  const [loading, setLoading] = useState(false)
  const [datas, setDatas] = useState([])

  const [total, setTotal] = useState(0)
  const [search, setSearch] = useState("")
  const [isSearch, setIsSearch] = useState(false)
  const [currentPage, setCurrentPage] = useState(1)

  const [loadingExport, setLoadingExport] = useState(false)
  const [loadingExportDetail, setLoadingExportDetail] = useState(false)

  const loadData = (
    url = "/api/product-management/convert/detail/" + convert_id,
    perpage = 10,
    params = { page: currentPage }
  ) => {
    setLoading(true)
    axios
      .post(url, { perpage, ...params })
      .then((res) => {
        const { data, total, current_page } = res.data.data
        setTotal(total)
        setCurrentPage(current_page)
        setDatas(data)
        setLoading(false)
      })
      .catch((e) => setLoading(false))
  }

  useEffect(() => {
    loadData()
  }, [])

  const handleChange = (page, pageSize = 10) => {
    loadData(
      `/api/product-management/convert/detail/${convert_id}?page=${page}`,
      pageSize,
      {
        search,
        page,
      }
    )
  }

  const handleChangeSearch = () => {
    setIsSearch(true)
    loadData(`/api/product-management/convert/detail/` + convert_id, 10, {
      search,
    })
  }
  const handleExportContent = () => {
    setLoadingExport(true)
    axios
      .post(`/api/product-management/convert/export/` + convert_id)
      .then((res) => {
        const { data } = res.data
        window.open(data)
        setLoadingExport(false)
      })
      .catch((e) => setLoadingExport(false))
  }

  const handleExportDetail = () => {
    setLoadingExportDetail(true)
    axios
      .post(`/api/product-management/convert/export/detail/` + convert_id)
      .then((res) => {
        const { data } = res.data
        window.open(data)
        setLoadingExportDetail(false)
      })
      .catch((e) => setLoadingExportDetail(false))
  }

  const rightContent = (
    <div className="flex items-center">
      <button
        className="text-white bg-green-800 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mr-2"
        onClick={() => handleExportContent()}
      >
        {loadingExport ? <LoadingOutlined /> : <FileExcelOutlined />}
        <span className="ml-2">Export</span>
      </button>

      <button
        className="text-white bg-blue-800 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
        onClick={() => handleExportDetail()}
      >
        {loadingExportDetail ? <LoadingOutlined /> : <DownCircleFilled />}
        <span className="ml-2">Export Convert</span>
      </button>
    </div>
  )

  return (
    <Layout
      title="List Convert Detail"
      href="/product-management/convert-product"
      rightContent={rightContent}
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
          {/* <Progress percent={progressData?.percentage} status="active" /> */}
          <Table
            scroll={{ x: "max-content" }}
            tableLayout={"auto"}
            dataSource={datas}
            columns={[...productConvertDetailListColumn]}
            loading={loading}
            pagination={false}
            rowKey="id"
          />
          <Pagination
            defaultCurrent={1}
            current={currentPage}
            total={total}
            className="mt-4 text-center"
            onChange={handleChange}
          />
        </div>
      </div>
    </Layout>
  )
}

export default ConvertProductDetailList
