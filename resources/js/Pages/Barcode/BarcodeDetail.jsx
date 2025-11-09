import { CloseCircleFilled, SearchOutlined } from "@ant-design/icons"
import { Input, Pagination, Table } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { useNavigate, useParams } from "react-router-dom"
import Layout from "../../components/layout"
import { formatNumber } from "../../helpers"
import FilterModal from "./Components/FilterModal"
import { binDetailListColumn } from "./config"

const BarcodeDetail = () => {
  const navigate = useNavigate()
  const { product_variant_id } = useParams()
  const [loading, setLoading] = useState(false)
  const [datas, setDatas] = useState([])
  const [total, setTotal] = useState(0)
  const [search, setSearch] = useState("")
  const [isSearch, setIsSearch] = useState(false)
  const [currentPage, setCurrentPage] = useState(1)
  const [filterData, setFilterData] = useState({})

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
        setTotal(total)
        setCurrentPage(current_page)
        const newData = data.map((item) => {
          return {
            ...item,
            id: item.id,
            name: item.name,
            package_name: item.package_name,
            variant_name: item.variant_name,
            product_image: item?.image_url,
            status: item?.status,
            stock: item?.stocks,
            stock_off_market: item?.stock_of_market,
            final_price: formatNumber(item?.price?.final_price, "Rp. "),
          }
        })

        setDatas(newData)
        setLoading(false)
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
      href="/barcode/list"
      title="Barcode Detail"
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

export default BarcodeDetail
