import { CloseOutlined } from "@ant-design/icons"
import { Input, Modal } from "antd"
import TextArea from "antd/lib/input/TextArea"
import React, { useState } from "react"
const ModalReject = ({ handleSubmit }) => {
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [notes, setNotes] = useState("")

  const showModal = () => {
    setIsModalOpen(true)
  }

  return (
    <div>
      <button
        className="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
        onClick={showModal}
      >
        <CloseOutlined />
        <span className="">Reject</span>
      </button>

      <Modal
        title="Notes"
        open={isModalOpen}
        onOk={() => {
          handleSubmit({ notes })
          setIsModalOpen(false)
        }}
        onCancel={() => {
          setIsModalOpen(false)
          setNotes("")
        }}
      >
        <div>
          <div>
            <label htmlFor="">Notes</label>
            <TextArea
              placeholder="Notes"
              onChange={(e) => setNotes(e.target.value)}
            />
          </div>
        </div>
      </Modal>
    </div>
  )
}

export default ModalReject
