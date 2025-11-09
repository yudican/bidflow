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
import { comissionWithdrawColumn } from "./config"

const ComissionWithdraw = () => {
  const navigate = useNavigate()
  const [loading, setLoading] = useState(false)
  const [datas, setDatas] = useState([])
  const [total, setTotal] = useState(0)
  const [search, setSearch] = useState("")
  const [isSearch, setIsSearch] = useState(false)
  const [currentPage, setCurrentPage] = useState(1)
  const [filterData, setFilterData] = useState({})
  const [commisions, setCommisions] = useState([])
  const [loadingExport, setLoadingExport] = useState(false)

  const loadData = (
    url = "/api/comission-withdraw",
    perpage = 10,
    params = { page: currentPage }
  ) => {
    setLoading(true)
    axios
      .post(url, {
        perpage,
        account_id: getItem("account_id"),
        ...params,
      })
      .then((res) => {
        const { data, total, current_page } = res.data.data
        setTotal(total)
        setCurrentPage(current_page)
        setDatas(data)
        setLoading(false)
        setCommisions(res.data.commisions)
      })
  }
  useEffect(() => {
    loadData()
  }, [])

  const handleChange = (page, pageSize = 10) => {
    loadData(`/api/comission-withdraw/?page=${page}`, pageSize, {
      search,
      page,
      ...filterData,
    })
  }

  const handleChangeSearch = () => {
    setIsSearch(true)
    loadData(`/api/comission-withdraw`, 10, { search })
  }

  const handleFilter = (data) => {
    setFilterData(data)
    loadData(`/api/comission-withdraw`, 10, data)
  }

  const cancelData = (id) => {
    axios
      .post(`/api/comission-withdraw/cancel/${id}`, { status: "canceled" })
      .then((res) => {
        toast.success("Data berhasil dibatalkan")
        loadData()
      })
      .catch((err) => {
        toast.error("Data gagal dibatalkan")
      })
  }

  const handleExportContent = () => {
    setLoadingExport(true)
    axios
      .post(`/api/comission-withdraw/export`)
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

      <button
        onClick={() => navigate("/comission-withdraw/form")}
        className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
      >
        <PlusOutlined />
        <span className="ml-2">Tambah Data</span>
      </button>
    </div>
  )

  return (
    <Layout rightContent={rightContent} title="Comission Withdraw">
      <div className="card">
        <div className="card-body">
          <div className=" mb-4">
            <h1 className="text-xl font-bold px-3">Withdraw Report </h1>
            <div className="row overflow-x-scroll flex-nowrap">
              {commisions?.map((item, index) => (
                <div
                  key={index}
                  className="col-md-4 cursor-pointer mr-8"
                  onClick={() => {
                    navigate(`/comission-withdraw/detail`)
                  }}
                >
                  <div
                    key={index}
                    className={`
                  card flex w-96 justify-center items-center
                  bg-gradient-to-r from-[#FF6600] via-[#FF6600]  to-[#FFC120]
                  hover:to-blue-500/20 text-white py-4
                `}
                  >
                    <strong className={`text-xs font-medium`}>
                      {item.label}
                    </strong>

                    <strong className={`text-base font-semibold`}>
                      {item.value}
                    </strong>
                  </div>
                </div>
              ))}
            </div>
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
                  Total Data: {total}
                </strong>
              </div>
            </div>
          </div>
          <Table
            dataSource={datas}
            columns={[
              ...comissionWithdrawColumn,
              {
                title: "Action",
                key: "id",
                align: "center",
                fixed: "right",
                width: 100,
                render: (text) => {
                  return (
                    <Dropdown.Button
                      style={{
                        left: -16,
                      }}
                      // icon={<MoreOutlined />}
                      overlay={
                        <Menu itemIcon={<RightOutlined />}>
                          <Menu.Item
                            icon={<EditOutlined />}
                            onClick={() => navigate(`form/${text.id}`)}
                          >
                            Ubah
                          </Menu.Item>
                          {inArray(text.status, [
                            "draft",
                            "waiting-approval",
                          ]) && (
                            <Popconfirm
                              title="Yakin hapus data ini?"
                              onConfirm={() => cancelData(text.id)}
                              // onCancel={cancel}
                              okText="Ya, Hapus"
                              cancelText="Batal"
                            >
                              <Menu.Item icon={<DeleteOutlined />}>
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
            ]}
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

export default ComissionWithdraw
