import { Button } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { useNavigate } from "react-router-dom"
import LoadingFallback from "../../components/LoadingFallback"
import Layout from "../../components/layout"

const Inventory = () => {
  // hooks
  const navigate = useNavigate()

  // state
  const [data, setData] = useState([])
  const [loading, setLoading] = useState(false)

  // api
  const getInventoryData = () => {
    setLoading(true)
    axios.get("/api/inventory/item").then((res) => {
      let newTitle = [
        "item receiving",
        "item transfer",
        "product / sales return",
        "item transfer konsinyasi",
      ]
      res.data.forEach(
        (value, index) => (value.title = newTitle[index]?.toUpperCase())
      )
      setData(res.data)
      setLoading(false)
    })
  }

  useEffect(() => {
    getInventoryData()
  }, [])

  if (loading) {
    return (
      <Layout title="Inventory">
        <LoadingFallback />
      </Layout>
    )
  }

  return (
    <Layout title="Inventory">
      <div className="grid md:grid-cols-2 gap-6">
        {data?.map((item, index) => (
          <div className="card" key={index}>
            <div className="card-header">
              <div className="header-titl">
                <strong>{item.title}</strong>
              </div>
            </div>
            <div className="card-body">
              <strong className={`text-black`}>Total : {item.value}</strong>

              <Button
                type="primary"
                size={"large"}
                onClick={() => navigate(item.path)}
                style={{ width: "100%", marginTop: 48 }}
              >
                Lihat Daftar
              </Button>
            </div>
          </div>
        ))}
      </div>
    </Layout>
  )
}

export default Inventory
