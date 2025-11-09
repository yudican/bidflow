import {
  CloseCircleFilled,
  EditOutlined,
  EyeFilled,
  FolderFilled,
  PlusOutlined,
  RightOutlined,
  SearchOutlined,
} from "@ant-design/icons"
import { Dropdown, Input, Menu, Pagination, Table } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { useNavigate } from "react-router-dom"
import Layout from "../../components/layout"
import { getItem } from "../../helpers"
import { returnListColumn } from "./config"

const CaseManual = () => {
  const navigate = useNavigate()
  const [loading, setLoading] = useState(false)
  const [returnData, setReturnData] = useState([])
  const [total, setTotal] = useState(0)
  const [search, setSearch] = useState("")
  const [isSearch, setIsSearch] = useState(false)
  const [currentPage, setCurrentPage] = useState(1)

  const loadContact = (
    url = "/api/case/manual/list",
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
    loadContact(`/api/case/manual/list/?page=${page}`, pageSize, {
      search,
      ...filterData,
    })
  }

  const handleChangeSearch = () => {
    setIsSearch(true)
    loadContact(`/api/case/manual/list`, 10, { search })
  }

  const actionColumns = [
    {
      title: "Action",
      key: "action",
      align: "center",
      fixed: "right",
      render: (text, record) => {
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
                  onClick={() => navigate(`form/${record.uid_case}`)}
                >
                  Ubah
                </Menu.Item>
                <Menu.Item
                  icon={<EyeFilled />}
                  onClick={() => navigate(`detail/${record.uid_case}`)}
                >
                  Detail
                </Menu.Item>
              </Menu>
            }
          ></Dropdown.Button>
        )
      },
    },
  ]

  const rightContent = (
    <div className="flex justify-between items-center">
      {/* <FilterModal handleOk={handleFilter} /> */}
      <button
        onClick={() => navigate("form")}
        className="text-white bg-blueColor hover:bg-blueColor/90 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
      >
        <PlusOutlined />
        <span className="ml-2">Tambah Data</span>
      </button>
    </div>
  )

  return (
    <Layout title="Manual Case" rightContent={rightContent}>
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

export default CaseManual
