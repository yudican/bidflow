import { FileExcelOutlined, LoadingOutlined } from "@ant-design/icons"
import { DatePicker, Modal, Select } from "antd"
import axios from "axios"
import React, { useState } from "react"
import ProgressImportData from "../ProgressImportData"

const { RangePicker } = DatePicker
const ModalOrderExport = ({
  formData = {},
  url = "/api/sales-order/export",
}) => {
  const [isFilterExportModalOpen, setIsFilterExportModalOpen] = useState(false)
  const [loadingExport, setLoadingExport] = useState(false)
  const [progressData, setProgressData] = useState(null)
  const [filter, setFilter] = useState({
    status: null,
    created_at: null,
  })
  const handleChange = (value, field) => {
    setFilter({ ...filter, [field]: value })
  }

  const handleNewExportContent = () => {
    setLoadingExport(true)
    axios
      // .post(`/api/order-manual/export/`)
      .post(url, { ...formData, ...filter }) // waiting new endpoint from mbak henny
      .then((res) => {
        const { data } = res.data
        // setIsFilterExportModalOpen(false)
        setLoadingExport(false)
        window.open(data)
      })
      .catch((e) => {
        // setIsFilterExportModalOpen(false)
        setLoadingExport(false)
      })
  }
  return (
    <div>
      <a
        // className="text-white bg-green-800 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center w-full"
        // onClick={() => handleExportContent()} // old scheme for export data
        onClick={() => setIsFilterExportModalOpen(true)} // new scheme for export data
      >
        {loadingExport ? <LoadingOutlined /> : <FileExcelOutlined />}
        <span className="ml-2">Export</span>
      </a>
      <Modal
        title="Export Sales Order"
        open={isFilterExportModalOpen}
        okText={"Export Data"}
        cancelText={"Batal"}
        onOk={handleNewExportContent}
        confirmLoading={loadingExport}
        onCancel={() => {
          setIsFilterExportModalOpen(false)
        }}
      >
        {progressData && (
          <ProgressImportData
            callback={(data) => setProgressData(data)}
            refetch={() => {}}
            type="manual"
            progressKey="export-so"
          />
        )}
        {!progressData && (
          <div>
            <div>
              <label htmlFor="">Status</label>
              <Select
                mode="multiple"
                allowClear
                className="w-full mb-2"
                placeholder="Pilih Status"
                onChange={(e) => handleChange(e, "status")}
              >
                {/* <Select.Option value={-1}>Draft</Select.Option> */}
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
                format={"DD-MM-YYYY"}
                onChange={(e, dateString) =>
                  handleChange(dateString, "created_at")
                }
              />
            </div>
          </div>
        )}
      </Modal>
    </div>
  )
}

export default ModalOrderExport
