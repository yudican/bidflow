import { CheckOutlined, LoadingOutlined, PlusOutlined } from "@ant-design/icons"
import { Button, Card, DatePicker, Form, Input, Table } from "antd"
import React, { useEffect, useState } from "react"
import "react-draft-wysiwyg/dist/react-draft-wysiwyg.css"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../components/layout"
import { formatDate } from "../../helpers"
import "../../index.css"
import moment from "moment"
import DebounceSelect from "../../components/atoms/DebounceSelect"
import { handleSearchContact, searchContact } from "./service"
import axios from "axios"

const AssetForm = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const { id } = useParams()
  const [contactList, setContactList] = useState([])
  const [loadingSubmit, setLoadingSubmit] = useState(false)
  const [selectedContact, setSelectedContact] = useState(null)
  const [showContact, setShowContact] = useState(false)
  const [loading, setLoading] = useState(false)
  const [detail, setDetail] = useState(null)

  const loadDetail = () => {
    axios.get(`/api/asset-control/${id}`).then((res) => {
      const { data } = res.data

      const newOwner = data.owner
        ? { value: data?.owner, label: data?.owner_user?.name }
        : null

      const newData = {
        ...data,
        generate_date: moment(data.generate_date || new Date(), "YYYY-MM-DD"),
        exp_date: moment(data.exp_date || new Date(), "YYYY-MM-DD"),
        useful_life: moment(data.useful_life || new Date(), "YYYY-MM-DD"),
        owner: newOwner,
        notes: data.notes || "",
        warranty: data.warranty || "",
        receiver_address: data.receiver_address || "",
      }

      setDetail(newData)
      form.setFieldsValue(newData)
    })
  }

  const handleGetContact = async () => {
    await searchContact(null).then((results) => {
      const newResult = results.map((result) => {
        return { label: result.nama, value: result.id }
      })
      setContactList(newResult)
    })
  }

  const fetchData = async () => {
    await handleGetContact()
  }

  useEffect(() => {
    fetchData()
    loadDetail()
  }, [])

  const logs = detail?.logs || []

  const onFinish = (values) => {
    setLoadingSubmit(true)
    let formData = new FormData()

    // Populate formData with form values
    Object.keys(values).forEach((key) => {
      let value = values[key]

      if (key === "owner") {
        value = value ? value.value : null
      } else if (
        key === "generate_date" ||
        key === "exp_date" ||
        key === "useful_life"
      ) {
        value = value ? value.format("YYYY-MM-DD") : null
      }

      formData.append(key, value !== undefined && value !== "" ? value : "")
    })

    const url = `/api/asset-control/save/${id}`

    return axios
      .post(url, formData)
      .then((res) => {
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        setLoadingSubmit(false)
        return navigate("/asset-control")
      })
      .catch((err) => {
        const { message } = err.response.data
        setLoadingSubmit(false)
        toast.error(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }

  return (
    <Layout title="Edit Data Asset" href="/asset-control">
      <Form
        form={form}
        name="basic"
        layout="vertical"
        onFinish={onFinish}
        autoComplete="off"
      >
        <Card title="Asset Data">
          <div className="card-body row">
            <div className="col-md-6">
              <Form.Item
                label="Barcode"
                name="barcode"
                rules={[
                  {
                    message: "Silakan masukkan barcode!",
                  },
                ]}
              >
                <Input placeholder="Input barcode" disabled />
              </Form.Item>
              <Form.Item
                label="Generate Date"
                name="generate_date"
                rules={[
                  {
                    required: false,
                    message: "Silakan pilih Generate Date!",
                  },
                ]}
              >
                <DatePicker className="w-full" format="DD-MM-YYYY" disabled />
              </Form.Item>
              <Form.Item
                label="Nomor Asset"
                name="asset_number"
                rules={[
                  {
                    required: true,
                    message: "Silahkan Masukkan nomor asset!",
                  },
                ]}
              >
                <Input placeholder="Input Nomor Asset" />
              </Form.Item>
              <Form.Item
                label="Purchase No."
                name="po_number"
                rules={[
                  {
                    message: "Silakan masukkan purchase number!",
                  },
                ]}
              >
                <Input placeholder="Input purchase number" disabled />
              </Form.Item>
              <Form.Item label="Notes" name="notes">
                <Input.TextArea placeholder="Ketik Customer Need" />
              </Form.Item>
              <Form.Item
                label="Lokasi Asset"
                name="asset_location"
                rules={[
                  {
                    required: true,
                    message: "Silahkan Masukkan Lokasi Asset!",
                  },
                ]}
              >
                <Input.TextArea placeholder="Input Lokasi Asset" />
              </Form.Item>
              <Form.Item
                label="Detail alamat penerima"
                name="receiver_address"
                rules={[
                  {
                    message: "Silahkan Masukkan Detail alamat penerima!",
                  },
                ]}
              >
                <Input.TextArea placeholder="Input Detail alamat penerima" />
              </Form.Item>
            </div>
            <div className="col-md-6">
              <Form.Item
                label="Item Name"
                name="item_name"
                rules={[
                  {
                    message: "Silakan masukkan  Item Name!",
                  },
                ]}
              >
                <Input placeholder="Input Item Name" disabled />
              </Form.Item>
              <Form.Item
                label="Brand Id"
                name="brand_name"
                rules={[
                  {
                    message: "Silakan masukkan Brand Id!",
                  },
                ]}
              >
                <Input placeholder="Input Brand Id" disabled />
              </Form.Item>
              <Form.Item
                label="Exp Date"
                name="exp_date"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Exp Date!",
                  },
                ]}
              >
                <DatePicker className="w-full" format="DD-MM-YYYY" />
              </Form.Item>
              <Form.Item
                label="Masa Manfaat"
                name="useful_life"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Masa Manfaat!",
                  },
                ]}
              >
                <DatePicker className="w-full" format="DD-MM-YYYY" />
              </Form.Item>
              <Form.Item
                label="Garansi"
                name="warranty"
                rules={[
                  {
                    message: "Silahkan Masukkan Garansi!",
                  },
                ]}
              >
                <Input placeholder="Input Garansi" />
              </Form.Item>
              <Form.Item
                label="Status alokasi"
                name="allocation_status"
                rules={[
                  {
                    required: true,
                    message: "Silahkan Masukkan Status alokasi!",
                  },
                ]}
              >
                <Input placeholder="Input Status alokasi" />
              </Form.Item>

              <Form.Item
                label="Owner"
                name="owner"
                rules={[
                  {
                    required: true,
                    message: "Silahkan Masukkan Owner!",
                  },
                ]}
              >
                <DebounceSelect
                  showSearch
                  placeholder="Cari Owner"
                  fetchOptions={handleSearchContact}
                  filterOption={false}
                  defaultOptions={contactList}
                  className="w-full"
                  onChange={(e) => {
                    setSelectedContact(e)
                    setShowContact(true)
                    form.setFieldsValue({ owner: e }) // Update form value
                  }}
                  dropdownRender={(menu) => (
                    <>
                      {menu}
                      <div className="py-1 flex w-full items-center justify-center">
                        <Button
                          className=""
                          type="text"
                          onClick={() => {
                            navigate("/contact/create")
                          }}
                        >
                          <strong className="text-blue-500">+ Add User</strong>
                        </Button>
                      </div>
                    </>
                  )}
                />
              </Form.Item>

              <div className="float-right mt-4">
                <button
                  type="button"
                  className="text-white bg-blue-700 hover:bg-blue-700/90 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
                  onClick={() => form.submit()}
                >
                  {loadingSubmit && <LoadingOutlined />}
                  <span className={loadingSubmit && "ml-2"}>Simpan</span>
                </button>
              </div>
            </div>
          </div>
        </Card>

        <div className="card mt-6 p-4">
          <Card title={"Log History"}>
            <div className="row">
              <div className="col-md-12 mt-4">
                <Table
                  dataSource={logs}
                  columns={[
                    {
                      title: "No.",
                      dataIndex: "number",
                      key: "number",
                      render: (value, row, index) => index + 1,
                    },
                    {
                      title: "Action",
                      dataIndex: "action",
                      key: "action",
                    },
                    {
                      title: "Executed By",
                      dataIndex: "user_name",
                      key: "user_name",
                    },
                    {
                      title: "Updated At",
                      dataIndex: "created_at",
                      key: "created_at",
                      render: (text) => formatDate(text),
                    },
                  ]}
                  loading={loading}
                  pagination={false}
                  rowKey="id"
                  scroll={{ x: "max-content" }}
                  tableLayout={"auto"}
                />
              </div>
            </div>
          </Card>
        </div>
      </Form>
    </Layout>
  )
}

export default AssetForm
