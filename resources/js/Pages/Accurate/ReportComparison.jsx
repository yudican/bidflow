import React, { useEffect, useState } from "react";
import { Table, Input, message, DatePicker, Tag } from "antd";
import Layout from "../../components/layout";
import dayjs from "dayjs";
import * as XLSX from "xlsx";

const formatNum = (value) =>
    Intl.NumberFormat("id-ID", { maximumFractionDigits: 0 }).format(value);

const ReportComparison = () => {
    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(true);
    const [search, setSearch] = useState("");
    const [dateRange, setDateRange] = useState([]);

    const fetchData = () => {
        setLoading(true);
        const [start, end] = dateRange || [];

        if (start && end) {
            fetch('/api/accurate/stock-comparison/filter', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    startDate: dayjs(start).format("YYYY-MM-DD"),
                    endDate: dayjs(end).format("YYYY-MM-DD"),
                }),
            })
                .then(() => {
                    return fetch('/api/accurate/stock-comparison/tmp');
                })
                .then((res) => res.json())
                .then((json) => setData(json.data || []))
                .catch(() => message.error("Gagal memuat data stok"))
                .finally(() => setLoading(false));
        } else {
            fetch('/api/accurate/stock-comparison')
                .then((res) => res.json())
                .then((json) => setData(json.data || []))
                .catch(() => message.error("Gagal memuat data stok"))
                .finally(() => setLoading(false));
        }
    };

    useEffect(() => {
        fetchData();
    }, []);

    const filtered = data.filter((i) =>
        JSON.stringify(i).toLowerCase().includes(search.toLowerCase())
    );

    const formatDiff = (value) => (
        <span style={{ color: value !== 0 ? "red" : "green" }}>
            {formatNum(value)}
        </span>
    );

    const statusTag = (status, gapStatus) => {
        if (status === "MATCH") {
            return <Tag color="green">MATCH</Tag>;
        } else if (gapStatus === "MAJOR") {
            return <Tag color="red">MAJOR GAP</Tag>;
        } else if (gapStatus === "MINOR") {
            return <Tag color="orange">MINOR GAP</Tag>;
        } else {
            return <Tag>{status}</Tag>;
        }
    };

    const exportToExcel = () => {
        const exportData = [];

        data.forEach((cust) => {
            cust.subs?.forEach((sub) => {
                sub.details?.forEach((detail) => {
                    exportData.push({
                        "Customer ID": cust.customer_id,
                        "Customer Name": cust.customer_name,
                        "Sub Customer ID": sub.sub_customer_id,
                        "Sub Customer Name": sub.sub_customer_name,
                        "Item No": detail.item_no,
                        "Product Name": detail.product_name,
                        "Stock System": detail.adjusted,
                        "Stock Actual": detail.stock_actual,
                        "Difference": detail.difference,
                        "Status": detail.status,
                        "Gap Status": detail.gap_status,
                    });
                });
            });
        });

        const worksheet = XLSX.utils.json_to_sheet(exportData);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, "Stock Comparison");

        const excelBuffer = XLSX.write(workbook, {
            bookType: "xlsx",
            type: "array",
        });

        const blob = new Blob([excelBuffer], {
            type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        });

        const url = URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = "stock-comparison.xlsx";
        a.click();
        URL.revokeObjectURL(url);
    };

    const columnsLvl1 = [
        { title: "Customer Code", dataIndex: "customer_id", key: "customer_id" },
        { title: "Customer Name", dataIndex: "customer_name", key: "customer_name" },
        { title: "Stock System", dataIndex: "adjusted", key: "adjusted", align: "right", render: formatNum },
        { title: "Stock Actual", dataIndex: "stock_actual", key: "stock_actual", align: "right", render: formatNum },
        { title: "Difference", dataIndex: "difference", key: "difference", align: "right", render: formatDiff },
        {
            title: "Status",
            key: "status",
            render: (_, record) => statusTag(record.status, record.gap_status)
        },
    ];

    const columnsLvl2 = [
        { title: "Sub Customer ", dataIndex: "sub_customer_id", key: "sub_customer_id" },
        { title: "Sub Customer Name", dataIndex: "sub_customer_name", key: "sub_customer_name" },
        { title: "Stock System", dataIndex: "adjusted", key: "adjusted", align: "right", render: formatNum },
        { title: "Stock Actual", dataIndex: "stock_actual", key: "stock_actual", align: "right", render: formatNum },
        { title: "Difference", dataIndex: "difference", key: "difference", align: "right", render: formatDiff },
        {
            title: "Status",
            key: "status",
            render: (_, record) => statusTag(record.status, record.gap_status)
        },
    ];

    const columnsLvl3 = [
        { title: "Item No", dataIndex: "item_no", key: "item_no" },
        { title: "Product Name", dataIndex: "product_name", key: "product_name" },
        { title: "Stock System", dataIndex: "adjusted", key: "adjusted", align: "right", render: formatNum },
        { title: "Stock Actual", dataIndex: "stock_actual", key: "stock_actual", align: "right", render: formatNum },
        { title: "Difference", dataIndex: "difference", key: "difference", align: "right", render: formatDiff },
        {
            title: "Status",
            key: "status",
            render: (_, record) => statusTag(record.status, record.gap_status)
        },
    ];

    return (
        <Layout title="Stock Comparison">
            <div className="card">
                <div className="card-body">
                    <div className="flex justify-between items-center mb-4 gap-3 flex-wrap">
                        <div className="flex items-center gap-3 flex-wrap">
                            <span>Total: <strong>{filtered.length}</strong></span>
                            <Input.Search
                                placeholder="Cari customer / produk..."
                                allowClear
                                onChange={(e) => setSearch(e.target.value)}
                                style={{ width: 250 }}
                            />
                            <DatePicker.RangePicker
                                value={dateRange}
                                onChange={(val) => setDateRange(val)}
                                format="DD-MM-YYYY"
                            />
                            <button
                                onClick={fetchData}
                                className="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700"
                            >
                                Filter Date Stock System
                            </button>
                            <button
                                onClick={exportToExcel}
                                className="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700"
                            >
                                Export Excel
                            </button>
                        </div>
                    </div>

                    <Table
                        columns={columnsLvl1}
                        dataSource={filtered}
                        loading={loading}
                        rowKey="customer_id"
                        expandable={{
                            expandedRowRender: (record) => (
                                <Table
                                    columns={columnsLvl2}
                                    dataSource={record.subs || []}
                                    rowKey="sub_customer_id"
                                    pagination={false}
                                    size="small"
                                    expandable={{
                                        expandedRowRender: (sub) => (
                                            <Table
                                                columns={columnsLvl3}
                                                dataSource={sub.details || []}
                                                rowKey={(rec) => `${sub.sub_customer_id}-${rec.item_no}`}
                                                pagination={false}
                                                size="small"
                                            />
                                        ),
                                        rowExpandable: (sub) => (sub.details || []).length > 0
                                    }}
                                />
                            ),
                            rowExpandable: (record) => (record.subs || []).length > 0
                        }}
                        scroll={{ x: "max-content" }}
                        tableLayout="auto"
                    />
                </div>
            </div>
        </Layout>
    );
};

export default ReportComparison;
