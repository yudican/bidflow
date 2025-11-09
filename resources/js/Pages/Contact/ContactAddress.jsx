import {
  DeleteOutlined,
  DownOutlined,
  EditFilled,
  RightOutlined,
} from "@ant-design/icons"
import { Dropdown, Menu, Popconfirm, Switch, Table } from "antd"
import axios from "axios"
import React, { useState } from "react"
import { useNavigate } from "react-router-dom"
import { toast } from "react-toastify"
import AddressDetail from "./Components/AddressDetail"
import FormAddressModal from "./Components/FormAddressModal"
import { contactAddressListColumn } from "./config"
import { formatPhone } from "../../helpers"

const ContactAddress = ({
  data = [],
  refetch,
  contact,
  title = "Contact Address",
  titleAddress = "Default",
  onChange,
  loading = false,
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
  const setDefaultAddress = (value, record) => {
    axios
      .post("/api/contact/address/set-default-address", {
        address_id: record.id,
        user_id: record.user_id,
        value,
      })
      .then((res) => {
        toast.success("Alamat default berhasil diupdate!" || res.data.message, {
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

  const action = [
    {
      title: titleAddress,
      key: "is_default",
      fixed: "right",
      width: 100,
      render: (text, record) => {
        return (
          <Switch
            checked={record.is_default > 0 ? true : false}
            defaultChecked={record.is_default > 0 ? true : false}
            onChange={() => {
              setDefaultAddress(record.is_default > 0 ? 0 : 1, record)
              if (onChange) {
                onChange(record.id)
              }
            }}
          />
        )
      },
    },
    {
      title: "Aksi",
      key: "id",
      fixed: "right",
      width: 100,
      hide: true,
      render: (text, record) => (
        <Dropdown.Button
          icon={<DownOutlined />}
          overlay={
            <Menu itemIcon={<RightOutlined />}>
              <Menu.Item icon={<EditFilled />}>
                <FormAddressModal
                  initialValues={{
                    ...record,
                    address_id: text.id,
                  }}
                  refetch={() => refetch()}
                  update={true}
                />
              </Menu.Item>
              <Popconfirm
                title="Yakin Hapus Alamat ini?"
                onConfirm={() => handleDeleteAddress(record.id)}
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
    },
  ]

  return (
    <div className="card">
      <div className="card-header">
        <h1 className="text-lg text-bold flex justify-content-between align-items-center">
          <span>{title}</span>
          <FormAddressModal
            initialValues={{
              user_id: contact?.id,
              nama: contact?.name,
              telepon:
                formatPhone(contact?.telepon) || formatPhone(contact?.phone),
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
          columns={[...contactAddressListColumn, ...action]}
          loading={loading}
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
