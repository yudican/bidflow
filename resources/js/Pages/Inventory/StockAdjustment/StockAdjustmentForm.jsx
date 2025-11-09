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
import ProductKonsinyasi from "../Components/ProductKonsinyasi"
import ProductList from "../Components/ProductList"
import {
  productListAllocationHistoryColumns,
  productListColumns,
} from "../config"
import { searchContact, searchSales } from "./service"
import ProductListInput from "../../../components/ProductAdjustment"

const StockAdjustmentForm = ({ inventory_type = "transfer" }) => {
  const [form] = Form.useForm()
  const role = getItem("role")
  const { inventory_id } = useParams()
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
  const [allBins, setAllBins] = useState([])
  const [detailPo, setDetailPo] = useState(null)
  const [detail, setDetail] = useState(null)
  const [loading, setLoading] = useState(false)
  const [loadingProduct, setLoadingProduct] = useState(false)
  const [sendEthix, setSendEthix] = useState(false)
  const [showKonsinyasi, setShowKonsinyasi] = useState(false)
  const [salesList, setSalesList] = useState([])
  const [termOfPayments, setTermOfPayments] = useState([])
  const [taxs, setTaxs] = useState([])
  const [showContact, setShowContact] = useState(false)
  const [userAddress, setUserAddress] = useState(null)
  const [selectedAddress, setSelectedAddress] = useState(null)
  const [printSi, setPrintSi] = useState([])
  const [printSo, setPrintSo] = useState([])

  const [isBinDisabled, setIsBinDisabled] = useState(true)
  const [isInitialized, setIsInitialized] = useState(false)

  // api
  const loadProducts = (warehouse_id = 2, master_bin_id = null) => {
    setLoadingProduct(true)
    axios
      .get("/api/master/product-lists")
      .then((res) => {
        const products = res.data.data
        console.log("products", products)
        const newProducts = products
          // .filter((item) => {
          //     return item.stock_warehouse.some((row) => row.id == warehouse_id)
          // })
          .map((item) => {
            let stock_off_market = 0
            let location_id =
              warehouse_id == "19" ? master_bin_id : warehouse_id
            const stocks =
              warehouse_id == "19" ? item?.stock_bins : item?.stock_warehouse
            stock_off_market =
              stocks?.find((itemStock) => itemStock.id == location_id)?.stock ||
              0

            // const final_stock = item.stock_warehouse.find(
            //     (row) => row.id == warehouse_id
            // )?.stock
            return {
              ...item,
              final_stock: stock_off_market,
              stock_off_market: stock_off_market,
              product_id: item.id,
              disabled: false,
            }
          })

        console.log("newProducts", newProducts)
        setProducts(newProducts)
        setLoadingProduct(false)
        form.setFieldValue(
          "created_on",
          moment(new Date()).format("DD-MM-YYYY")
        )
        // form.setFieldValue(
        //   "created_at",
        //   moment(new Date()).format("DD-MM-YYYY")
        // )
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

  const loadMasterBin = async () => {
    try {
      const response = await axios.get("/api/master/bin")
      const bins = response.data.data

      // Set semua data dan batasi awalnya hanya 5
      setAllBins(bins)
      setMasterBin(bins.slice(0, 5)) // Batasi hanya 5 item
      setLoading(false)
    } catch (error) {
      console.error("Error loading master bin:", error)
      setLoading(false)
    }
  }

  const handleSearch = (searchValue) => {
    if (!searchValue) {
      // Reset ke 5 data awal jika pencarian dihapus
      setMasterBin(allBins.slice(0, 5))
    } else {
      // Filter data berdasarkan pencarian
      const filteredBins = allBins.filter((bin) =>
        bin.name.toLowerCase().includes(searchValue.toLowerCase())
      )
      setMasterBin(filteredBins)
    }
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

  const loadInventoryDetail = (shouldResetProducts = true) => {
    setLoading(true)
    axios.get(`/api/inventory/product/detail/${inventory_id}`).then((res) => {
      const { data } = res.data
      console.log("cek data product: ", data)
      const forms = {
        ...data,
        from_warehouse_id: data?.warehouse_id,
        to_warehouse_id: data?.destination_warehouse_id,
        received_date: formatDate(data.received_date),
        order_number: data?.order_transfer?.order_number,
        invoice_number: data?.order_transfer?.invoice_number,
        master_bin_id: data?.master_bin_id,
        payment_term: data?.order_transfer?.payment_term,
        reference_number: data?.reference_number,
        created_by_name: data?.created_by_name,
        notes: data?.note,
        inventory_type,
        created_on: moment(new Date(data?.created_on)).format("DD-MM-YYYY"),
      }

      if (data?.master_bin_id) {
        forms.master_bin_id = {
          label: data?.master_bin_name,
          value: data?.master_bin_id,
        }
      }

      if (data?.order_transfer?.sales) {
        forms.sales = {
          label: data?.order_transfer?.sales_name,
          value: data?.order_transfer?.sales,
        }
      }

      if (data?.warehouse_id === 19) {
        setIsBinDisabled(false)
      }

      form.setFieldsValue(forms)
      form.setFieldValue(
        "created_on",
        moment(new Date(data?.created_on)).format("DD-MM-YYYY")
      )
      form.setFieldValue(
        "created_at",
        moment(new Date(data?.created_at)).format("DD-MM-YYYY")
      )

      const newData = data?.detail_items?.map((item, index) => {
        return {
          ...item,
          key: index,
          price: item?.product_price,
          price_satuan: item?.price_nego / item?.qty,
          stock: item?.stock_awal,
        }
      })
      if (shouldResetProducts) {
        setProductData(newData)
        setProductItems(newData) // Isi hanya saat load pertama kali
      }

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
      loadProducts(data?.warehouse_id, data?.master_bin_id)

      loadUserAddress(data?.order_transfer?.contact)
      // loadBinByContact(data?.order_transfer?.contact)
      setSendEthix(data?.post_ethix > 0)
      setDetailPo(data?.selected_po)
      setDetail(data)

      setSelectedPo(data?.selected_po)
      setHistoryAllocation(newhistory)

      setShowContact(true)

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
    // loadProducts()
    loadWarehouse()
    handleGetSales()
    handleSearchSales()
    loadMasterBin()
    loadTop()
    loadTaxs()
    // getProductNeed()
    if (inventory_id) {
      loadInventoryDetail()
      setIsInitialized(true)
    } else {
      loadTrfNumber()
      getCreatedInfo()
      loadSoNumber()
      loadSiNumber()
      form.setFieldValue("created_at", moment(new Date()).format("DD-MM-YYYY"))
    }
    setShowKonsinyasi(inventory_type === "adjustment")
    form.setFieldValue("inventory_type", inventory_type)
    form.setFieldValue("payment_term", 3)
    form.setFieldValue("account_id", Number(getItem("account_id")))
    // form.setFieldValue("created_on", moment(new Date()).format("DD-MM-YYYY"))
  }, [])

  const onFinish = (values) => {
    // const productItem = productData.every((item) => item.to_warehouse_id)
    // if (!productItem) {
    //   return message.error("Please select product")
    // }
    console.log(values.master_bin_id, "values master_bin_id")
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
      master_bin_id: values.master_bin_id?.value || values.master_bin_id,
      inventory_id,
    }
    console.log(data, "data")
    let url = "/api/inventory/product/adjustment/save"
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

  const handleSearchSales = async (e) => {
    return searchSales(e).then((results) => {
      const newResult = results.map((result) => {
        return { label: result.nama, value: result.id }
      })

      return newResult
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

  const handleWarehouseChange = (warehouse_id) => {
    loadProducts(warehouse_id)
    setDetailPo({ warehouse_id })
    setProductItems([defaultItems])
    if (warehouse_id === 19) {
      setIsBinDisabled(false)
      form.setFields([
        {
          name: "master_bin_id",
          errors: [],
        },
      ])
    } else {
      setIsBinDisabled(true)
      form.setFieldsValue({ master_bin_id: undefined }) // Reset nilai jika field dinonaktifkan
      form.setFields([
        {
          name: "master_bin_id",
          errors: [],
        },
      ])
    }
  }

  const disabled = detail?.status === "done"

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
      // onClick={() => navigate(-1)}
      href={hrefNavigate()}
      title="Form Item Stock Adjustment"
      rightContent={
        <>
          <div className="flex justify-end">
            <button
              className={`text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center`}
              onClick={() => form.submit()}
            >
              Simpan
            </button>
          </div>
        </>
      }
    >
      <div className="card">
        <div className="card-header flex justify-between items-center">
          <strong>Form Stock Adjustment Product</strong>
          <div></div>
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
                      placeholder="Pilih Company Account"
                    />
                  )}
                </Form.Item>
                <Form.Item label="No Preference" name="reference_number">
                  <Input
                    placeholder="Ketik No Preference"
                    disabled={disabled}
                  />
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
                    <Select.Option value="adjustment" key="2">
                      Adjustment
                    </Select.Option>
                  </Select>
                </Form.Item>
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
                      onChange={handleWarehouseChange}
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
                      required: !isBinDisabled,
                      message: "Silahkan Masukkan Destinasi BIN",
                    },
                  ]}
                >
                  {loading ? (
                    <Skeleton.Input active size={"default"} block={true} />
                  ) : (
                    <Select
                      disabled={isBinDisabled}
                      showSearch
                      placeholder="Pilih Destinasi BIN"
                      optionFilterProp="children"
                      className="w-full"
                      onSearch={handleSearch} // Trigger pencarian
                      onChange={(value) => {
                        loadProducts(19, value)
                      }}
                      filterOption={false} // Nonaktifkan filter bawaan
                    >
                      {masterBin.map((bin) => (
                        <Select.Option key={bin.id} value={bin.id}>
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
          isEdit={inventory_id ? true : false}
          taxs={taxs}
          typeProduct="adjustment"
        />
      </Card>
    </Layout>
  )
}

export default StockAdjustmentForm
