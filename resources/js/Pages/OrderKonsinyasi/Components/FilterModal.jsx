import { FilterFilled, FilterOutlined } from "@ant-design/icons"
import { DatePicker, Form, Modal, Select } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import DebounceSelect from "../../../components/atoms/DebounceSelect"
import { searchContact, searchSales } from "../service"
import { formatDate } from "../../../helpers"

const { RangePicker } = DatePicker

const FilterModal = ({ handleOk, isFiltered }) => {
  const [form] = Form.useForm()
  const userData = JSON.parse(localStorage.getItem("user_data"))
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [isFilter, setIsFilter] = useState(isFiltered)
  const [roles, setRoles] = useState([])
  const [contactList, setContactList] = useState([])
  const [salesList, setSalesList] = useState([])
  const [filter, setFilter] = useState({
    contact: null,
    sales: null,
    status: null,
    created_at: null,
    print_status: null,
    resi_status: null,
  })
  const [termOfPayments, setTermOfPayments] = useState([])

  useEffect(() => {
    setIsFilter(isFiltered)

    return () => {
      setIsFilter(false)
      setFilter({
        contact: null,
        sales: null,
        status: null,
        created_at: null,
        print_status: null,
        resi_status: null,
      })
    }
  }, [isFiltered])

  const loadTop = () => {
    axios.get("/api/master/top").then((res) => {
      setTermOfPayments(res.data.data)
    })
  }

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
    loadTop()
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
      print_status: null,
      resi_status: null,
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
    form.resetFields()
    setFilter({
      contact: null,
      sales: null,
      status: null,
      created_at: null,
      print_status: null,
      resi_status: null,
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
        maskClosable={false}
        title="Filter"
        open={isModalOpen}
        onOk={() => {
          form.submit()
        }}
        cancelText={isFilter ? "Clear Filter" : "Cancel"}
        onCancel={isFilter ? clearFilter : handleCancel}
        okText={"Apply Filter"}
      >
        <Form
          form={form}
          name="basic"
          layout="vertical"
          onFinish={(value) => {
            console.log(value, "value")
            handleOk({
              ...value,
              created_at:
                value?.created_at?.map((item) => formatDate(item)) || null,
            })
            setIsFilter(true)
            setIsModalOpen(false)
          }}
          // onFinishFailed={onFinishFailed}
          autoComplete="off"
        >
          <Form.Item label="Contact" name="contact">
            <DebounceSelect
              defaultOptions={contactList}
              showSearch
              placeholder="Cari Contact"
              fetchOptions={handleSearchContact}
              filterOption={false}
              className="w-full mb-2"
            />
          </Form.Item>
          <Form.Item label="Sales" name="sales">
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
            />
          </Form.Item>
          <Form.Item label="Status" name="status">
            <Select
              allowClear
              className="w-full mb-2"
              placeholder="Pilih Status"
            >
              <Select.Option value={-1}>Draft</Select.Option>
              <Select.Option value={1}>New</Select.Option>
              <Select.Option value={2}>Open</Select.Option>
              <Select.Option value={3}>Closed</Select.Option>
              <Select.Option value={4}>Canceled</Select.Option>
            </Select>
          </Form.Item>
          <Form.Item label="Tanggal" name="created_at">
            <RangePicker
              className="w-full"
              // format={"YYYY-MM-DD"}
              format={"DD-MM-YYYY"}
              // onChange={(e, dateString) => {
              //   form.setFieldValue("created_at", dateString)
              // }}
            />
          </Form.Item>
          <Form.Item label="Payment Term" name="payment_term">
            <Select
              mode="multiple"
              allowClear
              className="w-full mb-2"
              placeholder="Pilih Payment Term"
              // onChange={(e) => handleChange(e, "payment_term")}
            >
              {termOfPayments.map((top) => (
                <Select.Option value={top.id} key={top.id}>
                  {top.name}
                </Select.Option>
              ))}
            </Select>
          </Form.Item>
          <Form.Item label="Status Print" name="print_status">
            <Select
              allowClear
              className="w-full mb-2"
              placeholder="Pilih Status Print"
            >
              <Select.Option value={"printed"}>Printed</Select.Option>
              <Select.Option value={"not yet"}>Not Yet</Select.Option>
            </Select>
          </Form.Item>
          <Form.Item label="Resi Sudah Diinput" name="resi_status">
            <Select
              allowClear
              className="w-full mb-2"
              placeholder="Pilih Resi Sudah Diinput"
            >
              <Select.Option value={"done"}>Done</Select.Option>
              <Select.Option value={"not yet"}>Not Yet</Select.Option>
            </Select>
          </Form.Item>
          <Form.Item label="Kategori Data" name="order_type">
            <Select
              allowClear
              className="w-full mb-2"
              placeholder="Pilih Kategori Data"
            >
              <Select.Option value={"old"}>Data Lama</Select.Option>
              <Select.Option value={"new"}>Data Baru</Select.Option>
            </Select>
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

export default FilterModal
