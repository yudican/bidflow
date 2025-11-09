import React, { useEffect, useState } from "react"
import {
  Button,
  Input,
  Card,
  Avatar,
  Divider,
} from "antd"
import {
  UserOutlined,
  SearchOutlined,
  CloseCircleFilled,
} from "@ant-design/icons"
import { useNavigate } from "react-router-dom"
import Layout from "../../components/layout"

const ListMerchandiser = () => {
  const navigate = useNavigate()
  const [merchandisers, setMerchandisers] = useState([])
  const [loading, setLoading] = useState(false)
  const [search, setSearch] = useState("")
  const [isSearch, setIsSearch] = useState(false)

  const getMerchandisers = () => {
    setLoading(true)
    fetch("/api/accurate/merchandiser")
      .then((res) => res.json())
      .then((json) => {
        const data = json.data || []
        setMerchandisers(data)
        setLoading(false)
      })
      .catch(() => {
        setMerchandisers([])
        setLoading(false)
      })
  }



  useEffect(() => {
    getMerchandisers()
  }, [])

  const handleChangeSearch = () => {
    setIsSearch(true)
    // Search akan dilakukan di frontend dengan filteredMerchandisers
  }

  const formatDate = (dateString) => {
    if (!dateString) return "Belum ada update"
    return new Date(dateString).toLocaleDateString("id-ID", {
      day: "numeric",
      month: "long",
      year: "numeric",
    })
  }

  const filteredMerchandisers = merchandisers.filter((m) =>
    (m.nama_user_merchandiser || "")
      .toLowerCase()
      .includes(search.toLowerCase())
  )



  // const rightContent = (
  //   <div className="flex justify-between items-center">
  //     <strong className="text-red-400">Total Data: {total}</strong>
  //   </div>
  // )

  return (
    <Layout
      // rightContent={rightContent}
      title="List SCO & MD"
    >
      <div className="card">
        <div className="card-body">
          <div className="row mb-4">
            <div className="col-md-12"></div>
            <div className="col-md-4 col-sm-6 col-12">
              <p>Pencarian</p>
              <Input
                placeholder="Cari berdasarkan nama User"
                size={"large"}
                className="rounded"
                onPressEnter={() => handleChangeSearch()}
                suffix={
                  isSearch ? (
                    <CloseCircleFilled
                      onClick={() => {
                        getMerchandisers()
                        setSearch("")
                        setIsSearch(false)
                      }}
                    />
                  ) : (
                    <SearchOutlined onClick={() => handleChangeSearch()} />
                  )
                }
                value={search}
                onChange={(e) => setSearch(e.target.value)}
              />
            </div>
            <div className="col-md-8">
              <Card
                style={{
                  minWidth: "200px",
                  textAlign: "center",
                  float: "right",
                }}
              >
                <div
                  style={{
                    fontSize: "14px",
                    color: "#666",
                    marginBottom: "4px",
                  }}
                >
                  Total User
                </div>
                <div
                  style={{
                    fontSize: "32px",
                    fontWeight: "bold",
                    color: "#003E8A",
                  }}
                >
                  {filteredMerchandisers.length}
                </div>
              </Card>
            </div>
          </div>

          {/* Cards Grid */}
          {loading ? (
            <div style={{ textAlign: "center", padding: "48px" }}>
              Loading...
            </div>
          ) : (
            <div
              style={{
                display: "grid",
                gridTemplateColumns: "repeat(auto-fit, minmax(400px, 1fr))",
                gap: "24px",
              }}
            >
              {filteredMerchandisers.map((merchandiser, index) => (
                <Card
                  key={index}
                  style={{
                    borderRadius: "12px",
                    boxShadow: "0 4px 12px rgba(0,0,0,0.1)",
                  }}
                  bodyStyle={{ padding: "24px" }}
                >
                  <div
                    style={{
                      display: "flex",
                      alignItems: "flex-start",
                      gap: "16px",
                    }}
                  >
                    <Avatar
                      size={48}
                      icon={<UserOutlined />}
                      style={{ backgroundColor: "#003E8A", flexShrink: 0 }}
                    />
                    <div style={{ flex: 1 }}>
                      <h3
                        style={{
                          margin: "0 0 8px 0",
                          fontSize: "18px",
                          fontWeight: "bold",
                        }}
                      >
                        {merchandiser.nama_user_merchandiser ||
                          "Nama tidak tersedia"}
                      </h3>
                      <p
                        style={{
                          margin: "0 0 12px 0",
                          fontSize: "12px",
                          color: "#666",
                        }}
                      >
                        Terakhir pengisian data pada tanggal:{" "}
                        {formatDate(merchandiser.tanggal_update_stock_count)}
                      </p>
                      <div style={{ marginBottom: "12px" }}>
                        <div style={{ fontSize: "14px", fontWeight: "bold" }}>
                          Jml. Data Stock Diinput:{" "}
                          {merchandiser.jumlah_data_stock || 0}
                        </div>
                      </div>

                      <Divider />

                      <div style={{ marginBottom: "16px" }}>
                        <span
                          style={{
                            color: "#37953B",
                            fontWeight: "bold",
                            fontSize: "14px",
                          }}
                        >
                          Jml. Store {merchandiser.jumlah_toko || 0}
                        </span>
                      </div>
                      <Button
                        type="primary"
                        block
                        size="large"
                        onClick={() => navigate(`/accurate-integration/list-merchandiser/${merchandiser.id}`)}
                        style={{
                          borderRadius: "8px",
                          backgroundColor: "#080E7D",
                        }}
                      >
                        Detail
                      </Button>
                    </div>
                  </div>
                </Card>
              ))}
            </div>
          )}

          {/* Pagination removed for now since we're doing frontend filtering */}
        </div>
      </div>


    </Layout>
  )
}

export default ListMerchandiser
