import {
  CloseCircleFilled,
  FileExcelOutlined,
  FolderOpenOutlined,
  LoadingOutlined,
  PlusOutlined,
  RightOutlined,
  SearchOutlined,
} from "@ant-design/icons"
import {
  DatePicker,
  Dropdown,
  Input,
  Menu,
  Modal,
  Pagination,
  Select,
  Table,
} from "antd"
import React, { useEffect, useState } from "react"
import { useNavigate } from "react-router-dom"
import Layout from "../../components/layout"
import { getItem, inArray } from "../../helpers"
import FilterModalProduct from "./Components/FilterModalProduct"
import { inventoryReturnColumns } from "./config"

const { RangePicker } = DatePicker

const InventoryProductReturn = () => {
  const navigate = useNavigate()

  // state
  const [inventoryData, setInventoryData] = useState([])
  const [loading, setLoading] = useState(false)
  const [total, setTotal] = useState(0)
  const [search, setSearch] = useState("")
  const [isSearch, setIsSearch] = useState(false)
  const [currentPage, setCurrentPage] = useState(1)
  const [filterData, setFilterData] = useState({})
  const [filterExport, setFilterExport] = useState({
    status: null,
    created_at: null,
  })
  const [isFilterExportModalOpen, setIsFilterExportModalOpen] = useState(false)
  const [loadingExport, setLoadingExport] = useState(false)

  const loadInventoryData = (
    url = "/api/inventory/product/return",
    perpage = 10,
    params = {}
  ) => {
    setLoading(true)
    axios
      .post(url, { perpage, account_id: getItem("account_id"), ...params })
      .then((res) => {
        const { data, total, current_page } = res.data.data
        setTotal(total)
        setCurrentPage(current_page)
        setInventoryData(data)
        setLoading(false)
      })
  }

  const handleChange = (page, pageSize = 10) => {
    loadInventoryData(`/api/inventory/product/return/?page=${page}`, pageSize, {
      search,
      ...filterData,
    })
  }

  const handleChangeSearch = () => {
    setIsSearch(true)
    loadInventoryData(`/api/inventory/product/return`, 10, { search })
  }

  const handleFilter = (data) => {
    setFilterData(data)
    loadInventoryData(`/api/inventory/product/return`, 10, data)
  }

  // const handleExportContent = () => { // old scheme for export data
  //   setLoadingExport(true)
  //   axios
  //     .post(`/api/inventory/product/stock/export_return`)
  //     .then((res) => {
  //       const { data } = res.data
  //       window.open(data)
  //       setLoadingExport(false)
  //     })
  //     .catch((e) => setLoadingExport(false))
  // }

  const handleChangeExport = (value, field) => {
    if (field === "createdBy") {
      return setFilterExport({ ...filterExport, createdBy: value.value })
    }
    setFilterExport({ ...filterExport, [field]: value })
  }

  const handleNewExportContent = () => {
    setLoadingExport(true)
    axios
      .post(`/api/inventory/product/stock/export_return/`, filterExport) // waiting new endpoint from mbak henny
      .then((res) => {
        const { data } = res.data
        window.open(data)
        setLoadingExport(false)
      })
      .catch((e) => setLoadingExport(false))
  }

  useEffect(() => {
    loadInventoryData()
  }, [])

  const show = !inArray(getItem("role"), ["leadsales"])

  const rightContent = (
    <div className="flex justify-between items-center">
      <>
        <button
          className="ml-3 text-white bg-green-800 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mr-2"
          // onClick={() => handleExportContent()}
          onClick={() => setIsFilterExportModalOpen(true)}
        >
          {loadingExport ? <LoadingOutlined /> : <FileExcelOutlined />}
          <span className="ml-2">Export</span>
        </button>
        <Modal
          title="Export Config "
          open={isFilterExportModalOpen}
          okText={"Export Data"}
          cancelText={"Batal"}
          onOk={handleNewExportContent}
          confirmLoading={loadingExport}
          onCancel={() => {
            setIsFilterExportModalOpen(false)
          }}
        >
          <div>
            <label htmlFor="">Status</label>
            <Select
              mode="multiple"
              allowClear
              className="w-full mb-2"
              placeholder="Pilih Status"
              onChange={(e) => handleChangeExport(e, "status")}
            >
              <Select.Option value={-1}>Draft</Select.Option>
              <Select.Option value={1}>New</Select.Option>
              <Select.Option value={2}>Open</Select.Option>
              <Select.Option value={3}>Closed</Select.Option>
              <Select.Option value={4}>Canceled</Select.Option>
            </Select>
          </div>

          <div className="mb-2">
            <label htmlFor="">Tanggal</label>
            <RangePicker
              className="w-full"
              format={"YYYY-MM-DD"}
              onChange={(e, dateString) =>
                handleChangeExport(dateString, "created_at")
              }
            />
          </div>
        </Modal>
      </>

      <FilterModalProduct
        handleOk={(val) => handleFilter(val)}
        type={"return"}
      />
      {show && (
        <button
          onClick={() => navigate("form")}
          className="text-white bg-blueColor hover:bg-blueColor/90 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
        >
          <PlusOutlined />
          <span className="ml-2">Tambah Data</span>
        </button>
      )}
    </div>
  )

  const actionList = [
    {
      title: "Action",
      key: "action",
      fixed: "right",
      width: 100,
      render: (text, record) => {
        const { uid_inventory } = record
        return (
          <Dropdown.Button
            style={{
              left: -16,
            }}
            // icon={<MoreOutlined />}
            overlay={
              <Menu itemIcon={<RightOutlined />}>
                <Menu.Item
                  icon={<FolderOpenOutlined />}
                  onClick={() => navigate(`detail/${uid_inventory}`)}
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

  return (
    <Layout
      onClick={() => navigate(-1)}
      title="List Product / Sales Return"
      rightContent={rightContent}
    >
      <div className="card">
        <div className="card-body">
          <div className="row mb-4">
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
                        loadInventoryData()
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
            dataSource={inventoryData}
            columns={[...inventoryReturnColumns, ...actionList]}
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
            pageSizeOptions={["10", "20", "50", "100", "200", "500"]}
          />
        </div>
      </div>
    </Layout>
  )
}

export default InventoryProductReturn
