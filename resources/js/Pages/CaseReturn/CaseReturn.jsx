import {
  CloseCircleFilled,
  FolderFilled,
  SearchOutlined,
} from "@ant-design/icons"
import { Input, Pagination, Table } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { useNavigate } from "react-router-dom"
import Layout from "../../components/layout"
import { getItem } from "../../helpers"
import { returnListColumn } from "./config"

const CaseReturn = () => {
  const navigate = useNavigate()
  const [loading, setLoading] = useState(false)
  const [returnData, setReturnData] = useState([])
  const [total, setTotal] = useState(0)
  const [search, setSearch] = useState("")
  const [isSearch, setIsSearch] = useState(false)
  const [currentPage, setCurrentPage] = useState(1)

  const loadContact = (
    url = "/api/case/return/list",
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
        setReturnData(data)
        setLoading(false)
      })
  }
  useEffect(() => {
    loadContact()
  }, [])

  const handleChange = (page, pageSize = 10) => {
    loadContact(`/api/case/return/list/?page=${page}`, pageSize, {
      search,
      ...filterData,
    })
  }

  const handleChangeSearch = () => {
    setIsSearch(true)
    loadContact(`/api/case/return/list`, 10, { search })
  }

  const actionColumns = [
    {
      title: "Action",
      dataIndex: "action",
      key: "action",
      render: (text, record, index) => {
        return (
          <button
            onClick={() => {
              navigate("/case/return/" + record.uid_retur)
            }}
            className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
          >
            <FolderFilled />
            <span className="ml-2">{`Detail`}</span>
          </button>
        )
      },
    },
  ]

  return (
    <Layout title="Return Order">
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
            dataSource={returnData}
            columns={[...returnListColumn, ...actionColumns]}
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
          />
        </div>
      </div>
    </Layout>
  )
}

export default CaseReturn
