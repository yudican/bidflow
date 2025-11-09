import { CloseOutlined, PlusOutlined } from "@ant-design/icons"
import { Form, Switch, Table } from "antd"
import React, { useEffect, useState } from "react"
import DebounceSelect from "../../../components/atoms/DebounceSelect"
import { searchContact } from "../service"

const Reminder = ({ dataSource, handleChangeCell, handleClickCell }) => {
  const columns = [
    {
      title: "Contact",
      dataIndex: "contact",
      key: "contact",
    },
    {
      title: "Before 7 Days",
      dataIndex: "before_7_day",
      key: "before_7_day",
    },
    {
      title: "Before 3 Days",
      dataIndex: "before_3_day",
      key: "before_3_day",
    },
    {
      title: "Before 1 Days",
      dataIndex: "before_1_day",
      key: "before_1_day",
    },
    {
      title: "After 7 Days",
      dataIndex: "after_7_day",
      key: "after_7_day",
    },
    {
      title: "#",
      dataIndex: "action",
      key: "action",
    },
  ]

  const mergedColumns = columns.map((col) => {
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
    <div className="card">
      <div className="card-body">
        <div className="card-header">
          <h1 className="text-lg text-bold">Set Reminder</h1>
        </div>
        <div className="row">
          <div className="col-md-12">
            <Table
              components={{
                body: {
                  cell: EditableCell,
                },
              }}
              columns={mergedColumns}
              dataSource={dataSource}
              rowKey="key"
              scroll={{ x: "max-content" }}
              tableLayout={"auto"}
            />
          </div>
        </div>
      </div>
    </div>
  )
}

const EditableCell = (props) => {
  const [form] = Form.useForm()
  const { dataIndex, handleChange, handleClick, record } = props
  // console.log(record, "record");
  const [contactList, setContactList] = useState([])

  const handleGetContact = () => {
    searchContact(null).then((results) => {
      const newResult = results.map((result) => {
        return { label: result.nama, value: result.id }
      })
      setContactList(newResult)
    })
  }

  useEffect(() => {
    handleGetContact()
  }, [])

  const handleSearchContact = async (e) => {
    return await searchContact(e).then((results) => {
      const newResult = results.map((result) => {
        return { label: result.nama, value: result.id }
      })

      return newResult
    })
  }

  if (dataIndex === "contact") {
    return (
      <td>
        <DebounceSelect
          showSearch
          placeholder="Cari Contact"
          fetchOptions={handleSearchContact}
          filterOption={false}
          className="w-full mb-2"
          options={[record.contactUser]}
          defaultOptions={record.contact ? [record.contact] : contactList}
          value={record?.contact?.value}
          onChange={(e) =>
            handleChange({
              value: e.value,
              dataIndex,
              key: record.key,
              uid_lead: record.uid_lead,
              reminder_id: record.reminder_id,
            })
          }
        />
      </td>
    )
  }

  if (dataIndex === "action") {
    if (record.key > 0) {
      return (
        <td>
          <button
            onClick={() =>
              handleClick({
                key: record.reminder_id,
                type: "delete",
              })
            }
            className="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
          >
            <CloseOutlined />
          </button>
        </td>
      )
    }
    return (
      <td>
        <button
          onClick={() => handleClick({ key: record.key, type: "plus" })}
          className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
        >
          <PlusOutlined />
        </button>
      </td>
    )
  }

  return (
    <td>
      <Switch
        checked={record[dataIndex]}
        onChange={(e) =>
          handleChange({
            value: e,
            dataIndex,
            key: record.key,
            uid_lead: record.uid_lead,
            reminder_id: record.reminder_id,
          })
        }
      />
    </td>
  )
}

export default Reminder
