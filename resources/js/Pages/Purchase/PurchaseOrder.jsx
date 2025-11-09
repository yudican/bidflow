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
import {
  DatePicker,
  Dropdown,
  Input,
  Menu,
  Modal,
  Pagination,
  Popconfirm,
  Select,
  Table,
} from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import "react-circular-progressbar/dist/styles.css"
import { useNavigate } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../components/layout"
import { formatNumber, getItem, inArray, paginateData } from "../../helpers"
import ModalTax from "../Genie/Components/ModalTax"
import FilterModal from "./Components/FilterModal"
import {
  getOrderStatus,
  purchaseOrderListColumn,
  purchaseOrderWhListColumn,
} from "./config"

const { RangePicker } = DatePicker

const PurchaseOrder = () => {
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

  const [filterData, setFilterData] = useState({})
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [selectedRowKeys, setSelectedRowKeys] = useState([])
  const [readyToSubmit, setReadyToSubmit] = useState(false)
  const [loadingSubmit, setLoadingSubmit] = useState(false)
  const [selectedProducts, setSelectedProducts] = useState([])
  const [filterExport, setFilterExport] = useState({
    status: null,
    created_at: null,
  })
  const [isFilterExportModalOpen, setIsFilterExportModalOpen] = useState(false)
  const [loadingExport, setLoadingExport] = useState(false)

  const SearchResult = () => {
    return purchaseOrderList.filter((value) =>
      (value.vendor_code || value.po_number).includes(search)
    )
  }

  const loadData = (
    url = "/api/purchase/purchase-order",
    perpage = perPage,
    params = { page: currentPage }
  ) => {
    setLoading(true)
    axios
      .post(url, { perpage, account_id: getItem("account_id"), ...params })
      .then((res) => {
        const { data, total, from } = res.data.data
        setTotal(total) // set total of total data products

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
            if (a.status == "1") {
              return -1
            }
          })

        const newData = data.map((item, index) => {
          const number = from + index // overriding response to set paginated number per pages

          return {
            ...item,
            number,
            order: getOrderStatus(item.status),
            created_by: item?.created_by_name,
            total_tax: item?.tax_amount ?? 0,
            total: item.total_amount,
          }
        })

        setPurchaseOrderList(newData.sort((a, b) => a.order - b.order))
        setLoading(false)
      })
  }
  useEffect(() => {
    loadData()
  }, [])

  const handleChange = (page, pageSize = 10) => {
    setCurrentPage(page)
    loadData(`/api/purchase/purchase-order/?page=${page}`, pageSize, {
      search,
      page,
      ...filterData,
    })
  }

  const handleChangeSearch = () => {
    setIsSearch(true)
    loadData(`/api/purchase/purchase-order`, 10, { search })
  }

  const handleFilter = (data) => {
    setFilterData(data)
    loadData(`/api/purchase/purchase-order`, 10, data)
  }

  // const handleExportContent = () => { // old scheme for export data
  //   setLoadingExport(true)
  //   axios
  //     .post(`/api/purchase/purchase-order/export`)
  //     .then((res) => {
  //       const { data } = res.data
  //       window.open(data)
  //       setLoadingExport(false)
  //     })
  //     .catch((e) => setLoadingExport(false))
  // }

  //test update
  const handleChangeExport = (value, field) => {
    if (field === "createdBy") {
      return setFilterExport({ ...filterExport, createdBy: value.value })
    }
    setFilterExport({ ...filterExport, [field]: value })
  }

  const handleNewExportContent = () => {
    setLoadingExport(true)
    axios
      .post(`/api/purchase/purchase-order/export/`, filterExport) // waiting new endpoint from mbak henny
      .then((res) => {
        const { data } = res.data
        setFilterData({})
        setFilterExport({})
        window.open(data)
        setLoadingExport(false)
      })
      .catch((e) => setLoadingExport(false))
  }

  const showModal = () => {
    setIsModalOpen(true)
    form.setFieldsValue({
      vat_value: 0,
      tax_value: 0,
    })
  }

  const handleCancel = (po_id) => {
    axios
      .post(`/api/purchase/purchase-order/cancel/${po_id}`, {
        _method: "DELETE",
      })
      .then((res) => {
        toast.success("Purchase order berhasil dibatalkan")
        loadData()
      })
      .catch((err) => {
        toast.error("Purchase order gagal dibatalkan")
      })
  }

  // selected row handler
  const rowSelection = {
    selectedRowKeys,
    onChange: (e) => {
      setSelectedRowKeys(e)
      const productData = []
      if (e.length > 0) {
        e.map((value) => {
          const item = purchaseOrderList.find((item) => item.id == value)
          // console.log(item, "item")
          if (item) {
            const products = item?.items.map((row, index) => {
              return {
                key: index,
                item_id: row.id,
                po_id: item.id,
                product_name: row.product_name,
                sku: row.sku,
                po_number: item.po_number,
                received_number: row.received_number,
                do_number: row.do_number,
                gp_received_number: row.gp_received_number,
                received_date: row.received_date,
                qty_diterima: row.qty_diterima,
              }
            })
            productData.push(...products)
          }
        })
      }

      return setSelectedProducts(productData)
    },
    getCheckboxProps: (record) => {
      // when status waiting approval || '5' disabled (can't submit)
      if (record.status == "5") {
        return {
          disabled: true,
        }
      }
      if (record.status == "New") {
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

  const handleChangeProduct = (e, index) => {
    const data = [...selectedProducts]
    data[index].loc_node = e
    setSelectedProducts(data)
  }

  const handleSubmitGp = (value) => {
    setLoadingSubmit(true)
    axios
      .post(`/api/po/order/submit`, {
        ids: selectedRowKeys,
        type: "purchase-order",
        ...value,
        products: selectedProducts,
      })
      .then((res) => {
        toast.success("Purchase Order berhasil di submit")
        setReadyToSubmit(false)
        setSelectedRowKeys([])
        setSelectedProducts([])
        setLoadingSubmit(false)
      })
      .catch((e) => {
        const { message } = e.response?.data
        setLoadingSubmit(false)
        toast.error(message || "Error submitting purchase order")
      })
  }

  const handleSubmitReceivingGp = (value) => {
    setLoadingSubmit(true)
    axios
      .post(`/api/receiving/po/submit`, {
        ids: selectedRowKeys,
        type: "receiving-purchase-order",
        ...value,
        products: selectedProducts,
      })
      .then((res) => {
        toast.success("Purchase Order berhasil di submit")
        setReadyToSubmit(false)
        setSelectedRowKeys([])
        setSelectedProducts([])
        setLoadingSubmit(false)
      })
      .catch((e) => {
        setLoadingSubmit(false)
        toast.error("Error submitting purchase order")
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
                {record.status < 1 && (
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

                {inArray(record.status, ["0", "5"]) && (
                  <Popconfirm
                    title="Apakah anda yakin ingin membatalkan PO ini?"
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

  const menu = (
    <Menu>
      <Menu.Item>
        <ModalTax
          handleSubmit={(e) => handleSubmitGp(e)}
          products={selectedProducts}
          onChange={handleChangeProduct}
          type={"po"}
          title={"Submit PO"}
        />
      </Menu.Item>
      <Menu.Item>
        <ModalTax
          handleSubmit={(e) => handleSubmitReceivingGp(e)}
          products={selectedProducts.filter(
            (item) => item.do_number && !item.gp_received_number
          )}
          onChange={handleChangeProduct}
          type={"receiving"}
          title={"Submit Receiving "}
          titleModal={"Pilih data receiving"}
        />
      </Menu.Item>
      <Menu.Item>
        <>
          <a
            // onClick={() => handleExportContent()}
            onClick={() => setIsFilterExportModalOpen(true)}
          >
            {loadingExport ? <LoadingOutlined /> : <FileExcelOutlined />}
            <span className="ml-2">Export</span>
          </a>
          <Modal
            title="Export Config"
            open={isFilterExportModalOpen}
            okText={"Export Data"}
            cancelText={"Batal"}
            onOk={handleNewExportContent}
            confirmLoading={loadingExport}
            onCancel={() => {
              setIsFilterExportModalOpen(false)
            }}
          >
            <div>
              <label htmlFor="">Status</label>
              <Select
                mode="multiple"
                allowClear
                className="w-full mb-2"
                placeholder="Pilih Status"
                onChange={(e) => handleChangeExport(e, "status")}
              >
                <Select.Option value={0}>Draft</Select.Option>
                <Select.Option value={1}>On Process</Select.Option>
                <Select.Option value={2}>Delivery</Select.Option>
                <Select.Option value={3}>Stock Opname</Select.Option>
                <Select.Option value={4}>Delivered</Select.Option>
                <Select.Option value={5}>Waiting Approval</Select.Option>
                <Select.Option value={6}>Rejected</Select.Option>
                <Select.Option value={7}>Complete</Select.Option>
                <Select.Option value={8}>Canceled</Select.Option>
                <Select.Option value={9}>Partial Received</Select.Option>
              </Select>
            </div>

            <div className="mb-2">
              <label htmlFor="">Tanggal</label>
              <RangePicker
                className="w-full"
                format={"YYYY-MM-DD"}
                onChange={(e, dateString) =>
                  handleChangeExport(dateString, "created_at")
                }
              />
            </div>
          </Modal>
        </>
      </Menu.Item>
    </Menu>
  )

  const rightContent = (
    <div className="flex justify-between items-center">
      {/* <ModalTax
        handleSubmit={(e) => handleSubmitGp(e)}
        products={selectedProducts}
        onChange={handleChangeProduct}
      /> */}

      {/* <button
        className="ml-3 text-white bg-green-800 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mr-2"
        onClick={() => handleExportContent()}
      >
        {loadingExport ? <LoadingOutlined /> : <FileExcelOutlined />}
        <span className="ml-2">Export</span>
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

      <button
        onClick={() => navigate("form")}
        className="text-white bg-blue-700 hover:bg-blue-700/90 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-3"
      >
        <PlusOutlined />
        <span className="ml-2">Tambah Data</span>
      </button>
    </div>
  )

  const columns = inArray(getItem("role"), ["warehouse", "adminwarehouse"])
    ? purchaseOrderWhListColumn
    : purchaseOrderListColumn
  return (
    <Layout title="List Purchase Order" rightContent={rightContent}>
      <div className="card">
        <div className="card-body">
          <div className="row mb-4">
            <div className="col-md-12"></div>
            <div className="col-md-4 col-sm-6 col-12">
              <Input
                placeholder="Cari disini"
                size={"large"}
                className="rounded"
                onPressEnter={() => {
                  handleChangeSearch()
                  // setSearchPurchaseOrderList(SearchResult())
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
              <strong className="float-right text-red-400">
                Total Data: {formatNumber(total)}
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

export default PurchaseOrder
