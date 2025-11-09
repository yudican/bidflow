import {
  ArrowRightOutlined,
  DeleteOutlined,
  LoadingOutlined,
  PlusOutlined,
} from "@ant-design/icons"
import {
  Button,
  DatePicker,
  Form,
  Input,
  Popconfirm,
  Select,
  Switch,
  Table,
  Tabs,
  Upload,
  message,
} from "antd"
import { Option } from "antd/lib/mentions"
import axios from "axios"
import moment from "moment"
import React, { useEffect, useState } from "react"
import { useParams } from "react-router-dom"
import { toast } from "react-toastify"
import { ReactComponent as InvoiceActiveIcon } from "../../Assets/Icons/fa6-solid_file-invoice-dollar.svg"
import { ReactComponent as TotalDebtIcon } from "../../Assets/Icons/flat-color-icons_debt.svg"
import { ReactComponent as TotalAmountIcon } from "../../Assets/Icons/jam_coin-f.svg"
import { ReactComponent as DepositoIcon } from "../../Assets/Icons/ri_luggage-deposit-fill.svg"
import LoadingFallback from "../../components/LoadingFallback"
import ModalRateLimit from "../../components/Modal/ModalRateLimit"
import Layout from "../../components/layout"
import {
  RenderIf,
  formatDateTime,
  formatNumber,
  formatPhone,
  getBase64,
  getItem,
  handleString,
  inArray,
} from "../../helpers"
import { productNeedListColumn } from "../OrderManual/config"
import ModalContactLayer from "./Components/ModalContactLayer"
import ContactAddress from "./ContactAddress"
import {
  contactCaseHistory,
  contactTransaction,
  memberLayerList,
  redeemPointListColumn,
  referalListColumn,
  voucherListColumn,
} from "./config"
import TransactionList from "../Transaction/Transaction/TransactionList"
import TransactionData from "../Transaction/Transaction/Components/TransactionData"
import VoucherList from "./Components/VoucherList"

const { TabPane } = Tabs

