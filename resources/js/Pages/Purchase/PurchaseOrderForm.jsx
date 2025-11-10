import { LoadingOutlined, PlusOutlined, CheckOutlined } from "@ant-design/icons"
import {
  Button,
  Card,
  Divider,
  Form,
  Input,
  Select,
  Skeleton,
  Space,
  Table,
} from "antd"
import TextArea from "antd/lib/input/TextArea"
import React, { useEffect, useState } from "react"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import DebounceSelect from "../../components/atoms/DebounceSelect"
import Layout from "../../components/layout"
import { capitalizeEachWord, formatNumber, getItem } from "../../helpers"
import ProductAdditionalList from "./Components/ProductAdditionalList"
import { searchContact } from "./services"
import LoadingFallback from "../../components/LoadingFallback"
import axios from "axios"
import { typeWordingChange } from "../../components/Modal/ModalProduct"

const notes = `Pembayaran akan diproses dengan dokumen-dokumen berikut 
-	Invoice Asli 
-	Faktur Pajak 
-	Surat Jalan 
-	Copy PO (Purchase Order) 
-	Jumlah pengiriman barang harus sesuai dengan PO (Purchase Order) \n
Semua dokumen diatas mohon dikirimkan ke PT Anugrah Inovasi Makmur Indonesia, Jl. Boulevard Raya, Ruko Malibu Blok J 128-129, Cengkareng Jakarta Barat.`

