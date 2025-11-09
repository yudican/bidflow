import { LoadingOutlined, PlusOutlined } from "@ant-design/icons"
import {
  Button,
  DatePicker,
  Form,
  Input,
  Select,
  Switch,
  Table,
  Tabs,
  Upload,
  message,
} from "antd"
import { Option } from "antd/lib/mentions"
import React, { useEffect, useState } from "react"
import { useParams } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../components/layout"
import LoadingFallback from "../../components/LoadingFallback"
import { getBase64 } from "../../helpers"
import { contactCaseHistory, contactTransaction } from "./config"
import ContactAddress from "./ContactAddress"
const { TabPane } = Tabs
const DetailContact = () => {
  const [form] = Form.useForm()
  const params = useParams()
  const [detailContact, setDetailContact] = useState(null)
  const [transactionActive, setTransactionActive] = useState([])
  const [transactionHistory, setTransactionHistory] = useState([])
  const [caseHistory, setCaseHistory] = useState([])
  const [loading, setLoading] = useState(false)
  const [imageUrl, setImageUrl] = useState(false)
  const [fileList, setFileList] = useState(false)
  const loadDetailContact = () => {
    setLoading(true)
    axios.get(`/api/contact/detail/${params.user_id}`).then((res) => {
      const { data } = res.data
      setImageUrl(data.profile_photo_url)
      setDetailContact(data)
      setLoading(false)
    })
  }
  const handleBlaclist = () => {
    axios.get(`/api/contact/black-list/${params.user_id}`).then((res) => {
      const { message } = res.data
      toast.success(message, {
        position: toast.POSITION.TOP_RIGHT,
      })
      loadDetailContact()
    })
  }

  const loadTransaction = (type = "active") => {
    setLoading(true)
    axios
      .get(`/api/contact/detail/transaction/${type}/${params.user_id}`)
      .then((res) => {
        const { data } = res.data

        const newData = data.map((transaction) => {
          return {
            id: transaction.id,
            name: transaction?.user?.name,
            nominal: transaction.nominal,
            tanggal_transaksi: transaction.created_at,
            id_transaksi: transaction?.id_transaksi,
            payment_method: transaction?.payment_method?.nama_bank,
            status: transaction.status,
            status_delivery: transaction.status_delivery,
          }
        })
        type === "active"
          ? setTransactionActive(newData)
          : setTransactionHistory(newData)
        setLoading(false)
      })
  }

  const loadCasehistory = () => {
    setLoading(true)
    axios
      .get(`/api/contact/detail/case/history/${params.user_id}`)
      .then((res) => {
        const { data } = res.data

        const newData = data.map((caseItem) => {
          return {
            id: caseItem.id,
            title: caseItem.title,
            contact: caseItem.contact_user.name,
            type: caseItem.type_case.type_name,
            category: caseItem.category_case.category_name,
            priority: caseItem.priority_case.priority_name,
            created_by: caseItem.created_user.name,
            created_at: caseItem.created_at,
          }
        })
        setCaseHistory(newData)
        setLoading(false)
      })
  }

  useEffect(() => {
    loadDetailContact()
  }, [])

  const handleChangeTab = (key) => {
    switch (key) {
      case "2":
        console.log("ok")
        loadTransaction("active")
        break
      case "3":
        loadTransaction("history")
        break
      case "4":
        loadCasehistory()
        break

      default:
        break
    }
  }

  const handleChange = ({ fileList }) => {
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
    let formData = new FormData()

    if (fileList) {
      formData.append("profile_image", fileList)
    }

    formData.append("user_id", detailContact.id)
    formData.append("name", value.name)
    formData.append("email", value.email)
    formData.append("telepon", value.telepon)
    formData.append("gender", value.gender)
    formData.append("bod", value.bod.format("YYYY-MM-DD"))
    console.log(formData)
    axios.post(`/api/contact/detail/update`, formData).then((res) => {
      const { data } = res.data
      loadDetailContact()
      setFileList(null)
      toast.success("Contact berhasil diupdate", {
        position: toast.POSITION.TOP_RIGHT,
      })
    })
  }

  const uploadButton = (
    <div>
      {loading ? <LoadingOutlined /> : <PlusOutlined />}
      <div
        style={{
          marginTop: 8,
        }}
      >
        Upload
      </div>
    </div>
  )

  const { company, address_users, brand, user_created } = detailContact || {}
  const isBlacklist = detailContact?.status == 0 ? true : false

  if (loading) {
    return (
      <Layout title="Detail" href="/contact">
        <LoadingFallback />
      </Layout>
    )
  }

  return (
    <Layout title="Detail" href="/contact">
      <Tabs defaultActiveKey="1" onChange={handleChangeTab}>
        <TabPane tab="Contact Info">
          <div className="row">
            <div className="col-md-4">
              <div className="card card-profile">
                <div className="card-header">
                  <div className="profile-picture">
                    <div className="avatar avatar-xl">
                      <img
                        src={detailContact?.profile_photo_url}
                        alt="..."
                        className="avatar-img rounded-circle"
                      />
                    </div>
                  </div>
                </div>
                <div className="card-body">
                  <div className="user-profile text-center">
                    <div className="name flex justify-content-center align-items-center mb-3">
                      <img
                        src="https://img.icons8.com/color/48/000000/verified-badge.png"
                        style={{ height: 30 }}
                      />
                      <span>{detailContact?.name}</span>
                    </div>
                    <div className="job">{detailContact?.role?.role_name}</div>
                  </div>
                </div>
                <div className="card-footer p-0">
                  <ul class="list-group p-0 m-0">
                    <li class="list-group-item d-flex justify-content-between align-items-center ">
                      Blacklist
                      <Switch checked={isBlacklist} onChange={handleBlaclist} />
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center ">
                      Create Date
                      <span>
                        {moment(detailContact?.created_at).format("DD-MM-YYYY")}
                      </span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
            <div className="col-md-8">
              <div className="card">
                <div className="card-body">
                  <table className="w-100">
                    <tbody>
                      <tr>
                        <td className="py-2">
                          <strong>Email</strong>
                        </td>
                        <td>: {detailContact?.email}</td>
                      </tr>
                      <tr>
                        <td className="py-2">
                          <strong>Birth of Date</strong>
                        </td>
                        <td>: {detailContact?.bod}</td>
                      </tr>
                      <tr>
                        <td className="py-2">
                          <strong>Gender</strong>
                        </td>
                        <td>: {detailContact?.gender}</td>
                      </tr>
                      <tr>
                        <td className="py-2">
                          <strong>Phone</strong>
                        </td>
                        <td>: {detailContact?.telepon}</td>
                      </tr>
                      <tr>
                        <td className="py-2">
                          <strong>Brand</strong>
                        </td>
                        <td>: {brand?.name}</td>
                      </tr>
                      <tr>
                        <td className="py-2">
                          <strong>Owner</strong>
                        </td>
                        <td>: {user_created?.name}</td>
                      </tr>
                      <tr>
                        <td className="py-2">
                          <strong>NPWP</strong>
                        </td>
                        <td>: -</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            {/* company */}
            <div className="col-md-12">
              <div className="card">
                <div className="card-header">
                  <h1 className="text-lg text-bold ">Company Detail</h1>
                </div>
                <div className="card-body row">
                  <div className="col-md-6">
                    <table className="w-100" style={{ width: "100%" }}>
                      <tbody>
                        <tr>
                          <td style={{ width: "50%" }} className="py-2">
                            <strong>Company Name</strong>
                          </td>
                          <td>: {company?.name || "-"}</td>
                        </tr>
                        <tr>
                          <td style={{ width: "50%" }} className="py-2">
                            <strong>Business Entity</strong>
                          </td>
                          <td>: {company?.business_entity?.title || "-"}</td>
                        </tr>

                        <tr>
                          <td style={{ width: "50%" }} className="py-2">
                            <strong>Company Email</strong>
                          </td>
                          <td>: {company?.email || "-"}</td>
                        </tr>
                        <tr>
                          <td style={{ width: "50%" }} className="py-2">
                            <strong>Company Phone</strong>
                          </td>
                          <td>: {company?.phone || "-"}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <div className="col-md-6">
                    <table className="w-100" style={{ width: "100%" }}>
                      <tbody>
                        <tr>
                          <td style={{ width: "50%" }} className="py-2">
                            <strong>PIC Sales</strong>
                          </td>
                          <td>: {company?.pic_name || "-"}</td>
                        </tr>
                        <tr>
                          <td style={{ width: "50%" }} className="py-2">
                            <strong>PIC Phone</strong>
                          </td>
                          <td>: {company?.phone || "-"}</td>
                        </tr>

                        <tr>
                          <td style={{ width: "50%" }} className="py-2">
                            <strong>Owner Name</strong>
                          </td>
                          <td>: {company?.owner_name || "-"}</td>
                        </tr>
                        <tr>
                          <td style={{ width: "50%" }} className="py-2">
                            <strong>Owner Phone</strong>
                          </td>
                          <td>: {company?.owner_phone || "-"}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>

            {/* address */}
            <div className="col-md-12">
              <ContactAddress
                data={address_users}
                contact={detailContact}
                refetch={() => loadDetailContact()}
              />
            </div>
          </div>
        </TabPane>
        <TabPane tab="Active Transaction" key="2">
          <Table
            dataSource={transactionActive}
            columns={contactTransaction}
            // loading={loading}
            pagination={false}
            rowKey="id"
            scroll={{ x: "max-content" }}
            tableLayout={"auto"}
          />
        </TabPane>
        <TabPane tab="History Transaction" key="3">
          <Table
            dataSource={transactionHistory}
            columns={contactTransaction}
            // loading={loading}
            pagination={false}
            rowKey="id"
            scroll={{ x: "max-content" }}
            tableLayout={"auto"}
          />
        </TabPane>
        <TabPane tab="History Case" key="4">
          <Table
            dataSource={caseHistory}
            columns={contactCaseHistory}
            // loading={loading}
            pagination={false}
            rowKey="id"
            scroll={{ x: "max-content" }}
            tableLayout={"auto"}
          />
        </TabPane>
        <TabPane tab="Setting Profile" key="5">
          <Form
            form={form}
            name="basic"
            layout="vertical"
            initialValues={{
              name: detailContact?.name,
              email: detailContact?.email,
              telepon: detailContact?.telepon,
              gender: detailContact?.gender,
              bod: moment(detailContact?.bod ?? new Date(), "YYYY-MM-DD"),
            }}
            onFinish={onFinish}
            //   onFinishFailed={onFinishFailed}
            autoComplete="off"
          >
            <Form.Item
              label="Nama lengkap"
              name="name"
              rules={[
                {
                  required: true,
                  message: "Silakan masukkan nama lengkap!",
                },
              ]}
            >
              <Input />
            </Form.Item>

            <Form.Item
              label="Email"
              name="email"
              rules={[
                {
                  required: true,
                  message: "Silakan masukkan password!",
                },
              ]}
            >
              <Input />
            </Form.Item>
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
              <Input />
            </Form.Item>
            <Form.Item
              label="Jenis Kelamin"
              name="gender"
              rules={[
                {
                  required: true,
                  message: "Silakan pilih Jenis Kelamin!",
                },
              ]}
            >
              <Select placeholder="Pilih Jenis Kelamin">
                <Option value="Laki-Laki">Laki-Laki</Option>
                <Option value="Perempuan">Perempuan</Option>
              </Select>
            </Form.Item>

            <Form.Item
              label="Birth of Date"
              name="bod"
              rules={[
                {
                  required: true,
                  message: "Silakan pilih Birth of Date!",
                },
              ]}
            >
              <DatePicker className="w-full" />
            </Form.Item>

            <Form.Item
              label="Profile Photo"
              name="profile_image"
              rules={[
                {
                  required: detailContact?.profile_photo_path,
                  message: "Silakan pilih Photo!",
                },
              ]}
            >
              <Upload
                name="profile_image"
                listType="picture-card"
                className="avatar-uploader"
                showUploadList={false}
                multiple={false}
                beforeUpload={() => false}
                onChange={handleChange}
              >
                {imageUrl ? (
                  loading ? (
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

            <Form.Item
              label="Password"
              name="password"
              rules={[
                {
                  message: "Silakan masukkan Password!",
                },
              ]}
            >
              <Input.Password />
            </Form.Item>

            <div className="col-md-12 ">
              <div className="float-right">
                <Form.Item>
                  <Button type="primary" htmlType="submit">
                    Submit
                  </Button>
                </Form.Item>
              </div>
            </div>
          </Form>
        </TabPane>
      </Tabs>
    </Layout>
  )
}

export default DetailContact
