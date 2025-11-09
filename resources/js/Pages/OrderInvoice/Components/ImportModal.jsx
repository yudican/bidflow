import { FileExcelOutlined, UploadOutlined } from "@ant-design/icons"
import { Button, Form, Modal, Upload } from "antd"
import axios from "axios"
import React, { useState } from "react"
import { toast } from "react-toastify"
const ImportModal = ({ handleOk }) => {
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
      .post("/api/so/import", formData)
      .then((response) => {
        if (response.data.status == "failed") {
          toast.error(response.data.message)
        } else {
          const { message } = response.data
          setLoadingAtachment(false)
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

  return (
    <div className="w-full">
      <button onClick={() => showModal()}>
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
          <Form
            form={form}
            name="basic"
            layout="vertical"
            autoComplete="off"
            encType="multipart/form-data"
          >
            <p className="alert alert-info">
              Sebelum melakukan import pastikan sudah sesuai template yang telah
              disediakan, jika belum silahkan melakukan download terlebih dahulu
              dengan klik{" "}
              <a href="/assets/template/import-so.xlsx" download>
                Download Template
              </a>
            </p>
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
          </Form>
        </div>
      </Modal>
    </div>
  )
}

export default ImportModal
