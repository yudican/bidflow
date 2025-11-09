import { Progress } from "antd"
import Pusher from "pusher-js"
import React, { useEffect, useState } from "react"
import { getItem } from "../helpers"
import { toast } from "react-toastify"

const ProgressImportData = ({
  callback,
  refetch,
  type = "manual",
  progressKey = "import-so",
}) => {
  const [pusherChannel, setPusherChannel] = useState(null)
  const [progressData, setProgressData] = useState(null)
  const userData = getItem("user_data", true)
  const key = `${progressKey}-${type}-${userData?.id}`
  useEffect(() => {
    const pusher = new Pusher("f01866680101044abb79", {
      cluster: "ap1",
      debug: true, // Enable debug mode
    })
    const channelPusher = pusher.subscribe("bidflow")
    setPusherChannel(channelPusher)
  }, [])

  useEffect(() => {
    // console.log("Updated data : ", syncData);
    if (pusherChannel && pusherChannel.bind) {
      pusherChannel.unbind(key)

      pusherChannel.bind(key, function (data) {
        if (data?.refresh) {
          callback(null)
          setProgressData(null)
          refetch()
          toast.success("Data Berhasil Diimport")
        } else {
          callback(data)
          setProgressData(data)
        }
      })
    }
  }, [pusherChannel, progressData])
  if (progressData) {
    return (
      <div>
        <p className="mb-0">
          Import data sedang berlangsung mohon tunggu{" "}
          {`${progressData?.progress} dari ${progressData?.total}`}
        </p>
        <Progress
          percent={progressData?.percentage || 0}
          percentPosition={{
            align: "center",
            type: "inner",
          }}
          size={[200, 50]}
          strokeColor="#E6F4FF"
        />
      </div>
    )
  }

  return null
}

export default ProgressImportData
