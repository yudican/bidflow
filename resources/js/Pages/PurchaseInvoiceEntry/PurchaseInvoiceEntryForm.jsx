import {
  CheckCircleFilled,
  CloseOutlined,
  LoadingOutlined,
  PlusOutlined,
  SearchOutlined,
} from "@ant-design/icons"
import {
  Button,
  Card,
  DatePicker,
  Form,
  Input,
  Modal,
  Select,
  Table,
  Tooltip,
} from "antd"
import axios from "axios"
import moment from "moment"
import React, { useEffect, useState } from "react"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import LoadingFallback from "../../components/LoadingFallback"
import Layout from "../../components/layout"
import { formatNumber, getItem, inArray } from "../../helpers"
import PurchaseBillingModal from "../Purchase/Components/PurchaseBillingModal"
import { purchaseBillingFormListColumn } from "./config"
import TextArea from "antd/lib/input/TextArea"

const notes = `Pembayaran akan diproses dengan dokumen-dokumen berikut 
-	Invoice Asli 
-	Faktur Pajak 
-	Surat Jalan 
-	Copy PO (Invoice Entry) 
-	Jumlah pengiriman barang harus sesuai dengan PO (Invoice Entry) \n
Semua dokumen diatas mohon dikirimkan ke PT Anugrah Inovasi Makmur Indonesia, Jl. Boulevard Raya, Ruko Malibu Blok J 128-129, Cengkareng Jakarta Barat.`

