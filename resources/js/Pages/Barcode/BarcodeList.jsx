import {
  CloseCircleFilled,
  FolderFilled,
  SearchOutlined,
} from "@ant-design/icons"
import { Input, Pagination, Table } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { useNavigate } from "react-router-dom"
import Layout from "../../components/layout"
import { formatNumber, getItem, inArray } from "../../helpers"
import FilterModal from "./Components/FilterModal"
import { binListColumn } from "./config"

const BarcodeList = () => {
  const navigate = useNavigate()
  const [loading, setLoading] = useState(false)
  const [datas, setDatas] = useState([])
  const [total, setTotal] = useState(0)
  const [search, setSearch] = useState("")
  const [isSearch, setIsSearch] = useState(false)
  const [currentPage, setCurrentPage] = useState(1)
  const [filterData, setFilterData] = useState({})
  const [loadingExport, setLoadingExport] = useState(false)
  const [bins, setBins] = useState([])
  const [selectedBin, setSelectedBin] = useState(null)

  const loadData = (
    url = "/api/barcode/list",
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
        const newData = data.map((item) => {
          return {
            ...item,
            id: item.id,
            name: item.name,
            package_name: item.package_name,
            variant_name: item.variant_name,
            product_image: item?.image_url,
            status: item?.status,
            stock: item?.stocks,
            stock_off_market: item?.stock_of_market,
            final_price: formatNumber(item?.price?.final_price, "Rp. "),
          }
        })

        setDatas(newData)
        setLoading(false)
      })
      .catch((e) => setLoading(false))
  }

  const loadBin = () => {
    axios
      .get("/api/master/bin")
      .then((res) => {
        const { data } = res.data
        setBins(data)
      })
      .catch((e) => setLoading(false))
  }

  useEffect(() => {
    loadBin()
    loadData()
  }, [])

  const handleChange = (page, pageSize = 10) => {
    loadData(`/api/barcode/list?page=${page}`, pageSize, {
      search,
      page,
      ...filterData,
    })
  }

  const handleChangeSearch = () => {
    setIsSearch(true)
    loadData(`/api/barcode/list`, 10, { search })
  }

  const handleFilter = (data) => {
    setFilterData(data)
    loadData(`/api/barcode/list`, 10, data)
  }

  const show = !inArray(getItem("role"), [
    "adminsales",
    "leadwh",
    "leadsales",
    "leadcs",
  ])

  const rightContent = (
    <div className="flex justify-between items-center">
      <FilterModal handleOk={handleFilter} />
    </div>
  )

  return (
    <Layout rightContent={rightContent} title="Generate Barcode">
      <div className="card">
        <div className="card-body">
          <div className="row card-body   mb-4">
            {/* <div className="flex overflow-y-auto mb-4">
          {[...bins]?.map((item, index) => (
            <div
              key={index}
              className="cursor-pointer mr-4"
              onClick={() => {
                setSelectedBin(item)
                loadData(`/api/barcode/list`, 10, {
                  master_bin_id: item.id,
                })
              }}
            >
              <div
                key={index}
                className={`
                  card w-96
                  bg-gradient-to-r from-white via-white ${
                    selectedBin?.id === item.id ? "to-blue-500/20" : ""
                  }
                  hover:to-blue-500/20
                `}
              >
                <div className="p-3 border-b flex justify-between">
                  <div className="flex items-center">
                    <strong
                      className={`text-base font-semibold text-${
                        selectedBin?.id === item.id ? "blue-500" : "black"
                      }`}
                    >
                      {item.name}
                    </strong>
                  </div>
                </div>
                <div className="card-body">
                  <strong
                    className={`text-${
                      selectedBin?.id === item.id ? "blue-500" : "black"
                    } text-lg font-medium`}
                  >
                    Total Stock: {item.stock}
                  </strong>
                </div>
              </div>
            </div>
          ))}
        </div> */}
            <div className="col-md-12"></div>
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
          <Table
            scroll={{ x: "max-content" }}
            tableLayout={"auto"}
            dataSource={datas}
            columns={[
              ...binListColumn,
              {
                title: "Action",
                key: "action",
                dataIndex: "action",
                render: (_, record) => {
                  return (
                    <button
                      onClick={() => {
                        navigate("/barcode/detail/" + record.id)
                      }}
                      className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
                    >
                      <FolderFilled />
                      <span className="ml-2">{`Detail`}</span>
                    </button>
                  )
                },
              },
            ]}
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
            pageSizeOptions={["10", "20", "50", "100", "200", "500"]}
          />
        </div>
      </div>
    </Layout>
  )
}

export default BarcodeList
