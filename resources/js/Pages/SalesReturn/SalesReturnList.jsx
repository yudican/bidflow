import {
  CloseCircleFilled,
  EyeOutlined,
  FileDoneOutlined,
  FileExcelOutlined,
  LoadingOutlined,
  PlusOutlined,
  RightOutlined,
  SearchOutlined,
  WarningOutlined,
} from "@ant-design/icons"
import { Dropdown, Input, Menu, Pagination, Table } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { useNavigate } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../components/layout"
import { getItem } from "../../helpers"
import { salesReturnListColumn } from "./config"

const SalesReturnList = () => {
  const navigate = useNavigate()
  const [loading, setLoading] = useState(false)
  const [loadingCreate, setLoadingCreate] = useState(false)
  const [salesReturnData, setSalesReturnData] = useState([])
  const [total, setTotal] = useState(0)
  const [search, setSearch] = useState("")
  const [isSearch, setIsSearch] = useState(false)
  const [currentPage, setCurrentPage] = useState(1)
  const [filterData, setFilterData] = useState({})
  const [loadingExport, setLoadingExport] = useState(false)

  const loadContact = (
    url = "/api/order/sales-return",
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
        const newData = data.map((item) => {
          return {
            id: item.id,
            uid_retur: item.uid_retur,
            sr_number: item.sr_number,
            order_number: item.order_number,
            contact: item?.contact_user?.name || "-",
            sales: item?.sales_user?.name || "-",
            status: item?.status_return || "-",
            created_on: moment(item.created_at).format("DD-MM-YYYY"),
          }
        })

        setSalesReturnData(newData)
        setLoading(false)
      })
  }
  useEffect(() => {
    loadContact()
  }, [])

  const handleChange = (page, pageSize = 10) => {
    loadContact(`/api/order/sales-return/?page=${page}`, pageSize, {
      search,
      ...filterData,
    })
  }

  const handleChangeSearch = () => {
    setIsSearch(true)
    loadContact(`/api/order/sales-return`, 10, { search })
  }

  const handleFilter = (data) => {
    setFilterData(data)
    loadContact(`/api/order/sales-return`, 10, data)
  }

  const createNewData = (values) => {
    setLoadingCreate(true)
    axios
      .post("/api/order/sales-return/save", {
        ...values,
        uid_retur: null,
        account_id: getItem("account_id"),
      })
      .then((res) => {
        const { data } = res.data
        setLoadingCreate(false)
        return navigate("/order/sales-return/form/" + data?.uid_retur)
      })
      .catch((err) => {
        const { message } = err.response.data
        setLoadingCreate(false)
        console.log(err.response, "err.response")
      })
  }

  const actionReturn = (type, value) => {
    axios
      .post(`/api/order/sales-return/${type}`, value)
      .then((res) => {
        const { message } = res.data
        loadContact()
        toast.success(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
      .catch((err) => {
        const { message } = err.response.data
        toast.error(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }

  const handleExportContent = () => {
    setLoadingExport(true)
    axios
      // .post(`/api/sales-return/export/`)
      .post(`/api/sales-return/export/detail/1`)
      .then((res) => {
        const { data } = res.data
        window.open(data)
        setLoadingExport(false)
      })
      .catch((e) => setLoadingExport(false))
  }

  const rightContent = (
    <div className="flex justify-between items-center">
      {/* <FilterModal handleOk={handleFilter} /> */}
      <button
        className="ml-3 text-white bg-green-800 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mr-2"
        onClick={() => handleExportContent()}
      >
        {loadingExport ? <LoadingOutlined /> : <FileExcelOutlined />}
        <span className="ml-2">Export</span>
      </button>
      <button
        onClick={() => createNewData()}
        className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
      >
        {loadingCreate ? <LoadingOutlined /> : <PlusOutlined />}
        <span className="ml-2">Tambah Data</span>
      </button>
    </div>
  )

  const actionList = [
    {
      title: "Action",
      key: "action",
      fixed: "right",
      align: "center",
      width: 100,
      render: (text, record) => (
        <Dropdown.Button
          // icon={<DownOutlined />}
          style={{ left: -16 }}
          onClick={() =>
            navigate(`/order/sales-return/detail/${record.uid_retur}`)
          }
          overlay={
            <Menu
              onClick={({ key }) => {
                if (key === "cancel") {
                  return actionReturn("cancel", {
                    uid_retur: record.uid_retur,
                    status: 5,
                  })
                }
                return navigate(
                  `/order/sales-return/${key}/${record.uid_retur}`
                )
              }}
              itemIcon={<RightOutlined />}
              items={[
                {
                  label: "Detail",
                  key: "detail",
                  icon: <EyeOutlined />,
                },
                {
                  label: "Ubah",
                  key: "form",
                  icon: <FileDoneOutlined />,
                },
                {
                  label: "Cancel",
                  key: "cancel",
                  icon: <WarningOutlined />,
                },
              ]}
            />
          }
        ></Dropdown.Button>
      ),
    },
  ]

  return (
    <Layout title="Sales Return" rightContent={rightContent}>
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
                        loadContact()
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
            dataSource={salesReturnData}
            columns={[...salesReturnListColumn, ...actionList]}
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

export default SalesReturnList
