import { CheckOutlined, LoadingOutlined } from "@ant-design/icons"
import { Card, Form, Input, Select } from "antd"
import TextArea from "antd/lib/input/TextArea"
import React, { useEffect, useState } from "react"
import "react-draft-wysiwyg/dist/react-draft-wysiwyg.css"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import RichtextEditor from "../../../components/RichtextEditor"
import Layout from "../../../components/layout"
import axios from "axios"

const NotificationTemplateForm = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const { template_id, group_id } = useParams()
  const [groupName, setGroupName] = useState("")

  const [dataRole, setDataRole] = useState([])

  const [loadingRole, setLoadingRole] = useState(false)
  const [loadingSubmit, setLoadingSubmit] = useState(false)

  const loadDetailData = () => {
    axios
      .get(`/api/setting/notification-template/${template_id}`)
      .then((res) => {
        const { data } = res.data
        form.setFieldsValue({
          ...data,
          group_id: `${data.group_id}`,
        })
      })
  }

  const loadGroupName = () => {
    axios
      .get(`/api/setting/notification-template/group/${group_id}`)
      .then((res) => {
        setGroupName(res.data.data)
      })
  }

  const loadRole = () => {
    setLoadingRole(true)
    axios
      .get("/api/master/role")
      .then((res) => {
        setDataRole(res.data.data)
        setLoadingRole(false)
      })
      .catch((err) => setLoadingRole(false))
  }

  useEffect(() => {
    if (group_id) {
      form.setFieldValue("group_id", group_id)
      loadGroupName()
    }
    loadRole()
    loadDetailData()
  }, [])

  const onFinish = (values) => {
    setLoadingSubmit(true)

    const url = template_id ? `save/${template_id}` : "save"

    axios
      .post(`/api/setting/notification-template/${url}`, {
        ...values,
        role_ids: JSON.stringify(values.role_ids),
      })
      .then((res) => {
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        setLoadingSubmit(false)
        return navigate(`/setting/notification-template/list/${group_id}`)
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
    <Layout
      title="Form Template Notifikasi"
      href={`/setting/notification-template/list/${group_id}`}
      rightContent={
        <button
          type="button"
          onClick={() => form.submit()}
          className="text-white bg-blueColor hover:bg-blueColor/90 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
        >
          {loadingSubmit ? <LoadingOutlined /> : <CheckOutlined />}
          <span className="ml-2">Simpan</span>
        </button>
      }
      breadcrumbs={[
        {
          title: "Setting",
        },
        { title: "Template Notifikasi" },
        { title: groupName },
        { title: "Form" },
      ]}
    >
      <Form
        form={form}
        name="basic"
        layout="vertical"
        onFinish={onFinish}
        //   onFinishFailed={onFinishFailed}
        autoComplete="off"
      >
        <Card title="Data Template Notifikasi">
          <div className="card-body row">
            <div className="col-md-4">
              <Form.Item
                label="Kode Notifikasi"
                name="notification_code"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Kode Notifikasi!",
                  },
                ]}
              >
                <Input placeholder="Masukkan Kode Notifikasi" />
              </Form.Item>
              <Form.Item
                label="Judul Notifikasi"
                name="notification_title"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Judul Notifikasi!",
                  },
                ]}
              >
                <Input placeholder="Masukkan Judul Notifikasi" />
              </Form.Item>
            </div>
            <div className="col-md-4">
              <Form.Item
                label="Role"
                name="role_ids"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Role!",
                  },
                ]}
              >
                <Select
                  mode="multiple"
                  allowClear
                  optionFilterProp="children"
                  filterOption={(input, option) =>
                    (option?.children?.toLowerCase() ?? "").includes(
                      input.toLowerCase()
                    )
                  }
                  className="w-full"
                  placeholder="Pilih Role"
                  loading={loadingRole}
                >
                  {dataRole.map((item) => (
                    <Select.Option key={item.id} value={item.id}>
                      {item.role_name}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>
              <Form.Item
                label="Sub Judul Notifikasi"
                name="notification_subtitle"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Sub Judul Notifikasi!",
                  },
                ]}
              >
                <Input placeholder="Masukkan Sub Judul Notifikasi" />
              </Form.Item>
            </div>
            <div className="col-md-4">
              <Form.Item
                label="Tipe Notifikasi"
                name="notification_type"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Tipe Notifikasi!",
                  },
                ]}
              >
                <Select
                  allowClear
                  className="w-full"
                  placeholder="Pilih Tipe Notifikasi"
                >
                  <Select.Option value={"email"}>Email</Select.Option>
                  <Select.Option value={"alert"}>Alert</Select.Option>
                  <Select.Option value={"amail-alert"}>
                    Email & Alert
                  </Select.Option>
                  <Select.Option value={"mobile"}>Mobile</Select.Option>
                </Select>
              </Form.Item>
              <Form.Item
                label="Grup"
                name="group_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Grup!",
                  },
                ]}
              >
                <Select allowClear className="w-full" placeholder="Pilih Grup">
                  <Select.Option value={"91"}>FlimApp</Select.Option>
                  <Select.Option value={"92"}>Operational</Select.Option>
                  <Select.Option value={"93"}>Customer B2B</Select.Option>
                  <Select.Option value={"94"}>Telmark</Select.Option>
                  <Select.Option value={"94"}>LMS</Select.Option>
                </Select>
              </Form.Item>
            </div>

            <div className="col-md-12">
              <Form.Item
                label="Isi Notifikasi"
                name="notification_body"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Isi Notifikasi!",
                  },
                ]}
              >
                <RichtextEditor
                  value={form.getFieldValue("notification_body")}
                  form={form}
                  name={"notification_body"}
                />
              </Form.Item>

              <Form.Item label="Catatan Notifikasi" name="notification_note">
                <TextArea placeholder="Masukkan Catatan Notifikasi" rows={5} />
              </Form.Item>
            </div>
          </div>
        </Card>

        {/* <div className="card mt-6">
          <div className="card-body flex justify-end">
            <button
              type="button"
              onClick={() => form.submit()}
              className="text-white bg-blueColor hover:bg-blueColor/90 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
            >
              {loadingSubmit ? <LoadingOutlined /> : <CheckOutlined />}
              <span className="ml-2">Simpan</span>
            </button>
          </div>
        </div> */}
      </Form>
    </Layout>
  )
}

export default NotificationTemplateForm
