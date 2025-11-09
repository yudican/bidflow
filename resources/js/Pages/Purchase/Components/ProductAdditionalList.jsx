import { CloseOutlined, PlusOutlined } from "@ant-design/icons"
import { Input, Select, Table } from "antd"
import React, { useState, useEffect } from "react"
import ModalProduct from "../../../components/Modal/ModalProduct"
import { formatNumber } from "../../../helpers"
import { productListColumns } from "../config"
import { searchProduct } from "../services"
import { toast } from "react-toastify"

const ProductAdditionalList = ({
  products = [],
  packages = [],
  handleChange,
  handleClick,
  data = [],
  taxs = [],
  wh = false,
  loading = false,
  type = "Pengemasan",
  summary,
}) => {
  console.log("warehouse pa:", wh)
  const [cartonQtyMap, setCartonQtyMap] = useState({})

  const handleSearchProduct = async (productId) => {
    try {
      const result = await searchProduct(productId)

      return {
        label: result.productName,
        value: result.id,
        cartonQty: result.qty,
      }
    } catch (error) {
      console.error("Error fetching product data:", error)
      return { label: "Error", value: null, cartonQty: 1 } // default cartonQty jika error
    }
  }

  const updateCartonQtyMap = async (productId) => {
    const product = await handleSearchProduct(productId)
    if (product.cartonQty) {
      setCartonQtyMap((prevState) => ({
        ...prevState,
        [productId]: product.cartonQty,
      }))
    }
  }

  useEffect(() => {
    const productIds = Array.from(new Set(data.map((item) => item.product_id)))
    productIds.forEach((id) => {
      if (!cartonQtyMap[id]) {
        updateCartonQtyMap(id)
      }
    })
  }, [data, cartonQtyMap])

  const mergedColumns = productListColumns.map((col) => ({
    ...col,
    onCell: (record) => ({
      record,
      dataIndex: col.dataIndex,
      products,
      packages,
      taxs,
      wh,
      type,
      data,
      handleChange: (val) => handleChange(val),
      handleClick: (val) => handleClick(val),
      cartonQty: cartonQtyMap[record.product_id] || 1,
    }),
  }))

  return (
    <div>
      <Table
        components={{
          body: {
            cell: EditableCell,
          },
        }}
        dataSource={data}
        columns={mergedColumns}
        loading={loading}
        pagination={false}
        rowKey="key"
        scroll={{ x: "max-content" }}
        tableLayout={"auto"}
        summary={summary}
      />
      <div
        onClick={() =>
          handleClick({
            type: "add",
            key: 1,
            item_id: 1,
          })
        }
        className="
          w-full mt-4 cursor-pointer
          text-blue-600 hover:text-blue-800
          bg-blue-500/20 border-2 border-blue-700/70 hover:border-blue-800 border-dashed  focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 inline-flex items-center justify-center"
      >
        <PlusOutlined style={{ marginRight: 10 }} />
        <strong>Add More</strong>
      </div>
    </div>
  )
}

