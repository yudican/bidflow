import {
  CloseCircleFilled,
  CloseOutlined,
  DownOutlined,
  EditOutlined,
  EyeOutlined,
  FileExcelOutlined,
  LoadingOutlined,
  PlusOutlined,
  RightOutlined,
  SearchOutlined,
} from "@ant-design/icons"
import { Dropdown, Input, Menu, Pagination, Popconfirm, Table } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import "react-circular-progressbar/dist/styles.css"
import { useNavigate } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../components/layout"
import { getItem, inArray, paginateData } from "../../helpers"
import ModalTax from "../Genie/Components/ModalTax"
import FilterModal from "./Components/FilterModal"
import { purchaseOrderListColumn } from "./config"

const PurchaseInvoiceEntry = () => {
  // hooks
  const navigate = useNavigate()
  // state
  const [loading, setLoading] = useState(false)
  const [purchaseOrderList, setPurchaseOrderList] = useState([])
  const [searchPurchaseOrderList, setSearchPurchaseOrderList] = useState(null)
  const [total, setTotal] = useState(0)
  const [search, setSearch] = useState("")
  const [isSearch, setIsSearch] = useState(false)
  const [currentPage, setCurrentPage] = useState(1)
  const [perPage, setPerpage] = useState(10)
  const [dashboardList, setDashboardList] = useState([])
  const [selectedDashboard, setSelectedDashboard] = useState(null)
  const [filterData, setFilterData] = useState({})
  const [loadingExport, setLoadingExport] = useState(false)
  const [readyToSubmit, setReadyToSubmit] = useState(false)
  const [loadingSubmit, setLoadingSubmit] = useState(false)
  const [selectedRowKeys, setSelectedRowKeys] = useState([])
  const [selectedProducts, setSelectedProducts] = useState([])
  // console.log(selectedProducts, "selected products")
  const [typeInvoice, seTypeInvoice] = useState(null)

  // selected row handler
  const onSelectChange = (newSelectedRowKeys) => {
    return setSelectedRowKeys(newSelectedRowKeys)
  }

  // multiple checkbox
  const rowSelection = {
    selectedRowKeys,
    onChange: (e) => {
      setSelectedRowKeys(e)
      const productData = []
      if (e.length > 0) {
        e.map((value) => {
          const item = purchaseOrderList.find((item) => item.id == value)
          if (item) {
            seTypeInvoice(item.type_invoice)
            const products = item?.billings.map((row, index) => {
              return {
                key: index,
                item_id: row.id,
                entry_id: item.id,
                product_name: row.product_name,
                received_number: row.received_number,
                status: row.status,
                status_gp: row.status_gp,
                type_invoice: item.type_invoice,
                gp_payable_number: item.gp_payable_number,
                nama_bank: row.nama_bank,
                nama_pengirim: row.nama_pengirim,
                no_rekening: row.no_rekening,
                jumlah_transfer: row.jumlah_transfer,
                tax_amount: row.tax_amount,
                created_at: row.created_at,
                created_by_name: row.created_by_name,
                bukti_transfer_url: row.bukti_transfer_url,
                approved_by_name: row.approved_by_name,
              }
            })
            productData.push(...products)
          }
        })
      } else {
        seTypeInvoice(null)
      }

      return setSelectedProducts(productData)
    },
    getCheckboxProps: (record) => {
      if (typeInvoice && record.type_invoice !== typeInvoice) {
        return {
          disabled: true,
        }
      }

      if (record.status == "New") {
        return {
          disabled: true,
        }
      }

      if (record.status_submit === "submited") {
        return {
          disabled: true,
        }
      }

      return {
        disabled: false,
      }
    },
  }

  const SearchResult = () => {
    return purchaseOrderList.filter((value) => value.po_number.includes(search))
  }

  const loadData = (
    url = "/api/purchase/invoice-entry",
    perpage = perPage,
    params = { page: currentPage }
  ) => {
    setLoading(true)
    axios
      .post(url, { perpage, account_id: getItem("account_id"), ...params })
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

        // sort on process status first
        data
          .sort((a, b) => {
            if (a.status == "2") {
              return a.status - b.status
            }

            if (a.status == "7") {
              return 2
            }

            return -1
          })
          .sort((a, b) => {
            if (a.status == "5") {
              return -1
            }
          })

        const newData = data.map((item, index) => {
          const number = paginatedNumbers[index] // overriding response to set paginated number per pages

          return {
            ...item,
            number,
          }
        })

        setPurchaseOrderList(
          newData.sort((a, b) => {
            if (a.status == "2") {
              return a - b
            }
            return -1
          })
        )
        setLoading(false)
      })
  }

  const getDashboardList = () => {
    axios
      .get(`/api/purchase/invoice-entry/dashboard/status`)
      .then((res) => {
        setDashboardList(res?.data?.data)
      })
      .catch((err) => {})
  }

  useEffect(() => {
    loadData()
    getDashboardList()
  }, [])

  const handleChange = (page, pageSize = 10) => {
    setCurrentPage(page)
    loadData(`/api/purchase/invoice-entry/?page=${page}`, pageSize, {
      search,
      page,
      ...filterData,
    })
  }

  const handleChangeSearch = () => {
    setIsSearch(true)
    loadData(`/api/purchase/invoice-entry`, 10, { search })
  }

  const handleFilter = (data) => {
    setFilterData(data)
    loadData(`/api/purchase/invoice-entry`, 10, data)
  }

  const handleExportContent = () => {
    setLoadingExport(true)
    axios
      .post(`/api/purchase/invoice-entry/export`, { search, ...filterData })
      .then((res) => {
        const { data } = res.data
        window.open(data)
        setLoadingExport(false)
      })
      .catch((e) => setLoadingExport(false))
  }

  const handleCancel = (purchase_invoice_entry_id) => {
    axios
      .post(`/api/purchase/invoice-entry/cancel`, { purchase_invoice_entry_id })
      .then((res) => {
        toast.success("Invoice Entry berhasil dibatalkan")
        loadData()
      })
      .catch((err) => {
        toast.error("Invoice Entry gagal dibatalkan")
      })
  }

  const handleSubmitGP = (value) => {
    if (selectedRowKeys && selectedRowKeys.length < 1) {
      return toast.error("Mohon Pilih Salah Satu", {
        position: toast.POSITION.TOP_RIGHT,
      })
    }
    setLoadingSubmit(true)
    axios
      .post(`/api/purchase/invoice-entry/submit-gp`, {
        items: selectedRowKeys,
      })
      .then((res) => {
        console.log(res)
        if (res.data.status == "failed") {
          toast.error(res.data.message)
        } else {
          toast.success("Invoice Entry berhasil di submit ke GP")
        }

        setReadyToSubmit(false)
        setSelectedRowKeys([])
        setLoadingSubmit(false)
        loadData()
      })
      .catch((err) => {
        setLoadingSubmit(false)
        toast.error("Invoice Entry gagal di submit ke GP")
      })
  }

  const handleSubmitPaymentGP = (value) => {
    if (selectedRowKeys && selectedRowKeys.length < 1) {
      return toast.error("Mohon Pilih Salah Satu", {
        position: toast.POSITION.TOP_RIGHT,
      })
    }
    setLoadingSubmit(true)
    axios
      .post(`/api/purchase/payment-entry/submit-gp`, {
        items: selectedRowKeys,
        billingIds: selectedProducts.map((item) => item.item_id),
      })
      .then((res) => {
        toast.success("Payment Entry berhasil di submit ke GP")
        setReadyToSubmit(false)
        setSelectedRowKeys([])
        setLoadingSubmit(false)
        loadData()
      })
      .catch((err) => {
        setLoadingSubmit(false)
        toast.error("Payment Entry gagal di submit ke GP")
      })
  }

  const listActions = [
    {
      title: "Action",
      key: "id",
      align: "center",
      fixed: "right",
      width: 100,
      render: (text, record) => {
        return (
          <Dropdown.Button
            style={{
              left: -16,
            }}
            overlay={
              <Menu itemIcon={<RightOutlined />}>
                {inArray(record.status, ["-1", "-"]) && (
                  <Menu.Item
                    icon={<EditOutlined />}
                    onClick={() => navigate(`form/${text.id}`)}
                  >
                    Ubah
                  </Menu.Item>
                )}

                <Menu.Item
                  icon={<EyeOutlined />}
                  onClick={() => navigate(`detail/${text.id}`)}
                >
                  Detail
                </Menu.Item>

                {inArray(record.status, ["0", "-1", "-"]) && (
                  <Popconfirm
                    title="Apakah anda yakin ingin membatalkan Invoice ini?"
                    onConfirm={() => handleCancel(text.id)}
                    // onCancel={cancel}
                    okText="Ya, Batalkan"
                    cancelText="Batal"
                  >
                    <Menu.Item icon={<CloseOutlined />}>
                      <span>Cancel</span>
                    </Menu.Item>
                  </Popconfirm>
                )}
              </Menu>
            }
          ></Dropdown.Button>
        )
      },
    },
  ]
  console.log(selectedProducts, "selectedProducts")
  const role = getItem("role")
  const canCreate = inArray(role, [
    "finance",
    "admin",
    "superadmin",
    "adminsales",
  ])

  const menu = (
    <Menu>
      <Menu.Item>
        <a onClick={handleSubmitGP}>
          <span className="">
            {typeInvoice === "jasa"
              ? "Submit Payable to GP"
              : "Submit Invoice Entry to GP"}
          </span>
        </a>
      </Menu.Item>
      <Menu.Item>
        <ModalTax
          handleSubmit={(e) => handleSubmitPaymentGP(e)}
          products={selectedProducts.filter(
            (item) => !item.status_gp && item.status == 1
          )}
          // products={selectedProducts}
          type={"payment"}
          title={"Submit Payment Entry to GP"}
        />
      </Menu.Item>
      <Menu.Item>
        <a onClick={() => handleExportContent()}>
          {loadingExport ? <LoadingOutlined /> : <FileExcelOutlined />}
          <span className="ml-2">Export</span>
        </a>
      </Menu.Item>
    </Menu>
  )

  const rightContent = (
    <div className="flex justify-between items-center">
      {/* <button
        className="ml-3 text-white bg-green-800 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mr-2"
        onClick={() => handleExportContent()}
      >
        {loadingExport ? <LoadingOutlined /> : <FileExcelOutlined />}
        <span className="ml-2">Export</span>
      </button>
      <button
        className="text-white bg-mainColor hover:bg-mainColor/90 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mr-4"
        onClick={handleSubmitGP}
      >
        <span className="">Submit to GP</span>
      </button> */}
      <FilterModal handleOk={handleFilter} />
      <Dropdown overlay={menu}>
        <button
          className="text-blue-700 border-[1px] border-blue-700 hover:bg-blue-800/10 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mx-2"
          onClick={(e) => e.preventDefault()}
        >
          <span className="mr-2">More Option</span>
          <DownOutlined />
        </button>
      </Dropdown>

      {canCreate && (
        <button
          onClick={() => navigate("form")}
          className="text-white bg-blue-700 hover:bg-blue-700/90 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-3"
        >
          <PlusOutlined />
          <span className="ml-2">Tambah Data</span>
        </button>
      )}
    </div>
  )

  const columns = purchaseOrderListColumn
  return (
    <Layout title="List Invoice Entry" rightContent={rightContent}>
      <div className="card">
        <div className="card-body">
          <div className="row mb-4">
            <div className="flex overflow-y-auto mb-4">
              {dashboardList?.map((item, index) => (
                <div
                  key={index}
                  className="cursor-pointer mr-4"
                  onClick={() => {
                    const params = { ...filterData, status: item.value }
                    setSelectedDashboard(item.value)
                    loadData(`/api/purchase/invoice-entry`, 10, {
                      search,
                      ...params,
                    })
                  }}
                >
                  <div
                    key={index}
                    className={`
                  card w-96
                  bg-gradient-to-r from-white via-white ${
                    selectedDashboard === item.value ? "to-blue-500/20" : ""
                  }
                  hover:to-blue-500/20
                `}
                  >
                    <div className="p-3 border-b flex justify-between">
                      <div className="flex items-center">
                        <strong
                          className={`text-base font-semibold text-${
                            selectedDashboard === item.value
                              ? "blue-500"
                              : "black"
                          }`}
                        >
                          {item.label}
                        </strong>
                      </div>
                    </div>
                    <div className="card-body">
                      <strong
                        className={`text-${
                          selectedDashboard === item.value
                            ? "blue-500"
                            : "black"
                        } text-lg font-medium`}
                      >
                        Total Order: {item.count}
                      </strong>
                    </div>
                  </div>
                </div>
              ))}
            </div>
            <div className="col-md-4 col-sm-6 col-12">
              <Input
                placeholder="Cari disini"
                size={"large"}
                className="rounded"
                onPressEnter={(e) => {
                  // handleChangeSearch()
                  setSearchPurchaseOrderList(SearchResult())
                }}
                suffix={
                  isSearch ? (
                    <CloseCircleFilled
                      onClick={() => {
                        // loadData()
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
              <strong className="float-right text-blue-400">
                Total Data: {total}
              </strong>
            </div>
          </div>
          <Table
            rowSelection={rowSelection}
            dataSource={searchPurchaseOrderList || purchaseOrderList}
            columns={[...columns, ...listActions]}
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

export default PurchaseInvoiceEntry
