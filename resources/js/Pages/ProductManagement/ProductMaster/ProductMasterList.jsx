import {
  CloseCircleFilled,
  DeleteOutlined,
  EditOutlined,
  FileAddOutlined,
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
import { getItem, inArray } from "../../../helpers"
import FilterModal from "./Components/FilterModal"
import { productListColumn } from "./config"

const ProductMasterList = () => {
  const navigate = useNavigate()
  const [loading, setLoading] = useState(false)
  const [datas, setDatas] = useState([])
  const [warehouses, setWarehouses] = useState([])
  const [total, setTotal] = useState(0)
  const [search, setSearch] = useState("")
  const [isSearch, setIsSearch] = useState(false)
  const [currentPage, setCurrentPage] = useState(1)
  const [filterData, setFilterData] = useState({})
  const [loadingExport, setLoadingExport] = useState(false)
  const [selectedWarehouse, setSelectedWarehouse] = useState(null)

  const loadData = (
    url = "/api/product-management/product",
    perpage = 10,
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
        const { data, total, from, current_page } = res.data.data
        setTotal(total)
        setCurrentPage(current_page)
        const newData = data.map((item, index) => {
          return {
            ...item,
            id: item.id,
            name: item.name,
            brand_name: item.brand_name,
            category_name: item.category_name,
            product_image: item?.image_url,
            status: item?.status,
            stock: item?.stock_by_warehouse,
            sku: item?.sku,
            sku_marketplace: item?.sku_marketplace,
            image: null,
            number: from + index,
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
    loadData()
  }, [])

  const handleChange = (page, pageSize = 10) => {
    loadData(`/api/product-management/product/?page=${page}`, pageSize, {
      search,
      page,
      ...filterData,
    })
  }

  const handleChangeSearch = () => {
    setIsSearch(true)
    loadData(`/api/product-management/product`, 10, {
      search,
      // page: currentPage,
      ...filterData,
    })
  }

  const handleFilter = (data) => {
    setFilterData(data)
    loadData(`/api/product-management/product`, 10, { ...data, search })
  }

  const handleExportContent = () => {
    setLoadingExport(true)
    axios
      .post(`/api/product-management/product/export`, {
        search,
        account_id: getItem("account_id"),
        warehouse_ids: ["all"],
        ...filterData,
      })
      .then((res) => {
        const { data } = res.data
        window.open(data)
        setLoadingExport(false)
      })
      .catch((e) => setLoadingExport(false))
  }

  const deleteBanner = (banner_id) => {
    axios
      .post(`/api/product-management/product/delete/${banner_id}`, {
        _method: "DELETE",
      })
      .then((res) => {
        toast.success("Data Produk Master berhasil dihapus")
        loadData()
      })
      .catch((err) => {
        toast.error("Data Produk Master gagal dihapus")
      })
  }

  const updateStatus = (record) => {
    axios
      .post(`/api/product-management/product/status/${record.id}`, record)
      .then((res) => {
        toast.success("Status berhasil diupdate")
        loadData()
      })
      .catch((err) => {
        toast.error("Status gagal Di update")
      })
  }

  const show = !inArray(getItem("role"), [
    "adminsales",
    "leadwh",
    "leadsales",
    "leadcs",
    "warehouse",
    "adminwarehouse",
  ])

  const rightContent = (
    <div className="flex justify-between items-center">
      <FilterModal handleOk={handleFilter} />
      <button
        className="ml-3 text-white bg-green-800 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mr-2"
        // onClick={() => handleExportContent()}
        onClick={() => (loadingExport ? null : handleExportContent())}
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
    <Layout rightContent={rightContent} title="List Produk Master">
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
                    setSelectedWarehouse(item)
                    loadData(`/api/product-management/product`, 10, {
                      warehouse_id: item.id,
                    })
                  }}
                >
                  <div
                    key={index}
                    className={`
                  card w-96
                  bg-gradient-to-r from-white via-white ${
                    selectedWarehouse?.id === item.id ? "to-blue-500/20" : ""
                  }
                  hover:to-blue-500/20
                `}
                  >
                    <div className="p-3 border-b flex justify-between">
                      <div className="flex items-center">
                        <strong
                          className={`text-base font-semibold text-${
                            selectedWarehouse?.id === item.id
                              ? "blue-500"
                              : "black"
                          }`}
                        >
                          {item.name}
                        </strong>
                      </div>
                    </div>
                    <div className="card-body">
                      <strong
                        className={`text-${
                          selectedWarehouse?.id === item.id
                            ? "blue-500"
                            : "black"
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

              <div>
                <strong className="text-red-400">Total Data: {total}</strong>
              </div>
            </div>
          </div>

          <Table
            scroll={{ x: "max-content" }}
            tableLayout={"auto"}
            dataSource={datas}
            columns={[
              ...productListColumn,
              {
                title: "Status",
                key: "status",
                dataIndex: "status",
                render: (text, record, index) => {
                  return (
                    <Switch
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
                render: (text, record) => {
                  if (
                    inArray(getItem("role"), ["warehouse", "adminwarehouse"])
                  ) {
                    return (
                      <Dropdown.Button
                        style={{
                          left: -16,
                        }}
                        // icon={<MoreOutlined />}
                        overlay={
                          <Menu itemIcon={<RightOutlined />}>
                            <Menu.Item
                              icon={<FileAddOutlined />}
                              onClick={() =>
                                navigate(`stock-allocation/${text.id}`)
                              }
                              disabled={record.final_stock < 1}
                            >
                              Alokasi Stock
                            </Menu.Item>
                          </Menu>
                        }
                      ></Dropdown.Button>
                    )
                  }
                  if (!show) return null
                  return (
                    <Dropdown.Button
                      style={{
                        left: -16,
                      }}
                      // icon={<MoreOutlined />}
                      overlay={
                        <Menu itemIcon={<RightOutlined />}>
                          {/* <Menu.Item
                            icon={<FileAddOutlined />}
                            onClick={() =>
                              navigate(`stock-allocation/${text.id}`)
                            }
                            disabled={record.final_stock < 1}
                          >
                            Alokasi Stock
                          </Menu.Item> */}
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
          />
        </div>
      </div>
    </Layout>
  )
}

export default ProductMasterList
