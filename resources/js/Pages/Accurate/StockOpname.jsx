import React, { useEffect, useState } from "react"
import {
    Table,
    Button,
    Input,
    message,
    Modal,
    Upload,
    Spin,
} from "antd"
import {
    ReloadOutlined,
    EyeOutlined,
    PlusOutlined,
    UploadOutlined,
} from "@ant-design/icons"
import * as XLSX from "xlsx";
import Layout from "../../components/layout"

const StockOpname = () => {
    const [data, setData] = useState([])
    const [loading, setLoading] = useState(false)
    const [search, setSearch] = useState("")
    const [modalVisible, setModalVisible] = useState(false)
    const [selectedOpname, setSelectedOpname] = useState(null)
    const [detailItems, setDetailItems] = useState([])
    const [isModalOpen, setIsModalOpen] = useState(false)
    const [importData, setImportData] = useState([])
    const [fileName, setFileName] = useState("")   // ⬅️ simpan nama file
    const [disableImportBtn, setDisableImportBtn] = useState(true)
    const [currentPage, setCurrentPage] = useState(1)
    const [pageSize, setPageSize] = useState(10)
    const [syncing, setSyncing] = useState(false)
    const [uploading] = useState(false)
    const [detailLoading, setDetailLoading] = useState(false)

    const getStockOpnames = ({ page = 1, per_page = 10, keyword = "" } = {}) => {
        setLoading(true)
        fetch(`/api/accurate/stock-opname/opname/data?page=${page}&per_page=${per_page}&search=${keyword}`)
            .then((res) => res.json())
            .then((json) => {
                setData(json.data || {})
            })
            .catch(() => message.error("Gagal mengambil data Stock Opname"))
            .finally(() => setLoading(false))
    }

    const showDetail = (record) => {
        setSelectedOpname(record)
        setModalVisible(true)
        setDetailItems([])
        if (!record.id) {
            message.error("Record tidak punya ID!")
            return
        }
        setDetailLoading(true)
        fetch(`/api/accurate/stock-opname/${record.id}/detail`)
            .then(res => res.json())
            .then(json => {
                if (json.status === "success") {
                    setDetailItems(json.data.items || [])
                } else {
                    message.warning("Gagal mengambil detail opname")
                }
            })
            .catch(() => message.error("Gagal mengambil data detail"))
            .finally(() => setDetailLoading(false))
    }

    const handleFileUpload = (file) => {
        setFileName(file.name)   // ⬅️ simpan nama file
        const reader = new FileReader()
        reader.onload = (e) => {
            const wb = XLSX.read(new Uint8Array(e.target.result), { type: "array" })
            const sheet = wb.Sheets[wb.SheetNames[0]]
            const rows = XLSX.utils.sheet_to_json(sheet, { header: 1 })

            const data = rows.slice(1).map((r) => ({
                head_account: r[0]?.toString().trim(),
                sub_account: r[1]?.toString().trim(),
                date: r[2] ? new Date(r[2]).toISOString().slice(0, 10) : null,
                sku: r[3]?.toString().trim(),
                product_name: r[4]?.toString().trim(),
                qty_opname: parseFloat(r[5]) || 0,
                qty_gimmick: parseFloat(r[6]) || 0,
                qty_expired: parseFloat(r[7]) || 0,
                notes: r[8]?.toString().trim() || "",
            })).filter((row) => row.head_account && row.sub_account && row.sku)

            setImportData(data)
            setDisableImportBtn(data.length === 0)
        }
        reader.readAsArrayBuffer(file)
        return false
    }

    const handleOkAndImport = () => {
        if (importData.length === 0) {
            message.warning("Data kosong atau tidak valid")
            return
        }

        setSyncing(true)
        fetch("/api/accurate/stock-opname/import", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                rows: importData,
                type: "opname",
            }),
        })
            .then((res) => res.json())
            .then((json) => {
                if (json.status === "success") {
                    message.success("Import berhasil")
                    getStockOpnames()
                    setIsModalOpen(false)
                    setImportData([])
                    setFileName("")
                } else {
                    message.error("Import gagal: " + json.message)
                }
            })
            .catch(() => message.error("Gagal menghubungi server"))
            .finally(() => setSyncing(false))
    }

    useEffect(() => {
        getStockOpnames()
    }, [])

    const filteredOpnames = (data.data || []).filter((opname) =>
        [opname.customer_name, opname.customer_name_sub]
            .join(" ")
            .toLowerCase()
            .includes(search.toLowerCase())
    )

    return (
        <Layout title="Stock Opname">
            <div className="card">
                <div className="card-body">
                    <div className="mb-4 flex justify-between items-center gap-4 flex-wrap">
                        <div>
                            Total Stock Opname: <strong>{data.total || 0}</strong>
                        </div>
                        <Input.Search
                            placeholder="Cari nama store..."
                            allowClear
                            onSearch={(val) => {
                                setSearch(val)
                                setCurrentPage(1)
                                getStockOpnames({ page: 1, per_page: pageSize, keyword: val })
                            }}
                            style={{ width: 250 }}
                        />
                        <div className="flex gap-2">
                            <Button
                                type="primary"
                                icon={<PlusOutlined />}
                                onClick={() => setIsModalOpen(true)}
                            >
                                Import Data Stock Opname
                            </Button>
                            <Button icon={<ReloadOutlined />} onClick={getStockOpnames}>
                                Refresh
                            </Button>
                        </div>
                    </div>
                    <Table
                        rowKey="id"
                        columns={[
                            {
                                title: "No",
                                key: "no",
                                render: (_, __, index) => (currentPage - 1) * pageSize + index + 1,
                            },
                            { title: "Tanggal Opname", dataIndex: "trans_date" },
                            { title: "Head Account", dataIndex: "customer_name" },
                            { title: "Toko/Store", dataIndex: "customer_name_sub" },
                            { title: "Total Item", dataIndex: "total_item" },
                            {
                                title: "Aksi",
                                render: (_, record) => (
                                    <Button icon={<EyeOutlined />} onClick={() => showDetail(record)}>
                                        Lihat Detail
                                    </Button>
                                )
                            }
                        ]}
                        dataSource={filteredOpnames}
                        loading={loading}
                        scroll={{ x: "max-content" }}
                        pagination={{
                            current: currentPage,
                            pageSize: pageSize,
                            total: data.total || 0,
                            showSizeChanger: true,
                            pageSizeOptions: ["10", "20", "50", "100"],
                            onChange: (page, size) => {
                                setCurrentPage(page)
                                setPageSize(size)
                                getStockOpnames({ page, per_page: size, keyword: search })
                            },
                        }}
                    />
                </div>
            </div>

            <Modal
                title={`Detail Stock Awal ${selectedOpname?.opname_date || ""}`}
                open={modalVisible}
                onCancel={() => setModalVisible(false)}
                footer={null}
                width={900}
            >
                <Spin spinning={detailLoading}>
                    <Table
                        columns={[
                            { title: "Kode Produk", dataIndex: "item_no", key: "item_no" },
                            { title: "Nama Produk", dataIndex: "item_name", key: "item_name" },
                            {
                                title: "Qty Fisik", dataIndex: "stock_real", key: "stock_real", align: "center", render: (value) => {
                                    const n = Number(value)
                                    return n % 1 === 0 ? n.toString() : n.toFixed(2)
                                }
                            },
                            {
                                title: "Qty Gimmick", dataIndex: "qty_gimmick", key: "qty_gimmick", align: "center", render: (value) => {
                                    const n = Number(value)
                                    return n % 1 === 0 ? n.toString() : n.toFixed(2)
                                }
                            },
                            {
                                title: "Qty Expired", dataIndex: "qty_expired", key: "qty_expired", align: "center", render: (value) => {
                                    const n = Number(value)
                                    return n % 1 === 0 ? n.toString() : n.toFixed(2)
                                }
                            },
                            { title: "Catatan", dataIndex: "note", key: "note" },
                        ]}
                        dataSource={detailItems}
                        rowKey={(record, index) => `${record.item_no}-${index}`}
                        pagination={false}
                        size="small"
                    />
                </Spin>
            </Modal>

            <Modal
                title="Import Stock Opname"
                open={isModalOpen}
                onOk={handleOkAndImport}
                okText="Import Data"
                cancelText="Batal"
                confirmLoading={syncing}
                onCancel={() => setIsModalOpen(false)}
                okButtonProps={{ disabled: disableImportBtn }}
                width={800}
            >
                <p>
                    Gunakan template{" "}
                    <a href="/assets/template/import-stock-opname.xlsx" download>
                        di sini
                    </a>
                </p>
                <Upload
                    accept=".xlsx,.xls"
                    beforeUpload={handleFileUpload}
                    showUploadList={false}
                    disabled={uploading}
                >
                    <Button icon={<UploadOutlined />}>Pilih File Excel</Button>
                </Upload>

                {fileName && (
                    <p style={{ marginTop: 10 }}>
                        <strong>File:</strong> {fileName}
                    </p>
                )}

                {importData.length > 0 && (
                    <Table
                        style={{ marginTop: 16 }}
                        rowKey={(record, index) => index}
                        columns={[
                            { title: "Head Account", dataIndex: "head_account" },
                            { title: "Sub Account", dataIndex: "sub_account" },
                            { title: "Tanggal", dataIndex: "date" },
                            { title: "SKU", dataIndex: "sku" },
                            { title: "Nama Produk", dataIndex: "product_name" },
                            { title: "Qty Opname", dataIndex: "qty_opname" },
                            { title: "Qty Gimmick", dataIndex: "qty_gimmick" },
                            { title: "Qty Expired", dataIndex: "qty_expired" },
                            { title: "Catatan", dataIndex: "notes" },
                        ]}
                        dataSource={importData}
                        pagination={false}
                        size="small"
                        scroll={{ y: 200 }}
                    />
                )}
            </Modal>
        </Layout>
    )
}

export default StockOpname
