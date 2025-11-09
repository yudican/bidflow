import React, { useEffect, useState } from "react"
import { useNavigate, useParams } from "react-router-dom"
import { Modal, Button, Form, Select, Divider, Input, Space, Card } from "antd"
import TextArea from "antd/lib/input/TextArea"
import { LoadingOutlined, PlusOutlined } from "@ant-design/icons"
import DebounceSelect from "../../../components/atoms/DebounceSelect"
import { searchContact } from "./../services"
import { toast } from "react-toastify"

const { Option } = Select

const ModalGeneratePo = ({ isOpen, onClose, handleOk }) => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const { purchase_requisition_id } = useParams()
  const [vendors, setVendors] = useState([])
  const [vendorCode, setVendorCode] = useState(null)
  const [showSelect, setShowSelect] = useState(false)
  const [warehouses, setWarehouses] = useState([])
  const [warehouseUsers, setWarehouseUsers] = useState([])
  const [typePo, setTypePo] = useState("perlengkapan")

  const loadWarehouse = () => {
    axios.get("/api/master/warehouse").then((res) => {
      setWarehouses(res.data.data)
    })
  }

  const loadVendors = () => {
    axios.get("/api/master/vendors").then((res) => {
      setVendors(res.data.data)
    })
  }

  const handleGetContact = () => {
    searchContact(null).then((results) => {
      const newResult = results.map((result) => {
        return { label: result.nama, value: result.id }
      })
      setWarehouseUsers(newResult)
    })
  }

  const handleSearchContact = async (e) => {
    return searchContact(e).then((results) => {
      const newResult = results.map((result) => {
        return { label: result.nama, value: result.id }
      })

      return newResult
    })
  }

  const onFinish = (values) => {
    const form = {
      ...values,
      uid_requitition: purchase_requisition_id,
    }
    axios
      .post("/api/purchase/purchase-order/generate", form)
      .then((res) => {
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        onClose()
        return navigate("/purchase/purchase-order")
      })
      .catch((err) => {
        const { message } = err.response.data
        toast.error(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }

  useEffect(() => {
    handleGetContact()
    loadWarehouse()
    loadVendors()
  }, [])

  return (
    <Modal
      title="Generate PO"
      open={isOpen}
      onCancel={onClose}
      footer={[
        <Button key="cancel" onClick={onClose}>
          Cancel
        </Button>,
        <Button key="submit" type="primary" onClick={() => form.submit()}>
          Submit
        </Button>,
      ]}
    >
      <Form form={form} layout="vertical" onFinish={onFinish}>
        <Form.Item
          label="Kategori Permintaan"
          name="kategori_pr"
          rules={[
            { required: true, message: "Silakan Pilih Kategori Permintaan!" },
          ]}
        >
          <Select placeholder="Pilih Kategori Permintaan">
            <Option value="Asset">Asset</Option>
            <Option value="Supplies Consumable">Supplies Consumable</Option>
            <Option value="Jasa">Jasa</Option>
            <Option value="Lainnya">Lainnya</Option>
          </Select>
        </Form.Item>
        <Form.Item
          label="Vendor Code"
          name="vendor_code"
          rules={[
            {
              required: true,
              message: "Silakan pilih Vendor Code!",
            },
          ]}
        >
          <Select
            showSearch
            filterOption={(input, option) => {
              return (option?.label ?? "")
                .toLowerCase()
                .includes(input.toLowerCase())
            }}
            className="w-full"
            placeholder="Pilih vendor code"
            onChange={(value) => {
              const vendor = vendors.find((item) => item.code === value)
              form.setFieldsValue({
                vendor_code: value,
                vendor_name: vendor.name,
              })
              setShowSelect(false)
            }}
            dropdownRender={(menu) => (
              <>
                {menu}
                <Divider
                  style={{
                    margin: "8px 0",
                  }}
                />
                <Space
                  style={{
                    padding: "0 8px 4px",
                  }}
                >
                  <Input
                    placeholder="Please enter item"
                    value={vendorCode}
                    onChange={(e) => setVendorCode(e.target.value)}
                    className="w-full"
                  />
                  <Button
                    type="text"
                    icon={<PlusOutlined />}
                    onClick={() => {
                      // form.setFieldsValue({
                      //     vendor_code: vendorCode,
                      //     vendor_name: null,
                      // })

                      setVendors([{ code: vendorCode, name: null }, ...vendors])
                      setShowSelect(false)
                    }}
                  >
                    Add item
                  </Button>
                </Space>
              </>
            )}
            options={vendors.map((vendor) => {
              return {
                value: vendor.code,
                label: vendor.code,
              }
            })}
          />
        </Form.Item>
        <Form.Item
          label="Vendor Name"
          name="vendor_name"
          rules={[
            {
              required: true,
              message: "Silakan masukkan Vendor Name!",
            },
          ]}
        >
          <Input placeholder="Silakan input vendor name.." />
        </Form.Item>
        <Form.Item
          label="Tipe PO"
          name="type_po"
          rules={[
            {
              required: true,
              message: "Silakan pilih Tipe PO!",
            },
          ]}
        >
          <Select
            showSearch
            filterOption={(input, option) => {
              return (option?.children ?? "")
                .toLowerCase()
                .includes(input.toLowerCase())
            }}
            placeholder="Silakan pilih"
            onChange={(e) => {
              setTypePo(e)
            }}
          >
            <Select.Option value={"product"}>Product</Select.Option>
            <Select.Option value={"pengemasan"}>Pengemasan</Select.Option>
            <Select.Option value={"perlengkapan"}>Perlengkapan</Select.Option>
          </Select>
        </Form.Item>
        <Form.Item
          label="Warehouse"
          name="warehouse_id"
          rules={[
            {
              required: true,
              message: "Silakan pilih Warehouse!",
            },
          ]}
        >
          <Select
            showSearch
            filterOption={(input, option) => {
              return (option?.children ?? "")
                .toLowerCase()
                .includes(input.toLowerCase())
            }}
            placeholder="Silakan pilih"
            onChange={(e) => {
              // get address
              const warehouse = warehouses.find(
                (warehouse) => warehouse.id === e
              )
              form.setFieldsValue({
                warehouse_address: warehouse.alamat,
              })
            }}
          >
            {warehouses.map((warehouse, index) => (
              <Select.Option key={index} value={warehouse.id}>
                {warehouse.name}
              </Select.Option>
            ))}
          </Select>
        </Form.Item>

        <Form.Item
          label="PIC Warehouse"
          name="warehouse_pic"
          rules={[
            {
              required: true,
              message: "Silakan pilih PIC Warehouse!",
            },
          ]}
        >
          <DebounceSelect
            showSearch
            placeholder="Silakan pilih"
            fetchOptions={handleSearchContact}
            filterOption={false}
            className="w-full"
            defaultOptions={warehouseUsers}
          />
        </Form.Item>
        <Form.Item
          requiredMark={"Automatic"}
          label="Detail Alamat Warehouse (Automatic)"
          name="warehouse_address"
          rules={[
            {
              required: false,
              message: "Silakan masukkan Warehouse!",
            },
          ]}
        >
          <TextArea
            placeholder="Silakan input catatan.."
            showCount
            maxLength={100}
          />
        </Form.Item>
      </Form>
    </Modal>
  )
}

export default ModalGeneratePo
