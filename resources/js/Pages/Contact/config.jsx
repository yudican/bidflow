import {
  EditFilled,
  EditOutlined,
  EyeOutlined,
  RightOutlined,
  SyncOutlined,
} from "@ant-design/icons"
import { Button, Menu, Tag, Tooltip } from "antd"
import axios from "axios"
import React from "react"
import { Link, useNavigate } from "react-router-dom"
import { toast } from "react-toastify"
import { formatDateTime, formatNumber } from "../../helpers"

const ActionMenu = ({ value, record }) => {
  const navigate = useNavigate()
  return (
    <Menu
      onClick={({ key }) => {
        switch (key) {
          case "detail":
            navigate(`/contact/detail/${value}`)
            break
          case "update":
            navigate(`/contact/update/${value}`)
            break
          case "sync":
            axios
              .post(`/api/contact/service/sync-gp/${value}`, {
                uid: record.uid,
              })
              .then((res) => {
                const { message } = res.data
                toast.success("Sync Gp berhasil")
              })
              .catch((e) => {
                toast.error("Sync Gp gagal")
              })
            break
        }
      }}
      itemIcon={<RightOutlined />}
      items={[
        {
          label: "Detail Contact",
          key: "detail",
          icon: <EyeOutlined />,
        },
        {
          label: "Ubah Contact",
          key: "update",
          icon: <EditFilled />,
        },
        {
          label: "Sync GP Contact",
          key: "sync",
          icon: <SyncOutlined />,
        },
      ]}
    />
  )
}

const contactListColumn = [
  {
    title: "No.",
    dataIndex: "number",
    key: "number",
    // render: (text, record, index) => index + 1,
  },
  {
    title: "Customer Code",
    dataIndex: "uid",
    key: "uid",
  },
  {
    title: "Name",
    dataIndex: "name",
    key: "name",
  },
  {
    title: "Email",
    dataIndex: "email",
    key: "email",
  },
  {
    title: "Role",
    dataIndex: "role",
    key: "role",
  },
  {
    title: "Created by",
    dataIndex: "created_by",
    key: "created_by",
  },
  {
    title: "Created On",
    dataIndex: "created_on",
    key: "created_on",
  },
  {
    title: "Deposito",
    dataIndex: "deposit",
    key: "deposit",
  },
  {
    title: "Total Debt",
    dataIndex: "total_debt",
    key: "total_debt",
  },
  {
    title: "Actions",
    key: "id",
    fixed: "right",
    align: "center",
    width: 100,
    render: (text, record) => {
      const contact_id = text.key
      const uid = record.uid

      return (
        <>
          {/* <Dropdown.Button
            style={{ left: -16 }}
            // icon={<DownOutlined />}
            overlay={
              <ActionMenu value={text.key} record={record} role="list" />
            }
          ></Dropdown.Button> */}

          <div className="flex flex-row items-center justify-between gap-4">
            <Link to={`/contact/update/${contact_id}`}>
              <Tooltip title="Edit Contact">
                <Button icon={<EditOutlined />} />
              </Tooltip>
            </Link>

            <Link to={`/contact/detail/${contact_id}`}>
              <Tooltip title="Detail Contact">
                <Button icon={<EyeOutlined />}></Button>
              </Tooltip>
            </Link>

            <Tooltip title="Sync GP Contact ">
              <Button
                onClick={() => {
                  return axios
                    .post(`/api/contact/service/sync-gp/${contact_id}`, {
                      uid,
                    })
                    .then((res) => {
                      const { message } = res.data
                      toast.success("Sync Gp berhasil")
                    })
                    .catch((e) => {
                      toast.error("Sync Gp gagal")
                    })
                }}
                icon={<SyncOutlined />}
              />
            </Tooltip>
          </div>
        </>
      )
    },
  },
]

const contactAddressListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Type",
    dataIndex: "type",
    key: "type",
  },
  {
    title: "Name",
    dataIndex: "nama",
    key: "nama",
  },
  {
    title: "No Telepon",
    dataIndex: "telepon",
    key: "telepon",
  },
  {
    title: "Alamat",
    dataIndex: "alamat_detail",
    key: "alamat_detail",
    width: 500,
  },
]

const contactTransaction = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  // {
  //   title: "Name",
  //   dataIndex: "name",
  //   key: "name",
  // },
  {
    title: "TRX ID",
    dataIndex: "id_transaksi",
    key: "id_transaksi",
  },
  {
    title: "Tanggal Transaksi",
    dataIndex: "created_at",
    key: "created_at",
    render: (value) => {
      return formatDateTime(value)
    },
  },
  {
    title: "Nominal",
    dataIndex: "nominal",
    key: "nominal",
    render: (value) => `Rp ${formatNumber(value)}`,
  },
  {
    title: "Metode Pembayaran",
    dataIndex: "payment_method",
    key: "payment_method",
    render: (value, record) => {
      return record.data_payment_method?.bank_name
    },
  },
]

