import { LoadingOutlined } from "@ant-design/icons"
import {
  Card,
  DatePicker,
  Form,
  Input,
  Select,
  Skeleton,
  Switch,
  Table,
  message,
} from "antd"
import axios from "axios"
import moment from "moment"
import React, { useEffect, useState } from "react"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import LoadingFallback from "../../components/LoadingFallback"
import ProductListInput from "../../components/ProductListInput"
import DebounceSelect from "../../components/atoms/DebounceSelect"
import Layout from "../../components/layout"
import {
  useChangeAddressMutation,
  useCreateSalesOrderMutation,
} from "../../configs/Redux/Services/salesOrderService"
import { formatNumber, getItem } from "../../helpers"
import FormAddressModal from "../Contact/Components/FormAddressModal"
import {
  handleSearchBin,
  handleSearchSales,
  searchBin,
  searchSales,
} from "./service"

const OrderKonsinyasiForm = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const role = getItem("role")
  const userData = getItem("user_data", true)
  const { uid_lead = null } = useParams() || {}

  const [productLoading, setProductLoading] = useState(false)
  const [loading, setLoading] = useState(false)
  const [loadingSoKonsinyasi, setLoadingSoKonsinyasi] = useState(false)
  const [loadingBrand, setLoadingBrand] = useState(false)
  const [loadingBilling, setLoadingBilling] = useState(false)
  const [loadingAddress, setLoadingAddress] = useState(false)

  const [detail, setDetail] = useState(null)
  const [warehouses, setWarehouses] = useState([])
  const [masterBin, setMasterBin] = useState([])
  const [termOfPayments, setTermOfPayments] = useState([])
  const [soKonsinyasi, setSoKonsinyasi] = useState([])
  const [soKonsinyasiDetail, setSoKonsinyasiDetail] = useState({})
  const [brands, setBrands] = useState([])
  const [products, setProducts] = useState([])
  const [taxs, setTaxs] = useState([])
  const [productItems, setProductItems] = useState([
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
  ])
  const [billingData, setBilingData] = useState([])
  const [contactList, setContactList] = useState([])
  const [showContact, setShowContact] = useState(false)
  const [salesList, setSalesList] = useState([])
  const [showBilling, setShowBilling] = useState(false)
  const [showBin, setShowBin] = useState(false)
  const [status, setStatus] = useState(0)
  const [userAddress, setUserAddress] = useState(null)
  const [selectedAddress, setSelectedAddress] = useState(null)
  const [detailKonsiyasi, setDetailKonsiyasi] = useState(null)
  const [loadingBinByContact, setLoadingBinByContact] = useState(false)
  const [orderType, setOrderType] = useState(null)
  const [seletedContact, setSeletedcontact] = useState(null)

  const [changeAddress, { isLoading: loadingChangeAddress }] =
    useChangeAddressMutation()

  const [createSalesOrder, { isLoading: loadingSubmit }] =
    useCreateSalesOrderMutation()

  const loadBrand = async () => {
    setLoadingBrand(true)
    await axios
      .get("/api/master/brand")
      .then((res) => {
        setBrands(res.data.data)
        setLoadingBrand(false)
      })
      .catch(() => setLoadingBrand(false))
  }

  const loadWarehouse = async () => {
    await axios.get("/api/master/warehouse").then((res) => {
      setWarehouses(res.data.data)
    })
  }

  const loadMasterBin = async () => {
    await axios.get("/api/master/bin").then((res) => {
      setMasterBin(res.data.data)
    })
  }

  const loadTop = async () => {
    await axios.get("/api/master/top").then((res) => {
      setTermOfPayments(res.data.data)
    })
  }

  const loadSoKonsinyasi = async () => {
    setLoading(true)
    const accountId = localStorage.getItem("account_id")
    await axios
      .get(`/api/master/so-konsinyasi?account_id=${accountId}`)
      .then((res) => {
        setSoKonsinyasi(res.data.data)
        setLoading(false)
      })
      .catch((err) => setLoading(false))
  }

  const loadSoKonsinyasiDetail = async (id = "") => {
    setLoadingSoKonsinyasi(true)
    await axios.get(`/api/master/so-konsinyasi/${id}`).then((res) => {
      const { data } = res.data
      const { payment_term, contact, sales, master_bin_id, product } = data
      setDetailKonsiyasi(data)
      loadUserAddress(contact)
      // loadMasterBin()
      if (data) {
        form.setFieldValue("payment_terms", payment_term)
        // form.setFieldValue("contact", contact)
        // form.setFieldValue("sales", sales)
        form.setFieldValue("master_bin_id", master_bin_id)

        if (data?.contact) {
          form.setFieldValue("contact", {
            label: data?.contact_name,
            value: data?.contact,
          })
        }

        if (data?.sales) {
          form.setFieldValue("sales", {
            label: data?.sales_name,
            value: data?.sales,
          })
        }
      }

      setSoKonsinyasiDetail(data)
      setLoadingSoKonsinyasi(false)

      const productIdList = product.map((value) => value.product_id)

      loadProducts(productIdList, master_bin_id)
    })
  }

  const loadTaxs = async () => {
    await axios.get("/api/master/taxs").then((res) => {
      setTaxs(res.data.data)
    })
  }

  // const loadProducts = async (idList, master_bin_id) => {
  //   setProductLoading(true)
  //   await axios
  //     .get("/api/master/products/sales-offline")
  //     .then((res) => {
  //       const { data } = res.data

  //       // only show product filter from so konsi product_id list
  //       const filteredData = data.filter((value) => {
  //         return inArray(value.product_id, idList)
  //       })

  //       const newData = filteredData.map((item) => {
  //         console.log(item, "item new data")

  //         const stock_bin = item?.stock_bins || []
  //         const stock_off_market =
  //           stock_bin.find((itemStock) => itemStock.id == master_bin_id)
  //             ?.stock || item?.stock_off_market

  //         console.log(stock_bin, "stock_bin")
  //         // console.log(stock_off_market, "stock_off_market")

  //         return {
  //           ...item,
  //           stock_off_market: stock_off_market || 0,
  //           final_stock: stock_off_market || 0,
  //         }
  //       })
  //       console.log(master_bin_id, "master_bin_id")
  //       console.log(newData, "newData")
  //       setProducts(newData)
  //       setProductLoading(false)
  //     })
  //     .catch(() => setProductLoading(false))
  //   await loadTaxs()
  // }

  const loadProducts = async (idList, master_bin_id) => {
    setProductLoading(true)
    await axios
      .get("/api/master/products/sales-offline")
      .then((res) => {
        const { data } = res.data

        // only show product filter from so konsi product_id list
        // const filteredData = data.filter((value) => {
        //   return inArray(value.product_id, idList);
        // });
        const newData = data.map((item) => {
          let stock_off_market = 0
          let location_id = master_bin_id
          const stocks = item?.stock_bins
          stock_off_market = stocks?.find(
            (itemStock) => itemStock.id == location_id
          )?.stock

          return {
            ...item,
            stock_off_market: stock_off_market || 0,
            final_stock: stock_off_market || 0,
          }
        })
        console.log(newData, "newData")
        setProducts(newData)
        setProductLoading(false)
      })
      .catch(() => setProductLoading(false))
  }

  const loadUserAddress = async (id) => {
    setLoadingAddress(true)
    await axios
      .get("/api/general/user-with-address/" + id)
      .then((res) => {
        setUserAddress(res.data.data)
        const { address } = res?.data?.data || {}
        setLoadingAddress(false)
        // if (address) {
        //   const selectedAddr = address.find((item) => item.is_default == 1)
        //   if (selectedAddr) {
        //     setSelectedAddress(selectedAddr.id)
        //   }
        // }
      })
      .catch(() => setLoadingAddress(false))
  }

  const getOrderBilling = async () => {
    setLoadingBilling(true)
    await axios
      .get(`/api/order-manual/billing/list/${uid_lead}`)
      .then((res) => {
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
          setLoadingBilling(false)
        }
      })
      .catch(() => setLoadingBilling(false))
  }

  const loadProductDetail = async (updateForm = true) => {
    setLoading(true)
    await axios
      .get(`/api/order-konsinyasi/${uid_lead}`)
      .then((res) => {
        const { data } = res.data
        setDetail(data)
        setLoading(false)

        if (updateForm) {
          setSeletedcontact({
            label: data?.contact_name,
            value: data?.contact,
          })
          const forms = {
            ...data,
            master_bin_id: {
              label: data?.master_bin_name || data?.bin_name,
              value: data?.master_bin_id,
            },
            sales: {
              label: data?.sales_name,
              value: data?.sales,
            },
            payment_terms: data?.payment_term?.id,
            expired_at: moment(data?.expired_at ?? new Date(), "YYYY-MM-DD"),
            created_by: data?.created_by_name,
            so_konsinyasi: data?.id_konsinyasi,
          }
          if (data?.order_type) {
            setOrderType(data?.order_type)
          }
          if (role === "sales") {
            forms.sales = {
              label: userData.name,
              value: userData.id,
            }
          }

          let newForm = form.getFieldsValue()
          // console.log(newForm, "newform")

          form.setFieldsValue({ ...newForm, ...forms }) // update to fix suspected form error

          if (forms.payment_terms) {
            setShowBilling(forms.payment_terms === 4 ? true : false)
            getOrderBilling()
          }

          if (data.master_bin_id) {
            loadProducts(null, data.master_bin_id)
            setShowBin(data.master_bin_id ? true : false)
            loadContactByBin(data?.master_bin_id)
            handleGetBin({ order_type: data?.order_type })
          }
          // console.log(products)
          // loadProducts(data?.warehouse_id)
          if (data?.contact) {
            setShowContact(data?.contact ? true : false)
            loadUserAddress(data?.contact)
          }
          if (data?.product_needs && data?.product_needs.length > 0) {
            setProductItems(
              data.product_needs.map((item, index) => {
                return {
                  id: item.id,
                  key: index,
                  product_id: item.product_id,
                  price: item.product?.price?.final_price,
                  price_satuan: item?.price_nego / item?.qty,
                  qty: item.qty,
                  tax_id: item.tax_id,
                  tax_amount: item.tax_amount,
                  tax_percentage: data?.tax_percentage || 0,
                  discount_percentage: item.discount_percentage,
                  discount: item.discount,
                  discount_amount: item.discount_amount,
                  subtotal: item.final_price,
                  price_nego: item.price_nego,
                  total: item.total,
                  margin_price: 0,
                  stock: 0,
                }
              })
            )
          }
        } else {
          if (seletedContact) {
            form.setFieldValue("contact", seletedContact?.value)
          }
        }
      })
      .catch((e) => setLoading(false))
  }

  const handleChangeAddress = async (address_id) => {
    await changeAddress({
      body: { uid_lead, address_id },
    }).then(({ error, data }) => {
      if (error) {
        return toast.error(error?.message)
      }
      toast.success("Alamat berhasil diubah")
      loadUserAddress(userAddress?.id || seletedContact?.value)
    })
  }

  const handleGetBin = async (params = null) => {
    await searchBin(null, params).then((results) => {
      const newResult = results.map((result) => {
        return { label: result.nama, value: result.id }
      })
      setMasterBin(newResult)
    })
  }

  const handleGetSales = async () => {
    await searchSales(null).then((results) => {
      const newResult = results.map((result) => {
        return { label: result.nama, value: result.id }
      })

      setSalesList(newResult)
    })
  }

  const fetchData = async () => {
    form.setFieldValue("created_by", userData?.name)
    form.setFieldValue("payment_terms", 4)
    form.setFieldValue("created_on", moment(new Date()).format("DD-MM-YYYY"))
    if (uid_lead) {
      await loadProductDetail()
    }

    await loadTaxs()
    // await loadMasterBin()
    // await loadSoKonsinyasi()
    await loadTop()

    await handleGetSales()
    await loadBrand()
    await loadWarehouse()
  }

  useEffect(() => {
    fetchData()
  }, [])

  const loadContactByBin = (id) => {
    setContactList([])
    setLoadingBinByContact(true)

    axios
      .get("/api/master/contact-by-bin/" + id)
      .then((res) => {
        const { data } = res?.data || {}

        if (data && data.length > 0) {
          const formattedContacts = data.map((item) => ({
            id: item?.user_id,
            name: item?.user?.name,
          }))

          setContactList(formattedContacts)

          // Cari kontak yang tersimpan sebelumnya dalam mode edit
          const currentContactId = form.getFieldValue("contact")
          const selectedContact = formattedContacts.find(
            (contact) => contact.id === currentContactId
          )

          // Atur nilai kontak jika ditemukan
          if (selectedContact) {
            form.setFieldValue("contact", selectedContact.id)
          }
        } else {
          form.setFieldValue("contact", null)
          setContactList([])
        }
      })
      .finally(() => {
        setLoadingBinByContact(false)
      })
  }

  const onFinish = (values) => {
    // validate product
    const hasProduct = productItems.every((item) => item.product_id)
    if (!hasProduct) {
      return toast.error("Product harus diisi", {
        position: toast.POSITION.TOP_RIGHT,
      })
    }

    // validate price
    const hasPriceNego = productItems.every((item) => item.price_nego)
    if (!hasPriceNego) {
      return toast.error("Price Nego harus diisi", {
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
      return toast.error("Minimal Pembalian adalah 1", {
        position: toast.POSITION.TOP_RIGHT,
      })
    }

    const billingStatus = showBilling ? 1 : 1
    const status_save = status < 0 ? status : billingStatus

    const body = {
      ...values,
      status: status_save,
      uid_lead,
      address_id: selectedAddress,
      contact: values.contact,
      master_bin_id: values.master_bin_id?.value,
      sales: values.sales.value,
      product_items: productItems,
      account_id: getItem("account_id"),
      type: "konsinyasi",
      order_number: detail?.order_number,
      invoice_number: detail?.invoice_number,
      transfer_number: detailKonsiyasi?.order_number,
      expired_at: values.expired_at.format("YYYY-MM-DD"),
    }

    createSalesOrder({ url: "/api/order-konsinyasi/form/save", body }).then(
      ({ error, data }) => {
        if (error) {
          return toast.error(error?.message)
        }

        if (data.status == "error") {
          return toast.error(data.message)
        }

        setProductItems([
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
        ])
        toast.success(data.message)
        return navigate("/order/order-konsinyasi")
      }
    )
  }

  if (loading) {
    return (
      <Layout title="Order Konsinyasi Form " href="/order/order-konsinyasi">
        <LoadingFallback />
      </Layout>
    )
  }

  return (
    <Layout
      title="Order Konsinyasi Form"
      href="/order/order-konsinyasi"
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
            {/* <Form.Item
              label="Pilih SO Konsinyasi"
              name="so_konsinyasi"
              rules={[
                {
                  required: true,
                  message: "Silahkan Masukkan SO!",
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
                  mode="multiple"
                  loading={loading}
                  showSearch
                  filterOption={(input, option) => {
                    return (option?.children ?? "")
                      .toLowerCase()
                      .includes(input.toLowerCase())
                  }}
                  placeholder="Pilih SO Konsinyasi"
                  onChange={(e) => {
                    let consignmentId = e
                    console.log(consignmentId, "what is this?")
                    setShowBilling(consignmentId === 4 ? true : false)
                    setShowBin(consignmentId === 3 ? true : false) // ini maksudnya apa?
                    loadMasterBin()
                    loadSoKonsinyasiDetail(consignmentId)
                    // loadProducts()
                  }}
                >
                  {soKonsinyasi.map((so) => (
                    <Select.Option value={so.id} key={so.id}>
                      {so.order_number}
                    </Select.Option>
                  ))}
                </Select>
              )}
            </Form.Item> */}
            <Form.Item
              label="Kategori Data"
              name="order_type"
              rules={[
                {
                  required: true,
                  message: "Silakan Masukkan Kategori Data",
                },
              ]}
            >
              <Select
                placeholder="Pilih Kategori Data"
                onChange={(value) => {
                  form.setFieldsValue({ contact: null, master_bin_id: null })
                  setOrderType(value)
                  handleGetBin({ order_type: value })
                }}
              >
                <Select.Option value="old">Data Lama</Select.Option>
                <Select.Option value="new">Data Baru</Select.Option>
              </Select>
            </Form.Item>

            <Form.Item
              label="Destinasi BIN"
              name="master_bin_id"
              rules={[
                {
                  required: true,
                  message: "Silahkan Masukkan Destinasi BIN",
                },
              ]}
            >
              {loadingSoKonsinyasi ? (
                <Skeleton.Input
                  active
                  size={"default"}
                  block={false}
                  style={{ width: 450 }}
                />
              ) : (
                <DebounceSelect
                  showSearch
                  placeholder="Pilih Destinasi BIN"
                  fetchOptions={(e) =>
                    handleSearchBin(e, { order_type: orderType })
                  }
                  filterOption={false}
                  disabled={!orderType}
                  defaultOptions={masterBin}
                  className="w-full"
                  onChange={(e) => {
                    form.setFieldsValue({ contact: null })
                    loadContactByBin(e.value)
                    loadProducts(null, e.value)
                  }}
                />
              )}
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
              {loadingSoKonsinyasi ? (
                <Skeleton.Input
                  active
                  size={"default"}
                  block={false}
                  style={{ width: 450 }}
                />
              ) : (
                <Select
                  showSearch
                  filterOption={(input, option) => {
                    return (option?.children ?? "")
                      .toLowerCase()
                      .includes(input.toLowerCase())
                  }}
                  placeholder="Pilih Contact"
                  loading={loadingBrand}
                  onChange={(e) => {
                    const contact = contactList?.find((item) => item.id == e)
                    if (contact) {
                      setSeletedcontact({ value: e, label: contact?.name })
                    }
                    loadUserAddress(e)
                    setShowContact(true)
                  }}
                >
                  {contactList.map((item) => (
                    <Select.Option value={item.id} key={item.id}>
                      {item.name}
                    </Select.Option>
                  ))}
                </Select>
              )}
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
              {loadingSoKonsinyasi ? (
                <Skeleton.Input
                  active
                  size={"default"}
                  block={false}
                  style={{ width: 450 }}
                />
              ) : (
                <Select
                  disabled
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
              )}
            </Form.Item>

            <Form.Item
              label="Sales Person"
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

            {/* <Form.Item
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
                  loadProducts(e)
                  // setProductItems(pro)
                }}
              >
                {warehouses.map((warehouse) => (
                  <Select.Option value={warehouse.id} key={warehouse.id}>
                    {warehouse.name}
                  </Select.Option>
                ))}
              </Select>
            </Form.Item> */}

            <Form.Item label="Nomor Preferences" name="preference_number">
              <Input placeholder="Ketik Nomor Preferences" />
            </Form.Item>

            <Form.Item label="Customer Need" name="customer_need">
              <Input placeholder="Ketik Customer Need" />
            </Form.Item>

            <Form.Item label="Created by" name="created_by">
              <Input disabled />
            </Form.Item>

            <Form.Item label="Created On" name="created_on">
              <Input disabled />
            </Form.Item>

            <Form.Item label="Expired SO" name="expired_at">
              <DatePicker className="w-full" format={"DD-MM-YYYY"} />
            </Form.Item>

            <Form.Item
              className={"col-span-2"}
              label="Notes"
              name="notes"
              requiredMark="optional"
            >
              <Input.TextArea placeholder="Silahkan input catatan.." />
            </Form.Item>
          </div>
        </Card>
      </Form>

      {seletedContact && (
        <Card
          title="Address Information"
          className="mt-4"
          extra={
            <FormAddressModal
              initialValues={{
                user_id: userAddress?.id || seletedContact?.value,
                nama: userAddress?.name || seletedContact?.label,
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
                        setSelectedAddress(record.id)
                        return handleChangeAddress(record.id)
                      }}
                      checked={selectedAddress == record.id}
                    />
                  )
                },
              },
            ]}
            key={"id"}
            pagination={false}
            loading={loadingAddress}
          />
        </Card>
      )}

      <Card title="Informasi Produk" className="mt-4">
        <ProductListInput
          products={products}
          loading={productLoading || loadingSoKonsinyasi}
          onChange={(items) => setProductItems(items)}
          taxs={taxs}
          initialValues={productItems}
          isEdit={uid_lead ? true : false}
          typeProduct="order"
          // orderType={orderType}
        />
      </Card>

      {/* {showBilling && (
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
              loading={loadingBilling}
              pagination={false}
              rowKey="id"
              scroll={{ x: "max-content" }}
              tableLayout={"auto"}
            />
          </div>
        </div>
      )} */}

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

export default OrderKonsinyasiForm
