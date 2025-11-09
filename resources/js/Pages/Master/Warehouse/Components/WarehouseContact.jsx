import { CloseOutlined, PlusOutlined } from "@ant-design/icons"
import { Switch, Table } from "antd"
import React, { useEffect, useState } from "react"
import DebounceSelect from "../../../../components/atoms/DebounceSelect"
import { inArray } from "../../../../helpers"
import { searchContact } from "../service"

const WarehouseContact = ({
  dataSource,
  handleChangeCell,
  handleClickCell,
}) => {
  const [contactList, setContactList] = useState([])
  const columns = [
    {
      title: "No.",
      dataIndex: "id",
      key: "id",
    },
    {
      title: "Contact",
      dataIndex: "contact",
      key: "contact",
    },
    // {
    //   title: "Status",
    //   dataIndex: "status",
    //   key: "status",
    // },
    {
      title: " ",
      dataIndex: "action",
      key: "action",
    },
  ]

  const selectedContact = dataSource
    .map((contact) => contact?.contact?.value)
    .filter((item) => item)

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

  const mergedColumns = columns.map((col) => {
    return {
      ...col,
      onCell: (record) => ({
        record,
        contactList: contactList.map((contact) => {
          return {
            ...contact,
            selected: inArray(contact.value, selectedContact),
          }
        }),
        selectedContact,
        dataIndex: col.dataIndex,
        handleChange: (val) => handleChangeCell(val),
        handleClick: (val) => handleClickCell(val),
      }),
    }
  })

  return (
    <Table
      scroll={{ x: "max-content" }}
      tableLayout={"auto"}
      components={{
        body: {
          cell: EditableCell,
        },
      }}
      columns={mergedColumns}
      // columns={[
      //   {
      //     title: "No.",
      //     dataIndex: "id",
      //     key: "id",
      //     render: (text, record, index) => index + 1,
      //   },
      //   ...mergedColumns,
      // ]}
      dataSource={dataSource}
      rowKey="key"
    />
  )
}

const EditableCell = (props) => {
  const {
    dataIndex,
    handleChange,
    handleClick,
    record,
    selectedContact,
    contactList,
  } = props

  const handleSearchContact = async (e) => {
    return await searchContact(e).then((results) => {
      const newResult = results.map((result) => {
        return {
          label: result.nama,
          value: result.id,
          selected: inArray(result.id, selectedContact),
        }
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
          defaultOptions={
            record.contact
              ? [record.contact]
              : contactList.filter((contact) => !contact.selected)
          }
          value={record?.contact?.value}
          onChange={(e) =>
            handleChange({
              key: record.key,
              value: e,
              dataIndex,
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
            type="button"
            onClick={() =>
              handleClick({
                key: record.key,
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
          type="button"
          onClick={() => handleClick({ key: record.key, type: "plus" })}
          className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
        >
          <PlusOutlined />
        </button>
      </td>
    )
  }

  if (dataIndex === "status") {
    return (
      <td>
        <Switch
          checked={record[dataIndex]}
          onChange={(e) =>
            handleChange({
              value: e,
              dataIndex,
              key: record.key,
            })
          }
        />
      </td>
    )
  }

  if (dataIndex === "id") {
    return <td>{record.key + 1}</td>
  }
}

export default WarehouseContact
