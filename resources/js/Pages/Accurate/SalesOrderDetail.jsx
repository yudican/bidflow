import {
  CheckOutlined,
  CloseOutlined,
  FileTextOutlined,
  ClockCircleOutlined,
  EditOutlined,
} from '@ant-design/icons'
import {
  Card,
  Input,
  message,
  Modal,
  Spin,
  Table,
  Tag,
  Tooltip,
  Form,
  Button,
  DatePicker,
} from 'antd'
import dayjs from 'dayjs'
import 'dayjs/locale/id'
import React, { useEffect, useState } from 'react'
import { useParams } from 'react-router-dom'
import Layout from '../../components/layout'

dayjs.locale('id')
const { TextArea } = Input

const SalesOrderDetail = () => {
  const params = useParams()
  const [orderDetail, setOrderDetail] = useState(null)
  const [loading, setLoading] = useState(true)
  const [loadingApproval, setLoadingApproval] = useState(false)
  const [approvalModalVisible, setApprovalModalVisible] = useState(false)
  const [rejectModalVisible, setRejectModalVisible] = useState(false)
  const [approvalNotes, setApprovalNotes] = useState('')
  const [rejectionReason, setRejectionReason] = useState('')
  const [items, setItems] = useState([])
  const [form] = Form.useForm()

  const [isEditingOrder, setIsEditingOrder] = useState(false)
  const [editOrder, setEditOrder] = useState({
    date_transaction: null,
    delivery_date: null,
    delivery_address: '',
    sales_name: '',
  })

  const fetchOrderDetail = () => {
    setLoading(true)
    fetch(`/api/accurate/v2/sales-order-app/${params.id}`)
      .then((res) => res.json())
      .then((json) => {
        if (json.status === 'success') {
          const detail = { ...(json?.order || {}), items: json.items }
          setOrderDetail(detail)
          setEditOrder({
            date_transaction: detail.date_transaction,
            delivery_date: detail.delivery_date,
            delivery_address: detail.delivery_address,
            sales_name: detail.sales_name,
          })
          form.setFieldsValue({ items: json.items })
        } else {
          message.error(json.message || 'Gagal memuat detail sales order')
        }
      })
      .catch(() => message.error('Gagal memuat detail sales order'))
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    fetchOrderDetail()
  }, [params.id])

  useEffect(() => {
    if (orderDetail?.items) {
      setItems(
        orderDetail.items.map((item) => ({
          ...item,
          qty: Number(item.qty || 0),
          price: Number(item.price || 0),
          discount_percent: Number(item.discount_percent || 0),
        }))
      )
    }
  }, [orderDetail])

  const handleApprove = () => {
    setLoadingApproval(true)
    fetch(`/api/accurate/v2/sales-order-app/${params.id}/approve`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ notes: approvalNotes }),
    })
      .then((res) => res.json())
      .then((json) => {
        if (json.status === 'success') {
          message.success('Sales order berhasil disetujui')
          setApprovalModalVisible(false)
          setApprovalNotes('')
          fetchOrderDetail()
        } else {
          message.error(json.message || 'Gagal menyetujui sales order')
        }
      })
      .catch(() => message.error('Gagal menyetujui sales order'))
      .finally(() => setLoadingApproval(false))
  }

  const handleReject = () => {
    setLoadingApproval(true)
    fetch(`/api/accurate/v2/sales-order-app/${params.id}/reject`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ reason: rejectionReason }),
    })
      .then((res) => res.json())
      .then((json) => {
        if (json.status === 'success') {
          message.success('Sales order berhasil ditolak')
          setRejectModalVisible(false)
          setRejectionReason('')
          fetchOrderDetail()
        } else {
          message.error(json.message || 'Gagal menolak sales order')
        }
      })
      .catch(() => message.error('Gagal menolak sales order'))
      .finally(() => setLoadingApproval(false))
  }

  const formatRupiah = (val) =>
    parseFloat(val || 0).toLocaleString('id-ID', {
      minimumFractionDigits: 0,
    })

  const formatTanggal = (date, withTime = false) =>
    date
      ? dayjs(date).format(withTime ? 'D MMMM YYYY HH:mm' : 'D MMMM YYYY')
      : '-'

  const getStatusColor = (status) => {
    const map = {
      draft: 'default',
      confirmed: 'processing',
      processing: 'orange',
      shipped: 'purple',
      delivered: 'success',
      cancelled: 'error',
    }
    return map[status] || 'default'
  }

  const handleItemChange = (index, field, value) => {
    const updatedItems = [...items]
    updatedItems[index][field] = Number(value) || 0
    const qty = updatedItems[index].qty
    const price = updatedItems[index].price
    const discPercent = updatedItems[index].discount_percent
    const gross = qty * price
    const discAmount = gross * (discPercent / 100)
    updatedItems[index].discount_amount = discAmount
    updatedItems[index].line_total = gross - discAmount
    setItems(updatedItems)
  }

  const handleSaveChanges = async () => {
    try {
      // Simpan perubahan info order
      await fetch(`/api/accurate/v2/sales-order-app/${params.id}/update-order`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(editOrder),
      })

      // Simpan perubahan item
      await fetch(`/api/accurate/v2/sales-order-app/${params.id}/update-items`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ items }),
      })

      message.success('Perubahan berhasil disimpan')
      setIsEditingOrder(false)
      fetchOrderDetail()
    } catch (err) {
      message.error('Terjadi kesalahan saat menyimpan perubahan')
    }
  }

  const canApprove = () => orderDetail?.status === 'draft'

  const rightContent = (
    <div className="flex gap-3">
      {canApprove() && (
        <>
          <Tooltip title="Setujui Sales Order">
            <Button
              icon={<CheckOutlined />}
              className="bg-emerald-600 text-white hover:bg-emerald-700"
              onClick={() => setApprovalModalVisible(true)}
            >
              Approve
            </Button>
          </Tooltip>
          <Tooltip title="Tolak Sales Order">
            <Button
              icon={<CloseOutlined />}
              className="bg-red-600 text-white hover:bg-red-700"
              onClick={() => setRejectModalVisible(true)}
            >
              Reject
            </Button>
          </Tooltip>
        </>
      )}
    </div>
  )

  if (loading) {
    return (
      <Layout
        title={
          <>
            <FileTextOutlined className="mr-2" /> Detail Sales Order
          </>
        }
        rightContent={rightContent}
      >
        <div className="text-center py-12">
          <Spin size="large" tip="Memuat detail sales order..." />
        </div>
      </Layout>
    )
  }

  if (!orderDetail) {
    return (
      <Layout title="Detail Sales Order">
        <div className="text-center py-12">
          <p>Data sales order tidak ditemukan</p>
        </div>
      </Layout>
    )
  }

  const itemColumns = [
    {
      title: 'Kode Produk',
      dataIndex: 'product_code',
      key: 'product_code',
      render: (_, __, index) => (
        <Form.Item
          name={['items', index, 'product_code']}
          style={{ marginBottom: 0 }}
        >
          <Input disabled />
        </Form.Item>
      ),
    },
    {
      title: 'Nama Produk',
      dataIndex: 'product_name',
      key: 'product_name',
      render: (_, __, index) => (
        <Form.Item
          name={['items', index, 'product_name']}
          style={{ marginBottom: 0 }}
        >
          <Input disabled />
        </Form.Item>
      ),
    },
    {
      title: 'Qty',
      dataIndex: 'qty',
      key: 'qty',
      render: (_, __, index) => (
        <Form.Item style={{ marginBottom: 0 }}>
          <Input
            type="number"
            min={1}
            value={items[index]?.qty}
            onChange={(e) => handleItemChange(index, 'qty', e.target.value)}
          />
        </Form.Item>
      ),
    },
    {
      title: 'Harga Satuan',
      dataIndex: 'price',
      key: 'price',
      render: (_, __, index) => (
        <Form.Item style={{ marginBottom: 0 }}>
          <Input
            type="number"
            min={0}
            value={items[index]?.price}
            onChange={(e) => handleItemChange(index, 'price', e.target.value)}
          />
        </Form.Item>
      ),
    },
    {
      title: 'Diskon (%)',
      dataIndex: 'discount_percent',
      key: 'discount_percent',
      render: (_, __, index) => (
        <Form.Item style={{ marginBottom: 0 }}>
          <Input
            type="number"
            min={0}
            max={100}
            value={items[index]?.discount_percent}
            onChange={(e) =>
              handleItemChange(index, 'discount_percent', e.target.value)
            }
          />
        </Form.Item>
      ),
    },
    {
      title: 'Total',
      dataIndex: 'line_total',
      key: 'line_total',
      render: (_, __, index) =>
        `Rp ${formatRupiah(items[index]?.line_total || 0)}`,
    },
  ]

  return (
    <Layout
      title={
        <>
          <FileTextOutlined className="mr-2" /> Detail Sales Order
        </>
      }
      rightContent={rightContent}
      href="/accurate-integration/sales-order-app"
      lastItemLabel={orderDetail?.order_number}
    >
      {/* Order Info */}
      <Card
        title="🧾 Order Information"
        className="mb-4"
        extra={
          <Button
            type="default"
            icon={<EditOutlined />}
            onClick={() => setIsEditingOrder(!isEditingOrder)}
          >
            {isEditingOrder ? 'Batal Edit' : 'Edit'}
          </Button>
        }
      >
        <div className="grid md:grid-cols-2 gap-6 text-sm md:text-base">
          <div className="space-y-3">
            <div className="flex justify-between border-b pb-1">
              <span className="text-gray-500">Order Number :</span>
              <span className="font-medium">{orderDetail?.order_number}</span>
            </div>

            <div className="flex justify-between items-center border-b pb-1 gap-2">
              <span className="text-gray-500">Tanggal Transaksi :</span>
              {isEditingOrder ? (
                <DatePicker
                  value={
                    editOrder.date_transaction
                      ? dayjs(editOrder.date_transaction)
                      : null
                  }
                  format="DD/MM/YYYY"
                  onChange={(date) =>
                    setEditOrder({
                      ...editOrder,
                      date_transaction: date?.format('YYYY-MM-DD'),
                    })
                  }
                />
              ) : (
                <span>{formatTanggal(orderDetail?.date_transaction)}</span>
              )}
            </div>

            <div className="flex justify-between items-center border-b pb-1 gap-2">
              <span className="text-gray-500">Tanggal Kirim :</span>
              {isEditingOrder ? (
                <DatePicker
                  value={
                    editOrder.delivery_date
                      ? dayjs(editOrder.delivery_date)
                      : null
                  }
                  format="DD/MM/YYYY"
                  onChange={(date) =>
                    setEditOrder({
                      ...editOrder,
                      delivery_date: date?.format('YYYY-MM-DD'),
                    })
                  }
                />
              ) : (
                <span>{formatTanggal(orderDetail?.delivery_date)}</span>
              )}
            </div>

            <div className="flex flex-col gap-1 border-b pb-2">
              <span className="text-gray-500 text-sm">Status :</span>
              <div className="flex items-center justify-between">
                <Tag
                  icon={<ClockCircleOutlined />}
                  color={getStatusColor(orderDetail?.status)}
                  className="text-xs px-2 py-0.5 rounded-md"
                >
                  {orderDetail?.status?.toUpperCase() || 'DRAFT'}
                </Tag>
              </div>
            </div>
          </div>

          <div className="space-y-3">
            <div className="flex justify-between border-b pb-1">
              <span className="text-gray-500">Customer :</span>
              <span className="font-medium">
                {orderDetail?.customer_email}
              </span>
            </div>

            <div className="flex justify-between items-center border-b pb-1 gap-2">
              <span className="text-gray-500">Alamat Kirim :</span>
              {isEditingOrder ? (
                <Input
                  value={editOrder.delivery_address || ''}
                  onChange={(e) =>
                    setEditOrder({
                      ...editOrder,
                      delivery_address: e.target.value,
                    })
                  }
                />
              ) : (
                <span className="text-sm">
                  {orderDetail?.delivery_address || '-'}
                </span>
              )}
            </div>

            <div className="flex justify-between items-center border-b pb-1 gap-2">
              <span className="text-gray-500">Sales :</span>
              {isEditingOrder ? (
                <Input
                  value={editOrder.sales_name || ''}
                  onChange={(e) =>
                    setEditOrder({
                      ...editOrder,
                      sales_name: e.target.value,
                    })
                  }
                />
              ) : (
                <span className="text-sm">
                  {orderDetail?.sales_name || '-'}
                </span>
              )}
            </div>
          </div>
        </div>
      </Card>

      {/* Items */}
      <Card title="📦 Product Items" className="mb-4">
        <div className="mb-3 text-gray-500">
          Penambahan produk tidak tersedia. Edit melalui Product Items.
        </div>
        <Form form={form} layout="vertical">
          <Table
            columns={itemColumns}
            dataSource={items}
            rowKey={(record, i) => `${record.product_id}-${i}`}
            pagination={false}
            scroll={{ x: 'max-content' }}
            rowClassName={(_, index) => (index % 2 === 0 ? 'bg-gray-50' : '')}
            summary={() => {
              let total = 0
              let diskon = 0
              items.forEach(({ line_total, discount_amount }) => {
                total += parseFloat(line_total || 0)
                diskon += parseFloat(discount_amount || 0)
              })
              return (
                <Table.Summary.Row className="bg-gray-100 font-semibold">
                  <Table.Summary.Cell colSpan={4}>Total</Table.Summary.Cell>
                  <Table.Summary.Cell align="right">
                    Rp {formatRupiah(diskon)}
                  </Table.Summary.Cell>
                  <Table.Summary.Cell align="right">
                    Rp {formatRupiah(total)}
                  </Table.Summary.Cell>
                </Table.Summary.Row>
              )
            }}
          />
          <Button type="primary" className="mt-4" onClick={handleSaveChanges}>
            Save Changes
          </Button>
        </Form>
      </Card>

      {/* Modal Approve */}
      <Modal
        title="Approve Sales Order"
        open={approvalModalVisible}
        onOk={handleApprove}
        onCancel={() => {
          setApprovalModalVisible(false)
          setApprovalNotes('')
        }}
        confirmLoading={loadingApproval}
        okText="Approve"
        cancelText="Cancel"
      >
        <p className="mb-2">Yakin ingin menyetujui Sales Order ini?</p>
        <p className="text-sm text-gray-500 mb-3">
          Order Number: <strong>{orderDetail?.order_number}</strong>
        </p>
        <TextArea
          placeholder="Catatan persetujuan (opsional)..."
          rows={3}
          value={approvalNotes}
          onChange={(e) => setApprovalNotes(e.target.value)}
        />
      </Modal>

      {/* Modal Reject */}
      <Modal
        title="Reject Sales Order"
        open={rejectModalVisible}
        onOk={handleReject}
        onCancel={() => {
          setRejectModalVisible(false)
          setRejectionReason('')
        }}
        confirmLoading={loadingApproval}
        okText="Reject"
        cancelText="Cancel"
        okButtonProps={{ disabled: !rejectionReason.trim() }}
      >
        <p className="mb-2">Yakin ingin menolak Sales Order ini?</p>
        <p className="text-sm text-gray-500 mb-3">
          Order Number: <strong>{orderDetail?.order_number}</strong>
        </p>
        <TextArea
          placeholder="Berikan alasan penolakan..."
          rows={3}
          value={rejectionReason}
          onChange={(e) => setRejectionReason(e.target.value)}
          required
        />
      </Modal>
    </Layout>
  )
}

export default SalesOrderDetail
