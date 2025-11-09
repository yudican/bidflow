import { CheckOutlined, LoadingOutlined } from "@ant-design/icons"
import { Card, Form, Input, Select } from "antd"
import TextArea from "antd/lib/input/TextArea"
import React, { useEffect, useState } from "react"
import "react-draft-wysiwyg/dist/react-draft-wysiwyg.css"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../../components/layout"
import "../../../index.css"

const LevelForm = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const { level_id } = useParams()

  const [roles, setRoles] = useState([])
  const [loadingSubmit, setLoadingSubmit] = useState(false)
  const [loadingRole, setLoadingRole] = useState(false)

  const loadDetailBrand = () => {
    axios.get(`/api/master/level-price/${level_id}`).then((res) => {
      const { data } = res.data
      form.setFieldsValue(data)
    })
  }

  const loadRole = () => {
    setLoadingRole(true)
    axios
      .get("/api/master/role")
      .then((res) => {
        setLoadingRole(false)
        setRoles(res.data.data)
      })
      .catch((err) => {
        setLoadingRole(false)
      })
  }

  useEffect(() => {
    loadDetailBrand()
    loadRole()
  }, [])

  const onFinish = (values) => {
    setLoadingSubmit(true)
    let formData = new FormData()

    formData.append("name", values.name)
    formData.append("role_id", JSON.stringify(values.role_ids))
    formData.append("description", values.description)

    const url = level_id
      ? `/api/master/level-price/save/${level_id}`
      : "/api/master/level-price/save"

    axios
      .post(url, formData)
      .then((res) => {
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        setLoadingSubmit(false)
        return navigate("/master/level-price")
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
      title="Level"
      href="/master/level-price"
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
        <Card title="Level Data">
          <div className="card-body row">
            <div className="col-md-6">
              <Form.Item
                label="Level Name"
                name="name"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Level Name!",
                  },
                ]}
              >
                <Input placeholder="Ketik Level Name" />
              </Form.Item>
            </div>
            <div className="col-md-6">
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
                  className="w-full mb-2"
                  placeholder="Pilih Role"
                  loading={loadingRole}
                >
                  {roles.map((item) => (
                    <Select.Option key={item.id} value={item.id}>
                      {item.role_name}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>
            </div>

            <div className="col-md-12">
              <Form.Item
                label="Description"
                name="description"
                rules={[
                  {
                    required: false,
                    message: "Silakan masukkan Description!",
                  },
                ]}
              >
                <TextArea placeholder="Ketik Description " />
              </Form.Item>
            </div>
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

export default LevelForm
