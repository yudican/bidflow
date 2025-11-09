import { CloseOutlined, LoadingOutlined, PlusOutlined } from "@ant-design/icons"
import { Card, Form, Input, Select, Switch, Table, Upload, message } from "antd"
import React, { useEffect, useState } from "react"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../../components/layout"
import {
  formatPhone,
  getBase64,
  validateEmail,
  validatePhoneNumber,
} from "../../../helpers"
import CustomerList from "./Components/CustomerList"

const { TextArea } = Input

const FormBrand = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const { brand_id } = useParams()

  const costomerSupports = {
    key: 0,
    id: null,
    value: null,
    type: null,
    status: false,
  }
  const [listCustomerSuport, setListCustomerSuport] = useState([
    costomerSupports,
  ])

  const [dataBrand, setDataBrand] = useState(null)
  const [imageLoading, setImageLoading] = useState(false)
  const [imageUrl, setImageUrl] = useState(false)
  const [fileList, setFileList] = useState(false)

  const [provinsi, setProvinsi] = useState([])
  const [kabupaten, setKabupaten] = useState([])
  const [kecamatan, setKecamatan] = useState([])
  const [kelurahan, setKelurahan] = useState([])

  // loading
  const [loadingProvinsi, setLoadingProvinsi] = useState(false)
  const [loadingKabupaten, setLoadingKabupaten] = useState(false)
  const [loadingKecamatan, setLoadingKecamatan] = useState(false)
  const [loadingKelurahan, setLoadingKelurahan] = useState(false)

  const loadDetailBrand = () => {
    axios.get(`/api/master/brand/${brand_id}`).then((res) => {
      const { data } = res.data
      const dataCs =
        data.brand_customer_support &&
        data.brand_customer_support.map((cs, index) => {
          return {
            ...cs,
            value: cs.type === "email" ? cs.value : formatPhone(cs.value),
            status: cs.status === "1" ? true : false,
            key: index,
          }
        })
      if (dataCs.length > 0) {
        form.setFieldsValue({ items: dataCs })
        setListCustomerSuport(dataCs)
      }
      setImageUrl(data.logo_url)
      setDataBrand(data)
      form.setFieldsValue({
        ...data,
        phone: formatPhone(data?.phone),
      })
    })
  }

  const loadProvinsi = () => {
    setLoadingProvinsi(true)
    axios
      .get("/api/master/provinsi")
      .then((res) => {
        setProvinsi(res.data.data)
        setLoadingProvinsi(false)
      })
      .catch((err) => setLoadingProvinsi(false))
  }
  const loadKabupaten = (provinsi_id) => {
    setLoadingKabupaten(true)
    axios
      .get("/api/master/kabupaten/" + provinsi_id)
      .then((res) => {
        setKabupaten(res.data.data)
        setLoadingKabupaten(false)
      })
      .catch((err) => setLoadingKabupaten(false))
  }
  const loadKecamatan = (kabupaten_id) => {
    setLoadingKecamatan(true)
    axios
      .get("/api/master/kecamatan/" + kabupaten_id)
      .then((res) => {
        setKecamatan(res.data.data)
        setLoadingKecamatan(false)
      })
      .catch((err) => setLoadingKecamatan(false))
  }
  const loadKelurahan = (kelurahan_id) => {
    setLoadingKelurahan(true)
    axios
      .get("/api/master/kelurahan/" + kelurahan_id)
      .then((res) => {
        setKelurahan(res.data.data)
        setLoadingKelurahan(false)
      })
      .catch((err) => setLoadingKelurahan(false))
  }

  useEffect(() => {
    loadDetailBrand()
  }, [])

  useEffect(() => {
    loadProvinsi()
    if (dataBrand?.provinsi_id) {
      loadKabupaten(dataBrand?.provinsi_id)
    }
    if (dataBrand?.kabupaten_id) {
      loadKecamatan(dataBrand?.kabupaten_id)
    }
    if (dataBrand?.kecamatan_id) {
      loadKelurahan(dataBrand?.kecamatan_id)
    }
  }, [dataBrand?.provinsi_id, dataBrand?.kabupaten_id, dataBrand?.kecamatan_id])

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

  const isValidEmail = (email) => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return emailRegex.test(email)
  }

  const isValidPhone = (phone) => {
    const phoneRegex = /^[0-9]{10,15}$/ // Sesuaikan dengan format nomor telepon yang diinginkan
    return phoneRegex.test(phone)
  }

  const handleChangeProductItem = ({ dataIndex, value, key }) => {
    const records = [...listCustomerSuport]
    console.log(dataIndex, value, key)
    const type = records[key]["type"]
    let isValid = false

    if (type === "email") {
      isValid = isValidEmail(value)
    } else if (type === "phone") {
      isValid = isValidPhone(value)
    }

    if (isValid) {
      records[key][dataIndex] = value
      setListCustomerSuport(records)
    } else {
      return toast.error(`${type} tidak valid`, {
        position: toast.POSITION.TOP_RIGHT,
      })
    }
  }

  const handleClickProductItem = ({ type, key }) => {
    const records = [...listCustomerSuport]
    if (type === "add") {
      const lastData = records[records.length - 1]
      records.push({
        key: lastData.key + 1,
        id: null,
        value: null,
        type: null,
        status: null,
      })

      return setListCustomerSuport(records)
    }

    if (type === "delete") {
      records.splice(key, 1)
      return setListCustomerSuport(
        records.map((item, index) => {
          return {
            ...item,
            key: index + 1,
          }
        })
      )
    }
  }

  const handleAddProductItems = (item) => {
    const items = [...listCustomerSuport]
    const lastKey = items[items.length - 1].key
    items.push({ ...item, key: lastKey + 1 })

    setListCustomerSuport(items)
  }

  const handleRemoveProductItems = (key) => {
    const items = [...listCustomerSuport]
    const newItems = items
      .filter((val, index) => index !== key)
      .map((item, index) => ({ ...item, key: index }))

    newItems.forEach((value, keys) => {
      form.setFieldValue(["items", keys, "type"], value.type)
      form.setFieldValue(
        ["items", keys, "value"],
        value.type === "email" ? value.value : formatPhone(value.value)
      )
      form.setFieldValue(["items", keys, "status"], value.status)
    })

    setListCustomerSuport(newItems)
  }

  const handleChangeItems = (name, value, key, isEmail = false) => {
    const items = [...listCustomerSuport]
    const item = items.find((val) => val.key === key)
    if (name === "type") {
      if (isEmail) {
        item[name] = isEmail ? value : formatPhone(value)
      } else {
        item[name] = value
      }
    } else {
      item[name] = value
    }
    setListCustomerSuport(items)
  }

  const onFinish = (values) => {
    const csList = listCustomerSuport.every((item) => {
      if (item.value === null || item.type === null) {
        return false
      }
      return true
    })

    if (!csList) {
      return toast.error("Customer support tidak boleh kosong")
    }

    let formData = new FormData()
    if (fileList) {
      formData.append("logo", fileList)
    }

    formData.append("code", values.code)
    formData.append("phone", values.phone)
    formData.append("name", values.name)
    formData.append("pt_name", values.pt_name || "")
    formData.append("email", values.email)
    formData.append("twitter", values.twitter || "")
    formData.append("instagram", values.instagram || "")
    formData.append("facebook", values.facebook || "")
    formData.append("address", values.address)
    formData.append("provinsi_id", values.provinsi_id)
    formData.append("status", values.status)
    formData.append("kabupaten_id", values.kabupaten_id)
    formData.append("kecamatan_id", values.kecamatan_id)
    formData.append("kelurahan_id", values.kelurahan_id)
    formData.append("kodepos", values.kodepos)
    formData.append("description", values.description)
    formData.append("customerlist", JSON.stringify(listCustomerSuport))

    const url = brand_id
      ? `/api/master/brand/save/${brand_id}`
      : "/api/master/brand/save"
    console.log(formData, "formData")
    axios
      .post(url, formData)
      .then((res) => {
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        return navigate("/master/brand")
      })
      .catch((err) => {
        const { message } = err.response.data
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
        <span className="ml-2">Simpan</span>
      </button>
    </div>
  )

  return (
    <Layout
      title="Brand"
      href="/master/brand"
      // rightContent={rightContent}
    >
      <Form
        form={form}
        name="basic"
        layout="vertical"
        onFinish={onFinish}
        //   onFinishFailed={onFinishFailed}
        autoComplete="off"
      >
        <Card title="Brand Data">
          <div className="card-body row">
            <div className="col-md-4">
              <Form.Item
                label="Kode Brand"
                name="code"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Kode Brand!",
                  },
                ]}
              >
                <Input placeholder="Ketik Kode Brand" />
              </Form.Item>
            </div>
            <div className="col-md-4">
              <Form.Item
                label="Nama Brand"
                name="name"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Nama Brand!",
                  },
                ]}
              >
                <Input placeholder="Ketik Nama Brand" />
              </Form.Item>
            </div>
            <div className="col-md-4">
              <Form.Item
                label="Nama PT"
                name="pt_name"
                rules={[
                  {
                    required: false,
                    message: "Silakan masukkan Nama PT!",
                  },
                ]}
              >
                <Input placeholder="Ketik Nama PT" />
              </Form.Item>
            </div>

            <div className="col-md-6">
              <Form.Item
                label="Telepon"
                name="phone"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Telepon!",
                  },
                  {
                    validator: validatePhoneNumber,
                  },
                ]}
              >
                <Input placeholder="Ketik No Telepon" />
              </Form.Item>
            </div>
            <div className="col-md-6">
              <Form.Item
                label="Email"
                name="email"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan email!",
                  },
                  {
                    validator: validateEmail,
                  },
                ]}
              >
                <Input placeholder="Ketik Email" />
              </Form.Item>
            </div>

            <div className="col-md-4">
              <Form.Item
                label="Link Twitter"
                name="twitter"
                rules={[
                  {
                    message: "Silakan masukkan Link Twitter!",
                  },
                ]}
              >
                <Input placeholder="Ketik Link Twitter" />
              </Form.Item>
            </div>
            <div className="col-md-4">
              <Form.Item
                label="Link Instagram"
                name="instagram"
                rules={[
                  {
                    message: "Silakan masukkan Link Instagram!",
                  },
                ]}
              >
                <Input placeholder="Ketik Link Instagram" />
              </Form.Item>
            </div>
            <div className="col-md-4">
              <Form.Item
                label="Link Facebook"
                name="facebook"
                rules={[
                  {
                    message: "Silakan masukkan Link Facebook!",
                  },
                ]}
              >
                <Input placeholder="Ketik Link Facebook" />
              </Form.Item>
            </div>
            <div className="col-md-6">
              <Form.Item
                label="Alamat Brand"
                name="address"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Alamat Brand!",
                  },
                ]}
              >
                <Input placeholder="Ketik Alamat Brand" />
              </Form.Item>
              <Form.Item
                label="Provinsi"
                name="provinsi_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Provinsi!",
                  },
                ]}
              >
                <Select
                  loading={loadingProvinsi}
                  showSearch
                  optionFilterProp="children"
                  filterOption={(input, option) =>
                    (option?.children?.toLowerCase() ?? "").includes(
                      input.toLowerCase()
                    )
                  }
                  allowClear
                  className="w-full mb-2"
                  placeholder="Pilih Provinsi"
                  onChange={(value) => {
                    loadKabupaten(value)
                    setKabupaten([])
                    setKecamatan([])
                    setKelurahan([])
                    form.setFieldsValue({
                      kabupaten_id: null,
                      kecamatan_id: null,
                      kelurahan_id: null,
                      kodepos: null,
                    })
                  }}
                >
                  {provinsi.map((item) => (
                    <Select.Option key={item.pid} value={item.pid}>
                      {item.nama}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>
            </div>
            <div className="col-md-6">
              <Form.Item
                label="Status"
                name="status"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Status!",
                  },
                ]}
              >
                <Select placeholder="Pilih Status">
                  <Select.Option value="1">Active</Select.Option>
                  <Select.Option value="0">Non Active</Select.Option>
                </Select>
              </Form.Item>

              <Form.Item
                label="Kabupaten"
                name="kabupaten_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Kabupaten!",
                  },
                ]}
              >
                <Select
                  showSearch
                  optionFilterProp="children"
                  filterOption={(input, option) =>
                    (option?.children?.toLowerCase() ?? "").includes(
                      input.toLowerCase()
                    )
                  }
                  allowClear
                  className="w-full mb-2"
                  placeholder="Pilih Kabupaten"
                  loading={loadingKabupaten}
                  onChange={(value) => {
                    loadKecamatan(value)
                    setKecamatan([])
                    setKelurahan([])
                    form.setFieldsValue({
                      kecamatan_id: null,
                      kelurahan_id: null,
                      kodepos: null,
                    })
                  }}
                >
                  {kabupaten.map((item) => (
                    <Select.Option key={item.pid} value={item.pid}>
                      {item.nama}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>
            </div>
            <div className="col-md-6">
              <Form.Item
                label="Kecamatan"
                name="kecamatan_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Kecamatan!",
                  },
                ]}
              >
                <Select
                  showSearch
                  optionFilterProp="children"
                  filterOption={(input, option) =>
                    (option?.children?.toLowerCase() ?? "").includes(
                      input.toLowerCase()
                    )
                  }
                  allowClear
                  className="w-full mb-2"
                  placeholder="Pilih Kecamatan"
                  loading={loadingKecamatan}
                  onChange={(value) => {
                    loadKelurahan(value)
                    setKelurahan([])
                    form.setFieldsValue({
                      kelurahan_id: null,
                      kodepos: null,
                    })
                  }}
                >
                  {kecamatan.map((item) => (
                    <Select.Option key={item.pid} value={item.pid}>
                      {item.nama}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>
            </div>
            <div className="col-md-3">
              <Form.Item
                label="Kelurahan"
                name="kelurahan_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Kelurahan!",
                  },
                ]}
              >
                <Select
                  showSearch
                  optionFilterProp="children"
                  filterOption={(input, option) =>
                    (option?.children?.toLowerCase() ?? "").includes(
                      input.toLowerCase()
                    )
                  }
                  allowClear
                  className="w-full mb-2"
                  placeholder="Pilih Kelurahan"
                  loading={loadingKelurahan}
                  onChange={(value) => {
                    const data = kelurahan.find((item) => item.pid === value)
                    form.setFieldValue("kodepos", data.zip)
                  }}
                >
                  {kelurahan.map((item) => (
                    <Select.Option key={item.pid} value={item.pid}>
                      {item.nama}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>
            </div>
            <div className="col-md-3">
              <Form.Item
                label="Kode Pos"
                name="kodepos"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Kode Pos!",
                  },
                ]}
              >
                <Input placeholder="Ketik Kode Pos" />
              </Form.Item>
            </div>
            <div className="col-md-10">
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
                <TextArea
                  placeholder="Ketik Deskripsi"
                  rows={3}
                  style={{ height: 106 }}
                />
              </Form.Item>
            </div>
            <div className="col-md-2">
              <Form.Item
                label="Brand Logo"
                name="logo"
                rules={[
                  {
                    required: brand_id ? false : true,
                    message: "Silakan masukkan Brand Logo!",
                  },
                ]}
              >
                <Upload
                  name="logo"
                  listType="picture-card"
                  className="avatar-uploader"
                  showUploadList={false}
                  multiple={false}
                  beforeUpload={() => false}
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
        <Card title="Customer Support" className="mt-4">
          <Table
            dataSource={listCustomerSuport}
            columns={[
              {
                title: "Type",
                dataIndex: "type",
                key: "type",
                width: "30%",
                render: (value, item, index) => {
                  return (
                    <Form.Item
                      name={["items", index, "type"]}
                      rules={[
                        { required: true, message: "Silakan masukkan type" },
                      ]}
                    >
                      <Select
                        placeholder="Type.."
                        onChange={(value) =>
                          handleChangeItems("type", value, item.key)
                        }
                      >
                        <Select.Option value={"email"}>Email</Select.Option>
                        <Select.Option value={"telepon"}>Telepon</Select.Option>
                        <Select.Option value={"whatsapp"}>
                          Whatsapp
                        </Select.Option>
                      </Select>
                    </Form.Item>
                  )
                },
              },
              {
                title: "Value",
                dataIndex: "value",
                key: "value",
                width: "40%",
                render: (value, item, index) => {
                  const isEmail = item.type == "email"
                  const placeholder = isEmail ? "jhon@gmail.com" : "0822xxxxxxx"
                  return (
                    <Form.Item
                      name={["items", index, "value"]}
                      rules={[
                        {
                          required: true,
                          message: item.type
                            ? "Silakan masukkan " + item.type
                            : "Silahkan Pilih Type Terlebih Dahulu",
                        },
                        {
                          validator: isEmail
                            ? validateEmail
                            : validatePhoneNumber,
                        },
                      ]}
                    >
                      <Input
                        placeholder={placeholder}
                        value={isEmail ? item.value : formatPhone(item.value)}
                        onChange={(e) =>
                          handleChangeItems(
                            "value",
                            e.target.value,
                            item.key,
                            isEmail
                          )
                        }
                      />
                    </Form.Item>
                  )
                },
              },
              {
                title: "Status",
                dataIndex: "status",
                key: "status",
                width: "20%",
                render: (value, item, index) => {
                  return (
                    <Form.Item
                      name={["items", index, "status"]}
                      rules={[
                        { required: true, message: "Silakan masukkan Status" },
                      ]}
                    >
                      <Switch
                        checked={item.status}
                        onChange={(e) =>
                          handleChangeItems("status", e, item.key)
                        }
                      />
                    </Form.Item>
                  )
                },
              },

              {
                title: "",
                dataIndex: "action",
                key: "action",
                width: "10%",
                render: (value, item, index) => {
                  return (
                    <button
                      disabled={listCustomerSuport?.length < 2}
                      onClick={() => handleRemoveProductItems(index)}
                      type={"button"}
                      className={`
                    text-white text-sm font-medium text-center 
                      ${
                        listCustomerSuport?.length < 2
                          ? "bg-gray-700 hover:bg-gray-800"
                          : "bg-red-700 hover:bg-red-800"
                      }
                      focus:ring-4 focus:outline-none focus:ring-red-300 
                      px-4 py-2 rounded-lg 
                      inline-flex items-center mb-3
                    `}
                    >
                      <CloseOutlined />
                    </button>
                  )
                },
              },
            ]}
            pagination={false}
          />
          <div
            onClick={() => {
              const lastData = listCustomerSuport[listCustomerSuport.length - 1]
              handleAddProductItems({
                key: lastData.key + 1,
                type: null,
                value: null,
                status: false,
              })
            }}
            className="
              w-full mt-4 cursor-pointer
              text-blue-600 hover:text-blue-800
              bg-blue-500/20 border-2 border-blue-700/70 hover:border-blue-800 border-dashed  focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 inline-flex items-center justify-center"
          >
            <PlusOutlined style={{ marginRight: 10 }} />
            <strong>Add More</strong>
          </div>
        </Card>
      </Form>

      <div className="float-right mt-6">
        <button
          onClick={() => form.submit()}
          className="text-white bg-blueColor hover:bg-blueColor/90 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
        >
          <span className="ml-2">Simpan</span>
        </button>
      </div>
    </Layout>
  )
}

export default FormBrand
