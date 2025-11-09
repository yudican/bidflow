import {
  CheckCircleOutlined,
  CloseCircleOutlined,
  LoadingOutlined,
  SyncOutlined,
  UploadOutlined,
} from "@ant-design/icons"
import { Form, Modal, Select, Table } from "antd"
import axios from "axios"
import moment from "moment"
import React, { useState } from "react"
import { toast } from "react-toastify"
import { formatDate, formatNumber, inArray } from "../../../helpers"

const productSubmitColumns = [
  {
    title: "No",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Nama Produk",
    dataIndex: "product_name",
    key: "product_name",
  },

  {
    title: "SKU",
    dataIndex: "sku",
    key: "sku",
  },
]

const ModalTax = ({
  handleSubmit,
  products = [],
  onChange,
  type = "so",
  title = "Submit To GP",
  titleModal,
  isAction = false,
  loading = false,
  asMenuItem = false,
  orderIds = [],
}) => {
  const [form] = Form.useForm()
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [selectedRowKeys, setSelectedRowKeys] = useState([])
  const [taxs, setTaxs] = useState([])
  const [selectedTax, setSelectedTax] = useState(null)
  const [productItems, setProductItems] = useState([])
  const [sites, setSite] = useState([])
  const [batchIds, setBatchIds] = useState([])
  const [loadingSite, setLoadingSite] = useState(false)
  const [loadingBatchId, setLoadingBatchId] = useState(false)
  const [loadingProduct, setLoadingProduct] = useState(false)
  const [vatValue, setVatValue] = useState(0)
  const [taxValue, setTaxValue] = useState(0)
  const showModal = () => {
    loadTaxs()
    loadSite()
    loadBatch()
    if (inArray(type, ["so"])) {
      loadProducts()
    }

    setIsModalOpen(true)
    // form.setFieldsValue({
    //   vat_value: 0,
    //   tax_value: 0,
    // })
  }

  const loadTaxs = () => {
    axios.get("/api/master/taxs").then((res) => {
      setTaxs(res.data.data)
    })
  }

  const loadProducts = () => {
    setLoadingProduct(true)
    axios
      .post("/api/sales-order/items", { uid_lead: orderIds })
      .then((res) => {
        setProductItems(res.data.data)
        onChange(res.data.data, null, "products")
        setLoadingProduct(false)
      })
      .catch(() => setLoadingProduct(false))
  }

  const loadSite = () => {
    setLoadingSite(true)
    axios
      .get("/api/master/site")
      .then((res) => {
        // console.log(res, "res site")
        setSite(res.data.data)
        setLoadingSite(false)
      })
      .catch((err) => setLoadingSite(false))
  }

  const loadBatch = () => {
    setLoadingBatchId(true)
    axios
      .get("/api/master/batchId")
      .then((res) => {
        setBatchIds(res.data.data)
        setLoadingBatchId(false)
      })
      .catch((err) => setLoadingBatchId(false))
  }

  const handleCancel = () => {
    setIsModalOpen(false)
    form.resetFields()
  }

  const rowSelection = {
    selectedRowKeys,
    onChange: (newSelectedRowKeys) => {
      return setSelectedRowKeys(newSelectedRowKeys)
    },
    getCheckboxProps: (record) => ({
      disabled: record.gp_received_number, // Column configuration not to be checked
    }),
  }

  const so_button_disabled =
    productItems.filter((item) => !item.gp_submit_number)?.length < 1
  return (
    <div className=" mr-2">
      {isAction ? (
        <button
          className={
            asMenuItem
              ? ""
              : "bg-blue-700 border text-white hover:text-white delay-100 ease-in-out focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center w-36 mx-auto mr-4"
          }
          onClick={showModal}
        >
          <span className="">{title}</span>
        </button>
      ) : (
        <button
          // className="text-white bg-mainColor hover:bg-mainColor/90 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mr-4"
          onClick={showModal}
        >
          {type === "reset-gp" ? (
            <SyncOutlined />
          ) : loading ? (
            <LoadingOutlined />
          ) : (
            <UploadOutlined />
          )}
          <span className="ml-2">{title}</span>
        </button>
      )}

      <Modal
        title={titleModal || "Pilih Data"}
        open={isModalOpen}
        onOk={() => {
          if (inArray(type, ["payment", "receiving"])) {
            if (selectedRowKeys.length < 1) {
              return toast.error("Mohon pilih salah satu")
            }
          }
          setIsModalOpen(false)
          form.submit()
        }}
        onCancel={handleCancel}
        width={800}
        okText="Submit"
        confirmLoading={loading}
        okButtonProps={{
          disabled: type === "so" ? so_button_disabled : products?.length < 1,
        }}
      >
        <Form
          form={form}
          name="basic"
          layout="vertical"
          onFinish={(values) => {
            handleSubmit({
              vat_value: vatValue,
              tax_value: taxValue,
              receivingIds: selectedRowKeys,
              vat: selectedTax,
              ...values,
            })
          }}
          autoComplete="off"
        >
          <div>
            {!inArray(type, [
              "transfer",
              "billing",
              "receiving",
              "po",
              "payment",
              "klik-pajak",
              "reset-gp",
            ]) && (
              <Form.Item
                label="Tax"
                name="vat"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Tax!",
                  },
                ]}
              >
                {/* <Input placeholder="1.11" /> */}
                <Select
                  placeholder="Pilih Tax"
                  onChange={(e) => {
                    setSelectedTax(e)
                    const selected = taxs.find((item) => item.tax_code === e)
                    if (selected) {
                      setVatValue(0)
                      setTaxValue(parseInt(selected.tax_percentage))
                    }
                  }}
                >
                  {taxs.map((item) => (
                    <Select.Option key={item.id} value={item.tax_code}>
                      {item.tax_code}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>
            )}
            {type === "po" && (
              <Form.Item
                label="Site ID"
                name="warehouse_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Site ID",
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
                  placeholder="Pilih Site ID"
                  loading={loadingSite}
                >
                  {sites.map((item) => {
                    // console.log(item, "wh")
                    return (
                      <Select.Option key={item.id} value={item.site_id}>
                        {`${item.site_id} - ${item.warehouse_name}`}
                      </Select.Option>
                    )
                  })}
                </Select>
              </Form.Item>
            )}
          </div>
        </Form>

        {type === "so" && (
          <Table
            dataSource={productItems.filter((item) => !item.gp_submit_number)}
            loading={loadingProduct}
            columns={[
              ...productSubmitColumns,
              {
                title: "SITE ID",
                dataIndex: "loc_node",
                key: "loc_node",
                render: (text, record, index) => {
                  return (
                    <Select
                      loading={loadingSite}
                      value={record.loc_node} // new state for mapping all index loc_node (old mechanism doesnt need to put value props)
                      onChange={(e) => {
                        onChange(e, index, "loc_node")
                      }}
                      placeholder="Pilih Site ID"
                    >
                      {sites.map((item) => (
                        <Select.Option key={item.id} value={item.site_id}>
                          {item.site_id}
                        </Select.Option>
                      ))}
                    </Select>
                  )
                },
              },
              {
                title: "Batch ID",
                dataIndex: "batch_number",
                key: "batch_number",
                render: (text, record, index) => {
                  return (
                    <Select
                      loading={loadingBatchId}
                      value={record.batch_number} // new state for mapping all index loc_node (old mechanism doesnt need to put value props)
                      onChange={(e) => {
                        onChange(e, index, "batch_number")
                      }}
                      placeholder="Pilih Batch ID"
                    >
                      {batchIds.map((item) => (
                        <Select.Option key={item.id} value={item.batch_code}>
                          {item.batch_code}
                        </Select.Option>
                      ))}
                    </Select>
                  )
                },
              },
            ]}
            pagination={false}
          />
        )}
        {type === "telmark" && (
          <Table
            dataSource={products}
            loading={loadingProduct}
            columns={[
              {
                title: "No",
                dataIndex: "id",
                key: "id",
                render: (text, record, index) => index + 1,
              },
              {
                title: "ID Transaksi",
                dataIndex: "id_transaksi",
                key: "id_transaksi",
              },
              {
                title: "SITE ID",
                dataIndex: "loc_node",
                key: "loc_node",
                render: (text, record, index) => {
                  return (
                    <Select
                      loading={loadingSite}
                      value={record.loc_node} // new state for mapping all index loc_node (old mechanism doesnt need to put value props)
                      onChange={(e) => {
                        onChange(e, index, "loc_node")
                      }}
                      placeholder="Pilih Site ID"
                    >
                      {sites.map((item) => (
                        <Select.Option key={item.id} value={item.site_id}>
                          {item.site_id}
                        </Select.Option>
                      ))}
                    </Select>
                  )
                },
              },
              {
                title: "Batch ID",
                dataIndex: "batch_number",
                key: "batch_number",
                render: (text, record, index) => {
                  return (
                    <Select
                      loading={loadingBatchId}
                      value={record.batch_number} // new state for mapping all index loc_node (old mechanism doesnt need to put value props)
                      onChange={(e) => {
                        onChange(e, index, "batch_number")
                      }}
                      placeholder="Pilih Batch ID"
                    >
                      {batchIds.map((item) => (
                        <Select.Option key={item.id} value={item.batch_code}>
                          {item.batch_code}
                        </Select.Option>
                      ))}
                    </Select>
                  )
                },
              },
            ]}
            pagination={false}
          />
        )}
        {type === "klik-pajak" && (
          // <Table
          //   dataSource={products}
          //   columns={[
          //     {
          //       title: "No. ",
          //       dataIndex: "id",
          //       key: "id",
          //       render: (_, record, index) => index + 1,
          //     },
          //     {
          //       title: "Invoice Number",
          //       dataIndex: "invoice_number",
          //       key: "invoice_number",
          //     },
          //   ]}
          //   pagination={false}
          // />
          <div>Apakah anda yakin ingin submit ke Klik Pajak ?</div>
        )}
        {type === "reset-gp" && (
          <div>Apakah anda yakin ingin melakukan Reset SI GP ?</div>
        )}
        {type === "marketplace" && (
          <Table
            dataSource={products}
            columns={[
              {
                title: "No",
                dataIndex: "id",
                key: "id",
                render: (text, record, index) => index + 1,
              },
              {
                title: "TRX ID",
                dataIndex: "trx_id",
                key: "trx_id",
              },
              {
                title: "Nama Produk",
                dataIndex: "product_name",
                key: "product_name",
              },

              {
                title: "SKU",
                dataIndex: "sku",
                key: "sku",
              },
              {
                title: "SITE ID",
                dataIndex: "loc_node",
                key: "loc_node",
                render: (text, record, index) => {
                  return (
                    <Select
                      onChange={(e) => onChange(e, index, "loc_node")}
                      placeholder="Pilih Site ID"
                    >
                      {sites.map((item) => (
                        <Select.Option key={item.id} value={item.site_id}>
                          {item.site_id}
                        </Select.Option>
                      ))}
                    </Select>
                  )
                },
              },
              {
                title: "Batch ID",
                dataIndex: "batch_number",
                key: "batch_number",
                render: (text, record, index) => {
                  return (
                    <Select
                      loading={loadingBatchId}
                      value={record.batch_number} // new state for mapping all index loc_node (old mechanism doesnt need to put value props)
                      onChange={(e) => {
                        onChange(e, index, "batch_number")
                      }}
                      placeholder="Pilih Batch ID"
                    >
                      {batchIds.map((item) => (
                        <Select.Option key={item.id} value={item.batch_code}>
                          {item.batch_code}
                        </Select.Option>
                      ))}
                    </Select>
                  )
                },
              },
            ]}
            pagination={false}
          />
        )}

        {type === "receiving" && (
          <Table
            rowSelection={rowSelection}
            dataSource={products}
            columns={[
              {
                title: "No",
                dataIndex: "id",
                key: "id",
                render: (text, record, index) => index + 1,
              },
              {
                title: "Nomor PO",
                dataIndex: "po_number",
                key: "po_number",
              },
              {
                title: "Received Number",
                dataIndex: "received_number",
                key: "received_number",
              },
              {
                title: "Nama Produk",
                dataIndex: "product_name",
                key: "product_name",
              },
              {
                title: "Qty Diterima",
                dataIndex: "qty_diterima",
                key: "qty_diterima",
                render: (text, record, index) => {
                  return text || "-"
                },
              },
              {
                title: "Received Date",
                dataIndex: "received_date",
                key: "received_date",
                render: (text) => {
                  return text ? moment(text).format("DD-MM-YYYY") : "-"
                },
              },
            ]}
            rowKey={"item_id"}
            pagination={false}
          />
        )}
        {type === "payment" && (
          <Table
            scroll={{ x: "max-content" }}
            tableLayout={"auto"}
            rowSelection={rowSelection}
            dataSource={products}
            columns={[
              {
                title: "No",
                dataIndex: "id",
                key: "id",
                render: (text, record, index) => index + 1,
              },

              {
                title: "Received Number",
                dataIndex: "received_number",
                key: "received_number",
              },

              {
                title: "GP Payment Number",
                dataIndex: "gp_payable_number",
                key: "gp_payable_number",
                align: "center",
                render: (text, record, index) => {
                  return text || "-"
                },
                // key: "gp_invoice_number",
              },
              {
                title: "Nama Bank",
                dataIndex: "nama_bank",
                key: "nama_bank",
              },
              {
                title: "Nama Pengirim",
                dataIndex: "nama_pengirim",
                key: "nama_pengirim",
              },
              {
                title: "No Rekening",
                dataIndex: "no_rekening",
                key: "no_rekening",
              },
              {
                title: "Nominal",
                dataIndex: "jumlah_transfer",
                key: "jumlah_transfer",
                render: (text) => {
                  return formatNumber(text, "Rp ")
                },
              },
              {
                title: "Tax Amount",
                dataIndex: "tax_amount",
                key: "tax_amount",
                render: (text) => {
                  return formatNumber(text, "Rp ")
                },
              },
              {
                title: "Submit GP",
                dataIndex: "status_gp",
                key: "status_gp",
                align: "center",
                render: (text) => {
                  if (text === "submited") {
                    return <CheckCircleOutlined style={{ color: "#7C9B3A" }} />
                  }
                  return <CloseCircleOutlined style={{ color: "#FE3A30" }} />
                },
              },
              {
                title: "Created On",
                dataIndex: "created_at",
                key: "created_at",
                render: (text) => {
                  return formatDate(text)
                },
              },
              {
                title: "Created by",
                dataIndex: "created_by_name",
                key: "created_by_name",
              },
              {
                title: "Struct Transfer",
                dataIndex: "bukti_transfer_url",
                key: "bukti_transfer_url",
                align: "center",
                render: (text) => {
                  if (text) {
                    return (
                      <a href={text} target="_blank" rel="noreferrer">
                        Lihat Bukti
                      </a>
                    )
                  }
                  return "-"
                },
              },
              {
                title: "Approved by",
                dataIndex: "approved_by_name",
                key: "approved_by_name",
              },
            ]}
            rowKey={"item_id"}
            pagination={false}
          />
        )}
        {type === "transfer" && (
          <Table
            dataSource={products}
            columns={[
              {
                title: "No",
                dataIndex: "id",
                key: "id",
                render: (text, record, index) => index + 1,
              },

              {
                title: "Product Name",
                dataIndex: "product_name",
                key: "product_name",
              },
              {
                title: "Warehouse",
                dataIndex: "warehouse_name",
                key: "warehouse_name",
              },
              {
                title: "Warehouse Destination",
                dataIndex: "warehouse_destination_name",
                key: "warehouse_destination_name",
              },
            ]}
            rowKey={"item_id"}
            pagination={false}
          />
        )}
        {type === "billing" && (
          <Table
            dataSource={products}
            columns={[
              {
                title: "No",
                dataIndex: "id",
                key: "id",
                render: (text, record, index) => index + 1,
              },

              {
                title: "Payment Number",
                dataIndex: "payment_number",
                key: "payment_number",
              },
              {
                title: "Nama Bank",
                dataIndex: "account_bank",
                key: "account_bank",
              },
              {
                title: "Nama Rekening",
                dataIndex: "account_name",
                key: "account_name",
              },
              {
                title: "Total Transfer",
                dataIndex: "total_transfer",
                key: "total_transfer",
                render: (text, record, index) => formatNumber(text, "Rp. "),
              },
            ]}
            rowKey={"item_id"}
            pagination={false}
          />
        )}
      </Modal>
    </div>
  )
}

export default ModalTax
