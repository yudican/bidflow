import { message, Modal, Radio } from "antd"
import React, { useState } from "react"
import { formatNumber } from "../../../helpers"
import axios from "axios"

const ModalVariant = ({ refetch, variant = null, cart }) => {
  const [visible, setVisible] = useState(false)
  const [selectedVariant, setSelectedVariant] = useState(null)
  const variants = cart?.variants || []

  const handleSelectVariant = () => {
    axios
      .post("/api/cart/select-variant", {
        variant_id: selectedVariant?.id,
        cart_id: cart.id,
      })
      .then((res) => {
        message.success(res?.data?.message)
        setVisible(false)
        refetch()
      })
      .catch(() => {
        message.error("Variant gagal diubah")
      })
  }

  const hasVariant = selectedVariant || variant
  return (
    <div>
      <p className="text-dark mb-0" style={{ lineHeight: 1.2, fontSize: 12 }}>
        <span>{cart?.product?.weight * cart.qty} gr | </span>
        <span
          className="text-danger cursor-pointer"
          onClick={() => {
            setVisible(true)
            setSelectedVariant(variant)
          }}
        >
          {hasVariant ? "Ubah Variant" : "Pilih Variant"}
        </span>
      </p>
      <Modal
        title="Pilih Variasi Produk"
        open={visible}
        onOk={handleSelectVariant}
        onCancel={() => setVisible(false)}
        okText="Pilih Variasi Produk"
      >
        <ul className="list-group mt-2">
          {variants &&
            variants.map((item, index) => {
              // console.log(item, "variant item")
              return (
                <li
                  key={item.id}
                  className="list-group-item d-flex justify-content-between align-items-center"
                >
                  <div className="d-flex justify-content-start align-items-center">
                    <div className="font-bold w-8">{index + 1}.</div>
                    <div className="d-flex flex-column justify-content-center align-items-start ml-2">
                      <span>{item.name}</span>
                      <span className="text-danger font-semibold">
                        Stock : {item.stock_off_market}
                      </span>
                      <div>
                        {item.price.basic_price > 0 && (
                          <s>{`Rp ${formatNumber(item.price.basic_price)}`}</s>
                        )}

                        <span className=" text-danger ml-2">
                          {`Rp ${formatNumber(item.price.final_price)}`}
                        </span>
                      </div>
                    </div>
                  </div>
                  <Radio
                    checked={item?.id === hasVariant?.id}
                    onChange={() => {
                      setSelectedVariant(item)
                    }}
                  />
                </li>
              )
            })}
        </ul>
      </Modal>
    </div>
  )
}

export default ModalVariant
