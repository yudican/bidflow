import {
  CloseCircleFilled,
  DownOutlined,
  EditOutlined,
  InfoCircleOutlined,
  MinusOutlined,
  PlusOutlined,
  RightOutlined,
  SearchOutlined,
} from "@ant-design/icons"
import {
  Button,
  Card,
  Collapse,
  Divider,
  Empty,
  Form,
  Input,
  Modal,
  Select,
  Skeleton,
  Space,
  Spin,
  Tooltip,
} from "antd"
import { useForm } from "antd/lib/form/Form"
import TextArea from "antd/lib/input/TextArea"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { toast } from "react-toastify"
import DebounceSelect from "../../../../components/atoms/DebounceSelect"
import {
  formatNumber,
  getItem,
  inArray,
  validateEmail,
  validatePhoneNumber,
} from "../../../../helpers"
import { searchKecamatan } from "../service"

const { Panel } = Collapse

const AddTransaction = ({ refetch }) => {
  const [form] = useForm()
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [isSearch, setIsSearch] = useState(false)
  const [search, setSearch] = useState("")
  const [selectedProduct, setSelectedProduct] = useState(null)
  const [products, setProducts] = useState([])
  const [selectedKecamatan, setSelectedKecamatan] = useState(null)
  const [user, setUser] = useState(null)
  const [adressUser, setAdressUser] = useState([])
  const [selectedKecamatanUser, setSelectedKecamatanUser] = useState(null)
  const [note, setNote] = useState("")
  const [alamatDetail, setAlamatDetail] = useState("")
  const [newAddress, setNewAddress] = useState(false)
  const [shippingList, setShippingList] = useState([])
  const [kecamatans, setKecamatans] = useState([])
  const [paymentMethodList, setPaymentMethodList] = useState([])
  const [dropdownOpen, setDropdownOpen] = useState(false)
  const [discounts, setDiscounts] = useState([])
  const [paymentMethodColapse, setPaymentMethodColapse] = useState({
    item_0: true,
    item_1: true,
  })

  const [isModalShippingMethodOpen, setIsModalShippingMethodOpen] =
    useState(false)
  const [isModalPaymentMethodOpen, setIsModalPaymentMethodOpen] =
    useState(false)

  const [loadingUserData, setLoadingUserData] = useState(false)

  const [selectedPayment, setselectedPayment] = useState(null)
  const [selectedShippingMethod, setSelectedShippingMethod] = useState(null)
  const [activeKey, setActiveKey] = useState(0)

  const totalQty = products?.reduce(
    (prev, curr) => parseInt(prev) + parseInt(curr.qty),
    0
  )

  const weight = products?.reduce(
    (prev, curr) => parseInt(prev) + parseInt(curr.weight * curr.qty),
    0
  )
  const weightSum = weight >= 1 ? Math.round(weight / 1000) : 1
  const totalWeight = isNaN(weightSum) ? 1 : weightSum >= 1 ? weightSum : 1
  const totalPrice = products
    .reduce((acc, curr) => {
      if (curr?.diskon > 0) {
        return acc + parseInt(curr.qty) * curr.price?.final_price - curr?.diskon
      }
      return acc + parseInt(curr.qty) * curr.price?.final_price
    }, 0)
    ?.toFixed(0)
  const totalDiscount = products
    .reduce(
      (acc, curr) =>
        acc +
        parseInt(curr.qty) * curr.price?.final_price * (curr.percent || 0),
      0
    )
    ?.toFixed(0)

  const filteredProducts =
    products.filter(
      (value) =>
        value.name.toLowerCase().includes(search.toLocaleLowerCase()) ||
        value.sku.toLowerCase().includes(search.toLocaleLowerCase())
    ) || products

  const loadProducts = () => {
    axios.get("/api/master/products/telmark").then((res) => {
      if (res) {
        let data = res.data.data

        const withQty = data.map((value) => {
          const warehouse = value.stock_warehouse?.find((row) => row.id == 3)
          const stock_off_market = warehouse ? warehouse.stock : 0
          return {
            id: value.id,
            product_id: value.product_id,
            image_url: value.image_url,
            name: value.name,
            sku: value.sku,
            stock_off_market,
            price: value.price_data,
            weight: value.weight,
            qty: 0,
          }
        })
        withQty.sort((a, b) => b.stock_off_market - a.stock_off_market)
        setProducts(withQty)
      }
    })
  }

  const handleSearchAddress = async (e) => {
    return searchKecamatan(e).then((results) => {
      const newResult = results.map((result) => {
        return result
      })
      setKecamatans(results)
      return newResult
    })
  }

  const handleGetAddress = async () => {
    await searchKecamatan("bogor").then((results) => {
      const newResult = results.map((result) => {
        return result
      })
      setKecamatans(newResult)
    })
  }

  const handleGetDiscount = () => {
    return axios
      .get("/api/master/discounts")
      .then((res) => {
        const { data } = res.data
        setDiscounts(
          data
            ?.filter((item) => inArray("telmark", item.sales_channels))
            .map((row) => {
              const percentage = row?.percentage > 0 ? row?.percentage / 100 : 0
              return {
                ...row,
                percentage,
              }
            })
        )
      })
      .catch((e) => {
        console.log(e, "error get payment method")
      })
  }

  const handleGetPaymentMethod = () => {
    return axios
      .get("https://testingapi.daftar-agen.com/api/payment-method", {
        headers: {
          Authorization: "Bearer 4a6a6c7f2cdd12a826e2f15675a6c6ac",
        },
      })
      .then((res) => {
        const { data } = res.data
        console.log(data, "data get payment method")
        setPaymentMethodList(data)
      })
      .catch((e) => {
        console.log(e, "error get payment method")
      })
  }

  const [loadingShipping, setLoadingShipping] = useState(false)
  const [loadingVoucher, setLoadingVoucher] = useState(false)
  const [voucher, setVoucher] = useState(null)
  const handleGetShipping = (body) => {
    setLoadingShipping(true)
    axios
      .post(
        "https://testingapi.daftar-agen.com/api/shipping/info",
        { ...body, sales_channel: "telmark" },
        {
          headers: {
            Authorization: "Bearer 4a6a6c7f2cdd12a826e2f15675a6c6ac",
          },
        }
      )
      .then((res) => {
        const { data } = res.data
        setShippingList(data)
        setLoadingShipping(false)
      })
      .catch((e) => {
        setLoadingShipping(false)
      })
  }

  const applyVoucher = (body) => {
    setLoadingVoucher(true)
    axios
      .post("/api/transaction/apply-voucher", body)
      .then((res) => {
        const { message } = res.data
        form.resetFields(["voucher_code"])
        form.setFieldsValue({ voucher_code: body?.voucher_code })
        setLoadingVoucher(false)
        setVoucher(res.data)
        toast.success(message)
      })
      .catch((e) => {
        const { message } = e.response.data
        setLoadingVoucher(false)

        return form.setFields([
          {
            name: "voucher_code",
            errors: [message],
          },
        ])
      })
  }

  useEffect(() => {
    loadProducts()
    handleGetAddress()
    handleGetPaymentMethod()
    handleGetDiscount()
  }, [])

  const handleSearchUser = (phone) => {
    setLoadingUserData(true)
    return axios
      .post("/api/master/search/user", { phone })
      .then((res) => {
        setLoadingUserData(false)
        const { data } = res.data
        if (data) {
          setUser({ ...data, user_id: data.id })

          const newAdress = data.address.map((value) => {
            return {
              value: value.id,
              label: value.alamat_detail,
              alamat: value.alamat,
              kodepos: value.kodepos,
              telepon: value.telepon,
              is_default: value.is_default,
              kec_id: value.kecamatan_id,
            }
          })

          const addressDetail = data.address.map((value) => {
            return {
              value: value.alamat,
              label: value.alamat_detail,
              telepon: value.telepon,
              is_default: value.is_default,
            }
          })
          setAdressUser(newAdress)
          const kecamatan =
            (newAdress && newAdress.find((item) => item.is_default == 1)) ||
            newAdress[0]
          const alamatDetail =
            (addressDetail &&
              addressDetail.filter((item) => item.is_default == 1)) ||
            addressDetail[0]
          const alamat =
            alamatDetail && alamatDetail.length > 0 && alamatDetail[0].value
          const alamat_detail =
            alamatDetail && alamatDetail.length > 0 && alamatDetail[0].label
          const telepon =
            alamatDetail && alamatDetail.length > 0 && alamatDetail[0].telepon

          form.setFieldsValue({
            ...data,
            user_id: data.id,
            // kecamatan_id: kecamatan,
            address_id: kecamatan?.value,
            alamat: alamat,
            alamat_detail: alamat_detail,
            telepon,
          })

          setSelectedKecamatan(alamat_detail)
          setSelectedKecamatanUser(kecamatan)
          if (kecamatan.kodepos > 0) {
            handleGetShipping({
              kodepos_origin: `14470`, // static form our warehouses
              kodepos: `${kecamatan?.kodepos}`,
              weight: totalWeight,
            })
          }
        } else {
          setUser(null)
          setAdressUser([])
          setSelectedKecamatan(null)
          setSelectedKecamatanUser(null)
          form.setFieldsValue({
            name: null,
            email: null,
            user_id: null,
            address_id: null,
            kecamatan_id: null,
            kodepos: null,
            alamat: null,
            alamat_detail: null,
          })
        }
      })
      .catch((e) => {
        setLoadingUserData(false)
        setUser(null)
        setAdressUser([])
        form.setFieldsValue({
          name: null,
          email: null,
          user_id: null,
          address_id: null,
          kecamatan_id: null,
          kodepos: null,
          alamat: null,
          alamat_detail: null,
        })
      })
  }

  const handleResetQtyProducts = () => {
    let newProduct = [...products].map((product) => {
      return { ...product, qty: 0 }
    })
    setProducts(newProduct)
  }

  const handleSubmit = (values) => {
    if (values.kodepos < 1) {
      return form.setFields([
        {
          name: "kodepos",
          errors: ["Kodepos Tidak Boleh Kosong"],
        },
      ])
    }
    if (!selectedPayment) {
      return toast.error("Metode Pembayaran belum dipilih")
    }

    if (!selectedShippingMethod) {
      return toast.error("Metode Pengiriman belum dipilih")
    }

    const voucher_diskon = voucher?.amount_discount || 0
    const data = {
      ...values,
      total_harga: totalPrice,
      company_id: getItem("account_id"),
      kecamatan_id: values?.kecamatan_id?.value,
      kecamatan: selectedKecamatanUser,
      kodepos: values.kodepos || selectedKecamatanUser?.kodepos,
      address_id: values.address_id || "new",
      products: products.filter((product) => product.qty > 0),
      // voucher inclues in ...values
      payment_method_id: selectedPayment?.id,
      shipping: selectedShippingMethod,
      voucher_id: voucher?.voucher_id,
      diskon: parseInt(totalDiscount),
      diskon_voucher: parseInt(voucher_diskon),
      weight: totalWeight,
    }
    // createTransaction(data)
    // console.log(data, "data")
    // return console.log(selectedKecamatanUser, "selectedKecamatanUser")
    axios
      .post("/api/transaction/new-order", data)
      .then((res) => {
        setIsModalOpen(false)
        handleResetQtyProducts()
        toast.success("Transaksi berhasil ditambahkan")
        form.resetFields()
        setActiveKey(0)
        setSelectedShippingMethod(null)
        setselectedPayment(null)
        setNewAddress(false)
        refetch()
      })
      .catch((e) => {
        console.log(e)
        setIsModalOpen(false)
        handleResetQtyProducts()
        form.resetFields()
        toast.error("Transaksi gagal ditambahkan")
      })
  }

  const ShippingOption = ({ item, selected }) => {
    return (
      <button
        onClick={(e) => {
          e.preventDefault()
          setSelectedShippingMethod(item)
          setIsModalShippingMethodOpen(false)
          form.setFieldValue("shipping_method_id", item.shipping_type_name)
        }}
        className="flex w-full cursor-pointer items-center justify-between ease-in-out duration-300 hover:bg-gray-400/10 accordion-body py-4 px-5 border-b-[1px]"
      >
        <div>
          <div className="flex items-center w-full">
            <img
              className="aspect-auto w-14 object-contain h-8 mr-4"
              src={item?.shipping_logo}
              alt="shipping logo"
            />

            <label className="form-check-label inline-block text-xs text-black">
              {item?.shipping_type_name}
            </label>
          </div>
          <small className="text-gray pt-2 mb-0">
            (Estimasi barang sampai : {item.shipping_duration})
          </small>
        </div>
        <div>
          <small className="text-gray pt-2 mb-0 font-medium">
            Rp. {formatNumber(item.shipping_price)}
          </small>
        </div>
      </button>
    )
  }
  const listType = ["regular", "same_day", "express", "instant"]

  const BankOption = ({ item, selected }) => {
    return (
      <button
        onClick={(e) => {
          e.preventDefault()
          setselectedPayment(item)
          setIsModalPaymentMethodOpen(false)
          form.setFieldValue("payment_method_id", item.bank_name)
        }}
        className="flex w-full items-center justify-between  accordion-body py-4 hover:bg-gray-400/10 ease-in-out duration-300 px-5 border-b-[1px] h-[3.15rem]"
      >
        <div className="flex items-center w-full">
          <img
            className="aspect-auto w-14 object-contain h-8 mr-4"
            src={item?.bank_logo}
            alt="bank logo"
          />

          <label className="form-check-label inline-block text-xs text-black">
            {item?.bank_name}
          </label>
        </div>
      </button>
    )
  }

  return (
    <div>
      <button
        onClick={() => {
          setIsModalOpen(true)
        }}
        className="w-40 text-white bg-[#008BE1] hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
      >
        <PlusOutlined />
        <span className="ml-2">Tambah Data</span>
      </button>

      <Modal
        title="Tambah Data Transaksi"
        open={isModalOpen}
        onCancel={() => {
          setIsModalOpen(false)
          handleResetQtyProducts()
        }}
        width={"92%"}
        footer={[
          <div key={"search"} className="flex justify-between my-2">
            <div className="flex-1"></div>
            <div className="flex-1">
              <Button
                key="link"
                // href="https://google.com"
                type="primary"
                onClick={() => {
                  form.submit()
                  // setIsModalOpen(false)
                }}
                disabled={
                  products.every((product) => product.qty < 1) ||
                  !selectedPayment ||
                  !selectedShippingMethod
                }
                // loading={loading}
                // onClick={handleOk}
              >
                Proses Transaksi
              </Button>
            </div>
          </div>,
        ]}
      >
        <div className="grid lg:grid-cols-2 gap-4">
          <div>
            <div key="qty" className="flex justify-between">
              <div className="flex">
                <p className="font-semibold text-green-600 mr-16">
                  Qty: {isNaN(totalQty) ? 0 : parseInt(totalQty)}
                </p>

                <p className="font-semibold text-red-600 mr-16">
                  Total : {`Rp ${formatNumber(totalPrice)}`}
                </p>
                <p className="font-semibold text-red-600">
                  Diskon : {`Rp ${formatNumber(totalDiscount)}`}
                </p>
              </div>
            </div>
            <Input
              placeholder="Cari data produk disini.."
              size={"middle"}
              className="rounded"
              // onPressEnter={() => handleChangeSearch()}
              suffix={
                isSearch ? (
                  <CloseCircleFilled
                    onClick={() => {
                      // loadData(url)
                      // setSearch(null)
                      // setIsSearch(false)
                    }}
                  />
                ) : (
                  <SearchOutlined
                  // onClick={() => handleChangeSearch()}
                  />
                )
              }
              value={search}
              onChange={(e) => setSearch(e.target.value)}
            />
            <div className="h-[60vh] lg:h-[125vh] overflow-y-auto">
              {filteredProducts.map((product, index) => (
                <div
                  key={product.id}
                  className={`
                    mb-4 shadow-none rounded-md p-2 cursor-pointer bg-white
                    ${
                      selectedProduct == product.id
                        ? "border-[1px] border-blue-400 drop-shadow-md ring-blue-500"
                        : "border border-gray-400"
                    }
                  `}
                  onClick={() => {
                    // if (product.stock_off_market > 0) {
                    return setSelectedProduct(product.id)
                    // }
                  }}
                  // disabled={product.stock === 0}
                >
                  <div className="flex max-w-[800px] justify-between items-center">
                    <div className="pb-2">
                      <div className="flex items-center">
                        <img
                          src={product.image_url}
                          alt="product_photo"
                          className="mr-3 w-14 h-14 rounded-md border"
                        />
                        <div>
                          <Tooltip title={product.name}>
                            <div className="block text-md line-clamp-1 font-medium max-w-xs">
                              {product.name}{" "}
                            </div>
                          </Tooltip>
                          {/* <br /> */}
                          <div className="block text-gray-400 mt-2">
                            SKU : {product.sku}{" "}
                            <s>Rp {formatNumber(product.price?.basic_price)}</s>{" "}
                            <span className="text-gray-700">
                              Rp {formatNumber(product.price?.final_price)} /Qty
                            </span>
                          </div>
                        </div>
                      </div>
                      <Select
                        allowClear
                        className="w-full mt-4"
                        placeholder="Pilih Discount"
                        value={product?.discount_id}
                        onChange={(value) => {
                          let newProduct = [...filteredProducts]
                          const discount = discounts.find(
                            (item) => item.id == value
                          )
                          const price =
                            newProduct[index].price?.final_price || 0
                          const qty = newProduct[index].qty || 0
                          const percentage = discount?.percentage || 0
                          newProduct[index] = {
                            ...newProduct[index],
                            discount_id: value,
                            diskon: price * qty * percentage,
                            percent: percentage,
                          }
                          setProducts(newProduct)
                        }}
                      >
                        {discounts.map((item) => (
                          <Select.Option value={item?.id}>
                            {item?.title}
                          </Select.Option>
                        ))}
                      </Select>
                    </div>

                    <div className="flex flex-col justify-between items-end">
                      <div>
                        <Input.Group compact size="small">
                          <Button
                            size="small"
                            icon={<MinusOutlined />}
                            onClick={() => {
                              // if (product.stock_off_market > 0) {
                              let newProduct = [...filteredProducts]
                              const nextQty = newProduct[index]["qty"] - 1
                              if (nextQty >= 0) {
                                newProduct[index] = {
                                  ...newProduct[index],
                                  qty: nextQty,
                                }
                                setProducts(newProduct)
                              }
                              // }
                            }}
                          />
                          <Input
                            // className="text-center "
                            style={{
                              width: "50px",
                              textAlign: "center",
                            }}
                            value={
                              isNaN(product.qty) ? 0 : parseInt(product.qty)
                            }
                            // disabled={product.stock_off_market < 1}
                            onChange={(e) => {
                              const { value } = e.target
                              // check if value is 0-9
                              if (value === "" || !value) {
                                let newProduct = [...filteredProducts]
                                newProduct[index] = {
                                  ...newProduct[index],
                                  qty: 0,
                                }
                              }

                              // if (parseInt(value) > product.stock_off_market) {
                              //   return null
                              // }

                              if (value.match(/^[0-9]*$/)) {
                                // if (product.stock_off_market > 0) {
                                let newProduct = [...filteredProducts]
                                newProduct[index] = {
                                  ...newProduct[index],
                                  qty: parseInt(value),
                                }
                                setProducts(newProduct)
                                // }
                              }
                            }}
                          />
                          <Button
                            size="small"
                            icon={<PlusOutlined />}
                            onClick={() => {
                              // if (product.stock_off_market > 0) {
                              let newProduct = [...filteredProducts]
                              const nextQty = newProduct[index]["qty"] + 1
                              // if (nextQty <= product.stock_off_market) {
                              newProduct[index] = {
                                ...newProduct[index],
                                qty: nextQty,
                              }
                              setProducts(newProduct)
                              // }
                              // }
                            }}
                          />
                        </Input.Group>
                      </div>

                      <div className="mt-2">
                        <span className="text-red-500">
                          Sisa Stock: {product.stock_off_market}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>

          <div className="lg:border-l pl-3">
            <div className="text-center">
              <h2 className="font-semibold">Form Tambah Data Transaksi</h2>
              <p className="text-gray-400 line-clamp-2">
                Silakan lengkapi formulir di bawah ini untuk melanjutkan proses.
                Sistem akan melakukan pengecekan menggunakan nomor handphone
                yang terdaftar.
              </p>
            </div>

            <Form
              form={form}
              initialValues={{
                remember: true,
              }}
              autoComplete="off"
              layout="vertical"
              onFinishFailed={(e) => console.log(e)}
              onFinish={(values) => handleSubmit(values)}
            >
              <Form.Item name="user_id" className="hidden">
                <Input type="hidden" />
              </Form.Item>
              <Form.Item
                label="Masukkan nomor handphone Customer"
                name="phone"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Telepon!",
                  },
                  {
                    validator: validatePhoneNumber,
                  },
                ]}
              >
                <Input
                  placeholder="Ketik No Telepon"
                  onBlur={(e) => {
                    const phone = e.target.value
                    handleSearchUser(phone)
                  }}
                />
              </Form.Item>
              <Form.Item
                label="Nama Lengkap Penerima"
                name="name"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Nama Lengkap Penerima!",
                  },
                ]}
              >
                {loadingUserData ? (
                  <Skeleton.Input active size={"default"} block />
                ) : (
                  <Input placeholder="Ketik nama lengkap Customer.." />
                )}
              </Form.Item>
              <Form.Item
                label="Alamat email"
                name="email"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Email!",
                  },
                  {
                    validator: validateEmail,
                  },
                ]}
              >
                {loadingUserData ? (
                  <Skeleton.Input active size={"default"} block />
                ) : (
                  <Input placeholder="Ketik Email" />
                )}
              </Form.Item>
              {user && adressUser?.length > 0 && (
                <Form.Item
                  label="Alamat"
                  name="address_id"
                  rules={[
                    {
                      required: true,
                      message: "Silakan pilih Alamat!",
                    },
                  ]}
                >
                  {loadingUserData ? (
                    <Skeleton.Input active size={"default"} block />
                  ) : (
                    <Select
                      placeholder="Cari Alamat"
                      className="w-full"
                      open={dropdownOpen}
                      onDropdownVisibleChange={(open) => setDropdownOpen(open)}
                      onChange={(value) => {
                        if (value === "new") {
                          form.setFieldsValue({
                            alamat: null,
                            alamat_detail: null,
                            kecamatan_id: null,
                          })

                          return setNewAddress(true)
                        }
                        const address = adressUser.find(
                          (item) => item.value == value
                        )

                        form.setFieldsValue({
                          address_id: value,
                          alamat: address?.alamat,
                          alamat_detail: address?.label,
                          shipping_method_id: null,
                          kodepos: address?.kodepos,
                        })
                        setSelectedKecamatan(address.label)
                        setAlamatDetail(address?.alamat)
                        setSelectedShippingMethod(null)
                        setNewAddress(false)
                        setSelectedKecamatanUser({
                          ...value,
                          kec_id: address.kec_id,
                          label: address?.label,
                          kodepos: address?.kodepos || 0,
                        })
                        handleGetShipping({
                          kodepos_origin: `14470`, // static form our warehouses
                          kodepos: `${address?.kodepos}`,
                          weight: totalWeight,
                        })
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
                            <div className="py-1 flex w-full items-center justify-center text-center">
                              <Button
                                className="mx-auto"
                                type="text"
                                onClick={() => {
                                  form.setFieldsValue({
                                    alamat: null,
                                    alamat_detail: null,
                                    kecamatan_id: null,
                                    address_id: "new",
                                    kodepos: null,
                                  })
                                  setAlamatDetail(null)
                                  setDropdownOpen(false)
                                  setSelectedKecamatan(null)
                                  return setNewAddress(true)
                                }}
                              >
                                <strong className="text-blue-500">
                                  + Tambah Alamat Baru
                                </strong>
                              </Button>
                            </div>
                          </Space>
                        </>
                      )}
                      options={[
                        {
                          label: <span>Daftar Alamat Sebelumnya</span>,
                          title: "Daftar Alamat Sebelumnya",
                          options: adressUser,
                        },
                      ]}
                    />
                  )}
                </Form.Item>
              )}

              {newAddress || !user ? (
                <Form.Item
                  label="Kecamatan"
                  name="kecamatan_id"
                  rules={[
                    {
                      required: true,
                      message: "Silakan pilih Kecamatan!",
                    },
                  ]}
                >
                  {loadingUserData ? (
                    <Skeleton.Input active size={"default"} block />
                  ) : (
                    <DebounceSelect
                      showSearch
                      placeholder="Cari Kecamatan"
                      fetchOptions={handleSearchAddress}
                      filterOption={false}
                      className="w-full"
                      onChange={(value) => {
                        const kecamatan = kecamatans.find(
                          (item) => item.value == value.value
                        )
                        setSelectedShippingMethod(null)
                        form.setFieldValue("shipping_method_id", null)
                        form.setFieldValue("kodepos", kecamatan?.kodepos)
                        setSelectedKecamatan(value.label)
                        setSelectedKecamatanUser({
                          ...value,
                          ...kecamatan,
                          kodepos: kecamatan?.kodepos || 0,
                        })

                        handleGetShipping({
                          kodepos_origin: `14470`, // static form our warehouses
                          kodepos: `${kecamatan?.kodepos}`,
                          weight: totalWeight,
                        })
                      }}
                      defaultOptions={kecamatans}
                    />
                  )}
                </Form.Item>
              ) : null}

              <Form.Item
                label="Kode Pos"
                name="kodepos"
                tooltip={
                  "Mohon untuk konfirmasikan kodepos ke customer dan input ulang kodepos"
                }
                rules={[
                  {
                    required: true,
                    message: "Silahkan masukkan Kode Pos!",
                  },
                  {
                    validator: (_, value) => {
                      if (value) {
                        if (value < 1) {
                          return Promise.reject(
                            new Error(
                              "Kode Pos tidak boleh kurang dari 1 dan harus berisi 5 angka"
                            )
                          )
                        }
                        if (value.toString().length > 5) {
                          return Promise.reject(
                            new Error("Kode Pos harus berisi 5 angka")
                          )
                        }
                      }
                      return Promise.resolve()
                    },
                  },
                ]}
              >
                {loadingUserData ? (
                  <Skeleton.Input active size={"default"} block />
                ) : (
                  <Input
                    placeholder="Ketik Kode Pos"
                    onBlur={(e) => {
                      setSelectedShippingMethod(null)
                      form.setFieldValue("shipping_method_id", null)
                      handleGetShipping({
                        kodepos_origin: `14470`, // static form our warehouses
                        kodepos: `${e.target.value}`,
                        weight: totalWeight,
                      })
                    }}
                  />
                )}
              </Form.Item>

              {newAddress || !user ? (
                <Form.Item
                  label="Alamat Lengkap"
                  name="alamat_detail"
                  rules={[
                    {
                      required: true,
                      message: "Silakan masukkan alamat lengkap!",
                    },
                  ]}
                >
                  {loadingUserData ? (
                    <Skeleton.Input active size={"default"} block />
                  ) : (
                    <Input
                      placeholder="Masukkan alamat lengkap"
                      onChange={(e) => {
                        setAlamatDetail(e.target.value)
                      }}
                    />
                  )}
                </Form.Item>
              ) : null}
              <Form.Item name="alamat" hidden={true}>
                <Input placeholder="Masukkan alamat lengkap " />
              </Form.Item>

              <Form.Item
                shouldUpdate={(prevValues, curValues) =>
                  prevValues !== curValues
                }
                label="Metode Pengiriman"
                name="shipping_method_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan shipping method!",
                  },
                ]}
              >
                <Input
                  disabled
                  prefix={
                    <Button
                      style={{ marginRight: 8 }}
                      icon={
                        selectedShippingMethod?.shipping_type_name ? (
                          <EditOutlined />
                        ) : (
                          <PlusOutlined />
                        )
                      }
                      onClick={(e) => {
                        e.preventDefault()
                        setIsModalShippingMethodOpen(true)
                      }}
                    ></Button>
                  }
                  // placeholder="Pilih Metode Pengiriman"
                  placeholder={
                    selectedShippingMethod?.shipping_type_name ||
                    "Pilih Metode Pengiriman"
                  }
                />
              </Form.Item>

              <Form.Item
                shouldUpdate={(prevValues, curValues) =>
                  prevValues !== curValues
                }
                label="Metode Pembayaran"
                name="payment_method_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan payment method!",
                  },
                ]}
              >
                <Input
                  disabled
                  prefix={
                    <Button
                      style={{ marginRight: 8 }}
                      icon={
                        selectedPayment?.bank_name ? (
                          <EditOutlined />
                        ) : (
                          <PlusOutlined />
                        )
                      }
                      onClick={(e) => {
                        e.preventDefault()
                        setIsModalPaymentMethodOpen(true)
                      }}
                    ></Button>
                  }
                  // placeholder="Pilih Metode Pembayaran"
                  placeholder={
                    selectedPayment?.bank_name || "Pilih Metode Pembayaran"
                  }
                />
              </Form.Item>

              {/* <Form.Item
                label={
                  <div className="flex items-center">
                    Kode Voucher
                    <Tooltip title={"Kode voucher diberikan oleh superadmin"}>
                      <InfoCircleOutlined className="ml-1" />
                    </Tooltip>
                  </div>
                }
                name="voucher_code"
              >
                <Input
                  placeholder="Masukkan kode voucher"
                  suffix={
                    <CloseCircleFilled
                      onClick={() => {
                        form.resetFields(["voucher_code"])
                      }}
                    />
                  }
                  onBlur={(e) => {
                    const { value } = e.target
                    if (value) {
                      applyVoucher({
                        voucher_code: value,
                        nominal: totalPrice,
                        user_id: user?.id,
                      })
                    }
                  }}
                />
              </Form.Item> */}

              <Form.Item label="Catatan" name="note">
                <TextArea
                  placeholder="Catatan"
                  onChange={(e) => {
                    setNote(e.target.value)
                  }}
                />
              </Form.Item>

              <div className="mt-4 p-2 rounded-md border-2 border-[#008BE1] bg-[#D8F0FF] text-[#004AA6]">
                <p>Detail Customer</p>
                <table width={"100%"}>
                  <tbody>
                    <tr>
                      <td width={"20%"}>Nama</td>
                      <td>: {user?.name || form.getFieldValue("name")}</td>
                    </tr>
                    <tr>
                      <td width={"20%"}>No. Handphone</td>
                      <td>: {user?.phone || form.getFieldValue("phone")}</td>
                    </tr>
                    <tr>
                      <td width={"20%"}>Alamat Email</td>
                      <td>: {user?.email || form.getFieldValue("email")}</td>
                    </tr>
                    <tr>
                      <td width={"20%"}>Kecamatan</td>

                      {form.getFieldValue("address_id") != "new" ? (
                        <>
                          {!form.getFieldValue("address_id") ? (
                            <td>
                              :{" "}
                              {alamatDetail ||
                                form.getFieldValue("alamat_detail")}
                              {", "}
                              {selectedKecamatan}
                            </td>
                          ) : (
                            <td>:{selectedKecamatan}</td>
                          )}
                        </>
                      ) : (
                        <td>
                          :{" "}
                          {alamatDetail || form.getFieldValue("alamat_detail")}
                          {", "}
                          {selectedKecamatan}
                        </td>
                      )}
                    </tr>
                    <tr>
                      <td width={"20%"}>Catatan</td>
                      <td>: {note || form.getFieldValue("note")}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </Form>
          </div>
        </div>
      </Modal>

      <Modal
        title="Pilih Metode Pengiriman "
        open={isModalShippingMethodOpen}
        // onOk={handleOk}
        onCancel={() => {
          setIsModalShippingMethodOpen(false)
        }}
        footer={null}
      >
        {loadingShipping ? (
          <div className="flex justify-center items-center h-80 bg-white">
            <Spin size="large" />
          </div>
        ) : (
          <Collapse
            defaultActiveKey={[0]}
            activeKey={activeKey}
            onChange={(key) => setActiveKey(key)}
          >
            {listType.map((item, index) => {
              if (getList(shippingList[item]).length > 0) {
                return (
                  <Panel header={item} key={index}>
                    {getList(shippingList[item])?.map((row) => {
                      return (
                        <ShippingOption
                          item={row}
                          key={row?.id}
                          // selected={selectedPayment?.id === row?.id}
                        />
                      )
                    })}
                  </Panel>
                )
              }
            })}
          </Collapse>
        )}

        {shippingList.length === 0 && !loadingShipping && <Empty />}
      </Modal>

      <Modal
        title="Pilih Metode Pembayaran"
        open={isModalPaymentMethodOpen}
        // onOk={handleOk}
        onCancel={() => {
          setIsModalPaymentMethodOpen(false)
        }}
        footer={null}
      >
        <div>
          {paymentMethodList &&
            paymentMethodList.map((item, index) => {
              if (paymentMethodColapse[`item_${index}`]) {
                return (
                  <Card
                    title={item?.name}
                    key={index}
                    className=" mb-4 rounded-xl"
                    extra={
                      <DownOutlined
                        onClick={() =>
                          setPaymentMethodColapse((prev) => ({
                            ...prev,
                            [`item_${index}`]:
                              !paymentMethodColapse[`item_${index}`],
                          }))
                        }
                      />
                    }
                  >
                    {item?.childrens.map((row) => {
                      return (
                        <BankOption
                          item={row}
                          key={row?.id}
                          selected={selectedShippingMethod?.id === row?.id}
                        />
                      )
                    })}
                  </Card>
                )
              }

              return (
                <div className="flex justify-between items-center p-3 border border-black rounded-lg mb-4">
                  <h3 className="m-0 font-bold">{item?.name}</h3>
                  <RightOutlined
                    onClick={() =>
                      setPaymentMethodColapse((prev) => ({
                        ...prev,
                        [`item_${index}`]:
                          !paymentMethodColapse[`item_${index}`],
                      }))
                    }
                  />
                </div>
              )
            })}
        </div>
      </Modal>
    </div>
  )
}

const getList = (lists = []) => {
  return lists && lists.length > 0 ? lists : []
}

export default AddTransaction
