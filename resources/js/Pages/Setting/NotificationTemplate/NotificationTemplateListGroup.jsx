import { CloseCircleFilled, SearchOutlined } from "@ant-design/icons"
import { Button, Input, Pagination, Switch, Table } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { Link } from "react-router-dom"
import Layout from "../../../components/layout"
import { getItem, inArray } from "../../../helpers"
import { notificationTemplateGroupListColumn } from "./config"
import { toast } from "react-toastify"

const NotificationTemplateListGroup = () => {
  const [loading, setLoading] = useState(false)
  const [datas, setDatas] = useState([])
  const [total, setTotal] = useState(0)
  const [search, setSearch] = useState("")
  const [isSearch, setIsSearch] = useState(false)
  const [currentPage, setCurrentPage] = useState(1)

  const loadData = (
    url = "/api/setting/notification-template",
    perpage = 10,
    params = { page: currentPage }
  ) => {
    setLoading(true)
    axios
      .post(url, { perpage, type: "group", ...params })
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
  }, [])

  const handleChange = (page, pageSize = 10) => {
    loadData(`/api/setting/notification-template/?page=${page}`, pageSize, {
      search,
      page,
    })
  }

  const handleChangeSearch = () => {
    setIsSearch(true)
    loadData(`/api/setting/notification-template`, 10, { search })
  }

  const updateStatus = (row, value) => {
    axios
      .post(`/api/setting/notification-template/status/${row.id}`, {
        value,
      })
      .then((res) => {
        toast.success("Status berhasil diupdate")
        loadData()
      })
      .catch((err) => {
        toast.error("Status gagal diupdate")
      })
  }

  const show = !inArray(getItem("role"), [
    "adminsales",
    "leadwh",
    "leadsales",
    "warehouse",
  ])

  return (
    <Layout
      title="Grup Template Notifikasi"
      breadcrumbs={[
        {
          title: "Setting",
        },
        { title: "Template Notifikasi" },
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
              ...notificationTemplateGroupListColumn,
              {
                title: "Status",
                key: "status",
                dataIndex: "status",
                render: (text, record) => {
                  return (
                    <Switch
                      checked={record.status > 0}
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
                render: (text, record) => {
                  if (!show) return null
                  return (
                    <Link
                      to={`/setting/notification-template/list/${record.id}`}
                    >
                      <Button>Detail</Button>
                    </Link>
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

export default NotificationTemplateListGroup
