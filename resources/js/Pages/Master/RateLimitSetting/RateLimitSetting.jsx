import { CloseCircleFilled, SearchOutlined } from "@ant-design/icons"
import { Input, Pagination, Switch, Table } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { useNavigate } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../../components/layout"
import { packageListColumn } from "./config"
import { paginateData } from "../../../helpers"

const RateLimitSetting = () => {
  const navigate = useNavigate()
  const [loading, setLoading] = useState(false)
  const [datas, setDatas] = useState([])
  const [total, setTotal] = useState(0)
  const [search, setSearch] = useState("")
  const [isSearch, setIsSearch] = useState(false)
  const [currentPage, setCurrentPage] = useState(1)

  const loadData = (
    url = "/api/master/rate-limit",
    perpage = 10,
    params = { page: currentPage }
  ) => {
    setLoading(true)
    axios
      .post(url, { perpage, ...params })
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
        data.sort((a, b) => b["rate_limit_status"] - a["rate_limit_status"])
        const newData = data.map((value, index) => {
          const number = paginatedNumbers[index]
          return {
            number: number,
            ...value,
          }
        })
        setDatas(newData)
      })
      .catch((e) => console.log(e, "error"))
      .finally(() => setLoading(false))
  }
  useEffect(() => {
    loadData()
  }, [])

  const handleChange = (page, pageSize = 10) => {
    setCurrentPage(page)
    loadData(`/api/master/rate-limit/?page=${page}`, pageSize, {
      search,
      page,
    })
  }

  const handleChangeSearch = () => {
    setIsSearch(true)
    loadData(`/api/master/rate-limit`, 10, { search })
  }

  const updateStatus = ({ id, rate_limit_status }) => {
    axios
      .post(`/api/master/rate-limit/update/${id}`, {
        rate_limit_status,
      })
      .then((res) => {
        toast.success("Status berhasil diupdate")
        loadData()
      })
      .catch((err) => {
        toast.error("Status gagal Di update")
      })
  }

  return (
    <Layout title="List Rate Limit">
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
              ...packageListColumn,
              {
                title: "Status",
                key: "rate_limit_status",
                dataIndex: "rate_limit_status",
                render: (text, record) => {
                  return (
                    <Switch
                      checked={text > 0}
                      onChange={(e) =>
                        updateStatus({ ...record, rate_limit_status: e })
                      }
                    />
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

export default RateLimitSetting
