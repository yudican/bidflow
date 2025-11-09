import React, { useEffect, useState } from "react"
import { Table, Input, message, Button, Switch } from "antd"
import { ReloadOutlined } from "@ant-design/icons"
import Layout from "../../components/layout"

const AccurateProduct = () => {
    const [products, setProducts] = useState([])
    const [loading, setLoading] = useState(true)
    const [search, setSearch] = useState("")
    const [syncing, setSyncing] = useState(false)
    const [currentPage, setCurrentPage] = useState(1)
    const [pageSize, setPageSize] = useState(10)
    const [lastSyncedAt, setLastSyncedAt] = useState(null)

    const getProducts = () => {
        fetch("/api/accurate/product")
            .then((res) => res.json())
            .then((json) => {
                setProducts(json.data)
                setLoading(false)
            })
            .catch(() => setLoading(false))
    }

    const fetchLastSyncTime = () => {
        fetch("/api/accurate/last-sync-product")
            .then((res) => res.json())
            .then((json) => {
                if (json.status === "success") {
                    setLastSyncedAt(json.last_synced_at)
                }
            })
    }

    const handleSync = () => {
        setSyncing(true)
        fetch("/api/accurate/sync-product", {
            method: "POST",
        })
            .then((res) => res.json())
            .then((json) => {
                if (json.status === "success") {
                    message.success(json.message || "Sinkronisasi berhasil")
                    getProducts()
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
        getProducts()
        fetchLastSyncTime()
    }, [])

    const handleSwitchChange = (checked, record) => {
        fetch(`/api/accurate/product/${record.accurate_id}/switch`, {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({ is_active: checked }),
        })
            .then((res) => {
                if (!res.ok) throw new Error("Gagal update status")
                return res.json()
            })
            .then(() => {
                message.success(`Status produk ${record.name} diperbarui`)
                setProducts((prev) =>
                    prev.map((p) =>
                        p.accurate_id === record.accurate_id
                            ? { ...p, is_active: checked }
                            : p
                    )
                )
            })
            .catch(() => {
                message.error("Gagal memperbarui status produk")
            })
    }

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
            title: "SKU",
            dataIndex: "item_no",
            key: "item_no",
            render: (text) => text || "-",
        },
        {
            title: "Jenis Barang",
            dataIndex: "item_type_name",
            key: "item_type_name",
            render: (text) => text || "-",
        },
        {
            title: "Satuan",
            dataIndex: "unit1",
            key: "unit1",
            render: (text) => text || "-",
        },
        {
            title: "Stok",
            dataIndex: "stock_quantity",
            key: "stock_quantity",
            align: "right",
            render: (text) =>
                new Intl.NumberFormat("id-ID", {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 2,
                }).format(text || 0),
        },
        {
            title: "Aktif",
            dataIndex: "is_active",
            key: "is_active",
            align: "center",
            render: (text, record) => (
                <Switch
                    checked={record.is_active == 1}
                    onChange={(checked) => handleSwitchChange(checked, record)}
                />
            ),
        }
    ]

    const filteredProducts = products.filter((item) =>
        [item.name, item.item_no, item.item_type_name]
            .join(" ")
            .toLowerCase()
            .includes(search.toLowerCase())
    )

    return (
        <Layout title="Produk Accurate">
            <div className="card">
                <div className="card-body">
                    <div className="mb-4 flex justify-between items-center flex-wrap gap-4">
                        <div className="flex items-center gap-3 flex-wrap">
                            <div>
                                Total Produk: <strong>{filteredProducts.length}</strong>
                            </div>
                            <Input.Search
                                placeholder="Cari nama, SKU, atau jenis..."
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
                                Sync Product
                            </Button>
                        </div>
                    </div>
                    <Table
                        rowKey="accurate_id"
                        columns={columns}
                        dataSource={filteredProducts}
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

export default AccurateProduct