const PurchaseOrderForm = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const { purchase_order_id } = useParams()

  // state
  const defaultItems = [
    {
      key: 0,
      id: null,
      product_id: null,
      sku: null,
      uom: null,
      harga_satuan: 0,
      qty: 1,
      tax_id: null,
      subtotal: 0,
      total: 0,
      tax_total: 0,
      carton_vendor_code: null,
    },
  ]
  const [loading, setLoading] = useState(false)
  const [status, setStatus] = useState(0)
  const [productNeed, setProductNeed] = useState(defaultItems)
  const [warehouses, setWarehouses] = useState([])
  const [warehouseUsers, setWarehouseUsers] = useState([])
  const [companyLists, setCompanyList] = useState([])
  const [termOfPayments, setTermOfPayments] = useState([])
  const [products, setProducts] = useState([])
  const [productAdditionals, setProductAdditionals] = useState([])
  const [typePo, setTypePo] = useState("product")
  const [taxs, setTaxs] = useState([])
  const [packages, setPackages] = useState([])
  const [vendorCode, setVendorCode] = useState(null)
  const [showSelect, setShowSelect] = useState(false)
  const [vendors, setVendors] = useState([])
  const channelDistribution = ["sales-offline", "sales-online"]
  const [channel, setChannel] = useState(channelDistribution)
  const [detail, setDetail] = useState(null)
  const [loadingSite, setLoadingSite] = useState(false)
  const [prType, setPrType] = useState(null)
  const [hasBarcode, setHasBarcode] = useState(null)
  const [loadingPurchaseRequest, setLoadingPurchaseRequest] = useState(false)
  const [purchaseRequest, setPurchaseRequest] = useState([])
  const [detailPr, setDetailPr] = useState(null)
  const [selectedTax, setSelectedTax] = useState(0)
  const [brands, setBrands] = useState([])
  const [selectedWarehouse, setSelectedWarehouse] = useState(false)
  const [loadingSubmit, setLoadingSubmit] = useState(false)

  const loadPurchaseRequest = async () => {
    setLoading(true)
    const accountId = localStorage.getItem("account_id")
    await axios
      .get(`/api/master/purchase-request?account_id=${accountId}`)
      .then((res) => {
        setPurchaseRequest(res.data.data)
        setLoading(false)
      })
  }

  const loadDetail = () => {
    setLoading(true)
    axios
      .get(`/api/purchase/purchase-order/${purchase_order_id}`)
      .then((res) => {
        const { data } = res.data
        setLoading(false)
        setDetail(data)
        setSelectedTax(parseInt(data?.tax || 0))
        setTypePo(data?.type_po)
        if (data?.type_po != "product") {
          loadProductAdditionals(data?.type_po)
        }
        console.log("cccc")
        // console.log(data, "data")
        const forms = {
          ...data,
          tax_id: isNaN(parseInt(data?.tax_id)) ? null : parseInt(data?.tax_id),
          channel: data?.channel?.split(),
          warehouse_pic: {
            label: data?.warehouse_user_name,
            value: data?.warehouse_user_id,
          },
          payment_term_id: {
            label: data?.payment_term_name,
            value: data?.payment_term_id,
          },
          vendor_code: {
            label: data?.vendor_code,
            value: data?.vendor_code,
          },
          brand_id: {
            label: data?.brand_name,
            value: data?.brand_id,
          },
        }

        form.setFieldsValue(forms)
        if (data?.type_po === "product") {
          const items = data?.items.map((item, index) => {
            return {
              ...item,
              key: index,
              id: item.id,
              product_id: item.product_id,
              sku: item.sku,
              uom: item.u_of_m,
              harga_satuan: formatNumber(item.price),
              qty: item.qty,
              tax_id: item.tax_id,
              subtotal: formatNumber(item.subtotal),
              total: formatNumber(item.total_amount),
              tax_total: item.tax_amount,
            }
          })

          setProductNeed(items)
        } else {
          const items = data?.items.map((item, index) => {
            return {
              ...item,
              key: index,
              id: item.id,
              product_id: item.product_id,
              sku: item.sku,
              uom: item.u_of_m,
              harga_satuan: item.price,
              qty: item.qty,
              tax_id: item.tax_id,
              subtotal: item.subtotal,
              total: item.total_amount,
              tax_total: item.tax_amount,
            }
          })

          setProductNeed(items)
        }
      })
      .catch((e) => setLoading(false))
  }

  const loadPrDetail = async (id = "") => {
    await axios.get(`/api/master/purchase-request/${id}`).then((res) => {
      console.log(id, "id")
      const { data } = res.data
      console.log(data, "data")
      const { payment_term, contact, sales, master_bin_id, product } = data
      setDetailPr(data)
      console.log("cheek")
      // console.log(data)
      if (data) {
        // form.setFieldValue("payment_term_id", data?.payment_term_id)
        // form.setFieldValue("contact", contact)
        form.setFieldValue("type_po", "Perlengkapan")
        form.setFieldValue("vendor_name", data?.vendor_name)

        if (data?.payment_term_id) {
          form.setFieldValue("payment_term_id", {
            label: data?.payment_term_name,
            value: data?.payment_term_id,
          })
        }

        if (data?.brand_id) {
          form.setFieldValue("brand_id", {
            label: data?.brand_name,
            value: data?.brand_id,
          })
        }

        if (data?.vendor_code) {
          form.setFieldValue("vendor_code", {
            label: data?.vendor_code,
            value: data?.vendor_code,
          })
        }
        // console.log(data?.items, "ceki")
        const items = data?.items?.map((item, index) => {
          return {
            key: index,
            id: item?.id,
            product_id: item?.item_id,
            sku: item?.item_sku,
            uom: item?.item_unit,
            harga_satuan: item?.item_price,
            qty: item?.item_qty,
            tax_id: null,
            subtotal: item?.item_subtotal,
            total: item?.item_subtotal,
            tax_total: item?.item_tax,
          }
        })

        setProductNeed(items)
      }

      // setSoKonsinyasiDetail(data)
      // setLoadingSoKonsinyasi(false)

      // const productIdList = product.map((value) => value.product_id)

      // loadProducts(productIdList, master_bin_id)
    })
  }

  const loadWarehouse = () => {
    axios.get("/api/master/warehouse").then((res) => {
      setWarehouses(res.data.data)
    })
  }

  const loadBrand = () => {
    axios.get(`/api/master/brand`).then((res) => {
      setBrands(res.data.data)
    })
  }

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

  const loadTop = () => {
    axios.get("/api/master/top").then((res) => {
      setTermOfPayments(res.data.data)
    })
  }

  const loadVendors = () => {
    axios.get("/api/master/vendors").then((res) => {
      setVendors(res.data.data)
    })
  }

  // const loadProducts = () => {
  //   axios.get("/api/master/product-lists").then((res) => {
  //     setProducts(res.data.data)
  //   })
  // }

  const loadProducts = (warehouse_id) => {
    // const warehause_id = form.getFieldValue("warehouse_id")
    console.log("cekprod")
    axios.get("/api/master/product-lists").then((res) => {
      setProducts(
        res.data.data.map((item) => {
          const stock = item.stock_warehouse.find(
            (row) => row.id == warehouse_id
          )
          console.log("item", item)
          return {
            ...item,
            final_stock: stock?.stock || item?.final_stock,
          }
        })
      )
    })
  }

  const loadProductAdditionals = (type) => {
    axios.get("/api/master/products/additional/" + type).then((res) => {
      setProductAdditionals(res.data.data)
    })
  }

  const loadCompanyAccount = () => {
    axios.get("/api/master/company-account").then((res) => {
      setCompanyList(res.data.data)
    })
  }

  // load user
  const handleSearchContact = async (e) => {
    return searchContact(e).then((results) => {
      const newResult = results.map((result) => {
        return { label: result.nama, value: result.id }
      })

      return newResult
    })
  }

  // debounced search
  const handleGetContact = () => {
    searchContact(null).then((results) => {
      const newResult = results.map((result) => {
        return { label: result.nama, value: result.id }
      })
      setWarehouseUsers(newResult)
    })
  }

  const loadPackages = () => {
    axios.get("/api/master/package").then((res) => {
      const { data } = res.data
      setPackages(data)
    })
  }

  useEffect(() => {
    if (purchase_order_id) {
      loadDetail()
    }
    loadPurchaseRequest()
    loadCompanyAccount()
    handleGetContact()
    loadWarehouse()
    loadTop()
    loadProducts()
    loadTaxs()
    loadPackages()
    loadVendors()
    loadBrand()
    form.setFieldsValue({
      currency_id: "Rp",
      notes,
      company_id: parseInt(getItem("account_id")),
    })
  }, [])

  const handleChangeProductItem = ({ dataIndex, value, key, poType }) => {
    console.log("test masuk sini")
    const datas = [...productNeed]
    const qty = datas[key]["qty"]
    const tax_id = datas[key]["tax_id"]
    const tax_total = datas[key]["tax_total"]
    const price = datas[key]["harga_satuan"]
    const subtotal = datas[key]["subtotal"]
    const total = datas[key]["total"]

    if (dataIndex === "product_id") {
      let product = products.find((product) => product.id === value)
      if (poType === "additional") {
        product = productAdditionals.find((product) => product.id === value)
      }
      datas[key]["carton_vendor_code"] = product?.carton_vendor_code
      datas[key]["sku"] = product?.sku
      datas[key]["uom"] = product?.u_of_m || null
      datas[key]["product_id"] = value
      datas[key]["harga_satuan"] = product?.price?.final_price || 0 // new scheme, put price in Harga Satuan column
      datas[key]["subtotal"] = 0 * qty || 0
      datas[key]["total"] = 0 * qty + tax_total || 0
    } else if (dataIndex === "harga_satuan") {
      datas[key]["harga_satuan"] = value
      datas[key]["qty"] = qty
      datas[key]["subtotal"] = qty * value

      const tax = taxs.find((tax) => tax.id === tax_id)
      if (tax?.tax_percentage > 0) {
        const taxPercentage = tax.tax_percentage / 100
        const totalTax = qty * value * taxPercentage
        datas[key]["tax_total"] = totalTax
        datas[key]["subtotal"] = qty * value
        datas[key]["total"] = qty * value + totalTax
      } else {
        datas[key]["total"] = qty * value
      }
    } else if (dataIndex === "qty") {
      datas[key]["qty"] = value
      datas[key]["subtotal"] = price * value
      datas[key]["total"] = price * value + tax_total
    } else if (dataIndex === "tax_id") {
      const tax = taxs.find((tax) => tax.id === value)
      datas[key][dataIndex] = value
      if (tax.tax_percentage > 0) {
        const taxPercentage = tax.tax_percentage / 100
        const totalTax = subtotal * taxPercentage
        datas[key]["tax_total"] = totalTax
        datas[key]["subtotal"] = subtotal
        datas[key]["total"] = total + totalTax
      } else {
        datas[key]["total"] = subtotal * qty
      }
    } else {
      datas[key][dataIndex] = value
    }
    console.log("product-data", datas)
    setProductNeed(datas)
  }

  const handleClickProductItem = ({ key, type, poType }) => {
    const datas = [...productNeed]
    console.log("handleClickProductItem", datas)
    if (type === "add") {
      const lastData = datas[datas.length - 1]
      datas.push({
        key: lastData.key + 1,
        id: null,
        product_id: null,
        sku: null,
        uom: null,
        harga_satuan: 0,
        qty: 1,
        tax_id: null,
        subtotal: 0,
        total: 0,
        tax_total: 0,
      })
      return setProductNeed(
        datas.map((item, index) => ({ ...item, key: index }))
      )
    }

    if (type === "add-qty") {
      const item = datas[key]
      const qty = parseInt(item.qty) + 1
      const tax = taxs.find((tax) => tax.id === item.tax_id)
      datas[key]["qty"] = qty
      const subtotal = item.harga_satuan * qty
      if (tax) {
        if (tax.tax_percentage > 0) {
          const taxPercentage = tax.tax_percentage / 100
          const totalTax = subtotal * taxPercentage
          datas[key]["tax_total"] = totalTax
          datas[key]["subtotal"] = subtotal
          datas[key]["total"] = subtotal + totalTax
        } else {
          datas[key]["total"] = subtotal
        }
      } else {
        datas[key]["subtotal"] = subtotal
        datas[key]["total"] = subtotal
      }
      return setProductNeed(
        datas.map((item, index) => ({ ...item, key: index }))
      )
    }

    if (type === "remove-qty") {
      const item = datas[key]
      if (item.qty > 1) {
        const qty = item.qty - 1
        const tax = taxs.find((tax) => tax.id === item.tax_id)
        datas[key]["qty"] = qty
        const subtotal = item.harga_satuan * qty
        if (tax) {
          if (tax.tax_percentage > 0) {
            const taxPercentage = tax.tax_percentage / 100
            const totalTax = subtotal * taxPercentage
            datas[key]["tax_total"] = totalTax
            datas[key]["subtotal"] = subtotal
            datas[key]["total"] = subtotal + totalTax
          } else {
            datas[key]["total"] = subtotal
          }
        } else {
          datas[key]["subtotal"] = subtotal
          datas[key]["total"] = subtotal
        }
        return setProductNeed(
          datas.map((item, index) => ({ ...item, key: index }))
        )
      }
      return setProductNeed(
        datas.map((item, index) => ({ ...item, key: index }))
      )
    }

    const newData = datas.filter((item) => item.key !== key)
    return setProductNeed(
      newData.map((item, index) => ({ ...item, key: index }))
    )
  }

  const onFinish = (values) => {
    setLoading(true)
    setLoadingSubmit(true)
    // const vendor_po = values?.vendor_code
    // const allVendorsMatch = productNeed.every(
    //   (item) => item.carton_vendor_code === vendor_po
    // )

    // console.log("finish vendor_po", vendor_po)
    // console.log("finish items", productNeed)

    // if (!allVendorsMatch) {
    //   setLoading(false)
    //   setLoadingSubmit(false)
    //   return toast.error(
    //     "Vendor pada PO tidak sama dengan vendor produk karton",
    //     {
    //       position: toast.POSITION.TOP_RIGHT,
    //     }
    //   )
    // }

    const checkProductId = productNeed.every(
      (item) => item.product_id && item.harga_satuan > 0
    )
    if (!checkProductId) {
      setLoading(false)
      return toast.error("Harap Lengkapi Form Inputan anda", {
        position: toast.POSITION.TOP_RIGHT,
      })
    }

    let items = productNeed.map((item) => {
      if (item.product_id) {
        return {
          id: item?.id,
          product_id: item.product_id,
          qty: item.qty,
          tax_id: item.tax_id,
          uom: item.uom,
          price: item.harga_satuan,
          account_id: getItem("account_id"),
        }
      }
    })

    const form = {
      ...values,
      warehouse_user_id: values.warehouse_pic?.value || values.warehouse_pic,
      payment_term_id: values.payment_term_id?.value || values.payment_term_id,
      brand_id: values.brand_id?.value || values.brand_id,
      vendor_code: values.vendor_code?.value || values.vendor_code,
      channel: values.channel?.join(","),
      status,
      items,
    }

    const url = purchase_order_id ? `/save/${purchase_order_id}` : "/save"
    axios
      .post("/api/purchase/purchase-order" + url, form)
      .then((res) => {
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        setLoading(false)
        setLoadingSubmit(false)
        return navigate("/purchase/purchase-order")
      })
      .catch((err) => {
        const { message } = err.response.data
        toast.error(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        setLoading(false)
        setLoadingSubmit(false)
      })
  }

  if (loading) {
    return (
      <Layout
        title="Tambah Data Purchase Order"
        href="/purchase/purchase-order"
      >
        <LoadingFallback />
      </Layout>
    )
  }
  return (
    <Layout
      title="Tambah Data Purchase Order"
      href="/purchase/purchase-order"
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
          title="Informasi Purchase Order"
          extra={
            <div className="flex justify-end items-center">
              <strong>Status :</strong>
              <Button
                type="outline"
                size={"middle"}
                style={{
                  marginLeft: 10,
                }}
              >
                Draft
              </Button>
            </div>
          }
        >
          <div className="row">
            <div className="col-md-6">
              <Form.Item
                label="Purchase Order Type"
                name="pr_type"
                rules={[
                  {
                    required: true,
                    message: "Please select Purchase Order Type!",
                  },
                ]}
              >
                <Select
                  placeholder="Silakan pilih"
                  onChange={(value) => setPrType(value)}
                >
                  <Select.Option value="PR">PR</Select.Option>
                  <Select.Option value="Non PR">Non PR</Select.Option>
                </Select>
              </Form.Item>
            </div>

            {/* Conditionally render PR number input based on prType */}
            {prType === "PR" && (
              <div className="col-md-6">
                <Form.Item
                  label="Pilih PR Number"
                  name="pr_number"
                  rules={[
                    {
                      required: true,
                      message: "Silakan pilih PR number!",
                    },
                  ]}
                >
                  {loading ? (
                    <Skeleton.Input
                      active
                      size={"default"}
                      block={false}
                      style={{ width: 450 }}
                    />
                  ) : (
                    <Select
                      loading={loading}
                      showSearch
                      filterOption={(input, option) => {
                        return (option?.children ?? "")
                          .toLowerCase()
                          .includes(input.toLowerCase())
                      }}
                      placeholder="Pilih PR Number"
                      onChange={(e) => {
                        let consignmentId = e
                        loadPrDetail(consignmentId)
                        setTypePo("perlengkapan")
                        loadProductAdditionals("perlengkapan")
                      }}
                    >
                      {purchaseRequest.map((pr) => (
                        <Select.Option value={pr.pr_number} key={pr.id}>
                          {pr.pr_number}
                        </Select.Option>
                      ))}
                    </Select>
                  )}
                </Form.Item>
              </div>
            )}
            <div className="col-md-12"></div>
            <div className="col-md-6">
              <Form.Item
                label="Company"
                name="company_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Company!",
                  },
                ]}
              >
                <Select placeholder="Silakan pilih">
                  {companyLists.map((company) => (
                    <Select.Option key={company.id} value={company.id}>
                      {company.account_name}
                    </Select.Option>
                  ))}
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
                    if (e !== "product") {
                      return loadProductAdditionals(e)
                    }
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
                label="Payment Term"
                name="payment_term_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Payment Term!",
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
                >
                  {termOfPayments.map((top) => (
                    <Select.Option key={top.id} value={top.id}>
                      {top.name}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>
            </div>
            <div className="col-md-6">
              <Form.Item
                label="Brand ID"
                name="brand_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Brand ID!",
                  },
                ]}
              >
                <Select placeholder="Silakan pilih">
                  {brands.map((brand) => (
                    <Select.Option key={brand.id} value={brand.id}>
                      {brand.name}
                    </Select.Option>
                  ))}
                </Select>
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
                label="Channel Distribution (Tag)"
                name="channel"
                // rules={[
                //   {
                //     required: true,
                //     message: "Silakan pilih Channel Distribution!",
                //   },
                // ]}
              >
                <Select
                  mode="multiple"
                  allowClear
                  // onChange={(value, options) => {
                  //   // update data only when select one item or clear action
                  //   if (options?.length === 0 || options?.length === 1) {
                  //     setChannel(value)
                  //   }
                  // }}
                  placeholder="Silakan pilih"
                  onDeselect={() => setChannel(channelDistribution)} // revert channel selection
                >
                  {channel.map((value, index) => (
                    <Select.Option key={index} value={value}>
                      {capitalizeEachWord(value.replace("-", " "))}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>

              <Form.Item
                label="Currency ID"
                name="currency"
                rules={[
                  {
                    required: false,
                    message: "Silakan masukkan Currency ID!",
                  },
                ]}
              >
                <Input
                  placeholder="Silakan input.."
                  defaultValue={"Rp"}
                  disabled
                />
              </Form.Item>
            </div>
            {prType != "PR" && (
              <div className="col-md-6">
                <Form.Item
                  label="Memiliki Barcode ?"
                  name="has_barcode"
                  rules={[
                    {
                      required: true,
                      message: "Please select option!",
                    },
                  ]}
                >
                  <Select
                    placeholder="Silakan pilih"
                    onChange={(value) => setHasBarcode(value)}
                  >
                    <Select.Option value="1">Ya</Select.Option>
                    <Select.Option value="0">Tidak</Select.Option>
                  </Select>
                </Form.Item>
              </div>
            )}
            <div className="col-md-12">
              <Form.Item
                requiredMark={"optional"}
                label="Notes"
                name="notes"
                rules={[
                  {
                    required: false,
                    message: "Silakan masukkan Notes!",
                  },
                ]}
              >
                <TextArea
                  placeholder="Silakan input catatan.."
                  showCount
                  maxLength={1000}
                  rows={12}
                />
              </Form.Item>
            </div>
          </div>
        </Card>

        <Card title="Informasi Pembelian Item">
          <div className="card-body grid md:grid-cols-2 gap-4">
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
                  loadProducts(e)
                  setSelectedWarehouse(true)
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

            <div className="md:col-span-2">
              <Form.Item
                requiredMark={"Automatic"}
                label="Detail Alamat Warehouse (Automatic)"
                name="warehouse_address"
                rules={[
                  {
                    required: false,
                    message: "Silakan masukkan alamat Warehouse!",
                  },
                ]}
              >
                <TextArea
                  placeholder="Silakan input catatan.."
                  showCount
                  maxLength={100}
                />
              </Form.Item>
            </div>
          </div>
        </Card>

        <Card title={`Detail ${typeWordingChange(typePo)}`}>
          <ProductAdditionalList
            data={productNeed}
            products={typePo === "product" ? products : productAdditionals}
            packages={packages}
            type={typePo}
            taxs={taxs}
            wh={selectedWarehouse}
            handleChange={(value) =>
              handleChangeProductItem({
                ...value,
                poType: typePo === "product" ? "product" : "additional",
              })
            }
            handleClick={(value) =>
              handleClickProductItem({
                ...value,
                poType: typePo === "product" ? "product" : "additional",
              })
            }
            summary={(currentData) => {
              const subtotal = currentData.reduce(
                (acc, curr) => parseInt(acc) + parseInt(curr.subtotal),
                0
              )
              const ppn = currentData.reduce(
                (acc, curr) => parseInt(acc) + parseInt(curr.tax_total),
                0
              )
              const tax_percentage = selectedTax > 0 ? selectedTax / 100 : 0
              const tax_amount = subtotal * tax_percentage
              // console.log(selectedTax, "selectedTax")
              const total =
                currentData.reduce(
                  (acc, curr) => parseInt(acc) + parseInt(curr.total),
                  0
                ) + parseInt(tax_amount)

              return (
                <Table.Summary>
                  <Table.Summary.Row>
                    <Table.Summary.Cell align="right" colSpan={7}>
                      <strong>Sub total :</strong>
                    </Table.Summary.Cell>
                    <Table.Summary.Cell align="right">
                      <strong>Rp. {formatNumber(subtotal)}</strong>
                    </Table.Summary.Cell>
                  </Table.Summary.Row>

                  <Table.Summary.Row>
                    <Table.Summary.Cell align="right" colSpan={7}>
                      <strong>Pilih TAX :</strong>
                    </Table.Summary.Cell>
                    <Table.Summary.Cell align="right">
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
                          loading={loadingSite}
                          onChange={(value) => {
                            const tax = taxs.find((row) => row.id === value)
                            setSelectedTax(tax?.tax_percentage || 0)
                          }}
                        >
                          {taxs.map((item) => (
                            <Select.Option key={item.id} value={item.id}>
                              {item.tax_code}
                            </Select.Option>
                          ))}
                        </Select>
                      </Form.Item>
                    </Table.Summary.Cell>
                  </Table.Summary.Row>
                  <Table.Summary.Row>
                    <Table.Summary.Cell align="right" colSpan={7}>
                      <strong>Tax :</strong>
                    </Table.Summary.Cell>
                    <Table.Summary.Cell align="right">
                      <strong>Rp. {formatNumber(tax_amount)}</strong>
                    </Table.Summary.Cell>
                  </Table.Summary.Row>
                  <Table.Summary.Row>
                    <Table.Summary.Cell align="right" colSpan={7}>
                      <strong>Total Harga :</strong>
                    </Table.Summary.Cell>
                    <Table.Summary.Cell align="right">
                      <strong>Rp. {formatNumber(total)}</strong>
                    </Table.Summary.Cell>
                  </Table.Summary.Row>
                </Table.Summary>
              )
            }}
          />
        </Card>

        <div className="flex justify-end my-6">
          {!purchase_order_id && (
            <button
              onClick={() => {
                setStatus(0)
                setTimeout(() => {
                  form.submit()
                }, 1000)
              }}
              type="button"
              className={`text-blue-700 bg-white border hover:bg-black focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center`}
              disabled={loading}
            >
              {loading ? (
                <LoadingOutlined />
              ) : (
                <span className="">Simpan Sebagai Draft</span>
              )}
            </button>
          )}

          <button
            onClick={() => {
              setStatus(5)
              setLoadingSubmit(true)
              setTimeout(() => {
                form.submit()
                setLoadingSubmit(false)
              }, 1000)
            }}
            type="button"
            className={`ml-4 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center`}
            disabled={loadingSubmit}
          >
            {loadingSubmit ? <LoadingOutlined /> : <CheckOutlined />}
            <span className="ml-2">Simpan</span>
          </button>
        </div>
      </Form>
    </Layout>
  )
}

export default PurchaseOrderForm
