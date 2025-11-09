import { CopyOutlined } from "@ant-design/icons"
import { Button, Popover } from "antd"
import axios from "axios"
import React, { useState } from "react"
import { toast } from "react-toastify"

const SalesOrderPopover = ({ value, id }) => {
  console.log(value, id)
  const [items, setItems] = useState([])
  const [loading, setLoading] = useState(false)

  const loadData = () => {
    setItems([])
    setLoading(true)
    axios
      .get(`/api/sales-order/items/${id}`)
      .then(({ data }) => {
        setLoading(false)
        setItems(data?.data || [])
      })
      .catch((err) => {
        setLoading(false)
      })
  }

  const copyToClipboard = (text) => {
    if (!navigator.clipboard) {
      // Fallback for browsers that do not support navigator.clipboard
      var textarea = document.createElement("textarea")
      textarea.value = text
      document.body.appendChild(textarea)
      textarea.select()
      document.execCommand("copy")
      document.body.removeChild(textarea)
      toast.success("Nomor SO berhasil disalin!")
      return
    }

    navigator.clipboard
      .writeText(text)
      .then(() => {
        toast.success("Nomor SO berhasil disalin!")
        console.log("Text copied to clipboard:", text)
        // You can show a success message or perform any other action here
      })
      .catch((error) => {
        console.error("Unable to copy text to clipboard:", error)
        // You can show an error message or perform any other action here
      })
  }

  return (
    <div>
      <Popover
        placement="top"
        content={
          <div>
            {items.map((item, index) => {
              return (
                <span key={index}>
                  <span>{`${item.product_name} - ${item.qty}`}</span> <br />
                </span>
              )
            })}
          </div>
        }
        title="Title"
        trigger="click"
      >
        <Button
          type="link"
          className="text-[#000]"
          onClick={() => loadData()}
          loading={loading}
        >
          <span> {value} </span>
        </Button>
      </Popover>
      <CopyOutlined onClick={() => copyToClipboard(value)} />
    </div>
  )
}

export default SalesOrderPopover
