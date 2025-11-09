import { FileExcelOutlined } from "@ant-design/icons"
import { Form, Modal } from "antd"
import axios from "axios"
import React, { useState } from "react"
import { toast } from "react-toastify"
import ModalImportConfirm from "../../../components/Modal/ModalImportConfirm"
import { searchContact } from "../service"
const ImportModal = ({ handleOk, refetch, withContact, type = "manual" }) => {
  const [isModalOpen, setIsModalOpen] = useState(false)

  const [form] = Form.useForm()
  // attachments
  const [loadingAtachment, setLoadingAtachment] = useState(false)
  const [contactList, setContactList] = useState([])
  const [seletedContact, setSeletedcontact] = useState(null)

  const [fileList, setFileList] = useState([])

  const handleChange = ({ fileList: newFileList }) => {
    setFileList(newFileList)
  }

  const handleGetContact = async () => {
    await searchContact(null).then((results) => {
      const newResult = results.map((result) => {
        return { label: result.nama, value: result.id }
      })
      setContactList(newResult)
    })
  }

  const showModal = () => {
    setIsModalOpen(true)
    handleGetContact()
  }

  const handleOkAndImport = () => {
    setLoadingAtachment(true)
    const formData = new FormData()
    formData.append("attachment", fileList[0].originFileObj)
    if (withContact) {
      formData.append("contact", seletedContact?.value)
    }
    axios
      .post("/api/so/import", formData)
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
            <a href="/assets/template/import-freebies.xlsx" download>
              Download Template
            </a>
          </p>
          <ModalImportConfirm
            refetch={() => handleRefetch()}
            type={"freebies"}
          />
        </div>
      </Modal>
    </div>
  )
}

export default ImportModal
