import { PlusOutlined, SearchOutlined } from "@ant-design/icons"
import { Input, Modal, Pagination, Table } from "antd"
import React, { useState } from "react"
import { useGetSalesOrderQuery } from "../../../configs/Redux/Services/salesOrderService"
import { createQueryString, getItem } from "../../../helpers"
import { orderLeadListColumn } from "../config"

const OrderNumberModal = ({
  handleOk,
  value = null,
  type = "order-manual",
  isReturn = false,
}) => {
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [currentPage, setCurrentPage] = useState(1)
  const [perPage, setPerpage] = useState(10)
  const [total, setTotal] = useState(0)

  const [search, setSearch] = useState(null)
  const [selectedRowKeys, setSelectedRowKeys] = useState([])
  const [selectedSoNumber, setSelectedSoNumber] = useState([])

  const [paramUrl, setParamUrl] = useState("/api/sales-order")
  const {
    data: salesOrderData,
    isLoading: salesOrderLoading,
    isFetching: salesOrderFetching,
  } = useGetSalesOrderQuery(paramUrl)

  const showModal = () => {
    setIsModalOpen(true)
    loadOrder(`/api/sales-order`)
  }

  const loadOrder = (
    url = "/api/sales-order",
    perpage = perPage,
    params = { page: currentPage, status: 2 }
  ) => {
    const bodyData = {
      perpage,
      type: type.replace("order-", ""),
      account_id: parseInt(getItem("account_id")),
      isDelivery: true,
      ...params,
    }

    Object.keys(bodyData).forEach((value) => {
      if (Array.isArray(bodyData[value])) {
        bodyData[value] = bodyData[value].join(",")
      } else {
        bodyData[value] = bodyData[value]
      }
    })

    const cleanedData = Object.fromEntries(
      Object.entries(bodyData).filter(
        ([key, value]) => value !== null && value !== undefined
      )
    )

    const queryString = createQueryString(cleanedData)
    setParamUrl(`${url}${queryString}`)
  }

  const handleChange = (page, pageSize = 10) => {
    setCurrentPage(page)
    loadOrder(`/api/sales-order`, pageSize, {
      search,
      page,
      account_id: getItem("account_id"),
      // ...filterData,
    })
  }

  const handleCancel = () => {
    setIsModalOpen(false)
  }

  // selected row handler
  const rowSelection = {
    selectedRowKeys,
    onChange: (e) => {
      setSelectedRowKeys(e)
      const productData = []
      if (e.length > 0) {
        e.map((value) => {
          const item = salesOrderData?.data?.data.filter(
            (item) => item.id === value
          )
          const products = item.map((row, index) => row.title)
          productData.push(...products)
        })
      }
      setSelectedSoNumber(productData)
    },
    getCheckboxProps: (record) => {
      return {
        disabled: false,
      }
    },
  }
  console.log(selectedSoNumber, "selectedSoNumber")
  console.log("salesOrderData?.data?.data : ", salesOrderData?.data?.data)
  return (
    <div>
      <Input.Search
        onClick={() => showModal()}
        readOnly
        placeholder="Pilih Nomer SO"
        value={value}
        onSearch={() => showModal()}
      />

      <Modal
        maskClosable={false}
        title="Pilih So Number"
        open={isModalOpen}
        onOk={() => {
          if (type == "order-konsinyasi") {
            handleOk({
              value: selectedSoNumber?.join(","),
              uid_lead: selectedRowKeys,
            })
          }
          setIsModalOpen(false)
        }}
        onCancel={handleCancel}
        width={1000}
      >
        <Input
          placeholder="Cari So Number disini.."
          size={"large"}
          className="rounded mb-4"
          allowClear
          suffix={
            <SearchOutlined
              onClick={() => {
                loadOrder(`/api/sales-order`, 10, {
                  search,
                  status: 2,
                  page: 1,
                  account_id: getItem("account_id"),
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
              loadOrder()
            }
          }}
          onPressEnter={() => {
            loadOrder(`/api/sales-order`, 10, {
              search,
              page: 1,
              account_id: getItem("account_id"),
              // ...filterData,
            })
          }}
        />
        <Table
          rowSelection={type == "order-konsinyasi" && !isReturn && rowSelection}
          dataSource={salesOrderData?.data?.data || []}
          columns={[
            ...orderLeadListColumn,
            ...[
              type == "order-konsinyasi" && !isReturn
                ? []
                : {
                  title: "Action",
                  dataIndex: "action",
                  key: "action",
                  render: (_, record) => {
                    const selected = value === record?.title
                    if (selected) {
                      return (
                        <button
                          onClick={() => {
                            handleOk({
                              value: record?.title,
                              uid_lead: record?.id,
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
                            value: record?.title,
                            uid_lead: record?.id,
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
            ],
          ]}
          loading={salesOrderLoading || salesOrderFetching}
          pagination={false}
          rowKey="id"
          scroll={{ x: "max-content" }}
          tableLayout={"auto"}
        />
        <Pagination
          defaultCurrent={1}
          current={currentPage}
          total={salesOrderData?.data?.total}
          className="mt-4 text-center"
          onChange={handleChange}
          pageSizeOptions={["10", "20", "50", "100", "200", "500"]}
          onShowSizeChange={(current, size) => {
            setCurrentPage(current)
            setPerpage(size)
          }}
        />
      </Modal>
    </div>
  )
}

export default OrderNumberModal
