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
  Card,
} from "antd"
import {
  ReloadOutlined,
  EyeOutlined,
  ArrowLeftOutlined,
  LinkOutlined,
} from "@ant-design/icons"
import { useParams, useNavigate } from "react-router-dom"
import Layout from "../../components/layout"
import moment from "moment"

const { RangePicker } = DatePicker

const StoreStockCount = () => {
  const { merchandiserId, storeId } = useParams()
  const navigate = useNavigate()
  const [data, setData] = useState([])
  const [loading, setLoading] = useState(true)
  const [search, setSearch] = useState("")
  const [dateRange, setDateRange] = useState([])
  const [storeInfo, setStoreInfo] = useState(null)
  const [pagination, setPagination] = useState({
    current: 1,
    pageSize: 10,
    total: 0,
  })
  const [detailModalVisible, setDetailModalVisible] = useState(false)
  const [selectedCountId, setSelectedCountId] = useState(null)
  const [detailData, setDetailData] = useState([])
  const [detailLoading, setDetailLoading] = useState(false)

  const getStoreInfo = async () => {
    try {
      const response = await fetch(`/api/accurate/store/${storeId}`)
      if (response.ok) {
        const result = await response.json()
        if (result.status === "success") {
          setStoreInfo(result.data)
        }
      }
    } catch (error) {
      console.error("Error fetching store info:", error)
    }
  }

  const getStockCounts = (page = 1, pageSize = 10) => {
    setLoading(true)
    const params = new URLSearchParams({
      page: page.toString(),
      per_page: pageSize.toString(),
      customer_child_id: storeId,
    })

    if (search) params.append("search", search)
    if (dateRange.length === 2) {
      params.append("date_from", dateRange[0].format("YYYY-MM-DD"))
      params.append("date_to", dateRange[1].format("YYYY-MM-DD"))
    }

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
          // The getStockCountDetail method returns a different structure
          // with list, items, and items_by_type
          setDetailData(json.data.items || [])
        } else {
          message.error("Gagal mengambil detail data")
        }
      })
      .catch(() => message.error("Terjadi kesalahan"))
      .finally(() => setDetailLoading(false))
  }

  useEffect(() => {
    if (storeId) {
      getStoreInfo()
      getStockCounts()
    }
  }, [storeId])

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
    setPagination({ ...pagination, current: 1 })
    getStockCounts(1, pagination.pageSize)
  }

  const showDetail = (countId) => {
    setSelectedCountId(countId)
    setDetailModalVisible(true)
    getStockCountDetail(countId)
  }



  const handleBackToMerchandiser = () => {
    navigate(`/accurate-integration/list-merchandiser/${merchandiserId}`)
  }

  const getStoreInitials = (storeName) => {
    return storeName
      .split(" ")
      .map((word) => word.charAt(0))
      .join("")
      .toUpperCase()
      .slice(0, 2)
  }

  const columns = [
    {
      title: "Account Name",
      dataIndex: "customer_name",
      key: "customer_name",
      width: 200,
      render: (text) => (
        <Tooltip title={text}>
          {text?.length > 25 ? text.substring(0, 25) + "..." : text || "-"}
        </Tooltip>
      ),
    },
    {
      title: "Store Name",
      dataIndex: "customer_child_name",
      key: "customer_child_name",
      width: 200,
      render: (text) => (
        <Tooltip title={text}>
          {text?.length > 25 ? text.substring(0, 25) + "..." : text || "-"}
        </Tooltip>
      ),
    },
    {
      title: "Tanggal Input",
      dataIndex: "created_at",
      key: "created_at",
      width: 150,
      render: (text) => moment(text).format("DD/MM/YYYY HH:mm"),
    },
    {
      title: "Notes",
      dataIndex: "notes",
      key: "notes",
      width: 150,
    },
    {
      title: "Attachment",
      dataIndex: "attachment_0",
      key: "attachment_0",
      width: 120,
      align: "center",
      render: (text) =>
        text ? (
          <Button
            type="link"
            size="small"
            icon={<LinkOutlined />}
            onClick={() => window.open(text, "_blank")}
          >
            Lihat
          </Button>
        ) : (
          <span style={{ color: "#999" }}>-</span>
        ),
    },
    {
      title: "Action",
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
    {
      title: "Actual Stock",
      dataIndex: "actual_stock",
      key: "actual_stock",
      width: 120,
      align: "right",
      render: (text) => <Tag color="green">{text}</Tag>,
    },
    {
      title: "Tanggal Perhitungan",
      dataIndex: "created_at",
      key: "created_at",
      width: 150,
      render: (text) => moment(text).format("DD/MM/YYYY"),
    },
    // {
    //   title: "Notes",
    //   dataIndex: "notes",
    //   key: "notes",
    //   width: 200,
    //   render: (text) => (
    //     <Tooltip title={text}>
    //       {text?.length > 30 ? text.substring(0, 30) + "..." : text || "-"}
    //     </Tooltip>
    //   ),
    // },
    // {
    //   title: "Attachments",
    //   dataIndex: "attachments",
    //   key: "attachments",
    //   width: 150,
    //   render: (text, record) => {
    //     if (!text) return "-"
    //     const files = text.split(",")
    //     return (
    //       <Space direction="vertical" size="small">
    //         {files.map((filename, index) => (
    //           <Button
    //             key={index}
    //             size="small"
    //             icon={<LinkOutlined />}
    //             onClick={() => downloadAttachment(record.id, filename.trim())}
    //           >
    //             Lihat
    //           </Button>
    //         ))}
    //       </Space>
    //     )
    //   },
    // },
  ]

  return (
    <Layout title="Stock Count Data - Store Detail">
      <div className="card">
        <div className="card-body">
          {/* Back Button and Store Info */}
          <div className="row mb-4">
            <div className="col-12">
              <Button
                icon={<ArrowLeftOutlined />}
                onClick={handleBackToMerchandiser}
                style={{ marginBottom: "16px" }}
              >
                Kembali ke Detail Merchandiser
              </Button>

              {storeInfo && (
                <Card
                  style={{
                    backgroundColor: "#f8f9fa",
                    border: "1px solid #e9ecef",
                    borderRadius: "12px",
                  }}
                >
                  <div
                    style={{
                      display: "flex",
                      alignItems: "center",
                      gap: "16px",
                    }}
                  >
                    <div
                      style={{
                        width: "60px",
                        height: "60px",
                        backgroundColor: "#20B2AA",
                        borderRadius: "8px",
                        display: "flex",
                        alignItems: "center",
                        justifyContent: "center",
                        fontSize: "24px",
                        fontWeight: "bold",
                        color: "white",
                      }}
                    >
                      {getStoreInitials(
                        storeInfo.name || storeInfo.nama_store || "ST"
                      )}
                    </div>
                    <div style={{ flex: 1 }}>
                      <h2
                        style={{
                          margin: 0,
                          fontSize: "20px",
                          fontWeight: "bold",
                          color: "#333",
                        }}
                      >
                        {storeInfo.name || storeInfo.nama_store || "Store Name"}
                      </h2>
                      <p
                        style={{
                          margin: "4px 0 0 0",
                          color: "#666",
                          fontSize: "14px",
                        }}
                      >
                        Stock Count Data
                      </p>
                    </div>
                    <div style={{ textAlign: "center" }}>
                      <div
                        style={{
                          fontSize: "14px",
                          color: "#666",
                          marginBottom: "4px",
                        }}
                      >
                        Total Records
                      </div>
                      <div
                        style={{
                          fontSize: "24px",
                          fontWeight: "bold",
                          color: "#003E8A",
                        }}
                      >
                        {pagination.total}
                      </div>
                    </div>
                  </div>
                </Card>
              )}
            </div>
          </div>

          {/* Filter Section */}
          <div className="mb-4 p-4 bg-gray-50 rounded-lg">
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <div>
                <label className="block text-sm font-medium mb-1">Search</label>
                <Input.Search
                  placeholder="Cari Count ID, PIC, Product..."
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
              <div className="flex items-end">
                <div className="flex gap-2">
                  <Button type="primary" onClick={handleSearch}>
                    Filter
                  </Button>
                  <Button onClick={handleReset}>Reset</Button>
                </div>
              </div>
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

export default StoreStockCount