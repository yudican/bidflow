import React, { useEffect, useState } from "react"
import { Table, message, Button, Input } from "antd"
import { ReloadOutlined } from "@ant-design/icons"
import Layout from "../../components/layout"

const AccurateCustomer = () => {
    const [customers, setCustomers] = useState([])
    const [loading, setLoading] = useState(true)
    const [syncing, setSyncing] = useState(false)
    const [search, setSearch] = useState("")
    const [currentPage, setCurrentPage] = useState(1)
    const [pageSize, setPageSize] = useState(10)
    const [lastSync, setLastSync] = useState(null)

    const getCustomers = () => {
        fetch("/api/accurate/customer")
            .then((res) => res.json())
            .then((json) => {
                setCustomers(json.data)
                setLoading(false)
            })
            .catch(() => {
                message.error("Gagal mengambil data customer")
                setLoading(false)
            })
    }

    const getLastSync = () => {
        fetch("/api/accurate/last-sync-customer")
            .then((res) => res.json())
            .then((json) => {
                setLastSync(json.last_sync)
            })
            .catch(() => {
                setLastSync(null)
            })
    }

    const handleSync = () => {
        setSyncing(true)
        fetch("/api/accurate/sync-customer", {
            method: "POST",
        })
            .then((res) => res.json())
            .then((json) => {
                if (json.status === "success") {
                    message.success(json.message || "Sinkronisasi berhasil")
                    getCustomers()
                    getLastSync()
                } else {
                    message.warning(json.message || "Gagal sinkronisasi")
                }
            })
            .catch(() => {
                message.error("Terjadi kesalahan saat sinkronisasi")
            })
            .finally(() => setSyncing(false))
    }

    useEffect(() => {
        getCustomers()
        getLastSync()
    }, [])

    const columns = [
        {
            title: "No",
            key: "no",
            render: (_, __, index) => (currentPage - 1) * pageSize + index + 1,
        },
        {
            title: "Customer Code",
            dataIndex: "customer_no",
            key: "customer_no",
        },
        {
            title: "Customer Name",
            dataIndex: "name",
            key: "name",
        },
        {
            title: "Customer Type",
            dataIndex: "customer_type",
            key: "customer_type",
            render: (text) => text || "-",
        },
        {
            title: "Address",
            dataIndex: "ship_street",
            key: "ship_street",
            render: (text) => text || "-",
        },
        {
            title: "PIC Name",
            dataIndex: "wp_name",
            key: "wp_name",
            render: (text) => text || "-",
        },
        {
            title: "Telp",
            dataIndex: "work_phone",
            key: "work_phone",
            render: (text) => text || "-",
        },
    ]

    const filteredCustomers = customers.filter((cust) =>
        [
            cust.customer_no,
            cust.name,
            cust.category_name,
            cust.wp_name,
            cust.ship_street,
            cust.work_phone,
        ]
            .join(" ")
            .toLowerCase()
            .includes(search.toLowerCase())
    )

    return (
        <Layout title="Customer Accurate">
            <div className="card">
                <div className="card-body">
                    <div className="mb-4 flex justify-between items-center gap-4 flex-wrap">
                        <div className="flex items-center gap-3 flex-wrap">
                            <div>
                                Total Customer:{" "}
                                <strong>{filteredCustomers.length}</strong>
                            </div>
                            <Input.Search
                                placeholder="Cari ID, nama, alamat, PIC, telp..."
                                allowClear
                                onChange={(e) => setSearch(e.target.value)}
                                style={{ width: 300 }}
                            />
                        </div>

                        <div className="flex items-center gap-3 flex-wrap">
                            <div className="text-sm text-gray-500">
                                Terakhir Sync:{" "}
                                <strong>
                                    {lastSync
                                        ? new Intl.DateTimeFormat("id-ID", {
                                            day: "2-digit",
                                            month: "long",
                                            year: "numeric",
                                            hour: "2-digit",
                                            minute: "2-digit",
                                            second: "2-digit",
                                            hour12: false,
                                        }).format(new Date(lastSync))
                                        : "Belum pernah"}
                                </strong>
                            </div>
                            <Button
                                type="primary"
                                icon={<ReloadOutlined />}
                                loading={syncing}
                                onClick={handleSync}
                            >
                                Sync Customer
                            </Button>
                        </div>
                    </div>

                    <Table
                        rowKey="accurate_id"
                        columns={columns}
                        dataSource={filteredCustomers}
                        loading={loading}
                        scroll={{ x: "max-content" }}
                        tableLayout="auto"
                        pagination={{
                            current: currentPage,
                            pageSize: pageSize,
                            onChange: (page, size) => {
                                setCurrentPage(page)
                                setPageSize(size)
                            },
                            showSizeChanger: true,
                            pageSizeOptions: ["10", "20", "50", "100"],
                        }}
                    />
                </div>
            </div>
        </Layout>
    )
}

export default AccurateCustomer
