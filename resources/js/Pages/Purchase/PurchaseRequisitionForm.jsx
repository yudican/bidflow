import { CloseOutlined, LoadingOutlined, PlusOutlined } from "@ant-design/icons"
import { Button, Card, Form, Input, Select, Table } from "antd"
import TextArea from "antd/lib/input/TextArea"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import DebounceSelect from "../../components/atoms/DebounceSelect"
import Layout from "../../components/layout"
import { getItem } from "../../helpers"
import { searchUserApproval, searchUserApprovalPurchasing } from "./services"

const defaultItems = [
  {
    key: 0,
    item_name: null,
    item_qty: 0,
    item_price: null,
    item_unit: "-",
    item_url: null,
    item_tax: 0,
    subtotal: 0,
    item_note: "-",
  },
]

const PurchaseRequisitionForm = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const [formTambahData] = Form.useForm()
  const { purchase_requisition_id } = useParams()

  const [loading, setLoading] = useState(false)
  const [status, setStatus] = useState(0)
  const [productNeed, setProductNeed] = useState(defaultItems)
  const [companyLists, setCompanyList] = useState([])
  const [termOfPayments, setTermOfPayments] = useState([])
  const [brands, setBrands] = useState([])
  const [perlengkapans, setPerlengkapans] = useState([])
  const [roles, setRoles] = useState([])
  const [approvalLists, setApprovalLists] = useState([])
  const [purchasingLists, setPurchasingLists] = useState([])
  const [vendors, setVendors] = useState([])
  const [loadingTax, setLoadingTax] = useState(false)
  const [loadingAtachment, setLoadingAtachment] = useState(false)
  const [fileUrl, setFileUrl] = useState({ attachment: null })
  const [fileList, setFileList] = useState({ attachment: null })
  const [vendorCode, setVendorCode] = useState(null)
  const [packages, setPackages] = useState([])
  const [taxs, setTaxs] = useState([])
  const [productAdditionals, setProductAdditionals] = useState([])

  // modal state
  const [openTambahItem, setOpenTambahItem] = useState(false)

  const loadRole = () => {
    axios.get(`/api/master/role`).then((res) => {
      setRoles(res.data.data)
    })
  }

  const loadBrand = () => {
    axios.get(`/api/master/brand`).then((res) => {
      setBrands(res.data.data)
    })
  }

  const loadVendors = () => {
    axios.get("/api/master/vendors").then((res) => {
      setVendors(res.data.data)
    })
  }

  const loadPackages = () => {
    axios.get("/api/master/package").then((res) => {
      const { data } = res.data
      setPackages(data)
    })
  }

  const loadPerlengkapan = () => {
    axios.get(`/api/general/perlengkapan`).then((res) => {
      setPerlengkapans(res.data.data)
    })
  }

  const loadDetail = () => {
    setLoading(true)
    axios
      .get(`/api/purchase/purchase-requitition/${purchase_requisition_id}`)
      .then((res) => {
        const { data } = res.data
        setLoading(false)
        data.approval_leads.forEach((item) => {
          if (item.label === "Verified by") {
            form.setFieldsValue({
              verified_by: {
                label: item?.user_name,
                value: item?.user_value,
              },
              verified_role_id: item?.role_id,
            })
          }
          // if (item.label === "Approved by") {
          //   form.setFieldsValue({
          //     approved_by: {
          //       label: item?.user_name,
          //       value: item?.user_value,
          //     },
          //     approved_role_id: item?.role_id,
          //   })
          // }
          if (item.label === "Excecuted by") {
            form.setFieldsValue({
              executed_by: {
                label: item?.user_name,
                value: item?.user_value,
              },
              executed_role_id: item?.role_id,
            })
          }
        })
        const forms = {
          ...data,
          received_by: data?.received_by_name,
        }
        const newItems = data?.items.map((item) => {
          return {
            ...item,
            subtotal: item.item_subtotal,
          }
        })
        setProductNeed(newItems)
        form.setFieldsValue(forms)
      })
      .catch((e) => setLoading(false))
  }

  const loadTop = () => {
    axios.get("/api/master/top").then((res) => {
      setTermOfPayments(res.data.data)
    })
  }

  const loadTaxs = () => {
    setLoadingTax(true)
    axios
      .get("/api/master/taxs")
      .then((res) => {
        setLoadingTax(false)
        setTaxs(res.data.data)
      })
      .catch(() => {
        setLoadingTax(false)
      })
  }

  const loadCompanyAccount = () => {
    axios.get("/api/master/company-account").then((res) => {
      setCompanyList(res.data.data)
    })
  }

  // load user
  const handleSearchUserApproval = async (e) => {
    return searchUserApproval(e).then((results) => {
      const newResult = results.map((result) => {
        return { label: result.nama, value: result.id, role_id: result.role_id }
      })

      return newResult
    })
  }

  // purchasing
  const handleSearchUserApprovalPurchasing = async (e) => {
    return searchUserApprovalPurchasing(e).then((results) => {
      const newResult = results.map((result) => {
        return { label: result.nama, value: result.id, role_id: result.role_id }
      })

      return newResult
    })
  }

  const handleGetContactPurchasing = () => {
    searchUserApprovalPurchasing(null).then((results) => {
      const newResult = results.map((result) => {
        return { label: result.nama, value: result.id, role_id: result.role_id }
      })
      setPurchasingLists(newResult)
    })
  }

  // debounced search
  const handleGetContact = () => {
    searchUserApproval(null).then((results) => {
      const newResult = results.map((result) => {
        return { label: result.nama, value: result.id, role_id: result.role_id }
      })
      setApprovalLists(newResult)
    })
  }

  const loadProductAdditionals = (type) => {
    axios.get("/api/master/products/additional/" + type).then((res) => {
      setProductAdditionals(res.data.data)
    })
  }

  const handleAddProductItems = (item) => {
    const items = [...productNeed]
    const lastKey = items[items.length - 1].key
    items.push({ ...item, key: lastKey + 1 })

    setProductNeed(items)
  }

  const handleRemoveProductItems = (key) => {
    const items = [...productNeed]
    const newItems = items.filter((val, index) => index !== key)

    newItems.forEach((value) => {
      form.setFieldValue(["items", key, "item_name"], value.item_name)
      form.setFieldValue(["items", key, "item_qty"], value.item_qty)
      form.setFieldValue(["items", key, "item_url"], value.item_url)
      form.setFieldValue(["items", key, "item_note"], value.item_note)
    })

    setProductNeed(newItems)
  }

  const handleChangeItems = (name, value, key) => {
    const items = [...productNeed]
    const item = items.find((val) => val.key === key)
    item[name] = value
    setProductNeed(items)
  }

  useEffect(() => {
    if (purchase_requisition_id) {
      loadDetail()
    }
    loadProductAdditionals("perlengkapan")
    // loadCompanyAccount()
    handleGetContact()
    handleGetContactPurchasing()
    // loadTop()
    loadRole()
    loadBrand()
    loadPackages()
    loadVendors()
    loadTaxs()
    loadPerlengkapan()
    form.setFieldsValue({
      currency_id: "Rp",
      company_account_id: getItem("account_id"),
    })
  }, [purchase_requisition_id])

  const onFinish = (values) => {
    setLoading(true)

    if (!productNeed || productNeed.length === 0) {
      setLoading(false)
      toast.error("Item tidak boleh kosong")
      return
    }

    const approvals = [
      {
        user_id: values?.verified_by?.value,
        role_id: values?.verified_role_id,
        status: 0,
        label: "Verified by",
      },
      {
        user_id: "212ae8ee-7e7f-4b19-9444-4493c7ca0764",
        role_id: "ea612622-9bcc-49e9-8b0a-c56974941143",
        status: 0,
        label: "Approved by",
      },
      {
        user_id: "08dcf330-08b5-4d21-932c-805be9b41443",
        role_id: "ea612622-9bcc-49e9-8b0a-c56974941143",
        status: 0,
        label: "Approved by",
      },
      {
        user_id: "916e3abf-e08d-401c-b53b-e07e55c9ddde",
        role_id: "5e81f800-c326-474c-9209-029918a8282b",
        status: 0,
        label: "Approved by",
      },
      {
        user_id: values?.executed_by?.value,
        role_id: values?.executed_role_id,
        status: 0,
        label: "Excecuted by",
      },
    ]

    delete values.verified_by
    // delete values.approved_by
    delete values.executed_by
    delete values.verified_role_id
    delete values.approved_role_id
    delete values.executed_role_id

    const formData = new FormData()
    if (fileList.attachment) {
      formData.append("attachment", fileList.attachment)
    }

    for (let key in values) {
      if (values.hasOwnProperty(key)) {
        formData.append(key, values[key])
      }
    }
    formData.append("approvals", JSON.stringify(approvals))
    formData.append("items", JSON.stringify(productNeed))
    formData.append("status", status)
    formData.append("received_by", "b37a6213-bf64-4f13-bb48-43545cd5ae9e")

    const url = purchase_requisition_id
      ? `/save/${purchase_requisition_id}`
      : "/save"
    axios
      .post(`/api/purchase/purchase-requitition${url}`, formData)
      .then((res) => {
        setLoading(false)
        toast.success("Data berhasil disimpan")
        navigate("/purchase/purchase-requisition")
      })
      .catch((e) => {
        setLoading(false)
        toast.error("Data gagal disimpan")
      })
  }
  return (
    <Layout
      title="Tambah Data Purchase Requisition"
      href="/purchase/purchase-requisition"
      // rightContent={rightContent}
    >
      <Form
        form={form}
        name="basic"
        layout="vertical"
        onFinish={onFinish}
        // onFinishFailed={onFinishFailed}
        autoComplete="off"
      >
        <Card
          title="Informasi Purchase Requisition"
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
                Draft
              </Button>
            </div>
          }
        >
          <div className="row">
            {/* <div className="col-md-12">
              <Form.Item
                label="Account"
                name="company_account_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Account!",
                  },
                ]}
              >
                <Select placeholder="Silakan pilih">
                  {companyLists.map((company) => (
                    <Select.Option key={company?.id} value={`${company.id}`}>
                      {company.account_name}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>
            </div> */}
            <div className="col-md-6">
              {/* <Form.Item
                label="Vendor Code"
                name="vendor_code"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Vendor Code!",
                  },
                ]}
              >
                <Select
                  showSearch
                  filterOption={(input, option) => {
                    return (option?.label ?? "")
                      .toLowerCase()
                      .includes(input.toLowerCase())
                  }}
                  className="w-full"
                  placeholder="Pilih vendor code"
                  onChange={(value) => {
                    const vendor = vendors.find((item) => item.code === value)
                    form.setFieldsValue({
                      vendor_code: value,
                      vendor_name: vendor.name,
                    })
                    setShowSelect(false)
                  }}
                  dropdownRender={(menu) => (
                    <>
                      {menu}
                      <Divider
                        style={{
                          margin: "8px 0",
                        }}
                      />
                      <Space
                        style={{
                          padding: "0 8px 4px",
                        }}
                      >
                        <Input
                          placeholder="Please enter item"
                          value={vendorCode}
                          onChange={(e) => setVendorCode(e.target.value)}
                          className="w-full"
                        />
                        <Button
                          type="text"
                          icon={<PlusOutlined />}
                          onClick={() => {
                            form.setFieldsValue({
                              vendor_code: vendorCode,
                              vendor_name: null,
                            })
                            setShowSelect(false)
                          }}
                        >
                          Add item
                        </Button>
                      </Space>
                    </>
                  )}
                  options={vendors.map((vendor) => {
                    return {
                      value: vendor.code,
                      label: vendor.code,
                    }
                  })}
                />
              </Form.Item> */}

              {/* <Form.Item
                label="Payment Term"
                name="payment_term_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Payment Term!",
                  },
                ]}
              >
                <Select placeholder="Silakan pilih">
                  {termOfPayments.map((top) => (
                    <Select.Option key={top.id} value={top.id}>
                      {top.name}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item> */}
            </div>
            <div className="col-md-6">
              {/* <Form.Item
                label="Vendor Name"
                name="vendor_name"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Vendor Name!",
                  },
                ]}
              >
                <Input placeholder="Silakan input vendor name.." />
              </Form.Item> */}
              {/* <Form.Item
                label="Currency ID"
                name="currency"
                rules={[
                  {
                    required: false,
                    message: "Silakan masukkan Currency ID!",
                  },
                ]}
              >
                <Input
                  placeholder="Silakan input.."
                  defaultValue={"Rp"}
                  disabled
                />
              </Form.Item> */}
            </div>
            <div className="col-md-6">
              <Form.Item
                label="Request by"
                name="request_by_name"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Request By!",
                  },
                ]}
              >
                <Input placeholder="Silakan input request name.." />
              </Form.Item>
              <Form.Item
                label="Request Division"
                name="request_by_division"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Division!",
                  },
                ]}
              >
                <Input placeholder="Silakan input Request Division.." />
              </Form.Item>
              <Form.Item
                label="Range Harga"
                name="range_harga"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Range Harga!",
                  },
                ]}
              >
                <Select placeholder="Silakan pilih">
                  <Select.Option value={"> 1.000.000"}>
                    {"> 1.000.000"}
                  </Select.Option>
                  <Select.Option value={"< 1.000.000"}>
                    {"< 1.000.000"}
                  </Select.Option>
                </Select>
              </Form.Item>
            </div>
            <div className="col-md-6">
              <Form.Item
                label="Request Email"
                name="request_by_email"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Email!",
                  },
                  {
                    type: "email",
                    message: "Email tidak valid!",
                  },
                ]}
              >
                <Input placeholder="Silakan input Request Email.." />
              </Form.Item>
              <Form.Item
                label="Brand ID"
                name="brand_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Brand ID!",
                  },
                ]}
              >
                <Select placeholder="Silakan pilih">
                  {brands.map((brand) => (
                    <Select.Option key={brand.id} value={brand.id}>
                      {brand.name}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>
            </div>

            <div className="col-md-12">
              <Form.Item
                requiredMark={"optional"}
                label="Notes"
                name="request_note"
                rules={[
                  {
                    required: false,
                    message: "Silakan masukkan notes!",
                  },
                ]}
              >
                <TextArea
                  placeholder="Silakan input catatan.."
                  showCount
                  maxLength={100}
                  rows={3}
                />
              </Form.Item>
            </div>
          </div>
        </Card>

        <Card title="Informasi Approval">
          <div className="card-body grid md:grid-cols-2 gap-4">
            <Form.Item
              label="Atasan Divisi"
              name="verified_by"
              rules={[
                {
                  required: true,
                  message: "Silakan pilih Atasan Divisi!",
                },
              ]}
            >
              <DebounceSelect
                showSearch
                placeholder="Cari Atasan Divisi"
                fetchOptions={handleSearchUserApproval}
                filterOption={false}
                className="w-full"
                defaultOptions={approvalLists}
                onChange={(e) => {
                  const role = approvalLists.find(
                    (item) => item.value === e.value
                  )
                  form.setFieldValue("verified_role_id", role?.role_id)
                }}
              />
            </Form.Item>
            <Form.Item
              label="Role (Automatic)"
              name="verified_role_id"
              rules={[
                {
                  required: true,
                  message: "Silakan pilih Role!",
                },
              ]}
            >
              <Select disabled placeholder="Pilih Role">
                {roles.map((role) => (
                  <Select.Option value={role.id} key={role.id}>
                    {role.role_name}
                  </Select.Option>
                ))}
              </Select>
            </Form.Item>
            <Form.Item
              label="By Finance"
              name="approved_by"
              rules={[
                {
                  required: false,
                  message: "Silakan pilih By Finance!",
                },
              ]}
            >
              <Input placeholder="Tenawati" value="Tenawati" disabled />
            </Form.Item>
            <Form.Item
              label="Role (Automatic)"
              name="approved_role_id"
              rules={[
                {
                  required: false,
                  message: "Silakan pilih Role!",
                },
              ]}
            >
              <Select disabled placeholder="Finance" value={1}>
                <Select.Option value={1} key={1}>
                  Finance
                </Select.Option>
              </Select>
            </Form.Item>

            <Form.Item
              label="By BoD"
              name="approved_by"
              rules={[
                {
                  required: false,
                  message: "Silakan masukkan By BoD!",
                },
              ]}
            >
              <Input
                placeholder="Edward Jogia, Dennis Hadi"
                value="Edward Jogia, Dennis Hadi"
                disabled
              />
            </Form.Item>
            <Form.Item
              label="Role (Automatic)"
              name="approved_role_id"
              rules={[
                {
                  required: false,
                  message: "Silakan pilih Role!",
                },
              ]}
            >
              <Select disabled placeholder="BOD" value={1}>
                <Select.Option value={1} key={1}>
                  BOD
                </Select.Option>
              </Select>
            </Form.Item>

            <Form.Item
              label="Purchasing"
              name="executed_by"
              // rules={[
              //   {
              //     // required: true,
              //     message: "Silakan pilih Purchasing!",
              //   },
              // ]}
            >
              <DebounceSelect
                showSearch
                placeholder="Cari Purchasing"
                fetchOptions={handleSearchUserApprovalPurchasing}
                filterOption={false}
                className="w-full"
                defaultOptions={purchasingLists}
                onChange={(e) => {
                  const role = purchasingLists.find(
                    (item) => item.value === e.value
                  )
                  form.setFieldValue("executed_role_id", role?.role_id)
                }}
              />
            </Form.Item>
            <Form.Item
              label="Role (Automatic)"
              name="executed_role_id"
              // rules={[
              //   {
              //     // required: true,
              //     message: "Silakan pilih Role!",
              //   },
              // ]}
            >
              <Select disabled placeholder="Pilih Role">
                {roles.map((role) => (
                  <Select.Option value={role.id} key={role.id}>
                    {role.role_name}
                  </Select.Option>
                ))}
              </Select>
            </Form.Item>
          </div>
        </Card>

        {/* <Card title="Informasi Penerimaan Item">
          <div className="card-body grid md:grid-cols-2 gap-4">
            <Form.Item
              label="Contact"
              name="received_by"
              rules={[
                {
                  required: true,
                  message: "Silakan masukkan Contact!",
                },
              ]}
            >
              <Input placeholder="Salni GA" value="Salni GA" disabled />
            </Form.Item>

            <Form.Item
              label="Role (Automatic)"
              name="role_contact_id"
              rules={[
                {
                  required: false,
                  message: "Silakan pilih Role!",
                },
              ]}
            >
              <Select disabled placeholder="Pilih Role">
                <Select.Option value={1} key={1}>
                  HRGA
                </Select.Option>
              </Select>
            </Form.Item>

            {/* <div className="md:col-span-2">
              <Form.Item
                requiredMark={"Automatic"}
                label="Detail Alamat Penerima"
                name="received_address"
                rules={[
                  {
                    required: false,
                    message: "Silakan masukkan alamat!",
                  },
                ]}
              >
                <TextArea
                  placeholder="Silakan input catatan.."
                  showCount
                  maxLength={100}
                />
              </Form.Item>
            </div>

            <Form.Item
              label="Attachment"
              name="attachment"
              rules={[
                {
                  required: false,
                  message: "Please Attachment!",
                },
              ]}
            >
              <Upload
                name="attachments"
                showUploadList={false}
                multiple={false}
                beforeUpload={() => false}
                onChange={(e) => {
                  handleChange({
                    ...e,
                    field: "attachment",
                  })
                }}
              >
                {fileUrl.attachment ? (
                  loadingAtachment.attachment ? (
                    <LoadingOutlined />
                  ) : (
                    <Button icon={<LinkOutlined />}>
                      <span>{fileUrl?.original_file_name}</span>
                    </Button>
                  )
                ) : (
                  <Button
                    icon={<UploadOutlined />}
                    loading={loadingAtachment.attachment}
                  >
                    Upload
                  </Button>
                )}
              </Upload>
            </Form.Item> 
          </div>
        </Card> */}

        <Card title={`Detail Item`}>
          <Table
            dataSource={productNeed}
            columns={[
              {
                title: "Nama Item",
                dataIndex: "item_name",
                key: "item_name",
                render: (value, item, index) => {
                  return (
                    <Form.Item
                      label="Nama Item"
                      name={["items", index, "item_name"]}
                      rules={[
                        { required: true, message: "Nama Item wajib diisi!" },
                      ]}
                    >
                      <Select
                        placeholder="Nama Item.."
                        onChange={(value) =>
                          handleChangeItems("item_name", value, item.key)
                        }
                      >
                        {productAdditionals &&
                          productAdditionals.map((row) => (
                            <Select.Option key={row.id} value={row.id}>
                              {row.name}
                            </Select.Option>
                          ))}
                      </Select>
                    </Form.Item>
                  )
                },
              },
              {
                title: "Qty",
                dataIndex: "item_qty",
                key: "item_qty",
                render: (value, item, index) => {
                  return (
                    <Form.Item
                      label="Qty"
                      name={["items", index, "item_qty"]}
                      rules={[{ required: true, message: "Qty wajib diisi!" }]}
                    >
                      <Input
                        // type="number"
                        placeholder="Qty"
                        onChange={(e) => {
                          let value = e.target.value
                          // Hanya mengizinkan angka dan mencegah 0 di depan kecuali ada angka lain
                          if (value === "0") {
                            value = "" // Jangan izinkan input 0 tunggal
                          } else {
                            value = value.replace(/[^0-9]/g, "") // Mengizinkan semua angka
                          }

                          form.setFieldValue(
                            ["items", index, "item_qty"],
                            value
                          )
                          handleChangeItems("item_qty", value, item.key)
                        }}
                      />
                    </Form.Item>
                  )
                },
              },
              {
                title: "Url",
                dataIndex: "item_url",
                key: "item_url",
                render: (value, item, index) => {
                  return (
                    <Form.Item
                      label="Url"
                      name={["items", index, "item_url"]}
                      rules={[
                        { required: true, message: "URL wajib diisi!" },
                        { type: "url", message: "URL tidak valid!" },
                      ]}
                    >
                      <Input
                        placeholder="https://domain.com"
                        value={item.item_url}
                        onChange={(e) =>
                          handleChangeItems(
                            "item_url",
                            e.target.value,
                            item.key
                          )
                        }
                      />
                    </Form.Item>
                  )
                },
              },
              {
                title: "Notes",
                dataIndex: "item_note",
                key: "item_note",
                render: (value, item, index) => {
                  return (
                    <Form.Item
                      label="Notes"
                      name={["items", index, "item_note"]}
                    >
                      <Input
                        placeholder="Notes"
                        onChange={(e) =>
                          handleChangeItems(
                            "item_note",
                            e.target.value,
                            item.key
                          )
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
                render: (value, item, index) => {
                  return (
                    <button
                      disabled={productNeed?.length < 2}
                      onClick={() => handleRemoveProductItems(index)}
                      type={"button"}
                      className={`
                    text-white text-sm font-medium text-center 
                      ${
                        productNeed?.length < 2
                          ? "bg-gray-700 hover:bg-gray-800"
                          : "bg-red-700 hover:bg-red-800"
                      }
                      focus:ring-4 focus:outline-none focus:ring-red-300 
                      px-4 py-2 rounded-lg 
                      inline-flex items-center
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
              const lastData = productNeed[productNeed.length - 1]
              handleAddProductItems({
                key: lastData.key + 1,
                item_name: null,
                item_qty: null,
                item_price: null,
                item_unit: "-",
                item_url: null,
                item_tax: 0,
                subtotal: 0,
                item_note: "-",
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

        <div className="flex justify-end my-6">
          <button
            onClick={() => {
              setStatus(5)
              setTimeout(() => {
                form.submit()
              }, 1000)
            }}
            type="button"
            className={`text-blue-700 bg-white border hover:bg-black focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center `}
            disabled={loading}
          >
            {loading ? (
              <LoadingOutlined />
            ) : (
              <span className="">Simpan Sebagai Draft</span>
            )}
          </button>

          <button
            onClick={() => {
              setStatus(0)
              setTimeout(() => {
                form.submit()
              }, 1000)
            }}
            type="button"
            className={`ml-4 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center`}
            disabled={loading}
          >
            {loading ? <LoadingOutlined /> : <span className="">Simpan</span>}
          </button>
        </div>
      </Form>
    </Layout>
  )
}

export default PurchaseRequisitionForm