const EditableCell = (props) => {
  const {
    dataIndex,
    handleChange,
    handleClick,
    record,
    products,
    packages,
    taxs,
    wh,
    type,
    data,
    cartonQty,
  } = props
  console.log("warehouse table :", wh)
  const calculateRecommendations = (inputQty, cartonQty) => {
    if (cartonQty > 0) {
      if (inputQty < cartonQty) {
        return [cartonQty, cartonQty * 2, cartonQty * 3, cartonQty * 4]
      }
      if (inputQty % cartonQty === 0) {
        return []
      }

      const mod = inputQty % cartonQty
      const base = inputQty - mod

      const recommendations = [
        base,
        base + cartonQty,
        base + 2 * cartonQty,
        base + 3 * cartonQty,
      ]

      return recommendations
    }
    return []
  }

  if (dataIndex === "product_id") {
    console.log("warehouse pa 1 :", wh)
    return (
      <td>
        <ModalProduct
          products={products}
          productNeedSelected={data} // use for filtering
          type={type}
          warehouse={wh}
          handleChange={(e) =>
            handleChange({
              value: e,
              dataIndex,
              key: record.key,
              item_id: record.id,
            })
          }
          value={record?.product_id}
        />
      </td>
    )
  }

  if (dataIndex === "uom") {
    return (
      <td style={{ width: 200 }}>
        <Select
          style={{ width: 200 }}
          showSearch
          filterOption={(input, option) => {
            return (option?.children ?? "")
              .toLowerCase()
              .includes(input.toLowerCase())
          }}
          placeholder="Pilih UoM"
          value={record.uom}
          onChange={(e) =>
            handleChange({
              value: e,
              dataIndex,
              key: record.key,
              item_id: record.id,
            })
          }
        >
          {packages.map((tax) => (
            <Select.Option value={tax.name} key={tax.id}>
              {tax.name}
            </Select.Option>
          ))}
        </Select>
      </td>
    )
  }

  if (dataIndex === "harga_satuan") {
    return (
      <td>
        <Input
          value={record[dataIndex]}
          onChange={(e) => {
            if (e.target.value > -1) {
              return handleChange({
                value: e.target.value,
                dataIndex,
                key: record.key,
                item_id: record.id,
              })
            }
            return null
          }}
        />
      </td>
    )
  }

  if (dataIndex === "qty") {
    return (
      <td>
        <div>
          {record?.product_id &&
            record?.qty > 0 &&
            record.recommendations &&
            record.recommendations.length > 0 && (
              <div style={{ marginBottom: "10px" }}>&nbsp; </div>
            )}
          <div className="input-group input-spinner mr-3">
            <button
              className="btn btn-light btn-xs border"
              type="button"
              onClick={() => {
                const newQty = Math.max(record.qty - 1, 0) // Decrease qty, ensure it's not less than 0
                handleChange({
                  value: newQty,
                  dataIndex,
                  key: record.key,
                  item_id: record.id,
                })

                // Trigger recommendations after reducing qty
                const updatedRecommendations = calculateRecommendations(
                  newQty,
                  cartonQty
                )
                handleChange({
                  value: updatedRecommendations,
                  dataIndex: "recommendations",
                  key: record.key,
                  item_id: record.id,
                })
              }}
            >
              <i className="fas fa-minus"></i>
            </button>

            <Input
              value={record[dataIndex]}
              onChange={(e) => {
                const value = parseInt(e.target.value, 10)
                const recs = record.recommendations || []

                if (value >= 0 && recs.length >= 2) {
                  const min = recs[0]
                  const step = recs[1] - recs[0]

                  // Validasi: minimal dan kelipatan step
                  if (value < min || (value - min) % step !== 0) {
                    // alert(`Qty harus dimulai dari ${min} dan kelipatan ${step}`)
                    toast.error(`Qty harus dimulai dari ${min} dan kelipatan ${step}`)
                    return
                  }
                }

                handleChange({
                  value,
                  dataIndex,
                  key: record.key,
                  item_id: record.id,
                })

                if (cartonQty > 0) {
                  const updatedRecommendations = calculateRecommendations(
                    value,
                    cartonQty
                  )
                  handleChange({
                    value: updatedRecommendations,
                    dataIndex: "recommendations",
                    key: record.key,
                    item_id: record.id,
                  })
                }
              }}
              style={{ width: "240px" }}
            />


            <button
              className="btn btn-light btn-xs border"
              type="button"
              onClick={() => {
                const newQty = record.qty + 1 // Increase qty by 1
                handleChange({
                  value: newQty,
                  dataIndex,
                  key: record.key,
                  item_id: record.id,
                })

                // Trigger recommendations after increasing qty
                const updatedRecommendations = calculateRecommendations(
                  newQty,
                  cartonQty
                )
                handleChange({
                  value: updatedRecommendations,
                  dataIndex: "recommendations",
                  key: record.key,
                  item_id: record.id,
                })
              }}
            >
              <i className="fas fa-plus"></i>
            </button>
          </div>

          {record?.product_id &&
            record?.qty > 0 &&
            record.recommendations &&
            record.recommendations.length > 0 && (
              <div className="mt-2">
                <strong>Rekomendasi: </strong>
                {record.recommendations.map((rec, index) => (
                  <button
                    key={index}
                    className="btn btn-light btn-xs border ml-2"
                    type="button"
                    onClick={() =>
                      handleChange({
                        value: rec,
                        dataIndex: "qty",
                        key: record.key,
                        item_id: record.id,
                      })
                    }
                  >
                    {rec}
                  </button>
                ))}
              </div>
            )}
        </div>
      </td>
    )
  }

  if (dataIndex === "action") {
    return (
      <td className="ant-table-cell ant-table-cell-fix-right ant-table-cell-fix-right-first sticky right-0 text-center">
        <button
          disabled={data.length < 2}
          onClick={() =>
            handleClick({
              type: "delete",
              key: record.key,
              item_id: record.id,
            })
          }
          type={"button"}
          className={`
          text-white text-sm font-medium text-center 
            ${
              data.length < 2
                ? "bg-gray-700 hover:bg-gray-800"
                : "bg-red-700 hover:bg-red-800"
            }
            focus:ring-4 focus:outline-none focus:ring-red-300 
            px-4 py-2 rounded-lg 
            inline-flex items-center
          `}
        >
          <CloseOutlined />
        </button>
      </td>
    )
  }

  if (dataIndex === "subtotal") {
    return <td>{formatNumber(record[dataIndex])}</td>
  }

  if (dataIndex === "total") {
    return <td>{formatNumber(record[dataIndex])}</td>
  }

  return <td>{record[dataIndex]}</td>
}

export default ProductAdditionalList
