import {
  CloseCircleFilled,
  DeleteOutlined,
  EditOutlined,
  PlusOutlined,
  SearchOutlined,
  CopyOutlined,
  EyeOutlined,
} from "@ant-design/icons"
import {
  Input,
  Pagination,
  Popconfirm,
  Table,
  Tooltip,
  Button,
  Space,
  message,
} from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { useNavigate } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../../components/layout"
import FilterModal from "./Components/FilterModal"
import { urlShortenerListColumn } from "./config"

const UrlShortenerList = () => {
  const navigate = useNavigate()
  const [dataUrlShortener, setDataUrlShortener] = useState([])
  const [loading, setLoading] = useState(false)
  const [currentPage, setCurrentPage] = useState(1)
  const [pageSize, setPageSize] = useState(10)
  const [totalData, setTotalData] = useState(0)
  const [searchTerm, setSearchTerm] = useState("")

  useEffect(() => {
    fetchUrlShorteners()
  }, [currentPage, pageSize, searchTerm])

  const fetchUrlShorteners = async () => {
    setLoading(true)
    try {
      const response = await axios.get("/api/master/url-shortener", {
        params: {
          page: currentPage,
          per_page: pageSize,
          search: searchTerm,
        },
      })
      setDataUrlShortener(response.data.data.data || [])
      setTotalData(response.data.data.total || 0)
    } catch (error) {
      toast.error("Failed to fetch URL shorteners")
      setDataUrlShortener([])
      setTotalData(0)
    } finally {
      setLoading(false)
    }
  }

  const handleDelete = async (id) => {
    try {
      await axios.delete(`/api/master/url-shortener/${id}`)
      toast.success("URL shortener deleted successfully")
      fetchUrlShorteners()
    } catch (_error) {
      toast.error("Failed to delete URL shortener")
    }
  }

  const copyToClipboard = async (text) => {
    try {
      await navigator.clipboard.writeText(text)
      message.success("URL copied to clipboard!")
    } catch (_error) {
      message.error("Failed to copy URL")
    }
  }

  const actionColumn = {
    title: "Action",
    key: "action",
    width: 200,
    render: (_, record) => (
      <Space size="middle">
        <Tooltip title="Copy Short URL">
          <Button
            type="text"
            size="small"
            icon={<CopyOutlined />}
            onClick={() => copyToClipboard(record.short_url)}
          />
        </Tooltip>
        <Tooltip title="Preview">
          <Button
            type="text"
            size="small"
            icon={<EyeOutlined />}
            onClick={() => window.open(record.short_url, "_blank")}
          />
        </Tooltip>
        <Tooltip title="Edit">
          <Button
            type="text"
            size="small"
            icon={<EditOutlined />}
            onClick={() => navigate(`/master/url-shortener/form/${record.id}`)}
          />
        </Tooltip>
        <Popconfirm
          title="Are you sure you want to delete this URL shortener?"
          onConfirm={() => handleDelete(record.id)}
          okText="Yes"
          cancelText="No"
        >
          <Tooltip title="Delete">
            <Button
              type="text"
              size="small"
              danger
              icon={<DeleteOutlined />}
            />
          </Tooltip>
        </Popconfirm>
      </Space>
    ),
  }

  const columns = [...urlShortenerListColumn, actionColumn]

  return (
    <Layout>
      <div className="content-wrapper">
        <div className="content-header">
          <div className="container-fluid">
            <div className="row mb-2">
              <div className="col-sm-6">
                <h1 className="m-0">URL Shortener Management</h1>
              </div>
            </div>
          </div>
        </div>

        <section className="content">
          <div className="container-fluid">
            <div className="row">
              <div className="col-12">
                <div className="card">
                  <div className="card-header">
                    <div className="row">
                      <div className="col-md-6">
                        <Button
                          type="primary"
                          icon={<PlusOutlined />}
                          onClick={() => navigate("/master/url-shortener/form")}
                        >
                          Add URL Shortener
                        </Button>
                      </div>
                      <div className="col-md-6">
                        <div className="d-flex justify-content-end">
                          <Input
                            placeholder="Search URL shorteners..."
                            prefix={<SearchOutlined />}
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                            style={{ width: 300 }}
                            suffix={
                              searchTerm && (
                                <CloseCircleFilled
                                  onClick={() => setSearchTerm("")}
                                  style={{ cursor: "pointer" }}
                                />
                              )
                            }
                          />
                          <FilterModal
                            handleOk={(filters) => {
                              // Handle filter logic here
                              console.log("Filters:", filters)
                            }}
                          />
                        </div>
                      </div>
                    </div>
                  </div>

                  <div className="card-body">
                    <Table
                      dataSource={dataUrlShortener}
                      columns={columns}
                      loading={loading}
                      rowKey="id"
                      pagination={false}
                      scroll={{ x: 1200 }}
                    />
                    
                    <div className="d-flex justify-content-end mt-3">
                      <Pagination
                        current={currentPage}
                        pageSize={pageSize}
                        total={totalData}
                        showSizeChanger
                        showQuickJumper
                        showTotal={(total, range) =>
                          `${range[0]}-${range[1]} of ${total} items`
                        }
                        onChange={(page, size) => {
                          setCurrentPage(page)
                          setPageSize(size)
                        }}
                      />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </Layout>
  )
}

export default UrlShortenerList