import { FileExcelOutlined, UploadOutlined } from "@ant-design/icons"
import { Button, Form, Modal, Upload } from "antd"
import axios from "axios"
import React, { useState } from "react"
import { toast } from "react-toastify"
import DownloadImportTemplate from "./DownloadImportTemplate"
import ModalImportConfirm from "../../../components/Modal/ModalImportConfirm"
const ImportModal = ({ handleOk, refetch }) => {
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
      .post("/api/order-konsinyasi/import", formData)
      .then((response) => {
        if (response.data.status == "failed") {
          toast.error(response.data.message)
        } else {
          const { message } = response.data
          setLoadingAtachment(false)
          refetch()
          toast.success("Data berhasil diimport", {
            position: toast.POSITION.TOP_RIGHT,
          })
        }
        setIsModalOpen(false)
      })
      .catch((error) => {
        setLoadingAtachment(false)
        toast.error("Data gagal diimport", {
          position: toast.POSITION.TOP_RIGHT,
        })
      })

    setIsModalOpen(false)
  }

  const handleCancel = () => {
    setIsModalOpen(false)
  }

  const handleRefetch = () => {
    refetch()
    setIsModalOpen(false)
  }

  return (
    <div className="w-full">
      <button onClick={() => showModal()}>
        <FileExcelOutlined />
        <span className="ml-2">Import</span>
      </button>

      <Modal
        title="Import Data"
        open={isModalOpen}
        onOk={() => {}}
        cancelText={"Tutup"}
        onCancel={() => handleCancel()}
        okButtonProps={{ style: { display: "none" } }}
        // okText={"Import Data"}
      >
        <div className="w-full">
          <p className="alert alert-info">
            Silakan import template excel yang telah di generate sebelumnya di
            Download Template
          </p>
          <ModalImportConfirm
            refetch={() => handleRefetch()}
            type={"konsinyasi"}
          />
          {/* <Form
            form={form}
            name="basic"
            layout="vertical"
            autoComplete="off"
            encType="multipart/form-data"
          >
            <Form.Item
              label="Upload Excel"
              name="attachment"
              className="w-full"
            >
              <Upload
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
              </Upload>
            </Form.Item>
          </Form> */}
        </div>
      </Modal>
    </div>
  )
}

export default ImportModal
