import React, { useEffect, useState } from "react"
import { Table } from "antd"
import { formatNumber } from "../../helpers"
import { defaultItems } from "./config"
import ProductList from "./ProductList"

const ProductListInput = ({
  products = [],
  onChange,
  initialValues = defaultItems,
  validatePrice = true,
  loading = false,
  taxs = [],
  disabled = false,
  orderType = "",
  isEdit = false,
  typeProduct = ""
}) => {
  console.log("is edit:", isEdit)
  const [productItems, setProductItems] = useState(
    initialValues.length > 0 ? initialValues : defaultItems
  )

  useEffect(() => {
    if (initialValues.length > 0) {
      setProductItems(initialValues)
    }
  }, [initialValues])
  /* =========================PRODUCT LIST ACTION ============================ */

  const handleClickProductItem = async ({ type, key }) => {
    console.log(type, key)
    const datas = [...productItems]

    console.log("datas", datas)

    if (type === "add") {
      const lastData = datas[datas.length - 1]
      datas.push({
        key: lastData.key + 1,
        id: null,
        product_id: null,
        price: null,
        price_satuan: 0,
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

      setProductItems(datas.map((item, index) => ({ ...item, key: index })))
      return onChange(datas.map((item, index) => ({ ...item, key: index })))
    }

    if (type === "add-qty") {
      const updatedDatas = datas.map((item, index) => {
        if (index === key) {
          const qty = item.qty + 1
          const subtotal = item.price * qty
          const subtotal_price = item.price_satuan * qty
          const tax = taxs.find((tax) => tax.id === item.tax_id)
          const discount = parseInt(item.discount * qty)

          // Create a new object instead of modifying the existing one
          let newItem = { ...item, qty }

          if (tax && tax.tax_percentage > 0) {
            const price_nego = item.price_nego
            const tax_percentage = tax.tax_percentage / 100

            newItem = {
              ...newItem,
              tax_percentage,
              subtotal,
              discount_amount: discount,
            }

            if (price_nego > 0) {
              const tax_amount = parseInt(
                (price_nego - discount) * tax_percentage
              )
              newItem = {
                ...newItem,
                tax_amount,
                total: tax_amount + parseInt(price_nego - discount),
                price_nego: parseInt(subtotal_price),
                discount_amount: discount,
              }
            } else {
              const tax_amount = parseInt(
                (subtotal_price - discount) * tax_percentage
              )
              newItem = {
                ...newItem,
                tax_amount,
                total: tax_amount + parseInt(subtotal_price - discount),
                price_nego: parseInt(subtotal_price),
                discount_amount: discount,
              }
            }
          } else {
            newItem = {
              ...newItem,
              subtotal,
              total: parseInt(subtotal_price - discount),
              price_nego: parseInt(subtotal_price),
              discount_amount: discount,
            }
          }

          return newItem
        }
        return item
      })

      setProductItems(updatedDatas)
      return onChange(updatedDatas)
    }

    if (type === "remove-qty") {
      const updatedDatas = datas.map((item, index) => {
        if (index === key && item.qty > 1) {
          const qty = item.qty - 1
          const subtotal = item.price * qty
          const subtotal_price = item.price_satuan * qty
          const tax = taxs.find((tax) => tax.id === item.tax_id)
          const discount = parseInt(item.discount * qty)

          // Create a new object instead of modifying the existing one
          let newItem = { ...item, qty }

          if (tax && tax.tax_percentage > 0) {
            const price_nego = item.price_nego
            const tax_percentage = tax.tax_percentage / 100

            newItem = {
              ...newItem,
              tax_percentage,
              subtotal,
              discount_amount: discount,
            }

            if (price_nego > 0) {
              const tax_amount = parseInt(
                (price_nego - discount) * tax_percentage
              )
              newItem = {
                ...newItem,
                tax_amount,
                total: tax_amount + parseInt(price_nego - discount),
                price_nego: parseInt(subtotal_price),
                discount_amount: discount,
              }
            } else {
              const tax_amount = parseInt(
                (subtotal_price - discount) * tax_percentage
              )
              newItem = {
                ...newItem,
                tax_amount,
                total: tax_amount + parseInt(subtotal_price - discount),
                price_nego: parseInt(subtotal_price),
                discount_amount: discount,
              }
            }
          } else {
            newItem = {
              ...newItem,
              subtotal,
              total: parseInt(subtotal_price - discount),
              price_nego: parseInt(subtotal_price),
              discount_amount: discount,
            }
          }

          return newItem
        }
        return item
      })

      setProductItems(updatedDatas)
      return onChange(updatedDatas)
    }
    // Jika mode edit, panggil API delete
    if (isEdit && type === "delete") {
      try {
        const itemToDelete = datas.find((item) => item.key === key);
        console.log("itemToDelete", itemToDelete)
        await axios
          .get(`/api/general/delete-product/${typeProduct}/${itemToDelete.id}`)
          .then((res) => {
            console.log("Delete berhasil di API:", res.data);
          });
      } catch (error) {
        console.error("Error saat menghapus item:", error);
        return;
      }
    }

    // Hapus data dari frontend
    const newData = datas.filter((item) => item.key !== key)
    setProductItems(newData.map((item, index) => ({ ...item, key: index })))
    return onChange(newData.map((item, index) => ({ ...item, key: index })))
  }

  const handleChangeProductItem = ({ dataIndex, value, key }) => {
    const datas = [...productItems]

    if (value === null) {
      datas[key][dataIndex] = null

      setProductItems(datas)
      return onChange(datas)
    }

    if (dataIndex === "qty") {
      const updatedDatas = datas.map((item, index) => {
        const product = products.find((p) => p.id === item.product_id)

        if (!product) return item // Skip if product not found

        // Hitung ulang subtotal, discount_amount, dan total untuk setiap item
        const qty = index === key ? value : item.qty // Update hanya item yang diubah
        const price = product?.price?.final_price || 0
        const subtotal = price * qty
        const discountAmount =
          (item.discount_percentage / 100) * (item.price_nego || subtotal)
        const total = (item.price_nego || subtotal) - discountAmount

        return {
          ...item,
          qty,
          subtotal,
          discount_amount: discountAmount,
          total,
        }
      })

      setProductItems(updatedDatas)
      return onChange(updatedDatas)
    }

    if (dataIndex === "product_id") {
      const product = products.find((item) => item.id === value)

      if (product) {
        const updatedItem = {
          ...datas[key],
          stock: product.final_stock,
          price: product.price.final_price || 0,
          subtotal: (product.price.final_price || 0) * datas[key].qty,
          total: (product.price.final_price || 0) * datas[key].qty,
          [dataIndex]: value,
        }
        datas[key] = updatedItem
      } else {
        datas[key][dataIndex] = value
      }
    }

    if (dataIndex === "tax_id") {
      datas.forEach((item) => {
        const tax = taxs.find((t) => t.id === value)
        const updatedData = { ...item } // Copy item

        if (tax) {
          const product = products.find((p) => p.id === item.product_id)
          const discountAmount = item["discount_amount"]
          const qty = item["qty"]
          const priceNego = item["price_nego"]
          const subtotalPrice = product?.price?.final_price * qty

          if (tax.tax_percentage > 0) {
            const taxPercentage = tax.tax_percentage / 100
            updatedData["tax_percentage"] = taxPercentage

            const taxAmount =
              priceNego > 0
                ? parseInt((priceNego - discountAmount) * taxPercentage)
                : parseInt((subtotalPrice - discountAmount) * taxPercentage)

            updatedData["tax_amount"] = taxAmount
            updatedData["total"] =
              taxAmount +
              parseInt(
                priceNego > 0
                  ? priceNego - discountAmount
                  : subtotalPrice - discountAmount
              )
          } else {
            updatedData["tax_percentage"] = 0
            updatedData["tax_amount"] = 0
            updatedData["total"] = parseInt(
              priceNego > 0
                ? priceNego - discountAmount
                : subtotalPrice - discountAmount
            )
          }
        }
        datas[item.key] = updatedData
        datas[item.key][dataIndex] = value
      })
    }

    setProductItems(datas)
    return onChange(datas)
  }

  const handleChangeProductPrice = ({ dataIndex, value, key }) => {
    // console.log(dataIndex, value, "handle change product price")
    const datas = [...productItems]
    if (dataIndex === "qty") {
      const updatedDatas = datas.map((item, index) => {
        if (index === key) {
          const product = products.find((p) => p.id === item.product_id)

          if (product) {
            const subtotal_price = item.price_satuan * value
            const tax = taxs.find((taxItem) => taxItem.id === item.tax_id)
            const discount = parseInt(item.discount * item.qty)

            if (tax && tax.tax_percentage > 0) {
              const price_nego = item.price_satuan
              const tax_percentage = tax.tax_percentage / 100

              const tax_amount = parseInt(
                (price_nego - discount) * tax_percentage
              )

              // Create a new object with the updated tax_percentage
              item = { ...item, tax_percentage }

              if (price_nego > 0) {
                item.tax_amount = tax_amount
                item.total = tax_amount + parseInt(price_nego - discount)
              } else {
                item.tax_amount = parseInt(
                  (subtotal_price - discount) * tax_percentage
                )
                item.total = tax_amount + parseInt(subtotal_price - discount)
              }
            } else {
              item.total = parseInt(subtotal_price - discount)
            }
            // Create a new object with the updated quantity
            item.price_nego = parseInt(subtotal_price)
            item.subtotal = item.price * value
            item.discount_amount = value * item.discount

            // datas[key]["discount_amount"] = value * item.discount

            return { ...item, qty: value }
          }
        }
        return item
      })

      // Update the state with the new array
      setProductItems(updatedDatas)
      return onChange(updatedDatas)
    }

    if (dataIndex === "discount") {
      const price_nego = datas[key]["price_nego"]
      const subtotal_price = datas[key]["price_satuan"] * datas[key]["qty"]
      const price_amount = price_nego > 0 ? price_nego : subtotal_price
      // Update discount percentage
      datas[key]["discount_percentage"] =
        value > 0 ? (value / price_amount) * 100 : 0
      datas[key][dataIndex] = value
      const tax = taxs.find((item) => item.id === datas[key]["tax_id"])
      if (value === "" || value < 1) {
        datas[key]["discount_amount"] = 0
        if (tax && tax.tax_percentage > 0) {
          const tax_percentage = tax.tax_percentage / 100
          datas[key]["tax_percentage"] = tax_percentage

          if (price_nego > 0) {
            const tax_amount = parseInt((price_nego - 0) * tax_percentage)
            datas[key]["tax_amount"] = tax_amount
            datas[key]["total"] = tax_amount + parseInt(price_nego - 0)
          } else {
            const tax_amount = parseInt((subtotal_price - 0) * tax_percentage)
            datas[key]["tax_amount"] = tax_amount
            datas[key]["total"] = tax_amount + (subtotal_price - 0)
          }
        } else {
          datas[key]["total"] = price_nego
        }
      } else {
        const discount = value * datas[key]["qty"]
        datas[key]["discount_amount"] = discount
        const product = products.find(
          (item) => item.id === datas[key]["product_id"]
        )

        if (product) {
          const subtotal_price = price_nego

          if (tax && tax.tax_percentage > 0) {
            const tax_percentage = tax.tax_percentage / 100
            datas[key]["tax_percentage"] = tax_percentage

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
          } else {
            datas[key]["total"] = parseInt(subtotal_price - discount)
          }
        }
      }
    }

    if (dataIndex === "price_satuan") {
      // Clone the object to make it mutable
      const updatedDatas = { ...datas[key] }

      // Modify the cloned object
      updatedDatas[dataIndex] = value
      const price_nego = value * updatedDatas["qty"]
      updatedDatas["price_nego"] = price_nego
      const discount = parseInt(updatedDatas["discount_amount"])
      const product_id = updatedDatas["product_id"]
      const product = products.find((item) => item.id === product_id)
      if (value < 1 || value === "") {
        const subtotal = price_nego
        updatedDatas["total"] = subtotal - discount
      } else {
        if (product) {
          const subtotal = price_nego
          const tax = taxs.find((item) => item.id === updatedDatas["tax_id"])
          if (tax) {
            if (tax.tax_percentage > 0) {
              const tax_percentage = tax.tax_percentage / 100
              updatedDatas["tax_percentage"] = tax_percentage

              const tax_amount = parseInt(
                (subtotal - discount) * tax_percentage
              )

              updatedDatas["tax_amount"] = tax_amount
              updatedDatas["total"] = tax_amount + parseInt(subtotal - discount)
            }
          } else {
            updatedDatas["total"] = subtotal - discount
          }
        }
      }
      // Assign the updated object back to the original datas array
      datas[key] = updatedDatas
    }

    if (dataIndex === "price_nego") {
      // Clone the object to make it mutable
      const updatedDatas = { ...datas[key] }

      // Modify the cloned object
      updatedDatas[dataIndex] = value
      const discount = parseInt(updatedDatas["discount_amount"])
      const product_id = updatedDatas["product_id"]
      const product = products.find((item) => item.id === product_id)
      if (value < 1 || value === "") {
        const subtotal = updatedDatas["subtotal"]
        updatedDatas["total"] = subtotal - discount
      } else {
        if (product) {
          const subtotal = value
          const tax = taxs.find((item) => item.id === updatedDatas["tax_id"])
          if (tax) {
            if (tax.tax_percentage > 0) {
              const tax_percentage = tax.tax_percentage / 100
              updatedDatas["tax_percentage"] = tax_percentage

              const tax_amount = parseInt(
                (subtotal - discount) * tax_percentage
              )

              updatedDatas["tax_amount"] = tax_amount
              updatedDatas["total"] = tax_amount + parseInt(subtotal - discount)
            }
          } else {
            updatedDatas["total"] = subtotal - discount
          }
        }
      }
      // Assign the updated object back to the original datas array
      datas[key] = updatedDatas
    }

    datas[key][dataIndex] = value

    setProductItems(datas)
    return onChange(datas)
  }
  /* =========================END PRODUCT LIST ACTION ============================ */
  console.log(disabled, "disabled")
  return (
    <ProductList
      orderType={orderType}
      data={productItems}
      products={products}
      taxs={taxs}
      discounts={[]}
      onChange={handleChangeProductPrice}
      handleChange={handleChangeProductItem}
      handleClick={handleClickProductItem}
      loading={loading}
      key={"key"}
      disabled={disabled}
      summary={(currentData) => {
        const subtotal = currentData.reduce(
          (acc, curr) => parseInt(acc) + parseInt(curr.price_nego || 0),
          0
        )
        const discount_amount = currentData.reduce(
          (acc, curr) => parseInt(acc) + parseInt(curr.discount_amount || 0),
          0
        )

        const dpp = subtotal - discount_amount
        const ppn = dpp * currentData[0].tax_percentage
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
                <strong>Diskon :</strong>
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
    />
  )
}

export default ProductListInput
