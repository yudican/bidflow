import React, { useEffect, useState } from "react"
import {
  Table,
  Button,
  Input,
  Pagination,
  Tag,
  message,
  Card,
  Row,
  Col,
} from "antd"
import { ReloadOutlined, SearchOutlined, UserOutlined } from "@ant-design/icons"
import Layout from "../../components/layout"

const AccurateCustomerManagement = () => {
  const [customers, setCustomers] = useState([])
  const [loading, setLoading] = useState(true)
  const [search, setSearch] = useState("")
  const [pagination, setPagination] = useState({
    current: 1,
    pageSize: 20,
    total: 0,
  })

  // Fetch customers data
  const fetchCustomers = (page = 1, searchQuery = "") => {
    setLoading(true)
    const params = new URLSearchParams({
      page: page.toString(),
      per_page: pagination.pageSize.toString(),
    })

    if (searchQuery) params.append("search", searchQuery)

    fetch(`/api/accurate/customers?${params}`)
      .then((res) => res.json())
      .then((json) => {
        if (json.status === "success") {
          setCustomers(json.data || [])
          setPagination({
            ...pagination,
            current: json.data.current_page,
            total: json.data.total,
          })
        } else {
          message.error("Gagal mengambil data customer")
        }
      })
      .catch(() => message.error("Terjadi kesalahan"))
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    fetchCustomers()
  }, [])

  const handleSearch = () => {
    fetchCustomers(1, search)
  }

  const handlePaginationChange = (page, pageSize) => {
    setPagination({ ...pagination, pageSize })
    fetchCustomers(page, search)
  }

  const columns = [
    {
      title: "Customer No",
      dataIndex: "customer_no",
      key: "customer_no",
      width: 120,
    },
    {
      title: "Nama Customer",
      dataIndex: "name",
      key: "name",
      render: (text) => <span className="font-medium">{text}</span>,
    },
    {
      title: "Email",
      dataIndex: "email",
      key: "email",
      render: (text) => text || "-",
    },
    {
      title: "Kategori",
      dataIndex: "category_name",
      key: "category_name",
      render: (text) => (text ? <Tag color="blue">{text}</Tag> : "-"),
    },
    {
      title: "NPWP",
      dataIndex: "npwp_no",
      key: "npwp_no",
      render: (text) => text || "-",
    },
    {
      title: "Telepon",
      dataIndex: "work_phone",
      key: "work_phone",
      render: (text) => text || "-",
    },
    {
      title: "Kota",
      dataIndex: "ship_city",
      key: "ship_city",
      render: (text) => text || "-",
    },
    {
      title: "Warehouse",
      dataIndex: "warehouse_name",
      key: "warehouse_name",
      render: (text) => text || "-",
    },
  ]

  return (
    <Layout title="Customer Management">
      <div className="space-y-6">
        {/* Stats Card */}
        <Row gutter={16}>
          <Col span={6}>
            <Card>
              <div className="flex items-center">
                <UserOutlined className="text-2xl text-blue-500 mr-3" />
                <div>
                  <div className="text-2xl font-bold">{pagination.total}</div>
                  <div className="text-gray-500">Total Customers</div>
                </div>
              </div>
            </Card>
          </Col>
        </Row>

        {/* Search Filter */}
        <Card>
          <div className="flex gap-4 items-center">
            <div className="flex-1">
              <Input.Search
                placeholder="Cari nama, customer no, email, NPWP..."
                allowClear
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                onSearch={handleSearch}
                style={{ width: "100%" }}
                enterButton={<SearchOutlined />}
              />
            </div>
            <Button
              icon={<ReloadOutlined />}
              onClick={() => fetchCustomers(1, search)}
            >
              Refresh
            </Button>
          </div>
        </Card>

        {/* Table */}
        <Card>
          <Table
            rowKey="id"
            columns={columns}
            dataSource={customers}
            loading={loading}
            pagination={false}
            scroll={{ x: "max-content" }}
            size="middle"
          />

          <div className="flex justify-between items-center mt-4">
            <div className="text-gray-500">
              Menampilkan {customers.length} dari {pagination.total} data
            </div>
            <Pagination
              current={pagination.current}
              total={pagination.total}
              pageSize={pagination.pageSize}
              showSizeChanger
              showQuickJumper
              showTotal={(total, range) =>
                `${range[0]}-${range[1]} dari ${total} data`
              }
              onChange={handlePaginationChange}
              onShowSizeChange={handlePaginationChange}
            />
          </div>
        </Card>
      </div>
    </Layout>
  )
}

export default AccurateCustomerManagement
