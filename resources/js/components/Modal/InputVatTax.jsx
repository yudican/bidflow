import { Form, Input, message, Select } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"

const InputVatTax = ({
  url,
  refetch,
  initialValues = {},
  disabled = false,
  tax,
}) => {
  const [form] = Form.useForm()
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [loading, setLoading] = useState(false)
  const [taxs, setTaxs] = useState([])
  const [loadingSite, setLoadingSite] = useState(false)

  const loadTaxs = () => {
    setLoadingSite(true)
    axios
      .get("/api/master/taxs")
      .then((res) => {
        setLoadingSite(false)
        setTaxs(res.data.data)
      })
      .catch(() => {
        setLoadingSite(false)
      })
  }

  useEffect(() => {
    loadTaxs()
  }, [])

  const onFinish = (values) => {
    setLoading(true)
    axios
      .post(url, values)
      .then((res) => {
        setLoading(false)
        setIsModalOpen(false)
        message.success("Tax berhasil diupdate")
        refetch()
      })
      .catch((err) => {
        setLoading(false)
        message.error("Tax gagal diupdate")
      })
  }

  if (disabled) {
    return <Input value={tax} disabled placeholder="Pilih TAX" />
  }

  return (
    <div>
      <Form
        form={form}
        name="basic"
        layout="vertical"
        onFinish={onFinish}
        autoComplete="off"
        initialValues={{
          tax_id: initialValues?.tax_id
            ? parseInt(initialValues?.tax_id)
            : null,
        }}
      >
        <Form.Item name="tax_id">
          <Select
            style={{
              // marginTop: 16,
              top: 12,
            }}
            placeholder="Pilih TAX"
            loading={loadingSite}
            onChange={() => {
              form.submit()
            }}
          // showSearch
          // filterOption={(input, option) => {
          //   return (option?.children ?? "")
          //     .toLowerCase()
          //     .includes(input.toLowerCase())
          // }}
          >
            {taxs.map((tax) => (
              <Select.Option key={tax.id} value={tax.id}>
                {tax.tax_code}
              </Select.Option>
            ))}
          </Select>
        </Form.Item>
      </Form>
    </div>
  )
}

export default InputVatTax
