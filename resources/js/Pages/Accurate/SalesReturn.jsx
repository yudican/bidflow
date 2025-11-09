import React, { useEffect, useState } from "react"
import {
    Table,
    Button,
    Input,
    message,
    Modal,
    DatePicker,
} from "antd"
import { ReloadOutlined, EyeOutlined } from "@ant-design/icons"
import Layout from "../../components/layout"
import dayjs from "dayjs"
import isBetween from "dayjs/plugin/isBetween"
dayjs.extend(isBetween)

const { RangePicker } = DatePicker

const SalesReturn = () => {
    const [returns, setReturns] = useState([])
    const [loading, setLoading] = useState(true)
    const [syncing, setSyncing] = useState(false)
    const [search, setSearch] = useState("")
    const [dateRange, setDateRange] = useState(null)
    const [detailVisible, setDetailVisible] = useState(false)
    const [detailData, setDetailData] = useState([])
    const [selectedReturnNumber, setSelectedReturnNumber] = useState(null)
    const [currentPage, setCurrentPage] = useState(1)
    const [pageSize, setPageSize] = useState(10)

    const fetchData = () => {
        setLoading(true)
        fetch("/api/accurate/sales-returns")
            .then(res => res.json())
            .then(json => {
                let data = json?.data?.data || json?.data || []
                if (!Array.isArray(data)) data = []
                setReturns(data)
            })
            .catch(() => message.error("Gagal memuat data sales return"))
            .finally(() => setLoading(false))
    }

    const handleSync = () => {
        setSyncing(true)
        fetch("/api/accurate/sync-sales-returns", {
            method: "POST"
        })
            .then(res => res.json())
            .then(json => {
                if (json.status === "success") {
                    message.success("Sinkronisasi berhasil")
                    fetchData()
                } else {
                    message.warning("Gagal sinkronisasi")
                }
            })
            .catch(() => message.error("Terjadi kesalahan sinkronisasi"))
            .finally(() => setSyncing(false))
    }

    const showDetail = (id) => {
        setDetailVisible(true)
        setSelectedReturnNumber(id)
        setDetailData([])
        fetch(`/api/accurate/item-transfer/${id}/details`)
            .then(res => res.json())
            .then(json => {
                if (json.status === "success") {
                    setDetailData(json.data || [])
                } else {
                    message.warning("Gagal mengambil detail")
                }
            })
            .catch(() => message.error("Gagal mengambil data detail"))
    }

    useEffect(() => {
        fetchData()
    }, [])

    const filtered = returns.filter(item => {
        const matchSearch = Object.values(item)
            .join(" ")
            .toLowerCase()
            .includes(search.toLowerCase())

        const matchDate = dateRange
            ? dayjs(item.return_date).isBetween(
                dayjs(dateRange[0]).startOf("day"),
                dayjs(dateRange[1]).endOf("day"),
                null,
                "[]"
            )
            : true

        return matchSearch && matchDate
    })

    const formatCurrency = (value) =>
        new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 0,
        }).format(Number(value) || 0)

    const formatQty = (value) =>
        new Intl.NumberFormat("id-ID", {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2,
        }).format(Number(value) || 0)

    const columns = [
        {
            title: "No",
            key: "no",
            render: (_, __, index) => (currentPage - 1) * pageSize + index + 1,
        },
        {
            title: "Return Number",
            dataIndex: "number",
            key: "number",
        },
        {
            title: "Return Date",
            dataIndex: "trans_date",
            key: "trans_date",
            render: (text) =>
                text ? new Date(text).toLocaleDateString("id-ID") : "-",
        },
        {
            title: "Customer Code",
            dataIndex: "customer_code",
            key: "customer_code",
        },
        {
            title: "From Location",
            dataIndex: "from_location",
            key: "from_location"
        },
        {
            title: "To Customer Code",
            dataIndex: "to_customer_id",
            key: "to_customer_id"
        },
        {
            title: "Type Proccess",
            dataIndex: "tipe_proses",
            key: "tipe_proses",
        },
        {
            title: "Aksi",
            key: "action",
            render: (_, record) => (
                <Button
                    icon={<EyeOutlined />}
                    onClick={() => showDetail(record.id)}
                >
                    Detail
                </Button>
            ),
        },
    ]

    return (
        <Layout title="Sales Return">
            <div className="card">
                <div className="card-body">
                    <div className="flex justify-between items-center mb-4 gap-3 flex-wrap">
                        <div className="flex flex-wrap items-center gap-3">
                            <span>Total: <strong>{filtered.length}</strong></span>
                            <Input.Search
                                placeholder="Cari data..."
                                allowClear
                                onChange={(e) => setSearch(e.target.value)}
                                style={{ width: 250 }}
                            />
                            <RangePicker
                                onChange={(dates) => setDateRange(dates)}
                                format="YYYY-MM-DD"
                                allowClear
                            />
                        </div>
                        <Button
                            type="primary"
                            icon={<ReloadOutlined />}
                            loading={syncing}
                            onClick={handleSync}
                        >
                            Sync Return
                        </Button>
                    </div>

                    <Table
                        columns={columns}
                        dataSource={filtered}
                        loading={loading}
                        rowKey={(record) => record.return_number}
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

            <Modal
                title={`Detail Return #${selectedReturnNumber}`}
                open={detailVisible}
                onCancel={() => setDetailVisible(false)}
                footer={null}
                width={800}
            >
                <Table
                    columns={[
                        { title: "Item No", dataIndex: "item_no", key: "item_no" },
                        { title: "Name", dataIndex: "item_name", key: "item_name" },
                        { title: "Unit", dataIndex: "unit_name", key: "unit_name" },
                        {
                            title: "Qty",
                            dataIndex: "quantity",
                            key: "quantity",
                            align: "right",
                            render: (value) => Number(value).toLocaleString("id-ID", {
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 0
                            }),
                        },
                        { title: "Status", dataIndex: "status_name", key: "status_name" },
                    ]}
                    dataSource={detailData}
                    rowKey={(record, idx) => `${record.item_code}-${idx}`}
                    pagination={false}
                    size="small"
                    scroll={{ x: "max-content" }}
                />
            </Modal>
        </Layout>
    )
}

export default SalesReturn
