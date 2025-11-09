import { Modal } from "antd"
import React, { useState } from "react"

const ModalNotes = ({ value }) => {
  const [isModalOpen, setIsModalOpen] = useState(false)
  const showModal = () => {
    setIsModalOpen(true)
  }

  return (
    <div>
      <a href="#" onClick={() => showModal()} className="text-blue">
        Show
      </a>

      <Modal
        title="Notes"
        open={isModalOpen}
        cancelText={"Tutup"}
        onCancel={() => setIsModalOpen(false)}
        okButtonProps={{ style: { display: "none" } }}
      >
        <p>{value}</p>
      </Modal>
    </div>
  )
}

export default ModalNotes
