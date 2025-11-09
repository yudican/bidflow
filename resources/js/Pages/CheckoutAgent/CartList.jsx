import {
  CreditCardOutlined,
  LoadingOutlined,
  ScissorOutlined,
} from "@ant-design/icons"
import { Card, Checkbox, Input, message } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import Sticky from "react-stickynode"
import { toast } from "react-toastify"
import HeaderCard from "../../components/atoms/HeaderCard"
import { formatNumber } from "../../helpers"
import ModalAddressList from "./Components/ModalAddressList"
import ModalPaymentMethod from "./Components/ModalPaymentMethod"
import ModalShippingMethod from "./Components/ModalShippingMethod"
import ModalVariant from "./Components/ModalVariant"
import ModalWarehouseList from "./Components/ModalWarehouseList"

const NumericInput = (props) => {
  const { value, onChange } = props
  const handleChange = (e) => {
    const { value: inputValue } = e.target
    const reg = /^-?\d*(\.\d*)?$/
    if (reg.test(inputValue) || inputValue === "" || inputValue === "-") {
      onChange(inputValue)
    }
  }
  // '.' at the end or only '-' in the input box.
  const handleBlur = () => {
    let valueTemp = value
    if (value.charAt(value.length - 1) === "." || value === "-") {
      valueTemp = value.slice(0, -1)
    }
    onChange(valueTemp.replace(/0*(\d+)/, "$1"))
  }

  return (
    <Input
      className="text-center"
      {...props}
      onChange={handleChange}
      onBlur={handleBlur}
      placeholder="Input a number"
      maxLength={16}
    />
  )
}

