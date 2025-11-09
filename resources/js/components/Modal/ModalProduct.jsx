import { SearchOutlined } from "@ant-design/icons"
import { Empty, Input, Modal, Tag, Tooltip } from "antd"
import React, { useState } from "react"
import { capitalizeString, inArray, getItem } from "../../helpers"

export const typeWordingChange = (type) => {
  return type === "product" ? "Item" : type
}

const ModalProduct = ({
  products,
  productNeedSelected = [],
  handleChange,
  value,
  disabled = false,
  type = "product",
  warehouse = false,
  stock = "final_stock",
  style,
}) => {

  console.log('wh modal produk', warehouse)
  const newProductExcludeSelected =
    products?.filter(
      (value) =>
        !productNeedSelected.map((item) => item.product_id).includes(value.id)
    ) || []
  // console.log(newProductExcludeSelected, "newProductExcludeSelected")
  const [isModalProductListVisible, setIsModalProductListVisible] =
    useState(false)
  const [selectedProduct, setSelectedProduct] = useState(null)
  const [search, setSearch] = useState("")
  console.log(products, "products")
  const newProducts = [...newProductExcludeSelected]
  newProducts.sort((a, b) => b[stock] - a[stock])

  const lowerCaseSearch = search.toLowerCase();

  const filteredProducts =
    newProducts.filter((value) => value.name.toLowerCase().includes(lowerCaseSearch)) ||
    newProducts
  // console.log(filteredProducts, "fil")
  const title = products?.find((product) => product?.id === value)?.name
  const isSelectedProductStockEmpty =
    filteredProducts?.find((product) => product?.id === selectedProduct)
      ?.final_stock <= 0

  const show = inArray(getItem("role"), ["superadmin"])
  const wh = warehouse
  return (
    <div>
      {disabled ? (
        <div
          style={style}
          className="w-96 flex items-center border py-1 px-2 rounded-sm line-clamp-1 cursor-pointer bg-[#F5F5F5] text-[#BBBBBB]"
        >
          <SearchOutlined className="mr-2" />
          <span>
            {value
              ? title
              : `Pilih ${capitalizeString(typeWordingChange(type))}`}
          </span>
        </div>
      ) : (
        <Tooltip title={title}>
          <div
            style={style}
            className="w-96 flex items-center border py-1 px-2 rounded-sm line-clamp-1 cursor-pointer"
            // onClick={() => setIsModalProductListVisible(wh)}
            onClick={() => {
              console.log("cek wh", wh)
              if (wh) {
                setIsModalProductListVisible(true)
              } else {
                setIsModalProductListVisible(false)
              }
            }}
          >
            <SearchOutlined className="mr-2" />
            <span>
              {value
                ? title
                : `Pilih ${capitalizeString(typeWordingChange(type))}`}
            </span>
          </div>
        </Tooltip>
      )}

      <Modal
        title={`Daftar ${capitalizeString(typeWordingChange(type))}`}
        open={isModalProductListVisible}
        cancelText={"Batal"}
        okText={"Pilih"}
        // okButtonProps={{ disabled: isSelectedProductStockEmpty }}
        onOk={() => {
          handleChange(selectedProduct)
          setIsModalProductListVisible(false)
          // console.log("selectedProduct", selectedProduct)
        }}
        onCancel={() => setIsModalProductListVisible(false)}
        width={900}
        bodyStyle={{ height: "32rem", overflowY: "scroll" }}
      >
        <div>
          <Input
            placeholder="Cari produk disini.."
            size={"large"}
            className="rounded mb-4"
            suffix={<SearchOutlined />}
            value={search}
            onChange={(e) => setSearch(e.target.value)}

          />
          {filteredProducts?.length === 0 && productNeedSelected && (
            <Empty description="Product tidak tersedia, atau product sebelumnya sudah dipilih" />
          )}
          {filteredProducts.map((product) => {
            // const isEmptyFinalStock = product[stock] <= 0
            const vendorKarton = product?.carton_vendor || "-";
            const maskedVendor = show
              ? vendorKarton
              : vendorKarton.length > 2
                ? vendorKarton.slice(0, 2) + "*".repeat(vendorKarton.length - 2)
                : vendorKarton;
            return (
              <div
                key={product.id}
                className={`
                mb-4 shadow-none rounded-md p-2 cursor-pointer bg-white
                ${selectedProduct == product.id
                    ? "border-[1px] border-blue-400 drop-shadow-md ring-blue-500"
                    : "border border-gray-400"
                  }
              `}
                onClick={() => {
                  if (
                    inArray(type, ["product", "pengemasan", "perlengkapan"]) // enable click on any type
                  ) {
                    return setSelectedProduct(product.id)
                  }
                  product[stock] > 0 && setSelectedProduct(product.id)
                }}
              // disabled={product.stock === 0}
              >
                <div className="flex max-w-[800px] justify-between items-center">
                  <div className="flex items-center">
                    {!inArray(type, ["pengemasan", "perlengkapan"]) && ( // ^ hide img when type is not product ^
                      <img
                        src={product.image_url}
                        alt="product_photo"
                        className={`mr-4 w-20 h-20 rounded-md border`}
                      />
                    )}
                    <div>
                      <div className="block text-lg line-clamp-1 font-medium max-w-2xl">
                        {product.name}{" "}
                      </div>
                      <br />
                      {type === "product" &&
                        <>
                          <div className="block">
                            Tersedia di :
                            {product?.sales_channels?.map((value, index) => (
                              <Tag key={index} color="lime">
                                {value}
                              </Tag>
                            ))}
                          </div>
                          <div className="block">
                            <span>Produk Karton : {product?.carton_name || '-'}</span>
                          </div>
                          <div className="block">
                            <span>Vendor Karton : {maskedVendor}</span>
                          </div>
                        </>
                      }
                    </div>
                  </div>
                  {/* {product[stock] && ( */}
                  {type === "product" &&
                    <div className={`block text-red-500`}>
                      Stock Tersedia: {product[stock]}
                    </div>
                  }
                  {/* )} */}
                  {inArray(type, ["pengemasan", "perlengkapan"]) && (
                    <div className="block">Sku: {product?.sku}</div>
                  )}
                </div>
              </div>
            )
          })}
        </div>
      </Modal>
    </div>
  )
}

export default ModalProduct
