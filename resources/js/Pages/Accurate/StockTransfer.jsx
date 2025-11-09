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
import isBetween from "dayjs/plugin/isBetween";
dayjs.extend(isBetween);

const { RangePicker } = DatePicker

const StockTransfer = () => {
    const [transfers, setTransfers] = useState([])
    const [loading, setLoading] = useState(true)
    const [syncing, setSyncing] = useState(false)
    const [search, setSearch] = useState("")
    const [dateRange, setDateRange] = useState(null)
    const [detailVisible, setDetailVisible] = useState(false)
    const [detailData, setDetailData] = useState([])
    const [selectedTransferId, setSelectedTransferId] = useState(null)
    const [currentPage, setCurrentPage] = useState(1)
    const [pageSize, setPageSize] = useState(10)

    const fetchData = () => {
        setLoading(true)
        fetch("/api/accurate/item-transfer")
            .then(res => res.json())
            .then(json => {
                setTransfers(json.data || [])
            })
            .catch(() => message.error("Gagal memuat data stock transfer"))
            .finally(() => setLoading(false))
    }

    const handleSync = () => {
        setSyncing(true)
        fetch("/api/accurate/sync-item-transfer", {
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
        setSelectedTransferId(id)
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

    const columns = [
        {
            title: "No",
            key: "no",
            render: (_, __, index) => (currentPage - 1) * pageSize + index + 1,
        },
        {
            title: "Transfer No",
            dataIndex: "number",
            key: "number"
        },
        {
            title: "Date",
            dataIndex: "trans_date",
            key: "trans_date"
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
            title: "Sub Account",
            dataIndex: "to_sub_customer_id",
            key: "to_sub_customer_id"
        },
        {
            title: "Approval",
            dataIndex: "approval_status",
            key: "approval_status"
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

    console.log("SAMPLE transfers", transfers.slice(0, 3))

    const filtered = transfers.filter(t => {
        const matchSearch = Object.values(t)
            .join(" ")
            .toLowerCase()
            .includes(search.toLowerCase())

        const matchDate = dateRange
            ? dayjs(t.trans_date).isBetween(
                dayjs(dateRange[0]).startOf("day"),
                dayjs(dateRange[1]).endOf("day"),
                null,
                "[]"
            )
            : true

        // console.log("dateRange", dateRange)

        return matchSearch && matchDate
    })

    return (
        <Layout title="Stock Transfer">
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
                            Sync Transfer
                        </Button>
                    </div>

                    <Table
                        columns={columns}
                        dataSource={filtered}
                        loading={loading}
                        rowKey="id"
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
                title={`Detail Transfer #${selectedTransferId}`}
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
                    rowKey={(record) => record.item_no + record.unit_name}
                    pagination={false}
                    size="small"
                    scroll={{ x: "max-content" }}
                />
            </Modal>
        </Layout>
    )
}

export default StockTransfer
