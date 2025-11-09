import {
  CloseCircleFilled,
  FileExcelOutlined,
  FolderFilled,
  LoadingOutlined,
  SearchOutlined,
} from "@ant-design/icons"
import { Input, Pagination, Select, Table } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { useNavigate } from "react-router-dom"
import FilterTagModal from "../../components/FilterTagModal"
import Layout from "../../components/layout"
import { formatNumber, getItem, inArray } from "../../helpers"
import FilterModal from "./Components/FilterModal"
import { binListColumn } from "./config"

const BinList = () => {
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

  const loadData = (url = "/api/bin/list", perpage = 10, params = {}) => {
    setLoading(true)
    console.log(params, "params")
    axios
      .post(url, {
        perpage,
        master_bin_id: ["all"],
        page: currentPage,
        ...params,
      })
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
            realstocks: item?.realstocks,
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
        let newData = [{ id: null, name: "All (Default)" }, ...data]
        setBins(newData)
      })
      .catch((e) => setLoading(false))
  }

  useEffect(() => {
    loadBin()
    loadData()
  }, [])

  const handleChange = (page, pageSize = 10) => {
    loadData(`/api/bin/list?page=${page}`, pageSize, {
      search,
      page,
      ...filterData,
    })
  }

  const handleChangeSearch = () => {
    setIsSearch(true)
    loadData(`/api/bin/list`, 10, {
      search,
      page: 1,
      ...filterData,
    })
  }

  const handleFilter = (data) => {
    setFilterData(data)
    loadData(`/api/bin/list`, 10, { ...data, search, page: 1 })
  }

  const handleExportContent = () => {
    setLoadingExport(true)
    axios
      .post(`/api/bin/export`, filterData)
      .then((res) => {
        const { data } = res.data
        window.open(data)
        setLoadingExport(false)
      })
      .catch((e) => setLoadingExport(false))
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
      <button
        className="ml-3 text-white bg-green-800 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mr-2"
        onClick={() => (loadingExport ? null : handleExportContent())}
      >
        {loadingExport ? <LoadingOutlined /> : <FileExcelOutlined />}
        <span className="ml-2">Export</span>
      </button>
    </div>
  )

  return (
    <Layout rightContent={rightContent} title="Stock Bin">
      <div className="card">
        <div className="card-body">
          <FilterTagModal handleOk={handleFilter} isBin />

          <div className="mb-4">
            {/* <div className="flex overflow-y-auto mb-4">
              {[...bins]?.map((item, index) => (
                <div
                  key={index}
                  className="cursor-pointer mr-4"
                  onClick={() => {
                    setSelectedBin(item)
                    loadData(`/api/bin/list`, 10, {
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

            <div className="flex justify-between items-start">
              <Input
                placeholder="Cari disini"
                size={"large"}
                className="rounded max-w-lg"
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

              <div className="flex flex-col items-end">
                <Select
                  className="w-[20rem]"
                  placeholder="Filter BIN"
                  showSearch
                  filterOption={(input, option) => {
                    return (option?.children ?? "")
                      .toLowerCase()
                      .includes(input.toLowerCase())
                  }}
                  onChange={(item) => {
                    let newItem = bins.find((value) => value.id === item)
                    setSelectedBin(newItem)
                    loadData(`/api/bin/list`, 10, {
                      master_bin_id: newItem.id,
                    })
                  }}
                >
                  {[...bins].map((item) => (
                    <Select.Option key={item.id} value={item.id}>
                      {item.name}
                    </Select.Option>
                  ))}
                </Select>

                <div className="text-red-400 float-right">
                  Total Data: {total}
                </div>
              </div>
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
                  if (record?.stocks > 0) {
                    return (
                      <button
                        onClick={() => {
                          navigate("/bin/detail/" + record.id)
                        }}
                        className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
                      >
                        <FolderFilled />
                        <span className="ml-2">{`Detail`}</span>
                      </button>
                    )
                  }
                  return (
                    <button
                      className="text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
                      disabled
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

export default BinList
