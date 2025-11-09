import React, { useEffect, useState } from "react"
import {
  Button,
  DatePicker,
  Input,
  Select,
  Table,
  Tag,
  Modal,
  message,
  Pagination
} from "antd"
import {
  EyeOutlined,
  CheckOutlined,
  CloseOutlined,
  SearchOutlined,
  CloseCircleFilled,
  ReloadOutlined,
  EditOutlined
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
const { TextArea } = Input

const SalesOrderApp = () => {
  const [salesOrders, setSalesOrders] = useState([])
  const [loading, setLoading] = useState(true)
  const [syncing, setSyncing] = useState(false)
  const [search, setSearch] = useState("")
  const [, setIsSearch] = useState(false)
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
  const [approvalModalVisible, setApprovalModalVisible] = useState(false)
  const [rejectionModalVisible, setRejectionModalVisible] = useState(false)
  const [selectedOrder, setSelectedOrder] = useState(null)
  const [rejectionReason, setRejectionReason] = useState("")
  const [approvalNotes, setApprovalNotes] = useState("")
  const [userRole, setUserRole] = useState(null) // Add user role state
  const navigate = useNavigate()
  // const location = useLocation()

  // Function to get user role from auth or props
  useEffect(() => {
    // Assuming user role is available from auth context or props
    // This should be replaced with actual role fetching logic
    const getCurrentUserRole = () => {
      // Example: get from localStorage, auth context, or API
      const role = localStorage.getItem('userRole') || 'admin_sales' // Default for testing
      setUserRole(role)
    }
    getCurrentUserRole()
  }, [])

  // Function to change role for testing purposes
  const handleRoleChange = (newRole) => {
    setUserRole(newRole)
    localStorage.setItem('userRole', newRole)
    message.info(`Role diubah ke: ${newRole}`)
  }

  // Function to check if user can approve based on role and approval status
  const canUserApprove = (record, approvalType) => {
    if (!userRole) return false
    
    // Check if already approved by this role
    if (approvalType === 'admin_sales') {
      return userRole === 'admin_sales' && !record.approved_by_admin_sales
    } else if (approvalType === 'sco') {
      return userRole === 'sco' && !record.approved_by_sco
    }
    
    return false
  }

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

    // Use V2 API endpoint for sales-order-app
    fetch(`/api/accurate/v2/sales-order-app?${params}`)
      .then(res => res.json())
      .then(json => {
        if (json.status === 'success') {
          setSalesOrders(json.data || [])
          setTotalData(json.data.total || 0)
          setCurrentPage(json.data.current_page || 1)
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
    // Use V2 API endpoint for detail
    fetch(`/api/accurate/v2/sales-order-app/${id}`)
      .then(res => res.json())
      .then(json => {
        if (json.status === "success") {
          setDetailData(json.items || [])
        } else {
          message.warning("Gagal mengambil detail")
        }
      })
      .catch(() => message.error("Gagal mengambil data detail"))
      .finally(() => setLoadingDetail(false))
  }

  const handleApprove = (order, role = null) => {
    setSelectedOrder({ ...order, approvalRole: role })
    setApprovalModalVisible(true)
  }

  const handleReject = (order, role = null) => {
    setSelectedOrder({ ...order, rejectionRole: role })
    setRejectionModalVisible(true)
  }

  const confirmApproval = () => {
    if (!selectedOrder) return

    const payload = {
      approval_notes: approvalNotes,
      role: selectedOrder.approvalRole || userRole // Include role information
    }

    fetch(`/api/accurate/v2/sales-order-app/${selectedOrder.id}/approve`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      },
      body: JSON.stringify(payload)
    })
      .then(res => res.json())
      .then(json => {
        if (json.status === 'success') {
          message.success(`Sales order berhasil disetujui oleh ${selectedOrder.approvalRole || userRole}`)
          setApprovalModalVisible(false)
          setApprovalNotes('')
          fetchData(currentPage, search, {
            status: statusFilter,
            customer_no: customerFilter,
            date_from: dateRange?.[0]?.format('YYYY-MM-DD'),
            date_to: dateRange?.[1]?.format('YYYY-MM-DD')
          })
        } else {
          message.error(json.message || 'Gagal menyetujui sales order')
        }
      })
      .catch(() => message.error('Terjadi kesalahan saat menyetujui sales order'))
  }

  const confirmRejection = () => {
    if (!selectedOrder || !rejectionReason.trim()) {
      message.warning('Alasan penolakan harus diisi')
      return
    }

    const payload = {
      rejection_reason: rejectionReason,
      role: selectedOrder.rejectionRole || userRole // Include role information
    }

    fetch(`/api/accurate/v2/sales-order-app/${selectedOrder.id}/reject`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      },
      body: JSON.stringify(payload)
    })
      .then(res => res.json())
      .then(json => {
        if (json.status === 'success') {
          message.success(`Sales order berhasil ditolak oleh ${selectedOrder.rejectionRole || userRole}`)
          setRejectionModalVisible(false)
          setRejectionReason('')
          fetchData(currentPage, search, {
            status: statusFilter,
            customer_no: customerFilter,
            date_from: dateRange?.[0]?.format('YYYY-MM-DD'),
            date_to: dateRange?.[1]?.format('YYYY-MM-DD')
          })
        } else {
          message.error(json.message || 'Gagal menolak sales order')
        }
      })
      .catch(() => message.error('Terjadi kesalahan saat menolak sales order'))
  }

  const handleEdit = (order) => {
    navigate(`/accurate-integration/sales-order-app/${order.id}`)
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



  const getStatusColor = (status) => {
    switch (status) {
      case 'draft': return 'default'
      case 'approved': return 'success'
      case 'rejected': return 'error'
      case 'processing': return 'processing'
      case 'shipped': return 'warning'
      case 'delivered': return 'success'
      case 'cancelled': return 'error'
      default: return 'default'
    }
  }

  const getStatusText = (status) => {
    switch (status) {
      case 'draft': return 'Draft'
      case 'approved': return 'Disetujui'
      case 'rejected': return 'Ditolak'
      case 'processing': return 'Diproses'
      case 'shipped': return 'Dikirim'
      case 'delivered': return 'Diterima'
      case 'cancelled': return 'Dibatalkan'
      default: return status
    }
  }

  const columns = [
    {
      title: "No. Order",
      dataIndex: "order_number",
      key: "order_number",
      width: 150,
    },
    {
      title: "Customer",
      dataIndex: "customer_name",
      key: "customer_name",
      width: 200,
    },
    {
      title: "Nama Toko",
      dataIndex: "nama_toko",
      key: "nama_toko",
      width: 200,
    },
    {
      title: "Created By",
      dataIndex: "created_by",
      key: "created_by",
      width: 200,
    },
    {
      title: "Tanggal Transaksi",
      dataIndex: "date_transaction",
      key: "date_transaction",
      width: 150,
      render: (date) => dayjs(date).format("DD/MM/YYYY"),
    },
    {
      title: "Total",
      dataIndex: "grand_total",
      key: "grand_total",
      width: 120,
      render: (value) => `Rp ${Number(value || 0).toLocaleString('id-ID')}`,
    },
    {
      title: "Status",
      dataIndex: "status",
      key: "status",
      width: 120,
      render: (status) => (
        <Tag color={getStatusColor(status)}>
          {getStatusText(status)}
        </Tag>
      ),
    },
    {
      title: "No Referensi",
      dataIndex: "reference_number",
      key: "reference_number",
      width: 150,
      render: (value) => value || '-',
    },
    // {
    //   title: "Tanggal Transaksi",
    //   dataIndex: "date_transaction",
    //   key: "date_transaction",
    //   width: 150,
    //   render: (value) => value || '-',
    // },
    {
      title: "Tanggal Pengiriman",
      dataIndex: "delivery_date",
      key: "delivery_date",
      width: 150,
      render: (value) => value || '-',
    },
    {
      title: "Alamat Pengiriman",
      dataIndex: "delivery_address",
      key: "delivery_address",
      width: 150,
      render: (value) => value || '-',
    },
    {
      title: "Catatan",
      dataIndex: "notes",
      key: "notes",
      width: 150,
      render: (value) => value || '-',
    },
    {
      title: "Admin Sales",
      dataIndex: "approved_by_admin_sales",
      key: "approved_by_admin_sales",
      width: 150,
      render: (value) => value || '-',
    },
    {
      title: "Tanggal Admin Sales",
      dataIndex: "approved_at_admin_sales",
      key: "approved_at_admin_sales",
      width: 150,
      render: (date) => date ? dayjs(date).format("DD/MM/YYYY HH:mm") : '-',
    },
    {
      title: "SCO",
      dataIndex: "approved_by_sco",
      key: "approved_by_sco",
      width: 150,
      render: (value) => value || '-',
    },
    {
      title: "Tanggal SCO",
      dataIndex: "approved_at_sco",
      key: "approved_at_sco",
      width: 150,
      render: (date) => date ? dayjs(date).format("DD/MM/YYYY HH:mm") : '-',
    },
    {
      title: "Aksi",
      key: "action",
      width: 200,
      render: (_, record) => (
        <div className="flex gap-2">
          {/* Detail selalu ada */}
          <Button
            type="primary"
            size="small"
            icon={<EyeOutlined />}
            onClick={() => showDetail(record.id, record.order_number)}
          >
            Detail
          </Button>

          {/* waiting-approval: Setujui & Tolak - Role-based visibility */}
          {record.status === 'waiting-approval' && (
            <>
              {/* Admin Sales Approval */}
              {canUserApprove(record, 'admin_sales') && (
                <>
                  <Button
                    type="primary"
                    size="small"
                    icon={<CheckOutlined />}
                    onClick={() => handleApprove(record, 'admin_sales')}
                    style={{ backgroundColor: '#52c41a', borderColor: '#52c41a' }}
                  >
                    Setujui (Admin Sales)
                  </Button>
                  <Button
                    type="primary"
                    size="small"
                    icon={<CloseOutlined />}
                    onClick={() => handleReject(record, 'admin_sales')}
                    danger
                  >
                    Tolak (Admin Sales)
                  </Button>
                </>
              )}

              {/* SCO Approval */}
              {canUserApprove(record, 'sco') && (
                <>
                  <Button
                    type="primary"
                    size="small"
                    icon={<CheckOutlined />}
                    onClick={() => handleApprove(record, 'sco')}
                    style={{ backgroundColor: '#52c41a', borderColor: '#52c41a' }}
                  >
                    Setujui (SCO)
                  </Button>
                  <Button
                    type="primary"
                    size="small"
                    icon={<CloseOutlined />}
                    onClick={() => handleReject(record, 'sco')}
                    danger
                  >
                    Tolak (SCO)
                  </Button>
                </>
              )}
            </>
          )}

          {/* draft & rejected: Edit */}
          {(record.status === 'draft' || record.status === 'rejected') && (
            <Button
              type="default"
              size="small"
              icon={<EditOutlined />}
              onClick={() => handleEdit(record)}
            >
              Edit
            </Button>
          )}

          {/* approved → hanya detail (sudah otomatis karena default button detail di atas) */}
        </div>
      ),
    },
  ]

  const detailColumns = [
    {
      title: "Kode Produk",
      dataIndex: "product_code",
      key: "product_code",
    },
    {
      title: "Nama Produk",
      dataIndex: "product_name",
      key: "product_name",
    },
    {
      title: "Qty",
      dataIndex: "qty",
      key: "qty",
    },
    {
      title: "Harga",
      dataIndex: "price",
      key: "price",
      render: (value) => `Rp ${Number(value || 0).toLocaleString('id-ID')}`,
    },
    {
      title: "Diskon (%)",
      dataIndex: "discount_percent",
      key: "discount_percent",
      render: (value) => `${value || 0}%`,
    },
    {
      title: "Total",
      dataIndex: "line_total",
      key: "line_total",
      render: (value) => `Rp ${Number(value || 0).toLocaleString('id-ID')}`,
    },
  ]

  useEffect(() => {
    fetchData()
  }, [])

  return (
    <Layout>
      <div className="p-6">
        <div className="flex justify-between items-center mb-6">
          <h1 className="text-2xl font-bold">Sales Order App (Dengan Approval)</h1>
          <div className="flex gap-2 items-center">
            <div className="flex flex-col">
              <label className="text-xs text-gray-500 mb-1">Role Saat Ini:</label>
              <Select
                value={userRole}
                onChange={handleRoleChange}
                style={{ width: 150 }}
                size="small"
              >
                <Option value="admin_sales">Admin Sales</Option>
                <Option value="sco">SCO</Option>
              </Select>
            </div>
            <Button
              type="primary"
              icon={<ReloadOutlined />}
              onClick={handleSync}
              loading={syncing}
            >
              Sync Data
            </Button>
          </div>
        </div>

        <div className="bg-white p-4 rounded-lg shadow mb-6">
          <div className="flex flex-wrap gap-4 items-end">
            <div className="flex-1 min-w-[200px]">
              <label className="block text-sm font-medium mb-1">Pencarian</label>
              <Input
                placeholder="Cari berdasarkan nomor order, customer, dll..."
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                onPressEnter={handleSearch}
                suffix={<SearchOutlined />}
              />
            </div>

            <div className="min-w-[200px]">
              <label className="block text-sm font-medium mb-1">Rentang Tanggal</label>
              <RangePicker
                value={dateRange}
                onChange={setDateRange}
                format="DD/MM/YYYY"
                className="w-full"
              />
            </div>

            <div className="min-w-[150px]">
              <label className="block text-sm font-medium mb-1">Status</label>
              <Select
                placeholder="Pilih Status"
                value={statusFilter}
                onChange={setStatusFilter}
                allowClear
                className="w-full"
              >
                <Option value="draft">Draft</Option>
                <Option value="approved">Disetujui</Option>
                <Option value="rejected">Ditolak</Option>
                <Option value="processing">Diproses</Option>
                <Option value="shipped">Dikirim</Option>
                <Option value="delivered">Diterima</Option>
                <Option value="cancelled">Dibatalkan</Option>
              </Select>
            </div>

            <Button type="primary" onClick={handleSearch} icon={<SearchOutlined />}>
              Cari
            </Button>

            {(search || dateRange || statusFilter || customerFilter) && (
              <Button
                onClick={() => {
                  setSearch("")
                  setDateRange(null)
                  setStatusFilter(null)
                  setCustomerFilter(null)
                  setIsSearch(false)
                  fetchData(1)
                }}
                icon={<CloseCircleFilled />}
              >
                Reset
              </Button>
            )}
          </div>
        </div>

        <div className="bg-white rounded-lg shadow">
          <Table
            columns={columns}
            dataSource={salesOrders?.data || []}
            loading={loading}
            pagination={false}
            rowKey="id"
            scroll={{ x: 1200 }}
          />

          {totalData > 0 && (
            <div className="p-4 border-t">
              <Pagination
                current={currentPage}
                total={totalData}
                pageSize={perPage}
                onChange={handlePageChange}
                showSizeChanger={false}
                showQuickJumper
                showTotal={(total, range) =>
                  `${range[0]}-${range[1]} dari ${total} data`
                }
              />
            </div>
          )}
        </div>
      </div>

      {/* Detail Modal */}
      <Modal
        title={`Detail Sales Order - ${selectedOrderNumber}`}
        open={detailVisible}
        onCancel={() => setDetailVisible(false)}
        footer={null}
        width={800}
      >
        <Table
          columns={detailColumns}
          dataSource={detailData}
          loading={loadingDetail}
          pagination={false}
          rowKey="id"
          size="small"
        />
      </Modal>

      {/* Approval Modal */}
      <Modal
        title="Setujui Sales Order"
        open={approvalModalVisible}
        onOk={confirmApproval}
        onCancel={() => {
          setApprovalModalVisible(false)
          setApprovalNotes('')
        }}
        okText="Setujui"
        cancelText="Batal"
      >
        <div className="mb-4">
          <p><strong>Order Number:</strong> {selectedOrder?.order_number}</p>
          <p><strong>Customer:</strong> {selectedOrder?.customer_name}</p>
          <p><strong>Total:</strong> Rp {Number(selectedOrder?.grand_total || 0).toLocaleString('id-ID')}</p>
        </div>

        <div>
          <label className="block text-sm font-medium mb-2">Catatan Persetujuan (Opsional)</label>
          <TextArea
            rows={4}
            value={approvalNotes}
            onChange={(e) => setApprovalNotes(e.target.value)}
            placeholder="Masukkan catatan persetujuan..."
          />
        </div>
      </Modal>

      {/* Rejection Modal */}
      <Modal
        title="Tolak Sales Order"
        open={rejectionModalVisible}
        onOk={confirmRejection}
        onCancel={() => {
          setRejectionModalVisible(false)
          setRejectionReason('')
        }}
        okText="Tolak"
        cancelText="Batal"
        okButtonProps={{ danger: true }}
      >
        <div className="mb-4">
          <p><strong>Order Number:</strong> {selectedOrder?.order_number}</p>
          <p><strong>Customer:</strong> {selectedOrder?.customer_name}</p>
          <p><strong>Total:</strong> Rp {Number(selectedOrder?.grand_total || 0).toLocaleString('id-ID')}</p>
        </div>

        <div>
          <label className="block text-sm font-medium mb-2">Alasan Penolakan <span className="text-red-500">*</span></label>
          <TextArea
            rows={4}
            value={rejectionReason}
            onChange={(e) => setRejectionReason(e.target.value)}
            placeholder="Masukkan alasan penolakan..."
            required
          />
        </div>
      </Modal>
    </Layout>
  )
}

export default SalesOrderApp