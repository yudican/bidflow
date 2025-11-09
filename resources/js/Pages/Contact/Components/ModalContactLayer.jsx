import { PlusOutlined } from "@ant-design/icons"
import { Form, Modal } from "antd"
import React, { useEffect, useState } from "react"
import DebounceSelect from "../../../components/atoms/DebounceSelect"
import { searchContactMember } from "../service"
const ModalContactLayer = ({ handleOk, user_id }) => {
  const [form] = Form.useForm()
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [member, setMember] = useState([])
  const onFinish = (values) => {
    handleOk({
      company_id: values?.company_id?.value,
      user_id,
    })
    setIsModalOpen(false)
  }

  const handleGetContactMember = () => {
    searchContactMember(null, user_id).then((results) => {
      const newResult = results.map((result) => {
        return { label: result.nama, value: result.id }
      })

      setMember(newResult)
    })
  }

  const handleSearchContact = async (e) => {
    return searchContactMember(e, user_id).then((results) => {
      const newResult = results.map((result) => {
        return { label: result.nama, value: result.id }
      })

      return newResult
    })
  }

  useEffect(() => {
    handleGetContactMember()
  }, [])

  return (
    <div>
      <button
        onClick={() => setIsModalOpen(true)}
        className="text-white bg-[#008BE1] hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
      >
        <PlusOutlined />
        <span className="ml-2">Tambah Member</span>
      </button>

      <Modal
        title="Input Member"
        open={isModalOpen}
        onOk={() => {
          form.submit()
        }}
        cancelText={"Batal"}
        onCancel={() => setIsModalOpen(false)}
        okText={"Simpan"}
      >
        <Form
          form={form}
          name="basic"
          layout="vertical"
          onFinish={onFinish}
          autoComplete="off"
        >
          <Form.Item
            label="Pilih Member"
            name="company_id"
            rules={[
              {
                required: true,
                message: "Silakan pilih Member!",
              },
            ]}
          >
            <DebounceSelect
              showSearch
              placeholder="Cari Member"
              fetchOptions={handleSearchContact}
              filterOption={false}
              defaultOptions={member}
              className="w-full"
            />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

export default ModalContactLayer
