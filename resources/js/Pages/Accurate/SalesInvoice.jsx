import React, { useEffect, useState } from "react"
import { Table, Input, message, DatePicker } from "antd"
import Layout from "../../components/layout"
import dayjs from "dayjs"
import isBetween from "dayjs/plugin/isBetween"
dayjs.extend(isBetween)

const { RangePicker } = DatePicker

const SalesInvoice = () => {
    const [invoices, setInvoices] = useState([])
    const [loading, setLoading] = useState(true)
    const [search, setSearch] = useState("")
    const [dateRange, setDateRange] = useState(null)

    const getInvoices = () => {
        fetch("/api/accurate/sales-invoice")
            .then((res) => res.json())
            .then((json) => {
                setInvoices(json.data)
                setLoading(false)
            })
            .catch(() => setLoading(false))
    }

    useEffect(() => {
        getInvoices()
    }, [])

    const filteredInvoices = invoices.filter((item) => {
        const matchSearch = [
            item.number,
            item.customer_name,
            item.customer_no,
            item.po_number,
            item.description,
        ]
            .join(" ")
            .toLowerCase()
            .includes(search.toLowerCase())

        const matchDate = dateRange
            ? dayjs(item.trans_date).isBetween(
                dayjs(dateRange[0]).startOf("day"),
                dayjs(dateRange[1]).endOf("day"),
                null,
                "[]"
            )
            : true

        return matchSearch && matchDate
    })

    const columns = [
        {
            title: "Invoice ID",
            dataIndex: "invoice_number",
            key: "invoice_number",
        },
        {
            title: "Invoice Date",
            dataIndex: "trans_date",
            key: "trans_date",
            render: (text) =>
                text ? new Date(text).toLocaleDateString("id-ID") : "-",
        },
        {
            title: "Customer Code",
            dataIndex: "customer_no",
            key: "customer_no",
        },
        {
            title: "Product Code",
            dataIndex: "item_no",
            key: "item_no",
        },
        {
            title: "Qty Sold",
            dataIndex: "quantity",
            key: "quantity",
            align: "right",
            render: (text) =>
                new Intl.NumberFormat("id-ID", {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 2,
                }).format(text),
        },
        {
            title: "Unit Price",
            dataIndex: "unit_price",
            key: "unit_price",
            align: "right",
            render: (text) =>
                new Intl.NumberFormat("id-ID", {
                    style: "currency",
                    currency: "IDR",
                    minimumFractionDigits: 0,
                }).format(text),
        },
        {
            title: "Total",
            dataIndex: "total",
            key: "total",
            align: "right",
            render: (text) =>
                new Intl.NumberFormat("id-ID", {
                    style: "currency",
                    currency: "IDR",
                    minimumFractionDigits: 0,
                }).format(text),
        },
    ]

    return (
        <Layout title="Sales Invoice Accurate">
            <div className="card">
                <div className="card-body">
                    <div className="mb-4 flex justify-between items-center flex-wrap gap-4">
                        <div className="flex items-center gap-3 flex-wrap">
                            <div>
                                Total Invoice: <strong>{filteredInvoices.length}</strong>
                            </div>
                            <Input.Search
                                placeholder="Cari nomor invoice, nama pelanggan, PO..."
                                allowClear
                                onChange={(e) => setSearch(e.target.value)}
                                style={{ width: 300 }}
                            />
                            <RangePicker
                                onChange={(dates) => setDateRange(dates)}
                                format="YYYY-MM-DD"
                                allowClear
                            />
                        </div>
                    </div>
                    <Table
                        rowKey="id"
                        columns={columns}
                        dataSource={filteredInvoices}
                        loading={loading}
                        scroll={{ x: "max-content" }}
                        tableLayout="auto"
                    />
                </div>
            </div>
        </Layout>
    )
}

export default SalesInvoice
