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
import { useLocation, useNavigate } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../../components/layout"
import ModalCancelOrder from "../../../components/Modal/Transaction/ModalCancelOrder"
import { formatNumber, getStatusTransaction, inArray } from "../../../helpers"
import ModalTax from "../../Genie/Components/ModalTax"
import AddTransaction from "./Components/AddTransaction"
import FilterModal from "./Components/FilterModal"
import { transactionListColumn, transactionMealPlanListColumn } from "./config"
import ModalTrackOrder from "../../../components/Modal/Transaction/ModalTrackOrder"
import * as XLSX from "xlsx"
import dayjs from "dayjs"

const TransactionList = ({
  type = "general",
  stage = null,
  contact = false,
}) => {
  console.log(stage, "stage")
  const navigate = useNavigate()
  const location = useLocation()
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
  const loadData = (
    url = "/api/transaction/list",
    perpage = perPage,
    params = { page: currentPage }
  ) => {
    setLoading(true)
    axios
      .post(url, { perpage, ...params, type, stage })
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

  const handleNewExportContent = () => {
    if (!datas || datas.length === 0) {
      toast.error("Tidak ada data untuk di-export")
      return
    }

    setLoadingExport(true)

    try {
      console.log("=== DATA EXPORT RAW ===");
      console.log(datas);
      const exportData = datas.map((item, index) => ({
        No: index + 1,
        "TRX ID": item.id_transaksi,
        "Metode Pembayaran": item.data_payment_method?.bank_name,
        "Metode Pengiriman": item.shipping_type_name,
        "Nama Customer": item.user_name,
        Status: item.final_status,
        "Tanggal Transaksi": item.created_at,
        "Nama Produk": item.transaction_detail?.[0]?.product_name || "-",
        SKU: item.transaction_detail?.[0]?.data_product.product_sku || "-",
        "Harga Satuan": item.transaction_detail?.[0]?.price || "-",
        QTY: item.transaction_detail?.[0]?.qty || 0,
        Subtotal: item.transaction_detail?.[0]?.subtotal || "-",
      }))

      const worksheet = XLSX.utils.json_to_sheet(exportData)
      const workbook = XLSX.utils.book_new()
      XLSX.utils.book_append_sheet(workbook, worksheet, "Transaksi")

      const currentDate = dayjs().format("DD-MM-YYYY")
      XLSX.writeFile(workbook, `Transaksi-LMS-${currentDate}.xlsx`)

      setLoadingExport(false)
    } catch (e) {
      toast.error("Gagal export data")
      setLoadingExport(false)
    }
  }

  // selected row handler
  const rowSelection = {
    selectedRowKeys,
    onChange: (newSelectedRowKeys) => setSelectedRowKeys(newSelectedRowKeys),
    getCheckboxProps: () => ({
      disabled: false, // Column configuration not to be checked
    }),
  }

  useEffect(() => {
    setSelectedRowKeys([]) // Reset selected rows when transaction stage changes
  }, [stage])

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
      .finally(() => {
        setLoadingSubmit(false)
        setSelectedRowKeys([])
      })
  }

  const handleChangeProduct = (e, index, field) => {
    const data = [...datas]
    data.map((item, i) => (data[i][field] = e))
    setDatas(data)
  }

  const handleSubmitGp = (value) => {
    setLoadingSubmit(true)
    const newData = datas.filter((item) => selectedRowKeys.includes(item.id))
    const hasLocNode = newData.every((item) => item.loc_node)
    if (!hasLocNode) {
      toast.error("Lokasi Site ID harus diisi")
      return setLoadingSubmit(false)
    }
    axios
      .post(`/api/transaction/gp/submit`, {
        ids: selectedRowKeys,
        type,
        ...value,
        products: newData.map((item) => ({
          id: item.id,
          loc_node: item.loc_node,
        })),
      })
      .then((res) => {
        const { data } = res.data
        toast.success("Data order berhasil di submit!")
        setSelectedRowKeys([])
        setLoadingSubmit(false)
        loadData()
      })
      .catch((e) => {
        setLoadingSubmit(false)
        toast.error("Kesalahan saat submit GP")
      })
  }

  const disabled = selectedRowKeys.length < 1
  const rightContent = (
    <div className="flex justify-between items-center">
      <Dropdown.Button
        style={{ borderRadius: 10, width: 80 }}
        icon={<PrinterTwoTone />}
        disabled={disabled}
        trigger="click"
        className="rounded-lg"
        loading={loadingSubmit}
        overlay={
          <Menu>
            {inArray(stage, ["on-process", "ready-to-ship"]) && (
              <>
                {/* <Menu.Item className="flex justify-between items-center">
                  <PrinterTwoTone />{" "}
                  <a
                    href={"#"}
                    onClick={(e) => {
                      e.preventDefault()

                      handleAction("/api/transaction/bulk/print/label")
                    }}
                  >
                    <span>Cetak Label</span>
                  </a>
                </Menu.Item> */}
                <Menu.Item className="flex justify-between items-center">
                  <PrinterTwoTone />{" "}
                  <a
                    href={"#"}
                    onClick={(e) => {
                      e.preventDefault()
                      const labels = datas
                        .filter((item) => inArray(item.id, selectedRowKeys))
                        .map((item) => item.id_transaksi)
                      window.open("/print/label/" + labels.join(","))
                    }}
                  >
                    <span>Cetak Label</span>
                  </a>
                </Menu.Item>
              </>
            )}

            <Menu.Item className="flex justify-between items-center">
              <PrinterTwoTone />{" "}
              <a
                href={"#"}
                onClick={(e) => {
                  e.preventDefault()
                  window.open("/invoice/" + selectedRowKeys.join(","))
                  // handleAction("/api/transaction/bulk/print/invoice")
                }}
              >
                <span>Cetak Invoice</span>
              </a>
            </Menu.Item>
          </Menu>
        }
      ></Dropdown.Button>
      {inArray(stage, ["on-delivery", "delivered"]) && (
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
                  products={datas.filter((item) =>
                    selectedRowKeys.includes(item.id)
                  )}
                  onChange={handleChangeProduct}
                  type="telmark"
                  titleModal={"Konfirmasi Submit"}
                  title="Submit GP"
                />
              </Menu.Item>
            </Menu>
          }
        ></Dropdown.Button>
      )}

      {inArray(stage, ["on-process"]) && (
        <button
          onClick={() => {
            if (disabled) {
              return null
            }

            return handleAction("/api/transaction/bulk/ready-to-ship")
          }}
          className={`text-white bg-${disabled ? "gray" : "blue"
            }-700 hover:bg-${disabled ? "gray" : "blue"
            }-800 focus:ring-4 focus:outline-none focus:ring-${disabled ? "gray" : "blue"
            }-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mr-2`}
          disabled={disabled}
        >
          {loadingSubmit ? <LoadingOutlined /> : <span>Siap Dikirim</span>}
        </button>
      )}

      {inArray(stage, ["confirm-payment"]) && (
        <button
          onClick={() => {
            if (disabled) {
              return null
            }

            return handleCreateOrderPopaket(selectedRowKeys)
          }}
          className={`text-white bg-${disabled ? "gray" : "blue"
            }-700 hover:bg-${disabled ? "gray" : "blue"
            }-800 focus:ring-4 focus:outline-none focus:ring-${disabled ? "gray" : "blue"
            }-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mr-2 w-auto`}
          disabled={disabled}
        >
          {loadingSubmit ? (
            <LoadingOutlined />
          ) : (
            <span>Assign to Warehouse</span>
          )}
        </button>
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
        <AddTransaction refetch={() => loadData()} />
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

  const handleCreateOrderPopaket = (transaction_id) => {
    setLoadingSubmit(true)
    axios
      .post(
        "https://testingapi.daftar-agen.com/api/transaction/popaket/create-order",
        { transaction_id },
        {
          headers: {
            Authorization: "Bearer 4a6a6c7f2cdd12a826e2f15675a6c6ac",
          },
        }
      )
      .then((res) => {
        const { message } = res.data
        toast.success(message)
        setLoadingSubmit(false)
        handleAction("/api/transaction/bulk/asign-to-warehouse")
      })
      .catch((e) => {
        const { message } = e.response.data
        toast.error(message)
        setLoadingSubmit(false)
      })
  }

  // const handleNewExportContent = () => {
  //   setLoadingExport(true)
  //   axios
  //     // .post(`/api/order-manual/export/`)
  //     .post("/api/transaction/export", { ...filterData, type, stage }) // waiting new endpoint from mbak henny
  //     .then((res) => {
  //       const { data } = res.data
  //       setLoadingExport(false)
  //       window.open(data)
  //     })
  //     .catch((e) => {
  //       setLoadingExport(false)
  //     })
  // }

  const handleAwbPopaket = (transaction_id) => {
    axios
      .post(
        "https://testingapi.daftar-agen.com/api/transaction/popaket/get-awb",
        { transaction_id },
        {
          headers: {
            Authorization: "Bearer 4a6a6c7f2cdd12a826e2f15675a6c6ac",
          },
        }
      )
      .then((res) => {
        const { message } = res.data
        toast.success(message)
        loadData()
      })
      .catch((e) => { })
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
        render: (value) => `Rp ${formatNumber(value)}`,
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
  if (inArray(stage, ["on-delivery", "delivered"])) {
    additionalColumn.push(
      {
        title: "GP Number",
        key: "gp_submit_number",
        dataIndex: "gp_submit_number",
        render: (value) => value || "-",
      },
      {
        title: "SI Number",
        key: "invoice_number",
        dataIndex: "invoice_number",
        render: (value) => value || "-",
      }
    )
  }

  const columns =
    stage === "new-order"
      ? transactionMealPlanListColumn
      : transactionListColumn

  return (
    <Layout
      rightContent={rightContent}
      title={`
        ${location.pathname.includes("report") ? "List" : "List Transaksi"} 
        ${getStatusTransaction(
        stage.replace("report-transaction", "report transaksi")
      )}`}
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
            dataSource={datas}
            columns={[
              ...additionalColumn,
              ...columns,
              ...toogleStatus,
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
                              const params =
                                type === "agent" ? "detail/agent" : "detail"
                              const path =
                                stage === "new-order"
                                  ? "detail/new-order"
                                  : params
                              navigate(`/transaction/${path}/${text.id}`)
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

                        {inArray(stage, ["new-order", "waiting-payment"]) && (
                          <ModalCancelOrder
                            transactions_id={record.id}
                            refetch={() => loadData()}
                          >
                            <Tooltip title="Batalkan Transaksi">
                              <Button icon={<CloseOutlined />}></Button>
                            </Tooltip>
                          </ModalCancelOrder>
                        )}

                        {record?.resi && (
                          <ModalTrackOrder
                            resi={record?.resi}
                            order_number={record?.id_transaksi}
                          >
                            <img
                              width="20"
                              height="20"
                              src="https://img.icons8.com/ios/20/truck--v1.png"
                              alt="truck--v1"
                            />
                          </ModalTrackOrder>
                        )}
                      </div>
                    </>
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

export default TransactionList
