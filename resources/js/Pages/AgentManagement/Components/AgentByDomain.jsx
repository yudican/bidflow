import { ArrowLeftOutlined, FolderFilled } from "@ant-design/icons"
import { Modal, Table } from "antd"
import React, { useEffect, useState } from "react"
import { toast } from "react-toastify"
import {
  agentCityListColumns,
  agentListDomainColumns,
  agentProvinceListColumns,
} from "../config"
import AgentListTable from "./AgentListTable"

const AgentByDomain = ({ agent_id }) => {
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [activePid, setActivePid] = useState(null)
  const [activeCityPid, setActiveCityPid] = useState(null)
  const [title, setTitle] = useState("Agent Detail")
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
            no: index + 1,
            index,
            key: item?.agent?.user_id,
            nama: item.nama,
            telepon: item.telepon,
            alamat: item.alamat,
            libur: item?.agent?.libur > 0 ? true : false,
            active: item?.agent?.active > 0 ? true : false,
            order: item?.order,
            status_agent: item?.status_agent,
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

  useEffect(() => {
    loadProvince()
  }, [])

  const handleChangeCell = (user_id) => {
    axios
      .post("/api/agent/domain/toggle", {
        user_id,
        domain_id: agent_id,
      })
      .then((response) => {
        const { data } = response.data
        loadAgent(activeCityPid)
        toast.success("Domain berhasil disimpan", {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
      .catch((error) => {
        setLoading(false)
      })
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

              setTitle(`Agent Detail - ${record.nama}`)
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
    <div>
      <span onClick={() => setIsModalOpen(true)}>Detail</span>

      <Modal
        title={
          <div className="flex items-center">
            <ArrowLeftOutlined
              className="mr-3"
              onClick={() => handleHeaderClick()}
            />{" "}
            {title}
          </div>
        }
        open={isModalOpen}
        onOk={() => {
          setIsModalOpen(false)
        }}
        // cancelText={"Batal"}
        onCancel={() => setIsModalOpen(false)}
        okText={"Tutup"}
        width={1000}
        cancelButtonProps={{ style: { display: "none" } }}
      >
        {agentTabs ? (
          <AgentListTable
            dataSource={agentList.sort((a, b) => a.order - b.order)}
            handleChangeCell={({ key }) => handleChangeCell(key)}
            loading={loading}
            columns={agentListDomainColumns}
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
      </Modal>
    </div>
  )
}

export default AgentByDomain
