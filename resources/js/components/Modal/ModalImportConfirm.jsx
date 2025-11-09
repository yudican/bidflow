import { InboxOutlined } from "@ant-design/icons"
import { Alert, Modal, Result, Spin, Table, Upload } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { toast } from "react-toastify"
import * as XLSX from "xlsx" // For SheetJS
import { inArray } from "../../helpers"

const { Dragger } = Upload
const ModalImportConfirm = ({ url, type = "manual", refetch }) => {
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [loading, setLoading] = useState(false)
  const [fileList, setFileList] = useState([])
  const [dataImports, setDataImports] = useState([])
  const [dataValidations, setDataValidations] = useState(null)
  const [dataValidationUsers, setDataValidationUsers] = useState(null)
  const [dataValidationBins, setDataValidationBins] = useState([])
  const [dataValidationKonsinyasi, setDataValidationKonsinyasi] = useState([])
  const [loadingDataValidation, setLoadingDataValidation] = useState(false)
  const [loadingDataValidationUser, setLoadingDataValidationUser] =
    useState(false)
  const [loadingDataValidationBin, setLoadingDataValidationBin] =
    useState(false)
  const [loadingDataValidationKonsinyasi, setLoadingDataValidationKonsinyasi] =
    useState(false)
  const [uploadedFile, setUploadedFile] = useState(null)
  const [loadingUploadImport, setLoadingUploadImport] = useState(false)
  const isTfKonsi = type == "transfer-konsinyasi" ? true : false
  const is_konsinyasi = type == "konsinyasi" ? true : false
  const is_normal = inArray(type, ["manual", "freebies"]) ? true : false
  const keyData = isTfKonsi ? "Code TF" : "Code SO"
  const userKey = "customer code"
  const binKey =
    !is_normal && is_konsinyasi ? "destinasi bin id" : "destinasi bin id"
  const binLabel = !is_normal ? "Destinasi BIN ID" : "BIN ID"
  const showModal = () => {
    setIsModalOpen(true)

    getDataValidationUsers()
    if (isTfKonsi) {
      getDataValidationBins()
      getDataValidations("master")
    } else {
      getDataValidations()
    }

    if (type == "konsinyasi") {
      getDataValidationBins()
      // getDataValidationSoKonsinyasi()
    }
  }
  console.log("masuk sini")
  const getDataValidations = (product_type = "variants") => {
    setLoadingDataValidation(true)
    axios
      .get(`/api/general/import-validation/${product_type}`)
      .then((res) => {
        setDataValidations(res?.data?.data)
        setLoadingDataValidation(false)
      })
      .catch((err) => setLoadingDataValidation(false))
  }
  const getDataValidationUsers = () => {
    setLoadingDataValidationUser(true)
    axios
      .get("/api/general/import-validation-users")
      .then((res) => {
        setDataValidationUsers({ users: res?.data?.data })
        setLoadingDataValidationUser(false)
      })
      .catch((err) => setLoadingDataValidationUser(false))
  }

  const getDataValidationBins = () => {
    setLoadingDataValidationBin(true)
    axios
      .get("/api/general/import-validation-bins")
      .then((res) => {
        setDataValidationBins(res?.data?.data)
        setLoadingDataValidationBin(false)
      })
      .catch((err) => setLoadingDataValidationBin(false))
  }

  const getDataValidationSoKonsinyasi = () => {
    setLoadingDataValidationKonsinyasi(true)
    axios
      .get("/api/general/import-validation-konsinyasi")
      .then((res) => {
        setDataValidationKonsinyasi(res?.data?.data)
        setLoadingDataValidationKonsinyasi(false)
      })
      .catch((err) => setLoadingDataValidationKonsinyasi(false))
  }

  const handleUpload = (info) => {
    setLoading(true)
    const reader = new FileReader()
    reader.onload = (e) => {
      const arrayBuffer = e.target.result
      const workbook = XLSX.read(arrayBuffer, { type: "array" })
      const sheetName = workbook.SheetNames[0]
      const worksheet = workbook.Sheets[sheetName]
      const parsedData = XLSX.utils.sheet_to_json(worksheet)

      // Validasi format kolom yang dibutuhkan
      let requiredColumns = [
        keyData,
        "nama product",
        userKey,
        "qty",
        "diskon rp",
        "harga_satuan",
        "created by",
        "sales",
        "tax id",
      ]

      // Tambahkan bin_id ke required columns hanya jika bukan freebies atau manual
      if (!inArray(type, ["freebies", "manual"])) {
        requiredColumns.push(binKey)
      }

      if (!inArray(type, ["freebies"])) {
        requiredColumns.push("harga_satuan")
      }

      if (inArray(type, ["freebies", "manual", "konsinyasi"])) {
        requiredColumns.push("expired at")
      }

      if (isTfKonsi || is_konsinyasi) {
        requiredColumns.push("kategori_data")
      }

      // Cek header kolom
      const headers = Object.keys(parsedData[0] || {})
      const missingColumns = requiredColumns.filter(
        (col) => !headers.includes(col)
      )

      // if (missingColumns.length > 0) {
      //   setDataImports(
      //     parsedData.map((item) => ({
      //       ...item,
      //       alert: (
      //         <Alert
      //           type="error"
      //           message={
      //             <ul style={{ listStyleType: "circle" }} className="pl-4">
      //               <li>
      //                 Format file tidak sesuai. Kolom yang tidak ditemukan:{" "}
      //                 {missingColumns.join(", ")}
      //               </li>
      //             </ul>
      //           }
      //         />
      //       ),
      //     }))
      //   )
      //   setLoading(false)
      //   return
      // }

      // Validasi data kosong per baris
      setDataImports(
        parsedData.map((item, rowIndex) => {
          const code_so = getValue(item, keyData)
          const product_name = getValue(item, "nama product")
          const kategori_data = getValue(item, "kategori_data")
          const uid = getValue(item, userKey)
          const qty = getValue(item, "qty")
          const price = inArray(type, ["freebies"])
            ? true
            : getValue(item, "harga_satuan")
          const warehouse = is_konsinyasi
            ? true
            : getValue(item, "warehouse id")
          const payment_term = is_konsinyasi
            ? "Manual Transfer"
            : getValue(item, "payment_term")
          const diskon = getValue(item, "diskon rp")
          const expired_at = getValue(item, "expired at")
          const created_by = getValue(item, "created by")
          const sales = getValue(item, "sales")
          const tax_id = getValue(item, "tax id")
          const bin_id = isTfKonsi
            ? getValue(item, binKey)
            : getValue(item, "destinasi bin id")
          const so_konsinyasi = is_konsinyasi
            ? getValue(item, "so konsinyasi")
            : true
          const productExist = product_name
            ? dataValidations?.products?.find(
                (item) => item.name == product_name
              )
            : true

          const warehouseExist = warehouse
            ? inArray(
                warehouse,
                dataValidations?.warehouses?.map((item) => item.id) || []
              )
            : true
          const paymentTermExist = payment_term
            ? dataValidations?.payment_terms?.find(
                (item) => item.name == payment_term
              )
            : true
          const uidExist = uid
            ? inArray(
                uid,
                dataValidationUsers?.users?.map((item) => item.uid) || []
              )
            : true

          const createdByExist = created_by
            ? dataValidationUsers?.users?.find(
                (item) => item.name == created_by
              )
            : true
          const salesExist = sales
            ? dataValidationUsers?.users?.find((item) => item.name == sales)
            : true

          const taxExist = tax_id
            ? dataValidations?.master_tax?.find((item) => item.id == tax_id)
            : true
          const binExist = bin_id
            ? dataValidationBins?.find((item) => item.id == bin_id)
            : true
          // const orderNumberExist = so_konsinyasi
          //   ? dataValidationKonsinyasi?.find(
          //     (item) => item.order_number == so_konsinyasi
          //   )
          //   : true
          const orderNumberExist = true

          let validatePrice = price < 1
          let validPrice = true
          let validPriceKonsi = true
          let validQty = true

          let validateBinId = true
          let validateBinSoKonsinyasi = true
          let validateTaxExist = true
          let validateBinExist = true
          let validateOrderNumberExist = true

          let expiredAtExist = true

          if (expired_at) {
            expiredAtExist = expired_at
          }

          if (tax_id) {
            validateTaxExist = taxExist
          }

          if (isTfKonsi) {
            validateBinId = bin_id
            validateBinExist = binExist
          }

          if (is_konsinyasi) {
            //   validateBinId = true
            //   // validateBinSoKonsinyasi = so_konsinyasi
            //   validateBinSoKonsinyasi = true

            validateBinExist = binExist
            //   validateOrderNumberExist = orderNumberExist
          }

          if (isTfKonsi) {
            validateBinId = bin_id
            validateBinExist = binExist
          }

          if (inArray(type, ["freebies"])) {
            validatePrice = price < 0
          }

          let validCodeSo = true
          if (code_so) {
            if (isNaN(code_so)) {
              validCodeSo = false
            }
          }
          let validDiscount = true
          if (diskon) {
            if (isNaN(diskon)) {
              validDiscount = false
            }
          }
          if (price) {
            if (isNaN(price)) {
              validPrice = false
            }
          }
          if (qty) {
            if (isNaN(qty)) {
              validQty = false
            }
          }

          let validExpiredDate = expired_at
            ? isValidDateFormat(expired_at)
            : true
          let qtyExist = qty >= 0 || qty <= 0 || qty ? true : false
          let priceExist = price >= 0 || price <= 0 || price ? true : false
          let diskonExist = diskon >= 0 || diskon <= 0 || diskon ? true : false
          let errors = []

          if (!code_so) errors.push(`${keyData} wajib diisi `)
          if (!product_name) errors.push(`Nama Produk wajib diisi `)
          // if (!kategori_data) errors.push(`Kategori Data wajib diisi `)
          if (!uid || uid === "" || uid === undefined)
            errors.push(`${userKey} wajib diisi `)
          if (!qtyExist) errors.push("QTY wajib diisi")
          if (!is_konsinyasi && !warehouse)
            errors.push("Warehouse ID wajib diisi")
          if (!is_konsinyasi && !payment_term)
            errors.push("Payment Term wajib diisi")
          if (!created_by) errors.push("Created By wajib diisi")
          if (!sales) errors.push("Sales wajib diisi")
          if (!tax_id) errors.push("Tax ID wajib diisi")
          if (!bin_id && !inArray(type, ["freebies", "manual"]))
            errors.push(`${binLabel} wajib diisi`)
          if (!isTfKonsi && !expired_at) errors.push("Expired At wajib diisi")
          if (!price && price != 0) errors.push("Harga satuan wajib diisi")

          // Validasi format data
          if (code_so && !validCodeSo)
            errors.push(`${keyData} harus berupa angka `)
          if (qty && !validQty) errors.push(`QTY harus berupa angka `)
          if (qty < 1) errors.push("QTY tidak boleh kurang dari 1")
          if (price && !validPrice)
            errors.push("Harga Satuan harus berupa angka")
          if (diskon < 0) {
            errors.push("Diskon tidak boleh kurang dari 0")
          }
          if (price < 1) {
            errors.push("Harga satuan tidak boleh kurang dari 1")
          }
          if (diskon === null || diskon === undefined) {
            errors.push("Diskon wajib diisi")
          }
          if (diskon && !validDiscount) errors.push("Diskon harus berupa angka")
          if (expired_at && !validExpiredDate)
            errors.push("Format tanggal Expired At harus DD-MM-YYYY")

          // Validasi data master
          if (!productExist) errors.push(`Nama Produk tidak sesuai `)
          if (!uidExist) errors.push(`${userKey} tidak ditemukan `)
          if (!is_konsinyasi && !warehouseExist)
            errors.push("Warehouse ID tidak sesuai")
          if (!is_konsinyasi && !paymentTermExist)
            errors.push("Payment Term tidak sesuai")
          if (!createdByExist) errors.push("Created By tidak sesuai")
          if (!salesExist) errors.push("Sales tidak sesuai")
          if (!taxExist) errors.push("Tax ID tidak sesuai")
          if (isTfKonsi && !binExist) errors.push(`${binLabel} tidak sesuai`)
          if ((isTfKonsi || is_konsinyasi) && !kategori_data) {
            errors.push(`Kategori Data wajib diisi`)
          }
          if (!binExist) errors.push(`${binLabel} tidak sesuai`)

          if (kategori_data) {
            if (
              (isTfKonsi || is_konsinyasi) &&
              !inArray(kategori_data, ["new", "old"])
            ) {
              errors.push(`Kategori Data hanya boleh diisi dengan new atau old`)
            }
          }

          const alert =
            errors.length > 0 ? (
              <Alert
                type="error"
                message={
                  <ul style={{ listStyleType: "circle" }} className="pl-4">
                    {errors.map((error, index) => (
                      <li key={index}>{error}</li>
                    ))}
                  </ul>
                }
              />
            ) : null

          return {
            ...item,
            alert,
            rowIndex: rowIndex + 2,
          }
        })
      )
    }

    reader.readAsArrayBuffer(info)
    setTimeout(() => {
      setLoading(false)
    }, 2000)
  }

  const handleOkAndImport = () => {
    if (dataImports.length === 0) {
      toast.error("Tidak ada data yang akan diimport", {
        position: toast.POSITION.TOP_RIGHT,
      })
      return
    }

    const errorData = dataImports.filter((item) => item.alert)
    if (errorData.length > 0) {
      toast.error(`Masih terdapat ${errorData.length} data yang error`, {
        position: toast.POSITION.TOP_RIGHT,
      })
      return
    }

    setLoadingUploadImport(true)
    const formData = new FormData()
    formData.append("type", type)
    formData.append("file", fileList[0].originFileObj)

    const url =
      type == "transfer-konsinyasi"
        ? "/api/transfer-konsinyasi/import"
        : "/api/sales-order/import"

    axios
      .post(url, formData)
      .then((response) => {
        setLoadingUploadImport(false)
        refetch && refetch()
        toast.success("Import Sedang Diproses, Mohon Tunggu", {
          position: toast.POSITION.TOP_RIGHT,
        })
        handleCloseModal()
      })
      .catch((error) => {
        setLoadingUploadImport(false)
        const errorMessage =
          error.response?.data?.message || "Terjadi kesalahan saat import"
        setDataImports((prev) =>
          prev.map((item) => ({
            ...item,
            alert: <Alert type="error" message={errorMessage} />,
          }))
        )
      })
  }

  // Tambahkan fungsi untuk handle close modal
  const handleCloseModal = () => {
    setFileList([])
    setUploadedFile(null)
    setDataImports([])
    setIsModalOpen(false)
    window.location.reload()
  }

  useEffect(() => {
    if (
      dataValidations ||
      dataValidationUsers ||
      dataValidationBins ||
      dataValidationKonsinyasi
    ) {
      uploadedFile && handleUpload(uploadedFile) // Panggil fungsi handleUpload dengan file
    }
  }, [
    dataValidations,
    dataValidationUsers,
    dataValidationBins,
    dataValidationKonsinyasi,
  ])

  const finalDataImport = dataImports.reduce((acc, item) => {
    if (item.alert) {
      const groupKey = `${item[keyData] || ""}-${item[userKey] || ""}`

      if (!acc[groupKey]) {
        acc[groupKey] = {
          errors: {},
          [keyData]: item[keyData],
          [userKey]: item[userKey],
        }
      }

      // Ekstrak pesan error
      const errorMessages = Array.isArray(
        item.alert.props.message.props.children
      )
        ? item.alert.props.message.props.children.map((li) => li.props.children)
        : [item.alert.props.message.props.children]

      // Simpan error berdasarkan nomor baris
      acc[groupKey].errors[item.rowIndex] = errorMessages

      return acc
    }
    return acc
  }, {})

  // Konversi hasil ke format yang sesuai untuk Table
  const formattedDataImport = Object.values(finalDataImport).map(
    (item, index) => ({
      key: index,
      [keyData]: item[keyData],
      [userKey]: item[userKey],
      alert: (
        <Alert
          type="error"
          message={
            <div>
              {Object.entries(item.errors).map(([rowNum, errors]) => (
                <div key={rowNum}>
                  <strong>Baris {rowNum}:</strong>
                  <ul style={{ listStyleType: "circle" }} className="pl-4">
                    {errors.map((error, idx) => (
                      <li key={idx}>{error}</li>
                    ))}
                  </ul>
                </div>
              ))}
            </div>
          }
        />
      ),
    })
  )

  console.log("finalDataImport", finalDataImport?.length)
  const dataLoading =
    loading ||
    loadingDataValidation ||
    loadingUploadImport ||
    loadingDataValidationUser ||
    loadingDataValidationBin ||
    loadingDataValidationKonsinyasi
  return (
    <div>
      <Dragger
        accept=".xlsx, .xlsm, .xlsb"
        multiple={false}
        className="w-full"
        name="attachment"
        showUploadList={false}
        // fileList={fileList}
        // beforeUpload={() => false}
        beforeUpload={(file) => {
          showModal()
          setUploadedFile(file)
          return false
        }}
        onChange={({ fileList: newFileList }) => setFileList(newFileList)}
      >
        <p className="ant-upload-drag-icon">
          <InboxOutlined />
        </p>
        <p className="ant-upload-text">
          Click or drag file to this area to upload
        </p>
      </Dragger>

      <Modal
        title="Validasi Import"
        open={isModalOpen}
        onOk={handleOkAndImport}
        cancelText="Batal"
        onCancel={handleCloseModal}
        okText="Import Data"
        confirmLoading={dataLoading}
        width={1000}
        okButtonProps={{
          disabled:
            finalDataImport?.length > 0 ||
            Object.keys(finalDataImport).length > 0 ||
            dataLoading,
        }}
      >
        {dataLoading ? (
          <div className="flex justify-center items-center h-96">
            <Spin />
          </div>
        ) : (
          <div>
            {Object.keys(finalDataImport).length === 0 ? (
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
                    <span className="font-bold">
                      Total {isTfKonsi ? "TF" : "SO"}:
                    </span>{" "}
                    {
                      [...new Set(dataImports.map((item) => item[keyData]))]
                        .length
                    }
                  </div>
                  <div className="text-green-500">
                    <span className="font-bold">Data Valid:</span>{" "}
                    {[...new Set(dataImports.map((item) => item[keyData]))]
                      .length - Object.keys(finalDataImport).length}
                  </div>
                  <div className="text-red-500">
                    <span className="font-bold">Data Error:</span>{" "}
                    {Object.keys(finalDataImport).length}
                  </div>
                </div>
                <Table
                  loading={loadingDataValidation}
                  dataSource={formattedDataImport}
                  columns={[
                    {
                      title: keyData,
                      dataIndex: keyData,
                      key: keyData,
                      render: (text, record) =>
                        record?.[keyData] || record?.["Code TF"],
                    },
                    {
                      title: "Customer Code",
                      dataIndex: "uid",
                      key: "uid",
                      render: (text, record) =>
                        record?.["uid"] || record?.["customer code"],
                    },
                    {
                      title: "Pesan Error",
                      dataIndex: "alert",
                      key: "alert",
                    },
                  ]}
                />
              </div>
            )}
          </div>
        )}
      </Modal>
    </div>
  )
}

const getValue = (data, key) => {
  try {
    return data[key]
  } catch (error) {
    return null
  }
}

function containsLetter(str) {
  // Regular expression untuk mendeteksi huruf
  const letterPattern = /[a-zA-Z]/

  // Mengembalikan true jika terdapat huruf, false jika tidak ada
  return letterPattern.test(str)
}

function isValidDateFormat(dateString) {
  // Regular expression for dd-mm-yyyy format
  const regex = /^(0[1-9]|[12][0-9]|3[01])-(0[1-9]|1[0-2])-\d{4}$/

  // Check if the string matches the regex
  if (!regex.test(dateString)) {
    return false
  }

  // Split the date string into parts
  const [day, month, year] = dateString.split("-").map(Number)

  // Create a new date object using the parts
  const date = new Date(year, month - 1, day)

  // Check if the date object is valid (e.g., no 31st of February)
  return (
    date.getFullYear() === year &&
    date.getMonth() + 1 === month &&
    date.getDate() === day
  )
}

export default ModalImportConfirm
