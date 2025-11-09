import React, { useEffect, useState } from "react";
import { Table, Input, message, Button, Upload, Modal, Popconfirm, Form } from "antd";
import { UploadOutlined, ReloadOutlined, PlusOutlined, DeleteOutlined, CheckOutlined } from "@ant-design/icons";
import * as XLSX from "xlsx";
import Layout from "../../../components/layout";

const ModalImportContactConfirm = ({ data, onLoad }) => {
    const [valid, setValid] = useState(false);

    useEffect(() => {
        const ok = Array.isArray(data) && data.length > 0;
        setValid(ok);

        const hasDuplicate = data.some((row) => row.is_duplicate);
        onLoad(!ok || hasDuplicate); // disable tombol jika kosong atau ada duplikat
    }, [data]);

    return (
        <div>
            <p>Preview: {data.length} baris</p>
            {!valid && <p style={{ color: "red" }}>File kosong atau data tidak valid!</p>}
            {valid && (
                <Table
                    rowKey="customer_no"
                    dataSource={data}
                    loading={false}
                    pagination={false}
                    scroll={{ x: "max-content" }}
                    columns={[
                        { title: "Nama", dataIndex: "name" },
                        { title: "Tanggal Cut Off", dataIndex: "cut_off" },
                        { title: "Provinsi", dataIndex: "prov" },
                        { title: "Kabupaten/Kota", dataIndex: "kab_kota" },

                        {
                            title: "Aksi",
                            key: "aksi",
                            render: (_, row) => (
                                <div className="flex gap-2">
                                    <Button onClick={() => openEditModal(row)}>Edit</Button>
                                    <Popconfirm
                                        title="Hapus kontak?"
                                        onConfirm={() => deleteCustomer(row)}
                                        okText="Ya"
                                        cancelText="Batal"
                                    >
                                        <Button danger icon={<DeleteOutlined />} />
                                    </Popconfirm>
                                </div>
                            ),
                        },
                    ]}
                />
            )}
        </div>
    );
};

