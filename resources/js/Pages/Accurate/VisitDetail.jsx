import React, { useEffect, useState } from "react"
import {
  Modal,
  Table,
  Input,
  DatePicker,
  Select,
  Button,
  Space,
  Tag,
  Card,
  Row,
  Col,
  Statistic,
  message,
} from "antd"
import { SearchOutlined, ReloadOutlined, UserOutlined } from "@ant-design/icons"
import axios from "axios"
import moment from "moment"

const { RangePicker } = DatePicker
const { Option } = Select

const VisitDetailModal = ({ open, onClose, picName }) => {
  const [visits, setVisits] = useState([])
  const [loading, setLoading] = useState(false)
  const [pagination, setPagination] = useState({
    current: 1,
    pageSize: 10,
    total: 0,
  })
  const [filters, setFilters] = useState({
    search: "",
    dateRange: null,
    status: "",
  })
  const [statistics, setStatistics] = useState({
    total: 0,
    completed: 0,
    submitted: 0,
    draft: 0,
    cancelled: 0,
  })

  // Fetch visits data
  const fetchVisits = (page = 1) => {
    if (!picName) return

    setLoading(true)
    const params = {
      page,
      per_page: pagination.pageSize,
      search: filters.search,
      status: filters.status,
    }

    if (filters.dateRange && filters.dateRange.length === 2) {
      params.start_date = filters.dateRange[0].format("YYYY-MM-DD")
      params.end_date = filters.dateRange[1].format("YYYY-MM-DD")
    }

    axios
      .get(`/api/accurate/visits/by-pic/${encodeURIComponent(picName)}`, {
        params,
      })
      .then((response) => {
        const { data, pagination: paginationData } = response.data
        setVisits(data || [])
        setPagination({
          ...pagination,
          current: parseInt(paginationData.current_page),
          total: parseInt(paginationData.total),
        })

        // Calculate statistics
        const stats = {
          total: parseInt(paginationData.total),
          completed:
            data?.filter((visit) => visit.status === "completed").length || 0,
          submitted:
            data?.filter((visit) => visit.status === "submitted").length || 0,
          draft: data?.filter((visit) => visit.status === "draft").length || 0,
          cancelled:
            data?.filter((visit) => visit.status === "cancelled").length || 0,
        }
        setStatistics(stats)
      })
      .catch((error) => {
        console.error("Error fetching visits:", error)
        message.error("Gagal memuat data kunjungan")
      })
      .finally(() => {
        setLoading(false)
      })
  }

  // Load data when modal opens or filters change
  useEffect(() => {
    if (open && picName) {
      fetchVisits(1)
    }
  }, [open, picName, filters])

  // Handle pagination change
  const handleTableChange = (paginationInfo) => {
    setPagination(paginationInfo)
    fetchVisits(paginationInfo.current)
  }

  // Handle search
  const handleSearch = (value) => {
    setFilters({ ...filters, search: value })
  }

  // Handle date range change
  const handleDateRangeChange = (dates) => {
    setFilters({ ...filters, dateRange: dates })
  }

  // Handle status filter change
  const handleStatusChange = (value) => {
    setFilters({ ...filters, status: value })
  }

  // Reset filters
  const handleReset = () => {
    setFilters({
      search: "",
      dateRange: null,
      status: "",
    })
  }

  // Table columns
  const columns = [
    {
      title: "No.",
      key: "index",
      width: 60,
      render: (_, __, index) =>
        (pagination.current - 1) * pagination.pageSize + index + 1,
    },
    {
      title: "Visit ID",
      dataIndex: "visit_id",
      key: "visit_id",
      width: 150,
    },
    {
      title: "Customer ID",
      dataIndex: "customer_id",
      key: "customer_id",
      width: 120,
    },
    {
      title: "Customer Child",
      dataIndex: "customer_child_name",
      key: "customer_child_name",
      width: 200,
      render: (text, record) => text || record.customer_child_id || "-",
    },
    {
      title: "Tanggal Kunjungan",
      dataIndex: "date",
      key: "date",
      width: 150,
      render: (date) => moment(date).format("DD/MM/YYYY"),
    },
    {
      title: "Status",
      dataIndex: "status",
      key: "status",
      width: 120,
      render: (status) => {
        const statusConfig = {
          completed: { color: "green", text: "Selesai" },
          pending: { color: "orange", text: "Pending" },
          cancelled: { color: "red", text: "Dibatalkan" },
          submitted: { color: "blue", text: "Submitted" },
          draft: { color: "gray", text: "Draft" },
        }
        const config = statusConfig[status] || {
          color: "default",
          text: status,
        }
        return <Tag color={config.color}>{config.text}</Tag>
      },
    },
    {
      title: "Catatan",
      dataIndex: "notes",
      key: "notes",
      ellipsis: true,
      render: (text) => text || "-",
    },
    {
      title: "Dibuat",
      dataIndex: "created_at",
      key: "created_at",
      width: 150,
      render: (date) => moment(date).format("DD/MM/YYYY HH:mm"),
    },
  ]

  return (
    <Modal
      title={
        <Space>
          <UserOutlined />
          <span>Detail Kunjungan - {picName}</span>
        </Space>
      }
      open={open}
      onCancel={onClose}
      width={1200}
      footer={null}
      destroyOnClose
    >
      <div style={{ marginBottom: 16 }}>
        {/* Statistics Cards */}
        {/* <Row gutter={16} style={{ marginBottom: 16 }}>
          <Col span={4}>
            <Card>
              <Statistic
                title="Total Kunjungan"
                value={statistics.total}
                valueStyle={{ color: "#1890ff" }}
              />
            </Card>
          </Col>
          <Col span={5}>
            <Card>
              <Statistic
                title="Selesai"
                value={statistics.completed}
                valueStyle={{ color: "#52c41a" }}
              />
            </Card>
          </Col>
          <Col span={5}>
            <Card>
              <Statistic
                title="Submitted"
                value={statistics.submitted}
                valueStyle={{ color: "#1890ff" }}
              />
            </Card>
          </Col>
          <Col span={5}>
            <Card>
              <Statistic
                title="Draft"
                value={statistics.draft}
                valueStyle={{ color: "#faad14" }}
              />
            </Card>
          </Col>
          <Col span={5}>
            <Card>
              <Statistic
                title="Dibatalkan"
                value={statistics.cancelled}
                valueStyle={{ color: "#ff4d4f" }}
              />
            </Card>
          </Col>
        </Row> */}

        {/* Filters */}
        <Row gutter={16} style={{ marginBottom: 16 }}>
          <Col span={8}>
            <Input.Search
              placeholder="Cari berdasarkan Visit ID atau Customer"
              allowClear
              onSearch={handleSearch}
              prefix={<SearchOutlined />}
            />
          </Col>
          <Col span={6}>
            <RangePicker
              style={{ width: "100%" }}
              placeholder={["Tanggal Mulai", "Tanggal Akhir"]}
              onChange={handleDateRangeChange}
              value={filters.dateRange}
            />
          </Col>
          <Col span={4}>
            <Select
              placeholder="Status"
              style={{ width: "100%" }}
              allowClear
              onChange={handleStatusChange}
              value={filters.status}
            >
              <Option value="completed">Selesai</Option>
              <Option value="submitted">Submitted</Option>
              <Option value="draft">Draft</Option>
              <Option value="cancelled">Dibatalkan</Option>
            </Select>
          </Col>
          <Col span={6}>
            <Space>
              <Button
                icon={<ReloadOutlined />}
                onClick={() => fetchVisits(pagination.current)}
                loading={loading}
              >
                Refresh
              </Button>
              <Button onClick={handleReset}>Reset Filter</Button>
            </Space>
          </Col>
        </Row>
      </div>

      {/* Table */}
      <Table
        columns={columns}
        dataSource={visits}
        loading={loading}
        pagination={{
          ...pagination,
          showSizeChanger: true,
          showQuickJumper: true,
          showTotal: (total, range) =>
            `${range[0]}-${range[1]} dari ${total} kunjungan`,
        }}
        onChange={handleTableChange}
        rowKey="id"
        scroll={{ x: 1000 }}
        size="small"
      />
    </Modal>
  )
}

export default VisitDetailModal