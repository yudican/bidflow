import { LoadingOutlined, UploadOutlined } from "@ant-design/icons"
import { Button, Card, DatePicker, Form, Input, Select, Upload } from "antd"
import TextArea from "antd/lib/input/TextArea"
import axios from "axios"
import moment from "moment"
import React, { useEffect, useState } from "react"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import CheckbookModal from "../../components/Modal/CheckbookModal"
import Layout from "../../components/layout"
import {
  formatPhone,
  getBase64,
  getInitials,
  handleString,
  validateEmail,
  validatePhoneNumber,
} from "../../helpers"

const FormContact = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const params = useParams()
  const role = localStorage.getItem("role")
  const [brands, setBrands] = useState([])
  const [roles, setRoles] = useState([])
  const [contact, setContact] = useState([])
  const [bussinessEntity, setBussinnesEntity] = useState([])
  const [message, setMessage] = useState("")
  const [initialName, setInitialName] = useState(null)
  const [needFaktur, setNeedFaktur] = useState(false)
  const [checkbook, setCheckbook] = useState(null)
  const [loadingSaveContact, setLoadingSaveContact] = useState(false)
  const [roleSelected, setRoleSelected] = useState(null)
  const [loading, setLoading] = useState({
    file_nib: false,
  })

  const [imageUrl, setImageUrl] = useState({
    file_nib: null,
    file_name: null,
  })

  const [fileList, setFileList] = useState({
    file_nib: null,
  })

  const loadBrand = () => {
    axios.get("/api/master/brand").then((res) => {
      setBrands(res.data.data)
    })
  }

  const loadDetailContact = () => {
    axios.get(`/api/contact/detail/${params?.user_id}`).then((res) => {
      const { data } = res.data
      setContact(data)
      setRoleSelected(data?.role?.role_type)
      setCheckbook({
        value: data?.checkbook?.bank_name,
        checkbook_id: data?.checkbook?.id,
      })
      setNeedFaktur(handleString(data?.company?.need_faktur) > 0)
      form.setFieldsValue({
        ...data,
        telepon: formatPhone(data?.telepon),
        bod: moment(data.bod ?? new Date(), "YYYY-MM-DD"),
        role_id: data?.role?.id,
        brand_id:
          data?.brand_ids?.length > 0 ? data?.brand_ids : [data?.brand_id],
        company_name: handleString(data?.company?.name, null),
        company_email: handleString(data?.company?.email, null),
        company_telepon: formatPhone(handleString(data?.company?.phone, null)),
        business_entity: data?.company?.business_entity?.id,
        owner_name: handleString(data?.company?.owner_name, null),
        pic_name: handleString(data?.company?.pic_name, null),
        owner_phone: formatPhone(
          handleString(data?.company?.owner_phone, null)
        ),
        pic_phone: formatPhone(handleString(data?.company?.pic_phone, null)),
        company_address: handleString(data?.company?.address, null),
        layer_type: handleString(data?.company?.layer_type, null),
        npwp: handleString(data?.company?.npwp, null),
        nik: handleString(data?.company?.nik, null),
        npwp_name: handleString(data?.company?.npwp_name, null),
        nib: handleString(data?.company?.nib, null),
        need_faktur: handleString(data?.company?.need_faktur, null),
      })
    })
  }

  const loadBussinnesEntity = () => {
    axios.get("/api/master/bussiness-entity").then((res) => {
      setBussinnesEntity(res.data.data)
    })
  }

  const loadRole = () => {
    axios.get(`/api/master/role/${role}`).then((res) => {
      setRoles(res.data.data)
    })
  }

  const handleChange = ({ fileList, file, field }) => {
    const list = fileList.pop()
    setLoading({ ...loading, [field]: true })
    setTimeout(() => {
      const size = list.size / 1024
      if (size > 1024) {
        setLoading({ ...loading, [field]: false })
        return toast.error("Maksimum ukuran file adalah 1 MB")
      }
      getBase64(list.originFileObj, (url) => {
        setLoading({ ...loading, [field]: false })
        setImageUrl({ ...imageUrl, [field]: url, file_name: file.name })
      })
      setFileList({ ...fileList, [field]: list.originFileObj })
    }, 1000)
  }

  useEffect(() => {
    if (params?.user_id) {
      loadDetailContact()
    } else {
      form.setFieldsValue({
        need_faktur: "0",
      })
    }
    loadBrand()
    loadBussinnesEntity()
    loadRole()
  }, [])

  const onFinish = (values) => {
    let formData = new FormData()
    if (fileList.file_nib) {
      formData.append("file_nib", fileList.file_nib)
    }

    if (params?.user_id) {
      formData.append("user_id", params?.user_id)
    }

    formData.append("bod", values.bod.format("YYYY-MM-DD"))
    formData.append("sales_channel", JSON.stringify(values.sales_channels))
    formData.append("name", values.name || null)
    formData.append("uid", values.uid || null)
    formData.append("appendix", values.appendix || null)
    formData.append("telepon", values.telepon || null)
    formData.append("email", values.email || null)
    formData.append("gender", values.gender || null)
    formData.append("brand_id", JSON.stringify(values.brand_id) || [])
    formData.append("role_id", values.role_id || null)
    formData.append("layer_type", values.layer_type || null)
    formData.append("company_name", values.company_name || null)
    formData.append("company_email", values.company_email || null)
    formData.append("nik", values.nik || "0000000000000000")
    formData.append("npwp", values.npwp || "0000000000000000")
    formData.append("npwp_name", values.npwp_name || null)
    formData.append("company_telepon", values.company_telepon || null)
    formData.append("business_entity", values.business_entity || null)
    formData.append("owner_name", values.owner_name || null)
    formData.append("owner_phone", values.owner_phone || null)
    formData.append("nib", values.nib || null)
    formData.append("pic_name", values.pic_name || null)
    formData.append("pic_phone", values.pic_phone || null)
    formData.append("company_address", values.company_address || null)
    formData.append("need_faktur", values.need_faktur || null)
    formData.append(
      "checkbook_id",
      checkbook?.checkbook_id || values.checkbook_id || null
    )
    initialName && formData.append("initialName", initialName)
    setLoadingSaveContact(true)
    axios
      .post("/api/contact/save-contact", formData)
      .then((res) => {
        setMessage(res.data.message)
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })

        setLoadingSaveContact(false)
        return navigate("/contact")
      })
      .catch((err) => {
        const { message, type } = err.response.data
        setLoadingSaveContact(false)
        if (type === "company_email") {
          form.setFields([
            {
              name: "company_email",
              errors: ["company email has been registered"],
            },
          ])
        }

        if (type === "company_name") {
          form.setFields([
            {
              name: "company_name",
              errors: ["company name has been registered"],
            },
          ])
        }
        toast.error(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }

  const onFinishFailed = (errorInfo) => {
    console.log(errorInfo)
  }
  console.log(form.getFieldsError())
  return (
    <Layout
      title="Tambah Contact Baru"
      href="/contact"
      rightContent={
        <Button
          loading={loadingSaveContact}
          type="primary"
          htmlType="submit"
          onClick={() => form.submit()}
        >
          Save Contact
        </Button>
      }
    >
      <Form
        form={form}
        name="basic"
        layout="vertical"
        onFinish={onFinish}
        onFinishFailed={onFinishFailed}
        onError={onFinishFailed}
        autoComplete="off"
      >
        <Card title="User Info">
          <div className="card-body row">
            <div className="col-md-8">
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
                <Input
                  placeholder="Masukkan Nama Lengkap "
                  onChange={(e) => {
                    const { value } = e.target
                    setInitialName(getInitials(value))
                    form.setFieldValue("uid", getInitials(value) + "-23001")
                  }}
                />
              </Form.Item>
              <Form.Item
                label="Telepon"
                name="telepon"
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
                <Input placeholder="Masukkan No Telepon" />
              </Form.Item>
            </div>
            <div className="col-md-4">
              <Form.Item
                label="Customer Code"
                name="uid"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Customer Code!",
                  },
                ]}
              >
                <Input placeholder="Masukkan Customer Code" />
              </Form.Item>
            </div>
            <div className="col-md-12">
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
                <Input placeholder="Masukkan Email" />
              </Form.Item>
            </div>
            <div className="col-md-4">
              <Form.Item
                label="Jenis Kelamin"
                name="gender"
                rules={[
                  {
                    required: false,
                    message: "Silakan masukkan Jenis Kelamin!",
                  },
                ]}
              >
                <Select placeholder="Pilih Jenis Kelamin">
                  <Select.Option value="Laki-Laki">Laki-Laki</Select.Option>
                  <Select.Option value="Perempuan">Perempuan</Select.Option>
                </Select>
              </Form.Item>
            </div>
            <div className="col-md-4">
              <Form.Item
                label="Birth of Date"
                name="bod"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Birth of Date!",
                  },
                ]}
              >
                <DatePicker
                  className="w-full"
                  format={"DD-MM-YYYY"}
                  placeholder="Pilih Date"
                />
              </Form.Item>
            </div>
            <div className="col-md-4">
              <Form.Item
                label="Brand"
                name="brand_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Brand!",
                  },
                ]}
              >
                <Select placeholder="Pilih Brand" mode="multiple">
                  {brands.map((brand) => (
                    <Select.Option value={brand.id} key={brand.id}>
                      {brand.name}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>
            </div>
            <div className="col-md-6">
              <Form.Item
                label="Role"
                name="role_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Role!",
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
                  placeholder="Pilih Role"
                  onChange={(e) => {
                    const role = roles.find((role) => role.id === e)
                    setRoleSelected(role.role_type)
                  }}
                >
                  {roles.map((role) => (
                    <Select.Option value={role.id} key={role.id}>
                      {role.role_name}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>
            </div>
            {roleSelected === "agent" && (
              <Form.Item
                label="Type Layer"
                name="layer_type"
                rules={[
                  {
                    required: true,
                    message: "Pilih Type Layer!",
                  },
                ]}
              >
                <div className="col-md-12">
                  <Select placeholder="Pilih Type Layer">
                    <Select.Option value={"distributor"}>
                      Main Distributor
                    </Select.Option>
                    <Select.Option value={"sub-distributor"}>
                      Sub Distributor
                    </Select.Option>
                  </Select>
                </div>
              </Form.Item>
            )}
            <div className="col-md-3">
              <Form.Item
                label="Sales Tag"
                name="sales_channels"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan sales tag",
                  },
                ]}
              >
                <Select
                  mode="multiple"
                  allowClear
                  className="w-full mb-2"
                  placeholder="Pilih Sales Channel"
                  showSearch
                  optionFilterProp="children"
                  filterOption={(input, option) =>
                    (option?.children?.toLowerCase() ?? "").includes(
                      input.toLowerCase()
                    )
                  }
                >
                  <Select.Option value={"karyawan"}>Karyawan</Select.Option>
                  <Select.Option value={"endorsement"}>
                    Endorsement
                  </Select.Option>
                  <Select.Option value={"corner"}>Corner</Select.Option>
                  <Select.Option value={"mtp"}>
                    MTP (Management Training Program)
                  </Select.Option>
                  <Select.Option value={"agent-portal"}>
                    Agent Portal
                  </Select.Option>
                  <Select.Option value={"distributor"}>
                    Distributor
                  </Select.Option>
                  <Select.Option value={"super-agent"}>
                    Super Agent
                  </Select.Option>
                  <Select.Option value={"modern-store"}>
                    Modern Store
                  </Select.Option>
                  <Select.Option value={"e-store"}>E-Store</Select.Option>
                </Select>
              </Form.Item>
            </div>
            <div className="col-md-3">
              <Form.Item
                label="Checkbook"
                name="checkbook_id"
                rules={[
                  {
                    required: false,
                    message: "Silakan masukkan Checkbook!",
                  },
                ]}
              >
                <>
                  <Input hidden />
                  <CheckbookModal
                    handleOk={(item) => {
                      setCheckbook(item)
                      form.setFieldValue("checkbook_id", item.checkbook_id)
                    }}
                    checkbook={checkbook?.value}
                  />
                </>
              </Form.Item>
            </div>
          </div>
        </Card>
        <Card title="Company Info" className="mt-2">
          <div className="card-body row">
            <div className="col-md-6">
              <Form.Item
                label="Owner Name"
                name="owner_name"
                rules={[
                  {
                    required: false,
                    message: "Masukkan Owner Name!",
                  },
                ]}
              >
                <Input placeholder="Masukkan Owner Name" />
              </Form.Item>
              <Form.Item
                label="Company Name"
                name="company_name"
                rules={[
                  {
                    required: false,
                    message: "Silakan masukkan Company Name!",
                  },
                ]}
              >
                <Input placeholder="Masukkan Company Name" />
              </Form.Item>
              <Form.Item
                label="Company Telepon"
                name="company_telepon"
                rules={[
                  {
                    required: false,
                    message: "Silakan masukkan Company Telepon!",
                  },
                  {
                    validator: (_, value) => {
                      if (value) {
                        if (
                          value != "-" ||
                          value != "null" ||
                          value != " " ||
                          value != ""
                        ) {
                          return validatePhoneNumber(value)
                        }
                      }
                      return Promise.resolve()
                    },
                  },
                ]}
              >
                <Input
                  placeholder="Masukkan Company Telepon"
                  onChange={(e) => {
                    const { value } = e.target
                    form.setFieldValue("company_telepon", value)
                  }}
                />
              </Form.Item>
              <Form.Item label="NIB" name="nib">
                <Input placeholder="Masukkan NIB" />
              </Form.Item>
            </div>
            <div className="col-md-6">
              <Form.Item
                label="Owner Phone"
                name="owner_phone"
                rules={[
                  {
                    required: false,
                    message: "Ketika Owner Phone!",
                  },
                ]}
              >
                <Input placeholder="Masukkan Owner Phone" />
              </Form.Item>
              <Form.Item
                label="Company Email"
                name="company_email"
                rules={[
                  {
                    required: false,
                    message: "Silakan masukkan Company Email!",
                  },
                  {
                    validator: (_, value) => {
                      if (value) {
                        if (value != "-" || value != "null" || value != " ") {
                          return validateEmail(value)
                        }
                      }
                      return Promise.resolve()
                    },
                  },
                  // {
                  //   message: "company email has been registered",
                  //   validator: (_, value) => {
                  //     if (message === "Company Email sudah terdaftar") {
                  //       return Promise.resolve()
                  //     } else {
                  //       return Promise.reject("Some message here")
                  //     }
                  //   },
                  // },
                ]}
              >
                <Input placeholder="Masukkan Company Email" />
              </Form.Item>
              <Form.Item
                label="Business Entity"
                name="business_entity"
                rules={[
                  {
                    required: false,
                    message: "Silakan masukkan Business Entity!",
                  },
                ]}
              >
                <Select placeholder="Pilih Business Entity">
                  {bussinessEntity.map((be) => (
                    <Select.Option value={be.id} key={be.id}>
                      {be.title}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>
              <Form.Item
                label="File NIB"
                name="file_nib"
                rules={[
                  {
                    required: false,
                    message: "Silakan masukkan file_nib!",
                  },
                ]}
              >
                <Upload
                  name="file_nib"
                  // listType="picture-card"
                  // className="avatar-uploader w-100"
                  showUploadList={false}
                  multiple={false}
                  beforeUpload={() => false}
                  onChange={(e) =>
                    handleChange({
                      ...e,
                      field: "file_nib",
                    })
                  }
                >
                  {/* {imageUrl.file_nib ? (
                    loading.file_nib ? (
                      <LoadingOutlined />
                    ) : (
                      <img
                        src={imageUrl.file_nib}
                        alt="avatar"
                        style={{
                          height: 104,
                        }}
                      />
                    )
                  ) : ( */}
                  <div className="cursor-pointer ant-upload">
                    <Input
                      value={imageUrl.file_name}
                      readOnly
                      contentEditable={false}
                      placeholder="Attachment"
                      suffix={
                        loading.file_nib ? (
                          <LoadingOutlined />
                        ) : (
                          <UploadOutlined />
                        )
                      }
                    />
                  </div>
                  {/* // <div style={{ width: "100%" }}>
                    //   {loading.file_nib ? (
                    //     <LoadingOutlined />
                    //   ) : (
                    //     <PlusOutlined />
                    //   )}
                    //   <div
                    //     style={{
                    //       marginTop: 8,
                    //       width: "100%",
                    //     }}
                    //   >
                    //     Upload
                    //   </div>
                    // </div>
                  )} */}
                </Upload>
              </Form.Item>
            </div>

            <div className="col-md-6">
              <Form.Item
                label="Butuh Faktur Pajak ?"
                name="need_faktur"
                rules={[
                  {
                    required: false,
                    message: "Silakan pilih!",
                  },
                ]}
              >
                <Select
                  placeholder="Pilih Option"
                  onChange={(value) => setNeedFaktur(value > 0 ? true : false)}
                >
                  <Select.Option value={"0"} key="0">
                    Tidak
                  </Select.Option>
                  <Select.Option value={"1"} key="1">
                    Ya
                  </Select.Option>
                </Select>
              </Form.Item>
            </div>
            <div className="col-md-6">
              <Form.Item
                label="Appendix"
                name="appendix"
                rules={[
                  {
                    required: needFaktur,
                    message: "Silakan pilih!",
                  },
                ]}
              >
                <Select placeholder="Pilih Appendix">
                  <Select.Option value={"01"}>
                    01 - Kepada pihak yang bukan pemungut PPN
                  </Select.Option>
                  <Select.Option value={"02"}>
                    02 - Kepada pemungut bendaharawan
                  </Select.Option>
                  <Select.Option value={"03"}>
                    03 - Kepada pemungut selain bendaharawan
                  </Select.Option>
                  <Select.Option value={"04"}>
                    04 - DPP nilai lain-lain
                  </Select.Option>
                  <Select.Option value={"06"}>
                    06 - Penyerahan lainnya, termasuk penyerahan kepada turis
                    dalam rangka VAT refund
                  </Select.Option>
                  <Select.Option value={"07"}>
                    07 - Penyerahan yang PPN-nya tidak dipungut *
                  </Select.Option>
                  <Select.Option value={"08"}>
                    08 - Penyerahan yang PPN-nya dibebaskan *
                  </Select.Option>
                  <Select.Option value={"09"}>
                    09 - Penyerahan aktiva (Pasal 16D UU PPN)
                  </Select.Option>
                </Select>
              </Form.Item>
            </div>
            <div className="col-md-4">
              <Form.Item
                label="Nama NPWP"
                name="npwp_name"
                rules={[
                  () => ({
                    required: needFaktur,
                    message:
                      "Nama NPWP wajib diisi jika membutuhkan faktur pajak",
                  }),
                ]}
              >
                <Input placeholder="Masukkan Nama NPWP" className="w-full" />
              </Form.Item>
            </div>
            <div className="col-md-4">
              <Form.Item
                label="No. NPWP"
                name="npwp"
                rules={[
                  {
                    required: needFaktur,
                    message:
                      "Nomor NPWP wajib diisi jika membutuhkan faktur pajak",
                  },
                  {
                    validator: (_, value) => {
                      // Cek apakah value ada
                      if (needFaktur) {
                        if (!value) {
                          return Promise.reject(
                            new Error("Nomor NPWP tidak boleh kosong")
                          )
                        }

                        if (value.length !== 16) {
                          return Promise.reject(
                            new Error("Nomor NPWP harus terdiri dari 16 angka")
                          )
                        }
                        if (!/^\d+$/.test(value)) {
                          return Promise.reject(
                            new Error("Nomor NPWP hanya boleh berisi angka")
                          )
                        }

                        // Jika semua validasi berhasil, kembalikan Promise.resolve()
                        return Promise.resolve()
                      }
                      return Promise.resolve()
                    },
                  },
                ]}
              >
                <Input
                  type="text"
                  placeholder="Masukkan No. NPWP"
                  maxLength={16}
                  className="w-full"
                />
              </Form.Item>
            </div>
            <div className="col-md-4">
              <Form.Item
                label="NIK"
                name="nik"
                rules={[
                  () => ({
                    required: needFaktur,
                    message: "NIK wajib diisi jika membutuhkan faktur pajak",
                  }),
                ]}
              >
                <Input
                  type="number"
                  placeholder="Masukkan NIK"
                  maxLength={16}
                  className="w-full"
                />
              </Form.Item>
            </div>

            <div className="col-md-12">
              <Form.Item
                label="Company Address"
                name="company_address"
                rules={[
                  {
                    required: false,
                    message: " Company Address!",
                  },
                ]}
              >
                <TextArea placeholder="Masukkan Company Address" />
              </Form.Item>
            </div>
          </div>
        </Card>
      </Form>
    </Layout>
  )
}

export default FormContact
