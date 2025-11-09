import {
  CheckOutlined,
  CloseOutlined,
  LoadingOutlined,
  PlusOutlined,
} from "@ant-design/icons"
import { Card, Form, Input, Select, Table } from "antd"
import React, { useEffect, useState } from "react"
import "react-draft-wysiwyg/dist/react-draft-wysiwyg.css"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../../components/layout"
import "../../../index.css"
import WarehouseContact from "./Components/WarehouseContact"
import DebounceSelect from "../../../components/atoms/DebounceSelect"
import { inArray } from "../../../helpers"
import { searchContact } from "./service"

const contactLists = [
  {
    key: 0,
    contact: null,
    status: 0,
  },
]

const WarehouseForm = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const { warehouse_id } = useParams()
  const [detail, setDetail] = useState({})
  const [contacts, setContacts] = useState(contactLists)
  const [contactList, setContactList] = useState([])

  const [provinsi, setProvinsi] = useState([])
  const [kabupaten, setKabupaten] = useState([])
  const [kecamatan, setKecamatan] = useState([])
  const [kelurahan, setKelurahan] = useState([])
  const [site, setSite] = useState([])

  // loading
  const [loadingSubmit, setLoadingSubmit] = useState(false)
  const [loadingProvinsi, setLoadingProvinsi] = useState(false)
  const [loadingKabupaten, setLoadingKabupaten] = useState(false)
  const [loadingKecamatan, setLoadingKecamatan] = useState(false)
  const [loadingKelurahan, setLoadingKelurahan] = useState(false)
  const [loadingSite, setLoadingSite] = useState(false)

  const loadDetailBrand = () => {
    axios.get(`/api/master/warehouse/${warehouse_id}`).then((res) => {
      const { data } = res.data
      setDetail(data)
      form.setFieldsValue(data)

      if (data.users && data.users.length > 0) {
        const users = data.users.map((user, index) => {
          return {
            key: index,
            contact: {
              label: user.name,
              value: user.id,
            },
          }
        })
        form.setFieldsValue({
          items: users,
        })
        setContacts(users)
      }
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

  const loadSite = () => {
    setLoadingSite(true)
    axios
      .get("/api/master/site")
      .then((res) => {
        setSite(res.data.data)
        setLoadingSite(false)
      })
      .catch((err) => setLoadingSite(false))
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
    if (detail?.provinsi_id) {
      loadKabupaten(detail?.provinsi_id)
    }
    if (detail?.kabupaten_id) {
      loadKecamatan(detail?.kabupaten_id)
    }
    if (detail?.kecamatan_id) {
      loadKelurahan(detail?.kecamatan_id)
    }
  }, [detail?.provinsi_id, detail?.kabupaten_id, detail?.kecamatan_id])

  const selectedContact = contacts
    .map((contact) => contact?.contact?.value)
    .filter((item) => item)
  const handleGetContact = () => {
    searchContact(null).then((results) => {
      const newResult = results.map((result) => {
        return {
          label: result.nama,
          value: result.id,
        }
      })
      setContactList(newResult)
    })
  }

  const handleSearchContact = async (e) => {
    return await searchContact(e).then((results) => {
      const newResult = results.map((result) => {
        return {
          label: result.nama,
          value: result.id,
        }
      })

      return newResult
    })
  }

  useEffect(() => {
    handleGetContact()
    loadProvinsi()
    loadDetailBrand()
    loadSite()
  }, [])

  const onFinish = (values) => {
    setLoadingSubmit(true)
    const checkContact = contacts.every((contact) => contact.contact)
    if (!checkContact) {
      toast.error("Mohon lengkapi kontak")
      setLoadingSubmit(false)
      return
    }
    let formData = new FormData()

    formData.append("name", values.name)
    formData.append("wh_id", values.wh_id)
    formData.append("location", values.location)
    formData.append("address", values.address)
    formData.append("status", 1)
    formData.append("telepon", values.telepon)
    formData.append("provinsi_id", values.provinsi_id)
    formData.append("kabupaten_id", values.kabupaten_id)
    formData.append("kecamatan_id", values.kecamatan_id)
    formData.append("kelurahan_id", values.kelurahan_id)
    formData.append("kodepos", values.kodepos)
    const newContacts = contacts.map((contact) => {
      return {
        user_id: contact.contact.value,
        status: contact.status,
      }
    })
    formData.append("contacts", JSON.stringify(newContacts))

    const url = warehouse_id
      ? `/api/master/warehouse/save/${warehouse_id}`
      : "/api/master/warehouse/save"

    axios
      .post(url, formData)
      .then((res) => {
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        setLoadingSubmit(false)
        return navigate("/master/warehouse")
      })
      .catch((err) => {
        const { message } = err.response.data
        setLoadingSubmit(false)
        toast.error(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }

  const handleAddProductItems = (item) => {
    const items = [...contacts]
    const lastKey = items[items.length - 1].key
    items.push({ ...item, key: lastKey + 1 })

    setContacts(items)
  }

  const handleRemoveProductItems = (key) => {
    const items = [...contacts]
    const newItems = items
      .filter((val, index) => index !== key)
      .map((item, index) => ({ ...item, key: index }))

    newItems.forEach((value, keys) => {
      form.setFieldValue(["items", keys, "contact"], value.contact)
    })

    setContacts(newItems)
  }

  const handleChangeItems = (name, value, key) => {
    const items = [...contacts]
    const item = items.find((val) => val.key === key)
    item[name] = value
    setContacts(items)
  }

  return (
    <Layout
      title="Warehouse"
      href="/master/warehouse"
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
        <Card title="Warehouse Data">
          <div className="row">
            <div className="col-md-6">
              <Form.Item
                label="Warehouse ID"
                name="wh_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Warehouse ID!",
                  },
                ]}
              >
                <Select
                  loading={loadingSite}
                  allowClear
                  className="w-full"
                  placeholder="Pilih Warehouse ID"
                >
                  {site.map((item) => (
                    <Select.Option key={item.site_id} value={item.site_id}>
                      {item.site_id}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>
              <Form.Item
                label="Lokasi Warehouse"
                name="location"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Warehouse Location!",
                  },
                ]}
              >
                <Input placeholder="Ketik Warehouse Location " />
              </Form.Item>

              <Form.Item
                label="Kabupaten"
                name="kabupaten_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Kabupaten!",
                  },
                ]}
              >
                <Select
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
              <Form.Item
                label="Kelurahan"
                name="kelurahan_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Kelurahan!",
                  },
                ]}
              >
                <Select
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
            <div className="col-md-6">
              <Form.Item
                label="Warehouse Name"
                name="name"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Warehouse Name!",
                  },
                ]}
              >
                <Input placeholder="Ketik Warehouse Name" />
              </Form.Item>

              <Form.Item
                label="Provinsi"
                name="provinsi_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Provinsi!",
                  },
                ]}
              >
                <Select
                  loading={loadingProvinsi}
                  allowClear
                  className="w-full"
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
              <Form.Item
                label="Kecamatan"
                name="kecamatan_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Kecamatan!",
                  },
                ]}
              >
                <Select
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

              <Form.Item
                label="Nama Jalan"
                name="address"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Nama Jalan!",
                  },
                ]}
              >
                <Input />
              </Form.Item>
            </div>

            <div className="col-md-6">
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
                <Input type="number" />
              </Form.Item>
            </div>
            <div className="col-md-6">
              <Form.Item
                label="Telepon"
                name="telepon"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Telepon!",
                  },
                ]}
              >
                <Input type="number" />
              </Form.Item>
            </div>
          </div>
        </Card>
        <hr className="mt-4" />
        <Card title="Contact" className="mt-4">
          <Table
            dataSource={contacts}
            rowKey={"key"}
            columns={[
              {
                title: "No.",
                dataIndex: "id",
                key: "id",
                width: "2%",
                render: (text, record, index) => {
                  return index + 1
                },
              },
              {
                title: "Contact",
                dataIndex: "contact",
                key: "contact",
                width: "30%",
                render: (value, record, index) => {
                  return (
                    <Form.Item
                      name={["items", index, "contact"]}
                      rules={[
                        { required: true, message: "Contact wajib diisi!" },
                      ]}
                    >
                      <DebounceSelect
                        showSearch
                        placeholder="Cari Contact"
                        fetchOptions={handleSearchContact}
                        filterOption={false}
                        className="w-full"
                        options={[record?.contactUser]}
                        defaultOptions={
                          record.contact
                            ? [record.contact]
                            : contactList.filter(
                                (contact) =>
                                  !inArray(contact?.value, selectedContact)
                              )
                        }
                        value={record?.contact?.value}
                        onChange={(text) =>
                          handleChangeItems("contact", text, record.key)
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
                      disabled={contacts?.length < 2}
                      onClick={() => handleRemoveProductItems(index)}
                      type={"button"}
                      className={`
                    text-white text-sm font-medium text-center 
                      ${
                        contacts?.length < 2
                          ? "bg-gray-700 hover:bg-gray-800"
                          : "bg-red-700 hover:bg-red-800"
                      }
                      focus:ring-4 focus:outline-none focus:ring-red-300 
                      px-4 py-2 rounded-lg 
                      inline-flex items-center mb-6
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
              const lastData = contacts[contacts.length - 1]
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

        <div className="float-right mt-6">
          <button className="text-white bg-blueColor hover:bg-blueColor/90 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2">
            {loadingSubmit ? <LoadingOutlined /> : <CheckOutlined />}
            <span className="ml-2">Simpan</span>
          </button>
        </div>
      </Form>
    </Layout>
  )
}

export default WarehouseForm
