import {
  AuditOutlined,
  DeleteOutlined,
  DownOutlined,
  EditFilled,
  EyeOutlined,
  RightOutlined,
} from "@ant-design/icons"
import { Dropdown, Menu, Popconfirm, Table } from "antd"
import React, { useState } from "react"
import { useNavigate } from "react-router-dom"
import { toast } from "react-toastify"
import AddressDetail from "./Components/AddressDetail"
import FormAddressModal from "./Components/FormAddressModal"
import { contactAddressListColumn } from "./config"

const ContactAddress = ({
  data = [],
  refetch,
  contact,
  title = "Contact Address",
}) => {
  const navigate = useNavigate()
  const [modalDetail, setModalDetail] = useState(false)
  const [modalUpdate, setModalUpdate] = useState(false)
  const [selectedAddress, setSelectedAddress] = useState(null)

  const handleDeleteAddress = (id) => {
    axios
      .get("/api/contact/address/delete/" + id)
      .then((res) => {
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        refetch()
      })
      .catch((err) => {
        const { message } = err.response.data
        toast.error(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }
  const setDefaultAddress = (value) => {
    axios
      .post("/api/contact/address/set-default-address", {
        address_id: value.id,
        user_id: value.user_id,
      })
      .then((res) => {
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        refetch()
      })
      .catch((err) => {
        const { message } = err.response.data
        toast.error(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }

  const action = {
    title: "Aksi",
    key: "id",
    fixed: "right",
    width: 100,
    render: (text) => (
      <Dropdown.Button
        icon={<DownOutlined />}
        overlay={
          <Menu itemIcon={<RightOutlined />}>
            <Menu.Item
              icon={<AuditOutlined />}
              onClick={() => setDefaultAddress(text)}
            >
              Set Default
            </Menu.Item>
            {/* <Menu.Item
                            icon={<EyeOutlined />}
                            onClick={() => setModalDetail(true)}
                        >
                            Detail
                        </Menu.Item> */}
            <Menu.Item icon={<EditFilled />}>
              <FormAddressModal
                initialValues={{ ...text, address_id: text.id }}
                refetch={() => refetch()}
                update={true}
              />
            </Menu.Item>
            <Popconfirm
              title="Yakin hapus data ini?"
              onConfirm={() => handleDeleteAddress(text.id)}
              // onCancel={cancel}
              okText="Ya, Hapus"
              cancelText="Batal"
            >
              <Menu.Item icon={<DeleteOutlined />}>
                <span>Hapus</span>
              </Menu.Item>
            </Popconfirm>
          </Menu>
        }
        onClick={() => alert("detail")}
      ></Dropdown.Button>
    ),
  }
  return (
    <div className="card">
      <div className="card-header">
        <h1 className="text-lg text-bold flex justify-content-between align-items-center">
          <span>{title}</span>
          <FormAddressModal
            initialValues={{
              user_id: contact?.id,
              nama: contact?.name,
              telepon: contact?.telepon,
              ...selectedAddress,
            }}
            refetch={() => refetch()}
            visible={modalUpdate}
          />
        </h1>
      </div>
      <div className="card-body">
        <AddressDetail
          visible={modalDetail}
          onClose={() => setModalDetail(false)}
        />
        <Table
          dataSource={data}
          columns={[...contactAddressListColumn, action]}
          // loading={loading}
          pagination={false}
          rowKey="id"
          scroll={{ x: "max-content" }}
          tableLayout={"auto"}
        />
      </div>
    </div>
  )
}

export default ContactAddress
