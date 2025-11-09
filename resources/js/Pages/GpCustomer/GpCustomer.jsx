import { CloseOutlined } from "@ant-design/icons"
import { Pagination, Popconfirm, Table } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { toast } from "react-toastify"
import Layout from "../../components/layout"
import { getItem } from "../../helpers"
import GpCustomerForm from "./Components/GpCustomerForm"
import { gpCustomerColumns } from "./config"

const GpCustomer = () => {
  const [datas, setDatas] = useState([])
  const [total, setTotal] = useState(0)
  const [currentPage, setCurrentPage] = useState(1)
  const [loading, setLoading] = useState(false)

  const base_url = getItem("service_ginee_url")
  const loadData = (
    url = "/api/master/gp-customer-code/list",
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
        setLoading(false)

        setDatas(data)
      })
      .catch((err) => {
        setLoading(false)
      })
  }

  const deleteData = (customer_id) => {
    axios
      .post(`${base_url}/api/master/gp-customer-code/delete/${customer_id}`, {
        _method: "DELETE",
      })
      .then((res) => {
        const { message } = res.data
        loadData()
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

  useEffect(() => {
    loadData()
  }, [])

  const handleChangePage = (page, pageSize = 10) => {
    loadData(`/api/master/gp-customer-code/list/?page=${page}`, pageSize)
  }

  const actions = [
    {
      title: "Actions",
      key: "action",
      dataIndex: "action",
      render: (text, record) => {
        return (
          <div className="flex justify-between items-center w-20">
            <GpCustomerForm
              refetch={() => loadData()}
              initialValues={record}
              update
              url={`${base_url}/api/master/gp-customer-code/update/${record.id}`}
            />
            <Popconfirm
              title="Yakin Hapus Data ini?"
              onConfirm={() => deleteData(record.id)}
              // onCancel={cancel}
              okText="Ya, Hapus"
              cancelText="Batal"
            >
              <button className="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center">
                <CloseOutlined />
              </button>
            </Popconfirm>
          </div>
        )
      },
    },
  ]

  return (
    <Layout
      title="GP Customer"
      rightContent={
        <GpCustomerForm
          refetch={() => loadData()}
          parents={[]}
          url={`${base_url}/api/master/gp-customer-code/create`}
        />
      }
    >
      <div className="card">
        <div className="card-body">
          <Table
            dataSource={datas}
            columns={[...gpCustomerColumns, ...actions]}
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

export default GpCustomer
