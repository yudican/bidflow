import {
  CheckCircleFilled,
  CloseCircleFilled,
  InfoCircleFilled,
  LoadingOutlined,
} from "@ant-design/icons"
import {
  Button,
  Card,
  DatePicker,
  Form,
  Input,
  message,
  Pagination,
  Select,
  Table,
} from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import LoadingFallback from "../../components/LoadingFallback"
import Layout from "../../components/layout"
import { formatNumber, getItem, inArray } from "../../helpers"

import TextArea from "antd/lib/input/TextArea"
import { comissionWithdrawApprovalColumn } from "./config"
import { loadUserById, searchContact } from "./service"
import DebounceSelect from "../../components/atoms/DebounceSelect"
import RejectModal from "./Components/RejectModal"
import moment from "moment"

const ComissionWithdrawForm = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const role = getItem("role")
  const userData = getItem("user_data", true)
  const { commission_id } = useParams()
  const [detail, setDetail] = useState(null)
  const [loading, setLoading] = useState(false)
  const [contactList, setContactList] = useState([])
  const [contactRequestList, setContactRequestList] = useState([])
  const [status, setStatus] = useState(0)
  const [roles, setRoles] = useState([])
  const [loadingRole, setLoadingRole] = useState(false)

  const loadDetail = () => {
    setLoading(true)
    if (commission_id) {
      axios
        .get(`/api/comission-withdraw/detail/${commission_id}`)
        .then((res) => {
          const { data } = res.data
          setLoading(false)
          setDetail(data)
          const forms = {
            ...data,
            user_contact: {
              label: data?.user_name,
              value: data?.user_id,
            },
            user_request: {
              label: data?.request_by_name,
              value: data?.request_by,
            },
            created_at: moment(data.created_at || new Date(), "YYYY-MM-DD"),
          }
          form.setFieldsValue(forms)

          // search role
          const currentRole = roles.find((role) => role.id === data.role_id)
          if (!currentRole) {
            const newRoles = [...roles]
            newRoles.push({
              id: data.role_id,
              role_name: data.role_name,
              role_type: "superadmin",
            })

            setRoles(newRoles)
          }
        })
        .catch((e) => setLoading(false))
    } else {
      setLoading(false)
      if (role === "ahligizi") {
        const forms = {
          user_contact: {
            label: userData.name,
            value: userData.id,
          },
          user_request: {
            label: userData.name,
            value: userData.id,
          },
          created_at: moment(new Date(), "YYYY-MM-DD"),
          role_id: userData.role.id,
          email: userData.email,
          phone: userData.telepon,
        }
        form.setFieldsValue(forms)
      } else {
        const forms = {
          user_request: {
            label: userData.name,
            value: userData.id,
          },
          created_at: moment(new Date(), "YYYY-MM-DD"),
          role_id: userData.role.id,
        }
        form.setFieldsValue(forms)
      }
    }
  }

  const handleGetContact = (callback) => {
    searchContact(null).then((results) => {
      const newResult = results.map((result) => {
        return { label: result.nama, value: result.id }
      })
      callback(newResult)
    })
  }

  const handleSearchContact = async (e) => {
    return searchContact(e).then((results) => {
      const newResult = results.map((result) => {
        return { label: result.nama, value: result.id }
      })

      return newResult
    })
  }

  const loadRole = () => {
    axios.get("/api/master/role").then((res) => {
      const { data } = res.data
      if (userData.role.role_type === "superadmin") {
        data.push(userData.role)
      }
      setRoles(data)
    })
  }

  const onFinish = (values) => {
    const status_save = status < 1 ? "draft" : "waiting-approval"
    const params = commission_id ? `/${commission_id}` : ""
    axios
      .post(`/api/comission-withdraw/save${params}`, {
        ...values,
        status: status_save,
        user_id: values.user_contact.value,
        request_by: values.user_request.value,
      })
      .then((res) => {
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        return navigate("/comission-withdraw")
      })
      .catch((err) => {
        const { message } = err.response.data
        toast.error(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }

  const handleApprove = () => {
    axios
      .post(`/api/comission-withdraw/approve/${commission_id}`, {})
      .then((res) => {
        message.success("Approve berhasil")
        refetch()
      })
      .catch((err) => {
        message.error("Approve gagal")
      })
  }

  const canApprove = inArray(role, ["superadmin", "finance"])
  const rightContent = (
    <div>
      {canApprove && (
        <div className="flex items-center">
          <RejectModal
            url={`/api/comission-withdraw/reject/${commission_id}`}
            refetch={() => loadDetail()}
          />

          <button
            onClick={() => {
              handleApprove()
            }}
            className="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
          >
            <CheckCircleFilled />
            <span className="ml-2">{`Approve`}</span>
          </button>
        </div>
      )}
    </div>
  )

  useEffect(() => {
    loadDetail()
    loadRole()
    handleGetContact((e) => setContactList(e))
    handleGetContact((e) => setContactRequestList(e))
  }, [])

  if (loading) {
    return (
      <Layout title="Detail" href="/comission-withdraw">
        <LoadingFallback />
      </Layout>
    )
  }

  const handleGetText = () => {
    if (detail?.status === "onprocess") {
      return `Pengajuan withdraw Anda sejumlah Rp ${formatNumber(
        detail?.nominal
      )} saat ini sedang diproses.`
    }

    if (detail?.status === "success") {
      return `Pengajuan withdraw Anda sejumlah Rp ${formatNumber(
        detail?.nominal
      )}  telah berhasil terkirim ke nomor rekening tujuan!`
    }

    if (!detail) {
      return `Anda dapat melakukan withdraw maksimal sejumlah Rp ${formatNumber(
        userData?.amount_can_withdraw || 0
      )}`
    }

    return null
  }

  if (loading) {
    return (
      <Layout
        title={detail?.status ? "Detail Withdraw" : "Tambah Data Withdraw"}
        href="/comission-withdraw"
      >
        <LoadingFallback />
      </Layout>
    )
  }

  return (
    <Layout
      title={detail?.status ? "Detail Withdraw" : "Tambah Data Withdraw"}
      href="/comission-withdraw"
      rightContent={detail?.status === "waiting-approval" ? rightContent : null}
    >
      <Form
        form={form}
        name="basic"
        layout="vertical"
        onFinish={onFinish}
        //   onFinishFailed={onFinishFailed}
        autoComplete="off"
      >
        <Card
          title="Pengajuan Withdraw"
          extra={
            <div className="flex justify-end items-center">
              <strong>Status :</strong>
              <Button
                type="outline"
                size={"middle"}
                style={{
                  marginLeft: 10,
                }}
              >
                {detail?.status}
              </Button>
            </div>
          }
        >
          {handleGetText() && (
            <div className="full mx-auto bg-[#D8F0FF] rounded-[5px] border-[1px] border-blueColor p-3 shadow-flimty mb-6 text-sm font-normal text-blueColor flex">
              <InfoCircleFilled className="mr-2" /> {handleGetText()}
            </div>
          )}

          <div className="grid md:grid-cols-2 gap-x-10">
            <Form.Item
              label="Nama Lengkap"
              name="user_contact"
              rules={[
                {
                  required: true,
                  message: "Silakan pilih Nama Lengkap!",
                },
              ]}
            >
              <DebounceSelect
                showSearch
                placeholder="Cari Nama Lengkap"
                fetchOptions={handleSearchContact}
                filterOption={false}
                className="w-full"
                defaultOptions={contactList}
                onChange={(e) => {
                  const { value } = e
                  loadUserById(value, (res) => {
                    form.setFieldsValue(res)
                  })
                }}
              />
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
              label="No Telepon"
              name="phone"
              rules={[
                {
                  required: true,
                  message: "Silakan masukkan No Telepon!",
                },
              ]}
            >
              <Input />
            </Form.Item>
            <Form.Item label="Created Date" name="created_at">
              <DatePicker className="w-full" disabled />
            </Form.Item>
          </div>

          <div className="grid md:grid-cols-3 gap-x-10">
            <Form.Item
              label="Nama Rekening"
              name="nama_rekening"
              rules={[
                {
                  required: true,
                  message: "Silakan masukkan Nama Rekening!",
                },
              ]}
            >
              <Input placeholder="Ketik Nama Rekening" />
            </Form.Item>
            <Form.Item
              label="Nomor Rekening"
              name="nomor_rekening"
              rules={[
                {
                  required: true,
                  message: "Silakan masukkan Nomor Rekening!",
                },
              ]}
            >
              <Input placeholder="Ketik Nomor Rekening" />
            </Form.Item>
            <Form.Item
              label="Bank"
              name="nama_bank"
              rules={[
                {
                  required: true,
                  message: "Silakan masukkan Nama Rekening!",
                },
              ]}
            >
              <Input placeholder="Ketik Nama Rekening" />
            </Form.Item>
            <Form.Item
              label="Nominal"
              name="amount"
              rules={[
                {
                  required: true,
                  message: "Silakan masukkan Nominal!",
                },
              ]}
            >
              <Input placeholder="Input Nominal" type="number" />
              {userData?.amount_can_withdraw > 0 && (
                <span className="text-xs font-thin text-blueColor">
                  minimal penarikan sejumlah Rp {formatNumber(300000)}
                </span>
              )}
            </Form.Item>
            <Form.Item
              label="Request by"
              name="user_request"
              rules={[
                {
                  required: true,
                  message: "Silakan pilih Tipe PO!",
                },
              ]}
            >
              <DebounceSelect
                showSearch
                placeholder="Cari Request Name"
                fetchOptions={handleSearchContact}
                filterOption={false}
                className="w-full"
                defaultOptions={contactList}
                disabled={inArray(role, ["ahligizi"])}
              />
            </Form.Item>
            <Form.Item
              label="Role"
              name="role_id"
              rules={[
                {
                  required: true,
                  message: "Silakan pilih Role!",
                },
              ]}
            >
              <Select
                allowClear
                className="w-full mb-2"
                placeholder="Pilih Role"
                loading={loadingRole}
                disabled
              >
                {roles.map((item) => (
                  <Select.Option key={item.id} value={item.id}>
                    {item.role_name}
                  </Select.Option>
                ))}
              </Select>
            </Form.Item>
          </div>

          <Form.Item
            requiredMark={"optional"}
            label="Notes"
            name="notes"
            rules={[
              {
                required: false,
                message: "Silakan masukkan Warehouse!",
              },
            ]}
          >
            <TextArea
              placeholder="Silakan input catatan.."
              showCount
              maxLength={100}
              rows={2}
            />
          </Form.Item>
        </Card>
      </Form>

      <Card title="History Approval" className="mt-4">
        <Table
          dataSource={detail?.commision_withdraw_approvals || []}
          columns={[...comissionWithdrawApprovalColumn]}
          loading={loading}
          pagination={false}
          rowKey="id"
          scroll={{ x: "max-content" }}
          tableLayout={"auto"}
        />
        <Pagination
          defaultCurrent={1}
          // current={currentPage}
          // total={total}
          className="mt-4 text-center"
          // onChange={handleChange}
        />
      </Card>

      <Card>
        <div className="float-right">
          {!commission_id && (
            <button
              onClick={() => {
                setStatus(0)
                setTimeout(() => {
                  form.submit()
                }, 1000)
              }}
              type="button"
              className={`text-blue-700 bg-white border hover:bg-black focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center`}
              disabled={loading}
            >
              {loading ? (
                <LoadingOutlined />
              ) : (
                <span className="">Simpan Sebagai Draft</span>
              )}
            </button>
          )}
          {detail?.status === "onprocess" && (
            <button
              onClick={() => {
                handleComplete(commission_id)
              }}
              className="text-white bg-green hover:bg-green/90 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
            >
              {/* {loadingSubmit ? <LoadingOutlined /> : <CheckOutlined />} */}
              <span className="ml-2">Complete</span>
            </button>
          )}

          {inArray(detail?.status, ["draft", "waiting-approval"]) && (
            <button
              onClick={() => {
                setStatus(1)
                setTimeout(() => {
                  form.submit()
                }, 1000)
              }}
              className="text-white bg-blueColor hover:bg-blueColor/90 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
            >
              {/* {loadingSubmit ? <LoadingOutlined /> : <CheckOutlined />} */}
              <span className="ml-2">Simpan</span>
            </button>
          )}

          {!detail && (
            <button
              onClick={() => {
                setStatus(1)
                setTimeout(() => {
                  form.submit()
                }, 1000)
              }}
              className="text-white bg-blueColor hover:bg-blueColor/90 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
            >
              {/* {loadingSubmit ? <LoadingOutlined /> : <CheckOutlined />} */}
              <span className="ml-2">Simpan</span>
            </button>
          )}
        </div>
      </Card>
    </Layout>
  )
}

export default ComissionWithdrawForm
