import {
  CloseCircleFilled,
  EditOutlined,
  EyeOutlined,
  PlusOutlined,
  RightOutlined,
  SearchOutlined,
  FileExcelOutlined,
  DownOutlined,
  LoadingOutlined,
  UploadOutlined,
} from "@ant-design/icons"
import {
  Dropdown,
  Input,
  Menu,
  Pagination,
  Table,
  Modal,
  Select,
  DatePicker,
  Form,
  Button,
} from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import "react-circular-progressbar/dist/styles.css"
import { useNavigate } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../components/layout"
import FilterModal from "./Components/FilterModal"
import { purchaseRequisitionListColumn } from "./config"
import { getItem, inArray } from "../../helpers"
import ModalTax from "../Genie/Components/ModalTax"
import ModalBulkPo from "./Components/ModalBulkPo"

const { RangePicker } = DatePicker

const PurchaseRequisition = () => {
  // hooks
  const navigate = useNavigate()
  // state
  const [loading, setLoading] = useState(false)
  const [purchaseOrderList, setPurchaseOrderList] = useState([])
  const [dataPrList, setDataPrList] = useState([])
  const [searchPurchaseOrderList, setSearchPurchaseOrderList] = useState(null)
  const [total, setTotal] = useState(0)
  const [search, setSearch] = useState("")
  const [isSearch, setIsSearch] = useState(false)
  const [currentPage, setCurrentPage] = useState(1)
  const [filterData, setFilterData] = useState({})
  const [filterExport, setFilterExport] = useState({
    status: null,
    created_at: null,
  })
  const [form] = Form.useForm()
  const [isFilter, setIsFilter] = useState(false)
  const [isFilterExportModalOpen, setIsFilterExportModalOpen] = useState(false)
  const [loadingExport, setLoadingExport] = useState(false)
  const [isModalVisible, setIsModalVisible] = useState(false)

  const SearchResult = () => {
    return purchaseOrderList.filter((value) => value.po_number.includes(search))
  }

  const loadData = (
    url = "/api/purchase/purchase-requitition",
    perpage = 10,
    params = { page: currentPage }
  ) => {
    setLoading(true)
    axios.post(url, { perpage, ...params }).then((res) => {
      const { data, total, current_page } = res.data.data
      setTotal(total)
      setCurrentPage(current_page)
      // const newData = data.map((item) => {
      //   return {
      //     ...item,
      //   }
      // })

      // setPurchaseOrderList(
      //   newData.sort((a, b) => {
      //     if (a.status == "2") {
      //       return a - b
      //     }
      //     return -1
      //   })
      // )
      setPurchaseOrderList(data)
      setLoading(false)
    })
  }

  const loadPrComplete = () => {
    setLoading(true)
    axios
      .get("/api/purchase/purchase-requitition/complete")
      .then((res) => {
        const { data } = res.data
        console.log(data, "data")
        setDataPrList(data)
        setLoading(false)
      })
      .catch((error) => {
        console.error("Error fetching data:", error)
        setLoading(false)
      })
  }

  useEffect(() => {
    loadData()
    loadPrComplete()
  }, [])

  const handleChange = (page, pageSize = 10) => {
    loadData(`/api/purchase/purchase-requitition/?page=${page}`, pageSize, {
      search,
      page,
      ...filterData,
    })
  }

  const handleChangeSearch = () => {
    setIsSearch(true)
    loadData(`/api/purchase/purchase-requitition`, 10, { search })
  }

  const handleFilter = (data) => {
    setFilterData(data)
    loadData(`/api/purchase/purchase-requitition`, 10, data)
  }

  const handleBulkPoClick = () => {
    setIsModalVisible(true)
  }

  const handleCancel = () => {
    setIsModalVisible(false)
  }

  const handleSubmit = (selectedKeys) => {
    console.log("Selected keys:", selectedKeys)
    setIsModalVisible(false)
  }

  const handleChangeExport = (value, field) => {
    setFilterExport({ ...filterExport, [field]: value })
  }

  const clearFilter = () => {
    form.resetFields()
    setIsFilterExportModalOpen(false)
    setIsFilter(false)
    setFilterExport({
      created_at: null,
      status: null,
    })
    handleOk({})
  }

  const handleNewExportContent = (valueExport) => {
    setLoadingExport(true)
    axios
      .post(
        `/api/purchase/purchase-requitition/export/`,
        { ...valueExport, created_at: filterExport.created_at },
        {
          headers: {
            "Content-Type": "application/json",
          },
        }
      )
      .then((res) => {
        const { data } = res.data
        setFilterData({})
        setFilterExport({})
        form.resetFields()
        window.open(data)
        setLoadingExport(false)
      })
      .catch((e) => setLoadingExport(false))
  }

  const listActions = [
    {
      title: "Action",
      key: "id",
      align: "center",
      fixed: "right",
      width: 100,
      render: (text, record) => {
        if (!show) return null
        return (
          <Dropdown.Button
            style={{
              left: -16,
            }}
            overlay={
              <Menu itemIcon={<RightOutlined />}>
                {record.request_status == 5 && (
                  <Menu.Item
                    icon={<EditOutlined />}
                    onClick={() => navigate(`form/${record.uid_requitition}`)}
                  >
                    Ubah
                  </Menu.Item>
                )}

                <Menu.Item
                  icon={<EyeOutlined />}
                  onClick={() => navigate(`detail/${record.uid_requitition}`)}
                >
                  Detail
                </Menu.Item>

                {/* {inArray(record.request_status, ["0", "5"]) && (
                  <Popconfirm
                    title="Yakin Batalkan PO ini?"
                    onConfirm={() => handleCancel(record.uid_requitition)}
                    // onCancel={cancel}
                    okText="Ya, Batalkan"
                    cancelText="Batal"
                  >
                    <Menu.Item icon={<CloseOutlined />}>
                      <span>Cancel</span>
                    </Menu.Item>
                  </Popconfirm>
                )} */}
              </Menu>
            }
          ></Dropdown.Button>
        )
      },
    },
  ]

  const show = !inArray(getItem("role"), ["adminwarehouse"])

  const menu = (
    <Menu>
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
            onOk={() => {
              form.submit()
            }}
            confirmLoading={loadingExport}
            onCancel={clearFilter}
          >
            <Form
              form={form}
              name="basic"
              layout="vertical"
              onFinish={(value) => {
                console.log(value, "value")
                handleNewExportContent(value)
                setIsFilter(true)
                setIsFilterExportModalOpen(false)
              }}
              // onFinishFailed={onFinishFailed}
              autoComplete="off"
            >
              <Form.Item label="Status" name="status">
                <Select
                  mode="multiple"
                  allowClear
                  className="w-full mb-2"
                  placeholder="Pilih Status"
                >
                  <Select.Option value={0}>Waiting Approval</Select.Option>
                  <Select.Option value={1}>On Process</Select.Option>
                  <Select.Option value={2}>Complete</Select.Option>
                  <Select.Option value={3}>Rejected</Select.Option>
                  <Select.Option value={4}>Draft</Select.Option>
                </Select>
              </Form.Item>
              <Form.Item label="Created Date" name="created_at">
                <RangePicker
                  className="w-full"
                  format={"DD-MM-YYYY"}
                  onChange={(e, dateString) =>
                    handleChangeExport(dateString, "created_at")
                  }
                />
              </Form.Item>
            </Form>
          </Modal>
        </>
      </Menu.Item>
      <Menu.Item>
        <a onClick={handleBulkPoClick}>
          <UploadOutlined />
          <span className="ml-2">Bulk PO</span>
        </a>
        <ModalBulkPo
          visible={isModalVisible}
          onCancel={handleCancel}
          onSubmit={handleSubmit}
          data={dataPrList}
        />
      </Menu.Item>
    </Menu>
  )

  const rightContent = (
    <div className="flex justify-between items-center">
      <FilterModal handleOk={handleFilter} type="pr" />
      <Dropdown overlay={menu}>
        <button
          className="text-blue-700 border-[1px] border-blue-700 hover:bg-blue-800/10 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mx-2"
          onClick={(e) => e.preventDefault()}
        >
          <span className="mr-2">More Option</span>
          <DownOutlined />
        </button>
      </Dropdown>
      {show && (
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

  return (
    <Layout title="List Purchase Requisition" rightContent={rightContent}>
      <div className="card">
        <div className="card-body">
          <div className="row mb-4">
            <div className="col-md-12"></div>
            <div className="col-md-4 col-sm-6 col-12">
              <Input
                placeholder="Cari disini "
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
              <strong className="float-right text-blue-400">
                Total Data: {total}
              </strong>
            </div>
          </div>
          <Table
            rowSelection
            dataSource={searchPurchaseOrderList || purchaseOrderList}
            columns={[...purchaseRequisitionListColumn, ...listActions]}
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
          />
        </div>
      </div>
    </Layout>
  )
}

export default PurchaseRequisition