const DetailContact = () => {
  const [form] = Form.useForm()
  const params = useParams()
  const [activeTabKey, setActiveTabKey] = useState("1")
  const [detailContact, setDetailContact] = useState(null)
  const [transactionActive, setTransactionActive] = useState([])
  const [transactionHistory, setTransactionHistory] = useState([])
  const [contactDownlines, setContactDownlines] = useState([])
  const [referalList, setReferalList] = useState([])
  const [voucherList, setVoucherList] = useState([])
  const [redeemPointList, setRedeemPointList] = useState([])
  const [orderLead, setOrderLead] = useState(null)
  const [orderLeadList, setOrderLeadList] = useState([])
  const [caseHistory, setCaseHistory] = useState([])
  const [loading, setLoading] = useState(false)
  const [imageUrl, setImageUrl] = useState(false)
  const [fileList, setFileList] = useState(false)

  const loadDetailContact = () => {
    setLoading(true)
    axios.get(`/api/contact/detail/${params.user_id}`).then((res) => {
      const { data, order_lead } = res.data
      setActiveTabKey("1")
      setOrderLead(order_lead)
      const orderList =
        order_lead &&
        order_lead.list.map((item) =>
          item.product_needs
            .filter((row) => row.is_invoice == 1)
            .map((row_item) => {
              return {
                id: row_item.id,
                sku: row_item?.product?.sku,
                product: row_item?.product?.name || "-",
                product_id: row_item?.product_id,
                price: row_item?.prices?.final_price,
                qty: row_item?.qty,
                qty_delivery: row_item?.qty_delivery,
                total_price: row_item?.total,
                final_price: row_item?.final_price,
                margin_price: row_item?.margin_price,
                discount_id: row_item?.discount_id,
                tax_id: row_item?.tax_id,
                tax_amount: row_item?.tax_amount,
                price_nego: row_item?.price_nego,
                price_product: row_item?.price,
                total_price_nego: row_item?.price_nego * row_item?.qty,
                subtotal: row_item?.prices?.final_price * row_item?.qty,
                disabled_discount: row_item?.disabled_discount,
                disabled_price_nego: row_item?.disabled_price_nego,
                // disabled: data?.status > 1 ? true : false,
                is_invoice: row_item?.is_invoice,
                print_si_url: row_item?.print_si_url,
                due_date: row_item?.due_date,
              }
            })
        )

      const downlines = data.contact_downlines.map((item) => {
        return {
          id: item.userData?.id,
          name: item.userData?.name,
          email: item.userData?.email,
          phone: formatPhone(item.userData?.phone),
        }
      })
      setContactDownlines(downlines)

      const referals =
        data.referal_list?.map((item) => {
          return {
            id: item.id,
            name: item.name,
            email: item.email,
          }
        }) || []
      setReferalList(referals)

      const vouchers =
        data.voucher_list?.map((item) => {
          return {
            id: item.id,
            voucher: item.voucher_code,
          }
        }) || []
      setVoucherList(vouchers)

      const redeemPoint =
        data.redeem_point?.map((item) => {
          return {
            id: item.id,
            point: item.point,
            created_at: item.created_at,
          }
        }) || []
      setRedeemPointList(redeemPoint)

      setOrderLeadList(
        orderList.map((item, index) => item[index]).filter((item) => item)
      )
      setImageUrl(data.profile_photo_url)
      setDetailContact(data)
      form.setFieldsValue({
        name: data.name,
        email: data.email,
        telepon: data.telepon,
        gender: data.gender,
        bod: moment(data.bod ?? new Date(), "YYYY-MM-DD"),
      })
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
            id_transaksi: formatDateTime(transaction?.id_transaksi),
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
    setActiveTabKey(key)

    switch (key) {
      case "2":
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
    formData.append("password", value.password)
    formData.append("bod", value.bod.format("YYYY-MM-DD"))
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

  // const show = getItem("role") != "adminsales" && getItem("role") != "admin";
  const show = !inArray(getItem("role"), [
    "adminsales",
    "leadwh",
    "leadsales",
    "warehouse",
  ])

  if (loading) {
    return (
      <Layout title="Detail" href="/contact">
        <LoadingFallback />
      </Layout>
    )
  }

  const handleSaveMember = (values) => {
    setLoading(true)
    axios
      .post(`/api/contact/downline/member/save/${values.user_id}`, values)
      .then((res) => {
        const { data } = res.data
        loadDetailContact()
        setFileList(null)
        toast.success("Member berhasil disimpan", {
          position: toast.POSITION.TOP_RIGHT,
        })
        setLoading(false)
      })
  }

  const deleteMember = (user_id) => {
    axios
      .post(`/api/contact/downline/member/delete/${user_id}`, {
        _method: "DELETE",
      })
      .then((res) => {
        toast.success("Data berhasil dihapus")
        loadDetailContact()
      })
      .catch((err) => {
        toast.error("Data gagal dihapus")
      })
  }

  return (
    <Layout title="Detail" href="/contact">
      <Tabs activeKey={activeTabKey} onChange={handleChangeTab}>
        <TabPane key={"1"} tab="Contact Info">
          <div className="row">
            <RenderIf isTrue={true}>
              <div className="row w-full pl-3">
                <div className="col-md-3">
                  <div className="card bg-gradient-to-r from-white via-white to-[#FE3A304D]/20">
                    <div className="p-3 border-b-[1px] border-b-[#FE3A30]/50 flex justify-between">
                      <div className="flex items-center">
                        <TotalDebtIcon className="mr-2 h-6" />

                        <strong className="text-base font-semibold text-[#FE3A30]">
                          Total Debt
                        </strong>
                      </div>

                      <div>
                        <ArrowRightOutlined
                          onClick={() => setActiveTabKey("5")}
                          style={{
                            color: "#FE3A30",
                          }}
                        />
                      </div>
                    </div>
                    <div className="card-body">
                      <strong className="text-[#FE3A30] text-xl">
                        Rp.{" "}
                        {formatNumber(
                          parseInt(detailContact?.amount_detail?.total_debt)
                        )}
                      </strong>
                    </div>
                  </div>
                </div>
                <div className="col-md-3">
                  <div className="card bg-gradient-to-r from-white via-white to-[#1595001F]/20">
                    <div className="p-3 border-b-[1px] border-b-[#159500]/50 flex justify-between">
                      <div className="flex items-center">
                        <TotalAmountIcon className="mr-2 h-6" />
                        <strong className="text-base font-semibold text-[#159500]">
                          Total Amount
                        </strong>
                      </div>

                      <div>
                        <ArrowRightOutlined
                          onClick={() => setActiveTabKey("5")}
                          style={{
                            color: "#159500",
                          }}
                        />
                      </div>
                    </div>
                    <div className="card-body">
                      <strong className="text-[#159500] text-xl">
                        Rp.{" "}
                        {formatNumber(
                          detailContact?.amount_detail?.total_amount
                        )}
                      </strong>
                    </div>
                  </div>
                </div>
                <div className="col-md-3">
                  <div className="card bg-gradient-to-r from-white via-white to-[#7B61FF]/20">
                    <div className="p-3 border-b-[1px] border-b-[#7B61FF]/50 flex justify-between">
                      <div className="flex items-center">
                        <InvoiceActiveIcon className="mr-2 h-6" />
                        <strong className="text-base font-semibold text-[#7B61FF]">
                          Invoice Active
                        </strong>
                      </div>

                      <div>
                        <ArrowRightOutlined
                          onClick={() => setActiveTabKey("5")}
                          style={{
                            color: "#7B61FF",
                          }}
                        />
                      </div>
                    </div>
                    <div className="card-body">
                      <strong className="text-[#7B61FF] text-xl">
                        {formatNumber(orderLeadList?.length)}
                      </strong>
                    </div>
                  </div>
                </div>
                <div className="col-md-3">
                  <div className="card bg-gradient-to-r from-white via-white to-[#fac014]/20">
                    <div className="p-3 border-b-[1px] border-b-[#fac014]/50 flex justify-between">
                      <div className="flex items-center">
                        <DepositoIcon
                          style={{ color: "#fac014" }}
                          className="mr-2 h-6"
                        />
                        <strong className="text-base font-semibold text-[#fac014]">
                          Deposito
                        </strong>
                      </div>

                      <div>
                        <ArrowRightOutlined
                          onClick={() => setActiveTabKey("5")}
                          style={{
                            color: "#fac014",
                          }}
                        />
                      </div>
                    </div>
                    <div className="card-body">
                      <strong className="text-[#fac014] text-xl">
                        {`Rp ${formatNumber(detailContact?.deposit)}`}
                      </strong>
                    </div>
                  </div>
                </div>
              </div>
            </RenderIf>

            <div className="col-md-4">
              <div
                className={`card card-profile ${getItem("text-style")}
              `}
              >
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
                <div
                  className={`p-0 
                   
                  `}
                >
                  <div className="list-group p-0 m-0">
                    <div
                      className={`list-group-item d-flex justify-content-between align-items-center`}
                    >
                      Blacklist
                      <Switch checked={isBlacklist} onChange={handleBlaclist} />
                    </div>
                    <div className="list-group-item d-flex justify-content-between align-items-center ">
                      Create Date
                      <span>
                        {moment(detailContact?.created_at).format("DD-MM-YYYY")}
                      </span>
                    </div>
                    <div className="list-group-item d-flex justify-content-between align-items-center ">
                      Referal Code
                      <span>{detailContact?.referal_code}</span>
                    </div>
                    <div className="list-group-item d-flex justify-content-between align-items-center ">
                      Poin
                      <span>
                        {detailContact?.total_poin > 0
                          ? detailContact?.total_poin
                          : 0}
                      </span>
                    </div>
                    {detailContact?.role?.rate_limit_status > 0 && (
                      <ModalRateLimit
                        rateValue={detailContact?.rate_limit || 0}
                        initialValues={{
                          rate_limit: detailContact?.rate_limit || 0,
                        }}
                        url={`/api/contact/rate-limit/update/${params?.user_id}`}
                        refetch={() => loadDetailContact()}
                      />
                    )}
                  </div>
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
                          <strong>Customer Code</strong>
                        </td>
                        <td>: {handleString(detailContact?.uid)}</td>
                      </tr>
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
                        <td>
                          : {moment(detailContact?.bod).format("DD-MM-YYYY")}
                        </td>
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
                        <td>: {formatPhone(detailContact?.telepon)}</td>
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
                          <strong>No. NPWP</strong>
                        </td>
                        <td>: {handleString(company?.npwp)}</td>
                      </tr>
                      <tr>
                        <td className="py-2">
                          <strong>NPWP Name</strong>
                        </td>
                        <td>: {handleString(company?.npwp_name)}</td>
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
                          <td>: {handleString(company?.name)}</td>
                        </tr>
                        <tr>
                          <td style={{ width: "50%" }} className="py-2">
                            <strong>Business Entity</strong>
                          </td>
                          <td>
                            : {handleString(company?.business_entity?.title)}
                          </td>
                        </tr>

                        <tr>
                          <td style={{ width: "50%" }} className="py-2">
                            <strong>Company Email</strong>
                          </td>
                          <td>: {handleString(company?.email)}</td>
                        </tr>
                        <tr>
                          <td style={{ width: "50%" }} className="py-2">
                            <strong>Company Phone</strong>
                          </td>
                          <td>: {formatPhone(handleString(company?.phone))}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <div className="col-md-6">
                    <table className="w-100" style={{ width: "100%" }}>
                      <tbody>
                        {/* <tr>
                          <td style={{ width: "50%" }} className="py-2">
                            <strong>PIC Sales</strong>
                          </td>
                          <td>: {handleString(company?.pic_name)}</td>
                        </tr> */}
                        {/* <tr>
                          <td style={{ width: "50%" }} className="py-2">
                            <strong>PIC Phone</strong>
                          </td>
                          <td>: {formatPhone(handleString(company?.phone))}</td>
                        </tr> */}

                        <tr>
                          <td style={{ width: "50%" }} className="py-2">
                            <strong>Owner Name</strong>
                          </td>
                          <td>: {handleString(company?.owner_name)}</td>
                        </tr>
                        <tr>
                          <td style={{ width: "50%" }} className="py-2">
                            <strong>Owner Phone</strong>
                          </td>
                          <td>
                            : {formatPhone(handleString(company?.owner_phone))}
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>

            {/* layer */}
            {inArray(company?.layer_type, ["distributor"]) && (
              <div className="col-md-12">
                <div className="card">
                  <div className="card-header flex justify-between items-center">
                    <h1 className="text-lg text-bold ">Member</h1>
                    <ModalContactLayer
                      handleOk={(val) => handleSaveMember(val)}
                      user_id={params?.user_id}
                    />
                  </div>
                  <div className="card-body">
                    <Table
                      scroll={{ x: "max-content" }}
                      tableLayout={"auto"}
                      dataSource={contactDownlines}
                      columns={[
                        ...memberLayerList,
                        {
                          title: "Action",
                          dataIndex: "id",
                          key: "id",
                          render: (text, record) => {
                            return (
                              <div className="flex items-center">
                                <Popconfirm
                                  title="Yakin Hapus Data ini?"
                                  onConfirm={() => deleteMember(record.id)}
                                  okText="Ya, Hapus"
                                  cancelText="Batal"
                                >
                                  <button className="text-white bg-red-800 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2">
                                    <DeleteOutlined />
                                  </button>
                                </Popconfirm>
                              </div>
                            )
                          },
                        },
                      ]}
                      pagination={false}
                      rowKey="id"
                    />
                  </div>
                </div>
              </div>
            )}

            {/* address */}
            <div className="col-md-12">
              <ContactAddress
                data={
                  address_users &&
                  address_users.map((item) => {
                    return {
                      ...item,
                      telepon: formatPhone(item?.telepon),
                    }
                  })
                }
                contact={detailContact}
                refetch={() => loadDetailContact()}
              />
            </div>
          </div>
        </TabPane>
        <TabPane tab="Active Transaction FlimApp" key="2">
          <TransactionData
            stage={[
              "waiting-payment",
              "waiting-confirmation",
              "confirm-payment",
              "on-process",
              "ready-to-ship",
              "on-delivery",
            ]}
            columns={contactTransaction}
            contact={params?.user_id}
          />
        </TabPane>
        <TabPane tab="History Transaction FlimApp" key="3">
          <TransactionData
            columns={contactTransaction}
            contact={params?.user_id}
            stage={["delivered", "returned", "cancelled"]}
          />
        </TabPane>
        <TabPane tab="History Case" key="4">
          <Table
            dataSource={caseHistory}
            columns={contactCaseHistory}
            // loading={loading}
            pagination={false}
            rowKey="id"
          />
        </TabPane>
        <TabPane tab="History Order" key="5">
          <Table
            dataSource={orderLeadList}
            columns={[...productNeedListColumn]}
            loading={loading}
            pagination={false}
            rowKey="id"
            scroll={{ x: "max-content" }}
            tableLayout={"auto"}
          />
        </TabPane>
        <TabPane tab="History Referal" key="6">
          <Table
            dataSource={referalList}
            columns={[...referalListColumn]}
            loading={loading}
            pagination={false}
            rowKey="id"
            scroll={{ x: "max-content" }}
            tableLayout={"auto"}
          />
        </TabPane>
        <TabPane tab="History Redeem Point" key="7">
          <Table
            dataSource={redeemPointList}
            columns={[...redeemPointListColumn]}
            loading={loading}
            pagination={false}
            rowKey="id"
            scroll={{ x: "max-content" }}
            tableLayout={"auto"}
          />
        </TabPane>
        <TabPane tab="List Voucher" key="8">
          {/* <Table
            dataSource={voucherList}
            columns={[...voucherListColumn]}
            loading={loading}
            pagination={false}
            rowKey="id"
            scroll={{ x: "max-content" }}
            tableLayout={"auto"}
          /> */}
          <VoucherList columns={voucherListColumn} contact={params?.user_id} />
        </TabPane>
        {show && (
          <TabPane tab="Setting Profile" key="9">
            <Form
              form={form}
              name="basic"
              layout="vertical"
              onFinish={onFinish}
              //   onFinishFailed={onFinishFailed}
              autoComplete="off"
            >
              <Form.Item
                label="Nama lengkap"
                name="name"
                rules={[
                  {
                    required: false,
                    message: "Silakan masukkan nama lengkap!",
                  },
                ]}
              >
                <Input disabled />
              </Form.Item>

              <Form.Item
                label="Email"
                name="email"
                rules={[
                  {
                    required: false,
                    message: "Silakan masukkan password!",
                  },
                ]}
              >
                <Input disabled />
              </Form.Item>
              <Form.Item
                label="Telepon"
                name="telepon"
                rules={[
                  {
                    required: false,
                    message: "Silakan masukkan Telepon!",
                  },
                ]}
              >
                <Input disabled />
              </Form.Item>
              <Form.Item
                label="Jenis Kelamin"
                name="gender"
                rules={[
                  {
                    required: false,
                    message: "Silakan pilih Jenis Kelamin!",
                  },
                ]}
              >
                <Select placeholder="Pilih Jenis Kelamin" disabled>
                  <Option value="Laki-Laki">Laki-Laki</Option>
                  <Option value="Perempuan">Perempuan</Option>
                </Select>
              </Form.Item>

              <Form.Item
                label="Birth of Date"
                name="bod"
                rules={[
                  {
                    required: false,
                    message: "Silakan pilih Birth of Date!",
                  },
                ]}
              >
                <DatePicker
                  className="w-full"
                  disabled
                  format={"DD-MM-YYYY"}
                  style={{ width: "100%" }}
                />
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
        )}
      </Tabs>
    </Layout>
  )
}

export default DetailContact
