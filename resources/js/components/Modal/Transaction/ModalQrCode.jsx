import { Modal } from "antd"
import React, { useState } from "react"

const ModalQrCode = ({ title = "Lihat QR", qr_url, children }) => {
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
        onCancel={() => setIsModalOpen(false)}
        footer={null}
      >
        <img src={qr_url} alt="" />
      </Modal>
    </div>
  )
}

export default ModalQrCode
