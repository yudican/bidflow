import React, { useEffect, useState, useMemo } from "react"
import {
  Table,
  Button,
  Input,
  message,
  Modal,
  Form,
  Checkbox,
  Space,
  Typography,
} from "antd"
import { DownloadOutlined } from "@ant-design/icons"
import Layout from "../../components/layout"

const { Text } = Typography

const AccurateMerchandiser = () => {
  const [merchandisers, setMerchandisers] = useState([])
  const [stores, setStores] = useState([])
  const [loading, setLoading] = useState(true)
  const [search, setSearch] = useState("")
  const [storeSearch, setStoreSearch] = useState("")
  const [currentPage, setCurrentPage] = useState(1)
  const [pageSize, setPageSize] = useState(10)

  const [isModalOpen, setIsModalOpen] = useState(false)
  const [selectedMerchandiser, setSelectedMerchandiser] = useState(null)
  const [selectedStores, setSelectedStores] = useState([])
  const [form] = Form.useForm()

  const getMerchandisers = () => {
    fetch("/api/accurate/merchandiser")
      .then((res) => res.json())
      .then((json) => {
        setMerchandisers(json.data)
        setLoading(false)
      })
      .catch(() => setLoading(false))
  }

  const getStores = () => {
    fetch("/api/accurate/stores")
      .then((res) => res.json())
      .then((json) => {
        setStores(json.data)
      })
      .catch(() => { })
  }

  useEffect(() => {
    getMerchandisers()
    getStores()
  }, [])

  const sortedStores = useMemo(() => {
    return [...stores].sort((a, b) =>
      a.customer_name.localeCompare(b.customer_name, "id", {
        sensitivity: "base",
      })
    )
  }, [stores])

  const filteredStores = useMemo(() => {
    if (!stores || stores.length === 0) return []

    const searchTerm = storeSearch.trim().toLowerCase()

    if (!searchTerm) {
      return [...stores].sort((a, b) =>
        (a.customer_name || "").localeCompare(b.customer_name || "", "id", {
          sensitivity: "base",
        })
      )
    }

    const filtered = stores.filter((store) => {
      const name = (store.customer_name || "").toLowerCase()
      const group = (store.contact_group_name || "").toLowerCase()
      return name.includes(searchTerm) || group.includes(searchTerm)
    })

    return filtered.sort((a, b) =>
      (a.customer_name || "").localeCompare(b.customer_name || "", "id", {
        sensitivity: "base",
      })
    )
  }, [stores, storeSearch])

  const columns = [
    {
      title: "No",
      key: "no",
      render: (_, __, index) => (currentPage - 1) * pageSize + index + 1,
    },
    {
      title: "Nama User",
      dataIndex: "nama_user_merchandiser",
      key: "nama_user_merchandiser",
    },
    {
      title: "Role User",
      dataIndex: "nama_role_merchandiser",
      key: "nama_role_merchandiser",
    },
    {
      title: "Jumlah Toko",
      dataIndex: "jumlah_toko",
      key: "jumlah_toko",
    },
    {
      title: "Action",
      key: "action",
      render: (_, record) => (
        <Button type="primary" size="small" onClick={() => handleEdit(record)}>
          Assign Store
        </Button>
      ),
    },
  ]

  const filteredMerchandisers = merchandisers.filter((m) =>
    [m.nama_user_merchandiser, m.jumlah_toko]
      .join(" ")
      .toLowerCase()
      .includes(search.toLowerCase())
  )

  const handleEdit = (record) => {
    setSelectedMerchandiser(record)
    setSelectedStores(record.toko_ids || [])
    form.setFieldsValue({
      merchandiser: record.nama_user_merchandiser,
    })
    setIsModalOpen(true)
  }

  const handleModalClose = () => {
    setIsModalOpen(false)
    form.resetFields()
    setSelectedStores([])
    setSelectedMerchandiser(null)
  }

  const handleSave = async () => {
    // Ensure selectedStores is always an array, even if empty (for unassigning)
    const storesToAssign = Array.isArray(selectedStores) ? selectedStores : []

    // Show confirmation dialog when unassigning all stores
    if (storesToAssign.length === 0) {
      Modal.confirm({
        title: "Konfirmasi Unassign",
        content: `Apakah Anda yakin ingin meng-unassign ${selectedMerchandiser?.nama_user_merchandiser} dari semua toko?`,
        okText: "Ya, Unassign",
        cancelText: "Batal",
        onOk: () => performSave(storesToAssign),
      })
    } else {
      performSave(storesToAssign)
    }
  }

  const performSave = async (storesToAssign) => {
    try {
      setLoading(true)

      const response = await fetch(
        `/api/accurate/merchandiser/${selectedMerchandiser.id}/stores`,
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document
              .querySelector('meta[name="csrf-token"]')
              .getAttribute("content"),
          },
          body: JSON.stringify({
            toko: storesToAssign,
          }),
        }
      )

      const result = await response.json()

      if (result.status === "success") {
        const actionMessage =
          storesToAssign.length === 0
            ? "Merchandiser berhasil di-unassign dari semua toko"
            : "Merchandiser berhasil diperbarui"

        message.success(actionMessage)
        setIsModalOpen(false)
        form.resetFields()
        setSelectedStores([])
        getMerchandisers()
      } else {
        message.error(result.message || "Gagal memperbarui merchandiser")
      }
    } catch (error) {
      console.error("Error updating merchandiser:", error)
      message.error("Terjadi kesalahan saat memperbarui merchandiser")
    } finally {
      setLoading(false)
    }
  }

  // === EXPORT CSV ===
  const handleExport = () => {
    if (!filteredMerchandisers.length) {
      message.warning("Tidak ada data untuk diexport")
      return
    }

    const header = ["No", "Nama Merchandiser", "Jumlah Toko", "Nama Store"]

    const rows = filteredMerchandisers.map((m, i) => {
      // cari nama store berdasarkan toko_ids
      const storeNames = (m.toko_ids || [])
        .map((id) => {
          const s = stores.find((st) => st.id === id)
          return s ? `${s.customer_name} (${s.contact_group_name})` : null
        })
        .filter(Boolean) // buang null
        .join(" | ") // pisahkan pakai |

      return [i + 1, m.nama_user_merchandiser, m.jumlah_toko, storeNames || "-"]
    })

    const csvContent =
      "data:text/csv;charset=utf-8," +
      [header, ...rows].map((e) => e.join(",")).join("\n")

    const encodedUri = encodeURI(csvContent)
    const link = document.createElement("a")
    link.setAttribute("href", encodedUri)
    link.setAttribute("download", "merchandisers.csv")
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
  }

  return (
    <Layout title="Assign User SCO & MD">
      <div className="card">
        <div className="card-body">
          <div className="mb-4 flex justify-between items-center gap-4 flex-wrap">
            <div className="flex items-center gap-3 flex-wrap">
              <div>
                Total User:{" "}
                <strong>{filteredMerchandisers.length}</strong>
              </div>
              <Input.Search
                placeholder="Cari merchandiser"
                allowClear
                onChange={(e) => setSearch(e.target.value)}
                style={{ width: 300 }}
              />
            </div>
            <div className="flex items-center gap-2">
              <Button
                type="default"
                icon={<DownloadOutlined />}
                onClick={handleExport}
              >
                Export CSV
              </Button>
            </div>
          </div>

          <Table
            rowKey={(record, index) => index}
            columns={columns}
            dataSource={filteredMerchandisers}
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

          {/* Modal Edit */}
          <Modal
            title="Assign Merchandiser"
            open={isModalOpen}
            onCancel={handleModalClose}
            onOk={handleSave}
            okText="Simpan"
            cancelText="Batal"
            width={600}
            confirmLoading={loading}
          >
            <Form form={form} layout="vertical">
              <Form.Item label="Nama User" name="merchandiser">
                <Input disabled />
              </Form.Item>
              <Form.Item label="Toko">
                <div style={{ maxHeight: "400px", overflowY: "auto" }}>
                  <Input.Search
                    placeholder="Cari nama toko..."
                    allowClear
                    onChange={(e) => setStoreSearch(e.target.value)}
                    style={{ marginBottom: 10 }}
                  />
                  <Table
                    dataSource={filteredStores}
                    pagination={false}
                    size="small"
                    rowKey="id"
                    columns={[
                      {
                        title: "Pilih",
                        dataIndex: "id",
                        width: 60,
                        render: (id, record) => {
                          const isSelected = selectedStores.includes(record.id)
                          const isAssignedToOther =
                            record.id_user &&
                            record.id_user !== selectedMerchandiser?.id
                          const isDisabled = isAssignedToOther

                          return (
                            <Checkbox
                              checked={isSelected}
                              disabled={isDisabled || loading}
                              onChange={(e) => {
                                setSelectedStores((prev) =>
                                  e.target.checked
                                    ? [...new Set([...prev, record.id])]
                                    : prev.filter((id) => id !== record.id)
                                )
                              }}
                            />
                          )
                        },
                      },
                      {
                        title: "Nama Toko",
                        dataIndex: "customer_name",
                        render: (text, record) => {
                          const isAssignedToOther =
                            record.id_user &&
                            record.id_user !== selectedMerchandiser?.id
                          return (
                            <Space direction="vertical" size={0}>
                              <Text
                                style={{
                                  color: isAssignedToOther ? "#999" : "inherit",
                                }}
                              >
                                {text}
                              </Text>
                              {isAssignedToOther && (
                                <Text
                                  type="secondary"
                                  style={{ fontSize: "12px" }}
                                >
                                  Sudah ditugaskan ke: {record.assigned_to}
                                </Text>
                              )}
                            </Space>
                          )
                        },
                      },
                      {
                        title: "Nama PT",
                        dataIndex: "contact_group_name",
                        render: (text, record) => {
                          const isAssignedToOther =
                            record.id_user &&
                            record.id_user !== selectedMerchandiser?.id
                          return (
                            <Text
                              style={{
                                color: isAssignedToOther ? "#999" : "inherit",
                              }}
                            >
                              {text}
                            </Text>
                          )
                        },
                      },
                    ]}
                  />
                </div>
              </Form.Item>
            </Form>
          </Modal>
        </div>
      </div>
    </Layout>
  )
}

export default AccurateMerchandiser
