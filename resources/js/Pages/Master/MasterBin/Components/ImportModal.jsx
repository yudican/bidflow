import {
    FileExcelOutlined,
    FilterOutlined,
    UploadOutlined,
  } from "@ant-design/icons"
  import { Button, Form, Modal, Upload } from "antd"
  import axios from "axios"
  import React, { useEffect, useState } from "react"
  import { toast } from "react-toastify"
  
  const ImportModal = ({ handleOk }) => {
    const [isModalOpen, setIsModalOpen] = useState(false)
    const [isFilter, setIsFilter] = useState(false)
    const [roles, setRoles] = useState([])
    const [filter, setFilter] = useState({
      roles: null,
      status: null,
      createdBy: null,
    })
    const [form] = Form.useForm()
    // attachments
    const [loadingAtachment, setLoadingAtachment] = useState(false)
  
    const [fileList, setFileList] = useState([])
  
    const handleChange = ({ fileList: newFileList }) => {
      setFileList(newFileList)
    }
  
    const showModal = () => {
      loadRole()
      setIsModalOpen(true)
    }
  
    const loadRole = () => {
      axios.get("/api/master/role").then((res) => {
        setRoles(res.data.data)
      })
    }
  
    const handleOkAndImport = () => {
      handleOk(filter)
  
      const formData = new FormData()
      formData.append("attachment", fileList[0].originFileObj)
      console.log(formData)
  
      axios
        .post("/api/bin/import", formData)
        .then((response) => {
          const { message } = response.data
          console.log(response.data)
          // console.log("Data berhasil diimpor:", response.data)
          if (response.data.status == 'failed') {
            toast.error(response.data.message, {
              position: toast.POSITION.TOP_RIGHT,
            })
            
          } else {
            toast.success(response.data.message, {
              position: toast.POSITION.TOP_RIGHT,
            })
          }
          
          setIsModalOpen(false)
        })
        .catch((error) => {
          console.error("Terjadi kesalahan saat mengimpor data:", error)
          toast.error("Data gagal diimpor, cek kembali inputan anda", {
            position: toast.POSITION.TOP_RIGHT,
          })
        })
  
      setIsModalOpen(false)
    }
  
    useEffect(() => {
      loadRole()
    }, [])
  
    const handleCancel = () => {
      setIsModalOpen(false)
      setIsFilter(false)
      setFilter({
        roles: null,
        status: null,
        createdBy: null,
      })
    }
  
    const clearFilter = () => {
      setIsModalOpen(false)
      setIsFilter(false)
      setFilter({
        roles: null,
        status: null,
        createdBy: null,
      })
      handleOk({})
    }
    return (
      <div className="">
        {isFilter ? (
          <button
            onClick={() => showModal()}
            className="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center w-full"
          >
            <FilterOutlined />
            <span className="ml-2">Show Filter</span>
          </button>
        ) : (
          <button
            onClick={() => showModal()}
            //   className="
            //   bg-red-700 border
            //   text-white hover:bg-red-800
            //   delay-100 ease-in-out
            //   focus:ring-4 focus:outline-none focus:ring-blue-300
            //   font-medium rounded-lg
            //   text-sm px-4 py-2 text-center inline-flex items-center w-full
            // "
          >
            <FileExcelOutlined />
            <span className="ml-2">Import</span>
          </button>
        )}
  
        <Modal
          title="Import Bin"
          open={isModalOpen}
          onOk={handleOkAndImport}
          cancelText={isFilter ? "Clear Filter" : "Cancel"}
          onCancel={isFilter ? clearFilter : handleCancel}
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
                <a href="/assets/template/import-bin.xlsx" download>
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
  