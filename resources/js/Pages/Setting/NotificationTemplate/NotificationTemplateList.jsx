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
import {
  Dropdown,
  Input,
  Menu,
  Pagination,
  Popconfirm,
  Switch,
  Table,
} from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../../components/layout"
import { getItem, inArray } from "../../../helpers"
import FilterModal from "./Components/FilterModal"
import { notificationTemplateListColumn } from "./config"

const NotificationTemplateList = () => {
  const navigate = useNavigate()
  const { group_id } = useParams()
  const [loading, setLoading] = useState(false)
  const [datas, setDatas] = useState([])
  const [total, setTotal] = useState(0)
  const [search, setSearch] = useState("")
  const [isSearch, setIsSearch] = useState(false)
  const [currentPage, setCurrentPage] = useState(1)
  const [filterData, setFilterData] = useState({})
  const [loadingExport, setLoadingExport] = useState(false)
  const [groupName, setGroupName] = useState("")

  const loadData = (
    url = "/api/setting/notification-template",
    perpage = 10,
    params = { page: currentPage }
  ) => {
    setLoading(true)
    axios
      .post(url, { perpage, group_id, ...params })
      .then((res) => {
        const { data, total, from, current_page } = res.data.data
        setGroupName(res.data.group_name)
        setTotal(total)
        setCurrentPage(current_page)
        const newData = data.map((item, index) => {
          return {
            ...item,
            number: index + from,
            role_name: item?.role_names,
          }
        })

        setDatas(newData)
        setLoading(false)
      })
      .catch((e) => setLoading(false))
  }
  useEffect(() => {
    loadData()
  }, [])

  const handleChange = (page, pageSize = 10) => {
    loadData(`/api/setting/notification-template/?page=${page}`, pageSize, {
      search,
      page,
      ...filterData,
    })
  }

  const handleChangeSearch = () => {
    setIsSearch(true)
    loadData(`/api/setting/notification-template`, 10, { search })
  }

  const handleFilter = (data) => {
    setFilterData(data)
    loadData(`/api/setting/notification-template`, 10, data)
  }

  const deleteBanner = (banner_id) => {
    axios
      .post(`/api/setting/notification-template/delete/${banner_id}`, {
        _method: "DELETE",
      })
      .then((res) => {
        toast.success("Template Notifikasi berhasil dihapus")
        loadData()
      })
      .catch((err) => {
        toast.error("Template Notifikasi gagal dihapus")
      })
  }

  const updateStatus = (row, value) => {
    axios
      .post(`/api/setting/notification-template/status/${row.id}`, {
        value,
      })
      .then((res) => {
        toast.success("Status Berhasil Diupdate")
        loadData()
      })
      .catch((err) => {
        toast.error("Status gagal diupdate")
      })
  }

  const handleExportContent = () => {
    setLoadingExport(true)
    axios
      .post(`/api/setting/notification-template/export/${group_id}`)
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
    "warehouse",
  ])

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
      {show && (
        <button
          onClick={() =>
            navigate("/setting/notification-template/form/" + group_id)
          }
          className="text-white bg-blueColor hover:bg-blueColor/90 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
        >
          <PlusOutlined />
          <span className="ml-2">Tambah Data</span>
        </button>
      )}
    </div>
  )

  return (
    <Layout
      rightContent={rightContent}
      title="Template Notifikasi"
      href="/setting/notification-template/group"
      breadcrumbs={[
        {
          title: "Setting",
        },
        { title: "Template Notifikasi" },
        { title: groupName },
      ]}
    >
      <div className="card">
        <div className="card-body">
          <div className="row card-body   mb-4">
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
              ...notificationTemplateListColumn,
              {
                title: "Status",
                key: "status",
                dataIndex: "status",
                render: (text, record) => {
                  return (
                    <Switch
                      checked={text == 1}
                      onChange={(e) => updateStatus(record, e)}
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
                        show && (
                          <Menu itemIcon={<RightOutlined />}>
                            <Menu.Item
                              icon={<EditOutlined />}
                              onClick={() =>
                                navigate(
                                  `/setting/notification-template/form/${group_id}/${text.id}`
                                )
                              }
                            >
                              Ubah
                            </Menu.Item>
                            <Popconfirm
                              title="Yakin hapus data ini?"
                              onConfirm={() => deleteBanner(text.id)}
                              // onCancel={cancel}
                              okText="Ya, Hapus"
                              cancelText="Batal"
                            >
                              <Menu.Item icon={<DeleteOutlined />}>
                                <span>Hapus</span>
                              </Menu.Item>
                            </Popconfirm>
                          </Menu>
                        )
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

export default NotificationTemplateList
