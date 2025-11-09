import React, { useEffect, useState } from "react"
import { Modal, Button, Table, Form, Input, Select, Divider, Space } from "antd"
import TextArea from "antd/lib/input/TextArea"
import { PlusOutlined } from "@ant-design/icons"
import axios from "axios"
import { toast } from "react-toastify"
import DebounceSelect from "../../../components/atoms/DebounceSelect"
import { searchContact } from "./../services"
import { useNavigate } from "react-router-dom"

const { Option } = Select

const ModalBulkPo = ({ visible, onCancel, onSubmit, data }) => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const [selectedRowKeys, setSelectedRowKeys] = useState([])
  const [selectAll, setSelectAll] = useState(false)
  const [vendors, setVendors] = useState([])
  const [vendorCode, setVendorCode] = useState(null)
  const [showSelect, setShowSelect] = useState(false)
  const [warehouses, setWarehouses] = useState([])
  const [warehouseUsers, setWarehouseUsers] = useState([])
  const [typePo, setTypePo] = useState("perlengkapan")

  const loadVendors = () => {
    axios.get("/api/master/vendors").then((res) => {
      setVendors(res.data.data)
    })
  }

  const loadWarehouse = () => {
    axios.get("/api/master/warehouse").then((res) => {
      setWarehouses(res.data.data)
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

  useEffect(() => {
    handleGetContact()
    loadWarehouse()
    loadVendors()
  }, [])

  const handleSearchContact = async (e) => {
    return searchContact(e).then((results) => {
      const newResult = results.map((result) => {
        return { label: result.nama, value: result.id }
      })

      return newResult
    })
  }

  const handleSelectChange = (selectedKeys) => {
    setSelectedRowKeys(selectedKeys)
    setSelectAll(selectedKeys.length === data.length)
  }

  const handleSubmit = () => {
    if (selectedRowKeys.length === 0) {
      toast.error("Silakan pilih data PR terlebih dahulu untuk Generate PO")
      return
    }

    // Get the selected items
    const selectedItems = data.filter((item) =>
      selectedRowKeys.includes(item.id)
    )

    // Get unique brand IDs from the selected items
    const uniqueBrandIds = [
      ...new Set(selectedItems.map((item) => item.brand_id)),
    ]

    if (uniqueBrandIds.length > 1) {
      toast.error("Maaf.. data PR harus memiliki Brand ID yang sama")
      return
    }

    form
      .validateFields()
      .then((values) => {
        // Prepare the data payload
        const payload = {
          ids: selectedRowKeys,
          vendor_code: values.vendor_code,
          vendor_name: values.vendor_name,
          type_po: values.type_po,
          warehouse_id: values.warehouse_id,
          warehouse_address: values.warehouse_address,
          warehouse_pic: values.warehouse_pic,
          kategori_pr: values.kategori_pr,
        }

        // Send the data to the API
        axios
          .post("/api/purchase/purchase-order/generate-bulk", payload, {
            headers: {
              "Content-Type": "application/json",
            },
          })
          .then((res) => {
            const { data } = res.data
            toast.success("Data berhasil disimpan")
            onSubmit()
          })
          .catch((e) => {
            console.error(e)
            toast.error("Data gagal disimpan")
          })
      })
      .catch((info) => {
        console.log("Validate Failed:", info)
      })
  }

  const handleWarehouseChange = (value) => {
    const selectedWarehouse = warehouses.find(
      (warehouse) => warehouse.id === value
    )
    if (selectedWarehouse) {
      form.setFieldsValue({
        warehouse_address: selectedWarehouse.alamat,
      })
    } else {
      form.setFieldsValue({
        warehouse_address: "",
      })
    }
  }

  const rowSelection = {
    selectedRowKeys,
    onChange: handleSelectChange,
    getCheckboxProps: (record) => ({
      disabled: record.company_account_id != localStorage.getItem("account_id"),
    }),
  }

  const columns = [
    {
      title: "No.",
      dataIndex: "",
      key: "index",
      render: (value, row, index) => index + 1,
    },
    {
      title: "PR Number",
      dataIndex: "pr_number",
      key: "pr_number",
    },
    {
      title: "Brand ID",
      dataIndex: "brand_name",
      key: "brand_name",
    },
    {
      title: "Account",
      dataIndex: "company_account_id",
      key: "company_account_id",
      render: (value) => {
        if (value === 1) {
          return "PT"
        } else if (value) {
          return "Non PT"
        } else {
          return "-"
        }
      },
    },
  ]

  return (
    <Modal
      visible={visible}
      title="Bulk Generate PO"
      onCancel={onCancel}
      footer={[
        <Button key="cancel" onClick={onCancel}>
          Cancel
        </Button>,
        <Button key="submit" type="primary" onClick={handleSubmit}>
          Submit
        </Button>,
      ]}
      width={"92%"}
    >
      <div className="grid lg:grid-cols-2 gap-4">
        <div>
          <div className="text-center">
            <h2 className="font-semibold">Data Purchase Requisition</h2>
            <p className="text-gray-400 line-clamp-2">
              Silakan pilih nomor pr yang akan di generate <br></br>sebagai
              purchase order.
            </p>
          </div>
          <Table
            rowKey="id"
            columns={columns}
            dataSource={data}
            pagination={false}
            rowSelection={rowSelection}
            scroll={{ y: 240 }}
          />
        </div>
        <div className="lg:border-l pl-3">
          <div className="text-center">
            <h2 className="font-semibold">
              GENERATE MULTIPLE PURCHASE REQUISITION
            </h2>
            <p className="text-gray-400 line-clamp-2">
              Silakan lengkapi formulir di bawah ini untuk melanjutkan proses.
              Sistem akan melakukan Generate Multiple Purchase Requisition
              setelah data sudah dilengkapi.
            </p>
          </div>
          <Form layout="vertical" form={form}>
            <Form.Item
              label="Kategori Permintaan"
              name="kategori_pr"
              rules={[
                {
                  required: true,
                  message: "Silakan Pilih Kategori Permintaan!",
                },
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
                  message: "Silakan masukkan Vendor Code!",
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

                          setVendors([
                            { code: vendorCode, name: null },
                            ...vendors,
                          ])
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
                  message: "Silakan isi Vendor Name!",
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
                <Select.Option value={"perlengkapan"}>
                  Perlengkapan
                </Select.Option>
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
                onChange={handleWarehouseChange}
              >
                {warehouses.map((warehouse) => (
                  <Select.Option key={warehouse.id} value={warehouse.id}>
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
        </div>
      </div>
    </Modal>
  )
}

export default ModalBulkPo
