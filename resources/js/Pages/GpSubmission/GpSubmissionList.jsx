import { EyeOutlined } from "@ant-design/icons"
import { Pagination, Table } from "antd"
import React, { useEffect, useState } from "react"
import { useNavigate } from "react-router-dom"
import Layout from "../../components/layout"
import { getItem } from "../../helpers"
import { orderListGpColumn } from "./config"

const GpSubmissionList = () => {
  const navigate = useNavigate()
  const [loading, setLoading] = useState(false)
  const [datas, setDatas] = useState([])
  const [total, setTotal] = useState(0)
  const [currentPage, setCurrentPage] = useState(1)
  const base_url = getItem("service_ginee_url")
  const loadData = (
    url = "/api/channel/gp/list",
    perpage = 10,
    params = {}
  ) => {
    setLoading(true)
    axios
      .post(base_url + url, { perpage, ...params })
      .then((res) => {
        const { data, total, current_page } = res.data.data
        setTotal(total)
        setCurrentPage(current_page)
        setDatas(data)
        setLoading(false)
      })
      .catch((err) => {
        setLoading(false)
      })
  }
  useEffect(() => {
    loadData()
  }, [])

  const handleChangePage = (page, pageSize = 10) => {
    loadData(`/api/channel/gp/list/?page=${page}`, pageSize)
  }

  const actions = [
    {
      title: "action",
      key: "action",
      dataIndex: "action",
      render: (text, record) => (
        <button
          onClick={() => navigate("list/detail/" + record.id)}
          className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
        >
          <EyeOutlined />
          <span className="ml-2">Detail</span>
        </button>
      ),
    },
  ]

  return (
    <Layout title="Submission List">
      <div className="card">
        <div className="card-body">
          <Table
            dataSource={datas}
            columns={[...orderListGpColumn, ...actions]}
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
            onChange={handleChangePage}
          />
        </div>
      </div>
    </Layout>
  )
}

export default GpSubmissionList
