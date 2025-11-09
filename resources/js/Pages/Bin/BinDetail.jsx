import { CloseCircleFilled, SearchOutlined } from "@ant-design/icons"
import { Input, Pagination, Table } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { useNavigate, useParams } from "react-router-dom"
import Layout from "../../components/layout"
import { formatNumber } from "../../helpers"
import FilterModal from "./Components/FilterModal"
import { binDetailListColumn } from "./config"

const BinListDetail = () => {
  const navigate = useNavigate()
  const { product_variant_id } = useParams()
  const [loading, setLoading] = useState(false)
  const [datas, setDatas] = useState([])
  const [total, setTotal] = useState(0)
  const [search, setSearch] = useState("")
  const [isSearch, setIsSearch] = useState(false)
  const [currentPage, setCurrentPage] = useState(1)
  const [filterData, setFilterData] = useState({})
  const [productVariant, setProductVariant] = useState([])

  const loadData = (
    url = `/api/detail/bin/${product_variant_id}`,
    perpage = 10,
    params = { page: currentPage }
  ) => {
    setLoading(true)
    axios
      .post(url, { perpage, product_variant_id, ...params })
      .then((res) => {
        const { data, total, current_page } = res.data.data
        setProductVariant(res.data.product_variant)
        setTotal(total)
        setCurrentPage(current_page)

        const newData = data.map((item) => {
          return {
            ...item,
            id: item.id,
            order_number: item?.order_transfer?.order_number,
            invoice_number: item?.order_transfer?.invoice_number,
          }
        })
        console.log(newData)
        setLoading(false)
        setDatas(newData)
      })
      .catch((e) => setLoading(false))
  }

  useEffect(() => {
    loadData()
  }, [])

  const handleChange = (page, pageSize = 10) => {
    loadData(`/api/detail/bin/${product_variant_id}?page=${page}`, pageSize, {
      search,
      page,
      ...filterData,
    })
  }

  const handleChangeSearch = () => {
    setIsSearch(true)
    loadData(`/api/detail/bin/${product_variant_id}`, 10, { search })
  }

  const handleFilter = (data) => {
    setFilterData(data)
    loadData(`/api/detail/bin/${product_variant_id}`, 10, data)
  }

  const rightContent = (
    <div className="flex justify-between items-center">
      <FilterModal handleOk={handleFilter} />
    </div>
  )
  
  return (
    <Layout
      rightContent={rightContent}
      title="History BIN"
      href="/bin/list"
      lastItemLabel={productVariant}
    >
      <div className="card">
        <div className="card-body">
          <div className="row mb-4">
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
            columns={[...binDetailListColumn]}
            loading={loading}
            pagination={false}
            rowKey="id"
          />
          <Pagination
            defaultCurrent={1}
            pageSize={10}
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

export default BinListDetail
