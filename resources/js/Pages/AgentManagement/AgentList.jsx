import { FolderFilled } from "@ant-design/icons"
import { message, Table } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import Layout from "../../components/layout"
import AgentListTable from "./Components/AgentListTable"
import { agentCityListColumns, agentProvinceListColumns } from "./config"

const AgentList = () => {
  const [activePid, setActivePid] = useState(null)
  const [activeCityPid, setActiveCityPid] = useState(null)
  const [title, setTitle] = useState("Agent Management")
  const [activeTab, setActiveTab] = useState(0)
  const [provinces, setProvinces] = useState([])
  const [cities, setCities] = useState([])
  const [agentList, setAgentList] = useState([])
  const [loading, setLoading] = useState(false)
  const loadProvince = () => {
    setLoading(true)
    axios
      .get("/api/province-list")
      .then((res) => {
        const { data } = res.data
        setLoading(false)
        setProvinces(data)
      })
      .catch((err) => {
        setLoading(false)
      })
  }

  const loadCity = (province_id) => {
    setCities([])
    axios
      .get("/api/city-list/" + province_id)
      .then((res) => {
        const { data } = res.data
        setCities(data)
      })
      .catch((err) => {
        setLoading(false)
      })
  }

  const loadAgent = (city_id) => {
    setAgentList([])
    setLoading(true)
    axios
      .get("/api/agent-list/" + city_id)
      .then((res) => {
        const { data } = res.data
        const newData = data.map((item, index) => {
          return {
            index,
            key: item?.agent?.id,
            nama: item.nama,
            telepon: item.telepon,
            alamat: item.alamat,
            libur: item?.agent?.libur > 0 ? true : false,
            active: item?.agent?.active > 0 ? true : false,
            order: item?.order,
          }
        })
        setLoading(false)
        setAgentList(newData || [])
      })
      .catch((err) => {})
  }

  useEffect(() => {
    loadProvince()
  }, [])

  const handleHeaderClick = () => {
    if (activeTab == 1) {
      setActiveTab(activeTab - 1)
      setTitle("Agent Management")
    } else if (activeTab == 2) {
      setActiveTab(activeTab - 1)
      const province = provinces.find((item) => item.pid == activePid)
      setTitle(`Agent Management - ${province?.nama}`)
    }
  }

  const handleChangeCell = ({ key, dataIndex, value }) => {
    axios
      .post("/api/agent-update", {
        agent_id: key,
        field: dataIndex,
        value,
      })
      .then((res) => {
        message.success("berhasil update data")
        loadAgent(activeCityPid)
      })
      .catch((err) => message.error("gagal update data"))
  }
  const locationActionColumns = [
    {
      title: "Kota/Kabupaten",
      dataIndex: "kota_kabupaten",
      key: "kota_kabupaten",
      render: (text, record, index) => {
        return (
          <button
            onClick={() => {
              if (activeTab === 0) {
                loadCity(record.pid)
                setActivePid(record.pid)
              } else if (activeTab === 1) {
                loadAgent(record.pid)
                setActiveCityPid(record.pid)
              }

              setActiveTab(activeTab + 1)

              setTitle(`Agent Management - ${record.nama}`)
            }}
            className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
          >
            <FolderFilled />
            <span className="ml-2">{`Detail ${
              activeTab === 1 ? "Agen" : "Kota/Kabupaten"
            }`}</span>
          </button>
        )
      },
    },
  ]

  const agentActionColumns =
    activeTab == 0 ? agentProvinceListColumns : agentCityListColumns
  const agentDatasource = activeTab == 0 ? provinces : cities
  const agentLocationColumn = [...agentActionColumns, ...locationActionColumns]

  const agentTabs = activeTab > 1

  return (
    <Layout title={title} onClick={() => handleHeaderClick()}>
      {agentTabs ? (
        <AgentListTable
          dataSource={agentList.sort((a, b) => a.order - b.order)}
          handleChangeCell={handleChangeCell}
          loading={loading}
          refetch={() => {
            if (activeCityPid) {
              loadAgent(activeCityPid)
            }
          }}
        />
      ) : (
        <Table
          dataSource={agentDatasource}
          columns={agentLocationColumn}
          loading={loading}
          pagination={false}
          rowKey="index"
          scroll={{ x: "max-content" }}
          tableLayout={"auto"}
        />
      )}
    </Layout>
  )
}

export default AgentList
