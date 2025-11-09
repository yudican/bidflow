import { Modal } from "antd"
import React, { useState } from "react"

const ModalConfirm = ({
  onConfirm,
  loading,
  title = "Konfirmasi",
  description = "Konfirmasi Perubahan ?",
  cancelText = "Batal",
  okText = "Konfirmasi",
  children,
  okButtonProps,
  cancelButtonProps,
}) => {
  const [isModalOpen, setIsModalOpen] = useState(false)

  const showModal = () => {
    setIsModalOpen(true)
  }

  return (
    <div>
      <div className="cursor-pointer" onClick={() => showModal()}>
        {children}
      </div>

      <Modal
        title={title}
        open={isModalOpen}
        onOk={() => {
          onConfirm()

          setTimeout(() => {
            setIsModalOpen(false)
          }, 1000)
        }}
        cancelText={cancelText}
        onCancel={() => setIsModalOpen(false)}
        okText={okText}
        confirmLoading={loading}
        okButtonProps={okButtonProps}
        cancelButtonProps={cancelButtonProps}
      >
        <p>{description}</p>
      </Modal>
    </div>
  )
}

export default ModalConfirm
