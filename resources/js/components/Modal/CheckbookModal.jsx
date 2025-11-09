import { PlusOutlined, SearchOutlined } from "@ant-design/icons"
import { Input, Modal, Pagination, Table } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { contactCheckbookListColumn } from "../../Pages/Contact/config"

const CheckbookModal = ({ handleOk, checkbook = null }) => {
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [currentPage, setCurrentPage] = useState(1)
  const [total, setTotal] = useState(0)
  const [loadingcheckbook, setLoadingCheckbook] = useState(false)
  const [checkbooks, setCheckbooks] = useState([])
  const [search, setSearch] = useState(null)

  const showModal = () => {
    setIsModalOpen(true)
  }

  const loadCheckbook = (
    url = "/api/master/checkbook",
    perpage = 10,
    params = { page: currentPage, search }
  ) => {
    setLoadingCheckbook(true)
    axios
      .post(url, {
        perpage,
        ...params,
      })
      .then((res) => {
        const { data, total, current_page } = res.data.data
        setTotal(total) // set total of total data products
        setCurrentPage(current_page)
        setCheckbooks(data)
        setLoadingCheckbook(false)
      })
      .catch((error) => setLoadingCheckbook(false))
  }
  const handleChange = (page, pageSize = 10) => {
    setCurrentPage(page)
    loadCheckbook(`/api/master/checkbook/?page=${page}`, pageSize, {
      search,
      page,
    })
  }

  useEffect(() => {
    loadCheckbook()
  }, [])

  const handleCancel = () => {
    setIsModalOpen(false)
  }

  const value = checkbook
  return (
    <div>
      <Input.Search
        onClick={() => showModal()}
        readOnly
        placeholder="Pilih Checkbook"
        value={checkbook}
        onSearch={() => showModal()}
      />

      <Modal
        maskClosable={false}
        title="Pilih Checkbook"
        open={isModalOpen}
        onOk={() => {
          handleOk()
          setIsModalOpen(false)
        }}
        onCancel={handleCancel}
        footer={null}
        width={1000}
      >
        <Input
          placeholder="Cari Checkbook disini.."
          size={"large"}
          className="rounded mb-4"
          allowClear
          suffix={
            <SearchOutlined
              onClick={() => {
                loadCheckbook(`/api/master/checkbook`, 10, {
                  search,
                  page: 1,
                  // ...filterData,
                })
              }}
            />
          }
          value={search}
          onChange={(e) => {
            setSearch(e.target.value)
            if (e.target.value === "") {
              setSearch(null)
              loadCheckbook()
            }
          }}
          onPressEnter={() => {
            loadCheckbook(`/api/master/checkbook`, 10, {
              search,
              page: 1,
              // ...filterData,
            })
          }}
        />
        <Table
          dataSource={checkbooks}
          columns={[
            ...contactCheckbookListColumn,
            {
              title: "Action",
              dataIndex: "action",
              key: "action",
              fixed: "right",
              align: "center",
              render: (_, record) => {
                const selected = value == record?.bank_name
                if (selected) {
                  return (
                    <button
                      onClick={() => {
                        handleOk({
                          value: record?.bank_name,
                          checkbook_id: record?.id,
                        })
                        setIsModalOpen(false)
                      }}
                      className="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
                    >
                      <PlusOutlined />
                      <span className="ml-2">Pilih</span>
                    </button>
                  )
                }

                return (
                  <button
                    onClick={() => {
                      handleOk({
                        value: record?.bank_name,
                        checkbook_id: record?.id,
                      })
                      setIsModalOpen(false)
                    }}
                    className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
                  >
                    <PlusOutlined />
                    <span className="ml-2">Pilih</span>
                  </button>
                )
              },
            },
          ]}
          loading={loadingcheckbook}
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
          onShowSizeChange={(current, size) => {
            setCurrentPage(current)
          }}
        />
      </Modal>
    </div>
  )
}

export default CheckbookModal