const ContactGroupDetail = () => {
    const [groups, setGroups] = useState([])
    const [groupName, setGroupName] = useState("");
    const [customers, setCustomers] = useState([]);
    const [existingNames, setExistingNames] = useState([]);
    const [loading, setLoading] = useState(true);
    const [search, setSearch] = useState("")
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [importData, setImportData] = useState([]);
    const [disableImportBtn, setDisableImportBtn] = useState(true);
    const [syncing, setSyncing] = useState(false);
    const [isEditOpen, setIsEditOpen] = useState(false);
    const [editingCustomer, setEditingCustomer] = useState(null);
    const [currentPage, setCurrentPage] = useState(1);
    const [pageSize, setPageSize] = useState(10);
    const [total, setTotal] = useState(0);

    const id = new URLSearchParams(window.location.search).get("id");

    useEffect(fetchCustomers, [id]);

    function fetchCustomers() {
        if (!id) return;
        setLoading(true);
        fetch(`/api/accurate/contact-group/${id}/customers`)
            .then((r) => r.json())
            .then(({ data = [], group }) => {
                setCustomers(data);
                setExistingNames(
                    data.map((c) => c.customer_name?.toLowerCase().trim())
                );
                setGroupName(group?.name || `#${id}`);
            })
            .catch(() => message.error("Gagal mengambil data"))
            .finally(() => setLoading(false));
    }

    const paginationConfig = {
        current: currentPage,
        pageSize: pageSize,
        total: total,
        onChange: (page, size) => {
            setCurrentPage(page);
            setPageSize(size);
        },
    };

    function deleteCustomer(row) {
        fetch(`/api/accurate/contact-group/${id}/import-customers/${row.customer_no}`, {
            method: "DELETE",
        })
            .then((r) => r.json())
            .then((res) => {
                if (res.status === "success") {
                    message.success("Customer dihapus");
                    setCustomers(res.data);
                } else {
                    message.error("Gagal hapus");
                }
            })
            .catch(() => message.error("Kesalahan hapus"));
    }

    function handleOkAndImport() {
        const duplicatedRows = importData.filter((row) => row.is_duplicate);
        const filteredData = importData.filter((row) => !row.is_duplicate);

        if (filteredData.length === 0) {
            Modal.warning({
                title: "Semua Data Duplikat",
                content: (
                    <div>
                        <p>Semua data ({duplicatedRows.length}) sudah ada dan tidak akan diimport.</p>
                        <ul>
                            {duplicatedRows.slice(0, 5).map((row, idx) => (
                                <li key={idx}>{row.name}</li>
                            ))}
                            {duplicatedRows.length > 5 && <li>dan lainnya...</li>}
                        </ul>
                    </div>
                ),
                onOk: () => setIsModalOpen(false),
            });
            return;
        }

        setSyncing(true);

        fetch(`/api/accurate/contact-group/${id}/import-customers`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ rows: filteredData }),
        })
            .then((r) => r.json())
            .then((res) => {
                if (res.status === "success") {
                    if (res.imported.length === 0) {
                        message.warning("Semua data duplikat. Tidak ada yang diimport.");
                    } else if (res.duplicates?.length) {
                        message.success("Import berhasil sebagian. Beberapa data duplikat diabaikan.");
                    } else {
                        message.success("Import berhasil.");
                    }

                    setCustomers(res.data);
                    setIsModalOpen(false);
                } else {
                    message.error("Import gagal: " + res.message);
                }
            })
            .catch(() => {
                message.error("Kesalahan saat menghubungi server.");
            })
            .finally(() => {
                setSyncing(false);
            });
    }

    function handleFileUpload(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const wb = XLSX.read(new Uint8Array(e.target.result), { type: "array" });
            const sheet = wb.Sheets[wb.SheetNames[0]];
            const rows = XLSX.utils.sheet_to_json(sheet, { header: 1 });

            const headers = rows[0].map((h) => h.toString().toLowerCase().trim());

            const columnMap = {
                name: ["name", "sub account stock"],
                cut_off: ["cut_off", "tanggal cut off"],
                prov: ["prov", "provinsi"],
                kab_kota: ["kab_kota", "kabupaten"],
                alamat: ["alamat"]
            };

            function findHeaderIndex(possibleNames) {
                return headers.findIndex(h => possibleNames.includes(h.toLowerCase()));
            }

            const data = rows.slice(1).map((r) => {
                const get = (key) => {
                    const idx = findHeaderIndex(columnMap[key]);
                    return idx >= 0 ? r[idx] : "";
                };

                const name = get("name")?.toString().trim();

                return {
                    name,
                    cut_off: get("cut_off"),
                    prov: get("prov"),
                    kab_kota: get("kab_kota"),
                    alamat: get("alamat"),
                    is_duplicate: existingNames.includes(name?.toLowerCase()),
                };
            }).filter((row) => row.name);

            setImportData(data);
        };
        reader.readAsArrayBuffer(file);
        return false;
    }

    function openEditModal(customer) {
        setEditingCustomer(customer);
        setIsEditOpen(true);
    }

    function handleEditSubmit(values) {
        // console.log(editingCustomer)
        fetch(`/api/accurate/contact-group/${id}/import-customers/${editingCustomer.customer_no}`, {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(values),
        })
            .then((r) => r.json())
            .then((res) => {
                if (res.status) {
                    message.success(res.message || "Customer berhasil diupdate");
                    setIsEditOpen(false);
                    fetchCustomers();
                } else {
                    message.error(res.message || "Gagal update");
                }
            })
            .catch(() => message.error("Kesalahan update"));
    }

    const filteredCustomers = customers.filter((c) => {
        const keywords = [
            c.customer_no || "",
            c.customer_name || "",
            c.cut_off || "",
            c.prov || "",
            c.kab_kota || "",
            c.trans_date ? "opname" : "" // biar bisa cari "opname"
        ]
            .join(" ")
            .toLowerCase();

        return keywords.includes(search.toLowerCase());
    });

    return (
        <Layout title={`Detail Contact Group: ${groupName || `#${id}`}`}>
            <div className="card">
                <div className="card-body">
                    <div className="mb-4 flex justify-between items-center gap-4 flex-wrap">
                        <div className="flex items-center gap-3">
                            <div>
                                Total Data Sub Account: <strong>{filteredCustomers.length}</strong>
                            </div>
                            <Input.Search
                                placeholder="Cari nama/customer code..."
                                allowClear
                                onChange={(e) => setSearch(e.target.value)}
                                style={{ width: 250 }}
                            />
                        </div>
                        <div className="flex gap-2">
                            <Button
                                type="primary"
                                icon={<PlusOutlined />}
                                onClick={() => setIsModalOpen(true)}
                            >
                                Import Contact
                            </Button>
                            <Button icon={<ReloadOutlined />} loading={syncing} onClick={fetchCustomers}>Refresh</Button>
                            <Button onClick={() => window.history.back()}>Kembali</Button>
                        </div>
                    </div>

                    <Table
                        rowKey="customer_no"
                        dataSource={filteredCustomers}
                        loading={loading}
                        pagination={paginationConfig}
                        scroll={{ x: "max-content" }}
                        expandable={{
                            expandedRowRender: (record) => <DoHTable customerNo={record.customer_no} />,
                            rowExpandable: (record) => !!record.customer_no,
                        }}
                        columns={[
                            { title: "Customer Code", dataIndex: "customer_no" },
                            { title: "Nama", dataIndex: "customer_name" },
                            {
                                title: "Opname",
                                dataIndex: "trans_date",
                                render: (val) =>
                                    val ? (
                                        <CheckOutlined style={{ color: "green", fontSize: 18 }} />
                                    ) : (
                                        "-"
                                    ),
                            },
                            { title: "Tanggal Cut Off", dataIndex: "trans_date" },
                            { title: "Provinsi", dataIndex: "prov" },
                            { title: "Kabupaten/Kota", dataIndex: "kab_kota" },
                            { title: "Jumlah Hari DoH", dataIndex: "jml_hari" },
                            {
                                title: "Aksi",
                                key: "aksi",
                                render: (_, row) => (
                                    <div className="flex gap-2">
                                        <Button onClick={() => openEditModal(row)}>Edit</Button>
                                        <Popconfirm
                                            title="Hapus kontak?"
                                            onConfirm={() => deleteCustomer(row)}
                                            okText="Ya"
                                            cancelText="Batal"
                                        >
                                            <Button danger icon={<DeleteOutlined />} />
                                        </Popconfirm>
                                    </div>
                                ),
                            },
                        ]}
                    />
                </div>
            </div>

            <Modal
                title="Import Contact"
                open={isModalOpen}
                onOk={handleOkAndImport}
                okText="Import Data"
                cancelText="Batal"
                confirmLoading={syncing}
                onCancel={() => setIsModalOpen(false)}
                okButtonProps={{ disabled: disableImportBtn }}
            >
                <p>
                    Gunakan template{" "}
                    <a href="/assets/template/import-contact-group.xlsx" download>
                        di sini
                    </a>
                </p>
                <Upload
                    accept=".xlsx,.xls"
                    beforeUpload={handleFileUpload}
                    showUploadList={false}
                >
                    <Button icon={<UploadOutlined />}>Pilih File Excel</Button>
                </Upload>
                <div className="mt-4">
                    <ModalImportContactConfirm
                        data={importData}
                        onLoad={(shouldDisable) => setDisableImportBtn(shouldDisable)}
                    />
                </div>
            </Modal>

            <Modal
                title="Edit Customer"
                open={isEditOpen}
                onCancel={() => { setIsEditOpen(false); setEditingCustomer(null); }}
                footer={null}
                destroyOnClose
            >
                <Form
                    layout="vertical"
                    initialValues={editingCustomer}
                    onFinish={handleEditSubmit}
                >
                    <Form.Item name="customer_name" label="Nama" rules={[{ required: true }]}>
                        <Input />
                    </Form.Item>
                    <Form.Item name="cut_off" label="Tanggal Cut Off">
                        <Input />
                    </Form.Item>
                    <Form.Item name="prov" label="Provinsi">
                        <Input />
                    </Form.Item>
                    <Form.Item name="kab_kota" label="Kabupaten/Kota">
                        <Input />
                    </Form.Item>
                    <Form.Item name="jml_hari" label="Jumlah Hari DoH">
                        <Input />
                    </Form.Item>
                    <Form.Item>
                        <Button type="primary" htmlType="submit">
                            Simpan Perubahan
                        </Button>
                    </Form.Item>
                </Form>
            </Modal>
        </Layout>
    );
};

