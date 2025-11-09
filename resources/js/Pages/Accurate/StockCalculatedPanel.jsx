import React, { useState, useEffect, useRef } from "react";
import { Button, Progress, message, Input, DatePicker, Space } from "antd";
import dayjs from "dayjs";

const { RangePicker } = DatePicker;

const StockCalculatePanel = () => {
    const [dateRange, setDateRange] = useState([]);
    const [runningJobId, setRunningJobId] = useState(null);
    const [progress, setProgress] = useState(0);
    const [status, setStatus] = useState(null);
    const [loading, setLoading] = useState(false);
    const pollRef = useRef(null);

    useEffect(() => {
        return () => {
            if (pollRef.current) clearInterval(pollRef.current);
        };
    }, []);

    const startCalculation = async () => {
        if (!dateRange || dateRange.length !== 2) {
            message.warning("Pilih range tanggal dulu");
            return;
        }

        const startDate = dayjs(dateRange[0]).format("YYYY-MM-DD");
        const endDate = dayjs(dateRange[1]).format("YYYY-MM-DD");

        setLoading(true);
        try {
            const res = await fetch("/api/accurate/stock-system-calculate", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ startDate, endDate, type: "opname", per_page: 500 }),
            });
            const json = await res.json();
            if (json.status === "success") {
                setRunningJobId(json.job_id);
                setStatus("pending");
                setProgress(0);
                // start polling
                pollRef.current = setInterval(() => pollStatus(json.job_id), 1500);
            } else {
                message.error(json.message || "Gagal memulai job");
            }
        } catch (e) {
            message.error("Gagal memulai job");
        } finally {
            setLoading(false);
        }
    };

    const pollStatus = async (jobId) => {
        try {
            const res = await fetch(`/api/accurate/stock-system-calc-status/${jobId}`);
            const json = await res.json();
            if (json.status === "success") {
                const d = json.data;
                setProgress(d.progress);
                setStatus(d.status);
                if (d.status === "done" || d.status === "failed") {
                    clearInterval(pollRef.current);
                    pollRef.current = null;
                    setRunningJobId(null);
                    if (d.status === "done") message.success("Perhitungan selesai!");
                    else message.error("Perhitungan gagal. Cek logs.");
                }
            } else {
                // stop polling on error
                clearInterval(pollRef.current);
                pollRef.current = null;
                setRunningJobId(null);
                message.error("Status job tidak ditemukan");
            }
        } catch (e) {
            // network error - keep polling for transient errors
            console.error("poll error", e);
        }
    };

    return (
        <div>
            <Space direction="horizontal" style={{ marginBottom: 16 }}>
                <RangePicker
                    value={dateRange}
                    onChange={(val) => setDateRange(val)}
                    format="DD-MM-YYYY"
                    disabledDate={(current) => current && current > dayjs().endOf("day")}
                />
                <Button type="primary" onClick={startCalculation} loading={loading} disabled={!!runningJobId}>
                    Start Calculation
                </Button>
            </Space>

            <div style={{ width: 600 }}>
                <Progress percent={progress} status={status === "failed" ? "exception" : undefined} />
            </div>

            {runningJobId && <div style={{ marginTop: 8 }}>Job ID: {runningJobId} — Status: {status}</div>}
        </div>
    );
};

export default StockCalculatePanel;
