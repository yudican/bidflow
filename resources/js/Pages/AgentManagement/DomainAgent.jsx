import {
  DeleteOutlined,
  DownOutlined,
  EditFilled,
  EyeOutlined,
  RightOutlined,
} from "@ant-design/icons"
import { Dropdown, Menu, message, Popconfirm, Switch, Table } from "antd"
import axios from "axios"
import React, { useEffect } from "react"
import Layout from "../../components/layout"
import AgentByDomain from "./Components/AgentByDomain"
import FormDomain from "./Components/FormDomain"
import { domainListColumns } from "./config"

const DomainAgent = () => {
  const [domains, setDomains] = React.useState([])
  const [loading, setLoading] = React.useState(true)

  const getDomains = () => {
    axios
      .get("/api/agent/domain")
      .then((response) => {
        setDomains(response.data?.data)
        setLoading(false)
      })
      .catch((error) => {
        setLoading(false)
      })
  }

  useEffect(() => {
    getDomains()

    return () => {}
  }, [])

  const handleChangeCell = ({ value, key }) => {
    const domain = domains.find((domain) => domain.id === key)
    axios
      .post("/api/agent/domain/update", {
        ...domain,
        status: value ? 1 : 0,
        agent_domain_id: key,
      })
      .then((response) => {
        getDomains()
        message.success("Update Status Success")
      })
  }
  const handleClickCell = ({ type, agent_id }) => {
    switch (type) {
      case "show-agent":
        axios
          .post(`/api/agent/domain/list`, {
            agent_domain_id: agent_id,
            perpage: 10,
          })
          .then((response) => {
            console.log(response.data.data)
          })
        break
      case "refetch":
        console.log("refetch")
        getDomains()
        break
      case "confirm-delete":
        axios
          .post("/api/agent/domain/delete", {
            agent_domain_id: agent_id,
          })
          .then((response) => {
            getDomains()
            message.success("Delete Domain berhasil")
          })
          .catch((error) => {})
        break

      default:
        break
    }
  }

  const mergedColumns = domainListColumns.map((col) => {
    return {
      ...col,
      onCell: (record) => ({
        record,
        dataIndex: col.dataIndex,
        handleChange: (val) => handleChangeCell(val),
        handleClick: (val) => handleClickCell(val),
      }),
    }
  })

  return (
    <Layout
      title="Domain Agent"
      rightContent={<FormDomain refetch={() => getDomains()} />}
    >
      <div className="card">
        <div className="card-body">
          <Table
            components={{
              body: {
                cell: EditableCell,
              },
            }}
            loading={loading}
            columns={mergedColumns}
            dataSource={domains}
            scroll={{ x: "max-content" }}
            tableLayout={"auto"}
            rowKey="id"
          />
        </div>
      </div>
    </Layout>
  )
}

const EditableCell = (props) => {
  const { dataIndex, handleChange, handleClick, record, className } = props
  if (record) {
    if (dataIndex === "status") {
      return (
        <td className={className}>
          <Switch
            checked={record[dataIndex] > 0}
            onChange={(e) =>
              handleChange({
                value: e,
                dataIndex,
                key: record.id,
              })
            }
          />
        </td>
      )
    }

    if (dataIndex === "icon") {
      return (
        <td className={className}>
          <img src={record[dataIndex]} style={{ height: 30 }} alt="icon" />
        </td>
      )
    }

    if (dataIndex === "id") {
      return (
        <td className={className}>
          <Dropdown.Button
            icon={<DownOutlined />}
            overlay={
              <Menu itemIcon={<RightOutlined />}>
                <Menu.Item icon={<EyeOutlined />}>
                  <AgentByDomain agent_id={record.id} />
                </Menu.Item>
                <Menu.Item icon={<EditFilled />}>
                  <FormDomain
                    initialValues={record}
                    refetch={() =>
                      handleClick({
                        type: "refetch",
                        agent_id: record.id,
                      })
                    }
                    update={true}
                  />
                </Menu.Item>
                <Popconfirm
                  title="Yakin Hapus Alamat ini?"
                  onConfirm={() =>
                    handleClick({
                      type: "confirm-delete",
                      agent_id: record.id,
                    })
                  }
                  // onCancel={cancel}
                  okText="Ya, Hapus"
                  cancelText="Batal"
                >
                  <Menu.Item icon={<DeleteOutlined />}>
                    <span>Hapus</span>
                  </Menu.Item>
                </Popconfirm>
              </Menu>
            }
            onClick={() => alert("detail")}
          ></Dropdown.Button>
        </td>
      )
    }

    return <td className={className}>{record[dataIndex]}</td>
  }
  return <td></td>
}
export default DomainAgent