export default ContactGroupDetail;

const DoHTable = ({ customerNo }) => {
    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetch(`/api/accurate/customers/${customerNo}/doh`)
            .then((r) => r.json())
            .then((res) => setData(res.data || []))
            .catch(() => message.error("Gagal ambil data DoH"))
            .finally(() => setLoading(false));
    }, [customerNo]);

    const calcDoH = (stock, sales) => {
        return sales > 0 ? (stock / sales).toFixed(1) : "N/A";
    };

    const low = 10;
    const base = 20;
    const high = 30;

    return (
        <Table
            rowKey="sku"
            dataSource={data}
            loading={loading}
            pagination={false}
            size="small"
            scroll={{ x: "max-content" }}
            columns={[
                { title: "SKU", dataIndex: "item_no" },
                { title: "Nama Produk", dataIndex: "name" },
                { title: "Current Stock", dataIndex: "stock_quantity" },

                { title: "Avg. Daily Sales (Low)", render: () => low },
                { title: "Avg. Daily Sales (Base)", render: () => base },
                { title: "Avg. Daily Sales (High)", render: () => high },
                {
                    title: "DoH (Low)",
                    render: (_, row) => calcDoH(row.stock_quantity, low),
                },
                {
                    title: "DoH (Base)",
                    render: (_, row) => calcDoH(row.stock_quantity, base),
                },
                {
                    title: "DoH (High)",
                    render: (_, row) => calcDoH(row.stock_quantity, high),
                },

                { title: "Keterangan", dataIndex: "note" },
            ]}
        />
    );
};
