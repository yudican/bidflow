import { LoadingOutlined } from "@ant-design/icons"
import {
  Button,
  Card,
  Form,
  Input,
  Select,
  Skeleton,
  Switch,
  Table,
  message,
} from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import DebounceSelect from "../../components/atoms/DebounceSelect"
import Layout from "../../components/layout"
import { formatNumber, getItem } from "../../helpers"
import FormAddressModal from "../Contact/Components/FormAddressModal"
import ModalBilling from "./Components/ModalBilling"
import ProductList from "./Components/ProductList"
import { billingColumns } from "./config"
import { searchContact, searchSales } from "./service"

const OrderInvoiceForm = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const role = getItem("role")
  const userData = getItem("user_data", true)
  const { uid_lead } = useParams()
  const defaultItems = [
    {
      product_id: null,
      price: null,
      qty: 1,
      tax_id: null,
      discount_id: null,
      final_price: null,
      total_price: null,
      uid_lead,
      margin_price: 0,
      bundling: 1,
      price_product: 0,
      price_nego: 0,
      total_price_nego: 0,
      stock: 0,
      id: 0,
      key: 0,
    },
  ]
  const [detail, setDetail] = useState(null)
  const [warehouses, setWarehouses] = useState([])
  const [masterBin, setMasterBin] = useState([])
  const [termOfPayments, setTermOfPayments] = useState([])
  const [brands, setBrands] = useState([])
  const [products, setProducts] = useState([])
  const [taxs, setTaxs] = useState([])
  const [discounts, setDiscounts] = useState([])
  const [productItems, setProductItems] = useState(defaultItems)
  const [productLoading, setProductLoading] = useState(false)
  const [billingData, setBilingData] = useState([])
  const [loading, setLoading] = useState(false)
  const [loadingSubmit, setLoadingSubmit] = useState(false)
  const [contactList, setContactList] = useState([])
  const [showContact, setShowContact] = useState(false)
  const [salesList, setSalesList] = useState([])
  const [showBilling, setShowBilling] = useState(false)
  const [showBin, setShowBin] = useState(false)
  const [status, setStatus] = useState(0)
  const [userAddress, setUserAddress] = useState(null)
  const [selectedAddress, setSelectedAddress] = useState(null)
  const [selectedwarehouse, setSelectedWarehouse] = useState(null)
  const [loadingBrand, setLoadingBrand] = useState(false)

  const [seletedContact, setSeletedcontact] = useState(null)
  const loadBrand = () => {
    setLoadingBrand(true)
    axios
      .get("/api/master/brand")
      .then((res) => {
        setBrands(res.data.data)
        setLoadingBrand(false)
      })
      .catch((err) => setLoadingBrand(false))
  }

  const loadWarehouse = () => {
    axios.get("/api/master/warehouse").then((res) => {
      setWarehouses(res.data.data)
    })
  }
  const loadMasterBin = () => {
    axios.get("/api/master/bin").then((res) => {
      setMasterBin(res.data.data)
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

  const loadTop = () => {
    axios.get("/api/master/top").then((res) => {
      setTermOfPayments(res.data.data)
    })
  }

  const loadProducts = (warehouse_id) => {
    axios.get("/api/master/products/sales-offline").then((res) => {
      const { data } = res.data

      const newData = data.map((item) => {
        const stock_warehouse =
          (item?.stock_warehouse &&
            item.stock_warehouse.length > 0 &&
            item?.stock_warehouse) ||
          []
        const stock_off_market = stock_warehouse.find(
          (item) => item.id == warehouse_id
        )

        const canBuy =
          stock_off_market?.stock < item?.qty_bundling ? true : false

        const stock = stock_off_market?.stock || 0
        return {
          ...item,
          stock_off_market: stock_off_market?.stock || 0,
          // disabled: stock < 1 ? true : false,
        }
      })
      setProducts(newData)
    })
  }

  const loadTaxs = () => {
    axios.get("/api/master/taxs").then((res) => {
      setTaxs(res.data.data)
    })
  }

  const loadDiscounts = () => {
    axios.get("/api/master/discounts/sales-offline").then((res) => {
      setDiscounts(res.data.data)
    })
  }

  const getOrderBilling = () => {
    setLoading(true)
    axios.get(`/api/order-manual/billing/list/${uid_lead}`).then((res) => {
      const { data } = res.data
      if (data && data.length > 0) {
        const dataBillings = data.map((item) => {
          return {
            id: item.id,
            account_name: item.account_name,
            account_bank: item.account_bank,
            total_transfer: formatNumber(item.total_transfer),
            transfer_date: item.transfer_date,
            upload_billing_photo: item.upload_billing_photo_url,
            upload_transfer_photo: item.upload_transfer_photo_url,
            status: item.status,
            notes: item.notes ?? "-",
            approved_by_name: item.approved_by_name,
            approved_at: item.approved_at || "-",
            payment_number: item.payment_number || "-",
          }
        })
        setBilingData(dataBillings)
        setLoading(false)
      }
    })
  }

  const loadProductDetail = (updateForm = true) => {
    setLoading(true)
    setProductLoading(true)
    axios
      .get(`/api/order-manual/${uid_lead}`)
      .then((res) => {
        const { data } = res.data
        setDetail(data)
        setLoading(false)
        setProductLoading(false)

        if (updateForm) {
          setSeletedcontact({
            label: data?.contact_name,
            value: data?.contact_user?.id,
          })
          const forms = {
            ...data,
            contact: {
              label: data?.contact_name,
              value: data?.contact_user?.id,
            },
            sales: {
              label: data?.sales_user?.name,
              value: data?.sales_user?.id,
            },
            payment_terms: data?.payment_term?.id,
          }

          if (role === "sales") {
            forms.sales = {
              label: userData.name,
              value: userData.id,
            }
          }

          form.setFieldsValue(forms) // suspected form error

          setShowBilling(forms.payment_terms === 4 ? true : false)
          setShowBin(forms.master_bin_id ? true : false)
          loadProducts(data?.warehouse_id)
          setSelectedWarehouse(data?.warehouse_id)
          setShowContact(data?.contact ? true : false)
          loadUserAddress(data?.contact_user?.id)
        } else {
          if (seletedContact) {
            form.setFieldValue("contact", {
              label: seletedContact?.label,
              value: seletedContact?.value,
            })
          }
        }

        getOrderBilling()
      })
      .catch((e) => setLoading(false))
  }

  const getProductNeed = (warehouse_id = null) => {
    // setProductLoading(true)
    axios.get(`/api/order-manual/product-need/${uid_lead}`).then((res) => {
      const { data } = res.data
      if (data && data.length > 0) {
        const newData = data?.map((item, index) => {
          const stock_warehouse =
            (item?.product?.stock_warehouse &&
              item?.product?.stock_warehouse.length > 0 &&
              item?.product?.stock_warehouse) ||
            []
          const stock_off_market =
            stock_warehouse.find((item) => item.id == selectedwarehouse)
              ?.stock || item?.product?.stock_off_market
          const som = stock_off_market
          const bundling =
            item?.product?.qty_bundling > 0 ? item?.product?.qty_bundling : 1

          return {
            key: index,
            id: item.id,
            product: item?.product?.name || "-",
            product_id: item?.product_id,
            price: formatNumber(item?.prices?.final_price),
            qty: item?.qty,
            total_price: formatNumber(item?.prices?.final_price * item?.qty),
            final_price: formatNumber(item?.final_price),
            margin_price: formatNumber(item?.margin_price),
            discount_id: item?.discount_id,
            bundling: item?.product?.qty_bundling,
            tax_id: item?.tax_id,
            tax_amount: formatNumber(item?.tax_amount),
            uid_lead,
            stock: som,
            price_nego: item?.price_nego,
            price_product: formatNumber(item?.price),
            total_price_nego: formatNumber(item?.price_nego),
            disabled_discount: item?.disabled_discount,
            disabled_price_nego: item?.disabled_price_nego,
          }
        })
        setProductItems(newData)
        // setProductLoading(false)
        loadProductDetail(false)
      }
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

  useEffect(() => {
    getOrderBilling()
    loadBrand()
    loadWarehouse()
    loadMasterBin()
    loadTop()
    loadTaxs()
    loadDiscounts()
    loadProductDetail()
    getProductNeed()
    handleGetContact()
    handleGetSales()
  }, [])

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

  const handleChangeProductPrice = ({ dataIndex, value, key }) => {
    // console.log(dataIndex, value, "handle change product price")
    const data = [...productItems]
    // if (dataIndex ==='qty'){}
    data[key][dataIndex] = value
    setProductItems(data)
  }

  const handleChangeProductItem = ({ dataIndex, value, key }) => {
    console.log(dataIndex, value, "handle change product item")
    const record = productItems.find((item) => item.id === key) || {}
    axios
      .post("/api/lead-master/product-needs", {
        ...record,
        [dataIndex]: parseInt(value),
        final_price: record?.price_nego,
        price: record?.price_product,
        qty: dataIndex == "product_id" ? 1 : record.qty,
        key,
        item_id: key > 0 ? key : null,
      })
      .then((res) => {
        setLoading(true)
        getProductNeed()
      })
  }

  const productItem = (value) => {
    const item = value.type === "add" ? defaultItems[0] : {}
    setProductLoading(true)
    axios
      .post(`/api/order-manual/product-items/${value.type}`, {
        ...value,
        ...item,
        item_id: value.key,
        uid_lead,
      })
      .then((res) => {
        const { message } = res.data
        getProductNeed()
        setProductLoading(false)
        toast.success(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }
  const handleClickProductItem = (value) => {
    productItem({ ...value, newData: false })
    if (productItems.length === 1 && productItems[0].id === 0) {
      productItem({ ...value, newData: true })
    }
  }

  const onFinish = (values) => {
    setLoadingSubmit(true)
    const hasProduct = productItems.every((item) => item.product_id)
    if (!hasProduct) {
      setLoadingSubmit(false)
      return toast.error("Product harus diisi", {
        position: toast.POSITION.TOP_RIGHT,
      })
    }

    // if (!selectedAddress) {
    //   toast.error("Alamat Belum Dipilih", {
    //     position: toast.POSITION.TOP_RIGHT,
    //   });
    // }

    const hasQty = productItems.every((item) => item.qty > 0)
    if (!hasQty) {
      setLoadingSubmit(false)
      return toast.error("Minimal Pembalian adalah 1", {
        position: toast.POSITION.TOP_RIGHT,
      })
    }

    const billingStatus = showBilling ? 2 : 1
    const status_save = status < 0 ? status : billingStatus
    axios
      .post("/api/order-manual/form/save", {
        ...values,
        status: status_save,
        status_save: detail?.status,
        uid_lead,
        address_id: selectedAddress,
        contact: values.contact.value,
        sales: values.sales.value,
        kode_unik: detail?.kode_unik,
        product_items: productItems,
        account_id: getItem("account_id"),
        type: "manual",
      })
      .then((res) => {
        setLoadingSubmit(false)
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        return navigate("/order/order-manual")
      })
      .catch((err) => {
        const { message } = err.response.data
        setLoadingSubmit(false)
        toast.error(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }

  // if (loading) {
  //   return (
  //     <Layout title="Detail" href="/order/order-manual">
  //       <LoadingFallback />
  //     </Layout>
  //   )
  // }

  return (
    <Layout
      title="Order Manual Form"
      href="/order/order-manual"
      // rightContent={rightContent}
    >
      <Form
        form={form}
        name="basic"
        layout="vertical"
        onFinish={onFinish}
        //   onFinishFailed={onFinishFailed}
        autoComplete="off"
      >
        <Card title="Form Order">
          <div className="card-body grid grid-cols-2 gap-x-4">
            <Form.Item
              label="Type Customer"
              name="type_customer"
              rules={[
                {
                  required: true,
                  message: "Silahkan Masukkan Type Customer!",
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
                placeholder="Pilih Type Customer"
              >
                <Select.Option value={"new"} key={"new"}>
                  New Customer
                </Select.Option>
                <Select.Option value={"existing"} key={"existing"}>
                  Existing Customer
                </Select.Option>
              </Select>
            </Form.Item>

            <Form.Item
              label="Contact"
              name="contact"
              rules={[
                {
                  required: true,
                  message: "Silahkan Masukkan Contact!",
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
                onChange={(e) => {
                  loadUserAddress(e.value)
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
                        <strong className="text-blue-500">+ Add Contact</strong>
                      </Button>
                    </div>
                  </>
                )}
              />
            </Form.Item>

            <Form.Item
              label="Sales"
              name="sales"
              rules={[
                {
                  required: true,
                  message: "Silahkan Masukkan Sales!",
                },
              ]}
            >
              <DebounceSelect
                disabled={role === "sales"}
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

            <Form.Item
              label="Brand"
              name="brand_id"
              rules={[
                {
                  required: true,
                  message: "Silahkan Masukkan Brand!",
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
                placeholder="Pilih Brand"
                loading={loadingBrand}
              >
                {brands.map((brand) => (
                  <Select.Option value={brand.id} key={brand.id}>
                    {brand.name}
                  </Select.Option>
                ))}
              </Select>
            </Form.Item>

            <Form.Item
              label="Warehouse"
              name="warehouse_id"
              rules={[
                {
                  required: true,
                  message: "Silahkan Masukkan Warehouse!",
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
                placeholder="Pilih Warehouse"
                onChange={(e) => {
                  setSelectedWarehouse(e)
                  loadProducts(e)
                  setProductItems(defaultItems)
                }}
              >
                {warehouses.map((warehouse) => (
                  <Select.Option value={warehouse.id} key={warehouse.id}>
                    {warehouse.name}
                  </Select.Option>
                ))}
              </Select>
            </Form.Item>

            <Form.Item
              label="Payment Term"
              name="payment_terms"
              rules={[
                {
                  required: true,
                  message: "Silahkan Masukkan Payment Term!",
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
                onChange={(e) => {
                  setShowBilling(e === 4 ? true : false)
                  setShowBin(e === 3 ? true : false)
                }}
              >
                {termOfPayments.map((top) => (
                  <Select.Option value={top.id} key={top.id}>
                    {top.name}
                  </Select.Option>
                ))}
              </Select>
            </Form.Item>

            <Form.Item label="No Preference" name="preference_number">
              <Input placeholder="Ketik No Preference" />
            </Form.Item>

            {showBin && (
              <Form.Item
                label="Lokasi BIN"
                name="master_bin_id"
                rules={[
                  {
                    required: true,
                    message: "Silahkan Masukkan Warehouse!",
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
                  placeholder="Pilih Lokasi BIN"
                >
                  {masterBin
                    .filter((item) =>
                      item?.users?.some(
                        (user) => user?.id === seletedContact.value
                      )
                    )
                    .map((bin) => (
                      <Select.Option value={bin.id} key={bin.id}>
                        {bin.name}
                      </Select.Option>
                    ))}
                </Select>
              </Form.Item>
            )}

            <Form.Item
              className={showBin ? "col-span-2" : ""}
              label="Customer Need"
              name="customer_need"
            >
              {showBin ? (
                <Input.TextArea placeholder="Ketik Customer Need" />
              ) : (
                <Input placeholder="Ketik Customer Need" />
              )}
            </Form.Item>
            {/* {seletedContact && (
                <Form.Item
                  label=" Contact Address"
                  name="address_id"
                  rules={[
                    {
                      required: true,
                      message: "Silahkan Masukkan Contact Address!",
                    },
                  ]}
                >
                  <Select
                    placeholder="Pilih Contact Address"
                    dropdownStyle={{ zIndex: 2 }}
                    dropdownRender={(menu) => (
                      <div className="px-2 mx-auto  z-50">
                        {menu}
                        <div className="text-center">
                          <Divider
                            style={{
                              margin: "8px 0",
                            }}
                          />

                          <FormAddressModal
                            initialValues={{
                              user_id: userAddress?.id,
                              nama: userAddress?.name,
                              telepon:
                                userAddress?.telepon || userAddress?.phone,
                            }}
                            refetch={() => loadUserAddress(userAddress?.id)}
                          />
                        </div>
                      </div>
                    )}
                  >
                    {userAddress?.address?.map((bin) => (
                      <Select.Option value={bin.id} key={bin.id}>
                        {bin.alamat_detail}
                      </Select.Option>
                    )) || []}
                  </Select>
                </Form.Item>
              )} */}
          </div>
        </Card>
      </Form>

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
                      onChange={(e) => {
                        // if (selectedAddress) {
                        //   return setSelectedAddress(null)
                        // }
                        return setSelectedAddress(record.id)
                      }}
                      checked={
                        selectedAddress == record.id || record.is_default > 0
                      }
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

      <Card title="Detail Product" className="mt-4">
        <ProductList
          data={productItems}
          products={products}
          taxs={taxs}
          discounts={discounts}
          onChange={handleChangeProductPrice}
          handleChange={handleChangeProductItem}
          handleClick={handleClickProductItem}
          loading={loading}
          summary={(pageData) => {
            if (productItems.length > 0) {
              return (
                <>
                  <Table.Summary.Row>
                    <Table.Summary.Cell colSpan={7} align="right">
                      Subtotal
                    </Table.Summary.Cell>
                    {productLoading ? (
                      <Skeleton.Input
                        active
                        size={"default"}
                        block={false}
                        style={{ width: 250 }}
                      />
                    ) : (
                      <Table.Summary.Cell align="right">
                        {formatNumber(detail?.subtotal, "Rp ")}
                      </Table.Summary.Cell>
                    )}
                    <Table.Summary.Cell />
                  </Table.Summary.Row>
                  <Table.Summary.Row>
                    <Table.Summary.Cell colSpan={7} align="right">
                      Tax
                    </Table.Summary.Cell>
                    {productLoading ? (
                      <Skeleton.Input
                        active
                        size={"default"}
                        block={false}
                        style={{ width: 250 }}
                      />
                    ) : (
                      <Table.Summary.Cell align="right">
                        {formatNumber(detail?.tax_amount, "Rp ")}
                      </Table.Summary.Cell>
                    )}
                    <Table.Summary.Cell />
                  </Table.Summary.Row>
                  <Table.Summary.Row>
                    <Table.Summary.Cell colSpan={7} align="right">
                      Discount
                    </Table.Summary.Cell>
                    {productLoading ? (
                      <Skeleton.Input
                        active
                        size={"default"}
                        block={false}
                        style={{ width: 250 }}
                      />
                    ) : (
                      <Table.Summary.Cell align="right">
                        {formatNumber(detail?.discount_amount, "Rp ")}
                      </Table.Summary.Cell>
                    )}
                    <Table.Summary.Cell />
                  </Table.Summary.Row>
                  <Table.Summary.Row>
                    <Table.Summary.Cell colSpan={7} align="right">
                      Total
                    </Table.Summary.Cell>
                    {productLoading ? (
                      <Skeleton.Input
                        active
                        size={"default"}
                        block={false}
                        style={{ width: 250 }}
                      />
                    ) : (
                      <Table.Summary.Cell align="right">
                        {formatNumber(detail?.amount, "Rp ")}
                      </Table.Summary.Cell>
                    )}

                    <Table.Summary.Cell />
                  </Table.Summary.Row>
                </>
              )
            }
          }}
        />
      </Card>

      {showBilling && (
        <div className="card mt-8">
          <div className="card-header flex justify-between items-center">
            <h1 className="header-title">Informasi Penagihan</h1>
            <ModalBilling
              detail={{ ...detail, uid_lead }}
              refetch={getOrderBilling}
              user={userData}
            />
          </div>
          <div className="card-body">
            <Table
              dataSource={billingData}
              columns={[...billingColumns]}
              loading={loading}
              pagination={false}
              rowKey="id"
              scroll={{ x: "max-content" }}
              tableLayout={"auto"}
            />
          </div>
        </div>
      )}

      {/* <div className="card mt-6 p-4 items-end">
        <button
          onClick={() => {
            if (productLoading) {
              toast.error("Please wait for the product to load")
              return
            }
            form.submit()
          }}
          className={`text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center`}
        >
          {productLoading && <LoadingOutlined />}{" "}
          <span className="ml-2">Save Order</span>
        </button>
      </div> */}
      <div className="float-right">
        <div className="  w-full mt-6 p-4 flex flex-row">
          {!detail && (
            <button
              onClick={() => {
                if (loadingSubmit) {
                  return null
                }
                setStatus(-1)
                setTimeout(() => {
                  console.log("status", status)
                  form.submit()
                }, 1000)
              }}
              className={`text-blue bg-white hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 border font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mr-2`}
            >
              {loadingSubmit ? (
                <LoadingOutlined />
              ) : (
                <span className="ml-2">Save Draft</span>
              )}
            </button>
          )}
          <button
            onClick={() => {
              if (loadingSubmit) {
                return null
              }
              if (detail?.amount < 1) {
                return message.error("Anda belum input harga")
              }
              setStatus(1)
              setTimeout(() => {
                form.submit()
              }, 1000)
            }}
            className={`text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center`}
          >
            {loadingSubmit ? (
              <LoadingOutlined />
            ) : (
              <span className="ml-2">Save Order</span>
            )}
          </button>
        </div>
      </div>
    </Layout>
  )
}

export default OrderInvoiceForm
