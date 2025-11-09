import { EditOutlined, PlusOutlined } from "@ant-design/icons"
import { Form, Input, Modal, Select } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { toast } from "react-toastify"
const MenuForm = ({
  refetch,
  initialValues = {},
  update = false,
  parents = [],
  url,
  roles = [],
}) => {
  const [form] = Form.useForm()
  const [isModalOpen, setIsModalOpen] = useState(false)

  const showModal = () => {
    setIsModalOpen(true)
  }

  const handleSubmit = (value) => {
    axios
      .post(url, { ...initialValues, ...value })
      .then((res) => {
        const { message } = res.data
        form.resetFields()
        setIsModalOpen(false)
        refetch()
        toast.success(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
      .catch((err) => {
        const { message } = err.response.data
      })
  }

  return (
    <div>
      {update ? (
        <button
          onClick={() => showModal()}
          className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mr-2"
        >
          <EditOutlined />
        </button>
      ) : (
        <button
          onClick={() => showModal()}
          className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
        >
          <PlusOutlined />
          <span className="ml-2">Menu Baru</span>
        </button>
      )}

      <Modal
        title={update ? "Edit Menu" : "Tambah Menu Baru"}
        open={isModalOpen}
        cancelText={"Batal"}
        okText={"Simpan"}
        onCancel={() => setIsModalOpen(false)}
        onOk={() => form.submit()}
        width={1000}
      >
        <Form
          form={form}
          name="basic"
          layout="vertical"
          initialValues={{
            ...initialValues,
            role_id: initialValues.role_id
              ? initialValues.role_id
              : ["aaf5ab14-a1cd-46c9-9838-84188cd064b6"],
            show_menu: initialValues.show_menu ? initialValues.show_menu : "1",
          }}
          onFinish={handleSubmit}
          //   onFinishFailed={onFinishFailed}
          autoComplete="off"
        >
          <div className="row">
            <div className="col-md-4">
              <Form.Item label="Parent Menu" name="parent_id">
                <Select
                  allowClear
                  className="w-full mb-2"
                  placeholder="Pilih Parent Menu"
                >
                  {parents.map((item) => (
                    <Select.Option key={item.id} value={item.id}>
                      {item.menu_label}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>
            </div>
            <div className="col-md-4">
              <Form.Item
                label="Nama Menu"
                name="menu_label"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Nama Menu!",
                  },
                ]}
              >
                <Input />
              </Form.Item>
            </div>
            <div className="col-md-4">
              <Form.Item label="Menu Icon" name="menu_icon">
                <Input placeholder="fas fa-dashboard" />
              </Form.Item>
            </div>
            <div className="col-md-6">
              <Form.Item
                label="Menu Route"
                name="menu_route"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Menu Route!",
                  },
                ]}
              >
                <Input placeholder="menu.index" />
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
                  mode="multiple"
                  allowClear
                  className="w-full mb-2"
                  placeholder="Pilih Role"
                >
                  <Select.Option
                    key={"aaf5ab14-a1cd-46c9-9838-84188cd064b6"}
                    value={"aaf5ab14-a1cd-46c9-9838-84188cd064b6"}
                  >
                    Superadmin
                  </Select.Option>
                  {roles.map((item) => (
                    <Select.Option key={item.id} value={item.id}>
                      {item.role_name}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>
            </div>
            <div className="col-md-6">
              <Form.Item label="Menu Badge" name="badge" placeholder="badge">
                <Input />
              </Form.Item>
              <Form.Item
                label="Show Menu"
                name="show_menu"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Show Menu!",
                  },
                ]}
              >
                <Select
                  allowClear
                  className="w-full mb-2"
                  placeholder="Pilih Kabupaten"
                >
                  <Select.Option key={1} value={"1"}>
                    Ya
                  </Select.Option>
                  <Select.Option key={0} value={"0"}>
                    Tidak
                  </Select.Option>
                </Select>
              </Form.Item>
            </div>
          </div>
        </Form>
      </Modal>
    </div>
  )
}

export default MenuForm