const contactCaseHistory = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Case No",
    dataIndex: "title",
    key: "title",
  },
  {
    title: "Contact",
    dataIndex: "contact",
    key: "contact",
  },
  {
    title: "Type",
    dataIndex: "type",
    key: "type",
  },
  {
    title: "Category",
    dataIndex: "category",
    key: "category",
  },
  {
    title: "Priority",
    dataIndex: "priority",
    key: "priority",
  },
  {
    title: "Created by",
    dataIndex: "created_by",
    key: "created_by",
  },
  {
    title: "Created On",
    dataIndex: "created_at",
    key: "created_at",
  },
]

const memberLayerList = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Name",
    dataIndex: "name",
    key: "name",
  },
  {
    title: "Email",
    dataIndex: "email",
    key: "email",
  },
  {
    title: "Telepon",
    dataIndex: "phone",
    key: "phone",
  },
]

const orderLeadListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (value, row, index) => index + 1,
  },
  {
    title: "Invoice Number",
    dataIndex: "invoice_number",
    key: "invoice_number",
  },
  {
    title: "Order Number",
    dataIndex: "order_number",
    key: "order_number",
  },
  {
    title: "Subtotal",
    dataIndex: "subtotal",
    key: "subtotal",
    render: (value) => `Rp ${formatNumber(value)}`,
  },
  {
    title: "Kode Unik",
    dataIndex: "kode_unik",
    key: "kode_unik",
  },
  {
    title: "Tax Total",
    dataIndex: "tax_amount",
    key: "tax_amount",
    render: (value) => `Rp ${formatNumber(value)}`,
  },
  {
    title: "Diskon",
    dataIndex: "discount_amount",
    key: "discount_amount",
    render: (value) => `Rp ${formatNumber(value)}`,
  },
  {
    title: "Total",
    dataIndex: "amount_total",
    key: "amount_total",
  },
  {
    title: "Created by",
    dataIndex: "created_by",
    key: "created_by",
  },
  {
    title: "Created On",
    dataIndex: "created_on",
    key: "created_on",
  },
  {
    title: "Jatuh Tempo",
    dataIndex: "payment_due_date",
    key: "payment_due_date",
  },
  {
    title: "Payment Term",
    dataIndex: "payment_term",
    key: "payment_term",
  },
  {
    title: "Status",
    dataIndex: "status",
    key: "status",
  },
]

const contactCheckbookListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Nama Bank",
    dataIndex: "bank_name",
    key: "bank_name",
  },
  {
    title: "Description",
    dataIndex: "description",
    key: "description",
  },
  {
    title: "Address",
    dataIndex: "company_address",
    key: "company_address",
  },
]

const referalListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Name",
    dataIndex: "name",
    key: "name",
  },
  {
    title: "Email",
    dataIndex: "email",
    key: "email",
  },
]

const redeemPointListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Point",
    dataIndex: "point",
    key: "point",
  },
  {
    title: "Redeem Date ",
    dataIndex: "created_at",
    key: "created_at",
    render: (value) => {
      const date = new Date(value)
      const formatted =
        value &&
        `${date.getDate().toString().padStart(2, "0")}-${(date.getMonth() + 1)
          .toString()
          .padStart(2, "0")}-${date.getFullYear()} ${date
          .getHours()
          .toString()
          .padStart(2, "0")}:${date
          .getMinutes()
          .toString()
          .padStart(2, "0")}:${date.getSeconds().toString().padStart(2, "0")}`
      return formatted
    },
  },
]

const voucherListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Voucher",
    dataIndex: "voucher_code",
    key: "voucher_code",
    render: (_, record) => record?.voucher?.voucher_code,
  },
  {
    title: "Status",
    dataIndex: "status",
    key: "status",
    render: (_, record) => {
      if (record?.status > 0) {
        return <Tag color="green">Aktif</Tag>
      }
      return <Tag color="red">Sudah Terpakai</Tag>
    },
  },
]

export {
  contactAddressListColumn,
  contactCaseHistory,
  contactCheckbookListColumn,
  contactListColumn,
  contactTransaction,
  memberLayerList,
  orderLeadListColumn,
  redeemPointListColumn,
  referalListColumn,
  voucherListColumn,
}
