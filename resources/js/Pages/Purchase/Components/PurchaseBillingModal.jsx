import {
  CloseCircleOutlined,
  LoadingOutlined,
  PlusOutlined,
} from "@ant-design/icons"
import { Form, Input, Modal, Select, Upload, message } from "antd"
import { useForm } from "antd/es/form/Form"
import axios from "axios"
import React, { useState } from "react"
import { toast } from "react-toastify"
import { getBase64, getItem, formatNumber } from "../../../helpers"

const PurchaseBillingModal = ({
  refetch,
  detail,
  handleFinish,
  receivedNumbers = [],
  type = "product",
  onChangePoNumber,
  checkBooks = [],
}) => {
  const [form] = useForm()
  const { name } = getItem("user_data", true) || {}
  const [loadingSubmit, setLoadingSubmit] = useState(false)
  const [loading, setLoading] = useState(false)
  const [imageUrl, setImageUrl] = useState(null)
  const [fileList, setFileList] = useState(null)
  const [isModalOpen, setIsModalOpen] = useState(false)

  const showModal = () => {
    setIsModalOpen(true)

    const totalJumlahTransfer = detail?.billings.reduce(
      (acc, billing) => acc + (parseInt(billing.jumlah_transfer) || 0),
      0
    )

    const tax = type === "product" ? detail?.amount_to_pay * 0.11 : 0
    console.log(type)
    if (type === "product") {
      const amount_payment = detail?.amount_to_pay
        ? detail?.amount_to_pay + tax - totalJumlahTransfer
        : 0
      const nominal_bayar = detail?.amount_to_pay
        ? formatNumber(detail?.amount_to_pay + tax - totalJumlahTransfer)
        : "-"
      form.setFieldsValue({
        upload_by: name,
        jumlah_transfer: amount_payment,
        tax_amount: detail?.amount_to_pay ? tax : 0,
        nominal_invoice: nominal_bayar,
      })
    } else {
      console.log(detail?.items)
      // const total = detail?.items.map(item => item.total);

      detail?.items.map((item) =>
        form.setFieldsValue({
          upload_by: name,
          jumlah_transfer: item.total,
          tax_amount: detail?.amount_to_pay ? tax : 0,
          nominal_invoice: formatNumber(item.total) || 0,
        })
      )

      // Now you can use itemNames as needed.
      // console.log(total);
    }
  }

  const handleCancel = () => {
    setIsModalOpen(false)
  }

  const handleChange = ({ fileList, field }) => {
    const list = fileList.pop()
    setLoading(true)
    setTimeout(() => {
      const size = list.size / 1024
      if (size > 1024) {
        setLoading(false)
        return message.error("Maksimum ukuran file adalah 1 MB")
      }
      getBase64(list.originFileObj, (url) => {
        setLoading(false)
        setImageUrl(url)
      })
      setFileList(list.originFileObj)
    }, 1000)
  }

  const onFinish = (value) => {
    if (handleFinish) {
      setIsModalOpen(false)
      return handleFinish({ ...value, bukti_transfer: fileList })
    }
    setLoadingSubmit(true)
    let formData = new FormData()

    if (fileList) {
      formData.append("bukti_transfer", fileList)
    }

    formData.append("purchase_order_id", detail.id)
    formData.append("nama_bank", value.nama_bank)
    formData.append("nama_pengirim", value.nama_pengirim)
    formData.append("no_rekening", value.no_rekening)
    formData.append("jumlah_transfer", value.jumlah_transfer)
    formData.append("tax_amount", value.tax_amount)
    formData.append("sumberdana", value.sumberdana)
    formData.append("no_rekening_sumberdana", value.no_rekening_sumberdana)

    axios
      .post(`/api/purchase/purchase-order/billing/save/${detail.id}`, formData)
      .then((res) => {
        const { message } = res.data
        refetch()
        setFileList(null)
        setImageUrl(null)
        toast.success(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        setIsModalOpen(false)
        setLoadingSubmit(false)
      })
      .catch((e) => setLoadingSubmit(false))
  }
  return (
    <div>
      <button
        type="button"
        onClick={() => showModal()}
        className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
      >
        <PlusOutlined />
        <span className="ml-2">Tambah Data</span>
      </button>

      <Modal
        title="Informasi Pembayaran"
        open={isModalOpen}
        onOk={() => {
          form.submit()
        }}
        cancelText={"Cancel"}
        onCancel={handleCancel}
        okText={"Simpan"}
        confirmLoading={loadingSubmit}
        bodyStyle={{ height: "32rem", overflowY: "scroll" }}
      >
        <Form
          form={form}
          name="basic"
          layout="vertical"
          onFinish={onFinish}
          autoComplete="off"
        >
          <div className="row">
            {type === "product" && (
              <div className="col-md-12">
                {receivedNumbers && receivedNumbers.length > 0 && (
                  <Form.Item
                    label="Received Number"
                    name="received_number"
                    rules={[
                      {
                        required: true,
                        message: "Silakan masukkan Received Number!",
                      },
                    ]}
                  >
                    <Select
                      placeholder="Silakan pilih"
                      onChange={(e) => {
                        const item = receivedNumbers.find(
                          (row) => row.received_number == e
                        )
                        onChangePoNumber(item.purchase_order_id)
                        form.setFieldValue(
                          "jumlah_transfer",
                          item?.extended_cost
                        )
                      }}
                    >
                      {receivedNumbers.map((item) => (
                        <Select.Option
                          key={item.received_number}
                          value={item.received_number}
                        >
                          {item.received_number}
                        </Select.Option>
                      ))}
                    </Select>
                  </Form.Item>
                )}
              </div>
            )}

            <div className="col-md-12">
              <Form.Item
                label="Checkbook"
                name="checkbook"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Checkbook!",
                  },
                ]}
              >
                <Select
                  placeholder="Silakan pilih"
                  onChange={(e) => {
                    const checkbook = checkBooks.find(
                      (item) => item.bank_name === e
                    )
                    if (checkbook) {
                      form.setFieldsValue({
                        nama_bank: checkbook.bank_name,
                        no_rekening: checkbook.bank_account,
                      })
                    }
                  }}
                >
                  {checkBooks.map((item) => (
                    <Select.Option key={item.bank_name} value={item.bank_name}>
                      {item.description}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>
            </div>

            <div className="col-md-6">
              <Form.Item
                label={"Nama Bank"}
                name={"nama_bank"}
                rules={[
                  {
                    required: true,
                    message: "Field Tidak Boleh Kosong!",
                  },
                ]}
              >
                <Input readOnly />
              </Form.Item>
              <Form.Item
                label={"Nama Penerima"}
                name={"nama_pengirim"}
                rules={[
                  {
                    required: false,
                    message: "Field Tidak Boleh Kosong!",
                  },
                ]}
              >
                <Input />
              </Form.Item>
              <Form.Item
                label={"Nominal Invoice"}
                name={"nominal_invoice"}
                rules={[
                  {
                    required: true,
                    message: "Field Tidak Boleh Kosong!",
                  },
                ]}
              >
                <Input disabled />
              </Form.Item>
              <Form.Item label={"Upload by"} name={"upload_by"}>
                <Input disabled />
              </Form.Item>
            </div>
            <div className="col-md-6">
              <Form.Item
                label={"No. Rekening"}
                name={"no_rekening"}
                rules={[
                  {
                    required: true,
                    message: "Field Tidak Boleh Kosong!",
                  },
                ]}
              >
                <Input readOnly />
              </Form.Item>
              <Form.Item
                label={"No. Rekening Tujuan"}
                name={"no_rekening_sumberdana"}
                rules={[
                  {
                    required: false,
                    message: "Field Tidak Boleh Kosong!",
                  },
                ]}
              >
                <Input />
              </Form.Item>
              {/* <Form.Item
                label={type === "product" ? "Tax (Rp.)" : "Other Expense"}
                name={"tax_amount"}
              >
                <Input
                  suffix={
                    <CloseCircleOutlined
                      onClick={() => form.setFieldValue("tax_amount", 0)}
                    />
                  }
                />
              </Form.Item> */}
              <Form.Item
                label={"Jumlah Transfer (Rp.)"}
                name={"jumlah_transfer"}
                rules={[
                  {
                    required: true,
                    message: "Field Tidak Boleh Kosong!",
                  },
                ]}
              >
                <Input
                  suffix={
                    <CloseCircleOutlined
                      onClick={() =>
                        form.setFieldValue(
                          "jumlah_transfer",
                          detail?.amount_to_pay
                        )
                      }
                    />
                  }
                />
              </Form.Item>
            </div>

            <div className="col-md-12">
              <Form.Item
                label="Bukti Transfer"
                name="bukti_transfer"
                rules={[
                  {
                    required: false,
                    message: "Silakan masukkan Bukti Transfer!",
                  },
                ]}
              >
                <Upload
                  name="bukti_transfer"
                  listType="picture-card"
                  className="avatar-uploader w-100"
                  showUploadList={false}
                  multiple={false}
                  beforeUpload={() => false}
                  onChange={(e) =>
                    handleChange({
                      ...e,
                      field: "bukti_transfer",
                    })
                  }
                >
                  {imageUrl ? (
                    loading ? (
                      <LoadingOutlined />
                    ) : (
                      <img
                        src={imageUrl}
                        alt="avatar"
                        style={{
                          height: 104,
                        }}
                      />
                    )
                  ) : (
                    <div style={{ width: "100%" }}>
                      {loading ? <LoadingOutlined /> : <PlusOutlined />}
                      <div
                        style={{
                          marginTop: 8,
                          width: "100%",
                        }}
                      >
                        Upload
                      </div>
                    </div>
                  )}
                </Upload>
              </Form.Item>
            </div>
          </div>
        </Form>
      </Modal>
    </div>
  )
}

export default PurchaseBillingModal