const PurchaseInvoiceEntryForm = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const { purchase_invoice_entry_id } = useParams()

  // state
  const defaultItems = [
    {
      key: 0,
      id: null,
      po_number: null,
      product_id: null,
      product_name: null,
      uom: null,
      sku: null,
      qty: 1,
      tax: null,
      extended_cost: 0,
      purchase_order_item_id: null,
    },
  ]

  const [loading, setLoading] = useState(false)
  const [status, setStatus] = useState(-1)
  const [loadingSubmit, setLoadingSubmit] = useState(false)
  const [detail, setDetail] = useState(false)
  const [termOfPayments, setTermOfPayments] = useState([])
  const [vendors, setVendors] = useState([])
  const [purchases, setPurchases] = useState([])
  const [typeInvoice, setTypeInvoice] = useState("product")
  const [products, setProducts] = useState([])
  const [checkBooks, setCheckbooks] = useState([])
  const [taxs, setTaxs] = useState([])
  const [selectedTax, setSelectedTax] = useState(null)
  const [loadingSite, setLoadingSite] = useState(false)
  // console.log(
  //   purchases.map((value) => value.po_number),
  //   "purchases"
  // )
  const [productItem, setProductItem] = useState(defaultItems)
  const [productItems, setProductItems] = useState([])
  const [billingsItem, setBillingItems] = useState([])
  const user = getItem("user_data", true)

  // modal po state
  const [isPoModalVisible, setIsPoModalVisible] = useState(false)
  const [search, setSearch] = useState("")
  const [selectedPo, setselectedPo] = useState(null)

  const loadDetail = () => {
    if (purchase_invoice_entry_id) {
      setLoading(true)
      axios
        .get(`/api/purchase/invoice-entry/${purchase_invoice_entry_id}`)
        .then((res) => {
          const { data } = res.data
          setLoading(false)
          setDetail(data)
          setTypeInvoice(data?.type_invoice || "product")
          form.setFieldsValue({
            ...data,
            invoice_date: moment(
              data?.invoice_date ?? new Date(),
              "YYYY-MM-DD"
            ),
            created_by: data?.created_by_name,
            payment_term_id: parseInt(data?.payment_term_id),
          })

          setProductItem(data.items || [])
        })
        .catch((e) => {
          setLoading(false)
          form.setFieldValue("invoice_date", moment(new Date(), "YYYY-MM-DD"))
        })
    }
  }

  const loadTop = () => {
    axios.get("/api/master/top").then((res) => {
      setTermOfPayments(res.data.data)
    })
  }

  // const loadTaxs = () => {
  //   axios.get("/api/master/taxs").then((res) => {
  //     setTaxs(res.data.data)
  //   })
  // }

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

  const loadVendors = () => {
    axios.get("/api/master/vendors").then((res) => {
      const refactoredData = res.data.data.map((item) => {
        return {
          value: item.code,
          label: item.name,
        }
      })
      setVendors(refactoredData)
    })
  }

  const loadRcvNumber = () => {
    axios.get("/api/purchase/invoice-entry/get/rcv-number").then((res) => {
      form.setFieldValue("received_number", res.data.data)
    })
  }

  const loadPurchases = (vendor_code) => {
    axios
      .get("/api/purchase/invoice-entry/get/po-number/" + vendor_code)
      .then((res) => {
        setPurchases(res.data.data || [])
      })
  }

  const loadProducts = () => {
    axios.get("/api/master/product-lists").then((res) => {
      setProducts(res.data.data)
    })
  }

  const loadCheckbooks = () => {
    axios.get("/api/general/checkbook").then((res) => {
      setCheckbooks(res.data.data)
    })
  }

  useEffect(() => {
    loadDetail()
    loadTop()
    loadVendors()
    loadRcvNumber()
    loadProducts()
    loadCheckbooks()
    loadTaxs()
  }, [])

  const onFinish = (value) => {
    // console.log(value)
    setLoadingSubmit(true)
    const form = {
      ...value,
      purchase_invoice_entry_id,
      status: billingsItem.length > 0 ? 1 : status,
      items:
        typeInvoice == "product"
          ? productItem.filter((item) => item.id)
          : productItem,
      billings: billingsItem,
    }
    console.log(form)
    axios
      .post("/api/purchase/invoice-entry/save", form)
      .then((res) => {
        setLoadingSubmit(false)
        toast.success("Data berhasil disimpan")
        return navigate("/purchase/invoice-entry")
      })
      .catch((err) => {
        setLoadingSubmit(false)
        toast.error(err.response.data.message || "Data gagal disimpan")
      })
  }

  const handleTaxChange = (value) => {
    console.log(value)
    // Find the selected tax based on the value
    // const selectedTax = taxs.find((item) => item.tax_id === value);
    const selectedTax = taxs.find((element) => element.id === parseInt(value))
    console.log(selectedTax)
    // Update the state with the selected tax
    setSelectedTax(selectedTax)
  }

  const handleAddMore = () => {
    const productNewItem = [...productItem]

    productNewItem.push({
      key: productNewItem.length, // atau sesuai dengan logika pemberian key yang Anda gunakan
      id: null,
      po_number: null,
      product_id: null,
      product_name: null,
      uom: null,
      sku: null,
      qty: 1,
      tax: null,
      extended_cost: 0,
      purchase_order_item_id: null,
    })
    setProductItem(productNewItem)
  }

  const role = getItem("role")
  const canCreate = inArray(role, [
    "finance",
    "admin",
    "superadmin",
    "adminsales",
  ])

  if (loading) {
    return (
      <Layout title="Tambah Data Invoice Entry" href="/purchase/invoice-entry">
        <LoadingFallback />
      </Layout>
    )
  }

  const invoiceColumns =
    typeInvoice == "product"
      ? [
          {
            title: "PO Number",
            dataIndex: "po_number",
            key: "po_number",
            render: (text, record, index) => {
              return (
                <>
                  <div
                    className="w-32 flex items-center border py-1 px-2 rounded-sm line-clamp-1 cursor-pointer"
                    onClick={() => {
                      setIsPoModalVisible(true)
                    }}
                  >
                    <SearchOutlined className="mr-2" />
                    <span>
                      {record.po_number ? record.po_number : "Select Product"}
                    </span>
                  </div>

                  <Modal
                    title="PO Number"
                    open={isPoModalVisible}
                    cancelText={"Batal"}
                    okText={"Pilih"}
                    onOk={() => {
                      setIsPoModalVisible(false)

                      const productNewItem = [...productItem]
                      const purchase = purchases.find(
                        (item) => item.id === selectedPo?.id
                      )
                      setProductItems(
                        purchase?.items.filter(
                          (item) =>
                            !productItem
                              .map((row) => row.product_id)
                              .includes(item.product_id)
                        )
                      )
                      productNewItem[index]["key"] = index
                      productNewItem[index]["id"] = selectedPo?.id
                      productNewItem[index]["po_number"] = purchase?.po_number
                      // insert data for currentData table
                      productNewItem[index]["dpp"] = purchase?.subtotal
                      productNewItem[index]["ppn"] = purchase?.tax_amount

                      setProductItem(productNewItem)
                    }}
                    onCancel={() => setIsPoModalVisible(false)}
                    width={500}
                  >
                    <div>
                      <Input
                        placeholder="Cari PO number disini.."
                        size={"large"}
                        className="rounded mb-4"
                        suffix={<SearchOutlined />}
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                      />

                      {purchases
                        .filter((item) => item.items.length > 0)
                        .map((purchase, index) => {
                          // console.log(purchase, "purchase")
                          return (
                            <div
                              className={`
                      rounded-lg p-2 border-2 mb-2
                      ${
                        selectedPo?.po_number === purchase?.po_number
                          ? "border-blue-600 border-[3px] text-blue-600 font-bold"
                          : "border-gray-500"
                      }
                      `}
                              key={purchase.id}
                              value={purchase.id}
                              onClick={() => setselectedPo(purchase)}
                            >
                              {purchase.po_number}{" "}
                              {selectedPo?.po_number ===
                                purchase?.po_number && (
                                <CheckCircleFilled
                                  className="float-right"
                                  style={{ color: "blue" }}
                                />
                              )}
                            </div>
                          )
                        })}
                    </div>
                  </Modal>
                </>
              )
            },
          },
          {
            title: "Receipt number",
            dataIndex: "received_number",
            key: "received_number",
            render: (text, record, index) => {
              return (
                <Select
                  placeholder="Pilih Receipt Number"
                  value={text}
                  onChange={(e) => {
                    const productNewItem = [...productItem]
                    const purchase = productItems.find(
                      (item) => item.received_number === e
                    )
                    productNewItem[index]["uom"] = purchase.uom
                    productNewItem[index]["qty"] = purchase.qty_diterima
                    productNewItem[index]["tax"] = purchase?.tax_id
                    productNewItem[index]["extended_cost"] =
                      purchase.total_amount
                    productNewItem[index]["purchase_order_item_id"] =
                      purchase.id
                    productNewItem[index]["product_id"] = purchase?.product_id
                    productNewItem[index]["sku"] = purchase?.sku
                    productNewItem[index]["received_number"] =
                      purchase?.received_number
                    productNewItem[index]["product_name"] =
                      purchase?.product_name
                    setProductItem(productNewItem)
                  }}
                  disabled={!record.po_number}
                >
                  {productItems.map((product) => {
                    if (product.is_allocated == 0) {
                      return (
                        <Select.Option
                          key={product.received_number}
                          value={product.received_number}
                          disabled
                        >
                          <Tooltip title="Belum Dialokasi, Silakan Alokasi di Menu Inventory">
                            <span className="text-danger">
                              {product.received_number}
                            </span>
                          </Tooltip>
                        </Select.Option>
                      )
                    }
                    if (product.invoice_entry == 1) {
                      return (
                        <Select.Option
                          key={product.received_number}
                          value={product.received_number}
                          disabled
                        >
                          {product.received_number}
                        </Select.Option>
                      )
                    }
                    return (
                      <Select.Option
                        key={product.received_number}
                        value={product.received_number}
                      >
                        {product.received_number}
                      </Select.Option>
                    )
                  })}
                </Select>
              )
            },
          },
          {
            title: "Receipt Date",
            dataIndex: "received_date",
            key: "received_date",
            render: (text) => {
              return moment(text).format("DD-MM-YYYY")
            },
          },
          {
            title: "Product Name",
            dataIndex: "product_name",
            key: "product_name",
            render: (text, record, index) => {
              return text
            },
          },
        ]
      : [
          {
            title: "Product Name",
            dataIndex: "product_name",
            key: "product_name",
            render: (text, record, index) => {
              if (typeInvoice === "jasa") {
                return (
                  <Input
                    value={text}
                    onChange={(e) => {
                      const { value } = e.target
                      const productNewItem = [...productItem]

                      productNewItem[index]["product_name"] = value

                      setProductItem(productNewItem)
                    }}
                  />
                )
              }
              return <span>{text}</span>
            },
          },
        ]

  return (
    <Layout
      title="Tambah Data Invoice Entry"
      href="/purchase/invoice-entry"
      // rightContent={rightContent}
    >
      <Form
        form={form}
        name="basic"
        layout="vertical"
        onFinish={onFinish}
        // onFinishFailed={onFinishFailed}
        autoComplete="off"
      >
        <Card
          title="Informasi Invoice Entry"
          extra={
            <div className="flex justify-end items-center">
              <strong>Status :</strong>
              <Button
                type="outline"
                size={"middle"}
                className="border border-red"
                style={{
                  marginLeft: 10,
                }}
              >
                UNPAID
              </Button>
            </div>
          }
        >
          <div className="row">
            <div className="col-md-6">
              <Form.Item
                label="Type Invoice"
                name="type_invoice"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Type Invoice!",
                  },
                ]}
              >
                <Select
                  placeholder="Silakan pilih"
                  onChange={(e) => setTypeInvoice(e)}
                >
                  <Select.Option value={"product"}>Product</Select.Option>
                  <Select.Option value={"jasa"}>Jasa</Select.Option>
                </Select>
              </Form.Item>
              <Form.Item
                label="Receipt Number"
                name="received_number"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Receipt Number!",
                  },
                ]}
              >
                <Input placeholder="Silakan input Receipt Number.." disabled />
              </Form.Item>

              <Form.Item
                label="Invoice Date"
                name="invoice_date"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Invoice Date!",
                  },
                ]}
              >
                <DatePicker
                  placeholder="Silakan input Invoice Date.."
                  className="w-full"
                />
              </Form.Item>
            </div>
            <div className="col-md-6">
              <Form.Item
                label="Vendor Name"
                name="vendor_name"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Vendor Name!",
                  },
                ]}
              >
                <Select
                  showSearch
                  placeholder="Silakan pilih"
                  onChange={(e) => {
                    setProductItem(
                      productItem.map((record) => ({
                        ...record,
                        po_number: "",
                      }))
                    )

                    form.setFieldValue("vendor_id", e)
                    loadPurchases(e)
                  }}
                  filterOption={(input, option) =>
                    (option?.label ?? "")
                      .toLowerCase()
                      .includes(input.toLowerCase())
                  }
                  options={vendors}
                  // virtual={false}
                />
              </Form.Item>
              <Form.Item
                label="Vendor ID"
                name="vendor_id"
                // rules={[
                //   {
                //     required: true,
                //     message: "Silakan masukkan Vendor ID!",
                //   },
                // ]}
              >
                <Input
                  placeholder="Silakan input Vendor ID.."
                  readOnly
                  disabled
                />
              </Form.Item>

              <Form.Item
                label="Payment Term"
                name="payment_term_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Payment Term!",
                  },
                ]}
              >
                <Select placeholder="Silakan pilih">
                  {termOfPayments.map((top) => (
                    <Select.Option key={top.id} value={top.id}>
                      {top.name}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>
            </div>
            <div className="col-md-12">
              <Form.Item
                label="Created by"
                name="created_by"
                rules={[
                  {
                    required: false,
                    message: "Silakan masukkan Created by!",
                  },
                ]}
              >
                <Input
                  placeholder="Silakan input Created by.."
                  defaultValue={user?.name}
                  readOnly
                  disabled
                />
              </Form.Item>
              <Form.Item
                label="Vendor Doc.Number"
                name="vendor_doc_number"
                rules={[
                  {
                    required: false,
                    message: "Silakan masukkan Vendor DOC Number!",
                  },
                ]}
              >
                <Input placeholder="Silakan input Vendor DOC Number.." />
              </Form.Item>
              <Form.Item
                label="Batch ID"
                name="batch_id"
                rules={[
                  {
                    required: false,
                    message: "Silakan masukkan Batch ID!",
                  },
                ]}
              >
                <Input placeholder="Silakan input Batch ID.." />
              </Form.Item>

              <Form.Item
                label="Notes"
                name="notes"
                // rules={[
                //   {
                //     required: true,
                //     message: "Silakan masukkan Notes!",
                //   },
                // ]}
              >
                <TextArea
                  placeholder="Silakan input Notes.."
                  showCount
                  maxLength={1000}
                  rows={3}
                />
              </Form.Item>
            </div>
          </div>
        </Card>

        <Card title="Invoice Entry">
          <div className="card-body">
            <Table
              scroll={{ x: "max-content" }}
              tableLayout={"auto"}
              dataSource={productItem}
              columns={[
                ...invoiceColumns,
                {
                  title: "UOM",
                  dataIndex: "uom",
                  key: "uom",
                  render: (text, record, index) => {
                    if (typeInvoice === "jasa") {
                      return (
                        <Input
                          value={text}
                          onChange={(e) => {
                            const { value } = e.target
                            const productNewItem = [...productItem]

                            productNewItem[index]["uom"] = value

                            setProductItem(productNewItem)
                          }}
                        />
                      )
                    }
                    return <span>{text}</span>
                  },
                },
                {
                  title: "SKU",
                  dataIndex: "sku",
                  key: "sku",
                  render: (text, record, index) => {
                    if (typeInvoice === "jasa") {
                      return (
                        <Input
                          value={text}
                          onChange={(e) => {
                            const { value } = e.target
                            const productNewItem = [...productItem]

                            productNewItem[index]["sku"] = value

                            setProductItem(productNewItem)
                          }}
                        />
                      )
                    }
                    return <span>{text}</span>
                  },
                },
                {
                  title: "Qty",
                  dataIndex: "qty",
                  key: "qty",
                  render: (text, record, index) => {
                    if (typeInvoice === "jasa") {
                      return (
                        <Input
                          value={text}
                          onChange={(e) => {
                            const { value } = e.target
                            const productNewItem = [...productItem]
                            if (value == "" || !value) {
                              productNewItem[index]["qty"] = 1
                            } else {
                              productNewItem[index]["qty"] = value
                            }
                            productNewItem[index]["ppn"] = 0
                            console.log(productNewItem)
                            setProductItem(productNewItem)
                          }}
                        />
                      )
                    }
                    return <span>{text}</span>
                  },
                },
                {
                  title: "Amount",
                  dataIndex: "extended_cost",
                  key: "extended_cost",
                  render: (text, record, index) => {
                    if (typeInvoice === "jasa") {
                      return (
                        <Input
                          value={text}
                          onChange={(e) => {
                            const { value } = e.target
                            const productNewItem = [...productItem]
                            if (value == "" || !value) {
                              productNewItem[index]["extended_cost"] = 0
                            } else {
                              productNewItem[index]["extended_cost"] =
                                parseInt(value)
                            }
                            productNewItem[index]["ppn"] = 0
                            setProductItem(productNewItem)
                          }}
                        />
                      )
                    }
                    return <span>{formatNumber(text, "Rp ")}</span>
                  },
                },

                {
                  title: "Action",
                  dataIndex: "action",
                  key: "action",
                  fixed: "right",
                  render: (text, record, index) => {
                    return (
                      <Button
                        onClick={() => {
                          const productNewItem = [...productItem]
                          const newData = productNewItem.filter(
                            (item) => item.key != index
                          )
                          setProductItem(newData)
                        }}
                        disabled={productItem.length < 2}
                        type="danger"
                      >
                        <CloseOutlined />
                      </Button>
                    )
                  },
                },
              ]}
              pagination={false}
              rowKey={"id"}
              summary={(currentData) => {
                // console.log(currentData, "current data")
                const dpp = currentData.reduce(
                  (acc, curr) =>
                    parseInt(acc) + parseInt(Number(curr.extended_cost)),
                  0
                )
                const ppn = currentData.reduce(
                  (acc, curr) => parseInt(acc) + parseInt(curr.ppn),
                  0
                )
                const total = dpp + ppn

                const colSpan = typeInvoice == "product" ? 7 : 4

                return (
                  <Table.Summary>
                    <Table.Summary.Row>
                      <Table.Summary.Cell align="right" colSpan={colSpan}>
                        <strong>DPP :</strong>
                      </Table.Summary.Cell>
                      <Table.Summary.Cell align="right" colSpan={1}>
                        <strong>Rp. {formatNumber(dpp)}</strong>
                      </Table.Summary.Cell>
                    </Table.Summary.Row>
                    <Table.Summary.Row>
                      <Table.Summary.Cell align="right" colSpan={colSpan}>
                        <strong>TAX :</strong>
                      </Table.Summary.Cell>
                      <Table.Summary.Cell align="right" colSpan={1}>
                        {/* <strong>Rp. {formatNumber(ppn)}</strong> */}
                        <Form.Item
                          name="tax_id"
                          rules={[
                            {
                              required: false,
                              message: "Silakan pilih Tax!",
                            },
                          ]}
                        >
                          <Select
                            placeholder="Pilih TAX"
                            // onChange={handleTaxChange}
                            onChange={(value, index) => {
                              const selectedTax = taxs.find(
                                (element) => element.id === parseInt(value)
                              )

                              const productNewItem = [...productItem]
                              if (value == "" || !value) {
                                productNewItem[0]["ppn"] = 0
                              } else {
                                if (selectedTax.tax_percentage > 0) {
                                  const tax =
                                    parseInt(selectedTax.tax_percentage) / 100
                                  productNewItem[0]["ppn"] = tax * dpp
                                } else {
                                  productNewItem[0]["ppn"] = 0
                                }

                                productNewItem[0]["tax"] = selectedTax.tax_code
                              }
                              setProductItem(productNewItem)
                            }}
                            loading={loadingSite}
                          >
                            {taxs
                              .filter(
                                (item) =>
                                  item.tax_code === "VAT IN" ||
                                  item.tax_code === "VAT OUT" ||
                                  item.tax_code === "NO TAX"
                              )
                              .map((item) => (
                                <Select.Option
                                  key={item.id}
                                  value={item.tax_id}
                                >
                                  {item.tax_code}
                                </Select.Option>
                              ))}
                          </Select>
                        </Form.Item>
                      </Table.Summary.Cell>
                    </Table.Summary.Row>
                    <Table.Summary.Row>
                      <Table.Summary.Cell align="right" colSpan={colSpan}>
                        <strong>PPN :</strong>
                      </Table.Summary.Cell>
                      <Table.Summary.Cell align="right" colSpan={1}>
                        <strong>Rp. {formatNumber(ppn)}</strong>
                      </Table.Summary.Cell>
                    </Table.Summary.Row>
                    <Table.Summary.Row>
                      <Table.Summary.Cell align="right" colSpan={colSpan}>
                        <strong>Total Amount :</strong>
                      </Table.Summary.Cell>
                      <Table.Summary.Cell align="right" colSpan={1}>
                        <strong>Rp. {formatNumber(total)}</strong>
                      </Table.Summary.Cell>
                    </Table.Summary.Row>
                  </Table.Summary>
                )
              }}
            />
            <div
              onClick={handleAddMore}
              className="
              w-full mt-4 cursor-pointer
              text-blue-600 hover:text-blue-800
              bg-blue-500/20 border-2 border-blue-700/70 hover:border-blue-800 border-dashed  focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 inline-flex items-center justify-center"
            >
              <PlusOutlined style={{ marginRight: 10 }} />
              <strong>Add More</strong>
            </div>
          </div>
        </Card>

        <div className="mt-6">
          <Card
            title="Informasi Pembayaran"
            extra={
              <>
                <PurchaseBillingModal
                  // refetch={() => loadDetail()}
                  detail={detail}
                  handleFinish={(value) => {
                    const newBillings = [...billingsItem]
                    newBillings.push(value)

                    setBillingItems(newBillings)
                  }}
                  receivedNumbers={productItem || []}
                  type={typeInvoice}
                  checkBooks={checkBooks}
                />
              </>
            }
          >
            <Table
              dataSource={billingsItem}
              columns={[
                ...purchaseBillingFormListColumn,
                {
                  // title: "Action",
                  // dataIndex: "action",
                  // key: "action",
                  // render: (text, record) => {
                  //   if (record.status === "0") {
                  //     return (
                  //       <div>
                  //         <Popconfirm
                  //           title="Yakin akan reject data ini?"
                  //           onConfirm={() =>
                  //             handleUpdateBilling(record.id, "reject")
                  //           }
                  //           okText="Ya, Reject"
                  //           cancelText="Batal"
                  //         >
                  //           <button className="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mr-2">
                  //             <CloseOutlined />
                  //           </button>
                  //         </Popconfirm>
                  //         <button
                  //           onClick={() =>
                  //             handleUpdateBilling(record.id, "approve")
                  //           }
                  //           className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
                  //         >
                  //           <CheckOutlined />
                  //         </button>
                  //       </div>
                  //     )
                  //   } else if (record.status === "1") {
                  //     return <Tag color="green">Approved</Tag>
                  //   } else if (record.status === "2") {
                  //     return <Tag color="red">Rejected</Tag>
                  //   }
                  // },
                },
              ]}
              loading={loading}
              pagination={false}
              rowKey="id"
              scroll={{ x: "max-content" }}
              tableLayout={"auto"}
              summary={(pageData) => {
                if (detail?.billings?.length > 0) {
                  return (
                    <>
                      <Table.Summary.Row>
                        <Table.Summary.Cell index={0}></Table.Summary.Cell>
                        <Table.Summary.Cell index={1}></Table.Summary.Cell>
                        <Table.Summary.Cell index={2}></Table.Summary.Cell>
                        <Table.Summary.Cell index={3}></Table.Summary.Cell>
                        <Table.Summary.Cell index={4}></Table.Summary.Cell>
                        <Table.Summary.Cell index={5}></Table.Summary.Cell>
                        <Table.Summary.Cell index={6}></Table.Summary.Cell>
                        <Table.Summary.Cell index={7}></Table.Summary.Cell>
                        <Table.Summary.Cell index={7}></Table.Summary.Cell>
                        <Table.Summary.Cell index={8}>
                          Total Tax
                        </Table.Summary.Cell>
                        <Table.Summary.Cell index={9}>
                          {detail?.total_tax}
                        </Table.Summary.Cell>
                      </Table.Summary.Row>
                      <Table.Summary.Row>
                        <Table.Summary.Cell index={0}></Table.Summary.Cell>
                        <Table.Summary.Cell index={1}></Table.Summary.Cell>
                        <Table.Summary.Cell index={2}></Table.Summary.Cell>
                        <Table.Summary.Cell index={3}></Table.Summary.Cell>
                        <Table.Summary.Cell index={4}></Table.Summary.Cell>
                        <Table.Summary.Cell index={5}></Table.Summary.Cell>
                        <Table.Summary.Cell index={6}></Table.Summary.Cell>
                        <Table.Summary.Cell index={7}></Table.Summary.Cell>
                        <Table.Summary.Cell index={7}></Table.Summary.Cell>
                        <Table.Summary.Cell index={8}>Total</Table.Summary.Cell>
                        <Table.Summary.Cell index={9}>
                          {formatNumber(detail?.total_approved)}
                        </Table.Summary.Cell>
                      </Table.Summary.Row>
                    </>
                  )
                }
              }}
            />
          </Card>
        </div>

        {canCreate && (
          <div className="col-md-12 mt-8">
            <div className="float-right">
              {!purchase_invoice_entry_id && (
                <button
                  className={`text-blue bg-white hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 border font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mr-2`}
                  loading={loadingSubmit}
                  onClick={() => {
                    setStatus(-1)
                    setTimeout(() => {
                      form.submit()
                    }, 2000)
                  }}
                >
                  {status === -1 && loadingSubmit && <LoadingOutlined />}
                  <span className="ml-2">Save Draft</span>
                </button>
              )}
              <button
                className={`text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center`}
                loading={loadingSubmit}
                type="primary"
                // disabled={!checkAllObjectPropertiesValueFilled(productItem)}
                onClick={() => {
                  setStatus(0)
                  setTimeout(() => {
                    form.submit()
                  }, 2000)
                }}
              >
                {status === 0 && loadingSubmit && <LoadingOutlined />}
                <span className="ml-2">Simpan</span>
              </button>
            </div>
          </div>
        )}
      </Form>
    </Layout>
  )
}

export default PurchaseInvoiceEntryForm
