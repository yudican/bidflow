import { FileExcelOutlined, InboxOutlined } from "@ant-design/icons"
import { Form, Modal } from "antd"
import Dragger from "antd/lib/upload/Dragger"
import axios from "axios"
import React, { useState } from "react"
import { toast } from "react-toastify"
const ImportOrder = ({ refetch }) => {
  const [isModalOpen, setIsModalOpen] = useState(false)

  const [form] = Form.useForm()
  // attachments
  const [loadingAtachment, setLoadingAtachment] = useState(false)

  const [fileList, setFileList] = useState([])

  const handleChange = ({ fileList: newFileList }) => {
    setFileList(newFileList)
  }

  const showModal = () => {
    setIsModalOpen(true)
  }

  const handleOkAndImport = () => {
    setLoadingAtachment(true)
    const formData = new FormData()
    formData.append("attachment", fileList[0].originFileObj)

    axios
      .post("/api/marketplace/import", formData)
      .then((response) => {
        const { message } = response.data
        setLoadingAtachment(false)
        toast.success("Data berhasil diimpor", {
          position: toast.POSITION.TOP_RIGHT,
        })
        refetch()
        setIsModalOpen(false)
      })
      .catch((error) => {
        setLoadingAtachment(false)
        toast.error("Data gagal diimpor", {
          position: toast.POSITION.TOP_RIGHT,
        })
      })

    setIsModalOpen(false)
  }

  const handleCancel = () => {
    setIsModalOpen(false)
  }

  return (
    <div className="">
      <button
        onClick={() => showModal()}
        className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
      >
        <FileExcelOutlined />
        <span className="ml-2">Import</span>
      </button>

      <Modal
        title="Import Data"
        open={isModalOpen}
        onOk={handleOkAndImport}
        cancelText={"Cancel"}
        onCancel={handleCancel}
        okText={"Import Data"}
      >
        <div className="w-full">
          <p className="alert alert-info">
            Sebelum melakukan import pastikan sudah sesuai template yang telah
            disediakan, jika belum silahkan melakukan download terlebih dahulu
            dengan klik{" "}
            <a href="/assets/template/import-order-mp.xlsx" download>
              Download Template
            </a>
          </p>
          <Form
            form={form}
            name="basic"
            layout="vertical"
            autoComplete="off"
            encType="multipart/form-data"
            className="w-full"
          >
            <Form.Item
              label="Upload Excel"
              name="attachment"
              className="w-full"
            >
              {/* <Upload
                className="w-full"
                name="attachment"
                fileList={fileList}
                beforeUpload={() => false}
                onChange={(e) => {
                  handleChange({
                    ...e,
                  })
                }}
              >
                <Button icon={<UploadOutlined />} loading={loadingAtachment}>
                  Upload (Excel)
                </Button>
              </Upload> */}
              <Dragger
                name="attachment"
                fileList={fileList}
                beforeUpload={() => false}
                onChange={(e) => {
                  handleChange({
                    ...e,
                  })
                }}
              >
                <p className="ant-upload-drag-icon">
                  <InboxOutlined />
                </p>
                <p className="ant-upload-text">
                  Click or drag file to this area to upload
                </p>
                <p className="ant-upload-hint">
                  Support for a single or bulk upload. Strictly prohibited from
                  uploading company data or other banned files.
                </p>
              </Dragger>
            </Form.Item>
          </Form>
        </div>
      </Modal>
    </div>
  )
}

export default ImportOrder
