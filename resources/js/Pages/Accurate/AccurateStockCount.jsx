import React, { useEffect, useState } from "react"
import {
  Table,
  Button,
  Input,
  message,
  DatePicker,
  Modal,
  Tag,
  Tooltip,
  Space,
} from "antd"
import {
  ReloadOutlined,
  EyeOutlined,
  DownloadOutlined,
  LinkOutlined,
} from "@ant-design/icons"
import Layout from "../../components/layout"
import moment from "moment"

const { RangePicker } = DatePicker

const AccurateStockCount = () => {
  const [data, setData] = useState([])
  const [loading, setLoading] = useState(true)
  const [search, setSearch] = useState("")
  const [dateRange, setDateRange] = useState([])
  const [customerId, setCustomerId] = useState("")
  const [createdBy, setCreatedBy] = useState("")
  const [pagination, setPagination] = useState({
    current: 1,
    pageSize: 10,
    total: 0,
  })
  const [detailModalVisible, setDetailModalVisible] = useState(false)
  const [selectedCountId, setSelectedCountId] = useState(null)
  const [detailData, setDetailData] = useState([])
  const [detailLoading, setDetailLoading] = useState(false)

  const getStockCounts = (page = 1, pageSize = 10) => {
    setLoading(true)
    const params = new URLSearchParams({
      page: page.toString(),
      per_page: pageSize.toString(),
    })

    if (search) params.append("search", search)
    if (dateRange.length === 2) {
      params.append("date_from", dateRange[0].format("YYYY-MM-DD"))
      params.append("date_to", dateRange[1].format("YYYY-MM-DD"))
    }
    if (customerId) params.append("customer_id", customerId)
    if (createdBy) params.append("created_by", createdBy)

    fetch(`/api/accurate/stock-count?${params}`)
      .then((res) => res.json())
      .then((json) => {
        if (json.status === "success") {
          setData(json.data.data || [])
          setPagination({
            current: json.data.current_page,
            pageSize: json.data.per_page,
            total: json.data.total,
          })
        } else {
          message.error("Gagal mengambil data")
        }
      })
      .catch(() => message.error("Terjadi kesalahan"))
      .finally(() => setLoading(false))
  }

  const getStockCountDetail = (countId) => {
    setDetailLoading(true)
    fetch(`/api/accurate/stock-count/count/${countId}`)
      .then((res) => res.json())
      .then((json) => {
        if (json.status === "success") {
          setDetailData(json.data || [])
        } else {
          message.error("Gagal mengambil detail data")
        }
      })
      .catch(() => message.error("Terjadi kesalahan"))
      .finally(() => setDetailLoading(false))
  }

  useEffect(() => {
    getStockCounts()
  }, [])

  const handleTableChange = (paginationInfo) => {
    getStockCounts(paginationInfo.current, paginationInfo.pageSize)
  }

  const handleSearch = () => {
    setPagination({ ...pagination, current: 1 })
    getStockCounts(1, pagination.pageSize)
  }

  const handleReset = () => {
    setSearch("")
    setDateRange([])
    setCustomerId("")
    setCreatedBy("")
    setPagination({ ...pagination, current: 1 })
    getStockCounts(1, pagination.pageSize)
  }

  const showDetail = (countId) => {
    setSelectedCountId(countId)
    setDetailModalVisible(true)
    getStockCountDetail(countId)
  }

  const downloadAttachment = (stockCountId, filename) => {
    window.open(
      `/api/accurate/stock-count/${stockCountId}/attachment/${filename}`,
      "_blank"
    )
  }

  const columns = [
    // {
    //   title: "Count ID",
    //   dataIndex: "count_id",
    //   key: "count_id",
    //   width: 150,
    //   render: (text) => (
    //     <Button
    //       type="link"
    //       onClick={() => showDetail(text)}
    //       style={{ padding: 0, height: 'auto' }}
    //     >
    //       {text}
    //     </Button>
    //   ),
    // },
    {
      title: "Created At",
      dataIndex: "created_at",
      key: "created_at",
      width: 150,
      render: (text) => moment(text).format("DD/MM/YYYY HH:mm"),
    },
    {
      title: "PIC",
      dataIndex: "pic_name",
      key: "pic_name",
      width: 150,
      render: (text) => text || "-",
    },
    // {
    //   title: "Tanggal",
    //   dataIndex: "date",
    //   key: "date",
    //   width: 120,
    //   render: (text) => moment(text).format("DD/MM/YYYY"),
    // },
    // {
    //   title: "Customer Code",
    //   dataIndex: "customer_id",
    //   key: "customer_id",
    //   width: 120,
    // },
    {
      title: "Head Account",
      dataIndex: "customer_name",
      key: "customer_name",
      width: 200,
      render: (text) => (
        <Tooltip title={text}>
          {text?.length > 25 ? text.substring(0, 25) + "..." : text || "-"}
        </Tooltip>
      ),
    },
    // {
    //   title: "Customer Child ID",
    //   dataIndex: "customer_child_id",
    //   key: "customer_child_id",
    //   width: 150,
    //   render: (text) => text || "-",
    // },
    {
      title: "Nama Toko",
      dataIndex: "customer_child_name",
      key: "customer_child_name",
      width: 150,
      render: (text) => text || "-",
    },

    // {
    //   title: "Notes",
    //   dataIndex: "notes",
    //   key: "notes",
    //   width: 150,
    //   render: (text) => text || "-",
    // },

    {
      title: "Stock Expired",
      dataIndex: "count_expired",
      key: "count_expired",
      width: 100,
      align: "center",
      render: (text) => <Tag color="blue">{text}</Tag>,
    },
    {
      title: "Stock Gimmick",
      dataIndex: "count_gimmick",
      key: "count_gimmick",
      width: 100,
      align: "center",
      render: (text) => <Tag color="blue">{text}</Tag>,
    },
    {
      title: "Stock Reguler",
      dataIndex: "count_reguler",
      key: "count_reguler",
      width: 100,
      align: "center",
      render: (text) => <Tag color="blue">{text}</Tag>,
    },
    // {
    //   title: "Attachments",
    //   dataIndex: "attachments",
    //   key: "attachments",
    //   width: 150,
    //   render: (text) => text || "-",
    // },
    // {
    //   title: "Created By",
    //   dataIndex: "created_by",
    //   key: "created_by",
    //   width: 150,
    // },

    {
      title: "Status",
      dataIndex: "status",
      key: "status",
      width: 150,
      render: (text) => text || "-",
    },
    {
      title: "Aksi",
      key: "action",
      width: 100,
      fixed: "right",
      render: (_, record) => (
        <Space>
          <Tooltip title="Lihat Detail">
            <Button
              type="primary"
              size="small"
              icon={<EyeOutlined />}
              onClick={() => showDetail(record.count_id)}
            />
          </Tooltip>
        </Space>
      ),
    },
  ]

  const detailColumns = [
    // {
    //   title: "Product Code",
    //   dataIndex: "product_code",
    //   key: "product_code",
    //   width: 150,
    // },
    {
      title: "Product Name",
      dataIndex: "product_name",
      key: "product_name",
      width: 200,
      render: (text) => (
        <Tooltip title={text}>
          {text?.length > 30 ? text.substring(0, 30) + "..." : text || "-"}
        </Tooltip>
      ),
    },
    // {
    //   title: "Unit",
    //   dataIndex: "product_unit",
    //   key: "product_unit",
    //   width: 80,
    //   render: (text) => text || "-",
    // },
    {
      title: "Actual Stock",
      dataIndex: "actual_stock",
      key: "actual_stock",
      width: 120,
      align: "right",
      render: (text) => <Tag color="green">{text}</Tag>,
    },
    {
      title: "Notes",
      dataIndex: "notes",
      key: "notes",
      width: 200,
      render: (text) => (
        <Tooltip title={text}>
          {text?.length > 30 ? text.substring(0, 30) + "..." : text || "-"}
        </Tooltip>
      ),
    },
    // {
    //   title: "Key",
    //   dataIndex: "key",
    //   key: "key",
    //   width: 100,
    //   render: (text) => text || "-",
    // },
    {
      title: "Attachments",
      dataIndex: "attachments",
      key: "attachments",
      width: 150,
      render: (text, record) => {
        if (!text) return "-"
        const files = text.split(",")
        return (
          <Space direction="vertical" size="small">
            {files.map((filename, index) => (
              <Button
                key={index}
                size="small"
                icon={<LinkOutlined />}
                onClick={() => downloadAttachment(record.id, filename.trim())}
              >
                Lihat
              </Button>
            ))}
          </Space>
        )
      },
    },
  ]

  return (
    <Layout title="Stock Count Data">
      <div className="card">
        <div className="card-body">
          {/* Filter Section */}
          <div className="mb-4 p-4 bg-gray-50 rounded-lg">
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              <div>
                <label className="block text-sm font-medium mb-1">Search</label>
                <Input.Search
                  placeholder="Cari Count ID, Customer, PIC..."
                  allowClear
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                  onSearch={handleSearch}
                />
              </div>
              <div>
                <label className="block text-sm font-medium mb-1">
                  Tanggal
                </label>
                <RangePicker
                  style={{ width: "100%" }}
                  value={dateRange}
                  onChange={setDateRange}
                  format="DD/MM/YYYY"
                />
              </div>
              <div>
                <label className="block text-sm font-medium mb-1">
                  Customer Code
                </label>
                <Input
                  placeholder="Customer Code"
                  allowClear
                  value={customerId}
                  onChange={(e) => setCustomerId(e.target.value)}
                />
              </div>
              <div>
                <label className="block text-sm font-medium mb-1">
                  Created By
                </label>
                <Input
                  placeholder="Created By"
                  allowClear
                  value={createdBy}
                  onChange={(e) => setCreatedBy(e.target.value)}
                />
              </div>
            </div>
            <div className="flex gap-2 mt-4">
              <Button type="primary" onClick={handleSearch}>
                Filter
              </Button>
              <Button onClick={handleReset}>Reset</Button>
            </div>
          </div>

          {/* Action Buttons */}
          <div className="mb-4 flex justify-between items-center">
            <div className="flex items-center gap-3">
              <div>
                Total Data: <strong>{pagination.total}</strong>
              </div>
            </div>
            <div className="flex gap-2">
              <Button
                icon={<ReloadOutlined />}
                onClick={() => getStockCounts()}
              >
                Refresh
              </Button>
            </div>
          </div>

          {/* Table */}
          <Table
            rowKey="count_id"
            columns={columns}
            dataSource={data}
            loading={loading}
            pagination={{
              ...pagination,
              showSizeChanger: true,
              showQuickJumper: true,
              showTotal: (total, range) =>
                `${range[0]}-${range[1]} dari ${total} items`,
            }}
            onChange={handleTableChange}
            scroll={{ x: "max-content", y: 600 }}
            sticky={{ offsetHeader: 64 }}
            size="small"
          />
        </div>
      </div>

      {/* Detail Modal */}
      <Modal
        title={`Detail Stock Count - ${selectedCountId}`}
        open={detailModalVisible}
        onCancel={() => setDetailModalVisible(false)}
        footer={null}
        width={1200}
        centered
      >
        <Table
          rowKey="id"
          columns={detailColumns}
          dataSource={detailData}
          loading={detailLoading}
          pagination={false}
          scroll={{ x: "max-content", y: 400 }}
          sticky={{ offsetHeader: 0 }}
          size="small"
        />
      </Modal>
    </Layout>
  )
}

export default AccurateStockCount
