import {
  CloseCircleFilled,
  EyeOutlined,
  RightOutlined,
  SearchOutlined,
  PrinterTwoTone,
} from "@ant-design/icons"
import {
  DatePicker,
  Dropdown,
  Input,
  Menu,
  Modal,
  Pagination,
  Popconfirm,
  Select,
  Table,
} from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import "react-circular-progressbar/dist/styles.css"
import { useNavigate } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../components/layout"
import { formatNumber, getItem, inArray, paginateData } from "../../helpers"
import FilterModal from "./Components/FilterModal"
import { purchaseOrderListColumn } from "./config"

const { RangePicker } = DatePicker

const AssetList = () => {
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
  const [perPage, setPerpage] = useState(10)

  const [filterData, setFilterData] = useState({})
  const [selectedRowKeys, setSelectedRowKeys] = useState([])
  const [selectedProducts, setSelectedProducts] = useState([])
  const [isFilterExportModalOpen, setIsFilterExportModalOpen] = useState(false)

  const loadData = (
    url = "/api/asset-control",
    perpage = perPage,
    params = { page: currentPage }
  ) => {
    setLoading(true)
    axios
      .post(url, { perpage, account_id: getItem("account_id"), ...params })
      .then((res) => {
        const { data, total, current_page } = res.data.data
        setTotal(total) // set total of total data products
        const numberPages = Array(total) // create number columns data based on total data
          .fill()
          .map((_, index) => index + 1)
        const paginatedNumbers = paginateData(
          numberPages,
          current_page,
          perpage
        ) // convert to paginated data
        // sort on process status first
        data
          .sort((a, b) => {
            if (a.status == "2") {
              return a.status - b.status
            }
            if (a.status == "7") {
              return 2
            }
            return -1
          })
          .sort((a, b) => {
            if (a.status == "5") {
              return -1
            }
            if (a.status == "1") {
              return -1
            }
          })

        const newData = data.map((item, index) => {
          const number = paginatedNumbers[index] // overriding response to set paginated number per pages
          return {
            ...item,
            number,
            created_by: item?.created_by_name,
          }
        })
        setPurchaseOrderList(newData)
        setLoading(false)
      })
  }
  useEffect(() => {
    loadData()
  }, [])

  const handleChange = (page, pageSize = 10) => {
    setCurrentPage(page)
    loadData(`/api/asset-control/?page=${page}`, pageSize, {
      search,
      page,
      ...filterData,
    })
  }

  const handleChangeSearch = () => {
    setIsSearch(true)
    loadData(`/api/asset-control`, 10, { search })
  }

  const handleFilter = (data) => {
    setFilterData(data)
    loadData(`/api/asset-control`, 10, data)
  }

  // selected row handler
  const rowSelection = {
    selectedRowKeys,
    onChange: (newSelectedRowKeys) => setSelectedRowKeys(newSelectedRowKeys),
    getCheckboxProps: () => ({
      disabled: false, // Column configuration not to be checked
    }),
  }

  const handleBulkPrint = () => {
    if (selectedRowKeys.length === 0) {
      toast.error("Silakan pilih data terlebih dahulu untuk bulk print")
      return
    }

    const ids = selectedRowKeys.join(",")
    const url = `/api/asset-control/bulk/print?ids=${ids}`

    window.open(url, "_blank")
  }

  const handleAction = (url) => {
    setLoading(true)
    axios
      .post(url, { transaction_id: selectedRowKeys })
      .then((res) => {
        const { message } = res.data
        setLoading(false)
        toast.success(message)
        loadData()
      })
      .catch((e) => {
        const { message } = e.response.data
        setLoading(false)
        toast.error(message)
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
                <Menu.Item
                  icon={<PrinterTwoTone />}
                  onClick={() =>
                    window.open(`/asset-control/print/${text?.id}`, "_blank")
                  }
                >
                  Print
                </Menu.Item>
                <Menu.Item
                  icon={<EyeOutlined />}
                  onClick={() => navigate(`form/${text.id}`)}
                >
                  Detail
                </Menu.Item>
              </Menu>
            }
          ></Dropdown.Button>
        )
      },
    },
  ]

  const rightContent = (
    <div className="flex justify-between items-center">
      <Dropdown.Button
        style={{ borderRadius: 10, width: 80 }}
        icon={<PrinterTwoTone />}
        trigger="click"
        className="rounded-lg mr-2"
        overlay={
          <Menu>
            <Menu.Item className="flex justify-between items-center">
              <PrinterTwoTone />{" "}
              <a
                href={"#"}
                onClick={(e) => {
                  e.preventDefault()
                  handleBulkPrint()
                }}
              >
                <span>Bulk Print</span>
              </a>
            </Menu.Item>
          </Menu>
        }
      ></Dropdown.Button>
      <FilterModal handleOk={handleFilter} />
    </div>
  )

  const columns = purchaseOrderListColumn
  return (
    <Layout title="List Asset Control" rightContent={rightContent}>
      <div className="card">
        <div className="card-body">
          <div className="row mb-4">
            <div className="col-md-12"></div>
            <div className="col-md-4 col-sm-6 col-12">
              <Input
                placeholder="Cari disini"
                size={"large"}
                className="rounded"
                onPressEnter={(e) => {
                  handleChangeSearch()
                  // setSearchPurchaseOrderList(SearchResult())
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
              <strong className="float-right text-red-400">
                Total Data: {formatNumber(total)}
              </strong>
            </div>
          </div>
          <Table
            rowSelection={rowSelection}
            dataSource={purchaseOrderList}
            columns={[...columns, ...listActions]}
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
            onShowSizeChange={(current, size) => {
              setCurrentPage(current)
              setPerpage(size)
            }}
          />
        </div>
      </div>
    </Layout>
  )
}

export default AssetList
