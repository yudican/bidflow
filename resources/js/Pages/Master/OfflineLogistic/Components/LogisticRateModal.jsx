import { DeleteOutlined, OrderedListOutlined } from "@ant-design/icons"
import { Modal, Pagination, Popconfirm, Switch, Table } from "antd"
import React, { useEffect, useState } from "react"
import { toast } from "react-toastify"
import { logisticRatesListColumn } from "../config"
import FormLogisticRates from "./FormLogisticRates"
import LogisticDiscountModal from "./LogisticDiscountModal"

const LogisticRateModal = ({ logisticId }) => {
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [loading, setLoading] = useState(false)
  const [datas, setDatas] = useState([])
  const [total, setTotal] = useState(0)
  const [currentPage, setCurrentPage] = useState(1)

  const loadData = (
    url = "/api/master/online-logistic/rates",
    perpage = 10,
    params = { page: currentPage }
  ) => {
    setLoading(true)
    axios
      .post(url, { perpage, logistic_id: logisticId, ...params })
      .then((res) => {
        const { data, total, current_page } = res.data.data
        setTotal(total)
        setCurrentPage(current_page)
        setDatas(data)
        setLoading(false)
      })
      .catch((e) => setLoading(false))
  }
  useEffect(() => {
    loadData()
  }, [])

  const showModal = () => {
    setIsModalOpen(true)
  }

  const handleCancel = () => {
    setIsModalOpen(false)
  }

  const handleChange = (page, pageSize = 10) => {
    loadData(`/api/master/online-logistic/rates/?page=${page}`, pageSize, {
      page,
    })
  }

  const updateStatus = (record, field = "logistic_rate_status") => {
    axios
      .post(`/api/master/online-logistic/rates/update`, {
        logistic_rates_id: record.id,
        field,
        value: record.status,
      })
      .then((res) => {
        toast.success("Status berhasil diupdate")
        loadData()
      })
      .catch((err) => {
        toast.error("Status gagal Di update")
      })
  }

  const deleteLogistic = (logistic_id) => {
    axios
      .post(`/api/master/offline-logistic/rates/delete/${logistic_id}`, {
        _method: "DELETE",
      })
      .then((res) => {
        toast.success("Data berhasil dihapus")
        loadData()
      })
      .catch((err) => {
        toast.error("Data gagal dihapus")
      })
  }

  return (
    <div>
      <button
        onClick={() => showModal()}
        className="text-white bg-[#008BE1] hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
      >
        <OrderedListOutlined />
        <span className="ml-2">List Servis</span>
      </button>

      <Modal
        title="Logistic Service"
        open={isModalOpen}
        onOk={() => {
          setIsModalOpen(false)
        }}
        cancelText={"Close"}
        onCancel={handleCancel}
        // okText={"Logistic Sevice"}
        width={1200}
        okButtonProps={{ style: { display: "none" } }}
      >
        <div>
          <div className="mb-3 pull-right">
            <FormLogisticRates
              refetch={() => loadData()}
              logisticId={logisticId}
            />
          </div>
          <Table
            scroll={{ x: "max-content" }}
            tableLayout={"auto"}
            dataSource={datas}
            columns={[
              ...logisticRatesListColumn,
              {
                title: "Status",
                key: "logistic_rate_status",
                dataIndex: "logistic_rate_status",
                render: (text, record) => {
                  return (
                    <Switch
                      checked={text > 0}
                      onChange={(e) =>
                        updateStatus({ ...record, status: e ? "1" : "0" })
                      }
                    />
                  )
                },
              },
              {
                title: "Agen",
                key: "logistic_agent_status",
                dataIndex: "logistic_agent_status",
                render: (text, record) => {
                  return (
                    <Switch
                      checked={text > 0}
                      onChange={(e) =>
                        updateStatus(
                          { ...record, status: e ? "1" : "0" },
                          "logistic_agent_status"
                        )
                      }
                    />
                  )
                },
              },
              {
                title: "Customer",
                key: "logistic_custommer_status",
                dataIndex: "logistic_custommer_status",
                render: (text, record) => {
                  return (
                    <Switch
                      checked={text > 0}
                      onChange={(e) =>
                        updateStatus(
                          { ...record, status: e ? "1" : "0" },
                          "logistic_custommer_status"
                        )
                      }
                    />
                  )
                },
              },
              {
                title: "Cod",
                key: "logistic_cod_status",
                dataIndex: "logistic_cod_status",
                render: (text, record) => {
                  return (
                    <Switch
                      checked={text > 0}
                      onChange={(e) =>
                        updateStatus(
                          { ...record, status: e ? "1" : "0" },
                          "logistic_cod_status"
                        )
                      }
                    />
                  )
                },
              },
              {
                title: "Discount",
                key: "discount",
                dataIndex: "discount",
                render: (text, record) => {
                  return (
                    <LogisticDiscountModal
                      logisticId={record.id}
                      data={record}
                    />
                  )
                },
              },
              {
                title: "Action",
                dataIndex: "id",
                key: "id",
                render: (text, record) => {
                  return (
                    <div className="flex items-center">
                      <FormLogisticRates
                        record={record}
                        refetch={() => loadData()}
                        update={true}
                        logisticId={logisticId}
                      />
                      <Popconfirm
                        title="Yakin Hapus Data ini?"
                        onConfirm={() => deleteLogistic(record.id)}
                        // onCancel={cancel}
                        okText="Ya, Hapus"
                        cancelText="Batal"
                      >
                        <button className="text-white bg-red-800 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2">
                          <DeleteOutlined />
                        </button>
                      </Popconfirm>
                    </div>
                  )
                },
              },
            ]}
            loading={loading}
            pagination={false}
            rowKey="id"
          />
          <Pagination
            defaultCurrent={1}
            current={currentPage}
            total={total}
            className="mt-4 text-center"
            onChange={handleChange}
          />
        </div>
      </Modal>
    </div>
  )
}

export default LogisticRateModal
