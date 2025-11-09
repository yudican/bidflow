import {
  FileExcelFilled,
  FilterOutlined,
  InboxOutlined,
} from "@ant-design/icons"
import { Modal, Select, message, Upload } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"

const { Dragger } = Upload
const ImportModal = ({ disabled = false }) => {
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [fileList, setFileList] = useState(null)

  const props = {
    name: "file",
    multiple: false,
    onChange(info) {
      const { status } = info.file
      if (status !== "uploading") {
        setFileList(info.fileList[0])
      }
      if (status === "done") {
        message.success(`${info.file.name} file uploaded successfully.`)
      } else if (status === "error") {
        message.error(`${info.file.name} file upload failed.`)
      }
    },
    onDrop(e) {
      console.log("Dropped files", e.dataTransfer.files)
    },
  }

  const handleSubmit = () => {
    let formData = new FormData()

    if (fileList) {
      formData.append("file", fileList?.originFileObj)
    }
    axios
      .post("/api/product-management/import/save", formData)
      .then((res) => {
        message.success(res.data.message)
      })
      .catch((err) => {
        console.log(err.response)
        message.error(`Import gagal`)
      })
  }

  return (
    <div>
      {disabled ? (
        <button
          className="text-white bg-gray-800 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
          disabled
        >
          <FileExcelFilled />
          <span className="ml-2">Import</span>
        </button>
      ) : (
        <button
          onClick={() => setIsModalOpen(true)}
          className="text-white bg-green-800 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
        >
          <FileExcelFilled />
          <span className="ml-2">Import</span>
        </button>
      )}

      <Modal
        title="Filter Margin Bottom"
        open={isModalOpen}
        onOk={() => {
          handleSubmit()
          setIsModalOpen(false)
        }}
        cancelText={"Cancel"}
        onCancel={() => setIsModalOpen(false)}
        okText={"Upload"}
      >
        <Dragger {...props} className={"p-3"} beforeUpload={() => false}>
          <p className="ant-upload-drag-icon">
            <InboxOutlined />
          </p>
          <p className="ant-upload-text">
            Click or drag file to this area to upload
          </p>
          <p className="ant-upload-hint">
            Support for a single or bulk upload. Strictly prohibit from
            uploading company data or other band files
          </p>
        </Dragger>
      </Modal>
    </div>
  )
}

export default ImportModal
