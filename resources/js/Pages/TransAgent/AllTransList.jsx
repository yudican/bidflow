import { Dropdown, Pagination, Table } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { useNavigate } from "react-router-dom"
import Layout from "../../components/layout"
import { getItem } from "../../helpers"
import BulkAction from "./Components/BulkAction"
import ModalFilterTransaction from "./Components/ModalFilterTransaction"
import { TransAgentAllListColumn } from "./config"

const AllTransList = () => {
  const navigate = useNavigate()
  const [loading, setLoading] = useState(false)
  const [contacts, setContacts] = useState([])
  const [total, setTotal] = useState(0)
  const [currentPage, setCurrentPage] = useState(1)
  const [filterData, setFilterData] = useState({})
  const [selectedRowKeys, setSelectedRowKeys] = useState([])

  const loadData = (url = "/api/transAgent", perpage = 10, params = {}) => {
    setLoading(true)
    axios
      .post(url, { perpage, account_id: getItem("account_id"), ...params })
      .then((res) => {
        const { data, total, current_page } = res.data.data
        setTotal(total)
        setCurrentPage(current_page)
        const newdata = data.map((transagent, index) => {
          return {
            key: transagent.id,
            id: index + 1,
            name: transagent.user.name,
            id_transaksi: transagent.id_transaksi,
            created_at: moment(transagent.created_at).format("DD-MM-YYYY"),
            nominal: transagent.nominal,
          }
        })

        setContacts(newdata)
        setLoading(false)
      })
  }
  useEffect(() => {
    loadData()
  }, [])

  const handleChange = (page, pageSize = 10) => {
    loadData(`/api/transAgent/?page=${page}`, pageSize, filterData)
  }

  const handleFilter = (data) => {
    setFilterData(data)
    loadData(`/api/transAgent`, 10, data)
  }

  const onSelectChange = (newSelectedRowKeys) => {
    setSelectedRowKeys(newSelectedRowKeys)
  }

  const rowSelection = {
    selectedRowKeys,
    onChange: onSelectChange,
  }

  const rightContent = (
    <div className="flex justify-between items-center">
      <ModalFilterTransaction handleOk={handleFilter} />

      {/* dropdown */}
      <BulkAction selectedRowKeys={selectedRowKeys} />
    </div>
  )

  return (
    <Layout rightContent={rightContent} title="Transaction Agent">
      <div className="card">
        <div className="card-body">
          <Table
            rowSelection={rowSelection}
            dataSource={contacts}
            columns={TransAgentAllListColumn}
            loading={loading}
            pagination={false}
            rowKey="key"
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

export default AllTransList
