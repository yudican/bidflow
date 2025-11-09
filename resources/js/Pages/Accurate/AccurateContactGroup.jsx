import React, { useEffect, useState } from "react"
import {
    Table,
    Button,
    Input,
    message,
    Popconfirm,
    Modal,
    Select,
    Form,
    Radio,
    Card,
    Space,
    Checkbox,
} from "antd"
import {
    ReloadOutlined,
    DeleteOutlined,
    PlusOutlined,
    SettingOutlined,
} from "@ant-design/icons"
import Layout from "../../components/layout"

const AccurateContactGroup = () => {
    const [groups, setGroups] = useState([])
    const [customers, setCustomers] = useState([])
    const [loading, setLoading] = useState(true)
    const [search, setSearch] = useState("")
    const [syncing] = useState(false)
    const [modalVisible, setModalVisible] = useState(false)
    const [dohModalVisible, setDohModalVisible] = useState(false)
    const [selectedGroup, setSelectedGroup] = useState(null)
    const [dohSettings, setDohSettings] = useState([])
    const [dohLoading, setDohLoading] = useState(false)
    const [form] = Form.useForm()
    const [dohForm] = Form.useForm()
    const [currentPage, setCurrentPage] = useState(1)
    const [pageSize, setPageSize] = useState(10)

    const getContactGroups = () => {
        setLoading(true)
        fetch("/api/accurate/contact-group", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ perpage: 5000, search }),
        })
            .then((res) => res.json())
            .then((json) => {
                setGroups(json.data.data || [])
            })
            .catch(() => {
                message.error("Gagal mengambil data Contact Group")
            })
            .finally(() => setLoading(false))
    }

    const getCustomers = () => {
        fetch("/api/accurate/customer")
            .then((res) => res.json())
            .then((json) => {
                const list = json?.data || []
                const mapped = list.map((c) => ({
                    label: `${c.name} (${c.customer_no})`,
                    value: c.customer_no,
                }))
                setCustomers(mapped)
            })
            .catch(() => message.error("Gagal mengambil data customer"))
    }

    const deleteContactGroup = (id) => {
        fetch(`/api/accurate/contact-group/delete/${id}`, {
            method: "POST",
        })
            .then((res) => res.json())
            .then((json) => {
                if (json.status === "success") {
                    message.success(json.message)
                    getContactGroups()
                } else {
                    message.warning(json.message || "Gagal menghapus")
                }
            })
            .catch(() => {
                message.error("Terjadi kesalahan saat menghapus")
            })
    }

    const onAddGroup = (values) => {
        fetch("/api/accurate/contact-group/create", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(values),
        })
            .then((res) => res.json())
            .then((json) => {
                if (json.status === "success") {
                    message.success("Group berhasil ditambahkan")
                    form.resetFields()
                    setModalVisible(false)
                    getContactGroups()
                } else {
                    message.error("Gagal menambahkan group")
                }
            })
            .catch(() => message.error("Terjadi kesalahan"))
    }

    const openDohSettings = (group) => {
        setSelectedGroup(group)
        setDohModalVisible(true)
        getDohSettings(group.id)
    }

    const getDohSettings = (contactGroupId) => {
        setDohLoading(true)
        fetch(`/api/accurate/contact-group/${contactGroupId}/doh-settings`)
            .then((res) => res.json())
            .then((json) => {
                if (json.status === "success") {
                    const settings = json.data || []
                    // Initialize form with existing settings or default values
                    const formData = {}
                    const dohDays = [90, 30, 7]
                    
                    dohDays.forEach(days => {
                        const existing = settings.find(s => s.doh_days === days)
                        if (existing) {
                            formData[`doh_${days}_enabled`] = true
                            formData[`doh_${days}_type`] = existing.notification_type
                            formData[`doh_${days}_email_template`] = existing.email_template || ""
                            formData[`doh_${days}_whatsapp_template`] = existing.whatsapp_template || ""
                        } else {
                            formData[`doh_${days}_enabled`] = false
                            formData[`doh_${days}_type`] = "email"
                            formData[`doh_${days}_email_template`] = ""
                            formData[`doh_${days}_whatsapp_template`] = ""
                        }
                    })
                    
                    dohForm.setFieldsValue(formData)
                    setDohSettings(settings)
                } else {
                    message.error("Gagal mengambil DOH settings")
                }
            })
            .catch(() => message.error("Terjadi kesalahan saat mengambil DOH settings"))
            .finally(() => setDohLoading(false))
    }

    const saveDohSettings = (values) => {
        const settings = []
        const dohDays = [90, 30, 7]
        
        dohDays.forEach(days => {
            if (values[`doh_${days}_enabled`]) {
                const notificationType = values[`doh_${days}_type`]
                settings.push({
                    doh_days: days,
                    notification_type: notificationType,
                    email_template: notificationType === 'email' ? values[`doh_${days}_email_template`] : null,
                    whatsapp_template: notificationType === 'whatsapp' ? values[`doh_${days}_whatsapp_template`] : null,
                    is_active: true
                })
            }
        })

        setDohLoading(true)
        fetch(`/api/accurate/contact-group/${selectedGroup.id}/doh-settings`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ settings }),
        })
            .then((res) => res.json())
            .then((json) => {
                if (json.status === "success") {
                    message.success("DOH settings berhasil disimpan")
                    setDohModalVisible(false)
                    dohForm.resetFields()
                } else {
                    message.error(json.message || "Gagal menyimpan DOH settings")
                }
            })
            .catch(() => message.error("Terjadi kesalahan saat menyimpan"))
            .finally(() => setDohLoading(false))
    }

    useEffect(() => {
        getContactGroups()
        getCustomers()
    }, [])

    const columns = [
        {
            title: "No",
            key: "no",
            render: (_, __, index) => (currentPage - 1) * pageSize + index + 1,
        },
        {
            title: "Nama Group",
            dataIndex: "name",
            key: "name",
        },
        {
            title: "Customer Code (Head Account)",
            dataIndex: "customer_no",
            key: "customer_no",
        },
        {
            title: "Customer Name (Head Account)",
            dataIndex: "customer_name",
            key: "customer_name",
        },
        {
            title: "Deskripsi",
            dataIndex: "description",
            key: "description",
            render: (text) => text || "-",
        },
        {
            title: "Aksi",
            key: "action",
            width: 220,
            align: "center",
            render: (_, record) => (
                <div className="flex gap-1 justify-center">
                    <Button
                        size="small"
                        onClick={() =>
                            (window.location.href = `/accurate-integration/contact-group/detail?id=${record.id}`)
                        }
                    >
                        Lihat Customer
                    </Button>
                    <Button
                        size="small"
                        icon={<SettingOutlined />}
                        onClick={() => openDohSettings(record)}
                        title="DOH Settings"
                    >
                        DOH
                    </Button>
                    <Popconfirm
                        title="Yakin ingin menghapus group ini?"
                        onConfirm={() => deleteContactGroup(record.id)}
                        okText="Ya"
                        cancelText="Batal"
                    >
                        <Button type="link" danger icon={<DeleteOutlined />} />
                    </Popconfirm>
                </div>
            ),
        },
    ]

    const filteredGroups = groups.filter((group) =>
        [group.name, group.description]
            .join(" ")
            .toLowerCase()
            .includes(search.toLowerCase())
    )

    return (
        <Layout title="Contact Group Accurate">
            <div className="card">
                <div className="card-body">
                    <div className="mb-4 flex justify-between items-center gap-4 flex-wrap">
                        <div className="flex items-center gap-3">
                            <div>
                                Total Group: <strong>{filteredGroups.length}</strong>
                            </div>
                            <Input.Search
                                placeholder="Cari nama/deskripsi group..."
                                allowClear
                                onChange={(e) => setSearch(e.target.value)}
                                style={{ width: 250 }}
                            />
                        </div>
                        <div className="flex gap-2">
                            <Button
                                icon={<ReloadOutlined />}
                                loading={syncing}
                                onClick={getContactGroups}
                            >
                                Refresh
                            </Button>
                            <Button
                                type="primary"
                                icon={<PlusOutlined />}
                                onClick={() => setModalVisible(true)}
                            >
                                Tambah Group
                            </Button>
                        </div>
                    </div>

                    <Table
                        rowKey="id"
                        columns={columns}
                        dataSource={filteredGroups}
                        loading={loading}
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

            {/* DEBUG SECTION */}
            {/* <pre>{JSON.stringify(customers, null, 2)}</pre> */}

            {/* Modal Tambah Group */}
            <Modal
                title="Tambah Contact Group"
                open={modalVisible}
                onCancel={() => setModalVisible(false)}
                onOk={() => form.submit()}
                okText="Simpan"
                cancelText="Batal"
                width={600}
            >
                <Form layout="vertical" form={form} onFinish={onAddGroup}>
                    <Form.Item
                        name="name"
                        label="Nama Group"
                        rules={[{ required: true, message: "Wajib diisi" }]}
                    >
                        <Input />
                    </Form.Item>
                    <Form.Item name="description" label="Deskripsi">
                        <Input.TextArea />
                    </Form.Item>
                    <Form.Item
                        name="customer_no"
                        label="Pilih Customer"
                        rules={[{ required: true, message: "Wajib pilih 1 customer" }]}
                    >
                        <Select
                            showSearch
                            placeholder="Pilih customer"
                            options={customers}
                            optionFilterProp="label"
                            loading={customers.length === 0}
                            style={{ width: "100%" }}
                        />
                    </Form.Item>
                </Form>
            </Modal>

            {/* Modal DOH Settings */}
            <Modal
                title={`DOH Settings - ${selectedGroup?.name || ''}`}
                open={dohModalVisible}
                onCancel={() => setDohModalVisible(false)}
                onOk={() => dohForm.submit()}
                okText="Simpan"
                cancelText="Batal"
                width={800}
                confirmLoading={dohLoading}
            >
                <Form layout="vertical" form={dohForm} onFinish={saveDohSettings}>
                    <Form.Item
                        label="Nama Group"
                        style={{ marginBottom: 16 }}
                    >
                        <Input 
                            value={selectedGroup?.name || ''} 
                            disabled 
                            placeholder="Nama group akan muncul di sini"
                        />
                    </Form.Item>
                    
                    <div className="space-y-6">
                        {[90, 30, 7].map(days => (
                            <Card key={days} size="small" title={`DOH ${days} Hari`}>
                                <Space direction="vertical" style={{ width: '100%' }}>
                                    <Form.Item
                                        name={`doh_${days}_enabled`}
                                        valuePropName="checked"
                                        style={{ marginBottom: 8 }}
                                    >
                                        <Checkbox>Aktifkan DOH {days} hari</Checkbox>
                                    </Form.Item>
                                    
                                    <Form.Item
                                        noStyle
                                        shouldUpdate={(prevValues, currentValues) => 
                                            prevValues[`doh_${days}_enabled`] !== currentValues[`doh_${days}_enabled`]
                                        }
                                    >
                                        {({ getFieldValue }) => {
                                            const isEnabled = getFieldValue(`doh_${days}_enabled`)
                                            return isEnabled ? (
                                                <>
                                                    <Form.Item
                                                        name={`doh_${days}_type`}
                                                        label="Tipe Notifikasi"
                                                        rules={[{ required: true, message: "Pilih tipe notifikasi" }]}
                                                    >
                                                        <Radio.Group>
                                                            <Radio value="email">Email</Radio>
                                                            <Radio value="whatsapp">WhatsApp</Radio>
                                                        </Radio.Group>
                                                    </Form.Item>
                                                    
                                                    <Form.Item
                                                        noStyle
                                                        shouldUpdate={(prevValues, currentValues) => 
                                                            prevValues[`doh_${days}_type`] !== currentValues[`doh_${days}_type`]
                                                        }
                                                    >
                                                        {({ getFieldValue }) => {
                                                            const notificationType = getFieldValue(`doh_${days}_type`)
                                                            return notificationType === 'email' ? (
                                                                <Form.Item
                                                                    name={`doh_${days}_email_template`}
                                                                    label="Template Email"
                                                                >
                                                                    <Input.TextArea 
                                                                        rows={3} 
                                                                        placeholder="Template email untuk DOH..."
                                                                    />
                                                                </Form.Item>
                                                            ) : notificationType === 'whatsapp' ? (
                                                                <Form.Item
                                                                    name={`doh_${days}_whatsapp_template`}
                                                                    label="Template WhatsApp"
                                                                >
                                                                    <Input.TextArea 
                                                                        rows={3} 
                                                                        placeholder="Template WhatsApp untuk DOH..."
                                                                    />
                                                                </Form.Item>
                                                            ) : null
                                                        }}
                                                    </Form.Item>
                                                </>
                                            ) : null
                                        }}
                                    </Form.Item>
                                </Space>
                            </Card>
                        ))}
                    </div>
                </Form>
            </Modal>
        </Layout>
    )
}

export default AccurateContactGroup
