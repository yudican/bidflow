import { CloseCircleFilled, SearchOutlined } from "@ant-design/icons"
import { Input, Modal, Pagination, Table } from "antd"
import React, { useState } from "react"
import { orderNumberColumns } from "../config"
const ModalOrderNumber = ({
  handleSelected,
  url = "/api/order-lead",
  type = "b2b",
  form,
  getDueDate,
}) => {
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [data, setData] = useState([])
  const showModal = () => {
    setIsModalOpen(true)
  }

  const [loading, setLoading] = useState(false)
  const [total, setTotal] = useState(0)
  const [search, setSearch] = useState("")
  const [isSearch, setIsSearch] = useState(false)
  const [currentPage, setCurrentPage] = useState(1)

  const loadData = (url, perpage = 10, params = {}) => {
    setLoading(true)
    axios.post(url, { perpage, ...params }).then((res) => {
      const { data, total, current_page } = res.data.data
      setTotal(total)
      setCurrentPage(current_page)
      if (type === "b2c") {
        const newData = data.map((item) => {
          const user = item?.user || "-"
          return {
            id: item.uid_lead,
            value: item.trx_id,
            label: user,
          }
        })
        setData(newData)
      } else {
        const newData = data.map((item) => {
          const contact = item?.contact_user?.name || "-"
          return {
            id: item.uid_lead,
            value: item.order_number,
            label: contact,
          }
        })
        setData(newData)
      }

      setLoading(false)
    })
  }

  const handleChange = (page, pageSize = 10) => {
    loadData(`${url}/?page=${page}`, pageSize, {
      search,
    })
  }

  const handleChangeSearch = () => {
    setIsSearch(true)
    loadData(`${url}`, 10, { search })
  }

  const loadDataOrder = (order_number) => {
    axios
      .post("/api/order/sales-return/data-order", { order_number })
      .then((res) => {
        const { data } = res.data
        console.log(data, form)
        form.setFieldsValue(data)
        getDueDate(data)
      })
  }

  const orderNumberAction = [
    {
      title: "Action",
      dataIndex: "label",
      key: "label",
      render: (text, record) => {
        const selected = form.getFieldValue("order_number")
        const isSelected = selected === record.value
        const color = isSelected ? "green" : "blue"
        return (
          <button
            onClick={() => {
              form.setFieldsValue({ order_number: record.value })
              setIsModalOpen(false)
              loadDataOrder(record.value)
            }}
            className={`text-white bg-${color}-700 hover:bg-${color}-800 focus:ring-4 focus:outline-none focus:ring-${color}-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center`}
          >
            <span className="ml-2">Pilih</span>
          </button>
        )
      },
    },
  ]
  return (
    <div>
      <Input.Search
        value={form.getFieldValue("order_number")}
        placeholder="Input Order Number"
        onSearch={() => {
          showModal()
          loadData(url)
        }}
      />

      <Modal
        title="Pilih Order Number"
        open={isModalOpen}
        onOk={() => {
          handleSelected(selectedValue)
        }}
        cancelText={"Tutup"}
        onCancel={() => setIsModalOpen(false)}
        width={1000}
        okButtonProps={{ style: { display: "none" } }}
      >
        <div className="row mb-4">
          <div className="col-md-12"></div>
          <div className="col-md-4 col-sm-6 col-12">
            <Input
              placeholder="Cari disini"
              size={"large"}
              className="rounded"
              onPressEnter={() => handleChangeSearch()}
              suffix={
                isSearch ? (
                  <CloseCircleFilled
                    onClick={() => {
                      loadData(url)
                      setSearch(null)
                      setIsSearch(false)
                    }}
                  />
                ) : (
                  <SearchOutlined onClick={() => handleChangeSearch()} />
                )
              }
              value={search}
              onChange={(e) => setSearch(e.target.value)}
            />
          </div>
        </div>
        <Table
          dataSource={data}
          columns={[...orderNumberColumns, ...orderNumberAction]}
          loading={loading}
          pagination={false}
          rowKey="id"
          scroll={{ x: "max-content" }}
          tableLayout={"auto"}
        />
        <Pagination
          defaultCurrent={1}
          current={currentPage}
          total={total}
          className="mt-4 text-center"
          onChange={handleChange}
        />
      </Modal>
    </div>
  )
}

export default ModalOrderNumber
