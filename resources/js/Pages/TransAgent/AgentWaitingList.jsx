import { PlusOutlined } from "@ant-design/icons"
import { Button, Pagination, Table } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { useNavigate } from "react-router-dom"
import Layout from "../../components/layout"
import { TransAgentWaitingPaymentListColumn } from "./config"

const AgentWaitingList = () => {
  const navigate = useNavigate()
  const [loading, setLoading] = useState(false)
  const [contacts, setContacts] = useState([])
  const [total, setTotal] = useState(0)
  const [currentPage, setCurrentPage] = useState(1)

  const loadContact = (url = "/api/transAgentWaitingPayment", perpage = 10) => {
    setLoading(true)
    axios.post(url, { perpage }).then((res) => {
      const { data, total, current_page } = res.data.data
      setTotal(total)
      setCurrentPage(current_page)
      console.log(data)

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
    loadContact()
  }, [])

  const handleChange = (page, pageSize = 10) => {
    loadContact(`/api/contact/?page=${page}`, pageSize)
  }

  return (
    <Layout
      // rightContent={
      //     <Button
      //         onClick={() => navigate("/contact/create")}
      //         className={"flex items-center"}
      //     >
      //         <PlusOutlined />
      //         Tambah Kontak
      //     </Button>
      // }

      title="Transaction Agent - Waiting Payment"
    >
      <div className="card">
        <div className="card-body">
          <Table
            dataSource={contacts}
            columns={TransAgentWaitingPaymentListColumn}
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

export default AgentWaitingList
