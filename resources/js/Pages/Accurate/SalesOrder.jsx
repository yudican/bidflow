import React, { useEffect, useState } from "react"
import {
  Table,
  Button,
  Input,
  message,
  Modal,
  DatePicker,
  Spin,
  Pagination,
  Select,
  Dropdown,
  Menu,
  Tag
} from "antd"
import {
  ReloadOutlined,
  EyeOutlined,
  SearchOutlined,
  CloseCircleFilled,
  DownOutlined,
  PlusOutlined,
  FilterOutlined
} from "@ant-design/icons"
import Layout from "../../components/layout"
import { useNavigate } from "react-router-dom"
import dayjs from "dayjs"
import isBetween from "dayjs/plugin/isBetween"
import "dayjs/locale/id"
dayjs.locale("id")
dayjs.extend(isBetween)

const { RangePicker } = DatePicker
const { Option } = Select

const SalesOrder = () => {
  const [salesOrders, setSalesOrders] = useState([])
  const [loading, setLoading] = useState(true)
  const [syncing, setSyncing] = useState(false)
  const [search, setSearch] = useState("")
  const [isSearch, setIsSearch] = useState(false)
  const [dateRange, setDateRange] = useState(null)
  const [detailVisible, setDetailVisible] = useState(false)
  const [detailData, setDetailData] = useState([])
  const [selectedOrderNumber, setSelectedOrderNumber] = useState(null)
  const [loadingDetail, setLoadingDetail] = useState(false)
  const [currentPage, setCurrentPage] = useState(1)
  const [perPage] = useState(10)
  const [totalData, setTotalData] = useState(0)
  const [statusFilter, setStatusFilter] = useState(null)
  const [customerFilter, setCustomerFilter] = useState(null)

  const fetchData = (page = 1, searchTerm = "", filters = {}) => {
    setLoading(true)

    const params = new URLSearchParams({
      per_page: perPage,
      page: page,
      ...(searchTerm && { search: searchTerm }),
      ...(filters.status && { status: filters.status }),
      ...(filters.customer_no && { customer_no: filters.customer_no }),
      ...(filters.date_from && { date_from: filters.date_from }),
      ...(filters.date_to && { date_to: filters.date_to })
    })

    fetch(`/api/accurate/sales-order?${params}`)
      .then(res => res.json())
      .then(json => {
        if (json.status === 'success') {
          const paginated = json.data
          setSalesOrders(paginated.data || [])
          setTotalData(paginated.total || 0)
          setCurrentPage(paginated.current_page || 1)
        } else {
          message.error(json.message || "Gagal memuat data sales order")
        }
      })
      .catch(() => message.error("Gagal memuat data sales order"))
      .finally(() => setLoading(false))
  }

  const handleSync = () => {
    setSyncing(true)
    fetch("/api/accurate/sync-sales-order", { method: "POST" })
      .then(res => res.json())
      .then(json => {
        if (json.status === "success") {
          message.success("Sinkronisasi berhasil")
          fetchData(currentPage, search, {
            status: statusFilter,
            customer_no: customerFilter,
            date_from: dateRange?.[0]?.format('YYYY-MM-DD'),
            date_to: dateRange?.[1]?.format('YYYY-MM-DD')
          })
        } else {
          message.warning("Gagal sinkronisasi")
        }
      })
      .catch(() => message.error("Terjadi kesalahan sinkronisasi"))
      .finally(() => setSyncing(false))
  }

  const showDetail = (id, number) => {
    setDetailVisible(true)
    setSelectedOrderNumber(number)
    setLoadingDetail(true)
    setDetailData([])
    fetch(`/api/accurate/sales-order/${id}/details`)
      .then(res => res.json())
      .then(json => {
        if (json.status === "success") {
          setDetailData(json.data || [])
        } else {
          message.warning("Gagal mengambil detail")
        }
      })
      .catch(() => message.error("Gagal mengambil data detail"))
      .finally(() => setLoadingDetail(false))
  }

  const handleSearch = () => {
    setIsSearch(true)
    setCurrentPage(1)
    fetchData(1, search, {
      status: statusFilter,
      customer_no: customerFilter,
      date_from: dateRange?.[0]?.format('YYYY-MM-DD'),
      date_to: dateRange?.[1]?.format('YYYY-MM-DD')
    })
  }

  const handlePageChange = (page) => {
    setCurrentPage(page)
    fetchData(page, search, {
      status: statusFilter,
      customer_no: customerFilter,
      date_from: dateRange?.[0]?.format('YYYY-MM-DD'),
      date_to: dateRange?.[1]?.format('YYYY-MM-DD')
    })
  }

  const handleFilter = (filters) => {
    setStatusFilter(filters.status)
    setCustomerFilter(filters.customer_no)
    setCurrentPage(1)
    fetchData(1, search, filters)
  }

  const clearSearch = () => {
    setSearch("")
    setIsSearch(false)
    setCurrentPage(1)
    fetchData(1, "", {
      status: statusFilter,
      customer_no: customerFilter,
      date_from: dateRange?.[0]?.format('YYYY-MM-DD'),
      date_to: dateRange?.[1]?.format('YYYY-MM-DD')
    })
  }

  useEffect(() => {
    fetchData()
  }, [])

  const getStatusColor = (status) => {
    const colors = {
      'draft': 'default',
      'confirmed': 'blue',
      'processing': 'orange',
      'shipped': 'purple',
      'delivered': 'green',
      'cancelled': 'red'
    }
    return colors[status] || 'default'
  }

  const columns = [
    {
      title: "No. Order",
      dataIndex: "number",
      key: "number",
      width: 150
    },
    {
      title: "Tanggal Transaksi",
      dataIndex: "trans_date",
      key: "trans_date",
      width: 130,
      render: (text) => text ? dayjs(text).format("D MMM YYYY") : "-"
    },
    {
      title: "Customer",
      dataIndex: "customer_name",
      key: "customer_name",
      width: 200,
      render: (text, record) => (
        <div>
          <div className="font-medium">{text || '-'}</div>
          <div className="text-xs text-gray-500">{record.customer_no}</div>
        </div>
      )
    },
    {
      title: "Description",
      dataIndex: "description",
      key: "description"
    },
    {
      title: "Status",
      dataIndex: "status_name",
      key: "status_name"
    },
    {
      title: "Total",
      dataIndex: "total_amount",
      key: "total_amount",
      render: (val) => parseFloat(val).toLocaleString("id-ID")
    },
    {
      title: "Aksi",
      key: "action",
      width: 100,
      fixed: 'right',
      render: (_, record) => (
        <Button
          type="primary"
          size="small"
          icon={<EyeOutlined />}
          onClick={() => showDetail(record.id, record.number)}
        >
          Detail
        </Button>
      ),
    },
  ]

  const menu = (
    <Menu>
      <Menu.Item key="sync">
        <a onClick={handleSync}>
          <ReloadOutlined />
          <span className="ml-2">Sync Data</span>
        </a>
      </Menu.Item>
      <Menu.Item key="export">
        <a>
          <FilterOutlined />
          <span className="ml-2">Export Data</span>
        </a>
      </Menu.Item>
    </Menu>
  )

  const rightContent = (
    <div className="flex justify-between items-center gap-2">
      <Select
        placeholder="Filter Status"
        allowClear
        style={{ width: 150 }}
        onChange={(value) => {
          setStatusFilter(value)
          handleFilter({
            status: value,
            customer_no: customerFilter,
            date_from: dateRange?.[0]?.format("YYYY-MM-DD"),
            date_to: dateRange?.[1]?.format("YYYY-MM-DD"),
          })
        }}
        value={statusFilter}
      >
        <Option value="draft">Draft</Option>
        <Option value="confirmed">Confirmed</Option>
        <Option value="processing">Processing</Option>
        <Option value="shipped">Shipped</Option>
        <Option value="delivered">Delivered</Option>
        <Option value="cancelled">Cancelled</Option>
      </Select>

      <Dropdown overlay={menu}>
        <Button
          className="text-blue-700 border-[1px] border-blue-700 hover:bg-blue-800/10 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
          loading={syncing}
        >
          <span className="mr-2">More Option</span>
          <DownOutlined />
        </Button>
      </Dropdown>

      <Button
        type="primary"
        icon={<PlusOutlined />}
        className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
      >
        <span className="ml-2">Tambah Order</span>
      </Button>
    </div>
  )

  return (
    <Layout rightContent={rightContent} title="Sales Order">
      <div className="card">
        <div className="card-body">
          <div className="row mb-4">
            <div className="col-md-4 col-sm-6 col-12">
              <Input
                placeholder="Cari disini"
                size="large"
                className="rounded"
                onPressEnter={handleSearch}
                suffix={
                  isSearch ? (
                    <CloseCircleFilled onClick={clearSearch} />
                  ) : (
                    <SearchOutlined onClick={handleSearch} />
                  )
                }
                value={search}
                onChange={(e) => setSearch(e.target.value)}
              />
            </div>
            <div className="col-md-4 col-sm-6 col-12">
              <RangePicker
                placeholder={["Tanggal Mulai", "Tanggal Akhir"]}
                size="large"
                className="w-full"
                onChange={(dates) => {
                  setDateRange(dates)
                  handleFilter({
                    status: statusFilter,
                    customer_no: customerFilter,
                    date_from: dates?.[0]?.format('YYYY-MM-DD'),
                    date_to: dates?.[1]?.format('YYYY-MM-DD')
                  })
                }}
                format="YYYY-MM-DD"
                allowClear
              />
            </div>
            <div className="col-md-4">
              <div className="float-right text-right">
                <strong className="text-red-400">
                  Total Data: {totalData}
                </strong>
              </div>
            </div>
          </div>

          <Table
            columns={columns}
            dataSource={salesOrders}
            loading={loading}
            rowKey="id"
            scroll={{ x: "max-content" }}
            tableLayout="auto"
            pagination={false}
          />

          <Pagination
            current={currentPage}
            total={totalData}
            pageSize={perPage}
            className="mt-4 text-center"
            onChange={handlePageChange}
            showSizeChanger={false}
            showQuickJumper
            showTotal={(total, range) =>
              `${range[0]}-${range[1]} dari ${total} data`
            }
          />
        </div>
      </div>

      <Modal
        title={`Detail Sales Order ${selectedOrderNumber || ""}`}
        open={detailVisible}
        onCancel={() => setDetailVisible(false)}
        footer={null}
        width={900}
      >
        {loadingDetail ? (
          <div className="text-center py-10">
            <Spin tip="Memuat detail..." />
          </div>
        ) : (
          <Table
            columns={[
              { title: "Kode Produk", dataIndex: "item_no", key: "item_no", width: 120 },
              { title: "Nama Produk", dataIndex: "item_name", key: "item_name", width: 200 },
              {
                title: "Qty",
                dataIndex: "quantity",
                key: "quantity",
                width: 80,
                align: 'center',
                render: (val) => parseFloat(val || 0).toLocaleString("id-ID")
              },
              {
                title: "Harga Satuan",
                dataIndex: "unit_price",
                key: "unit_price",
                width: 120,
                align: 'right',
                render: (val) => `Rp ${parseFloat(val || 0).toLocaleString("id-ID")}`
              },
              {
                title: "Total",
                dataIndex: "total_price",
                key: "total_price",
                width: 130,
                align: 'right',
                render: (val) => `Rp ${parseFloat(val || 0).toLocaleString("id-ID")}`
              },
            ]}
            dataSource={detailData}
            rowKey={(record, index) => `${record.product_id}-${index}`}
            pagination={false}
            size="small"
            scroll={{ x: "max-content" }}
          />
        )}
      </Modal>
    </Layout>
  )
}

export default SalesOrder