const CartList = () => {
  // loading
  const [loading, setLoading] = React.useState(false)
  const [applyVoucherLoading, setApplyVoucherLoading] = React.useState(false)
  const [loadingTransaction, setLoadingTransaction] = React.useState(false)

  const [carts, setCart] = React.useState([])
  const [selectedAll, setSelectedAll] = React.useState(false)
  const [voucher, setVoucher] = React.useState(null)

  const [selectedWarehouse, setSelectedWarehouse] = React.useState(null)
  const [selectedAddress, setSelectedAddress] = React.useState(null)
  const [selectedShipping, setSelectedShipping] = React.useState(null)
  const [selectedPayment, setSelectedPayment] = React.useState(null)
  const [checkoutInfo, setCheckoutInfo] = React.useState(null)

  const [voucherCode, setVoucherCode] = React.useState("")
  const loadCart = (url = "/api/cart") => {
    setLoading(true)
    axios
      .get(url)
      .then((res) => {
        const { carts, checkoutData } = res.data
        setCheckoutInfo(checkoutData)
        setCart(carts)
        setLoading(false)
        setSelectedAll(checkoutData.selected_all)
      })
      .catch(() => setLoading(false))
  }

  const applyVoucher = () => {
    setApplyVoucherLoading(true)
    axios
      .post(checkoutInfo?.voucher_url, {
        user_id: checkoutInfo?.user.user_id,
        voucher_code: voucherCode,
        nominal: checkoutInfo?.total_amount,
      })
      .then((res) => {
        const { data } = res.data
        message.success("Voucher berhasil di gunakan")
        setVoucher(data)
        setApplyVoucherLoading(false)
      })
      .catch((err) => {
        const { data } = err.response
        message.error(data.message)
        setApplyVoucherLoading(false)
      })
  }

  const handleCheckoutProccess = () => {
    if (!selectedPayment) {
      return message.error("Harap Pilih Metode Pembayaran Terlebih Dahulu")
    }
    const discount = voucher?.amount_discount || 0
    const totalPrice = checkoutInfo?.total_amount || 0
    const shippingDiscount = getShippingDiscount(selectedShipping)
    const shippingPrice = selectedShipping?.shipping_price || 0

    const amount_to_pay =
      totalPrice + shippingPrice - discount - shippingDiscount
    const dataToStore = {
      payment_method_id: selectedPayment?.id,
      brand_id: checkoutInfo?.user?.brand_id,
      amount_to_pay,
      address_user_id: selectedAddress?.id,
      shipper_address_id: selectedWarehouse?.id,
      products: getProductPayload(carts),
      voucher_id: voucher?.voucher_id,
      diskon: discount,
      shipping: selectedShipping,
      ongkir: shippingPrice - shippingDiscount,
      user_id: checkoutInfo?.user?.user_id,
    }
    setLoadingTransaction(true)
    axios
      .post(checkoutInfo?.checkout_url, dataToStore)
      .then((res) => {
        const { data } = res.data
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        setLoadingTransaction(false)
        setTimeout(() => {
          window.location.href = checkoutInfo.redirect_url.replace(
            ":transaction_id",
            data.id
          )
        }, 2000)
      })
      .catch((err) => {
        const { data } = err.response
        setLoadingTransaction(false)
        toast.error(err.response.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }

  const handleChangeQty = (cart_id, type = "add-qty") => {
    axios
      .get(`/api/cart/${type}/${cart_id}`)
      .then((res) => {
        loadCart()
      })
      .catch(() => setLoading(false))
  }

  const handleChangeDynamicQty = (cart_id, amount, type = "update-qty") => {
    axios
      .post(`/api/cart/${type}/${cart_id}`, { qty: amount })
      .then((res) => {
        loadCart()
      })
      .catch(() => setLoading(false))
  }

  const deleteCartItem = (cart_id) => {
    axios
      .get(`/api/cart/delete/${cart_id}`)
      .then((res) => {
        loadCart()
      })
      .catch(() => setLoading(false))
  }

  const handleSelected = (type = "all", cart_id) => {
    const url =
      type === "all" ? "/api/cart/select-all" : `/api/cart/select/${cart_id}`
    axios
      .get(url)
      .then((res) => {
        loadCart()
      })
      .catch(() => setLoading(false))
  }

  useEffect(() => {
    loadCart()
  }, [])

  const totalQty = checkoutInfo?.total_qty || 0
  const discount = voucher?.amount_discount || 0
  const totalPrice = checkoutInfo?.total_amount || 0
  const shippingDiscount = getShippingDiscount(selectedShipping)
  const shippingPrice = selectedShipping?.shipping_price || 0
  const haveCart = carts.length > 0

  return (
    <div>
      <div className="row">
        <HeaderCard title="List Cart" href={"/"} />
      </div>
      {haveCart ? (
        <div className="row">
          <div className="col-md-8 ">
            <Sticky enabled={true} top={100}>
              <div className="card shadow-none">
                <div className="card-body">
                  <Checkbox
                    onChange={() => handleSelected("all")}
                    checked={selectedAll}
                  >
                    Pilih Semua
                  </Checkbox>
                </div>
              </div>
              <div className="card  shadow-none ">
                <div className="card-body">
                  {carts.map((cart, index) => {
                    console.log(cart, "cart item")
                    return (
                      <CartItem
                        cart={cart}
                        key={cart.id}
                        handleChangeQty={handleChangeQty}
                        handleChangeDynamicQty={handleChangeDynamicQty}
                        deleteCartItem={deleteCartItem}
                        handleSelected={handleSelected}
                        index={index + 1}
                        refetch={loadCart}
                      />
                    )
                  })}
                </div>
              </div>
            </Sticky>
          </div>

          <div className="col-md-4 ">
            <div className="card shadow-none">
              <div className="card-body py-4">
                <h1
                  style={{ fontSize: 18, fontWeight: "bold" }}
                  className="mb-0"
                >
                  Informasi Pengiriman
                </h1>
              </div>
            </div>
            <Card title="Alamat Pengiriman" bordered={false}>
              <ModalAddressList
                handleSelected={(item) => setSelectedAddress(item)}
                initialValues={checkoutInfo?.user || {}}
              />
            </Card>

            {/* gudang pengiriman */}
            <Card title="Dikirim Dari" bordered={false} className="mt-2">
              <ModalWarehouseList
                handleSelected={(item) => setSelectedWarehouse(item)}
                isTrue={!selectedAddress ? true : false}
                validateText="Harap Pilih Alamat Pengiriman"
              />
            </Card>

            {/* jasa kirim */}
            <Card
              // title="Kurir Pengiriman"
              bordered={false}
              className="mt-2"
            >
              <ModalShippingMethod
                handleSelected={(item) => setSelectedShipping(item)}
                url={checkoutInfo?.shipping_info_url}
                form={{
                  kodepos: selectedAddress?.kodepos,
                  kodepos_origin: selectedWarehouse?.kodepos,
                  weight: checkoutInfo?.total_weight,
                }}
                loader={checkoutInfo?.loader}
                isTrue={!selectedWarehouse ? true : false}
                validateText="Harap Pilih Gudang Pengiriman"
              />
            </Card>

            {/* voucher */}
            <Card title="Punya Kode Promo?" bordered={false} className="mt-2">
              <Input
                className="rounded"
                placeholder="masukkan kode promo"
                onChange={(e) => setVoucherCode(e.target.value)}
              />
              <button
                onClick={() => applyVoucher()}
                className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center justify-center w-full mt-2"
                disabled={applyVoucherLoading}
              >
                {applyVoucherLoading ? (
                  <LoadingOutlined />
                ) : (
                  <ScissorOutlined />
                )}
                <span className="ml-2">Gunakan Voucher</span>
              </button>
            </Card>

            {/* Metode Pembayaran */}
            <Card title="Metode Pembayaran" bordered={false} className="mt-2">
              <ModalPaymentMethod
                handleSelected={(item) => setSelectedPayment(item)}
                isTrue={!selectedShipping ? true : false}
                validateText="Mohon Pilih Metode Pengiriman"
              />
            </Card>

            {/* rincian pembelian */}
            <Card title="Rincian Pembayaran" bordered={false} className="mt-2">
              <ul className="list-group">
                <li className="list-group-item d-flex justify-content-between align-items-center px-0 m-0 border-0">
                  Ongkos Kirim
                  <span>{getShippingDiscount(selectedShipping, "label")}</span>
                </li>
                {discount > 0 && (
                  <li className="list-group-item d-flex justify-content-between align-items-center px-0 m-0 border-0">
                    Diskon
                    <span>Rp. {formatNumber(discount)}</span>
                  </li>
                )}

                <li className="list-group-item d-flex justify-content-between align-items-center px-0 m-0 border-0">
                  Total ({totalQty}) Barang
                  <span>Rp. {formatNumber(totalPrice)}</span>
                </li>
                <li className="list-group-item d-flex justify-content-between align-items-center px-0 m-0 border-0">
                  Total Harga
                  <span className="text-danger">
                    Rp.{" "}
                    {formatNumber(
                      totalPrice + shippingPrice - discount - shippingDiscount
                    )}
                  </span>
                </li>
              </ul>

              <button
                onClick={handleCheckoutProccess}
                className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center justify-center w-full mt-2"
                disabled={loadingTransaction}
              >
                {loadingTransaction ? (
                  <LoadingOutlined />
                ) : (
                  <CreditCardOutlined />
                )}
                <span className="ml-2">Buat Pesanan</span>
              </button>
            </Card>
          </div>
        </div>
      ) : (
        <div className="row">
          <div className="col-md-12">
            <div className="card">
              <div className="card-body d-flex justify-content-center align-items-center">
                <img
                  src="https://aimidev.s3.us-west-004.backblazeb2.com/upload/user/28x5e4FjRn9ve2BKCWcY1ZyDLo3UbbL6gpxeYijW.svg"
                  style={{ height: 300 }}
                  alt=""
                />
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}

const CartItem = ({
  cart,
  handleChangeQty,
  handleChangeDynamicQty,
  deleteCartItem,
  handleSelected,
  index,
  refetch,
}) => {
  return (
    <div className="mb-3 border-b-2 pb-3">
      <div className="d-flex justify-content-between align-items-center flex-row">
        <div className="d-flex align-items-start justify-content-start w-100">
          <div className="form-check p-0 m-0">
            <Checkbox
              onChange={() => handleSelected("single", cart.id)}
              checked={cart.selected > 0}
            />
          </div>
          <div className="aside ml-2">
            <img
              src={cart?.product?.image_url}
              style={{ height: 72, width: 72 }}
              className="img-thumbnail img-sm"
            />
          </div>
          <div className="d-flex flex-column align-items-start ml-2 w-100">
            <div className="d-flex justify-content-between w-100">
              <span
                className="title mb-0 text-dark"
                style={{
                  fontSize: 12,
                  lineHeight: 1.5,
                  width: 300,
                }}
              >
                {cart?.product_variant_id
                  ? cart?.variant?.name
                  : cart?.product?.name}
              </span>
              <span>
                <img
                  src="https://i.ibb.co/m0KsjB3/freeongkir.png"
                  style={{
                    height: 20,
                    // position: "absolute",
                    // right: 10,
                  }}
                  className="float-right mt-0"
                />
              </span>
            </div>
            <div className="w-full d-flex flex-row justify-content-between">
              <div>
                <ModalVariant
                  cart={cart}
                  variant={cart.variant}
                  refetch={refetch}
                />
                <p
                  className=" mb-0 text-black"
                  style={{ fontSize: 12, fontWeight: "bold" }}
                >
                  Rp. {formatNumber(cart?.product?.priceData?.final_price)}
                </p>
              </div>

              <div
                className="text-right"
                // style={{ position: "absolute", right: 10, bottom: 60 }}
              >
                <div className="d-flex  align-items-center flex-row mt-2">
                  <div className="input-group input-spinner mr-3">
                    <button
                      className="btn btn-light btn-xs border"
                      type="button"
                      onClick={() => handleChangeQty(cart.id, "remove-qty")}
                      // wire:click="min_qty({{$cart->id}})"
                    >
                      <i className="fas fa-minus"></i>
                    </button>
                    {/* Qty CART */}
                    {/* <button
                      className="btn btn-light btn-xs border"
                      type="button"
                    >
                      {cart.qty}
                    </button> */}
                    <Input
                      style={{
                        width: 60,
                        textAlign: "center",
                      }}
                      defaultValue={cart.qty.toString()}
                      // value={cart.qty.toString()}
                      onChange={(e) =>
                        handleChangeDynamicQty(cart.id, e.target.value)
                      }
                    />
                    <button
                      className="btn btn-light btn-xs border"
                      type="button"
                      onClick={() => handleChangeQty(cart.id, "add-qty")}
                      // wire:click="add_qty({{$cart->id}})"
                    >
                      <i className="fas fa-plus"></i>
                    </button>
                  </div>
                  <button
                    className="btn btn-light btn-xs border"
                    // wire:click="delete({{$cart->id}})"
                    onClick={() => deleteCartItem(cart.id)}
                  >
                    <i className="fas fa-trash"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}

const getShippingDiscount = (shipping = null, type = "value") => {
  if (shipping) {
    const discount =
      shipping.shipping_discount > 0 ? shipping.shipping_discount : 0
    const shippingPrice = shipping.shipping_price
    if (discount > 0) {
      if (discount > shippingPrice) {
        const discountAmount = discount - shippingPrice
        return type === "label" ? `Gratis Ongkir` : discountAmount
      }
      const totalDiscount = shippingPrice - discount
      if (totalDiscount == 0) {
        return type === "label" ? `Gratis Ongkir` : 0
      }

      return type === "label"
        ? `Rp ${formatNumber(totalDiscount)}`
        : totalDiscount
    }

    return type === "label" ? `Rp ${formatNumber(shippingPrice)}` : 0
  }

  return type === "label" ? "Belum Pilih Kurir" : 0
}

const getProductPayload = (cartData) => {
  return cartData
    .map((item) => {
      if (item.selected > 0) {
        return {
          product_id: item?.product?.id,
          qty: item?.qty,
          price: item?.product?.priceData?.final_price,
          variant_id: item?.product_variant_id,
        }
      }
    })
    .filter((item) => item)
}

export default CartList
