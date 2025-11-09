import {
  CloseCircleFilled,
  DeleteOutlined,
  EditOutlined,
  FileExcelOutlined,
  LoadingOutlined,
  PlusOutlined,
  RightOutlined,
  SearchOutlined,
} from "@ant-design/icons"
import {
  Dropdown,
  Input,
  Menu,
  Pagination,
  Popconfirm,
  Switch,
  Table,
} from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { useNavigate } from "react-router-dom"
import { toast } from "react-toastify"
import FilterTagModal from "../../../components/FilterTagModal"
import Layout from "../../../components/layout"
import { formatNumber, getItem, inArray, paginateData } from "../../../helpers"
import FilterModal from "./Components/FilterModal"
import { productVariantListColumn } from "./config"

const ProductVariantList = () => {
  const navigate = useNavigate()
  const [loading, setLoading] = useState(false)
  const [datas, setDatas] = useState([])
  const [total, setTotal] = useState(0)
  const [search, setSearch] = useState("")
  const [isSearch, setIsSearch] = useState(false)
  const [currentPage, setCurrentPage] = useState(1)
  const [perPage, setPerpage] = useState(10)
  const [filterData, setFilterData] = useState({})
  const [loadingExport, setLoadingExport] = useState(false)
  const [warehouses, setWarehouses] = useState([])
  const [selectedWarehouse, setSelectedWarehouse] = useState(
    getItem("selectedWarehouse")
  )

  const loadData = (
    url = "/api/product-management/product-variant",
    perpage = perPage,
    params = { page: currentPage }
  ) => {
    setLoading(true)
    axios
      .post(url, {
        perpage,
        account_id: getItem("account_id"),
        warehouse_ids: ["all"],
        ...params,
      })
      .then((res) => {
        const { data, total, current_page } = res.data.data

        setTotal(total) // set total of total data products

        const numberPages = Array(total) // create number columns data based on total data
          .fill()
          .map((_, index) => index + 1)
        const paginatedNumbers = paginateData(
          numberPages,
          current_page,
          perpage
        ) // convert to paginated data

        const newData = data.map((item, index) => {
          const number = paginatedNumbers[index] // overriding response to set paginated number per pages

          return {
            ...item,
            id: item.id,
            number,
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

  const loadWarehouses = () => {
    axios
      .get("/api/master/warehouse")
      .then((res) => {
        const { data } = res.data
        setWarehouses(data)
      })
      .catch((e) => setLoading(false))
  }

  useEffect(() => {
    loadWarehouses()
    const filter = getItem("variantFilter", true) || null
    if (filter || selectedWarehouse) {
      loadData(`/api/product-management/product-variant`, 10, {
        ...filter,
        warehouse_id: selectedWarehouse,
      })
    } else {
      loadData()
    }
  }, [])

  const handleChange = (page, pageSize = 10) => {
    setCurrentPage(page) // only update current page while pagination is cliked
    loadData(
      `/api/product-management/product-variant/?page=${page}`, // then load data
      pageSize,
      {
        search,
        page,
        warehouse_id: selectedWarehouse,
        ...filterData,
      }
    )
  }

  const handleChangeSearch = () => {
    setIsSearch(true)
    loadData(`/api/product-management/product-variant`, 10, {
      search,
      // page: currentPage,
      // warehouse_id: selectedWarehouse,
      ...filterData,
    })
  }

  const handleFilter = (data) => {
    setFilterData(data)
    loadData(`/api/product-management/product-variant`, 10, {
      ...data,
      search,
      page: currentPage,
      // warehouse_id: selectedWarehouse,
    })
  }

  const deleteBanner = (banner_id) => {
    axios
      .post(`/api/product-management/product-variant/delete/${banner_id}`, {
        _method: "DELETE",
      })
      .then((res) => {
        toast.success("Data Produk Varian berhasil dihapus")
        loadData()
      })
      .catch((err) => {
        toast.error("Data Produk Varian gagal dihapus")
      })
  }

  const updateStatus = (record) => {
    axios
      .post(
        `/api/product-management/product-variant/status/${record.id}`,
        record
      )
      .then((res) => {
        toast.success("Status berhasil diupdate")
        loadData()
      })
      .catch((err) => {
        toast.error("Status gagal Di update")
      })
  }

  const handleExport = () => {
    setLoadingExport(true)
    axios
      .post(`/api/product-management/product-variant/export`, filterData)
      .then((res) => {
        const { data } = res.data
        setLoadingExport(false)
        return window.open(data)
      })
      .catch((err) => {
        setLoadingExport(false)
      })
  }

  const show = !inArray(getItem("role"), [
    "adminsales",
    "leadwh",
    "leadsales",
    "leadcs",
    "adminwarehouse",
  ])

  const rightContent = (
    <div className="flex justify-between items-center">
      <FilterModal handleOk={handleFilter} />
      <button
        onClick={() => (loadingExport ? null : handleExport())}
        className="text-white bg-green-800 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
      >
        {loadingExport ? <LoadingOutlined /> : <FileExcelOutlined />}
        <span className="ml-2">Export</span>
      </button>
      {show && (
        <button
          onClick={() => navigate("form")}
          className="text-white bg-blueColor hover:bg-blueColor/90 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
        >
          <PlusOutlined />
          <span className="ml-2">Tambah Data</span>
        </button>
      )}
    </div>
  )

  return (
    <Layout rightContent={rightContent} title="List Produk Varian">
      <div className="card">
        <div className="card-body">
          <FilterTagModal handleOk={handleFilter} />

          <div className="mb-4">
            {/* <div className="flex overflow-y-auto mb-4">
              {[...warehouses]?.map((item, index) => (
                <div
                  key={index}
                  className="cursor-pointer mr-4"
                  onClick={() => {
                    setSelectedWarehouse(item.id)
                    localStorage.setItem("selectedWarehouse", item.id)
                    const filter = getItem("variantFilter", true) || {}
                    loadData(`/api/product-management/product-variant`, 10, {
                      ...filter,
                      warehouse_id: item.id,
                    })
                  }}
                >
                  <div
                    key={index}
                    className={`
                  card w-96
                  bg-gradient-to-r from-white via-white ${
                    selectedWarehouse === item.id ? "to-blue-500/20" : ""
                  }
                  hover:to-blue-500/20
                `}
                  >
                    <div className="p-3 border-b flex justify-between">
                      <div className="flex items-center">
                        <strong
                          className={`text-base font-semibold text-${
                            selectedWarehouse === item.id ? "blue-500" : "black"
                          }`}
                        >
                          {item.name}
                        </strong>
                      </div>
                    </div>
                    <div className="card-body">
                      <strong
                        className={`text-${
                          selectedWarehouse === item.id ? "blue-500" : "black"
                        } text-lg font-medium`}
                      >
                        Total Stock: {item.stock}
                      </strong>
                    </div>
                  </div>
                </div>
              ))}
            </div> */}

            <div className="flex justify-between items-center">
              <Input
                placeholder="Cari disini"
                size={"large"}
                className="rounded max-w-lg"
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
              <strong className="float-right text-red-400">
                Total Data: {total}
              </strong>
            </div>
          </div>
          <Table
            scroll={{ x: "max-content" }}
            tableLayout={"auto"}
            dataSource={datas}
            columns={[
              ...productVariantListColumn,
              {
                title: "Status",
                key: "status",
                dataIndex: "status",
                render: (text, record, index) => {
                  return (
                    <Switch
                      key={index}
                      checked={text > 0}
                      onChange={(e) => {
                        updateStatus({ status: e ? "1" : "0", id: record.id })
                      }}
                    />
                  )
                },
              },
              {
                title: "Action",
                key: "id",
                align: "center",
                fixed: "right",
                width: 100,
                render: (text) => {
                  if (!show) return null
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
                            onClick={() => navigate(`form/${text.id}`)}
                          >
                            Ubah
                          </Menu.Item>
                          <Popconfirm
                            title="Yakin hapus data ini?"
                            onConfirm={() => deleteBanner(text.id)}
                            // onCancel={cancel}
                            okText="Ya, Hapus"
                            cancelText="Batal"
                          >
                            <Menu.Item icon={<DeleteOutlined />}>
                              <span>Hapus</span>
                            </Menu.Item>
                          </Popconfirm>
                        </Menu>
                      }
                    ></Dropdown.Button>
                  )
                },
              },
            ]}
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
            onShowSizeChange={(current, size) => {
              setCurrentPage(current)
              setPerpage(size)
            }}
          />
        </div>
      </div>
    </Layout>
  )
}

export default ProductVariantList
