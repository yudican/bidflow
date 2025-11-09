import { CloseOutlined, DeleteOutlined } from "@ant-design/icons"
import { message, Popconfirm } from "antd"
import React from "react"

const UpdateUniqueCode = ({
  item,
  order,
  url,
  refetch,
  disabled = false,
  field = "uid_lead",
}) => {
  const updateUniqueCode = (uid_lead, kode_unik) => {
    axios
      .post(url, {
        [field]: uid_lead,
        kode_unik,
      })
      .then((res) => {
        message.success("Unique Code berhasil diupdate")
        refetch()
      })
      .catch((err) => {
        message.error("Unique Code gagal diupdate")
      })
  }

  if (disabled) {
    return <strong>{item.label}</strong>
  }

  return (
    <div className="flex justify-between items-center cursor-pointer">
      <strong>{item.label}</strong>
      {item.value > 0 ? (
        <Popconfirm
          title="Yakin Hapus Kode Unik?"
          onConfirm={() => updateUniqueCode(order[field], 0)}
          okText="Ya, Hapus"
          cancelText="Batal"
        >
          <DeleteOutlined />
        </Popconfirm>
      ) : (
        <CloseOutlined
          onClick={() => updateUniqueCode(order[field], order.temp_kode_unik)}
        />
      )}
    </div>
  )
}

export default UpdateUniqueCode
