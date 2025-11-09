import { CheckOutlined, LoadingOutlined, PlusOutlined } from "@ant-design/icons"
import { Card, Form, Input, Select, Upload, message } from "antd"
import React, { useEffect, useState } from "react"
import "react-draft-wysiwyg/dist/react-draft-wysiwyg.css"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../../components/layout"
import RichtextEditor from "../../../components/RichtextEditor"
import { getBase64 } from "../../../helpers"
import "../../../index.css"

const FormPaymentMethod = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const { payment_method_id } = useParams()

  const [parents, setParents] = useState([])
  const [parentId, setParentId] = useState(null)
  const [imageLoading, setImageLoading] = useState(false)
  const [imageUrl, setImageUrl] = useState(false)
  const [fileList, setFileList] = useState(false)

  const [typePembayaran, setTypePembayaran] = useState(null)
  const [paymentChannel, setPaymentChannel] = useState(null)

  const [loadingSubmit, setLoadingSubmit] = useState(false)

  const loadDetailData = () => {
    axios.get(`/api/master/payment-method/${payment_method_id}`).then((res) => {
      const { data } = res.data

      // 1. Ambil data object dan ubah menjadi array key-value pairs menggunakan Object.entries()
      // 2. Lakukan mapping pada setiap key-value pair:
      //    - Jika value undefined/null/string "undefined", ganti dengan string kosong
      //    - Jika tidak, gunakan value asli
      // 3. Ubah kembali array hasil mapping menjadi object menggunakan Object.fromEntries()
      const newData = Object.fromEntries(
        Object.entries(data).map(([key, value]) => [
          key,
          value === undefined || value === "undefined" || value === null
            ? ""
            : value,
        ])
      )

      setImageUrl(newData.logo)
      setTypePembayaran(newData.payment_type)
      setPaymentChannel(newData.payment_channel)
      setParentId(newData.parent_id)
      form.setFieldsValue(newData)
    })
  }

  const getParents = () => {
    axios.get(`/api/master/payment-method-parents`).then((res) => {
      const { data } = res.data
      setParents(data)
    })
  }

  useEffect(() => {
    getParents()
    loadDetailData()
  }, [])

  const handleChange = ({ fileList }) => {
    const list = fileList.pop()
    setImageLoading(true)
    setTimeout(() => {
      const size = list.size / 1024
      if (size > 1024) {
        setImageLoading(false)
        return message.error("Maksimum ukuran file adalah 1 MB")
      }
      getBase64(list.originFileObj, (url) => {
        setImageLoading(false)
        setImageUrl(url)
      })
      setFileList(list.originFileObj)
    }, 1000)
  }

  const onFinish = (values) => {
    setLoadingSubmit(true)
    let formData = new FormData()
    if (fileList) {
      formData.append("logo_bank", fileList)
    }

    formData.append("nama_bank", values.nama_bank)
    formData.append("nomor_rekening_bank", values.nomor_rekening_bank)
    formData.append("nama_rekening_bank", values.nama_rekening_bank)
    formData.append("parent_id", values.parent_id)
    formData.append("payment_type", values.payment_type)
    formData.append("payment_channel", values.payment_channel)
    formData.append("payment_code", values.payment_code)
    formData.append("payment_va_number", values.payment_va_number)
    formData.append("status", values.status)

    const url = payment_method_id
      ? `/api/master/payment-method/save/${payment_method_id}`
      : "/api/master/payment-method/save"

    axios
      .post(url, formData)
      .then((res) => {
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        setLoadingSubmit(false)
        return navigate("/master/payment-method")
      })
      .catch((err) => {
        const { message } = err.response.data
        setLoadingSubmit(false)
        toast.error(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }

  const uploadButton = (
    <div>
      {imageLoading ? <LoadingOutlined /> : <PlusOutlined />}
      <div
        style={{
          marginTop: 8,
        }}
      >
        Upload
      </div>
    </div>
  )

  const rightContent = (
    <div className="flex justify-between items-center">
      <button
        onClick={() => form.submit()}
        className="text-white bg-blueColor hover:bg-blueColor/90 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
      >
        {loadingSubmit ? <LoadingOutlined /> : <CheckOutlined />}
        <span className="ml-2">Simpan</span>
      </button>
    </div>
  )

  const isPaymentAuto = typePembayaran === "Otomatis" ? true : false
  const isHavePaymentVaNumber =
    paymentChannel === "bank_transfer" || paymentChannel === "echannel"
  const canInputVaNumber = isPaymentAuto && isHavePaymentVaNumber && true
  return (
    <Layout
      title="Form Metode Pembayaran"
      href="/master/payment-method"
      // rightContent={rightContent}
      rightContent={
        <button
          onClick={() => form.submit()}
          className="text-white bg-blueColor hover:bg-blueColor/90 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
        >
          {loadingSubmit ? <LoadingOutlined /> : <CheckOutlined />}
          <span className="ml-2">Simpan</span>
        </button>
      }
    >
      <Form
        form={form}
        name="basic"
        layout="vertical"
        onFinish={onFinish}
        //   onFinishFailed={onFinishFailed}
        autoComplete="off"
      >
        <Card title="Metode Pembayaran Data">
          <div className="card-body row">
            <div className="col-md-6">
              <Form.Item
                label="Jenis Metode Pembayaran"
                name="parent_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Jenis Metode Pembayaran!",
                  },
                ]}
              >
                <Select
                  allowClear
                  className="w-full"
                  placeholder="Pilih Jenis Metode Pembayaran"
                  onChange={(e) => setParentId(e)}
                >
                  {parents.map((item) => (
                    <Select.Option key={item.id} value={item.id}>
                      {item.nama_bank}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>

              {parentId > 0 && (
                <Form.Item
                  label="Tipe Pembayaran"
                  name="payment_type"
                  rules={[
                    {
                      required: true,
                      message: "Silakan pilih Tipe Pembayaran!",
                    },
                  ]}
                >
                  <Select
                    allowClear
                    className="w-full"
                    placeholder="Pilih Tipe Pembayaran"
                    onChange={(e) => {
                      setTypePembayaran(e)
                      if (e === "Manual") {
                        form.setFieldValue("payment_channel", "bank_transfer")
                      } else {
                        form.setFieldValue("payment_channel", paymentChannel)
                      }
                    }}
                  >
                    <Select.Option key={1} value={"Otomatis"}>
                      Otomatis
                    </Select.Option>
                    <Select.Option key={0} value={"Manual"}>
                      Manual
                    </Select.Option>
                  </Select>
                </Form.Item>
              )}
              {getPaymentCode(paymentChannel, typePembayaran).length > 0 && (
                <Form.Item
                  label="Kode Pembayaran"
                  name="payment_code"
                  rules={[
                    {
                      required: true,
                      message: "Silakan pilih Kode Pembayaran!",
                    },
                  ]}
                >
                  <Select
                    allowClear
                    className="w-full"
                    placeholder="Pilih Kode Pembayaran"
                  >
                    {getPaymentCode(paymentChannel, typePembayaran).map(
                      (item) => (
                        <Select.Option key={item.value} value={item.value}>
                          {item.name}
                        </Select.Option>
                      )
                    )}
                  </Select>
                </Form.Item>
              )}

              {canInputVaNumber && (
                <Form.Item
                  label="Nomor Virtual Account"
                  name="payment_va_number"
                  rules={[
                    {
                      required: true,
                      message: "Silakan masukkan Nomor Virtual Account!",
                    },
                  ]}
                >
                  <Input placeholder="Masukkan Nomor Virtual Account" />
                </Form.Item>
              )}

              {typePembayaran === "Manual" && (
                <Form.Item
                  label="Nama Rekening"
                  name="nama_rekening_bank"
                  rules={[
                    {
                      required: true,
                      message: "Silakan masukkan Nama Rekening!",
                    },
                  ]}
                >
                  <Input placeholder="Masukkan Nama Rekening" />
                </Form.Item>
              )}
            </div>
            <div className="col-md-6">
              <Form.Item
                label="Nama Pembayaran"
                name="nama_bank"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Nama Pembayaran!",
                  },
                ]}
              >
                <Input placeholder="Masukkan Nama Pembayaran" />
              </Form.Item>
              {typePembayaran === "Otomatis" && (
                <Form.Item
                  label="Channel Pembayaran"
                  name="payment_channel"
                  rules={[
                    {
                      required: true,
                      message: "Silakan pilih Channel Pembayaran!",
                    },
                  ]}
                >
                  <Select
                    allowClear
                    className="w-full"
                    placeholder="Pilih Channel Pembayaran"
                    onChange={(e) => setPaymentChannel(e)}
                  >
                    <Select.Option value="bank_transfer">
                      Bank Transfer
                    </Select.Option>
                    <Select.Option value="echannel">Echannel</Select.Option>
                    <Select.Option value="bca_klikpay">
                      BCA Klikpay
                    </Select.Option>
                    <Select.Option value="bca_klikbca">
                      BCA Klikbca
                    </Select.Option>
                    <Select.Option value="bri_epay">BRI Epay</Select.Option>
                    <Select.Option value="gopay">Gopay</Select.Option>
                    <Select.Option value="shopeepay">Shopeepay</Select.Option>
                    <Select.Option value="qris">QRIS</Select.Option>
                    <Select.Option value="mandiri_clickpay">
                      Mandiri Clickpay
                    </Select.Option>
                    <Select.Option value="cimb_clicks">
                      CIMB Clicks
                    </Select.Option>
                    <Select.Option value="danamon_online">
                      Danamon Online
                    </Select.Option>
                    <Select.Option value="cstore">Cstore</Select.Option>
                    <Select.Option value="cod_jne">COD JNE</Select.Option>
                    <Select.Option value="cod_jxe">COD JXE</Select.Option>
                  </Select>
                </Form.Item>
              )}

              <Form.Item
                label="Status"
                name="status"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Status!",
                  },
                ]}
              >
                <Select
                  allowClear
                  className="w-full"
                  placeholder="Pilih Status"
                >
                  <Select.Option key={"1"} value={"1"}>
                    Active
                  </Select.Option>
                  <Select.Option key={"0"} value={"0"}>
                    Non Active
                  </Select.Option>
                </Select>
              </Form.Item>

              {typePembayaran === "Manual" && (
                <Form.Item
                  label="Nomor Rekening"
                  name="nomor_rekening_bank"
                  rules={[
                    {
                      required: true,
                      message: "Silakan masukkan Nomor Rekening!",
                    },
                  ]}
                >
                  <Input placeholder="Masukkan Nomor Rekening" />
                </Form.Item>
              )}
            </div>

            {parentId > 0 && (
              <div className="col-md-2">
                <Form.Item
                  label="Logo Bank"
                  name="logo_bank"
                  rules={[
                    {
                      required: payment_method_id ? false : true,
                      message: "Silakan pilih Logo Bank!",
                    },
                  ]}
                >
                  <Upload
                    name="logo_bank"
                    listType="picture-card"
                    className="avatar-uploader"
                    showUploadList={false}
                    multiple={false}
                    beforeUpload={() => false}
                    onChange={handleChange}
                    accept="image/*" // Accepts all image types
                  >
                    {imageUrl ? (
                      imageLoading ? (
                        <LoadingOutlined />
                      ) : (
                        <img
                          src={imageUrl}
                          alt="avatar"
                          className="max-h-[100px] h-28 w-28 aspect-square"
                        />
                      )
                    ) : (
                      uploadButton
                    )}
                  </Upload>
                </Form.Item>
              </div>
            )}
          </div>
        </Card>
      </Form>

      {/* <div className="card ">
        <div className="card-body flex justify-end">
          <button
            onClick={() => form.submit()}
            className="text-white bg-blueColor hover:bg-blueColor/90 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
          >
            {loadingSubmit ? <LoadingOutlined /> : <CheckOutlined />}
            <span className="ml-2">Simpan</span>
          </button>
        </div>
      </div> */}
    </Layout>
  )
}

const getPaymentCode = (channel = null, type = "Otomatis") => {
  if (type === "Otomatis") {
    switch (channel) {
      case "bank_transfer":
        return [
          {
            name: "BNI",
            value: "bni",
          },
          {
            name: "BRI",
            value: "bri",
          },
          {
            name: "BCA",
            value: "bca",
          },
        ]

      case "echannel":
        return [
          {
            name: "Mandiri",
            value: "mandiri",
          },
        ]
      case "cstore":
        return [
          {
            name: "Alfamart",
            value: "alfamart",
          },
          {
            name: "Indomart",
            value: "indomart",
          },
        ]

      default:
        return []
    }
  }

  return []
}

export default FormPaymentMethod
