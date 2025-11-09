import { CreditCardOutlined } from "@ant-design/icons"
import { message, Modal, Radio } from "antd"
import axios from "axios"
import React, { useState } from "react"

const ModalPaymentMethod = ({
  handleSelected,
  isTrue = false,
  validateText = "Text",
}) => {
  const [visible, setVisible] = useState(false)
  const [loadingPayment, setLoadingPayment] = useState(false)
  const [paymentMethod, setPaymentMethod] = useState([])
  const [selectedPayment, setSelectedPayment] = useState(null)
  const [hasSelected, setHasSelected] = useState(false)
  const loadPaymentMethod = (url = "/api/cart/payment-method") => {
    setLoadingPayment(true)
    axios
      .get(url)
      .then((res) => {
        const { payment_methods } = res.data
        setPaymentMethod(payment_methods)
        setLoadingPayment(false)
      })
      .catch((err) => setLoadingPayment(false))
  }

  const handleSelectedPayment = () => {
    if (!selectedPayment) {
      return message.error("Silakan pilih salah satu metode pembayaran")
    }
    handleSelected(selectedPayment)
    setVisible(false)
    setHasSelected(true)
    message.success("Metode pembayaran berhasil dipilih")
  }
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
              loadPaymentMethod()
            }}
          >
            <div className="d-flex justify-content-start align-items-center">
              <img
                src={selectedPayment?.logo}
                alt=""
                style={{
                  // height: 20,
                  width: 35,
                }}
                className="object-contain"
              />
              <span className="ml-2">{selectedPayment?.nama_bank}</span>
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
              loadPaymentMethod()
            }}
          >
            <span>
              <CreditCardOutlined />
              <span className="ml-2">Pilih Metode Pembayaran</span>
            </span>

            <i className="fas fa-arrow-right"></i>
          </button>
        )}
      </div>
      <Modal
        title="Pilih Metode Pembayaran"
        visible={visible}
        onOk={handleSelectedPayment}
        onCancel={() => setVisible(false)}
        okText="Pilih Metode Pembayaran"
      >
        {paymentMethod.map((payment) => (
          <div className="mb-4">
            <span className="my-4 text-bold">{payment.nama_bank}</span>
            <ul class="list-group mt-2">
              {payment.children.map((child) => (
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <div className="d-flex justify-content-start align-items-center">
                    <img
                      src={child.logo}
                      alt=""
                      style={{
                        // height: 20,
                        width: 35,
                      }}
                      className="object-contain"
                    />
                    <span className="ml-2">{child.nama_bank}</span>
                  </div>
                  <Radio
                    checked={selectedPayment?.id === child.id}
                    onChange={() => {
                      setSelectedPayment(child)
                    }}
                  />
                </li>
              ))}
            </ul>
          </div>
        ))}
      </Modal>
    </div>
  )
}

export default ModalPaymentMethod
