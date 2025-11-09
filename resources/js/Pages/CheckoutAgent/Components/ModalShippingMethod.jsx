import { CreditCardOutlined } from "@ant-design/icons"
import { message, Modal, Radio } from "antd"
import React, { useState } from "react"
import { formatNumber } from "../../../helpers"

const ModalShippingMethod = ({
  handleSelected,
  isTrue = false,
  validateText = "Text",
  url,
  form,
  loader,
}) => {
  const [visible, setVisible] = useState(false)
  const [loadingShipping, setLoadingShipping] = useState(false)
  const [shippingMethod, setShippingMethod] = useState([])
  const [selectedShipping, setSelectedShipping] = useState(null)
  const [hasSelected, setHasSelected] = useState(false)
  const loadShippingMethod = () => {
    setLoadingShipping(true)
    axios
      .post(url, form)
      .then((res) => {
        const { data } = res.data
        console.log(res.data)
        setShippingMethod(data)
        setLoadingShipping(false)
      })
      .catch((err) => {
        const { data } = err.response
        message.error(data?.message)
        setLoadingShipping(false)
      })
  }

  const handleSelectedShipping = () => {
    if (!selectedShipping) {
      return message.error("Silakan pilih salah satu Kurir Pengiriman")
    }
    handleSelected(selectedShipping)
    setVisible(false)
    setHasSelected(true)
    message.success("Kurir Pengiriman berhasil dipilih")
  }

  const listType = ["cargo", "regular", "same_day", "express", "instant"]
  return (
    <div>
      <div
        style={{
          border: "1px solid #e5e5e5",
          borderRadius: 10,
          padding: 10,
        }}
      >
        {hasSelected ? (
          <div
            class="list-group-item d-flex justify-content-between align-items-center"
            onClick={() => {
              setVisible(true)
              loadShippingMethod()
            }}
          >
            <div className="d-flex justify-content-start align-items-center">
              <img
                src={selectedShipping?.shipping_logo}
                alt=""
                style={{
                  // height: 20,
                  width: 35,
                }}
                className="object-contain"
              />
              <span className="ml-2">
                {selectedShipping?.shipping_type_name}
              </span>
            </div>
            <i className="fas fa-arrow-right"></i>
          </div>
        ) : (
          <button
            className="btn d-flex flex-row justify-content-between align-items-center w-100 rounded-lg"
            style={{
              border: "1px solid #e5e5e5",
              color: "#0478ae",
              borderRadius: 10,
            }}
            onClick={() => {
              if (isTrue) {
                return message.error(validateText)
              }
              setVisible(true)
              loadShippingMethod()
            }}
          >
            <span>
              <i className="fas fa-truck"></i>
              <span className="ml-2">Pilih Kurir Pengiriman</span>
            </span>

            <i className="fas fa-arrow-right"></i>
          </button>
        )}
      </div>
      <Modal
        title="Pilih Kurir Pengiriman"
        visible={visible}
        onOk={handleSelectedShipping}
        onCancel={() => setVisible(false)}
        okText="Pilih Kurir Pengiriman"
      >
        {loadingShipping ? (
          <div className="d-flex flex-column justify-content-center align-items-center">
            <img src={loader} alt="" />
            <span className="mt-2">Mohon Tunggu</span>
          </div>
        ) : (
          <div>
            {listType.map((item, index) => {
              if (getList(shippingMethod[item]).length > 0) {
                return (
                  <div className="mb-4" key={index}>
                    <span className="my-4 text-bold">
                      {item.replace("_", " ")}
                    </span>
                    <ul class="list-group mt-2">
                      {getList(shippingMethod[item]).map((shipping) => (
                        <li
                          key={shipping.shipping_type_code}
                          class="list-group-item d-flex justify-content-between align-items-center"
                        >
                          <div className="d-flex justify-content-start align-items-center">
                            <img
                              src={shipping.shipping_logo}
                              alt=""
                              style={{
                                // height: 20,
                                width: 35,
                              }}
                              className="object-contain"
                            />
                            <div className="d-flex flex-column justify-content-center align-items-start ml-2">
                              <span>{shipping.shipping_type_name}</span>
                              {shipping.shipping_discount > 0 ? (
                                <div>
                                  <s>
                                    {`Rp ${formatNumber(
                                      shipping.shipping_price
                                    )}`}
                                  </s>
                                  {shipping.shipping_discount >=
                                  shipping.shipping_price ? (
                                    <span className="ml-2 text-danger">
                                      {`Gratis Ongkir`}
                                    </span>
                                  ) : (
                                    <span className="ml-2 text-danger">
                                      {`Rp ${formatNumber(
                                        shipping.shipping_discount
                                      )}`}
                                    </span>
                                  )}
                                </div>
                              ) : (
                                <span className=" text-danger">
                                  {`Rp ${formatNumber(
                                    shipping.shipping_price
                                  )}`}
                                </span>
                              )}
                            </div>
                          </div>
                          <Radio
                            checked={
                              selectedShipping?.shipping_type_code ===
                              shipping.shipping_type_code
                            }
                            onChange={() => {
                              setSelectedShipping(shipping)
                            }}
                          />
                        </li>
                      ))}
                    </ul>
                  </div>
                )
              }
            })}
          </div>
        )}
      </Modal>
    </div>
  )
}

const getList = (lists = []) => {
  return lists && lists.length > 0 ? lists : []
}

export default ModalShippingMethod
