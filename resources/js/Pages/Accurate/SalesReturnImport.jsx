import React, { useEffect, useState } from "react";
import {
    Table,
    Button,
    Input,
    message,
    Modal,
    Upload,
    Spin,
    DatePicker,
    Select,
} from "antd";
import {
    ReloadOutlined,
    EyeOutlined,
    PlusOutlined,
    UploadOutlined,
    DownloadOutlined,
} from "@ant-design/icons";
import * as XLSX from "xlsx";
import dayjs from "dayjs";
import Layout from "../../components/layout";

const { RangePicker } = DatePicker;
const { Option } = Select;

const SalesReturnImport = () => {
    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(false);
    const [search, setSearch] = useState("");
    const [modalVisible, setModalVisible] = useState(false);
    const [selectedOpname, setSelectedOpname] = useState(null);
    const [detailItems, setDetailItems] = useState([]);
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [importData, setImportData] = useState([]);
    const [fileName, setFileName] = useState("");
    const [disableImportBtn, setDisableImportBtn] = useState(true);
    const [currentPage, setCurrentPage] = useState(1);
    const [pageSize, setPageSize] = useState(10);
    const [syncing, setSyncing] = useState(false);
    const [uploading] = useState(false);
    const [detailLoading, setDetailLoading] = useState(false);
    const [headAccounts, setHeadAccounts] = useState([]);
    const [subAccounts, setSubAccounts] = useState([]);
    const [selectedHead, setSelectedHead] = useState(null);
    const [selectedSub, setSelectedSub] = useState(null);
    const [filterUser, setFilterUser] = useState(null);
    const [filterDate, setFilterDate] = useState([]);
    const [users, setUsers] = useState([]);

    // Ambil data utama
    const getStockOpnames = ({
        page = 1,
        per_page = 10,
        keyword = "",
        user = "",
        startDate = "",
        endDate = "",
    } = {}) => {
        setLoading(true);
        const params = new URLSearchParams({
            page,
            per_page,
            search: keyword,
            user,
            startDate,
            endDate,
        });
        fetch(`/api/accurate/stock-opname/return/data?${params.toString()}`)
            .then((res) => res.json())
            .then((json) => {
                setData(json.data || {});
            })
            .catch(() => message.error("Gagal mengambil data Sales Return"))
            .finally(() => setLoading(false));
    };

    // Ambil data detail
    const showDetail = (record) => {
        setSelectedOpname(record);
        setModalVisible(true);
        setDetailItems([]);
        if (!record.id) {
            message.error("Record tidak punya ID!");
            return;
        }
        setDetailLoading(true);
        fetch(`/api/accurate/stock-opname/${record.id}/detail`)
            .then((res) => res.json())
            .then((json) => {
                if (json.status === "success") {
                    setDetailItems(json.data.items || []);
                } else {
                    message.warning("Gagal mengambil detail sales return");
                }
            })
            .catch(() => message.error("Gagal mengambil data detail"))
            .finally(() => setDetailLoading(false));
    };

    // Upload file Excel
    const handleFileUpload = (file) => {
        setFileName(file.name);
        const reader = new FileReader();
        reader.onload = (e) => {
            const wb = XLSX.read(new Uint8Array(e.target.result), { type: "array" });
            const sheet = wb.Sheets[wb.SheetNames[0]];
            const rows = XLSX.utils.sheet_to_json(sheet, { header: 1 });

            const data = rows
                .slice(1)
                .map((r) => ({
                    retur_number: r[0]?.toString().trim(),
                    head_account: r[1]?.toString().trim(),
                    sub_account: r[2]?.toString().trim(),
                    date: r[3] ? new Date(r[3]).toISOString().slice(0, 10) : null,
                    sku: r[4]?.toString().trim(),
                    product_name: r[5]?.toString().trim(),
                    qty: parseFloat(r[6]) || 0,
                    md: r[7]?.toString().trim() || "",
                    notes: r[8]?.toString().trim() || "",
                }))
                .filter((row) => row.head_account && row.sub_account && row.sku);

            setImportData(data);
            setDisableImportBtn(data.length === 0);
        };
        reader.readAsArrayBuffer(file);
        return false;
    };

    // Ambil Head Account
    useEffect(() => {
        fetch("/api/accurate/stock-opname/group-data")
            .then((res) => res.json())
            .then((json) => setHeadAccounts(json.data || []))
            .catch(() => message.error("Gagal memuat head account"));
    }, []);

    // Ambil Sub Account
    useEffect(() => {
        if (selectedHead) {
            fetch(`/api/accurate/stock-opname/group-data/${selectedHead}`)
                .then((res) => res.json())
                .then((json) => setSubAccounts(json.data || []))
                .catch(() => message.error("Gagal memuat sub account"));
        } else {
            setSubAccounts([]);
            setSelectedSub(null);
        }
    }, [selectedHead]);

    // Ambil daftar user untuk filter
    useEffect(() => {
        fetch("/api/accurate/stock-opname/return/users")
            .then((res) => res.json())
            .then((json) => setUsers(json.data || []))
            .catch(() => { });
    }, []);

    const handleOkAndImport = () => {
        if (importData.length === 0) {
            message.warning("Data kosong atau tidak valid");
            return;
        }

        setSyncing(true);
        fetch("/api/accurate/stock-opname/import", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                rows: importData,
                type: "return",
            }),
        })
            .then((res) => res.json())
            .then((json) => {
                if (json.status === "success") {
                    message.success("Import berhasil");
                    getStockOpnames();
                    setIsModalOpen(false);
                    setImportData([]);
                    setFileName("");
                } else {
                    message.error("Import gagal: " + json.message);
                }
            })
            .catch(() => message.error("Gagal menghubungi server"))
            .finally(() => setSyncing(false));
    };

    useEffect(() => {
        getStockOpnames();
    }, []);

    const filteredOpnames = (data.data || []).filter((opname) =>
        [opname.customer_name, opname.customer_name_sub]
            .join(" ")
            .toLowerCase()
            .includes(search.toLowerCase())
    );

    // === EXPORT XLSX ===
    const handleExport = async () => {
        if (!filteredOpnames.length) {
            message.warning("Tidak ada data untuk diexport");
            return;
        }

        try {
            const exportRows = [];

            // Loop semua store & ambil detail per store
            for (let i = 0; i < filteredOpnames.length; i++) {
                const item = filteredOpnames[i];
                const detailRes = await fetch(`/api/accurate/stock-opname/${item.id}/detail`);
                const detailJson = await detailRes.json();
                const details = detailJson?.data?.items || [];

                if (details.length === 0) {
                    exportRows.push({
                        "Retur Number": item.code_id || "-",
                        "Tanggal": item.trans_date,
                        "Head Account": item.customer_name,
                        "Store": item.customer_name_sub,
                        "SKU": "-",
                        "Nama Produk": "-",
                        "Qty": 0,
                        "Merchandiser": item.md || "-",
                        "Catatan": "-",
                    });
                } else {
                    details.forEach((det) => {
                        exportRows.push({
                            "Retur Number": item.code_id || "-",
                            "Tanggal": item.trans_date,
                            "Head Account": item.customer_name,
                            "Store": item.customer_name_sub,
                            "SKU": det.item_no,
                            "Nama Produk": det.item_name,
                            "Qty": det.stock_real,
                            "Merchandiser": item.md || "-",
                            "Catatan": det.note || "-",
                        });
                    });
                }
            }

            const ws = XLSX.utils.json_to_sheet(exportRows);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Sales Return");
            XLSX.writeFile(wb, "sales_return.xlsx");

        } catch (error) {
            console.error("Export error:", error);
            message.error("Gagal export data");
        }
    };

    return (
        <Layout title="Sales Return Import">
            <div className="card">
                <div className="card-body">
                    {/* Filter Section */}
                    <div className="mb-4 flex flex-wrap gap-3 items-center">
                        <Input.Search
                            placeholder="Cari store..."
                            allowClear
                            onSearch={(val) => {
                                setSearch(val);
                                setCurrentPage(1);
                                getStockOpnames({
                                    page: 1,
                                    per_page: pageSize,
                                    keyword: val,
                                    user: filterUser,
                                    startDate: filterDate[0] ? filterDate[0].format("YYYY-MM-DD") : "",
                                    endDate: filterDate[1] ? filterDate[1].format("YYYY-MM-DD") : "",
                                });
                            }}
                            style={{ width: 200 }}
                        />
                        <Select
                            placeholder="Filter User Import"
                            allowClear
                            style={{ width: 200 }}
                            value={filterUser}
                            onChange={(val) => {
                                setFilterUser(val);
                                getStockOpnames({
                                    page: 1,
                                    per_page: pageSize,
                                    keyword: search,
                                    user: val,
                                    startDate: filterDate[0] ? filterDate[0].format("YYYY-MM-DD") : "",
                                    endDate: filterDate[1] ? filterDate[1].format("YYYY-MM-DD") : "",
                                });
                            }}
                        >
                            {users.map((u) => (
                                <Option key={u.id} value={u.id}>
                                    {u.name}
                                </Option>
                            ))}
                        </Select>
                        <RangePicker
                            format="YYYY-MM-DD"
                            value={filterDate}
                            onChange={(dates) => {
                                setFilterDate(dates || []);
                                getStockOpnames({
                                    page: 1,
                                    per_page: pageSize,
                                    keyword: search,
                                    user: filterUser,
                                    startDate: dates && dates[0] ? dates[0].format("YYYY-MM-DD") : "",
                                    endDate: dates && dates[1] ? dates[1].format("YYYY-MM-DD") : "",
                                });
                            }}
                        />
                        <Button
                            type="primary"
                            icon={<PlusOutlined />}
                            onClick={() => setIsModalOpen(true)}
                        >
                            Import Data Sales Return
                        </Button>
                        <Button icon={<DownloadOutlined />} onClick={handleExport}>
                            Export Excel
                        </Button>
                        <Button icon={<ReloadOutlined />} onClick={getStockOpnames}>
                            Refresh
                        </Button>
                    </div>

                    {/* Table */}
                    <Table
                        rowKey="id"
                        columns={[
                            {
                                title: "No",
                                key: "no",
                                render: (_, __, index) =>
                                    (currentPage - 1) * pageSize + index + 1,
                            },
                            { title: "Retur Number", dataIndex: "code_id" },
                            { title: "Tanggal", dataIndex: "trans_date" },
                            { title: "Head Account", dataIndex: "customer_name" },
                            { title: "Store", dataIndex: "customer_name_sub" },
                            { title: "Total Item", dataIndex: "total_item" },
                            { title: "Merchandiser", dataIndex: "md" },
                            {
                                title: "Aksi",
                                render: (_, record) => (
                                    <Button
                                        icon={<EyeOutlined />}
                                        onClick={() => showDetail(record)}
                                    >
                                        Lihat Detail
                                    </Button>
                                ),
                            },
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
                                setCurrentPage(page);
                                setPageSize(size);
                                getStockOpnames({
                                    page,
                                    per_page: size,
                                    keyword: search,
                                    user: filterUser,
                                    startDate: filterDate[0]
                                        ? filterDate[0].format("YYYY-MM-DD")
                                        : "",
                                    endDate: filterDate[1]
                                        ? filterDate[1].format("YYYY-MM-DD")
                                        : "",
                                });
                            },
                        }}
                    />
                </div>
            </div>

            {/* Modal Detail */}
            <Modal
                title={`Detail Sales Return ${selectedOpname?.customer_name_sub || ""}`}
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
                                title: "Qty Fisik",
                                dataIndex: "stock_real",
                                key: "stock_real",
                                align: "center",
                                render: (value) => {
                                    const n = Number(value);
                                    return n % 1 === 0 ? n.toString() : n.toFixed(2);
                                },
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
        </Layout>
    );
};

export default SalesReturnImport;
