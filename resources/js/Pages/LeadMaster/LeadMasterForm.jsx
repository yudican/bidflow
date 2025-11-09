import {
  Button,
  Card,
  DatePicker,
  Form,
  Input,
  Select,
  Switch,
  Table,
} from "antd"
import axios from "axios"
import moment from "moment"
import React, { useEffect, useState } from "react"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import LoadingFallback from "../../components/LoadingFallback"
import DebounceSelect from "../../components/atoms/DebounceSelect"
import Layout from "../../components/layout"
import { getItem } from "../../helpers"
import FormAddressModal from "../Contact/Components/FormAddressModal"
import { searchContact, searchSales } from "./service"

const LeadMasterForm = () => {
  const navigate = useNavigate()
  const userData = JSON.parse(localStorage.getItem("user_data"))
  console.log(userData)
  const role = localStorage.getItem("role")
  const [form] = Form.useForm()
  const { uid_lead } = useParams()
  const [warehouses, setWarehouses] = useState([])
  const [masterBin, setMasterBin] = useState([])
  const [termOfPayments, setTermOfPayments] = useState([])
  const [brands, setBrands] = useState([])
  const [loading, setLoading] = useState(false)
  const [contactList, setContactList] = useState([])
  const [salesList, setSalesList] = useState([])
  const [status, setStatus] = useState(0)
  const [detail, setDetail] = useState(null)
  const [showBin, setShowBin] = useState(false)
  const [userAddress, setUserAddress] = useState(null)
  const [selectedAddress, setSelectedAddress] = useState(null)
  const [seletedContact, setSeletedcontact] = useState(null)
  const loadBrand = () => {
    axios.get("/api/master/brand").then((res) => {
      setBrands(res.data.data)
    })
  }

  const loadMasterBin = () => {
    axios.get("/api/master/bin").then((res) => {
      setMasterBin(res.data.data)
    })
  }

  const loadWarehouse = () => {
    axios.get("/api/master/warehouse").then((res) => {
      setWarehouses(res.data.data)
    })
  }

  const loadUserAddress = (id) => {
    axios.get("/api/general/user-with-address/" + id).then((res) => {
      setUserAddress(res.data.data)
    })
  }

  const loadTop = () => {
    axios.get("/api/master/top").then((res) => {
      setTermOfPayments(res.data.data)
    })
  }

  const loadProductDetail = () => {
    setLoading(true)
    axios
      .get(`/api/lead-master/detail/${uid_lead}`)
      .then((res) => {
        const { data } = res.data
        const forms = {
          ...data,
          contact: {
            label: data?.contact_name,
            value: data?.contact_user?.id,
          },
          sales: {
            label: data?.sales_user?.name,
            value: data?.sales_user?.id,
          },
          payment_term: data?.payment_term?.id,
          brand_id: data?.brand_ids,
          expired_at: moment(data?.expired_at ?? new Date(), "YYYY-MM-DD"),
          created_by: data?.created_by_name,
        }

        let newForm = form.getFieldsValue()

        if (role === "sales") {
          forms.sales = {
            label: userData.name,
            value: userData.id,
          }
        }
        setSeletedcontact({
          label: data?.contact_name,
          value: data?.contact_user?.id,
        })
        setDetail(data)
        form.setFieldsValue({ ...forms, ...newForm }) // update to fix suspected form error
        setLoading(false)
      })
      .catch((e) => setLoading(false))
  }

  useEffect(() => {
    loadBrand()
    loadWarehouse()
    loadMasterBin()
    loadTop()

    uid_lead && loadProductDetail()
    handleGetContact()
    handleGetSales()
    form.setFieldValue("created_by", userData?.name)
    if (!uid_lead && role === "sales") {
      form.setFieldValue("sales", {
        label: userData?.name,
        value: userData?.id,
      })
    }
  }, [])

  const handleGetContact = () => {
    searchContact(null).then((results) => {
      const newResult = results.map((result) => {
        return { label: result.nama, value: result.id }
      })
      setContactList(newResult)
    })
  }

  const handleGetSales = () => {
    searchSales(null).then((results) => {
      const newResult = results.map((result) => {
        return { label: result.nama, value: result.id }
      })
      setSalesList(newResult)
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

  const handleSearchSales = async (e) => {
    return searchSales(e).then((results) => {
      const newResult = results.map((result) => {
        return { label: result.nama, value: result.id }
      })
      return newResult
    })
  }

  const onFinish = (values) => {
    let url = uid_lead
      ? "/api/lead-master/update/" + uid_lead
      : "/api/lead-master/create"

    if (!selectedAddress) {
      toast.error("Alamat Belum Dipilih", {
        position: toast.POSITION.TOP_RIGHT,
      })
    }

    axios
      .post(url, {
        ...values,
        status,
        uid_lead,
        contact: values.contact.value,
        sales: values.sales.value,
        account_id: getItem("account_id"),
        address_id: selectedAddress,
        expired_at: values.expired_at.format("YYYY-MM-DD"),
      })
      .then((res) => {
        toast.success(res?.data?.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        return navigate("/lead-master")
      })
      .catch((err) => {
        toast.error("Lead gagal disimpan", {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }

  if (loading) {
    return (
      <Layout title="Lead Master Form" href="/lead-master">
        <LoadingFallback />
      </Layout>
    )
  }

  const isCreate = detail ? false : true

  return (
    <Layout
      title="Create New Lead"
      href="/lead-master"
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
        <Card title="Form New Lead">
          <div className="card-body row">
            <div className="col-md-4">
              <Form.Item
                label="Type Lead"
                name="lead_type"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Type Lead!",
                  },
                ]}
              >
                <Select placeholder="Pilih Type Lead">
                  <Select.Option value={"new"} key={"new"}>
                    New Lead
                  </Select.Option>
                  <Select.Option value={"existing"} key={"existing"}>
                    Existing Lead
                  </Select.Option>
                </Select>
              </Form.Item>
              <Form.Item
                label="Brand"
                name="brand_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Brand!",
                  },
                ]}
              >
                <Select placeholder="Pilih Brand" mode="multiple" allowClear>
                  {brands.map((brand) => (
                    <Select.Option value={brand.id} key={brand.id}>
                      {brand.name}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>
            </div>
            <div className="col-md-4">
              <Form.Item
                label="Contact"
                name="contact"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Contact!",
                  },
                ]}
              >
                <DebounceSelect
                  showSearch
                  placeholder="Cari Contact"
                  fetchOptions={handleSearchContact}
                  filterOption={false}
                  className="w-full"
                  defaultOptions={contactList}
                  // defaultOptions={[]}
                  onChange={(e) => {
                    loadUserAddress(e.value)
                    setSeletedcontact(e)
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
                          <strong className="text-blue-500">
                            + Add Contact
                          </strong>
                        </Button>
                      </div>
                    </>
                  )}
                />
              </Form.Item>
              <Form.Item
                label="Payment Term"
                name="payment_term"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Payment Term!",
                  },
                ]}
              >
                <Select
                  showSearch
                  filterOption={(input, option) => {
                    return (option?.children ?? "")
                      .toLowerCase()
                      .includes(input.toLowerCase())
                  }}
                  placeholder="Pilih Payment Term"
                  onChange={(e) => setShowBin(e === 3 ? true : false)}
                >
                  {termOfPayments.map((top) => (
                    <Select.Option value={top.id} key={top.id}>
                      {top.name}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>
            </div>
            <div className="col-md-4">
              <Form.Item
                label="Sales"
                name="sales"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Sales!",
                  },
                ]}
              >
                <DebounceSelect
                  disabled={role === "sales"}
                  defaultOptions={
                    role === "sales"
                      ? [{ label: userData.name, value: userData.id }]
                      : salesList
                  }
                  showSearch
                  placeholder="Cari Sales"
                  fetchOptions={handleSearchSales}
                  filterOption={false}
                  className="w-full"
                />
              </Form.Item>
              <Form.Item
                label="Warehouse"
                name="warehouse_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Warehouse!",
                  },
                ]}
              >
                <Select
                  showSearch
                  filterOption={(input, option) => {
                    return (option?.children ?? "")
                      .toLowerCase()
                      .includes(input.toLowerCase())
                  }}
                  placeholder="Pilih Warehouse"
                >
                  {warehouses.map((warehouse) => (
                    <Select.Option value={warehouse.id} key={warehouse.id}>
                      {warehouse.name}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>
            </div>

            <div className={showBin ? "col-md-4" : "col-md-6"}>
              <Form.Item label="Expired SO" name="expired_at">
                <DatePicker className="w-full" />
              </Form.Item>
            </div>

            {showBin && (
              <div className={"col-md-4"}>
                <Form.Item
                  label=" Lokasi BIN"
                  name="master_bin_id"
                  rules={[
                    {
                      required: true,
                      message: "Silakan pilih Warehouse!",
                    },
                  ]}
                >
                  <Select placeholder="Pilih Lokasi BIN">
                    {masterBin.map((bin) => (
                      <Select.Option value={bin.id} key={bin.id}>
                        {bin.name}
                      </Select.Option>
                    ))}
                  </Select>
                </Form.Item>
              </div>
            )}

            <div className={showBin ? "col-md-4" : "col-md-6"}>
              <Form.Item label="Created By" name="created_by">
                <Input
                  placeholder="Created By"
                  disabled
                  value={userData.name}
                />
              </Form.Item>
            </div>

            <div className={"col-md-12"}>
              <Form.Item label="Customer Need" name="customer_need">
                <Input placeholder="Ketik Customer Need" />
              </Form.Item>
            </div>
            {/* <div className={"col-md-12"}>
              {seletedContact && (
                <Form.Item
                  label=" Contact Address"
                  name="address_id"
                  rules={[
                    {
                      required: true,
                      message: "Silakan pilih Contact Address!",
                    },
                  ]}
                >
                  <Select
                    placeholder="Pilih Contact Address"
                    dropdownStyle={{ zIndex: 2 }}
                    dropdownRender={(menu) => (
                      <div className="px-2 mx-auto  z-50">
                        {menu}
                        <div className="text-center">
                          <Divider
                            style={{
                              margin: "8px 0",
                            }}
                          />

                          <FormAddressModal
                            initialValues={{
                              user_id: userAddress?.id,
                              nama: userAddress?.name,
                              telepon:
                                userAddress?.telepon || userAddress?.phone,
                            }}
                            refetch={() => loadUserAddress(userAddress?.id)}
                          />
                        </div>
                      </div>
                    )}
                  >
                    {userAddress?.address?.map((bin) => (
                      <Select.Option value={bin.id} key={bin.id}>
                        {bin.alamat_detail}
                      </Select.Option>
                    )) || []}
                  </Select>
                </Form.Item>
              )}
            </div> */}
          </div>
        </Card>
      </Form>

      <Card
        title="Informasi Alamat"
        className="mt-4"
        extra={
          <FormAddressModal
            initialValues={{
              user_id: userAddress?.id,
              nama: userAddress?.name,
              telepon: userAddress?.telepon || userAddress?.phone,
            }}
            refetch={() => loadUserAddress(userAddress?.id)}
          />
        }
      >
        <Table
          dataSource={userAddress?.address || []}
          columns={[
            {
              title: "No.",
              dataIndex: "no",
              key: "no",
              render: (_, record, index) => index + 1,
            },
            {
              title: "Alamat",
              dataIndex: "alamat_detail",
              key: "alamat_detail",
            },
            {
              title: "Pilih",
              dataIndex: "action",
              key: "action",
              render: (_, record) => {
                return (
                  <Switch
                    onChange={(e) => {
                      // if (selectedAddress) {
                      //   return setSelectedAddress(null)
                      // }
                      return setSelectedAddress(record.id)
                    }}
                    checked={selectedAddress == record.id}
                  />
                )
              },
            },
          ]}
          key={"id"}
          pagination={false}
        />
      </Card>

      <div className="float-right">
        <div className="  w-full mt-6 p-4 flex flex-row">
          {isCreate && (
            <button
              onClick={async () => {
                await setStatus(7)
                await form.submit()
              }}
              className={`text-blue bg-white hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 border font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mr-2`}
            >
              <span className="ml-2">Save Draft</span>
            </button>
          )}
          <button
            onClick={async () => {
              await setStatus(0)
              await form.submit()
            }}
            className={`text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center`}
          >
            <span className="ml-2">Save Lead</span>
          </button>
        </div>
      </div>
    </Layout>
  )
}

export default LeadMasterForm
