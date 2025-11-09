import React, { useEffect, useState } from "react"
import { Table, message, Button, Input } from "antd"
import { ReloadOutlined } from "@ant-design/icons"
import Layout from "../../components/layout"

const AccurateWarehouse = () => {
    const [warehouses, setWarehouses] = useState([])
    const [loading, setLoading] = useState(true)
    const [syncing, setSyncing] = useState(false)
    const [search, setSearch] = useState("")
    const [currentPage, setCurrentPage] = useState(1)
    const [pageSize, setPageSize] = useState(10)
    const [lastSyncedAt, setLastSyncedAt] = useState(null)

    const getWarehouses = () => {
        fetch("/api/accurate/warehouse")
            .then((res) => res.json())
            .then((json) => {
                setWarehouses(json.data)
                setLoading(false)
            })
            .catch(() => setLoading(false))
    }

    const fetchLastSyncTime = () => {
        fetch("/api/accurate/last-sync-warehouse")
            .then((res) => res.json())
            .then((json) => {
                if (json.status === "success") {
                    setLastSyncedAt(json.last_synced_at)
                }
            })
    }

    const handleSync = () => {
        setSyncing(true)
        fetch("/api/accurate/sync-warehouse", {
            method: "POST",
        })
            .then((res) => res.json())
            .then((json) => {
                if (json.status === "success") {
                    message.success(json.message || "Sinkronisasi berhasil")
                    getWarehouses()
                    fetchLastSyncTime()
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
        getWarehouses()
        fetchLastSyncTime()
    }, [])

    const columns = [
        {
            title: "No",
            key: "no",
            render: (_, __, index) => (currentPage - 1) * pageSize + index + 1,
        },
        {
            title: "Nama",
            dataIndex: "name",
            key: "name",
        },
        {
            title: "Lokasi ID",
            dataIndex: "location_id",
            key: "location_id",
        },
        {
            title: "Deskripsi",
            dataIndex: "address_name",
            key: "address_name",
            render: (text) => text || "-",
        },
    ]

    const filteredWarehouses = warehouses.filter((wh) =>
        [wh.name, wh.address_name, wh.location_id]
            .join(" ")
            .toLowerCase()
            .includes(search.toLowerCase())
    )

    return (
        <Layout title="Warehouse Accurate">
            <div className="card">
                <div className="card-body">
                    <div className="mb-4 flex justify-between items-center gap-4 flex-wrap">
                        <div className="flex items-center gap-3 flex-wrap">
                            <div>
                                Total Warehouse: <strong>{filteredWarehouses.length}</strong>
                            </div>
                            <Input.Search
                                placeholder="Cari nama, lokasi, atau deskripsi..."
                                allowClear
                                onChange={(e) => setSearch(e.target.value)}
                                style={{ width: 250 }}
                            />
                        </div>
                        <div className="flex items-center gap-3">
                            <div className="text-sm text-gray-500">
                                Terakhir Sync:{" "}
                                <strong>
                                    {lastSyncedAt
                                        ? new Intl.DateTimeFormat("id-ID", {
                                            day: "2-digit",
                                            month: "long",
                                            year: "numeric",
                                        }).format(new Date(lastSyncedAt))
                                        : "Belum pernah"}
                                </strong>
                            </div>
                            <Button
                                type="primary"
                                icon={<ReloadOutlined />}
                                loading={syncing}
                                onClick={handleSync}
                            >
                                Sync Warehouse
                            </Button>
                        </div>
                    </div>

                    <Table
                        rowKey="accurate_id"
                        columns={columns}
                        dataSource={filteredWarehouses}
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

export default AccurateWarehouse
