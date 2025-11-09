import React, { useEffect, useState } from "react"
import { Button, Input, Card, DatePicker, Space } from "antd"
import {
  SearchOutlined,
  CloseCircleFilled,
  FilterOutlined,
  ReloadOutlined,
} from "@ant-design/icons"
import { useParams, useNavigate } from "react-router-dom"
import Layout from "../../components/layout"

const ListMerchandiserDetail = () => {
  const { id } = useParams()
  const navigate = useNavigate()
  const [merchandiser, setMerchandiser] = useState(null)
  const [stores, setStores] = useState([])
  const [filteredStores, setFilteredStores] = useState([])
  const [loading, setLoading] = useState(false)
  const [search, setSearch] = useState("")
  const [isSearch, setIsSearch] = useState(false)
  const [dateRange, setDateRange] = useState([null, null])
  const [isFiltered, setIsFiltered] = useState(false)

  const fetchMerchandiserStores = async () => {
    setLoading(true)
    try {
      const response = await fetch(`/api/accurate/merchandiser/${id}/stores`)
      if (response.ok) {
        const result = await response.json()
        console.log('API Response:', result)
        const data = result.data || result // Handle both nested and flat response structures
        console.log('Parsed data:', data)
        console.log('Stores:', data.stores)
        setMerchandiser(data.merchandiser)
        setStores(data.stores || [])
        setFilteredStores(data.stores || [])
      } else {
        console.error('Failed to fetch merchandiser stores')
        setStores([])
        setFilteredStores([])
      }
    } catch (error) {
      console.error('Error fetching merchandiser stores:', error)
      setStores([])
      setFilteredStores([])
    } finally {
      setLoading(false)
    }
  }

  const handleSearch = (value) => {
    setSearch(value)
    setIsSearch(value.length > 0)

    if (value.trim() === "") {
      setFilteredStores(stores)
    } else {
      const filtered = stores.filter((store) =>
        store.nama_store.toLowerCase().includes(value.toLowerCase())
      )
      setFilteredStores(filtered)
    }
  }

  const clearSearch = () => {
    setSearch("")
    setIsSearch(false)
    setFilteredStores(stores)
  }

  const handleFilter = () => {
    setIsFiltered(true);

    if (dateRange[0] && dateRange[1]) {
      const [start, end] = dateRange;

      // pastikan rentang waktu mencakup seluruh hari
      const startDate = new Date(start.startOf("day"));
      const endDate = new Date(end.endOf("day"));

      const filtered = stores.map((store) => {
        if (!store.last_update) return { ...store, isActive: false }; // skip null
        const storeDate = new Date(store.last_update);
        const isActive = storeDate >= startDate && storeDate <= endDate;
        return { ...store, isActive };
      });

      const sorted = filtered.sort((a, b) => {
        if (a.isActive === b.isActive) return 0;
        return a.isActive ? -1 : 1;
      });

      setFilteredStores(sorted);
    } else {
      const reset = stores.map((s) => ({ ...s, isActive: true }));
      setFilteredStores(reset);
    }
  };


  const handleReset = () => {
    setIsFiltered(false)
    setDateRange([null, null])
    setSearch("")
    setIsSearch(false)
    const reset = stores.map((s) => ({ ...s, isActive: true }))
    setFilteredStores(reset)
  }

  const getStoreInitials = (storeName) => {
    return storeName
      .split(" ")
      .map((word) => word.charAt(0))
      .join("")
      .toUpperCase()
      .slice(0, 2)
  }

  const handleViewData = (store) => {
    // Navigate to StoreStockCount page with store customer_no and user ID
    navigate(`/accurate-integration/store-stock-count/${store.customer_no}/${id}`)
  }

  const visitedStores = filteredStores.filter(store => store.stock_count > 0);
  const notVisitedStores = filteredStores.filter(store => store.stock_count === 0);

  useEffect(() => {
    if (id) {
      fetchMerchandiserStores()
    }
  }, [id])

  return (
    <Layout title="Detail Merchandiser">
      <div className="card">
        <div className="card-body">
          {/* Merchandiser Info Section */}
          {merchandiser && (
            <div className="row mb-4">
              <div className="col-12">
                <Card
                  style={{
                    backgroundColor: "#f8f9fa",
                    border: "1px solid #e9ecef",
                    borderRadius: "12px",
                  }}
                >
                  <div style={{ display: "flex", alignItems: "center", gap: "16px" }}>
                    <div
                      style={{
                        width: "60px",
                        height: "60px",
                        backgroundColor: "#003E8A",
                        borderRadius: "8px",
                        display: "flex",
                        alignItems: "center",
                        justifyContent: "center",
                        fontSize: "24px",
                        fontWeight: "bold",
                        color: "white",
                      }}
                    >
                      {getStoreInitials(merchandiser.name)}
                    </div>
                    <div style={{ flex: 1 }}>
                      <h2 style={{ margin: 0, fontSize: "20px", fontWeight: "bold", color: "#333" }}>
                        {merchandiser.name}
                      </h2>
                      <p style={{ margin: "4px 0 0 0", color: "#666", fontSize: "14px" }}>
                        {merchandiser.email}
                      </p>
                    </div>
                    <div style={{ textAlign: "center" }}>
                      <div style={{ fontSize: "14px", color: "#666", marginBottom: "4px" }}>
                        Total Stores
                      </div>
                      <div style={{ fontSize: "24px", fontWeight: "bold", color: "#003E8A" }}>
                        {stores.length}
                      </div>
                    </div>
                  </div>
                </Card>
              </div>
            </div>
          )}

          {/* Search Section */}
          <div className="row mb-4">
            <div className="col-md-4 col-sm-6 col-12">
              <p>Search Subaccount</p>
              <Input
                placeholder="Search Subaccount..."
                size={"large"}
                className="rounded"
                value={search}
                onChange={(e) => handleSearch(e.target.value)}
                suffix={
                  isSearch ? (
                    <CloseCircleFilled
                      onClick={clearSearch}
                      style={{ color: "#999", cursor: "pointer" }}
                    />
                  ) : (
                    <SearchOutlined style={{ color: "#999" }} />
                  )
                }
              />
            </div>
            <div className="col-md-8">
              <div
                style={{
                  display: "flex",
                  gap: "16px",
                  alignItems: "center",
                  justifyContent: "flex-end",
                }}
              >
                <Card style={{ minWidth: "200px", textAlign: "center" }}>
                  <div style={{ fontSize: "14px", color: "#666", marginBottom: "4px" }}>
                    Sudah Divisit
                  </div>
                  <div
                    style={{
                      fontSize: "32px",
                      fontWeight: "bold",
                      color: "#003E8A",
                    }}
                  >
                    {visitedStores.length}
                  </div>
                </Card>

                <Card style={{ minWidth: "200px", textAlign: "center" }}>
                  <div style={{ fontSize: "14px", color: "#666", marginBottom: "4px" }}>
                    Belum Divisit
                  </div>
                  <div
                    style={{
                      fontSize: "32px",
                      fontWeight: "bold",
                      color: "#003E8A",
                    }}
                  >
                    {notVisitedStores.length}
                  </div>
                </Card>

                {isFiltered && (
                  <div
                    style={{
                      padding: "4px 12px",
                      backgroundColor: "#e6f7ff",
                      border: "1px solid #91d5ff",
                      borderRadius: "6px",
                      fontSize: "12px",
                      color: "#1890ff",
                    }}
                  >
                    Filter aktif
                  </div>
                )}
              </div>
            </div>
          </div>

          {/* Date Filter Section */}
          <div className="row mb-4">
            <div className="col-md-12">
              <div style={{ display: "flex", gap: "16px", alignItems: "center" }}>
                <Space>
                  <DatePicker.RangePicker
                    value={dateRange}
                    onChange={setDateRange}
                    placeholder={["Start Date", "End Date"]}
                    style={{ borderRadius: "8px" }}
                  />
                  <Button
                    type="primary"
                    icon={<FilterOutlined />}
                    onClick={handleFilter}
                    style={{ borderRadius: "8px" }}
                  >
                    Filter
                  </Button>
                  <Button
                    icon={<ReloadOutlined />}
                    onClick={handleReset}
                    style={{ borderRadius: "8px" }}
                  >
                    Reset
                  </Button>
                </Space>
              </div>
            </div>
          </div>

          {/* Content - Store Cards Grid */}
          <div style={{ minHeight: "400px" }}>
            {loading ? (
              <div
                style={{
                  display: "flex",
                  justifyContent: "center",
                  alignItems: "center",
                  height: "200px",
                }}
              >
                Loading...
              </div>
            ) : (
              <div
                style={{
                  display: "grid",
                  gridTemplateColumns: "repeat(4, 1fr)",
                  gap: "24px",
                }}
              >
                {console.log('Rendering stores, filteredStores length:', filteredStores.length)}
                {console.log('filteredStores:', filteredStores)}
                {filteredStores.map((store) => (
                  <Card
                    key={store.id}
                    style={{
                      borderRadius: "12px",
                      boxShadow: "0 4px 12px rgba(0,0,0,0.1)",
                      border: store.isActive ? "2px solidrgb(231, 46, 13)" : "1px solid #f0f0f0",
                      overflow: "hidden",
                      opacity: store.stock_count === 0 ? 0.4 : 1,
                      filter: store.stock_count === 0 ? "grayscale(100%)" : "none",
                      transition: "0.3s ease",
                    }}
                    bodyStyle={{ padding: "0" }}
                  >
                    <div
                      style={{
                        display: "flex",
                        flexDirection: "column",
                        height: "100%",
                      }}
                    >
                      {/* Avatar Section with Background */}
                      <div
                        style={{
                          backgroundColor: "#20B2AA",
                          padding: "24px",
                          display: "flex",
                          justifyContent: "center",
                          alignItems: "center",
                          minHeight: "120px",
                        }}
                      >
                        <div
                          style={{
                            width: "60px",
                            height: "60px",
                            backgroundColor: "white",
                            borderRadius: "8px",
                            display: "flex",
                            alignItems: "center",
                            justifyContent: "center",
                            fontSize: "24px",
                            fontWeight: "bold",
                            color: "#20B2AA",
                          }}
                        >
                          {getStoreInitials(store.name || store.nama_store)}
                        </div>
                      </div>

                      {/* Content Section */}
                      <div
                        style={{
                          padding: "20px",
                          display: "flex",
                          flexDirection: "column",
                          gap: "16px",
                          flex: 1,
                          textAlign: "center",
                        }}
                      >
                        {/* Store Name */}
                        <h3
                          style={{
                            margin: 0,
                            fontSize: "16px",
                            fontWeight: "bold",
                            color: "#333",
                            lineHeight: "1.4",
                          }}
                        >
                          {store.name || store.nama_store}
                        </h3>

                        {/* Stock Count Label */}
                        <div
                          style={{
                            fontSize: "14px",
                            color: "#666",
                            marginBottom: "4px",
                          }}
                        >
                          Data Stock Count
                        </div>

                        {/* Stock Count Number */}
                        <div
                          style={{
                            fontSize: "32px",
                            fontWeight: "bold",
                            color: "#003E8A",
                            marginBottom: "16px",
                          }}
                        >
                          {store.stock_count || 0}
                        </div>

                        {/* CTA Button */}
                        <Button
                          type="primary"
                          block
                          onClick={() => handleViewData(store)}
                          style={{
                            borderRadius: "8px",
                            backgroundColor: "#003E8A",
                            borderColor: "#003E8A",
                            fontWeight: "500",
                            height: "40px",
                          }}
                        >
                          Lihat Data
                        </Button>
                      </div>
                    </div>
                  </Card>
                ))}
              </div>
            )}
          </div>
        </div>
      </div>
    </Layout>
  )
}

export default ListMerchandiserDetail
