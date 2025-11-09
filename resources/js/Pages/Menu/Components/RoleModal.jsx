import { KeyOutlined } from "@ant-design/icons"
import { Modal, Table } from "antd"
import React, { useState } from "react"
import { roleColumns } from "../config"

const RoleModal = ({
  actionColumns = [],
  dataSource = [],
  loading = false,
  hasChildren = false,
  title = "Submenu",
}) => {
  const [isModalOpen, setIsModalOpen] = useState(false)

  const showModal = () => {
    setIsModalOpen(true)
  }

  return (
    <div>
      {hasChildren ? (
        <button
          onClick={() => showModal()}
          className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
        >
          <KeyOutlined />
          <span className="ml-2">Role</span>
        </button>
      ) : (
        <button className="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center">
          <KeyOutlined />
          <span className="ml-2">Role</span>
        </button>
      )}

      <Modal
        title={title}
        open={isModalOpen}
        cancelText={"Tutup"}
        onCancel={() => setIsModalOpen(false)}
        okButtonProps={{ style: { display: "none" } }}
        width={800}
      >
        <Table
          dataSource={dataSource}
          columns={[...roleColumns, ...actionColumns]}
          loading={loading}
          pagination={false}
          rowKey="id"
          scroll={{ x: "max-content" }}
          tableLayout={"auto"}
        />
      </Modal>
    </div>
  )
}

export default RoleModal
