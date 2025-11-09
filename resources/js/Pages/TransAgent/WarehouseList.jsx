import {
  DownOutlined,
  EditFilled,
  EyeOutlined,
  RightOutlined,
} from "@ant-design/icons"
import { Dropdown, Menu, Pagination, Table, message } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { useNavigate } from "react-router-dom"
import Layout from "../../components/layout"
import BulkAction from "./Components/BulkAction"
import ModalFilterTransaction from "./Components/ModalFilterTransaction"
import { WarehouseListColumn } from "./config"

const WarehouseList = () => {
  const navigate = useNavigate()
  const [loading, setLoading] = useState(false)
  const [contacts, setContacts] = useState([])
  const [total, setTotal] = useState(0)
  const [currentPage, setCurrentPage] = useState(1)
  const [filterData, setFilterData] = useState({})
  const [selectedRowKeys, setSelectedRowKeys] = useState([])

  const loadContact = (url = "/api/warehouseAgent", perpage = 10) => {
    setLoading(true)
    axios.post(url, { perpage }).then((res) => {
      const { data, total, current_page } = res.data.data
      setTotal(total)
      setCurrentPage(current_page)
      //console.log(data);

      const newdata = data.map((transagent, index) => {
        return {
          key: transagent.id,
          id: index + 1,
          name: transagent.user.name,
          id_transaksi: transagent.id_transaksi,
          created_at: moment(transagent.created_at).format("DD-MM-YYYY"),
          nominal: transagent.nominal,
          label: transagent.label,
        }
      })

      setContacts(newdata)
      setLoading(false)
    })
  }
  useEffect(() => {
    loadContact()
  }, [])

  const handleChange = (page, pageSize = 10) => {
    loadContact(`/api/warehouseAgent/?page=${page}`, pageSize, filterData)
  }

  const ActionMenuDetail = ({ value, label }) => {
    return (
      <Menu
        onClick={({ key }) => {
          switch (key) {
            case "detail":
              navigate(`/trans-agent/detail/${value}`)
              break
            case "label":
              window.open(label.label_url)
              break
            case "pengemasan":
              axios
                .post(`/api/trans-agent/packing-process/${value}`, {
                  value: value,
                })
                .then((res) => {
                  message.success(res.data.status)
                  loadContact()
                })
              break
          }
        }}
        itemIcon={<RightOutlined />}
        items={[
          {
            label: "Detail Pesanan",
            key: "detail",
            icon: <EyeOutlined />,
          },
          {
            label: "Cetak Label",
            key: "label",
            icon: <EditFilled />,
          },
          {
            label: "Proses Pengemasans",
            key: "pengemasan",
            icon: <EditFilled />,
          },
        ]}
      />
    )
  }

  const Aksi = {
    title: "Action",
    key: "id",
    fixed: "right",
    width: 100,
    render: (text) => (
      <Dropdown.Button
        icon={<DownOutlined />}
        overlay={<ActionMenuDetail value={text.key} label={text.label} />}
      ></Dropdown.Button>
    ),
  }

  const handleFilter = (data) => {
    setFilterData(data)
    loadData(`/api/transAgent`, 10, data)
  }

  const onSelectChange = (newSelectedRowKeys) => {
    setSelectedRowKeys(newSelectedRowKeys)
  }

  const rowSelection = {
    selectedRowKeys,
    onChange: onSelectChange,
  }

  const rightContent = (
    <div className="flex justify-between items-center">
      <ModalFilterTransaction handleOk={handleFilter} />

      {/* dropdown */}
      <BulkAction selectedRowKeys={selectedRowKeys} />
    </div>
  )

  return (
    <Layout
      rightContent={rightContent}
      title="Transaction Agent - Proses Gudang"
    >
      <div className="card">
        <div className="card-body">
          <Table
            dataSource={contacts}
            columns={[...WarehouseListColumn, Aksi]}
            loading={loading}
            pagination={false}
            rowKey="id"
            rowClassName={rowSelection}
            scroll={{ x: "max-content" }}
            tableLayout={"auto"}
          />
          <Pagination
            defaultCurrent={1}
            current={currentPage}
            total={total}
            className="mt-4 text-center"
            onChange={handleChange}
            pageSizeOptions={["10", "20", "50", "100", "200", "500"]}
          />
        </div>
      </div>
    </Layout>
  )
}

export default WarehouseList
