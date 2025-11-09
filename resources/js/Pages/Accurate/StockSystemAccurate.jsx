import React, { useEffect, useState } from "react";
import { Table, Input, message, DatePicker, Button, Tooltip } from "antd";
import { FileExcelOutlined, SearchOutlined, FilterOutlined, QuestionCircleOutlined } from "@ant-design/icons";
import Layout from "../../components/layout";
import dayjs from "dayjs";
import * as XLSX from "xlsx";

const formatNum = (value) =>
    Intl.NumberFormat("id-ID", { maximumFractionDigits: 0 }).format(value || 0);

const StockSystemAccurate = () => {
    const [stockData, setStockData] = useState([]);
    const [loading, setLoading] = useState(true);
    const [search, setSearch] = useState("");
    const [dateRange, setDateRange] = useState([]);

    const fetchStockData = () => {
        setLoading(true);
        const [start, end] = dateRange || [];
        const params = new URLSearchParams();
        params.append("type", "accurate");

        if (start && end) {
            params.append("startDate", dayjs(start).format("YYYY-MM-DD"));
            params.append("endDate", dayjs(end).format("YYYY-MM-DD"));
        }

        params.append("page", 1);
        params.append("per_page", 500);

        fetch(`/api/accurate/stock-system-calculated?${params.toString()}`)
            .then((res) => res.json())
            .then((json) => setStockData(json.data || []))
            .catch(() => message.error("Gagal memuat data stok"))
            .finally(() => setLoading(false));
    };

    useEffect(() => {
        fetchStockData();
    }, []);

    const filtered = stockData.filter((item) =>
        JSON.stringify(item).toLowerCase().includes(search.toLowerCase())
    );

    // Export ke Excel (tanpa file-saver)
    const exportToExcel = () => {
        const exportRows = [];

        (filtered || []).forEach((cust) => {
            // Layer 1 - Customer Header
            exportRows.push({
                "Customer Code": cust.customer_id,
                "Customer Name": cust.customer_name,
                "Sub Customer Code": "",
                "Sub Customer Name": "",
                "Item No (SKU)": "",
                "Product Name": "",
                "Stock Awal": (cust.subs || []).reduce((sum, s) => sum + (s.stock_opname || 0), 0),
                "Stock In": (cust.subs || []).reduce((sum, s) => sum + (s.total_stock_in || 0), 0),
                "Stock Out": (cust.subs || []).reduce((sum, s) => sum + (s.total_stock_out || 0), 0),
                "Stock Return": (cust.subs || []).reduce((sum, s) => sum + (s.total_stock_return || 0), 0),
                "Stock System": (cust.subs || []).reduce((sum, s) => sum + (s.adjusted_stock || 0), 0),
            });

            // Layer 2 - Sub Account
            (cust.subs || []).forEach((sub) => {
                exportRows.push({
                    "Customer Code": "",
                    "Customer Name": "",
                    "Sub Customer Code": sub.customer_no_sub,
                    "Sub Customer Name": sub.customer_name_sub,
                    "Item No (SKU)": "[SUB TOTAL]",
                    "Product Name": "",
                    "Stock Awal": sub.stock_opname || 0,
                    "Stock In": sub.total_stock_in || 0,
                    "Stock Out": sub.total_stock_out || 0,
                    "Stock Return": sub.total_stock_return || 0,
                    "Stock System": sub.adjusted_stock || 0,
                });

                // Layer 3 - Product Detail
                (sub.details || []).forEach((item) => {
                    exportRows.push({
                        "Customer Code": "",
                        "Customer Name": "",
                        "Sub Customer Code": "",
                        "Sub Customer Name": "",
                        "Item No (SKU)": item.item_no,
                        "Product Name": item.product_name,
                        "Stock Awal": item.stock_opname || 0,
                        "Stock In": item.stock_in || 0,
                        "Stock Out": item.stock_out || 0,
                        "Stock Return": item.stock_return || 0,
                        "Stock System": item.selisih_opname || 0,
                    });
                });
            });
        });

        if (exportRows.length === 0) {
            message.warning("Tidak ada data untuk diexport.");
            return;
        }

        const worksheet = XLSX.utils.json_to_sheet(exportRows);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, "Stock System");

        XLSX.writeFile(workbook, `StockSystem_${dayjs().format("YYYYMMDD_HHmm")}.xlsx`);
    };

    const columns = [
        { title: "Customer Code", dataIndex: "customer_id", key: "customer_id" },
        { title: "Customer Name", dataIndex: "customer_name", key: "customer_name" },
        {
            title: (
                <span>
                    Stock Awal (Data by Accurate){" "}
                    <Tooltip title="Last sync date">
                        <QuestionCircleOutlined style={{ color: "#1890ff" }} />
                    </Tooltip>
                </span>
            ),
            align: "right",
            render: () => 0,
        },
        {
            title: "Stock Awal (Data by Stock Opname)",
            align: "right",
            render: (_, record) =>
                formatNum((record.subs || []).reduce((sum, s) => sum + (s.stock_opname || 0), 0)),
        },
        {
            title: "Stock In",
            align: "right",
            render: (_, record) =>
                formatNum((record.subs || []).reduce((sum, s) => sum + (s.total_stock_in || 0), 0)),
        },
        {
            title: "Stock Out",
            align: "right",
            render: (_, record) =>
                formatNum((record.subs || []).reduce((sum, s) => sum + (s.total_stock_out || 0), 0)),
        },
        {
            title: "Stock Return",
            align: "right",
            render: (_, record) =>
                formatNum((record.subs || []).reduce((sum, s) => sum + (s.total_stock_return || 0), 0)),
        },
        {
            title: "Stock System",
            align: "right",
            render: (_, record) =>
                formatNum((record.subs || []).reduce((sum, s) => sum + (s.adjusted_stock || 0), 0)),
        },
    ];

    const subAccountColumns = [
        { title: "Sub Customer Code", dataIndex: "customer_no_sub", key: "customer_no_sub" },
        { title: "Sub Customer Name", dataIndex: "customer_name_sub", key: "customer_name_sub" },
        { title: "Stock Awal (Data by Accurate)", dataIndex: "stock_opname", align: "right", render: () => 0 },
        { title: "Stock Awal (Data by Stock Opname)", dataIndex: "stock_opname", align: "right", render: formatNum },
        { title: "Stock In", dataIndex: "total_stock_in", align: "right", render: formatNum },
        { title: "Stock Out", dataIndex: "total_stock_out", align: "right", render: formatNum },
        { title: "Stock Return", dataIndex: "total_stock_return", align: "right", render: formatNum },
        { title: "Stock System", dataIndex: "adjusted_stock", align: "right", render: formatNum },
    ];

    const detailColumns = [
        { title: "Item No (SKU)", dataIndex: "item_no", key: "item_no" },
        { title: "Product Name", dataIndex: "product_name", key: "product_name" },
        { title: "Stock Awal (Data by Accurate)", dataIndex: "stock_opname", align: "right", render: () => 0 },
        { title: "Stock Awal (Data by Stock Opname)", dataIndex: "stock_opname", align: "right", render: formatNum },
        { title: "Stock In", dataIndex: "stock_in", align: "right", render: formatNum },
        { title: "Stock Out", dataIndex: "stock_out", align: "right", render: formatNum },
        { title: "Stock Return", dataIndex: "stock_return", align: "right", render: formatNum },
        { title: "Stock System", dataIndex: "selisih_opname", align: "right", render: formatNum },
    ];

    return (
        <Layout title="Stock System Calculated">
            <div className="card">
                <div className="card-body">
                    <div className="flex items-center gap-3 flex-nowrap mb-4">
                        <span>Total: <strong>{filtered.length}</strong></span>
                        <Input
                            prefix={<SearchOutlined />}
                            placeholder="Cari data..."
                            allowClear
                            onChange={(e) => setSearch(e.target.value)}
                            style={{ width: 250 }}
                        />
                        <DatePicker.RangePicker
                            value={dateRange}
                            onChange={(val) => setDateRange(val)}
                            format="DD-MM-YYYY"
                            disabledDate={(current) => {
                                return current && current > dayjs().endOf("day");
                            }}
                        />
                        <div className="flex gap-2">
                            <Button
                                type="primary"
                                icon={<FilterOutlined />}
                                onClick={fetchStockData}
                            >
                                Filter
                            </Button>
                            <Button
                                type="primary"
                                icon={<FileExcelOutlined />}
                                style={{ backgroundColor: "#28a745", borderColor: "#28a745" }}
                                onClick={exportToExcel}
                            >
                                Export Excel
                            </Button>
                        </div>
                    </div>

                    <Table
                        columns={columns}
                        dataSource={filtered}
                        loading={loading}
                        rowKey="customer_id"
                        expandable={{
                            expandedRowRender: (record) => (
                                <Table
                                    columns={subAccountColumns}
                                    dataSource={record.subs || []}
                                    rowKey={(rec) => `${record.customer_id}-${rec.customer_no_sub}`}
                                    pagination={false}
                                    size="small"
                                    expandable={{
                                        expandedRowRender: (sub) => (
                                            <Table
                                                columns={detailColumns}
                                                dataSource={sub.details || []}
                                                rowKey={(rec) => `${sub.customer_no_sub}-${rec.item_no}`}
                                                pagination={false}
                                                size="small"
                                            />
                                        ),
                                        rowExpandable: (sub) => (sub.details || []).length > 0,
                                    }}
                                />
                            ),
                            rowExpandable: (record) => (record.subs || []).length > 0,
                        }}
                        scroll={{ x: "max-content" }}
                        tableLayout="auto"
                    />
                </div>
            </div>
        </Layout>
    );
};

export default StockSystemAccurate;
