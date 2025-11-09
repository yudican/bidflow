import { InfoCircleFilled, QuestionCircleOutlined } from "@ant-design/icons"
import { Button, Drawer, Pagination, Popconfirm, Space, Spin } from "antd"
import React, { useEffect, useState } from "react"
import {
  useGetNotificationsQuery,
  useReadAllNotificationMutation,
  useReadNotificationMutation,
} from "../../configs/Redux/Services/generalServices"
import { RenderIf, formatDate } from "../../helpers"
import RenderHtml from "../atoms/RenderHtml"

const Notification = () => {
  const [open, setOpen] = useState(false)
  const [selectedItem, setSelectedItem] = useState(null)
  const [pageUrl, setPageUrl] = useState("/api/general/notifications")
  const {
    data: notification,
    isLoading,
    refetch,
  } = useGetNotificationsQuery(pageUrl)
  const [readNotification, { isLoading: isLoadingRead }] =
    useReadNotificationMutation()
  const [readAllNotification, { isLoading: isLoadingReadAll }] =
    useReadAllNotificationMutation()

  const handleReadNotification = (notification_id) => {
    readNotification(notification_id).then(({ data, error }) => {
      if (data) {
        refetch()
      }
    })
  }

  const handleReadAllNotification = () => {
    readAllNotification().then(({ data, error }) => {
      if (data) {
        console.log("first", data)
        refetch()
      }
    })
  }

  useEffect(() => {
    if (selectedItem && !selectedItem?.is_read) {
      handleReadNotification(selectedItem?.id)
    }

    return () => {}
  }, [selectedItem])

  const items = notification?.data || []
  // const badge = notification?.total_unread || notification?.total || 0
  const badge = notification?.total_unread || 0

  return (
    <>
      <a
        className="nav-link dropdown-toggle"
        href="#"
        role="button"
        onClick={(event) => {
          event.preventDefault()
          setSelectedItem(null)
          setOpen(!open)
        }}
      >
        <i className="fa fa-bell"></i>
        <span className="notification">{badge}</span>
      </a>
      <Drawer
        title={selectedItem ? selectedItem?.title : `Notification (${badge})`}
        placement="right"
        onClose={() => {
          if (selectedItem) {
            return setSelectedItem(null)
          }

          return setOpen(false)
        }}
        open={open}
        width={500}
        className="mt-16 z-10"
        footer={
          !selectedItem && (
            <Pagination
              defaultCurrent={1}
              total={badge}
              className="my-2 text-center"
              onChange={(page) => {
                setPageUrl(`/api/general/notifications?page=${page}`)
              }}
            />
          )
        }
        extra={
          !selectedItem && (
            <Space>
              <Popconfirm
                disabled={notification?.total_unread === 0}
                title="Konfirmasi"
                onConfirm={handleReadAllNotification}
                okText="Ya"
                cancelText="Tidak"
                placement="bottom"
                icon={
                  <QuestionCircleOutlined
                    style={{
                      color: "red",
                    }}
                  />
                }
                okButtonProps={{
                  loading: isLoadingReadAll,
                }}
              >
                <Button type="link" disabled={notification?.total_unread === 0}>
                  Tandai semua telah dibaca
                </Button>
              </Popconfirm>
            </Space>
          )
        }
      >
        <RenderIf isTrue={isLoading}>
          <div className="h-[80vh] flex justify-center items-center">
            <Spin />
          </div>
        </RenderIf>

        <RenderIf isTrue={!isLoading && !selectedItem}>
          <div className="notif-center">
            {items &&
              items.map((item) => {
                const { is_read } = item
                return (
                  <div
                    key={item.id}
                    className={`${
                      is_read ? "bg-[#f2f6fc]" : "bg-white"
                    } p-4 rounded shadow-md mb-2 cursor-pointer`}
                    onClick={() => setSelectedItem(item)}
                  >
                    <div className="flex items-start">
                      <div className="mr-3">
                        {/* <!-- Icon or Image for Notification (Optional) --> */}
                        <InfoCircleFilled
                          style={{
                            color: is_read ? "#9ca3af" : "#1f2937",
                          }}
                        />
                      </div>
                      <div
                        className={`text-sm font-medium ${
                          is_read ? "text-gray-400" : "text-gray-800"
                        }`}
                      >
                        {/* <!-- Notification Content --> */}
                        <p
                          className={`text-sm ${
                            is_read ? "font-medium" : "font-bold"
                          }`}
                        >
                          {item?.title}
                        </p>
                        {formatDate(item?.created_at)}
                      </div>
                    </div>
                  </div>
                )
              })}
          </div>
        </RenderIf>

        <RenderIf isTrue={!isLoading && selectedItem}>
          <div className="notif-center">
            <RenderHtml htmlContent={selectedItem?.body} />
          </div>
        </RenderIf>
      </Drawer>
    </>
  )
}

export default Notification
