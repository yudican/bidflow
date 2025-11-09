import React, { useState, useEffect } from "react";
import { Select, DatePicker, message } from "antd";
import Layout from "../../components/layout";
import dayjs from "dayjs";

const { RangePicker } = DatePicker;

const ReportComparisonExport = () => {
    const [headAccounts, setHeadAccounts] = useState([]);
    const [subAccounts, setSubAccounts] = useState([]);
    const [selectedHead, setSelectedHead] = useState(null);
    const [selectedSubs, setSelectedSubs] = useState([]);
    const [dateRange, setDateRange] = useState([]);
    const [loading, setLoading] = useState(false);

    // Ambil daftar head account untuk filter
    useEffect(() => {
        fetch("/api/accurate/stock-opname/group-data")
            .then((res) => res.json())
            .then((json) => setHeadAccounts(json.data || []))
            .catch(() => message.error("Gagal memuat head account"));
    }, []);

    // Ambil daftar sub account berdasarkan head account terpilih
    useEffect(() => {
        if (selectedHead) {
            fetch(`/api/accurate/stock-opname/group-data/${selectedHead}`)
                .then((res) => res.json())
                .then((json) => setSubAccounts(json.data || []))
                .catch(() => message.error("Gagal memuat sub account"));
        } else {
            setSubAccounts([]);
            setSelectedSubs([]);
        }
    }, [selectedHead]);

    const exportToExcel = () => {
        if (!selectedHead || !dateRange?.length) {
            return message.warning("Pilih Head Account dan Range Date dulu");
        }

        if (!selectedSubs || selectedSubs.length === 0) {
            return message.warning("Pilih minimal satu Sub Account");
        }

        // Batasi endDate tidak boleh lebih dari hari ini
        const today = dayjs();
        const startDate = dayjs(dateRange[0]).format("YYYY-MM-DD");
        let endDate = dayjs(dateRange[1]);
        if (endDate.isAfter(today)) {
            endDate = today;
        }
        endDate = endDate.format("YYYY-MM-DD");

        setLoading(true);

        fetch("/api/accurate/stock-comparison/export", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                headAccount: selectedHead,
                subAccounts: selectedSubs,
                startDate,
                endDate,
            }),
        })
            .then(async (res) => {
                // Check if response is actually an Excel file
                const contentType = res.headers.get("content-type");

                if (contentType && contentType.includes("application/json")) {
                    // It's a JSON error response
                    const errorData = await res.json();
                    throw new Error(errorData.message || "Gagal export data");
                }

                if (!res.ok) {
                    // Try to get error message from JSON response
                    try {
                        const errorData = await res.json();
                        throw new Error(errorData.message || "Gagal export data");
                    } catch (e) {
                        throw new Error("Gagal export data");
                    }
                }

                // Verify it's actually an Excel file
                if (!contentType || !contentType.includes("application/vnd.openxmlformats-officedocument.spreadsheetml.sheet")) {
                    // Try to parse as JSON to get error message
                    try {
                        const errorData = await res.clone().json();
                        throw new Error(errorData.message || "File yang didownload bukan format Excel yang valid");
                    } catch (e) {
                        throw new Error("File yang didownload bukan format Excel yang valid");
                    }
                }

                return res.blob();
            })
            .then((blob) => {
                // Verify blob is not empty
                if (blob.size === 0) {
                    throw new Error("File Excel kosong");
                }

                const url = window.URL.createObjectURL(blob);
                const a = document.createElement("a");
                a.href = url;
                a.download = `stock_comparison_${startDate}_${endDate}.xlsx`;
                document.body.appendChild(a);
                a.click();
                a.remove();
                URL.revokeObjectURL(url);
                setLoading(false);
                message.success("Export berhasil");
            })
            .catch((err) => {
                message.error(err.message || "Export gagal");
                setLoading(false);
            });
    };

    return (
        <Layout title="Stock Comparison Export">
            <div className="card">
                <div className="card-body">
                    <div className="flex flex-col gap-4">
                        <div>
                            <label className="block mb-1 font-semibold">Head Account</label>
                            <Select
                                style={{ width: 300 }}
                                placeholder="Pilih Head Account"
                                options={headAccounts.map((h) => ({
                                    value: h.customer_code,
                                    label: h.customer_name,
                                }))}
                                value={selectedHead}
                                onChange={setSelectedHead}
                                allowClear
                            />
                        </div>

                        <div>
                            <label className="block mb-1 font-semibold">Sub Accounts</label>
                            <Select
                                mode="multiple"
                                style={{ width: 400 }}
                                placeholder="Pilih Sub Accounts"
                                options={subAccounts.map((s) => ({
                                    value: s.customer_no,
                                    label: s.customer_name,
                                }))}
                                value={selectedSubs}
                                onChange={setSelectedSubs}
                            />
                        </div>

                        <div>
                            <label className="block mb-1 font-semibold">Range Date</label>
                            <RangePicker
                                value={dateRange}
                                onChange={(val) => setDateRange(val)}
                                format="DD-MM-YYYY"
                            />
                        </div>

                        <div>
                            <button
                                onClick={exportToExcel}
                                disabled={loading}
                                className="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 disabled:bg-gray-400"
                            >
                                {loading ? "Exporting..." : "Export to Excel"}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Layout>
    );
};

export default ReportComparisonExport;
