import { LoadingOutlined } from "@ant-design/icons"
import React from "react"

const Button = ({
  loading = false,
  disabled = false,
  color = "blue",
  label = "Button",
  icon = null,
  onClick,
  className,
}) => {
  if (loading) {
    return (
      <button
        className={`ml-3 text-white bg-${color}-800 hover:bg-${color}-800 focus:ring-4 focus:outline-none focus:ring-${color}-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ${className}}`}
        disabled
      >
        <LoadingOutlined />
      </button>
    )
  }

  if (disabled) {
    return (
      <button
        className={`ml-3 text-white bg-gray-800 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ${className}}`}
        disabled
      >
        <span className="mr-2  mb-1">{icon}</span>
        <span>{label}</span>
      </button>
    )
  }

  return (
    <button
      className={`ml-3 text-white bg-${color}-800 hover:bg-${color}-800 focus:ring-4 focus:outline-none focus:ring-${color}-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ${className}}`}
      onClick={onClick}
    >
      <span className="mr-2 mb-1">{icon}</span>
      <span>{label}</span>
    </button>
  )
}

export default Button
