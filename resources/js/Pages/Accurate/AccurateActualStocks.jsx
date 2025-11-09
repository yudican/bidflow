import React, { useEffect, useState } from "react"
import { Table, Button, Input, message, Upload, Modal, Tooltip } from "antd"
import {
  ReloadOutlined,
  UploadOutlined,
  DownloadOutlined,
} from "@ant-design/icons"
import Layout from "../../components/layout"
import moment from "moment"

const AccurateActualStocks = () => {
  const [data, setData] = useState([])
  const [loading, setLoading] = useState(true)
  const [search, setSearch] = useState("")
  const [importModalVisible, setImportModalVisible] = useState(false)

  const getActualStocks = () => {
    setLoading(true)
    fetch("/api/accurate/actual-stocks")
      .then((res) => res.json())
      .then((json) => {
        if (json.status === "success") {
          setData(json.data.data || [])
        } else {
          message.error("Gagal mengambil data")
        }
      })
      .catch(() => message.error("Terjadi kesalahan"))
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    getActualStocks()
  }, [])

  const handleImport = async (file) => {
    const formData = new FormData()
    formData.append("file", file)

    try {
      const response = await fetch("/api/accurate/actual-stocks/import", {
        method: "POST",
        body: formData,
      })
      const json = await response.json()

      if (json.status === "success") {
        message.success(json.message)
        getActualStocks()
        setImportModalVisible(false)
      } else {
        message.error(json.message || "Gagal import data")
      }
    } catch (error) {
      message.error("Terjadi kesalahan saat import")
    }
    return false
  }

  const downloadTemplate = () => {
    window.location.href = "/api/accurate/actual-stocks/template"
  }

  const columns = [
    {
      title: "Count ID",
      dataIndex: "count_id",
      key: "count_id",
    },
    {
      title: "Tanggal",
      dataIndex: "date",
      key: "date",
      render: (text) => moment(text).format("DD/MM/YYYY"),
    },
    {
      title: "Customer Code",
      dataIndex: "customer_id",
      key: "customer_id",
    },
    {
      title: "Barcode",
      dataIndex: "product_code",
      key: "product_code",
    },
    {
      title: "Stock Aktual",
      dataIndex: "actual_stock",
      key: "actual_stock",
      align: "right",
    },
    {
      title: "PIC",
      dataIndex: "pic_name",
      key: "pic_name",
    },
    {
      title: "Catatan",
      dataIndex: "notes",
      key: "notes",
      render: (text) => (
        <Tooltip title={text}>
          {text?.length > 30 ? text.substring(0, 30) + "..." : text}
        </Tooltip>
      ),
    },
    {
      title: "Key",
      dataIndex: "key",
      key: "key",
    },
    {
      title: "Upload By",
      dataIndex: "upload_by",
      key: "upload_by",
    },
  ]

  const filteredData = data.filter((item) =>
    Object.values(item).join(" ").toLowerCase().includes(search.toLowerCase())
  )

  return (
    <Layout title="Actual Stocks">
      <div className="card">
        <div className="card-body">
          <div className="mb-4 flex justify-between items-center gap-4 flex-wrap">
            <div className="flex items-center gap-3">
              <div>
                Total Data: <strong>{filteredData.length}</strong>
              </div>
              <Input.Search
                placeholder="Cari data..."
                allowClear
                onChange={(e) => setSearch(e.target.value)}
                style={{ width: 250 }}
              />
            </div>
            <div className="flex gap-2">
              <Button icon={<ReloadOutlined />} onClick={getActualStocks}>
                Refresh
              </Button>
              <Button
                type="primary"
                icon={<UploadOutlined />}
                onClick={() => setImportModalVisible(true)}
              >
                Import Data
              </Button>
            </div>
          </div>

          <Table
            rowKey="id"
            columns={columns}
            dataSource={filteredData}
            loading={loading}
            scroll={{ x: "max-content" }}
          />
        </div>
      </div>

      <Modal
        title="Import Actual Stocks"
        open={importModalVisible}
        onCancel={() => setImportModalVisible(false)}
        footer={null}
      >
        <div className="flex flex-col gap-4">
          <div>
            <Button icon={<DownloadOutlined />} onClick={downloadTemplate}>
              Download Template
            </Button>
          </div>
          <Upload.Dragger
            name="file"
            accept=".xlsx,.xls"
            beforeUpload={handleImport}
            showUploadList={false}
          >
            <p className="ant-upload-drag-icon">
              <UploadOutlined />
            </p>
            <p className="ant-upload-text">
              Klik atau drag file Excel ke area ini
            </p>
            <p className="ant-upload-hint">
              Format file harus sesuai template yang disediakan
            </p>
          </Upload.Dragger>
        </div>
      </Modal>
    </Layout>
  )
}

export default AccurateActualStocks
