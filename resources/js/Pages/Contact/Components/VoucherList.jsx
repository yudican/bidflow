import { Pagination, Table } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"

const VoucherList = ({ stage = null, contact = null, columns = [] }) => {
  const [loading, setLoading] = useState(false)
  const [datas, setDatas] = useState([])
  const [total, setTotal] = useState(0)
  const [search, setSearch] = useState("")
  const [isSearch, setIsSearch] = useState(false)
  const [currentPage, setCurrentPage] = useState(1)
  const [perPage, setPerpage] = useState(10)
  const [filterData, setFilterData] = useState({})

  const [selectedRowKeys, setSelectedRowKeys] = useState([])
  const loadData = (
    url = "/api/contact/voucher",
    perpage = perPage,
    params = { page: currentPage }
  ) => {
    setLoading(true)
    axios
      .post(url, { perpage, ...params, user_id: contact })
      .then((res) => {
        const { data, from, total } = res.data.data
        setTotal(total) // set total of total data products

        const newData = data.map((value, index) => {
          const number = from + index
          return {
            ...value,
            number,
          }
        })

        setDatas(newData)
        setLoading(false)
      })
      .catch(() => setLoading(false))
  }
  useEffect(() => {
    loadData()
  }, [stage])

  const handleChange = (page, pageSize = 10) => {
    setCurrentPage(page)
    loadData(`/api/contact/voucher/?page=${page}`, pageSize, {
      search,
      page,
      ...filterData,
    })
  }

  // const handleChangeSearch = () => {
  //   setIsSearch(true)
  //   loadData(`/api/contact/voucher/`, 10, { search })
  // }

  // const handleFilter = (data) => {
  //   setFilterData(data)
  //   loadData(`/api/contact/voucher/`, 10, data)
  // }

  // selected row handler
  const rowSelection = {
    selectedRowKeys,
    onChange: (newSelectedRowKeys) => setSelectedRowKeys(newSelectedRowKeys),
    getCheckboxProps: () => ({
      disabled: false, // Column configuration not to be checked
    }),
  }

  return (
    <div>
      <Table
        rowSelection={rowSelection}
        scroll={{ x: "max-content" }}
        tableLayout={"auto"}
        // dataSource={[1]}
        dataSource={datas}
        columns={columns}
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
        pageSizeOptions={["10", "20", "50", "100"]}
        onShowSizeChange={(current, size) => {
          setCurrentPage(current)
          setPerpage(size)
        }}
      />
    </div>
  )
}

export default VoucherList
