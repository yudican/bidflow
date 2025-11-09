import { DownCircleOutlined } from "@ant-design/icons"
import { Form, message, Modal, Pagination, Select, Table } from "antd"
import axios from "axios"
import React, { useState } from "react"
import { getItem } from "../../../helpers"
import { inventoryTransferStockKonsinyasiColumns } from "../config"
import FilterBin from "./FilterBin"
const DownloadImportTemplate = ({ handleOk, refetch }) => {
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [isModalOpenProduct, setIsModalOpenProduct] = useState(false)

  const [form] = Form.useForm()
  // attachments
  const [loadingData, setLoadingData] = useState(false)
  const [search, setSearch] = useState("")
  const [isSearch, setIsSearch] = useState(false)
  const [loadingDownload, setLoadingDownload] = useState(false)
  const [productLoading, setProductLoading] = useState(false)
  const [currentPage, setCurrentPage] = useState(1)
  const [total, setTotal] = useState(0)
  const [inventoryData, setInventoryData] = useState([])
  const [inventoryDataWithItem, setInventoryDataWithItem] = useState([])
  const [selectedRowKeys, setSelectedRowKeys] = useState([])
  const [productSelected, setProductSelected] = useState([])
  const [selectedBin, setSelectedBin] = useState(null)
  const [products, setProducts] = useState([])

  const loadInventoryData = (
    url = "/api/inventory/order-konsinyasi/template",
    perpage = 10,
    params = {}
  ) => {
    setLoadingData(true)
    axios
      .post(url, {
        perpage,
        account_id: getItem("account_id"),
        ...params,
        inventory_type: "konsinyasi",
        inventory_status: "done",
        status_so: 1,
      })
      .then((res) => {
        const { data, total, from, current_page } = res.data.data
        setTotal(total)
        setCurrentPage(current_page)
        const newData = data.map((item, index) => {
          return {
            ...item,
            number: from + index,
          }
        })

        setInventoryData(newData)
        setLoadingData(false)
      })
      .catch(() => setLoadingData(false))
  }

  const loadInventoryItemData = (inventory_id, record) => {
    setLoadingData(true)
    axios
      .get("/api/inventory/order-konsinyasi/template/" + record?.master_bin_id)
      .then((res) => {
        const { data } = res.data

        const newProduct = data.map((item) => {
          // Filter produk yang sesuai
          const matchingProducts = products.filter(
            (row) => row.product_id === item.product_id
          )

          // Cek produk yang sudah tersimpan sebelumnya di inventoryDataWithItem
          const savedProduct = inventoryDataWithItem.find(
            (row) =>
              row.product_id === item.product_id &&
              row.master_bin_id === record.master_bin_id
          )

          let productDetails = {
            product_variant_id: null,
            product_name: null,
            selected: false,
            qty: item.qty,
          }

          // Jika ada di inventoryDataWithItem, gunakan data yang sudah tersimpan
          if (savedProduct) {
            productDetails = {
              product_variant_id: savedProduct.product_variant_id,
              product_name: savedProduct.product_name,
              selected: true,
              qty: savedProduct.qty,
            }
          }
          // Jika matching product hanya 1, otomatis pilih
          else if (matchingProducts.length === 1) {
            const product = matchingProducts[0]
            const stock_bin = product?.stock_bins || []
            const stockQty = stock_bin.find(
              (itemStock) => itemStock.id === record?.master_bin_id
            )?.stock

            // if (stockQty > 0) {
            productDetails = {
              product_variant_id: product.id,
              product_name: product.name,
              selected: true,
              qty: stockQty,
            }
            // }
          }

          return {
            ...item,
            ...productDetails,
            master_bin_id: record.master_bin_id, // Tambahkan master_bin_id
          }
        })

        setProductSelected(newProduct)
        setLoadingData(false)
      })
      .catch((error) => {
        console.error("Error loading inventory item data:", error)
        setLoadingData(false)
      })
  }

  const handleChange = (page, pageSize = 10) => {
    loadInventoryData(
      `/api/inventory/order-konsinyasi/template/?page=${page}`,
      pageSize,
      {
        search,
      }
    )
  }

  const handleChangeSearch = () => {
    setIsSearch(true)
    loadInventoryData(`/api/inventory/order-konsinyasi/template`, 10, {
      search,
      master_bin_id: selectedBin?.value,
    })
  }

  const loadProducts = async (master_bin_id) => {
    setProductLoading(true)
    await axios
      .get("/api/master/products/sales-offline")
      .then((res) => {
        const { data } = res.data

        // const newData = data.map((item) => {
        //   const stock_bin = item?.stock_bins || []
        //   const stock_off_market = stock_bin.find(
        //     (itemStock) => itemStock.id == master_bin_id
        //   )?.stock

        //   return {
        //     ...item,
        //     stock_off_market: stock_off_market || 0,
        //     final_stock: stock_off_market || 0,
        //   }
        // })
        setProducts(data)
        setProductLoading(false)
      })
      .catch(() => setProductLoading(false))
  }

  const downloadTemplate = () => {
    setLoadingDownload(true)
    axios
      .post("/api/inventory/order-konsinyasi/download/template", {
        data: inventoryDataWithItem.filter((item) => item.selected),
      })
      .then((res) => {
        setLoadingDownload(false)
        setInventoryData([])
        setInventoryDataWithItem([])
        setSelectedRowKeys([])
        setProductSelected([])
        setIsModalOpenProduct(false)
        setIsModalOpen(false)
        message.success("Template berhasil di download")
        window.open(res.data.data)
      })
      .catch(() => setLoadingDownload(false))
  }

  const showModal = () => {
    setIsModalOpen(true)
    loadInventoryData()
    loadProducts()
  }

  const handleCancel = () => {
    setIsModalOpen(false)
  }

  const rowSelection = {
    selectedRowKeys,
    onChange: (e) => {
      setSelectedRowKeys(e)
    },
    getCheckboxProps: (record) => {
      return {
        disabled: true,
      }
    },
  }
  console.log(selectedRowKeys, "selectedRowKeys")
  return (
    <div className="w-full">
      <button onClick={() => showModal()}>
        <DownCircleOutlined />
        <span className="ml-2">Download Template</span>
      </button>

      <Modal
        title="Download Template Import Order Konsinyasi"
        open={isModalOpen}
        onOk={() => downloadTemplate()}
        cancelText={"Batal"}
        onCancel={handleCancel}
        okText={"Download"}
        width={1000}
        okButtonProps={{
          disabled: selectedRowKeys?.length < 1,
          loading: loadingDownload,
        }}
      >
        <div className="w-full">
          <p className="alert alert-info">
            Sebelum melakukan import pastikan sudah sesuai template yang telah
            disediakan, jika belum silakan melakukan download terlebih dahulu
            dengan memilih Destinasi BIN yang akan di import.
          </p>
          <div className="py-4">
            <div class="grid grid-cols-1 gap-4 ">
              <div class="text-white">
                <FilterBin
                  onChange={(value) => {
                    setSelectedBin(value)
                    loadInventoryData(
                      `/api/inventory/order-konsinyasi/template`,
                      10,
                      {
                        master_bin_id: value?.value,
                      }
                    )
                  }}
                  selected={selectedBin}
                />
              </div>
              {/* <div class="text-white">
                <Input
                  placeholder="Cari Nomor SO"
                  className="rounded w-full"
                  onPressEnter={() => handleChangeSearch()}
                  suffix={
                    isSearch ? (
                      <CloseCircleFilled
                        onClick={() => {
                          loadInventoryData()
                          setSearch(null)
                          setIsSearch(false)
                        }}
                      />
                    ) : (
                      <SearchOutlined onClick={() => handleChangeSearch()} />
                    )
                  }
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                />
              </div> */}
            </div>
          </div>
          <Table
            rowSelection={rowSelection}
            scroll={{ x: "max-content" }}
            tableLayout={"auto"}
            dataSource={inventoryData}
            columns={[
              ...inventoryTransferStockKonsinyasiColumns,
              {
                title: "Aksi",
                dataIndex: "action",
                key: "action",
                fixed: "right",
                render: (text, record) => (
                  <button
                    className="text-blue-700 border-[1px] border-blue-700 hover:bg-blue-800/10 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mx-2"
                    onClick={(e) => {
                      setIsModalOpenProduct(true)
                      loadInventoryItemData(record.id, record)
                      console.log(productSelected, "productSelected")
                    }}
                  >
                    <span className="mr-2">Pilih Produk</span>
                  </button>
                ),
              },
            ]}
            loading={loadingData || productLoading}
            pagination={false}
            rowKey="id"
          />
          <Pagination
            defaultCurrent={1}
            current={currentPage}
            total={total}
            className="mt-4 text-center"
            onChange={handleChange}
            pageSizeOptions={["10", "20", "50", "100", "200", "500"]}
          />
        </div>
      </Modal>

      {/* modal select product */}
      <Modal
        title="Pilih Product Variant"
        open={isModalOpenProduct}
        cancelText={"Tutup"}
        onCancel={() => {
          setIsModalOpenProduct(false)
        }}
        okText={"Simpan"}
        onOk={() => {
          const hasSelectedProduct = productSelected.some(
            (item) => item.product_name && item.selected
          )

          if (!hasSelectedProduct) {
            return message.error("Mohon pilih produk terlebih dahulu")
          }

          // Filter hanya yang selected dan punya qty
          const validProducts = productSelected.filter(
            (product) => product.selected
          )

          if (validProducts.length === 0) {
            return message.error("Tidak ada produk valid yang dipilih")
          }

          // Update selected row keys
          const newKeys = validProducts.map((item) => item.master_bin_id)
          setSelectedRowKeys((prev) => {
            const uniqueKeys = new Set([...prev, ...newKeys])
            return Array.from(uniqueKeys)
          })

          // Update inventory data - replace existing data with same master_bin_id
          setInventoryDataWithItem((prev) => {
            const filtered = prev.filter(
              (item) =>
                !validProducts.some(
                  (vp) => vp.master_bin_id === item.master_bin_id
                )
            )
            return [...filtered, ...validProducts]
          })

          setIsModalOpenProduct(false)
        }}
        width={800}
      >
        <div className="w-full">
          <Table
            scroll={{ x: "max-content" }}
            tableLayout={"auto"}
            dataSource={productSelected}
            columns={[
              {
                title: "No",
                dataIndex: "id",
                key: "id",
                render: (text, record, index) => index + 1,
                width: 10,
              },
              {
                title: "Product",
                dataIndex: "produk",
                key: "produk",
                render: (text, record, index) => {
                  const matchingProducts = products.filter(
                    (row) => row.product_id === record.product_id
                  )

                  return (
                    <Select
                      className="w-full"
                      placeholder="Pilih Product Variant"
                      value={record.product_variant_id || undefined}
                      onChange={(value) => {
                        const selectedProduct = products?.find(
                          (row) => row.id === value
                        )
                        if (!selectedProduct) return

                        const newProduct = [...productSelected]
                        const stock_bin = selectedProduct?.stock_bins || []
                        const qty =
                          stock_bin.find(
                            (itemStock) =>
                              itemStock.id === record?.master_bin_id
                          )?.stock || record.qty

                        newProduct[index] = {
                          ...newProduct[index],
                          product_variant_id: selectedProduct.id,
                          product_name: selectedProduct.name,
                          selected: true,
                          qty,
                        }

                        setProductSelected(newProduct)
                      }}
                    >
                      {matchingProducts.map((item) => {
                        const stock =
                          item?.stock_bins?.find(
                            (bin) => bin.id === record?.master_bin_id
                          )?.stock || 0

                        return (
                          <Select.Option
                            key={item.id}
                            value={item.id}
                            // disabled={stock <= 0}
                          >
                            {item.name}
                          </Select.Option>
                        )
                      })}
                    </Select>
                  )
                },
              },
              {
                title: "QTY",
                dataIndex: "qty",
                key: "qty",
                width: 10,
                render: (text, record) => {
                  if (record?.product_variant_id) {
                    return record?.qty
                  }
                  return "-"
                },
              },
            ]}
            loading={productLoading || loadingData}
            pagination={false}
            rowKey="key"
          />
        </div>
      </Modal>
    </div>
  )
}

export default DownloadImportTemplate
