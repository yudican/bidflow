import { FilterFilled, FilterOutlined } from "@ant-design/icons"
import { Modal, Select } from "antd"
import React, { useEffect, useState } from "react"
const FilterModal = ({ handleOk }) => {
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [isFilter, setIsFilter] = useState(false)
  const [roles, setRoles] = useState([])
  const [filter, setFilter] = useState({
    roles: [],
    status: [],
    createdBy: null,
  })

  const showModal = () => {
    loadRole()
    setIsModalOpen(true)
  }

  const loadRole = () => {
    axios.get("/api/master/role").then((res) => {
      setRoles(res.data.data)
    })
  }

  useEffect(() => {
    loadRole()
  }, [])

  const handleCancel = () => {
    setIsModalOpen(false)
    setIsFilter(false)
    setFilter({
      roles: [],
      status: [],
      createdBy: null,
    })
  }

  const handleChange = (value, field) => {
    if (field === "createdBy") {
      return setFilter({ ...filter, createdBy: value.value })
    }
    setFilter({ ...filter, [field]: value })
  }

  const clearFilter = () => {
    setIsFilter(false)
    setFilter({
      roles: [],
      status: [],
      createdBy: null,
    })
    setIsModalOpen(false)
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
        title="Filter Data"
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
            <label htmlFor="">Role</label>
            <Select
              defaultValue={[...filter.roles]}
              value={[...filter.roles]}
              mode="multiple"
              allowClear
              className="w-full mb-2"
              placeholder="Pilih Role"
              optionFilterProp="children"
              filterOption={(input, option) =>
                (option?.children?.toLowerCase() ?? "").includes(
                  input.toLowerCase()
                )
              }
              onChange={(e) => handleChange(e, "roles")}
            >
              {roles.map((item) => (
                <Select.Option key={item.id} value={item.id}>
                  {item.role_name}
                </Select.Option>
              ))}
            </Select>
          </div>

          <div>
            <label htmlFor="">Status</label>
            <Select
              defaultValue={[...filter.status]}
              value={[...filter.status]}
              mode="multiple"
              allowClear
              className="w-full mb-2"
              placeholder="Pilih Status"
              onChange={(e) => handleChange(e, "status")}
            >
              <Select.Option key={1} value={1}>
                Active
              </Select.Option>
              <Select.Option key={0} value={0}>
                Non Active
              </Select.Option>
              {/* <Select.Option key={2} value={2}>
                Blacklist
              </Select.Option> */}
            </Select>
          </div>
        </div>
      </Modal>
    </div>
  )
}

export default FilterModal
