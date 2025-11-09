import {
  CloseCircleFilled,
  DeleteOutlined,
  EditOutlined,
  InfoCircleOutlined,
  PlusOutlined,
  RightOutlined,
  SearchOutlined,
} from "@ant-design/icons"
import {
  Dropdown,
  Input,
  Menu,
  Pagination,
  Popconfirm,
  Switch,
  Table,
  Tooltip,
} from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { useNavigate } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../../components/layout"
import { capitalizeFirstLetter, getItem, inArray } from "../../../helpers"
import FilterModal from "./Components/FilterModal"
import { productListColumn } from "./config"

const ProductAdditionalList = ({ type = "pengemasan" }) => {
  const navigate = useNavigate()
  const [loading, setLoading] = useState(false)
  const [datas, setDatas] = useState([])
  const [total, setTotal] = useState(0)
  const [search, setSearch] = useState("")
  const [isSearch, setIsSearch] = useState(false)
  const [currentPage, setCurrentPage] = useState(1)
  const [filterData, setFilterData] = useState({})

  const loadData = (
    url = `/api/master/${type}`,
    perpage = 10,
    params = { page: currentPage }
  ) => {
    setLoading(true)
    axios
      .post(url, { perpage, type, ...params })
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
  }, [type])

  const handleChange = (page, pageSize = 10) => {
    loadData(`/api/master/${type}/?page=${page}`, pageSize, {
      search,
      page,
      ...filterData,
    })
  }

  const handleChangeSearch = () => {
    setIsSearch(true)
    loadData(`/api/master/${type}`, 10, { search })
  }

  const handleFilter = (data) => {
    setFilterData(data)
    loadData(`/api/master/${type}`, 10, data)
  }

  const deletePackage = (variant_id) => {
    axios
      .post(`/api/master/${type}/delete/${variant_id}`, { _method: "DELETE" })
      .then((res) => {
        toast.success(
          "Data " + capitalizeFirstLetter(type) + " berhasil dihapus"
        )
        loadData()
      })
      .catch((err) => {
        toast.error("Data " + capitalizeFirstLetter(type) + " gagal dihapus")
      })
  }

  const updateStatus = (record) => {
    axios
      .post(`/api/master/${type}/save/${record.id}`, record)
      .then((res) => {
        toast.success("Status berhasil diupdate")
        loadData()
      })
      .catch((err) => {
        toast.error("Status gagal Di update")
      })
  }

  const show = !inArray(getItem("role"), [
    "adminsales",
    "leadwh",
    "leadsales",
    "warehouse",
  ])

  const statushide = !inArray(getItem("role"), [
    "member",
    "warehouse",
    "leadsales",
    "finance",
    "agent",
    "courier",
    "sales",
    "collector",
    "subagent",
    "cs",
    "leadcs",
    "adminsales",
    "leadwh",
  ])

  const rightContent = (
    <div className="flex justify-between items-center">
      <FilterModal handleOk={handleFilter} type={capitalizeFirstLetter(type)} />
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

  const currentUrl = new URL(window.location.href)
  const pathName = currentUrl?.pathname
  const parts = pathName?.split("/").filter(Boolean)
  const MainUrl = parts[1]

  return (
    <Layout rightContent={rightContent} title={`List ${MainUrl}`}>
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
              ...productListColumn,
              {
                title: "Status",
                key: "status",
                dataIndex: "status",
                align: "center",
                render: (text, record) => {
                  if (!statushide)
                    return (
                      <Tooltip title="You don't have access.">
                        <InfoCircleOutlined />
                      </Tooltip>
                    )
                  return (
                    <Switch
                      checked={text > 0}
                      onChange={(e) =>
                        updateStatus({ ...record, status: e ? "1" : "0" })
                      }
                    />
                  )
                },
              },
              {
                title: "Action",
                key: "id",
                align: "center",
                fixed: "right",
                width: 100,
                render: (text) => {
                  if (!show) return null
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
                          <Popconfirm
                            title="Yakin hapus data ini?"
                            onConfirm={() => deletePackage(text.id)}
                            // onCancel={cancel}
                            okText="Ya, Hapus"
                            cancelText="Batal"
                          >
                            <Menu.Item icon={<DeleteOutlined />}>
                              <span>Hapus</span>
                            </Menu.Item>
                          </Popconfirm>
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

export default ProductAdditionalList
