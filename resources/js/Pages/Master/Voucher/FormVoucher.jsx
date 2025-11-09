import {
  CheckOutlined,
  InfoCircleFilled,
  InfoCircleOutlined,
  LoadingOutlined,
  PlusOutlined,
} from "@ant-design/icons"
import {
  Card,
  DatePicker,
  Form,
  Input,
  Select,
  Tooltip,
  Upload,
  message,
} from "antd"
import React, { useEffect, useState } from "react"
import "react-draft-wysiwyg/dist/react-draft-wysiwyg.css"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../../components/layout"
import RichtextEditor from "../../../components/RichtextEditor"
import { getBase64, inArray } from "../../../helpers"
import "../../../index.css"
import moment from "moment"

const FormVoucher = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const { voucher_id } = useParams()

  const [dataBrand, setDataBrand] = useState([])
  const [imageLoading, setImageLoading] = useState(false)
  const [imageUrl, setImageUrl] = useState(false)
  const [fileList, setFileList] = useState(false)

  const [loadingBrand, setLoadingBrand] = useState(false)
  const [loadingSubmit, setLoadingSubmit] = useState(false)

  const [typeVoucher, setTypeVoucher] = useState("general")

  const loadDetailBrand = () => {
    axios.get(`/api/master/voucher/${voucher_id}`).then((res) => {
      const { data } = res.data
      console.log("------")
      console.log(data)
      setImageUrl(data.voucher_image)
      setTypeVoucher(data.type)
      const newData = {
        ...data,
        start_date: moment(data.start_date || new Date(), "YYYY-MM-DD"),
        end_date: moment(data.end_date || new Date(), "YYYY-MM-DD"),
      }
      console.log("------")
      console.log(newData)
      form.setFieldsValue(newData)
    })
  }

  const loadBrand = () => {
    setLoadingBrand(true)
    axios
      .get("/api/master/brand")
      .then((res) => {
        setDataBrand(res.data.data)
        setLoadingBrand(false)
      })
      .catch((err) => setLoadingBrand(false))
  }

  useEffect(() => {
    loadBrand()
    loadDetailBrand()
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
      formData.append("image", fileList)
    }

    formData.append("voucher_code", values.voucher_code)
    formData.append("title", values.title)
    formData.append("description", values.description)
    formData.append("nominal", values.nominal)
    formData.append("percentage", values?.percentage || 0)
    formData.append("total", values.total)
    formData.append("start_date", values.start_date.format("YYYY-MM-DD"))
    formData.append("end_date", values.end_date.format("YYYY-MM-DD"))
    formData.append("min", values.min)
    formData.append("type", values.type)
    formData.append("total_point", values.total_point)
    formData.append("usage_for", values.usage_for)
    formData.append("brand_id", JSON.stringify(values.brand_ids))
    formData.append("status", values.status)

    const url = voucher_id
      ? `/api/master/voucher/save/${voucher_id}`
      : "/api/master/voucher/save"

    return axios
      .post(url, formData)
      .then((res) => {
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        setLoadingSubmit(false)
        return navigate("/master/voucher")
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

  return (
    <Layout
      title="Form Voucher"
      href="/master/voucher"
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
        <Card title="Voucher Data">
          <div className="card-body row">
            <div className="col-md-6">
              <Form.Item
                label="Tipe Voucher"
                name="type"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Tipe Voucher!",
                  },
                ]}
              >
                <Select
                  allowClear
                  className="w-full"
                  placeholder="Pilih Tipe Voucher"
                  onChange={(value) => setTypeVoucher(value)}
                >
                  <Select.Option key={"0"} value={"general"}>
                    General
                  </Select.Option>
                  <Select.Option key={"1"} value={"point"}>
                    Point
                  </Select.Option>
                  <Select.Option key={"2"} value={"referral"}>
                    Referral
                  </Select.Option>
                  <Select.Option key={"3"} value={"lms"}>
                    LMS
                  </Select.Option>
                </Select>
              </Form.Item>

              <Form.Item
                label="Kode Voucher"
                name="voucher_code"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Kode Voucher!",
                  },
                ]}
              >
                <Input placeholder="Masukkan Kode Voucher" />
              </Form.Item>
              <Form.Item
                label="Nominal"
                name="nominal"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Nominal!",
                  },
                  {
                    pattern: /^[0-9]+$/,
                    message: "Nominal harus berupa angka",
                  },
                  {
                    validator: (_, value) =>
                      value && value < 1
                        ? Promise.reject(
                            new Error("Nominal tidak boleh kurang dari 1")
                          )
                        : Promise.resolve(),
                  },
                ]}
              >
                <Input placeholder="Masukkan Nominal" type="number" />
              </Form.Item>
              <Form.Item
                label="Start Date"
                name="start_date"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Start Date!",
                  },
                ]}
              >
                <DatePicker
                  className="w-full"
                  format="DD-MM-YYYY"
                  placeholder="Pilih Start Date"
                />
              </Form.Item>
              {!inArray(typeVoucher, ["point"]) && (
                <Form.Item
                  label="Persentase"
                  name="percentage"
                  rules={[
                    {
                      required: true,
                      message: "Silakan masukkan Persentase!",
                    },
                    {
                      pattern: /^[0-9]+$/,
                      message: "Persentase harus berupa angka",
                    },
                    {
                      validator: (_, value) =>
                        value && value < 1
                          ? Promise.reject(
                              new Error("Persentase tidak boleh kurang dari 1")
                            )
                          : Promise.resolve(),
                    },
                  ]}
                >
                  <Input
                    placeholder="Masukkan Persentase 1-100 %"
                    type="number"
                  />
                </Form.Item>
              )}

              <Form.Item
                label={
                  <span>
                    Jumlah Voucher{" "}
                    <Tooltip title="Jumlah Voucher merupakan jumlah voucher yang dapat di klaim oleh pengguna FlimApp">
                      <InfoCircleOutlined />
                    </Tooltip>
                  </span>
                }
                name="total"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Jumlah Voucher!",
                  },
                  {
                    pattern: /^[0-9]+$/,
                    message: "Jumlah Voucher harus berupa angka",
                  },
                  {
                    validator: (_, value) =>
                      value && value < 1
                        ? Promise.reject(
                            new Error(
                              "Jumlah Voucher tidak boleh kurang dari 1"
                            )
                          )
                        : Promise.resolve(),
                  },
                ]}
              >
                <Input placeholder="Masukkan Jumlah Voucher" />
              </Form.Item>
              <Form.Item label="Sisa Voucher" name="voucher_limit">
                <Input placeholder="0" disabled />
              </Form.Item>
            </div>
            <div className="col-md-6">
              <Form.Item
                label="Judul"
                name="title"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Judul!",
                  },
                ]}
              >
                <Input placeholder="Masukkan Judul" />
              </Form.Item>

              <Form.Item
                label="Min Transaksi"
                name="min"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Min Transaksi!",
                  },
                  {
                    pattern: /^[0-9]+$/,
                    message: "Min Transaksi harus berupa angka",
                  },
                  {
                    validator: (_, value) =>
                      value && value < 1
                        ? Promise.reject(
                            new Error("Min Transaksi tidak boleh kurang dari 1")
                          )
                        : Promise.resolve(),
                  },
                ]}
              >
                <Input placeholder="Masukkan Min Transaksi" type="number" />
              </Form.Item>

              {typeVoucher === "point" && (
                <Form.Item
                  label="Total Point"
                  name="total_point"
                  rules={[
                    {
                      required: true,
                      message: "Silakan masukkan Total Point!",
                    },
                    {
                      pattern: /^[0-9]+$/,
                      message: "Total point harus berupa angka",
                    },
                    {
                      validator: (_, value) =>
                        value && value < 0
                          ? Promise.reject(
                              new Error("Total point tidak boleh kurang dari 0")
                            )
                          : Promise.resolve(),
                    },
                  ]}
                >
                  <Input placeholder="Masukkan Total Point" />
                </Form.Item>
              )}

              {typeVoucher === "point" && (
                <Form.Item
                  label="End Date"
                  name="end_date"
                  rules={[
                    {
                      required: true,
                      message: "Silakan pilih End Date!",
                    },
                  ]}
                >
                  <DatePicker
                    className="w-full"
                    format="DD-MM-YYYY"
                    placeholder="Pilih End Date"
                  />
                </Form.Item>
              )}

              <Form.Item
                label={
                  <span>
                    Batas Penggunaan{" "}
                    <Tooltip title="Batas Penggunaan berfungsi membatasi penggunaan voucher oleh masing-masing pengguna FlimApp">
                      <InfoCircleOutlined />
                    </Tooltip>
                  </span>
                }
                name="usage_for"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Batas Penggunaan!",
                  },
                  {
                    pattern: /^[0-9]+$/,
                    message: "Batas Penggunaan harus berupa angka",
                  },
                  {
                    validator: (_, value) =>
                      value && value < 1
                        ? Promise.reject(
                            new Error(
                              "Batas Penggunaan tidak boleh kurang dari 1"
                            )
                          )
                        : Promise.resolve(),
                  },
                ]}
              >
                <Input placeholder="Masukkan Batas Penggunaan" />
              </Form.Item>

              {typeVoucher !== "point" && (
                <Form.Item
                  label="End Date"
                  name="end_date"
                  rules={[
                    {
                      required: true,
                      message: "Silakan pilih End Date!",
                    },
                  ]}
                >
                  <DatePicker
                    className="w-full"
                    format="DD-MM-YYYY"
                    placeholder="Pilih End Date"
                  />
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
              <Form.Item
                label="Brand"
                name="brand_ids"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Brand!",
                  },
                ]}
              >
                <Select
                  mode="multiple"
                  allowClear
                  className="w-full mb-2"
                  placeholder="Pilih Brand"
                  loading={loadingBrand}
                >
                  {dataBrand.map((item) => (
                    <Select.Option key={item.id} value={item.id}>
                      {item.name}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>
            </div>

            <div className="col-md-12">
              <Form.Item
                label="Deskripsi"
                name="description"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Deskripsi!",
                  },
                ]}
              >
                <RichtextEditor
                  value={form.getFieldValue("description")}
                  form={form}
                  name={"description"}
                />
              </Form.Item>
            </div>

            <div className="col-md-2">
              <Form.Item
                label={
                  <span>
                    {!voucher_id && <span className="text-red-500">*</span>}
                    Gambar Voucher
                  </span>
                }
                name="image"
                rules={[
                  {
                    required: voucher_id && imageUrl ? false : true,
                    message: "Silakan pilih Gambar Voucher!",
                  },
                ]}
              >
                <Upload
                  name="image"
                  listType="picture-card"
                  className="avatar-uploader"
                  showUploadList={false}
                  multiple={false}
                  beforeUpload={(file) => {
                    const isImage = file.type.startsWith("image/")
                    if (!isImage) {
                      message.error("You can only upload image files!")
                    }
                    return false
                  }}
                  accept="image/*"
                  onChange={handleChange}
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
          </div>
        </Card>

        {/* <div className="float-right mt-6">
          <button className="text-white bg-blueColor hover:bg-blueColor/90 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2">
            {loadingSubmit ? <LoadingOutlined /> : <CheckOutlined />}
            <span className="ml-2">Simpan</span>
          </button>
        </div> */}
      </Form>
    </Layout>
  )
}

export default FormVoucher
