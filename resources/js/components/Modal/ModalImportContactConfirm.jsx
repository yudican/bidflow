import React, { useState, useEffect } from "react"
import { InboxOutlined } from "@ant-design/icons"
import { Alert, Modal, Result, Spin, Table, Upload } from "antd"
import axios from "axios"
import * as XLSX from "xlsx"
import { toast } from "react-toastify"

const { Dragger } = Upload

const ModalImportContactConfirm = ({ refetch, onLoad }) => {
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [loading, setLoading] = useState(false)
  const [fileList, setFileList] = useState([])
  const [dataImports, setDataImports] = useState([])
  const [errorDataImports, setErrorDataImports] = useState([])
  const [masterData, setMasterData] = useState({})
  const [loadingMasterData, setLoadingMasterData] = useState(false)
  const [uploadedFile, setUploadedFile] = useState(null)
  const [loadingUploadImport, setLoadingUploadImport] = useState(false)

  const showModal = () => {
    setIsModalOpen(true)
    Promise.all([
      getMasterData("/api/general/import-validation-brands"),
      getMasterData("/api/general/import-validation-roles"),
      getMasterData("/api/general/import-validation-users"),
    ]).catch(() => {
      toast.error("Error loading master data")
    })
  }

  const getMasterData = async (url) => {
    try {
      setLoadingMasterData(true)
      const response = await axios.get(url)
      console.log(response.data, url)
      if (url.includes("brands")) {
        setMasterData((prev) => ({
          ...prev,
          brands: response.data.data,
        }))
      } else if (url.includes("roles")) {
        setMasterData((prev) => ({
          ...prev,
          roles: response.data.data,
        }))
      } else if (url.includes("users")) {
        setMasterData((prev) => ({
          ...prev,
          salesTags: [
            { id: 1, value: "karyawan" },
            { id: 2, value: "endorsement" },
            { id: 3, value: "corner" },
            { id: 4, value: "mtp" },
            { id: 5, value: "agent-portal" },
            { id: 6, value: "distributor" },
            { id: 7, value: "super-agent" },
            { id: 8, value: "modern-store" },
            { id: 9, value: "e-store" },
          ],
          contacts: response.data.data,
          existingCsCodes: response.data.data.map((contact) => contact.uid),
        }))
      }
    } catch (error) {
      console.error("Error fetching master data:", error)
      throw error
    } finally {
      setLoadingMasterData(false)
    }
  }

  const validatePhoneNumber = (phone) => {
    if (!phone) return false
    phone = phone.toString().replace(/[\s-]/g, "")

    const validPrefix =
      phone.startsWith("+628") ||
      phone.startsWith("628") ||
      phone.startsWith("08") ||
      phone.startsWith("+622") ||
      phone.startsWith("622") ||
      phone.startsWith("02")

    let normalizedPhone = phone
    if (phone.startsWith("+62")) normalizedPhone = phone.substring(2)
    else if (phone.startsWith("62")) normalizedPhone = phone.substring(1)
    else if (phone.startsWith("0")) normalizedPhone = phone

    const validLength =
      normalizedPhone.length >= 8 && normalizedPhone.length <= 13
    const validChars = /^[+]?\d+$/.test(phone)

    return validPrefix && validLength && validChars
  }

  const validateEmail = (email) => {
    const emailRegex = /^[a-zA-Z0-9._]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/
    return emailRegex.test(email)
  }

  const isValidDate = (dateString) => {
    const regex = /^(0[1-9]|[12][0-9]|3[01])-(0[1-9]|1[0-2])-\d{4}$/
    if (!regex.test(dateString)) return false

    const [day, month, year] = dateString.split("-").map(Number)
    const date = new Date(year, month - 1, day)
    return (
      date.getFullYear() === year &&
      date.getMonth() + 1 === month &&
      date.getDate() === day
    )
  }

  const handleUpload = (info) => {
    setLoading(true)
    onLoad(false)
    const reader = new FileReader()
    reader.onload = (e) => {
      const workbook = XLSX.read(e.target.result, { type: "array" })
      const sheetName = workbook.SheetNames[0]
      const worksheet = workbook.Sheets[sheetName]
      const parsedData = XLSX.utils.sheet_to_json(worksheet)

      const validatedData = parsedData.map((item, rowIndex) => {
        const errors = []

        // 1. Nama validation
        if (!item.nama_npwp) {
          if (item.need_faktur > 0) {
            errors.push("Nama NPWP wajib diisi")
          }
        }
        // 2. CS Code validation
        if (!item.cs_code) {
          errors.push("Customer Code wajib diisi")
        } else if (masterData?.existingCsCodes?.includes(item.cs_code)) {
          errors.push("Customer Code sudah terdaftar")
        }

        // 3. Phone validation
        if (!item.hp) {
          errors.push("Nomor Telepon wajib diisi")
        } else if (!validatePhoneNumber(item.hp)) {
          errors.push(
            "Nomor Telepon tidak valid. Pastikan nomor 8-13 digit dan awalan benar"
          )
        }

        // 4. Email validation
        if (!item.email) {
          errors.push("Email wajib diisi")
        } else if (!validateEmail(item.email)) {
          errors.push(
            "Email tidak valid. Hanya karakter alfanumerik, titik (.) dan garis bawah (_) yang diizinkan."
          )
        }

        // 5. Gender validation
        if (!item.jk) {
          errors.push("Jenis Kelamin wajib diisi")
        } else if (!["Laki-Laki", "Perempuan"].includes(item.jk)) {
          errors.push(
            "Jenis Kelamin hanya boleh diisi dengan Laki-Laki atau Perempuan"
          )
        }

        // 6. Birth date validation
        if (!item.bod) {
          errors.push("BOD wajib diisi")
        } else if (!isValidDate(item.bod)) {
          errors.push("BOD harus menggunakan format tanggal dd-mm-yyyy")
        }

        // 7. Brand validation
        if (!item.brand) {
          errors.push("Brand wajib diisi")
        } else if (!masterData?.brands?.find((b) => b.name === item.brand)) {
          errors.push("Brand tidak sesuai")
        }

        // 8. Role validation
        if (!item.role) {
          errors.push("Role wajib diisi")
        } else if (!masterData?.roles?.find((r) => r.role_name === item.role)) {
          errors.push("Role tidak sesuai")
        }

        // 9. Sales tag validation
        if (!item.sales_tag) {
          errors.push("Sales Tag wajib diisi")
        } else if (
          !masterData?.salesTags?.find((st) => st.value === item.sales_tag)
        ) {
          errors.push("Sales Tag tidak sesuai")
        }

        // 10. Need faktur validation
        if (
          item.need_faktur !== undefined &&
          ![0, 1, "0", "1"].includes(item.need_faktur)
        ) {
          errors.push("Need Faktur hanya boleh diisi dengan 0 atau 1")
        }

        // 11. NPWP validation
        if (item.need_faktur > 0) {
          if (!item.no_npwp) {
            errors.push("Nomor NPWP wajib diisi karena membutuhkan faktur")
          } else if (!/^\d{16}$/.test(item.no_npwp.toString())) {
            errors.push("Nomor NPWP harus terdiri dari 16 angka")
          }
        }

        if (!item.need_faktur) {
          errors.push(
            "Need Faktur wajib di isi dan hanya boleh diisi dengan 0 atau 1"
          )
        }

        // 12. Created by validation
        if (!item.created_by) {
          errors.push("Created by wajib diisi")
        } else if (
          !masterData?.contacts.find((c) => c.name === item.created_by)
        ) {
          errors.push("Created by tidak ditemukan")
        }

        return {
          ...item,
          rowIndex: rowIndex + 2,
          errors: errors,
        }
      })

      setDataImports(validatedData)

      // Separate error records for display
      const errorRecords = validatedData.filter(
        (item) => item.errors.length > 0
      )
      setErrorDataImports(errorRecords)
    }

    reader.readAsArrayBuffer(info)
    setTimeout(() => setLoading(false), 1000)
  }

  const handleOkAndImport = () => {
    if (dataImports.length === 0) {
      toast.error("Tidak ada data yang akan diimport")
      return
    }

    if (errorDataImports.length > 0) {
      toast.error(`Masih terdapat ${errorDataImports.length} data yang error`)
      return
    }

    setLoadingUploadImport(true)
    const formData = new FormData()
    formData.append("file", fileList[0].originFileObj)

    axios
      .post("/api/contacts/import", formData)
      .then((response) => {
        setLoadingUploadImport(false)
        refetch?.()
        toast.success("Import Sedang Diproses, Mohon Tunggu")
        handleCloseModal()
      })
      .catch((error) => {
        setLoadingUploadImport(false)
        toast.error(
          error.response?.data?.message || "Terjadi kesalahan saat import"
        )
      })
  }

  const handleCloseModal = () => {
    setFileList([])
    setUploadedFile(null)
    setDataImports([])
    setErrorDataImports([])
    setIsModalOpen(false)
    window.location.reload()
  }

  useEffect(() => {
    if (masterData && uploadedFile) {
      handleUpload(uploadedFile)
    }
  }, [masterData])

  const dataLoading = loading || loadingMasterData || loadingUploadImport
  console.log(
    errorDataImports.length > 0 || dataLoading || fileList.length === 0,
    "masterData"
  )
  return (
    <div>
      <Dragger
        accept=".xlsx, .xlsm, .xlsb"
        multiple={false}
        className="w-full"
        showUploadList={false}
        beforeUpload={(file) => {
          showModal()
          setUploadedFile(file)
          return false
        }}
        onChange={({ fileList }) => setFileList(fileList)}
      >
        <p className="ant-upload-drag-icon">
          <InboxOutlined />
        </p>
        <p className="ant-upload-text">
          Click or drag file to this area to upload
        </p>
      </Dragger>

      <Modal
        title="Validasi Import Contact"
        open={isModalOpen}
        onOk={handleOkAndImport}
        onCancel={handleCloseModal}
        width={1000}
        confirmLoading={dataLoading}
        okText="Import Data"
        cancelText="Batal"
        okButtonProps={{
          disabled:
            errorDataImports.length > 0 || dataLoading || fileList.length === 0,
        }}
      >
        {dataLoading ? (
          <div className="flex justify-center items-center h-96">
            <Spin />
          </div>
        ) : (
          <div>
            {errorDataImports.length === 0 ? (
              <Result
                status="success"
                title="Tidak Ada Masalah"
                subTitle="Data yang kamu import tidak ditemukan masalah, silahkan lanjut untuk import data"
              />
            ) : (
              <div>
                <div className="alert alert-info mb-4">
                  Silakan perbaiki data yang error sebelum melanjutkan import
                </div>
                <div className="flex items-center mb-4 gap-8">
                  <div>
                    <span className="font-bold">Total Data:</span>{" "}
                    {dataImports.length}
                  </div>
                  <div className="text-green-500">
                    <span className="font-bold">Data Valid:</span>{" "}
                    {dataImports.length - errorDataImports.length}
                  </div>
                  <div className="text-red-500">
                    <span className="font-bold">Data Error:</span>{" "}
                    {errorDataImports.length}
                  </div>
                </div>
                <Table
                  dataSource={errorDataImports}
                  columns={[
                    {
                      title: "Baris",
                      dataIndex: "rowIndex",
                      key: "rowIndex",
                      width: 80,
                    },
                    {
                      title: "CS Code",
                      dataIndex: "cs_code",
                      key: "cs_code",
                      width: 120,
                    },
                    {
                      title: "Nama",
                      dataIndex: "nama",
                      key: "nama",
                      width: 150,
                    },
                    {
                      title: "Error",
                      key: "errors",
                      render: (record) => (
                        <Alert
                          type="error"
                          message={
                            <ul
                              style={{ listStyleType: "circle" }}
                              className="pl-4"
                            >
                              {record.errors.map((error, index) => (
                                <li key={index}>{error}</li>
                              ))}
                            </ul>
                          }
                        />
                      ),
                    },
                  ]}
                  pagination={false}
                />
              </div>
            )}
          </div>
        )}
      </Modal>
    </div>
  )
}

export default ModalImportContactConfirm
