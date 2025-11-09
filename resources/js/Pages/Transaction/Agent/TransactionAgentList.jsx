import {
  CloseCircleFilled,
  CloseOutlined,
  EyeOutlined,
  FileExcelOutlined,
  LoadingOutlined,
  PrinterTwoTone,
  SearchOutlined,
} from "@ant-design/icons"
import {
  Button,
  Dropdown,
  Input,
  Menu,
  Pagination,
  Switch,
  Table,
  Tooltip,
} from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { useNavigate } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../../components/layout"
import ModalCancelOrder from "../../../components/Modal/Transaction/ModalCancelOrder"
import {
  createQueryString,
  formatNumber,
  getItem,
  inArray,
} from "../../../helpers"
import ModalTax from "../../Genie/Components/ModalTax"
import AddTransaction from "./Components/AddTransaction"
import FilterModal from "./Components/FilterModal"
import { transactionListColumn, transactionMealPlanListColumn } from "./config"
import { useGetSalesOrderQuery } from "../../../configs/Redux/Services/salesOrderService"

const TransactionAgentList = ({ type = "general", stage = 0, status = 0 }) => {
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

  const [selectedRowKeys, setSelectedRowKeys] = useState([])
  const [loadingSubmit, setLoadingSubmit] = useState(false)
  const [selectedProducts, setSelectedProducts] = useState([])

  const [paramUrl, setParamUrl] = useState("/api/sales-order")
  const {
    data: salesOrderData,
    isLoading: salesOrderLoading,
    isFetching: salesOrderFetching,
    refetch: salesOrderRefetch,
  } = useGetSalesOrderQuery(paramUrl)

  const loadData = (
    url = "/api/sales-order",
    perpage = perPage,
    params = { page: currentPage }
  ) => {
    const bodyData = {
      perpage,
      type: "agent",
      account_id: parseInt(getItem("account_id")),
      status: status > 0 ? status : 10,
      ...params,
    }
    if (params?.contact) {
      bodyData.contact = params?.contact?.value
    }

    if (params?.sales) {
      bodyData.sales = params?.sales?.value
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

  useEffect(() => {
    loadData()
  }, [stage])

  const handleChange = (page, pageSize = 10) => {
    setCurrentPage(page)
    loadData(`/api/transaction/list/?page=${page}`, pageSize, {
      search,
      page,
      ...filterData,
    })
  }

  const handleChangeSearch = () => {
    setIsSearch(true)
    loadData(`/api/transaction/list`, 10, { search })
  }

  const handleFilter = (data) => {
    setFilterData(data)
    loadData(`/api/transaction/list`, 10, data)
  }

  // selected row handler
  const rowSelection = {
    selectedRowKeys,
    onChange: (newSelectedRowKeys) => setSelectedRowKeys(newSelectedRowKeys),
    getCheckboxProps: () => ({
      disabled: false, // Column configuration not to be checked
    }),
  }

  const handleAction = (url) => {
    setLoadingSubmit(true)
    axios
      .post(url, { transaction_id: selectedRowKeys, type, stage })
      .then((res) => {
        const { data, message } = res.data
        setLoadingSubmit(false)
        toast.success(message)
        loadData()

        data.forEach((element) => {
          window.open(element)
        })
      })
      .catch((e) => {
        const { message } = e.response.data
        setLoadingSubmit(false)
        toast.error(message)
      })
  }

  const handleChangeProduct = (e, index, field) => {
    if (field === "products") {
      setSelectedProducts(e)
    } else {
      const data = [...selectedProducts]
      // old mechanism (select each index for loc_node change)
      // data[index].loc_node = e

      // new mechanism (mapping all index for loc_node change)
      data.map((item, i) => (data[i][field] = e))
      setSelectedProducts(data)
    }
  }

  const handleSubmitGp = (value) => {
    setLoadingSubmit(true)
    const hasLocNode = selectedProducts.every((item) => item.loc_node)
    if (!hasLocNode) {
      toast.error("Lokasi Site ID harus diisi")
      return setLoadingSubmit(false)
    }
    axios
      .post(`/api/order/order-lead/submit`, {
        ids: selectedRowKeys,
        type: "transaction-agent",
        ...value,
        products: selectedProducts,
      })
      .then((res) => {
        const { data } = res.data
        toast.success("Data order berhasil di submit!")
        setSelectedRowKeys([])
        setSelectedProducts([])
        setLoadingSubmit(false)
        loadData()
      })
      .catch((e) => {
        setLoadingSubmit(false)
        toast.error("Error submitting order lead")
      })
  }

  const disabled = selectedRowKeys.length < 1
  const rightContent = (
    <div className="flex justify-between items-center">
      {inArray(stage, ["delivered"]) && (
        <Dropdown.Button
          style={{ borderRadius: 10, width: 80 }}
          label="More Action"
          trigger="click"
          className="rounded-lg"
          loading={loadingSubmit}
          overlay={
            <Menu>
              <Menu.Item className="flex justify-between items-center">
                <ModalTax
                  handleSubmit={(e) => handleSubmitGp(e)}
                  // products={selectedProducts}
                  onChange={handleChangeProduct}
                  type="so"
                  titleModal={"Konfirmasi Submit"}
                  title="Submit SI to GP"
                  orderIds={selectedRowKeys}
                />
              </Menu.Item>
            </Menu>
          }
        ></Dropdown.Button>
      )}

      <button
        className="text-white bg-green-800 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mr-2"
        onClick={() => handleNewExportContent()} // old scheme for export data
      >
        {loadingExport ? <LoadingOutlined /> : <FileExcelOutlined />}
        <span className="ml-2">Export</span>
      </button>

      <FilterModal handleOk={handleFilter} showCreatedBy={type == "telmart"} />
      {inArray(stage, ["new-order"]) && (
        <AddTransaction refetch={() => salesOrderRefetch()} />
      )}
    </div>
  )

  const updateStatus = (data) => {
    axios
      .post("/api/transaction/new-order/status", data)
      .then((res) => {
        const { message } = res.data
        toast.success(message)
        loadData()
      })
      .catch((e) => {
        const { message } = e.response.data
        toast.error(message)
      })
  }

  const handleNewExportContent = () => {
    setLoadingExport(true)
    axios
      // .post(`/api/order-manual/export/`)
      .post("/api/transaction/export", { ...filterData, type, stage }) // waiting new endpoint from mbak henny
      .then((res) => {
        const { data } = res.data
        setLoadingExport(false)
        window.open(data)
      })
      .catch((e) => {
        setLoadingExport(false)
      })
  }

  const toogleStatus = []
  if (stage === "cancelled") {
    toogleStatus.push({
      title: "Alasan Pembatalan",
      key: "note",
      dataIndex: "note",
      render: (text, record) => {
        if (record.final_status == "Dibatalkan User") {
          return text
        }
        return "-"
      },
    })
  }
  if (stage === "new-order") {
    toogleStatus.push({
      title: "Status",
      key: "status_link",
      dataIndex: "status_link",
      render: (text, record) => {
        return (
          <Switch
            checked={text > 0}
            onChange={(e) =>
              updateStatus({
                id_transaksi: record.id,
                status_link: e ? "1" : "0",
              })
            }
          />
        )
      },
    })
  }
  const extraColumn = []
  if (stage === "delivered") {
    toogleStatus.push(
      {
        title: "Deduction",
        key: "deduction",
        dataIndex: "deduction",
        render: (value) => `Rp ${formatNumber(value)}`,
      },
      {
        title: "Administrasi Midtrans",
        key: "admin_fee",
        dataIndex: "admin_fee",
      },
      {
        title: "Total Uang Masuk",
        key: "total",
        dataIndex: "total",
        render: (value) => `Rp ${formatNumber(value)}`,
      }
    )
  }
  const additionalColumn = [
    {
      title: "No.",
      dataIndex: "number",
      key: "number",
    },
  ]
  // if (inArray(stage, ["on-delivery", "delivered"])) {
  //   additionalColumn.push(
  //     {
  //       title: "Gp Number",
  //       key: "gp_submit_number",
  //       dataIndex: "gp_submit_number",
  //       render: (value) => value || "-",
  //     },
  //     {
  //       title: "SI Number",
  //       key: "invoice_number",
  //       dataIndex: "invoice_number",
  //       render: (value) => value || "-",
  //     }
  //   )
  // }
  console.log(
    salesOrderData?.data?.data || [],
    "salesOrderData?.data?.data || []"
  )
  const columns =
    stage === "new-order"
      ? transactionMealPlanListColumn
      : transactionListColumn

  return (
    <Layout
      rightContent={rightContent}
      title={`List Transaction ${stage.replace("-", " ")}`}
      onClick={() => navigate(-1)}
    >
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
            rowSelection={rowSelection}
            scroll={{ x: "max-content" }}
            tableLayout={"auto"}
            // dataSource={[1]}
            dataSource={salesOrderData?.data?.data || []}
            columns={[
              ...additionalColumn,
              ...columns,
              // ...toogleStatus,
              ...extraColumn,
              {
                title: "Action",
                key: "id",
                align: "center",
                fixed: "right",
                width: 100,
                render: (text, record) => {
                  return (
                    <>
                      <div className="flex flex-row items-center justify-between gap-4">
                        {/* <Dropdown.Button
                          style={{
                            left: -16,
                          }}
                          // icon={<MoreOutlined />}
                          overlay={
                            <Menu itemIcon={<RightOutlined />}>
                              <Menu.Item
                                icon={<EyeOutlined />}
                                onClick={() => {
                                  const params =
                                    type === "agent" ? "detail/agent" : "detail"
                                  const path =
                                    stage === "new-order"
                                      ? "detail/new-order"
                                      : params
                                  navigate(`/transaction/${path}/${text.id}`)
                                }}
                              >
                                Detail
                              </Menu.Item>
                              {record.awb_status == 0 && (
                                <Menu.Item
                                  icon={<BoxPlotFilled />}
                                  onClick={() =>
                                    handleCreateOrderPopaket(record.id)
                                  }
                                >
                                  Kirim Order Popaket
                                </Menu.Item>
                              )}
                              {stage === "new-order" && (
                                <Menu.Item
                                  icon={<LinkOutlined />}
                                  onClick={() => {
                                    // copy link
                                    const url = record?.transaction_url
                                    navigator.clipboard.writeText(url)
                                    toast.success("Link berhasil disalin")
                                  }}
                                >
                                  Copy Link
                                </Menu.Item>
                              )}
                              {stage === "waiting-payment" && (
                                <ModalCancelOrder
                                  transactions_id={[record.id]}
                                  type={type}
                                  refetch={() => loadData()}
                                >
                                  <Menu.Item icon={<ClockCircleOutlined />}>
                                    <span>Batalkan</span>
                                  </Menu.Item>
                                </ModalCancelOrder>
                              )}
                              {stage === "new-order" && (
                                <Popconfirm
                                  title="Batalkan Transaksi ini?"
                                  onConfirm={() =>
                                    updateStatus({
                                      id_transaksi: record.id,
                                      status_link: 0,
                                    })
                                  }
                                  // onCancel={cancel}
                                  okText="Ya, Batal"
                                  cancelText="Tidak"
                                >
                                  <Menu.Item
                                    color="red"
                                    icon={<CloseOutlined />}
                                  >
                                    Batalkan
                                  </Menu.Item>
                                </Popconfirm>
                              )}
                            </Menu>
                          }
                        ></Dropdown.Button> */}

                        <Tooltip title="Detail">
                          <Button
                            onClick={() => {
                              navigate(`/transaction-agent/detail/${text.id}`)
                            }}
                            icon={<EyeOutlined />}
                          ></Button>
                        </Tooltip>

                        {/* {record.awb_status == 0 && (
                          <Tooltip title="Kirim Order Popaket">
                            <Button
                              onClick={() =>
                                handleCreateOrderPopaket(record.id)
                              }
                              icon={<BoxPlotFilled />}
                            />
                          </Tooltip>
                        )} */}

                        {/* {stage === "new-order" && (
                          <Tooltip title="Copy Link">
                            <Button
                              onClick={() => {
                                // copy link
                                const url = record?.transaction_url
                                navigator.clipboard.writeText(url)
                                toast.success("Link berhasil disalin")
                              }}
                              icon={<LinkOutlined />}
                            ></Button>
                          </Tooltip>
                        )} */}

                        {/* {inArray(stage, ["new-order", "waiting-payment"]) && (
                          <ModalCancelOrder
                            transactions_id={record.id}
                            refetch={() => loadData()}
                          >
                            <Tooltip title="Batalkan Transaksi">
                              <Button icon={<CloseOutlined />}></Button>
                            </Tooltip>
                          </ModalCancelOrder>
                        )} */}
                      </div>
                    </>
                  )
                },
              },
            ]}
            loading={salesOrderLoading || salesOrderFetching}
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
      </div>
    </Layout>
  )
}

export default TransactionAgentList
