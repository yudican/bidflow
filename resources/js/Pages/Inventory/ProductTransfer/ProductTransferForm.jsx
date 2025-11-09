import {
  CheckOutlined,
  CloseOutlined,
  LoadingOutlined,
  PrinterTwoTone,
} from "@ant-design/icons"
import {
  Button,
  Card,
  Checkbox,
  Dropdown,
  Form,
  Input,
  Menu,
  Select,
  Skeleton,
  Switch,
  Table,
  message,
} from "antd"
import moment from "moment"
import TextArea from "antd/lib/input/TextArea"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import DebounceSelect from "../../../components/atoms/DebounceSelect"
import Layout from "../../../components/layout"
import { formatDate, formatNumber, getItem, inArray } from "../../../helpers"
import FormAddressModal from "../../Contact/Components/FormAddressModal"
import ProductKonsinyasi from "../Components/ProductKonsinyasi"
import ProductList from "../Components/ProductList"
import {
  productListAllocationHistoryColumns,
  productListColumns,
} from "../config"
import { searchContact, searchSales } from "./service"
import ProductListInput from "../../../components/ProductListInput"

const ProductTransferForm = ({ inventory_type = "transfer" }) => {
  const [form] = Form.useForm()
  const role = getItem("role")
  const { inventory_id } = useParams()
  const defaultItems = [
    {
      id: null,
      key: 0,
      product_id: null,
      price: null,
      qty: 1,
      tax_id: null,
      tax_amount: 0,
      tax_percentage: 0,
      discount_percentage: 0,
      discount: 0,
      discount_amount: 0,
      subtotal: null,
      price_nego: null,
      total: 0,
      margin_price: 0,
      stock: 0,
    },
  ]
  // hooks
  const navigate = useNavigate()
  // state
  const intialProduct = {
    id: 0,
    key: 0,
    product_id: null,
    price: 0,
    qty: 0,
    qty_alocation: 0,
    sub_total: 0,
    from_warehouse_id: null,
    to_warehouse_id: null,
    sku: null,
    u_of_m: null,
  }
  const userData = getItem("user_data", true)
  const [productData, setProductData] = useState([intialProduct])
  const [productItems, setProductItems] = useState(defaultItems)
  const [historyAllocation, setHistoryAllocation] = useState([])
  const [selectedPo, setSelectedPo] = useState(null)
  const [products, setProducts] = useState([])
  const [warehouses, setWarehouses] = useState([])
  const [masterBin, setMasterBin] = useState([])
  const [detailPo, setDetailPo] = useState(null)
  const [detail, setDetail] = useState(null)
  const [loading, setLoading] = useState(false)
  const [loadingProduct, setLoadingProduct] = useState(false)
  const [loadingBinByContact, setLoadingBinByContact] = useState(false)
  const [loadingBinList, setLoadingBinList] = useState(false)
  const [loadingComplete, setLoadingComplete] = useState(false)
  const [loadingReject, setLoadingReject] = useState(false)
  const [sendEthix, setSendEthix] = useState(false)
  const [contactList, setContactList] = useState([])
  const [showKonsinyasi, setShowKonsinyasi] = useState(false)
  const [salesList, setSalesList] = useState([])
  const [seletedContact, setSeletedcontact] = useState(null)
  const [termOfPayments, setTermOfPayments] = useState([])
  const [taxs, setTaxs] = useState([])
  const [productLoading, setProductLoading] = useState(false)
  const [showContact, setShowContact] = useState(false)
  const [userAddress, setUserAddress] = useState(null)
  const [selectedAddress, setSelectedAddress] = useState(null)
  const [printSi, setPrintSi] = useState([])
  const [printSo, setPrintSo] = useState([])

  // api
  const loadProducts = (warehouse_id = 2) => {
    setLoadingProduct(true)
    axios
      .get("/api/master/product-lists")
      .then((res) => {
        const products = res.data.data
        const newProducts = products
          .filter((item) => {
            return item.stock_warehouse.some((row) => row.id == warehouse_id)
          })
          .map((item) => {
            const final_stock = item.stock_warehouse.find(
              (row) => row.id == warehouse_id
            )?.stock
            return {
              ...item,
              final_stock,
              stock_off_market: final_stock,
              product_id: item.id,
              disabled: false,
            }
          })

        setProducts(newProducts)
        setLoadingProduct(false)
      })
      .catch((err) => setLoadingProduct(false))
  }

  const loadWarehouse = () => {
    setLoading(true)
    axios.get("/api/master/warehouse").then((res) => {
      setWarehouses(res.data.data)
      setLoading(false)

      if (inventory_type === "konsinyasi") {
        form.setFieldValue("to_warehouse_id", 19)
      }
    })
  }

  const loadUserAddress = (id) => {
    axios.get("/api/general/user-with-address/" + id).then((res) => {
      setUserAddress(res.data.data)
      const { address } = res?.data?.data || {}
      if (address) {
        const selectedAddr = address.find((item) => item.is_default == 1)
        if (selectedAddr) {
          setSelectedAddress(selectedAddr.id)
        }
      }
    })
  }

  const loadBinByContact = (id) => {
    setMasterBin([])
    setLoadingBinByContact(true)

    axios
      .get("/api/master/bin-by-contact/" + id)
      .then((res) => {
        const { data } = res?.data || {}

        if (data && data.length > 0) {
          // Format bin untuk dropdown
          const formattedBins = data.map((item) => ({
            id: item?.master_bin_id,
            name: item?.master_bin?.name,
          }))

          // Ambil master_bin_id sebelumnya dari form
          const previousMasterBinId = form.getFieldValue("master_bin_id")

          // Tambahkan bin default ke daftar jika tidak ditemukan di hasil API
          const isDefaultBinMissing = !formattedBins.find(
            (bin) => bin.id === previousMasterBinId?.value
          )

          if (previousMasterBinId && isDefaultBinMissing) {
            formattedBins.unshift({
              id: previousMasterBinId.value,
              name: previousMasterBinId.label,
            })
          }

          // Set daftar bin dan nilai default
          setMasterBin(formattedBins)

          if (previousMasterBinId) {
            form.setFieldValue("master_bin_id", previousMasterBinId)
          }
        } else {
          // Jika tidak ada bin, kosongkan form dan data bin
          form.setFieldValue("master_bin_id", null)
          setMasterBin([])
        }
      })
      .finally(() => {
        setLoadingBinByContact(false)
      })
  }

  const loadTop = () => {
    axios.get("/api/master/top").then((res) => {
      setTermOfPayments(res.data.data)
    })
  }

  const loadTrfNumber = () => {
    setLoading(true)
    axios.get("/api/inventory/trf-number").then((res) => {
      form.setFieldValue("so_ethix", res?.data?.value)
      setLoading(false)
    })
  }

  const loadSoNumber = () => {
    setLoading(true)
    axios.get("/api/inventory/so-number").then((res) => {
      form.setFieldValue("order_number", res?.data?.value)
      setLoading(false)
    })
  }

  const loadSiNumber = () => {
    setLoading(true)
    axios.get("/api/inventory/si-number").then((res) => {
      form.setFieldValue("invoice_number", res?.data?.value)
      setLoading(false)
    })
  }

  // const loadProductStock = (value) => {
  //   setLoading(true)
  //   axios.post("/api/master/product/stocks", value).then((res) => {
  //     const products = res.data.data

  //     const newProduct = products.map((item, index) => {
  //       return {
  //         ...item,
  //         key: index,
  //         qty_alocation: item?.qty,
  //       }
  //     })

  //     setProductData(newProduct)
  //     setLoading(false)
  //   })
  // }

  const loadInventoryDetail = () => {
    setLoading(true)
    axios.get(`/api/inventory/product/detail/${inventory_id}`).then((res) => {
      const { data } = res.data
      console.log("cek data", data)
      const forms = {
        ...data,
        from_warehouse_id: data?.warehouse_id,
        to_warehouse_id: data?.destination_warehouse_id,
        received_date: formatDate(data.received_date),
        order_number: data?.order_transfer?.order_number,
        invoice_number: data?.order_transfer?.invoice_number,
        // master_bin_id: data?.order_transfer?.master_bin_id,
        payment_term: data?.order_transfer?.payment_term,
        preference_number: data?.order_transfer?.preference_number,
        created_by_name: data?.created_by_name,
        notes: data?.note,
        inventory_type,
        account_id: data?.company_id,
        created_on: moment(new Date()).format("DD-MM-YYYY"),
        master_bin_id: data?.order_transfer?.master_bin_id
          ? {
              label: data?.order_transfer?.master_bin?.name,
              value: data?.order_transfer?.master_bin_id,
            }
          : null,
      }

      if (data?.order_transfer?.contact) {
        forms.contact = {
          label: data?.order_transfer?.contact_name + " - " + data?.role_name,
          value: data?.order_transfer?.contact,
        }
      }

      if (data?.order_transfer?.sales) {
        forms.sales = {
          label: data?.order_transfer?.sales_name,
          value: data?.order_transfer?.sales,
        }
      }

      form.setFieldsValue(forms)
      form.setFieldValue(
        "created_at",
        moment(new Date(data?.created_at)).format("DD-MM-YYYY")
      )
      const newhistory = data.history_allocations.map((item, index) => {
        return {
          ...item,
          key: index,
          qty: item.quantity,
        }
      })

      setPrintSo(`/print/sok/${inventory_id}`)
      setPrintSi(`/print/sjk/${inventory_id}`)
      if (data?.is_konsinyasi == "1") {
        setShowKonsinyasi(true)
      }
      loadProducts(data?.warehouse_id)

      loadUserAddress(data?.order_transfer?.contact)
      loadBinByContact(data?.order_transfer?.contact)
      setSendEthix(data?.post_ethix > 0)
      setDetailPo(data?.selected_po)
      setDetail(data)

      setSelectedPo(data?.selected_po)
      setHistoryAllocation(newhistory)
      setLoading(false)

      setShowContact(true)
      const newData = data?.detail_items?.map((item, index) => {
        const discount_amount = item?.discount_amount

        const percentage =
          item.tax_percentage > 1
            ? item.tax_percentage / 100
            : item.tax_percentage
        const tax = item.tax_percentage > 0 ? percentage : 0
        const subtotal = item?.price_nego
        const tax_amount = (subtotal - discount_amount) * tax

        const preTotal = subtotal - discount_amount + tax_amount
        return {
          ...item,
          tax_id: item?.tax_id || 1,
          key: index,
          price: item?.product_price,
          discount_amount: item?.discount * item?.qty,
          price_satuan: item?.price_nego / item?.qty,
          subtotal: subtotal,
          total: preTotal,
        }
      })
      setProductData(newData)
      setProductItems(newData)
    })
  }

  const getCreatedInfo = () => {
    setLoading(true)
    axios.get("/api/inventory/info/created").then((res) => {
      form.setFieldsValue(res.data)
      setLoading(false)
    })
  }

  const loadTaxs = () => {
    axios.get("/api/master/taxs").then((res) => {
      setTaxs(res.data.data)
    })
  }

  // const productItemKons = (value) => {
  //   const item = value.type === "add" ? defaultItems[0] : {}
  //   setProductLoading(true)
  //   axios
  //     .post(`/api/order-manual/product-items/${value.type}`, {
  //       ...value,
  //       ...item,
  //       item_id: value.key,
  //       uid_lead,
  //     })
  //     .then((res) => {
  //       const { message } = res.data
  //       getProductNeed()
  //       setProductLoading(false)
  //       toast.success(message, {
  //         position: toast.POSITION.TOP_RIGHT,
  //       })
  //     })
  // }

  // const getProductNeed = (warehouse_id = null, uid_lead) => {
  //   // setProductLoading(true)
  //   axios.get(`/api/order-manual/product-need/${uid_lead}`).then((res) => {
  //     const { data } = res.data
  //     if (data && data.length > 0) {
  //       const newData = data?.map((item, index) => {
  //         const stock_warehouse =
  //           (item?.product?.stock_warehouse &&
  //             item?.product?.stock_warehouse.length > 0 &&
  //             item?.product?.stock_warehouse) ||
  //           []
  //         const stock_off_market =
  //           stock_warehouse.find((item) => item.id == selectedwarehouse)
  //             ?.stock || item?.product?.stock_off_market
  //         const som = stock_off_market
  //         const bundling =
  //           item?.product?.qty_bundling > 0 ? item?.product?.qty_bundling : 1

  //         return {
  //           key: index,
  //           id: item.id,
  //           product: item?.product?.name || "-",
  //           product_id: item?.product_id,
  //           price: formatNumber(item?.prices?.final_price),
  //           qty: item?.qty,
  //           subtotal: formatNumber(item?.prices?.final_price * item?.qty),
  //           margin_price: formatNumber(item?.margin_price),
  //           discount: item?.discount,
  //           discount_percentage: item?.discount_percentage,
  //           tax_id: item?.tax_id,
  //           stock: som,
  //           price_nego: item?.price_nego,
  //           total: formatNumber(item?.total),
  //           // disabled_discount: item?.disabled_discount,
  //           // disabled_price_nego: item?.disabled_price_nego,
  //         }
  //       })
  //       setProductItems(newData)
  //       // setProductLoading(false)
  //       loadProductDetail(false)
  //     }
  //   })
  // }

  // cycle
  useEffect(() => {
    setShowKonsinyasi(inventory_type === "konsinyasi")
    form.setFieldValue("inventory_type", inventory_type)
    form.setFieldValue("payment_term", 3)
    // form.setFieldValue("account_id", Number(getItem("account_id")))
    form.setFieldValue("created_at", moment(new Date()).format("DD-MM-YYYY"))
    loadProducts()
    loadWarehouse()
    handleGetContact()
    handleGetSales()
    handleSearchSales()
    // loadMasterBin();
    loadTop()
    loadTaxs()
    // getProductNeed()
    if (inventory_id) {
      loadInventoryDetail()
    } else {
      loadTrfNumber()
      getCreatedInfo()
      loadSoNumber()
      loadSiNumber()
    }
  }, [])

  /* ===========================INTERNAL FIS TRANSFER ============================= */
  const handleChangeProductItem = ({ dataIndex, value, key }) => {
    const datas = [...productData]
    const from_warehouse_id = datas[key].from_warehouse_id
    const to_warehouse_id = datas[key].to_warehouse_id

    if (value === null) {
      datas[key][dataIndex] = null
      return setProductData(datas)
    }

    if (dataIndex === "from_warehouse_id") {
      if (to_warehouse_id === value) {
        return message.error(
          "Asal Warehouse tidak boleh sama dengan To warehouse"
        )
      }
      datas[key][dataIndex] = value
    }

    if (dataIndex === "to_warehouse_id") {
      if (from_warehouse_id === value) {
        return message.error(
          "Asal Warehouse tidak boleh sama dengan To warehouse"
        )
      }
      const exist = datas.find((item) => item.to_warehouse_id === value)
      if (exist) {
        const newData = datas.filter((item) => item.key !== key)
        return setProductData(newData)
      } else {
        datas[key][dataIndex] = value
      }
    }

    if (dataIndex === "qty_alocation") {
      datas[key][dataIndex] = value
    }
    if (dataIndex === "product_id") {
      const product = products.find((item) => item.id === value)
      if (product) {
        datas[key]["u_of_m"] = product?.u_of_m
        datas[key]["sku"] = product?.sku
        datas[key]["qty"] = product?.final_stock
        datas[key]["qty_alocation"] = product?.final_stock
        datas[key]["from_warehouse_id"] = form.getFieldValue("warehouse_id")
        datas[key]["to_warehouse_id"] = form.getFieldValue("to_warehouse_id")
      }
      datas[key][dataIndex] = value
    }

    setProductData(datas)
  }

  const handleClickProductItem = ({ key, type }) => {
    const datas = [...productData]
    if (type === "add") {
      const lastData = datas[datas.length - 1]
      datas.push({
        key: lastData.key + 1,
        id: 0,
        product_id: null,
        price: 0,
        qty: 0,
        qty_alocation: 0,
        sub_total: 0,
        from_warehouse_id: lastData.from_warehouse_id,
        to_warehouse_id: lastData.to_warehouse_id,
        sku: lastData.sku,
        u_of_m: lastData.u_of_m,
      })
      return setProductData(
        datas.map((item, index) => ({ ...item, key: index }))
      )
    }

    if (type === "add-qty") {
      const item = datas[key]
      if (item.qty_alocation + 1 <= item.qty) {
        const qty_alocation = item.qty_alocation + 1
        datas[key]["qty_alocation"] = qty_alocation
        return setProductData(
          datas.map((item, index) => ({ ...item, key: index }))
        )
      }

      return null
    }

    if (type === "remove-qty") {
      const item = datas[key]
      if (item.qty_alocation > 1) {
        const qty_alocation = item.qty_alocation - 1
        datas[key]["qty_alocation"] = qty_alocation
        return setProductData(
          datas.map((item, index) => ({ ...item, key: index }))
        )
      }
      return setProductData(
        datas.map((item, index) => ({ ...item, key: index }))
      )
    }

    const newData = datas.filter((item) => item.key !== key)
    return setProductData(
      newData.map((item, index) => ({ ...item, key: index }))
    )
  }

  /* =========================END INTERNAL FIS TRANSFER ============================ */

  const onFinish = (values) => {
    // const productItem = productData.every((item) => item.to_warehouse_id)
    // if (!productItem) {
    //   return message.error("Please select product")
    // }
    console.log(values, "values")
    const data = {
      ...values,
      po_number: selectedPo?.po_number,
      created_by: selectedPo?.created_by || userData?.id,
      items: productData,
      itemkons: productItems,
      note: values.notes,
      post_ethix: sendEthix ? 1 : 0,
      account_id: values.account_id,
      payment_term: 3,
      inventory_id,
    }

    let url = "/api/inventory/product/transfer/save"
    axios
      .post(url, data)
      .then((res) => {
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        setSendEthix(false)
        return navigate(-1)
      })
      .catch((err) => {
        const { message } = err.response.data
        toast.error(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
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

  const handleSearchSales = async (e) => {
    return searchSales(e).then((results) => {
      const newResult = results.map((result) => {
        return { label: result.nama, value: result.id }
      })

      return newResult
    })
  }

  const handleGetContact = () => {
    searchContact(null).then((results) => {
      const newResult = results.map((result) => {
        return { label: result.nama, value: result.id }
      })
      setContactList(newResult)
    })
  }

  const handleGetSales = () => {
    searchSales(null).then((results) => {
      const newResult = results.map((result) => {
        return { label: result.nama, value: result.id }
      })

      setSalesList(newResult)
    })
  }

  const disabled = detail?.status === "done"

  const hrefNavigate = () => {
    switch (inventory_type) {
      case "konsinyasi":
        return "/inventory-new/item-transfer-konsinyasi"

      case "transfer":
        return "/inventory-new/inventory-product-transfer"

      default:
        return "/inventory-new"
    }
  }

  console.log("product", products)
  console.log("productItem", productItems)

  const title = inventory_type == "konsinyasi" ? "Konsinyasi" : ""
  return (
    <Layout
      // onClick={() => navigate(-1)}
      href={hrefNavigate()}
      title={`Form Item Transfer ${title}`}
    >
      <div className="card">
        <div className="card-header flex justify-between items-center">
          <strong>Form Received Product</strong>
          <div></div>
        </div>
        <div className="card-body">
          <Form
            form={form}
            name="basic"
            layout="vertical"
            onFinish={onFinish}
            autoComplete="off"
            // initialValues={{
            //   created_on: moment(new Date()).format("DD-MM-YYYY"),
            // }}
          >
            <div className="row">
              {/* <div className="col-md-12">
                <Form.Item
                  label="Product"
                  name="product_id"
                  rules={[
                    {
                      required: true,
                      message: "Silakan pilih Product!",
                    },
                  ]}
                >
                  {loading ? (
                    <Skeleton.Input
                      active
                      size={"default"}
                      block={false}
                      style={{ width: 500 }}
                    />
                  ) : (
                    // flow before (select)

                    //   <Select
                    //   showSearch
                    //   filterOption={(input, option) => {
                    //     return (option?.children ?? "")
                    //       .toLowerCase()
                    //       .includes(input.toLowerCase())
                    //   }}
                    //   placeholder="Pilih Product"
                    //   onChange={(e) => {
                    //     const newProduct = productData.map((item) => {
                    //       const product = products.find((row) => row.id === e)
                    //       return {
                    //         ...item,
                    //         product_id: e,
                    //         price: product.price,
                    //         sku: product.sku || "-",
                    //         u_of_m: product.u_of_m,
                    //         qty: product.stock,
                    //       }
                    //     })

                    //     setProductData(newProduct)
                    //   }}
                    //   disabled={disabled}
                    // >
                    //   {products.map((product) => (
                    //     <Select.Option value={product.id} key={product.id}>
                    //       {product.name}
                    //     </Select.Option>
                    //   ))}
                    // </Select>

                    // flow after (modal component)
                    // <ModalProduct
                    //   style={{ width: "100%" }}
                    //   type={"product"}
                    //   products={products}
                    //   handleChange={(e) => {
                    //     form.setFieldValue("product_id", e)
                    //   }}
                    //   value={form.getFieldValue("product_id")}
                    // />
                  )}
                </Form.Item>
              </div> */}
              <div className="col-md-6">
                <Form.Item
                  label="Company Account"
                  name="account_id"
                  rules={[
                    {
                      required: true,
                      message: "Silakan pilih Company Account!",
                    },
                  ]}
                >
                  {loading ? (
                    <Skeleton.Input
                      active
                      size={"default"}
                      block={true}
                      style={{ width: "100%" }}
                    />
                  ) : (
                    <Select
                      options={[
                        {
                          value: 1,
                          label: "PT Anugrah Inovasi Makmur Indonesia",
                        },
                        {
                          value: 2,
                          label: "Flimty",
                        },
                      ]}
                      placeholder="Company Account"
                    />
                  )}
                </Form.Item>
                {showKonsinyasi && (
                  <Form.Item
                    label="Kategori Data"
                    name="transfer_category"
                    rules={[
                      {
                        required: true,
                        message: "Silakan pilih Kategori Data!",
                      },
                    ]}
                  >
                    {loading ? (
                      <Skeleton.Input
                        active
                        size={"default"}
                        block={true}
                        style={{ width: "100%" }}
                      />
                    ) : (
                      <Select
                        options={[
                          {
                            value: "old",
                            label: "Data Lama",
                          },
                          {
                            value: "new",
                            label: "Data Baru",
                          },
                        ]}
                        placeholder="Kategori Data"
                      />
                    )}
                  </Form.Item>
                )}

                <Form.Item label="TRF ID" name="so_ethix">
                  {loading ? (
                    <Skeleton.Input
                      active
                      size={"default"}
                      block={true}
                      style={{ width: "100%" }}
                    />
                  ) : (
                    <Input
                      placeholder="TRF ID"
                      disabled
                      // bordered={false}
                    />
                  )}
                </Form.Item>

                {showKonsinyasi && (
                  <Form.Item
                    label="Contact"
                    name="contact"
                    rules={[
                      {
                        required: true,
                        message: "Silakan pilih Contact!",
                      },
                    ]}
                  >
                    <DebounceSelect
                      showSearch
                      placeholder="Cari Contact"
                      fetchOptions={handleSearchContact}
                      filterOption={false}
                      defaultOptions={contactList}
                      className="w-full"
                      disabled={disabled}
                      onChange={(e) => {
                        loadUserAddress(e.value)
                        loadBinByContact(e.value)
                        setSeletedcontact(e)
                        setShowContact(true)
                      }}
                      dropdownRender={(menu) => (
                        <>
                          {menu}
                          <div className="py-1 flex w-full items-center justify-center">
                            <Button
                              className=""
                              type="text"
                              onClick={() => {
                                navigate("/contact/create")
                              }}
                            >
                              <strong className="text-blue-500">
                                + Add Contact
                              </strong>
                            </Button>
                          </div>
                        </>
                      )}
                    />
                  </Form.Item>
                )}

                {showKonsinyasi && (
                  <Form.Item
                    label="Sales"
                    name="sales"
                    rules={[
                      {
                        required: true,
                        message: "Silakan pilih Sales!",
                      },
                    ]}
                  >
                    <DebounceSelect
                      disabled={role === "sales" || inventory_id}
                      defaultOptions={
                        role === "sales"
                          ? [{ label: userData.name, value: userData.id }]
                          : salesList
                      }
                      showSearch
                      placeholder="Cari Sales"
                      fetchOptions={handleSearchSales}
                      filterOption={false}
                      className="w-full"
                    />
                  </Form.Item>
                )}

                <Form.Item
                  label={showKonsinyasi ? "Asal Warehouse" : "Warehouse"}
                  name="warehouse_id"
                  rules={[
                    {
                      required: true,
                      message: "Silakan Pilih Asal Warehouse!",
                    },
                  ]}
                >
                  {loading ? (
                    <Skeleton.Input
                      active
                      size={"default"}
                      block={true}
                      style={{ width: "100%" }}
                    />
                  ) : (
                    <Select
                      showSearch
                      filterOption={(input, option) => {
                        return (option?.children ?? "")
                          .toLowerCase()
                          .includes(input.toLowerCase())
                      }}
                      placeholder="Pilih Asal Warehouse"
                      disabled={disabled}
                      onChange={(warehouse_id) => {
                        loadProducts(warehouse_id)
                        // loadProductKons(e)
                        setDetailPo({ warehouse_id })
                        // const product_id = form.getFieldValue("product_id")
                        // loadProductStock({
                        //   product_id,
                        //   warehouse_id: e,
                        // })
                      }}
                    >
                      {warehouses.map((warehouse) => (
                        <Select.Option value={warehouse.id} key={warehouse.id}>
                          {warehouse.name}
                        </Select.Option>
                      ))}
                    </Select>
                  )}
                </Form.Item>

                {showKonsinyasi && (
                  <Form.Item
                    label="Payment Term"
                    name="payment_term"
                    rules={[
                      {
                        required: false,
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
                      placeholder="Pilih Payment Term"
                      disabled={showKonsinyasi}
                      onChange={(e) => {
                        // setShowBilling(e === 4 ? true : false)
                        // setShowBin(e === 3 ? true : false)
                      }}
                    >
                      {termOfPayments.map((top) => (
                        <Select.Option value={top.id} key={top.id}>
                          {top.name}
                        </Select.Option>
                      ))}
                    </Select>
                  </Form.Item>
                )}
                <Form.Item label="Created by" name="created_by_name">
                  {loading ? (
                    <Skeleton.Input
                      active
                      size={"default"}
                      block={true}
                      style={{ width: "100%" }}
                    />
                  ) : (
                    <Input placeholder=" Created by" disabled />
                  )}
                </Form.Item>
              </div>
              <div className="col-md-6">
                <Form.Item
                  label="Tipe Item Transfer"
                  name="inventory_type"
                  rules={[
                    {
                      required: true,
                      message: "Silakan pilih!",
                    },
                  ]}
                >
                  <Select
                    placeholder="Pilih Option"
                    onChange={(e) => {
                      setShowKonsinyasi(e === "konsinyasi" ? true : false)

                      if (e === "konsinyasi") {
                        form.setFieldValue("payment_terms", 3)
                      }
                    }}
                    disabled
                  >
                    <Select.Option value="transfer" key="0">
                      Internal
                    </Select.Option>
                    <Select.Option value="konsinyasi" key="1">
                      Konsinyasi
                    </Select.Option>
                  </Select>
                </Form.Item>
                {showKonsinyasi && (
                  <Form.Item label="DO Number" name="invoice_number">
                    {loading ? (
                      <Skeleton.Input
                        active
                        size={"default"}
                        block={true}
                        style={{ width: "100%" }}
                      />
                    ) : (
                      <Input placeholder="DO Number" disabled />
                    )}
                  </Form.Item>
                )}
                {showKonsinyasi && (
                  <Form.Item label="SO Number" name="order_number">
                    {loading ? (
                      <Skeleton.Input
                        active
                        size={"default"}
                        block={true}
                        style={{ width: "100%" }}
                      />
                    ) : (
                      <Input placeholder="SO Number" disabled />
                    )}
                  </Form.Item>
                )}

                {showKonsinyasi && (
                  <Form.Item
                    label="Destinasi BIN"
                    name="master_bin_id"
                    rules={[
                      {
                        required: true,
                        message: "Silakan pilih Destinasi BIN!",
                      },
                    ]}
                  >
                    {loadingBinByContact ? (
                      <Skeleton.Input
                        active
                        size={"default"}
                        block={true}
                        style={{ width: "100%" }}
                      />
                    ) : (
                      <Select
                        showSearch
                        filterOption={(input, option) => {
                          return (option?.children ?? "")
                            .toLowerCase()
                            .includes(input.toLowerCase())
                        }}
                        placeholder="Pilih Destinasi BIN"
                        disabled={disabled}
                      >
                        {masterBin.map((bin) => (
                          <Select.Option value={bin.id} key={bin.id}>
                            {bin.name}
                          </Select.Option>
                        ))}
                      </Select>
                    )}
                  </Form.Item>
                )}

                {showKonsinyasi && (
                  <Form.Item
                    label="Destinasi Warehouse"
                    name="to_warehouse_id"
                    rules={[
                      {
                        required: true,
                        message: "Silakan pilih Destinasi Warehouse!",
                      },
                    ]}
                  >
                    {loading ? (
                      <Skeleton.Input
                        active
                        size={"default"}
                        block={true}
                        style={{ width: "100%" }}
                      />
                    ) : (
                      <Select
                        showSearch
                        filterOption={(input, option) => {
                          return (option?.children ?? "")
                            .toLowerCase()
                            .includes(input.toLowerCase())
                        }}
                        placeholder="Pilih Destinasi Warehouse"
                        onChange={(e) => {
                          const newProduct = productData.map((item) => {
                            return {
                              ...item,
                              to_warehouse_id: e,
                            }
                          })
                          setProductData(newProduct)
                        }}
                        disabled={disabled || showKonsinyasi}
                      >
                        {warehouses
                          .filter((item) => item.id !== detailPo?.warehouse_id)
                          .map((warehouse) => (
                            <Select.Option
                              value={warehouse.id}
                              key={warehouse.id}
                            >
                              {warehouse.name}
                            </Select.Option>
                          ))}
                      </Select>
                    )}
                  </Form.Item>
                )}

                {showKonsinyasi && (
                  <Form.Item label="No Preference" name="preference_number">
                    <Input
                      placeholder="Ketik No Preference"
                      disabled={disabled}
                    />
                  </Form.Item>
                )}

                <Form.Item label="Created On" name="created_at">
                  {loading ? (
                    <Skeleton.Input
                      active
                      size={"default"}
                      block={true}
                      style={{ width: "100%" }}
                    />
                  ) : (
                    <Input
                      placeholder="Created At"
                      disabled
                      // bordered={false}
                    />
                  )}
                </Form.Item>

                {!showKonsinyasi && (
                  <Form.Item
                    label="Post To Ethix"
                    name="post_ethix"
                    // rules={[
                    //   {
                    //     required: true,
                    //     message: "Silakan pilih Post To Ethix!",
                    //   },
                    // ]}
                  >
                    {loading ? (
                      <Skeleton.Input
                        active
                        size={"default"}
                        block={false}
                        style={{ width: 500 }}
                      />
                    ) : (
                      <Checkbox
                        checked={sendEthix}
                        onChange={(e) => setSendEthix(e)}
                      >
                        Post to ethix
                      </Checkbox>
                    )}
                  </Form.Item>
                )}
              </div>
              <div className="col-md-12">
                <Form.Item label="Notes" name="notes">
                  {loading ? (
                    <Skeleton.Input
                      active
                      size={"default"}
                      block={true}
                      style={{ width: "100%" }}
                    />
                  ) : (
                    <TextArea placeholder=" Notes" disabled={disabled} />
                  )}
                </Form.Item>
              </div>
            </div>
          </Form>
        </div>
      </div>

      {showContact && (
        <Card
          title="Informasi Alamat"
          className="mt-4"
          extra={
            <FormAddressModal
              initialValues={{
                user_id: userAddress?.id,
                nama: userAddress?.name,
                telepon: userAddress?.telepon || userAddress?.phone,
              }}
              refetch={() => loadUserAddress(userAddress?.id)}
            />
          }
        >
          <Table
            dataSource={userAddress?.address || []}
            columns={[
              {
                title: "No.",
                dataIndex: "no",
                key: "no",
                render: (_, record, index) => index + 1,
              },
              {
                title: "Alamat",
                dataIndex: "alamat_detail",
                key: "alamat_detail",
              },
              {
                title: "Pilih",
                dataIndex: "action",
                key: "action",
                render: (_, record) => {
                  return (
                    <Switch
                      onChange={() => {
                        axios
                          .post("/api/general/switch-default-address", {
                            user_id: userAddress?.id,
                            address_id: record.id,
                          })
                          .then((res) => {
                            console.log(res)
                            loadUserAddress(userAddress?.id)
                          })
                        // Set the selected address to the new one or reset it if already selected
                        setSelectedAddress((prev) =>
                          prev === record.id ? null : record.id
                        )
                      }}
                      checked={selectedAddress === record.id}
                    />
                  )
                },
              },
            ]}
            key={"id"}
            pagination={false}
          />
        </Card>
      )}

      {!showKonsinyasi && (
        <div className="card">
          <div className="card-header">
            <div className="header-titl">
              <strong>Detail Item</strong>
            </div>
          </div>
          <div className="card-body">
            <ProductList
              loading={loading}
              data={productData}
              products={products}
              warehouses={warehouses}
              columns={productListColumns}
              disabled={{
                from_warehouse_id: true,
                qty: true,
                product_id: detail?.status == "done",
                to_warehouse_id: true,
                action: detail ? true : false,
                qty_alocation: inventory_id,
              }}
              handleChange={handleChangeProductItem}
              handleClick={handleClickProductItem}
              multiple={!detail}
              showAdmore={!detail}
            />
          </div>
        </div>
      )}

      {showKonsinyasi && (
        <Card title="Detail Product Konsinyasi" className="mt-4 mb-4">
          <ProductListInput
            initialValues={productItems}
            products={products}
            loading={loadingProduct}
            onChange={(items) => setProductItems(items)}
            taxs={taxs}
            isEdit={inventory_id ? true : false}
            typeProduct="transfer"
          />
          {/* <ProductList
            disabled={detail?.status == "done"} // if status success disable add more button
            data={productItems}
            products={products}
            taxs={taxs}
            discounts={[]}
            onChange={handleChangeProductPriceKons}
            handleChange={handleChangeProductItemKons}
            handleClick={handleClickProductItemKons}
            loading={loadingProduct}
            summary={(currentData) => {
              const subtotal = currentData.reduce(
                (acc, curr) => parseInt(acc) + parseInt(curr.price_nego || 0),
                0
              )

              const discount_amount = currentData.reduce(
                (acc, curr) =>
                  parseInt(acc) + parseInt(curr.discount_amount || 0),
                0
              )
              const dpp = subtotal - discount_amount
              const ppn = dpp * currentData[0]?.tax_percentage
              const total = parseInt(dpp) + parseInt(ppn)
              return (
                <Table.Summary>
                  <Table.Summary.Row>
                    <Table.Summary.Cell align="right" colSpan={7}>
                      <strong>Subtotal :</strong>
                    </Table.Summary.Cell>
                    <Table.Summary.Cell align="left" colSpan={1}>
                      <strong>Rp. {formatNumber(subtotal)}</strong>
                    </Table.Summary.Cell>
                    <Table.Summary.Cell />
                  </Table.Summary.Row>
                  <Table.Summary.Row>
                    <Table.Summary.Cell align="right" colSpan={7}>
                      <strong>Discount :</strong>
                    </Table.Summary.Cell>
                    <Table.Summary.Cell align="left" colSpan={1}>
                      <strong>Rp. {formatNumber(discount_amount)}</strong>
                    </Table.Summary.Cell>
                    <Table.Summary.Cell />
                  </Table.Summary.Row>
                  <Table.Summary.Row>
                    <Table.Summary.Cell align="right" colSpan={7}>
                      <strong>DPP :</strong>
                    </Table.Summary.Cell>
                    <Table.Summary.Cell align="left" colSpan={1}>
                      <strong>Rp. {formatNumber(dpp)}</strong>
                    </Table.Summary.Cell>
                    <Table.Summary.Cell />
                  </Table.Summary.Row>
                  <Table.Summary.Row>
                    <Table.Summary.Cell align="right" colSpan={7}>
                      <strong>PPN :</strong>
                    </Table.Summary.Cell>
                    <Table.Summary.Cell align="left" colSpan={1}>
                      <strong>Rp. {formatNumber(ppn)}</strong>
                    </Table.Summary.Cell>
                    <Table.Summary.Cell />
                  </Table.Summary.Row>

                  <Table.Summary.Row>
                    <Table.Summary.Cell align="right" colSpan={7}>
                      <strong>Total Amount :</strong>
                    </Table.Summary.Cell>
                    <Table.Summary.Cell align="left" colSpan={1}>
                      <strong>Rp. {formatNumber(total)}</strong>
                    </Table.Summary.Cell>
                    <Table.Summary.Cell />
                  </Table.Summary.Row>
                </Table.Summary>
              )
            }}
          /> */}
        </Card>
      )}

      <div className="card p-6 ">
        <div className="flex justify-end">
          <button
            className={`text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center`}
            // style={{
            //   backgroundColor: "#1A56DC",
            //   borderColor: "#1A56DC",
            //   color: "white",
            // }}
            onClick={() => form.submit()}
          >
            Simpan
          </button>
        </div>
      </div>
    </Layout>
  )
}

export default ProductTransferForm
