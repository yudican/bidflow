import {
  CloseCircleFilled,
  CloseOutlined,
  EditOutlined,
  EyeOutlined,
  FileExcelOutlined,
  LoadingOutlined,
  RightOutlined,
  SearchOutlined,
} from "@ant-design/icons"
import { Dropdown, Input, Menu, Pagination, Popconfirm, Table } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import "react-circular-progressbar/dist/styles.css"
import { useNavigate } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../components/layout"
import { getItem, inArray } from "../../helpers"
import FilterModal from "./Components/FilterModal"
import { purchaseOrderListColumn } from "./config"

const PurchaseOrder = () => {
  // hooks
  const navigate = useNavigate()
  // state
  const [loading, setLoading] = useState(false)
  const [purchaseOrderList, setPurchaseOrderList] = useState([])
  const [searchPurchaseOrderList, setSearchPurchaseOrderList] = useState(null)
  const [total, setTotal] = useState(0)
  const [search, setSearch] = useState("")
  const [isSearch, setIsSearch] = useState(false)
  const [currentPage, setCurrentPage] = useState(1)
  const [filterData, setFilterData] = useState({})
  const [loadingExport, setLoadingExport] = useState(false)

  const SearchResult = () => {
    return purchaseOrderList.filter((value) => value.po_number.includes(search))
  }

  const loadData = (
    url = "/api/stock-movement",
    perpage = 10,
    params = { page: currentPage }
  ) => {
    setLoading(true)
    axios
      .post(url, { perpage, account_id: getItem("account_id"), ...params })
      .then((res) => {
        const { data, total, current_page } = res.data.data
        setTotal(res.data.data.per_page)
        setCurrentPage(current_page)
        const newData = data.map((item) => {
          return {
            ...item,
          }
        })

        setPurchaseOrderList(
          newData.sort((a, b) => {
            if (a.status == "2") {
              return a - b
            }
            return -1
          })
        )
        setLoading(false)
      })
  }
  useEffect(() => {
    loadData()
  }, [])

  const handleChange = (page, pageSize = 10) => {
    loadData(`/api/stock-movement/?page=${page}`, pageSize, {
      search,
      page,
      ...filterData,
    })
  }

  const handleChangeSearch = () => {
    setIsSearch(true)
    loadData(`/api/stock-movement`, 10, { search })
  }

  const handleFilter = (data) => {
    console.log(data)
    setFilterData(data)
    loadData(`/api/stock-movement`, 10, data)
  }

  const handleExportContent = () => {
    setLoadingExport(true)
    axios
      .post(`/api/stock-movement/export`)
      .then((res) => {
        const { data } = res.data
        window.open(data)
        setLoadingExport(false)
      })
      .catch((e) => setLoadingExport(false))
  }

  const handleCancel = (po_id) => {
    axios
      .post(`/api/stock-movement/cancel/${po_id}`, {
        _method: "DELETE",
      })
      .then((res) => {
        toast.success("Purchase order berhasil dibatalkan")
        loadData()
      })
      .catch((err) => {
        toast.error("Purchase order gagal dibatalkan")
      })
  }

  const listActions = [
    {
      title: "Action",
      key: "id",
      align: "center",
      fixed: "right",
      width: 100,
      render: (text, record) => {
        return (
          <Dropdown.Button
            style={{
              left: -16,
            }}
            overlay={
              <Menu itemIcon={<RightOutlined />}>
                {record.status < 1 && (
                  <Menu.Item
                    icon={<EditOutlined />}
                    onClick={() => navigate(`form/${text.id}`)}
                  >
                    Ubah
                  </Menu.Item>
                )}

                <Menu.Item
                  icon={<EyeOutlined />}
                  onClick={() => navigate(`detail/${text.id}`)}
                >
                  Detail
                </Menu.Item>

                {inArray(record.status, ["0", "5"]) && (
                  <Popconfirm
                    title="Yakin Batalkan PO ini?"
                    onConfirm={() => handleCancel(text.id)}
                    // onCancel={cancel}
                    okText="Ya, Batalkan"
                    cancelText="Batal"
                  >
                    <Menu.Item icon={<CloseOutlined />}>
                      <span>Cancel</span>
                    </Menu.Item>
                  </Popconfirm>
                )}
              </Menu>
            }
          ></Dropdown.Button>
        )
      },
    },
  ]

  const rightContent = (
    <div className="flex justify-between items-center">
      <button
        className="ml-3 text-white bg-green-800 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mr-2"
        onClick={() => handleExportContent()}
      >
        {loadingExport ? <LoadingOutlined /> : <FileExcelOutlined />}
        <span className="ml-2">Export</span>
      </button>
      <FilterModal handleOk={handleFilter} />
    </div>
  )

  const columns = inArray(getItem("role"), ["warehouse"])
    ? purchaseOrderListColumn
    : purchaseOrderListColumn
  return (
    <Layout title="Stock Movement" rightContent={rightContent}>
      <div className="row mb-4">
        <div className="col-md-12"></div>
        <div className="col-md-4 col-sm-6 col-12">
          <Input
            placeholder="Cari disini"
            size={"large"}
            className="rounded"
            onPressEnter={(e) => {
              // handleChangeSearch()
              setSearchPurchaseOrderList(SearchResult())
            }}
            suffix={
              isSearch ? (
                <CloseCircleFilled
                  onClick={() => {
                    // loadData()
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
          <strong className="float-right text-blue-400">
            Total Data: {total}
          </strong>
        </div>
      </div>
      <Table
        rowSelection
        dataSource={searchPurchaseOrderList || purchaseOrderList}
        columns={[...columns]}
        loading={loading}
        pagination={false}
        rowKey="id"
        scroll={{ x: "max-content" }}
        tableLayout={"auto"}
      />
      {/* <Pagination
        defaultCurrent={1}
        current={currentPage}
        total={total}
        className="mt-4 text-center"
        onChange={handleChange}
        pageSizeOptions={["10", "20", "50", "100", "200", "500"]}
      /> */}
    </Layout>
  )
}

export default PurchaseOrder
