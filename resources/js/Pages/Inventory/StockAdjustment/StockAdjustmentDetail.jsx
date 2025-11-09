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
  barcodeListColumns,
} from "../config"
import { searchContact, searchSales } from "./service"
import ProductListInput from "../../../components/ProductAdjustment/ProductListDetail"

const ProductTransferDetail = ({ inventory_type = "transfer" }) => {
  const [form] = Form.useForm()
  const role = getItem("role")
  const { inventory_id } = useParams()
  const { uid_lead } = useParams()
  const defaultItems = [
    {
      id: 0,
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
  const [printAdjust, setPrintAdjust] = useState([])

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
              product_id: item.id,
              disabled: final_stock < 1,
            }
          })
        setProducts(newProducts)
        console.log("newProducts", newProducts)
        setLoadingProduct(false)
      })
      .catch((err) => setLoadingProduct(false))
  }

  const loadWarehouse = () => {
    setLoading(true)
    axios.get("/api/master/warehouse").then((res) => {
      setWarehouses(res.data.data)
      setLoading(false)
    })
  }

  const loadUserAddress = (id) => {
    axios.get("/api/general/user-with-address/" + id).then((res) => {
      setUserAddress(res.data.data)
      console.log(res.data.data)
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
        console.log(res.data, "bin by contact")
        const { data } = res?.data || {}
        if (data) {
          setMasterBin(data)
        } else {
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

  const loadInventoryDetail = () => {
    setLoading(true)
    axios.get(`/api/inventory/product/detail/${inventory_id}`).then((res) => {
      const { data } = res.data
      const forms = {
        ...data,
        from_warehouse_id: data?.warehouse_id,
        to_warehouse_id: data?.destination_warehouse_id,
        received_date: formatDate(data.received_date),
        order_number: data?.order_transfer?.order_number,
        invoice_number: data?.order_transfer?.invoice_number,
        master_bin_id: data?.order_transfer?.master_bin_id,
        payment_term: data?.order_transfer?.payment_term,
        reference_number: data?.reference_number,
        created_by_name: data?.created_by_name,
        notes: data?.note,
        inventory_type,
      }

      if (data?.master_bin_id) {
        forms.master_bin_id = {
          label: data?.master_bin_name,
          value: data?.master_bin_id,
        }
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
      setMasterBin([
        {
          id: data?.order_transfer?.master_bin_id,
          name: data?.order_transfer?.master_bin_name,
        },
      ])
      form.setFieldValue(
        "created_on",
        moment(data?.created_on).format("DD-MM-YYYY")
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
      setPrintAdjust(`/print/adjust/${inventory_id}`)
      if (data?.is_konsinyasi == "1") {
        setShowKonsinyasi(true)
      }
      // loadProducts(data?.warehouse_id)

      loadUserAddress(data?.order_transfer?.contact)
      setSendEthix(data?.post_ethix > 0)
      setDetailPo(data?.selected_po)
      setDetail(data)

      setSelectedPo(data?.selected_po)
      setHistoryAllocation(newhistory)

      setShowContact(true)
      const newData = data?.detail_items?.map((item, index) => {
        const discount_amount = item?.discount_amount

        const tax = item.tax_percentage > 0 ? item.tax_percentage / 100 : 0.11
        const subtotal = item?.price_nego
        const tax_amount = (subtotal - discount_amount) * tax

        const preTotal = subtotal - discount_amount + tax_amount
        return {
          ...item,
          tax_id: item?.tax_id || 1,
          key: index,
          price: item?.product_price,
          price_satuan: item?.price_nego / item?.qty,
          subtotal: subtotal,
          total: preTotal,
        }
      })
      console.log("ProductItems", newData)
      setProductData(newData)
      setProductItems(newData)
      setLoading(false)
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

  // cycle
  useEffect(() => {
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
    setShowKonsinyasi(inventory_type === "konsinyasi")
    form.setFieldValue("inventory_type", inventory_type)
    form.setFieldValue("payment_term", 3)
    // form.setFieldValue("created_on", moment(data?.created_on).format("DD-MM-YYYY"))
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
          "From warehouse tidak boleh sama dengan To warehouse"
        )
      }
      datas[key][dataIndex] = value
    }

    if (dataIndex === "to_warehouse_id") {
      if (from_warehouse_id === value) {
        return message.error(
          "From warehouse tidak boleh sama dengan To warehouse"
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

  /* =========================KONSIYASI FIS TRANSFER ============================ */

  const handleClickProductItemKons = ({ type, key }) => {
    const datas = [...productItems]
    if (type === "add") {
      const lastData = datas[datas.length - 1]
      datas.push({
        key: lastData.key + 1,
        id: 0,
        product_id: null,
        price: null,
        qty: 1,
        tax_id: lastData?.tax_id,
        tax_amount: 0,
        tax_percentage: 0,
        discount_percentage: 0,
        discount: 0,
        discount_amount: 0,
        subtotal: 0,
        price_nego: null,
        total: 0,
        margin_price: 0,
        stock: 0,
      })
      return setProductItems(
        datas.map((item, index) => ({ ...item, key: index }))
      )
    }

    if (type === "add-qty") {
      const item = datas[key]
      const qty = item.qty + 1
      const product = products.find(
        (item) => item.id === datas[key]["product_id"]
      )
      datas[key]["qty"] = qty
      if (product) {
        const subtotal_price = product?.price?.final_price * qty
        const tax = taxs.find((item) => item.id === datas[key]["tax_id"])
        const discount = parseInt(datas[key]["discount_amount"])
        if (tax) {
          if (tax.tax_percentage > 0) {
            const qty = datas[key]["qty"]
            const price_nego = datas[key]["price_nego"]
            const tax_percentage = tax.tax_percentage / 100
            datas[key]["tax_percentage"] = tax_percentage

            datas[key]["subtotal"] = subtotal_price
            if (price_nego > 0) {
              const tax_amount = parseInt(
                (price_nego - discount) * tax_percentage
              )
              datas[key]["tax_amount"] = tax_amount
              datas[key]["total"] = tax_amount + parseInt(price_nego - discount)
            } else {
              const tax_amount = parseInt(
                (subtotal_price - discount) * tax_percentage
              )
              datas[key]["tax_amount"] = tax_amount
              datas[key]["total"] =
                tax_amount + parseInt(subtotal_price - discount)
            }
          }
        } else {
          datas[key]["subtotal"] = subtotal_price
          datas[key]["total"] = parseInt(subtotal_price - discount)
        }
      }
      datas[key]["qty"] = qty
      return setProductItems(
        datas.map((item, index) => ({ ...item, key: index }))
      )
    }

    if (type === "remove-qty") {
      const item = datas[key]
      if (item.qty > 1) {
        const product = products.find(
          (item) => item.id === datas[key]["product_id"]
        )
        const qty = item.qty - 1
        datas[key]["qty"] = qty
        if (product) {
          const subtotal_price = product?.price?.final_price * qty
          const tax = taxs.find((item) => item.id === datas[key]["tax_id"])
          const discount = parseInt(datas[key]["discount_amount"])
          if (tax) {
            if (tax.tax_percentage > 0) {
              const qty = datas[key]["qty"]
              const price_nego = datas[key]["price_nego"]
              const tax_percentage = tax.tax_percentage / 100
              datas[key]["tax_percentage"] = tax_percentage

              datas[key]["subtotal"] = subtotal_price
              if (price_nego > 0) {
                const tax_amount = parseInt(
                  (price_nego - discount) * tax_percentage
                )
                datas[key]["tax_amount"] = tax_amount
                datas[key]["total"] =
                  tax_amount + parseInt(price_nego - discount)
              } else {
                const tax_amount = parseInt(
                  (subtotal_price - discount) * tax_percentage
                )
                datas[key]["tax_amount"] = tax_amount
                datas[key]["total"] =
                  tax_amount + parseInt(subtotal_price - discount)
              }
            }
          } else {
            datas[key]["subtotal"] = subtotal_price
            datas[key]["total"] = parseInt(subtotal_price - discount)
          }
        }
        return setProductItems(
          datas.map((item, index) => ({ ...item, key: index }))
        )
      }
      return setProductItems(
        datas.map((item, index) => ({ ...item, key: index }))
      )
    }

    const newData = datas.filter((item) => item.key !== key)
    return setProductItems(
      newData.map((item, index) => ({ ...item, key: index }))
    )
  }

  const handleChangeProductItemKons = ({ dataIndex, value, key }) => {
    const datas = [...productItems]
    dataIndex, value, key
    if (value === null) {
      datas[key][dataIndex] = null
      return setProductItems(datas)
    }

    if (dataIndex === "qty") {
      const product = products.find((item) => item.id === value)
      if (product) {
        datas[key]["subtotal"] = product?.price?.final_price * datas[key]["qty"]
        datas[key][dataIndex] = value
      }
    }

    if (dataIndex === "product_id") {
      const product = products.find((item) => item.id === value)
      if (product) {
        datas[key]["stock"] = product?.final_stock
        datas[key]["price"] = product?.price?.final_price || 0
        datas[key]["subtotal"] = product?.price?.final_price * datas[key]["qty"]
        datas[key]["total"] = product?.price?.final_price * datas[key]["qty"]
        datas[key]["u_of_m"] = product?.u_of_m
        datas[key]["sku"] = product?.sku
      }
      datas[key][dataIndex] = value
    }

    if (dataIndex === "tax_id") {
      datas.forEach((item) => {
        const tax = taxs.find((item) => item.id === value)
        if (tax) {
          if (tax.tax_percentage > 0) {
            const discount_amount = datas[item.key]["discount_amount"]
            const product_id = datas[item.key]["product_id"]
            const qty = datas[item.key]["qty"]
            const price_nego = datas[item.key]["price_nego"]
            const tax_percentage = tax.tax_percentage / 100
            datas[item.key]["tax_percentage"] = tax_percentage
            const product = products.find((item) => item.id === product_id)
            if (product) {
              const subtotal_price = product?.price?.final_price * qty

              if (price_nego > 0) {
                const tax_amount = parseInt(
                  (price_nego - discount_amount) * tax_percentage
                )
                datas[item.key]["tax_amount"] = tax_amount
                datas[item.key]["total"] =
                  tax_amount + parseInt(price_nego - discount_amount)
              } else {
                const tax_amount = parseInt(
                  (subtotal_price - discount_amount) * tax_percentage
                )
                datas[item.key]["tax_amount"] = tax_amount
                datas[item.key]["total"] =
                  tax_amount + parseInt(subtotal_price - discount_amount)
              }
            }
          } else {
            datas[item.key]["tax_percentage"] = 0
          }
        }
        datas[item.key][dataIndex] = value
      })
    }

    setProductItems(datas)
  }

  const handleChangeProductPriceKons = ({ dataIndex, value, key }) => {
    // console.log(dataIndex, value, "handle change product price")
    const datas = [...productItems]
    if (dataIndex === "qty") {
      const product = products.find(
        (item) => item.id === datas[key]["product_id"]
      )
      if (product) {
        const subtotal_price = product?.price?.final_price * value
        const tax = taxs.find((item) => item.id === datas[key]["tax_id"])
        const discount = parseInt(datas[key]["discount_amount"])
        if (tax) {
          if (tax.tax_percentage > 0) {
            const price_nego = datas[key]["price_nego"]
            const tax_percentage = tax.tax_percentage / 100
            datas[key]["tax_percentage"] = tax_percentage

            datas[key]["subtotal"] = subtotal_price

            if (price_nego > 0) {
              const tax_amount = parseInt(
                (price_nego - discount) * tax_percentage
              )
              datas[key]["tax_amount"] = tax_amount
              datas[key]["total"] = tax_amount + parseInt(price_nego - discount)
            } else {
              const tax_amount = parseInt(
                (subtotal_price - discount) * tax_percentage
              )
              datas[key]["tax_amount"] = tax_amount
              datas[key]["total"] =
                tax_amount + parseInt(subtotal_price - discount)
            }
          }
        } else {
          datas[key]["total"] = parseInt(subtotal_price - discount)
        }
      }
    }

    if (dataIndex === "discount") {
      const price_nego = datas[key]["price_nego"]
      const subtotal_price = datas[key]["subtotal"]
      const price_amount = price_nego > 0 ? price_nego : subtotal_price
      datas[key][dataIndex] = value
      datas[key]["discount_percentage"] =
        value > 0 ? (value / price_amount) * 100 : 0
      if (value < 1 || value === "") {
        datas[key]["total"] = datas[key]["subtotal"]
      } else {
        const discount = value * datas[key]["qty"]
        datas[key]["discount_amount"] = discount
        const product = products.find(
          (item) => item.id === datas[key]["product_id"]
        )
        if (product) {
          const subtotal_price = product?.price?.final_price * datas[key]["qty"]
          const tax = taxs.find((item) => item.id === datas[key]["tax_id"])
          if (tax) {
            if (tax.tax_percentage > 0) {
              const price_nego = datas[key]["price_nego"]
              const tax_percentage = tax.tax_percentage / 100
              datas[key]["tax_percentage"] = tax_percentage

              datas[key]["subtotal"] = subtotal_price
              if (price_nego > 0) {
                const tax_amount = parseInt(
                  (price_nego - discount) * tax_percentage
                )
                datas[key]["tax_amount"] = tax_amount
                datas[key]["total"] =
                  tax_amount + parseInt(price_nego - discount)
              } else {
                const tax_amount = parseInt(
                  (subtotal_price - discount) * tax_percentage
                )
                datas[key]["tax_amount"] = tax_amount
                datas[key]["total"] =
                  tax_amount + parseInt(subtotal_price - discount)
              }
            }
          } else {
            datas[key]["total"] = parseInt(subtotal_price - discount)
          }
        }
      }
    }
    if (dataIndex === "price_nego") {
      datas[key][dataIndex] = value
      const discount = parseInt(datas[key]["discount_amount"])
      const product_id = datas[key]["product_id"]
      const product = products.find((item) => item.id === product_id)
      if (value < 1 || value === "") {
        const subtotal = datas[key]["subtotal"]
        datas[key]["total"] = subtotal - discount
      } else {
        if (product) {
          const subtotal = value
          const tax = taxs.find((item) => item.id === datas[key]["tax_id"])
          if (tax) {
            if (tax.tax_percentage > 0) {
              const tax_percentage = tax.tax_percentage / 100
              datas[key]["tax_percentage"] = tax_percentage

              const tax_amount = parseInt(
                (subtotal - discount) * tax_percentage
              )

              datas[key]["tax_amount"] = tax_amount
              datas[key]["total"] = tax_amount + parseInt(subtotal - discount)
            }
          } else {
            datas[key]["total"] = subtotal - discount
          }
        }
      }
    }

    datas[key][dataIndex] = value
    setProductItems(datas)
  }
  /* =========================END KONSIYASI FIS TRANSFER ============================ */
  const onFinish = (values) => {
    // const productItem = productData.every((item) => item.to_warehouse_id)
    // if (!productItem) {
    //   return message.error("Please select product")
    // }

    const data = {
      ...values,
      po_number: selectedPo?.po_number,
      created_by: selectedPo?.created_by || userData?.id,
      items: productData,
      itemkons: productItems,
      note: values.notes,
      post_ethix: sendEthix ? 1 : 0,
      account_id: getItem("account_id"),
      payment_term: 3,
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

  const handleComplete = () => {
    setLoadingComplete(true)
    const data = { uid_inventory: detail?.uid_inventory }
    let url =
      "/api/inventory/product/transfer/complete/" + detail?.uid_inventory
    axios
      .post(url, data)
      .then((res) => {
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        setLoadingComplete(false)
        loadInventoryDetail()
        // return navigate(-1)
      })
      .catch((err) => {
        const { message } = err.response.data
        setLoadingComplete(false)
        toast.error(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }

  const handleApprove = () => {
    setLoadingComplete(true)
    const data = { uid_inventory: detail?.uid_inventory }
    let url =
      "/api/inventory/product/adjustment/approve/" + detail?.uid_inventory
    axios
      .post(url, data)
      .then((res) => {
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        setLoadingComplete(false)
        loadInventoryDetail()
        // return navigate(-1)
      })
      .catch((err) => {
        const { message } = err.response.data
        setLoadingComplete(false)
        toast.error(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }

  const handleProcess = () => {
    setLoadingComplete(true)
    const data = { uid_inventory: detail?.uid_inventory }
    let url =
      "/api/inventory/product/adjustment/process/" + detail?.uid_inventory
    axios
      .post(url, data)
      .then((res) => {
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        setLoadingComplete(false)
        loadInventoryDetail()
        // return navigate(-1)
      })
      .catch((err) => {
        const { message } = err.response.data
        setLoadingComplete(false)
        toast.error(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }

  const handleReject = () => {
    setLoadingReject(true)
    axios
      .post(
        `/api/inventory/product/adjustment/reject/${detail?.uid_inventory}`,
        {}
      )
      .then((res) => {
        loadInventoryDetail()
        toast.success("Stock Adjustment Berhasil Ditolak", {
          position: toast.POSITION.TOP_RIGHT,
        })
        setLoadingReject(false)
      })
      .catch(() => {
        setLoadingReject(true)
        toast.error("Stock Adjustment Gagal Di Tolak", {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }

  const canAllocated =
    inArray(getItem("role"), [
      "adminsales",
      "warehouse",
      "adminwarehouse",
      "finance",
      "superadmin",
    ]) && productData.length > 0
  const canComplete =
    inArray(getItem("role"), [
      "warehouse",
      "adminwarehouse",
      "finance",
      "superadmin",
    ]) && detail?.status === "waiting"

  const canProcessApprove =
    inArray(getItem("role"), [
      "warehouse",
      "adminwarehouse",
      "finance",
      "superadmin",
    ]) && detail?.status === "draft"

  const disabled = true
  const canApprove = canComplete
  const complete = detail?.status === "done"
  console.log(disabled, "disabled")
  const hrefNavigate = () => {
    switch (inventory_type) {
      case "adjustment":
        return "/stock-adjustment"

      default:
        return "/inventory-new"
    }
  }
  return (
    <Layout
      href={hrefNavigate()}
      title="Form Item Stock Adjustment"
      rightContent={
        inventory_id && (
          <div className="flex justify-end">
            {canApprove && (
              <>
                <button
                  className="mr-2 text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
                  onClick={() => {
                    if (loadingReject) {
                      return null
                    }
                    return handleReject()
                  }}
                >
                  {loadingReject ? <LoadingOutlined /> : <CloseOutlined />}
                  <span className="ml-2">Reject</span>
                </button>

                <button
                  className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
                  disabled={loadingComplete}
                  onClick={() => {
                    if (loadingComplete) {
                      return null
                    }

                    return handleApprove()
                  }}
                >
                  {loadingComplete ? <LoadingOutlined /> : <CheckOutlined />}
                  <span className="ml-2">Approve</span>
                </button>
              </>
            )}

            {canProcessApprove && (
              <button
                className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
                onClick={() => {
                  if (loadingComplete) {
                    return null
                  }

                  return handleProcess()
                }}
              >
                {loadingComplete ? <LoadingOutlined /> : <CheckOutlined />}
                <span className="ml-2">Proses Approval</span>
              </button>
            )}
          </div>
        )
      }
    >
      <div className="card">
        <div className="card-header flex justify-between items-center">
          <strong>Form Stock Adjustment</strong>
          <div>
            <Dropdown.Button
              style={{ borderRadius: 10 }}
              icon={<PrinterTwoTone />}
              overlay={
                <Menu>
                  <Menu.Item className="flex justify-between items-center">
                    <PrinterTwoTone />{" "}
                    <a href={printAdjust} target="_blank">
                      <span>Print Penyesuaian Stok</span>
                    </a>
                  </Menu.Item>
                </Menu>
              }
            ></Dropdown.Button>
          </div>
        </div>
        <div className="card-body">
          <Form
            form={form}
            name="basic"
            layout="vertical"
            onFinish={onFinish}
            autoComplete="off"
          >
            <div className="row">
              <div className="col-md-6">
                <Form.Item label="Adjustment ID" name="so_ethix">
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
                <Form.Item label="Company Account" name="company_id">
                  {loading ? (
                    <Skeleton.Input
                      active
                      size={"default"}
                      block={true}
                      style={{ width: "100%" }}
                    />
                  ) : (
                    <Select
                      disabled
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
                <Form.Item label="No Preference" name="reference_number">
                  <Input
                    placeholder="Ketik No Preference"
                    disabled={disabled}
                  />
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
                    <Select.Option value="adjustment" key="2">
                      Adjustment
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
                        disabled={disabled}
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

                <Form.Item label="Created On" name="created_on">
                  {loading ? (
                    <Skeleton.Input
                      active
                      size={"default"}
                      block={true}
                      style={{ width: "100%" }}
                    />
                  ) : (
                    <Input
                      placeholder=" Created On"
                      disabled
                      // bordered={false}
                    />
                  )}
                </Form.Item>
                <Form.Item
                  label="Destinasi Warehouse"
                  name="warehouse_id"
                  rules={[
                    {
                      required: true,
                      message: "Silakan pilih Warehouse!",
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
                      disabled={disabled}
                    >
                      {warehouses.map((warehouse) => (
                        <Select.Option value={warehouse.id} key={warehouse.id}>
                          {warehouse.name}
                        </Select.Option>
                      ))}
                    </Select>
                  )}
                </Form.Item>
                <Form.Item
                  label="Destinasi BIN"
                  name="master_bin_id"
                  rules={[
                    {
                      required: true,
                      message: "Silakan pilih Warehouse!",
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

      <Card title="Detail Produk" className="mt-4 mb-4">
        <ProductListInput
          initialValues={productItems}
          products={products}
          loading={loadingProduct || loading}
          onChange={(items) => setProductItems(items)}
          taxs={taxs}
          disabled={true}
        />
      </Card>
      {inventory_id && (
        <div className="card">
          <div className="card-header">
            <div className="header-titl">
              <strong>History Alokasi Item</strong>
            </div>
          </div>
          <div className="card-body">
            <ProductList
              loading={loading}
              data={historyAllocation}
              products={products}
              warehouses={warehouses}
              columns={productListAllocationHistoryColumns}
              disabled={{
                from_warehouse_id: true,
                qty: true,
                product_id: true,
                to_warehouse_id: true,
                qty_alocation: true,
              }}
              handleChange={handleChangeProductItem}
              multiple={false}
              action={false}
              showAdmore={false}
            />
          </div>
        </div>
      )}

      {canAllocated && (
        <>
          {!disabled && (
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
          )}
        </>
      )}
      {canComplete && !showKonsinyasi && (
        <div className="card p-6 ">
          <div className="flex justify-end">
            <button
              type="button"
              className={`text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center`}
              // style={{
              //   backgroundColor: "#1A56DC",
              //   borderColor: "#1A56DC",
              //   color: "white",
              // }}
              disabled={loadingComplete}
              onClick={() => {
                if (loadingComplete) {
                  return null
                }

                return handleComplete()
              }}
            >
              {loadingComplete ? <LoadingOutlined /> : "Complete"}
            </button>
          </div>
        </div>
      )}
    </Layout>
  )
}

export default ProductTransferDetail
