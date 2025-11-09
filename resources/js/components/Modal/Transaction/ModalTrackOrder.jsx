import { Empty, Modal, Spin, Timeline } from "antd"
import axios from "axios"
import React, { useState } from "react"
import { formatDate } from "../../../helpers"

const ModalTrackOrder = ({ resi, order_number, children }) => {
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [trackHistories, setTrackhistories] = useState([])
  const [trackInfo, setTrackInfo] = useState(null)
  const [loadingTrack, setLoadingTrack] = useState(false)

  const showModal = () => {
    setLoadingTrack(true)
    loadTrackOrder()
    setIsModalOpen(true)
  }

  const loadTrackOrder = () => {
    axios
      .post("/api/transaction/track", { resi })
      .then((res) => {
        setLoadingTrack(false)
        setTrackhistories(
          (res?.data?.data?.tracking_history || []).sort((a, b) => {
            return new Date(b.date) - new Date(a.date) // Replace 'date' with the actual date field name
          })
        )
        setTrackInfo(res?.data?.data)
      })
      .catch(() => {
        setLoadingTrack(false)
      })
  }

  return (
    <div>
      <div className="cursor-pointer" onClick={() => showModal()}>
        {children}
      </div>

      <Modal
        title={"Lacak Pesanan - [001562021057]"}
        open={isModalOpen}
        onCancel={() => setIsModalOpen(false)}
        footer={null}
      >
        {loadingTrack ? (
          <div className="h-80 w-full flex justify-center items-center">
            <Spin />
          </div>
        ) : (
          <div>
            {order_number && (
              <div className="p-2 border border-gray-200 rounded-lg">
                <div>
                  <p className="mb-0 pb-0 ">No Order</p>
                  <h1 className="mb-0 pb-0 font-bold text-lg">
                    {order_number}
                  </h1>
                </div>
              </div>
            )}

            {trackInfo?.origin && trackInfo?.destination && (
              <div className="flex items-center justify-between my-4">
                <div className="p-2 border border-gray-200 rounded-lg w-[48%]">
                  <div>
                    <p className="mb-0 pb-0 ">Penerima</p>
                    <h1 className="mb-0 pb-0 font-bold text-lg">
                      {trackInfo?.destination}
                    </h1>
                  </div>
                </div>
                <div className="p-2 border border-gray-200 rounded-lg w-[48%]">
                  <div>
                    <p className="mb-0 pb-0 ">Pengirim</p>
                    <h1 className="mb-0 pb-0 font-bold text-lg">
                      {" "}
                      {trackInfo?.origin}
                    </h1>
                  </div>
                </div>
              </div>
            )}

            {trackHistories && trackHistories.length > 0 && (
              <div className="p-2 border border-gray-200 rounded-lg">
                <div>
                  <p className="mb-0 pb-0 ">Status</p>
                  <h1 className="mb-0 pb-0 font-bold text-lg">
                    {trackHistories[0].status}
                  </h1>
                </div>
              </div>
            )}

            <div className="mt-4">
              <Timeline>
                {trackHistories && trackHistories?.length > 0 ? (
                  trackHistories.map((item) => {
                    return (
                      <Timeline.Item key={item.date}>
                        <p className="mb-0 pb-0 font-medium">
                          <strong>{item.description}</strong>
                        </p>
                        <p className="mb-0 pb-0 text-xs">
                          {formatDate(item.date, "DD MMMM YYYY HH:mm")}
                        </p>
                      </Timeline.Item>
                    )
                  })
                ) : (
                  <div className="h-80 w-full flex justify-center items-center">
                    <Empty description={"Menunggu dikirim oleh kurir"} />
                  </div>
                )}
              </Timeline>
            </div>
          </div>
        )}
      </Modal>
    </div>
  )
}

export default ModalTrackOrder
