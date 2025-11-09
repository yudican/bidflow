import { Spin } from "antd"
import React from "react"
export default function LoadingFallback() {
  return (
    <div className="flex justify-center items-center h-screen bg-white">
      <Spin size="large" />
    </div>
  )
}
