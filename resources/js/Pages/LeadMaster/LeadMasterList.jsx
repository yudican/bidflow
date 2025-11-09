import {
  CloseCircleFilled,
  FileExcelOutlined,
  LoadingOutlined,
  PlusOutlined,
  SearchOutlined,
} from "@ant-design/icons"
import { Input, Pagination, Table } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { useNavigate, useParams } from "react-router-dom"
import Button from "../../components/atoms/Button"
import Layout from "../../components/layout"
import { getItem } from "../../helpers"
import FilterModal from "./Components/FilterModal"
import { leadMasterListColumn } from "./config"

const LeadMasterList = () => {
  const navigate = useNavigate()
  const { convert_id } = useParams()
  const [loading, setLoading] = useState(false)
  const [loadingOrder, setLoadingOrder] = useState(false)
  const [uidLoading, setUidLoading] = useState(false)
  const [leadMasterList, setLeadMasterList] = useState([])
  console.log(leadMasterList, "lead master list")
  const [total, setTotal] = useState(0)
  const [search, setSearch] = useState("")
  const [isSearch, setIsSearch] = useState(false)
  const [currentPage, setCurrentPage] = useState(1)
  const [filterData, setFilterData] = useState({})
  const [loadingExport, setLoadingExport] = useState(false)

  const [selectedRowKeys, setSelectedRowKeys] = useState([])

  const [channelList, setChannelList] = useState([
    { label: "Corner", value: "corner" },
    { label: "Agent Portal", value: "agent-portal" },
    { label: "Distributor", value: "distributor" },
    { label: "Super Agent", value: "super-agent" },
    { label: "Modern Store", value: "modern-store" },
    { label: "E-Store", value: "e-store" },
  ])
  const [selectedChannel, setselectedChannel] = useState(null)

  const loadOrder = (
    url = "/api/lead-master",
    perpage = 10,
    params = { page: currentPage }
  ) => {
    setLoadingOrder(true)
    axios
      .post(url, { perpage, account_id: getItem("account_id"), ...params })
      .then((res) => {
        const { data, total, current_page } = res.data.data
        setTotal(total)
        setCurrentPage(current_page)
        const newData = data.map((item) => {
          return {
            id: item.uid_lead,
            title: item.title,
            contact: item?.contact_name || "-",
            sales: item?.sales_name || "-",
            created_by: item?.created_by_name || "-",
            brand: item?.brand_name || "-",
            created_on: item?.created_on,
            status: item?.status_name,
            product_needs: item?.product_needs,
          }
        })

        setLeadMasterList(newData)
        setLoadingOrder(false)
      })
  }
  const loadSalesChannel = () => {
    setLoading(true)
    axios
      .get("/api/order-manual/sales/channel")
      .then((res) => {
        const { data } = res.data
        setChannelList(data)
        setLoading(false)
      })
      .catch((err) => {
        setLoading(false)
      })
  }
  useEffect(() => {
    loadOrder()
    loadSalesChannel()
  }, [])

  const handleChange = (page, pageSize = 10) => {
    loadOrder(`/api/lead-master/?page=${page}`, pageSize, {
      search,
      page,
      ...filterData,
    })
  }

  const handleChangeSearch = () => {
    setIsSearch(true)
    loadOrder(`/api/lead-master`, 10, { search })
  }

  const handleFilter = (data) => {
    setFilterData(data)
    loadOrder(`/api/lead-master`, 10, data)
  }

  const handleExportContent = () => {
    setLoadingExport(true)
    axios
      .post(`/api/lead-master/export/`, { items: selectedRowKeys })
      .then((res) => {
        const { data } = res.data
        setSelectedRowKeys([])
        window.open(data)
        setLoadingExport(false)
      })
      .catch((e) => setLoadingExport(false))
  }

  // selected row handler
  const rowSelection = {
    selectedRowKeys,
    onChange: (newSelectedRowKeys) => setSelectedRowKeys(newSelectedRowKeys),
    getCheckboxProps: (record) => ({
      disabled: record.status_submit === "submited", // Column configuration not to be checked
    }),
  }

  const rightContent = (
    <div className="flex justify-between items-center">
      <FilterModal handleOk={handleFilter} />
      <Button
        color="green"
        icon={<FileExcelOutlined />}
        label={" Export"}
        onClick={() => handleExportContent()}
        loading={loadingExport}
        disabled={selectedRowKeys.length === 0}
      />
      <button
        onClick={() => navigate("/lead-master/form")}
        className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
      >
        {uidLoading ? <LoadingOutlined /> : <PlusOutlined />}
        <span className="ml-2">Tambah Data</span>
      </button>
    </div>
  )

  return (
    <Layout rightContent={rightContent} title="List Lead (Negotiation)">
      <div className="card">
        <div className="card-body">
          <div className="row card-body   mb-4">
            <div className="flex overflow-y-auto mb-4">
              {channelList?.map((item, index) => (
                <div
                  key={index}
                  className="cursor-pointer mr-4"
                  onClick={() => {
                    // setSelectedWarehouse(item.id)
                    // localStorage.setItem("selectedChannel", item.id)
                    // const filter = getItem("variantFilter", true) || {}
                    // loadData(`/api/product-management/product-variant`, 10, {
                    //   ...filter,
                    //   warehouse_id: item.id,
                    // })
                  }}
                >
                  <div
                    key={index}
                    className={`
                  card w-96
                  bg-gradient-to-r from-white via-white ${
                    selectedChannel === item.id ? "to-blue-500/20" : ""
                  }
                  hover:to-blue-500/20
                `}
                  >
                    <div className="p-3 border-b flex justify-between">
                      <div className="flex items-center">
                        <strong
                          className={`text-base font-semibold text-${
                            selectedChannel === item.id ? "blue-500" : "black"
                          }`}
                        >
                          {item.label}
                        </strong>
                      </div>
                    </div>
                    <div className="card-body">
                      <strong
                        className={`text-${
                          selectedChannel === item.id ? "blue-500" : "black"
                        } text-lg font-medium`}
                      >
                        Total Order: {item.count}
                      </strong>
                    </div>
                  </div>
                </div>
              ))}
            </div>
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
                        loadOrder()
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
            rowSelection={rowSelection}
            dataSource={leadMasterList}
            columns={leadMasterListColumn}
            loading={loadingOrder}
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
            pageSizeOptions={["10", "20", "50", "100", "200", "500"]}
          />
        </div>
      </div>
    </Layout>
  )
}

export default LeadMasterList
