import { FilterFilled, FilterOutlined } from "@ant-design/icons"
import { DatePicker, Modal, Select } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import DebounceSelect from "../../../components/atoms/DebounceSelect"
import { searchContact, searchSales } from "../service"

const { RangePicker } = DatePicker

const FilterModal = ({ handleOk }) => {
  const userData = JSON.parse(localStorage.getItem("user_data"))
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [isFilter, setIsFilter] = useState(false)
  const [roles, setRoles] = useState([])
  const [contactList, setContactList] = useState([])
  const [salesList, setSalesList] = useState([])
  const [filter, setFilter] = useState({
    contact: null,
    sales: null,
    status: null,
    created_at: null,
  })

  const showModal = () => {
    setIsModalOpen(true)
  }

  const loadRole = () => {
    axios.get("/api/master/role").then((res) => {
      setRoles(res.data.data)
    })
  }

  const handleGetContact = () => {
    searchContact(null).then((results) => {
      const newResult = results.map((result) => {
        return { label: result.nama, value: result.id }
      })
      setContactList(newResult)
    })
  }

  const handleGetSales = () => {
    searchSales(null).then((results) => {
      const newResult = results.map((result) => {
        return { label: result.nama, value: result.id }
      })
      setSalesList(newResult)
    })
  }

  const handleSearchContact = async (e) => {
    return searchContact(e).then((results) => {
      const newResult = results.map((result) => {
        return { label: result.nama, value: result.id }
      })

      return newResult
    })
  }

  const handleSearchSales = async (e) => {
    return searchSales(e).then((results) => {
      const newResult = results.map((result) => {
        return { label: result.nama, value: result.id }
      })

      return newResult
    })
  }

  useEffect(() => {
    loadRole()
    handleGetContact()
    handleGetSales()
  }, [])

  const handleCancel = () => {
    setIsModalOpen(false)
    setIsFilter(false)
    setFilter({
      contact: null,
      sales: null,
      status: null,
      created_at: null,
    })
  }

  const handleChange = (value, field) => {
    if (field === "createdBy") {
      return setFilter({ ...filter, createdBy: value.value })
    }
    setFilter({ ...filter, [field]: value })
  }

  const clearFilter = () => {
    setIsModalOpen(false)
    setIsFilter(false)
    setFilter({
      contact: null,
      sales: null,
      status: null,
      created_at: null,
    })
    handleOk({})
  }

  return (
    <div>
      {isFilter ? (
        <button
          onClick={() => showModal()}
          className="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
        >
          <FilterOutlined />
          <span className="ml-2">Show Filter</span>
        </button>
      ) : (
        <button
          onClick={() => showModal()}
          className="
          bg-white border 
          text-blue-700 hover:text-blue-700/90
          delay-100 ease-in-out
          focus:ring-4 focus:outline-none focus:ring-blue-300 
          font-medium rounded-lg 
          text-sm px-4 py-2 text-center inline-flex items-center
        "
        >
          <FilterFilled />
          <span className="ml-2">Filter</span>
        </button>
      )}

      <Modal
        title="Filter"
        open={isModalOpen}
        onOk={() => {
          handleOk(filter)
          setIsFilter(true)
          setIsModalOpen(false)
        }}
        cancelText={isFilter ? "Clear Filter" : "Cancel"}
        onCancel={isFilter ? clearFilter : handleCancel}
        okText={"Apply Filter"}
      >
        <div>
          <div>
            <label htmlFor="">Contact</label>
            <DebounceSelect
              defaultOptions={contactList}
              showSearch
              placeholder="Cari Contact"
              fetchOptions={handleSearchContact}
              filterOption={false}
              className="w-full mb-2"
              onChange={(e) => handleChange(e, "contact")}
            />
          </div>
          <div>
            <label htmlFor="">Sales</label>
            <DebounceSelect
              defaultOptions={
                roles === "sales"
                  ? [{ label: userData.name, value: userData.id }]
                  : salesList
              }
              showSearch
              placeholder="Cari Sales"
              fetchOptions={handleSearchSales}
              filterOption={false}
              className="w-full mb-2"
              onChange={(e) => handleChange(e, "sales")}
            />
          </div>
          <div>
            <label htmlFor="">Status</label>
            <Select
              mode="multiple"
              allowClear
              className="w-full mb-2"
              placeholder="Pilih Status"
              onChange={(e) => handleChange(e, "status")}
            >
              <Select.Option value={1}>New</Select.Option>
              <Select.Option value={2}>Open</Select.Option>
              <Select.Option value={3}>Closed</Select.Option>
              <Select.Option value={4}>Canceled</Select.Option>
            </Select>
          </div>

          <div className="mb-2">
            <label htmlFor="">Tanggal</label>
            <RangePicker
              className="w-full"
              format={"YYYY-MM-DD"}
              onChange={(e, dateString) =>
                handleChange(dateString, "created_at")
              }
            />
          </div>
        </div>
      </Modal>
    </div>
  )
}

export default FilterModal
