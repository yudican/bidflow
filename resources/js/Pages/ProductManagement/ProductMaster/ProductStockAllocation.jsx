import {
  ArrowRightOutlined,
  CheckOutlined,
  HomeOutlined,
  LoadingOutlined,
} from "@ant-design/icons"
import { Input, Table, Tooltip } from "antd"
import React, { useEffect, useState } from "react"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../../components/layout"
import { productListColumn, productVariantListColumn } from "./config"

const ProductStockAllocation = () => {
  const navigate = useNavigate()
  const { product_id } = useParams()
  const [loading, setLoading] = useState(false)
  const [loadingStock, setLoadingStock] = useState(false)
  const [datas, setDatas] = useState([])
  const [product, setProduct] = useState({})

  const loadData = (
    url = "/api/product-management/product-variant",
    perpage = 20,
    params = { page: 1 }
  ) => {
    setLoading(true)
    axios
      .post(url, { perpage, product_id, ...params })
      .then((res) => {
        const { data } = res.data.data
        const newData = data.map((item) => {
          return {
            ...item,
            id: item.id,
            name: item.name,
            sku: item.sku,
            package_name: item.package_name,
            variant_name: item.variant_name,
            product_image: item?.image_url,
            status: item?.status,
            stock: item?.stock,
          }
        })

        setDatas(newData)
        setLoading(false)
      })
      .catch((e) => setLoading(false))
  }

  const loadDetailProduct = () => {
    if (product_id) {
      axios.get(`/api/product-management/product/${product_id}`).then((res) => {
        const { data } = res.data
        setProduct({
          stock: data.final_stock,
          variant_stock: data.variant_stock,
          warehouses: data.stock_warehouse || [],
        })
      })
    }
  }

  useEffect(() => {
    loadData()
    loadDetailProduct()
  }, [])

  const stockCanAllocate = product.stock - product.variant_stock

  const handleSetStock = (newData = datas) => {
    const checkStock = newData.every((item) => item.stock_add)
    if (!checkStock) {
      toast.error("Mohon isi stock terlebih dahulu")
      return
    }

    // sum stock
    const sumStock = newData.reduce(
      (a, b) => parseInt(a) + parseInt(b.stock_add),
      0
    )

    if (stockCanAllocate > 0) {
      if (sumStock > stockCanAllocate) {
        toast.error(`Stock tidak boleh lebih dari ${stockCanAllocate}`)
        return
      }
    }

    if (stockCanAllocate < 1) {
      toast.error(`Total Stock produk sudah ${product.variant_stock}`)
      return
    }

    setLoadingStock(true)
    const data = newData.map((item) => {
      return {
        id: item.id,
        stock: item.stock_add,
      }
    })

    axios
      .post("/api/product-management/product/set-stock/" + product_id, { data })
      .then((res) => {
        setLoadingStock(false)
        loadData()
        loadDetailProduct()
        toast.success("Stock updated")
      })
      .catch((e) => {
        const { message } = e.response.data
        setLoadingStock(false)
        toast.error(message)
      })
  }

  const rightContent = (
    <div className="flex justify-between items-center">
      {/* <button
        onClick={() => handleSetStock()}
        className="text-white bg-blueColor hover:bg-blueColor/90 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
        disabled={loadingStock}
      >
        {loadingStock ? <LoadingOutlined /> : <CheckOutlined />}
        <span className="ml-2">Simpan Semua</span>
      </button> */}
    </div>
  )

  return (
    <Layout
      title="Product Stock Allocation"
      href="/product-management/product"
      rightContent={rightContent}
    >
      <div className="card">
        <div className="card-body">
          <Table
            scroll={{ x: "max-content" }}
            tableLayout={"auto"}
            dataSource={datas}
            columns={[
              ...productVariantListColumn,
              // {
              //   title: "Stock Master",
              //   dataIndex: "stock_master",
              //   key: "stock_master",
              //   render: (text, record, index) => product?.stock,
              // },
              {
                title: "Stock Variant",
                dataIndex: "final_stock",
                key: "final_stock",
              },
              {
                title: "Stock Off Market",
                dataIndex: "stock_off_market",
                key: "stock_off_market",
              },
              {
                title: "UOM",
                dataIndex: "package_name",
                key: "package_name",
              },
              {
                title: "Variant",
                dataIndex: "variant_name",
                key: "variant_name",
              },
              // {
              //   title: "Add Stock",
              //   dataIndex: "stock_add",
              //   key: "stock_add",
              //   render: (text, record, index) => {
              //     return (
              //       <Tooltip
              //         title={`Sisa Stock Yang dapat diinput adalah ${stockCanAllocate}`}
              //         placement={"left"}
              //       >
              //         <Input
              //           type="number"
              //           value={text}
              //           onChange={(e) => {
              //             const newDatas = [...datas]
              //             newDatas[index].stock_add = e.target.value
              //             setDatas(newDatas)
              //           }}
              //           disabled={product.stock < 1}
              //         />
              //       </Tooltip>
              //     )
              //   },
              // },
              // {
              //   title: "Action",
              //   dataIndex: "action",
              //   key: "action",
              //   render: (text, record, index) => {
              //     return (
              //       <div className="flex justify-between items-center">
              //         <button
              //           onClick={() => {
              //             if (product.stock > 0) {
              //               handleSetStock([record])
              //             }
              //           }}
              //           className="text-white bg-blueColor hover:bg-blueColor/90 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
              //           disabled={product.stock < 1}
              //         >
              //           <CheckOutlined />
              //         </button>
              //       </div>
              //     )
              //   },
              // },
            ]}
            loading={loading || loadingStock}
            pagination={false}
            rowKey="id"
          />
        </div>
      </div>
    </Layout>
  )
}

export default ProductStockAllocation
