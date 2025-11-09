import { LoadingOutlined } from "@ant-design/icons"
import { Button, Card, DatePicker, Form, Input, Select, message } from "antd"
import React, { useEffect, useState } from "react"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import LoadingFallback from "../../components/LoadingFallback"
import ProductListInput from "../../components/ProductListInput"
import DebounceSelect from "../../components/atoms/DebounceSelect"
import Layout from "../../components/layout"
import {
  useGetAddressUserQuery,
  useGetBrandQuery,
  useGetMasterBinQuery,
  useGetProductVariantByTagMutation,
  useGetTaxQuery,
  useGetTopQuery,
  useGetWarehouseQuery,
} from "../../configs/Redux/Services/generalServices"
import {
  useCreateSalesOrderMutation,
  useGetDetailSalesOrderFormQuery,
} from "../../configs/Redux/Services/salesOrderService"
import { getItem } from "../../helpers"
import ContactAddress from "../Contact/ContactAddress"
import {
  handleSearchContact,
  handleSearchSales,
  searchContact,
  searchSales,
} from "./service"

const OrderManualLeadForm = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const role = getItem("role")
  const userData = getItem("user_data", true)
  const { uid_lead = null } = useParams() || {}

  const [products, setProducts] = useState([])
  const [productItems, setProductItems] = useState([
    {
      id: null,
      key: 0,
      product_id: null,
      price: null,
      qty: 1,
      tax_id: null,
      tax_amount: 0,
      tax_percentage: 0,
      discount_percentage: 0,
      discount: 0,
      discount_amount: 0,
      subtotal: null,
      price_nego: null,
      total: 0,
      margin_price: 0,
      stock: 0,
    },
  ])
  const [billingData, setBilingData] = useState([])
  const [contactList, setContactList] = useState([])
  const [showContact, setShowContact] = useState(false)
  const [salesList, setSalesList] = useState([])
  const [showBilling, setShowBilling] = useState(false)
  const [showBin, setShowBin] = useState(false)
  const [status, setStatus] = useState(0)

  const [selectedAddress, setSelectedAddress] = useState(null)
  const [seletedContact, setSeletedcontact] = useState(null)

  const { data: taxs } = useGetTaxQuery()
  const { data: warehouses } = useGetWarehouseQuery()
  const { data: masterBin } = useGetMasterBinQuery()
  const { data: termOfPayments } = useGetTopQuery()
  const { data: brands, isLoading: loadingBrand } = useGetBrandQuery()
  const {
    data: userAddressList,
    isLoading: loadingAddress,
    refetch: refetchAddress,
  } = useGetAddressUserQuery(seletedContact?.value)

  const {
    data: detail,
    isLoading: loading,
    isSuccess,
    isFetching,
  } = useGetDetailSalesOrderFormQuery(`/api/order-manual/detail/${uid_lead}`)

  const [getProducts, { isloading: productLoading }] =
    useGetProductVariantByTagMutation()

  const [createSalesOrder, { isLoading: loadingSubmit }] =
    useCreateSalesOrderMutation()

  const loadProducts = async (warehouse_id = 2) => {
    await getProducts().then(({ error, data }) => {
      if (data) {
        const newData = data.map((item) => {
          const stock_warehouse =
            (item?.stock_warehouse &&
              item?.stock_warehouse?.length > 0 &&
              item?.stock_warehouse) ||
            []
          const stock_off_market = stock_warehouse.find(
            (item) => item.id == warehouse_id
          )

          const canBuy =
            stock_off_market?.stock < item?.qty_bundling ? true : false

          return {
            ...item,
            stock_off_market: stock_off_market?.stock || 0,
            // disabled: stock < 1 ? true : false,
          }
        })
        setProducts(newData)
      }
    })
  }

  const handleGetContact = async () => {
    await searchContact(null).then((results) => {
      const newResult = results.map((result) => {
        return { label: result.nama, value: result.id }
      })
      setContactList(newResult)
    })
  }

  const handleGetSales = async () => {
    await searchSales(null).then((results) => {
      const newResult = results.map((result) => {
        return { label: result.nama, value: result.id }
      })

      setSalesList(newResult)
    })
  }

  const fetchData = async () => {
    await handleGetContact()
    await handleGetSales()
  }

  useEffect(() => {
    fetchData()
    form.setFieldValue("created_by", userData?.name)
  }, [])

  useEffect(() => {
    if (isSuccess || isFetching) {
      loadProducts(detail?.warehouse_id)
      setProductItems(detail?.product_needs)
      const forms = detail?.forms || {}
      form.setFieldsValue(forms) // update to fix suspected form error

      if (forms.payment_terms) {
        setShowBilling(forms.payment_terms === 4 ? true : false)
      }

      // if (forms.master_bin_id) {
      //   setShowBin(forms.master_bin_id ? true : false)
      // }
      setSeletedcontact(forms?.contact)

      if (detail?.contact) {
        setShowContact(detail?.contact ? true : false)
      }

      setBilingData(detail?.billings)
    }
  }, [isSuccess, isFetching])

  const onFinish = (values) => {
    // validate product
    const hasProduct = productItems.every((item) => item.product_id)
    if (!hasProduct) {
      return toast.error("Product harus diisi", {
        position: toast.POSITION.TOP_RIGHT,
      })
    }

    // validate price
    // const hasPriceNego = productItems.every((item) => item.price_nego)
    // if (!hasPriceNego) {
    //   setLoadingSubmit(false)
    //   return toast.error("Price Nego harus diisi", {
    //     position: toast.POSITION.TOP_RIGHT,
    //   })
    // }

    // if (!selectedAddress) {
    //   toast.error("Alamat Belum Dipilih", {
    //     position: toast.POSITION.TOP_RIGHT,
    //   });
    // }

    const hasQty = productItems.every((item) => item.qty > 0)
    if (!hasQty) {
      return toast.error("Minimal Pembalian adalah 1", {
        position: toast.POSITION.TOP_RIGHT,
      })
    }

    const billingStatus = showBilling ? 1 : 1
    const status_save = status < 0 ? status : billingStatus
    const body = {
      uid_lead,
      ...values,
      status: status_save,
      address_id: selectedAddress,
      contact: values.contact.value,
      sales: values.sales.value,
      product_items: productItems,
      account_id: getItem("account_id"),
      expired_at: values.expired_at.format("YYYY-MM-DD"),
      type: "manual",
    }
    createSalesOrder({ url: "/api/order-manual/form/save", body }).then(
      ({ error, data }) => {
        if (error) {
          return toast.error(error?.message)
        }
        setProductItems([
          {
            id: null,
            key: 0,
            product_id: null,
            price: null,
            qty: 1,
            tax_id: null,
            tax_amount: 0,
            tax_percentage: 0,
            discount_percentage: 0,
            discount: 0,
            discount_amount: 0,
            subtotal: null,
            price_nego: null,
            total: 0,
            margin_price: 0,
            stock: 0,
          },
        ])
        toast.success(data.message)
        return navigate("/order/order-manual")
      }
    )
  }

  if (loading) {
    return (
      <Layout title="Detail" href="/order/order-manual">
        <LoadingFallback />
      </Layout>
    )
  }
  console.log(productItems, "productItems")
  return (
    <Layout
      title="Order Manual Form "
      href="/order/order-manual"
      // rightContent={rightContent}
    >
      <Form
        form={form}
        name="basic"
        layout="vertical"
        onFinish={onFinish}
        //   onFinishFailed={onFinishFailed}
        autoComplete="off"
      >
        <Card title="Form Order">
          <div className="card-body grid grid-cols-2 gap-x-4">
            <Form.Item
              label="Type Customer"
              name="type_customer"
              rules={[
                {
                  required: true,
                  message: "Silahkan Masukkan Type Customer!",
                },
              ]}
            >
              <Select
                showSearch
                filterOption={(input, option) => {
                  return (option?.children ?? "")
                    .toLowerCase()
                    .includes(input.toLowerCase())
                }}
                placeholder="Pilih Type Customer"
              >
                <Select.Option value={"new"} key={"new"}>
                  New Customer
                </Select.Option>
                <Select.Option value={"existing"} key={"existing"}>
                  Existing Customer
                </Select.Option>
              </Select>
            </Form.Item>

            <Form.Item
              label="Contact"
              name="contact"
              rules={[
                {
                  required: true,
                  message: "Silahkan Masukkan Contact!",
                },
              ]}
            >
              <DebounceSelect
                showSearch
                placeholder="Cari Contact"
                fetchOptions={handleSearchContact}
                filterOption={false}
                defaultOptions={contactList}
                className="w-full"
                onChange={(e) => {
                  setSeletedcontact(e)
                  setShowContact(true)
                }}
                dropdownRender={(menu) => (
                  <>
                    {menu}
                    <div className="py-1 flex w-full items-center justify-center">
                      <Button
                        className=""
                        type="text"
                        onClick={() => {
                          navigate("/contact/create")
                        }}
                      >
                        <strong className="text-blue-500">+ Add Contact</strong>
                      </Button>
                    </div>
                  </>
                )}
              />
            </Form.Item>

            <Form.Item
              label="Sales"
              name="sales"
              rules={[
                {
                  required: true,
                  message: "Silahkan Masukkan Sales!",
                },
              ]}
            >
              <DebounceSelect
                disabled={role === "sales"}
                defaultOptions={
                  role === "sales"
                    ? [{ label: userData.name, value: userData.id }]
                    : salesList
                }
                showSearch
                placeholder="Cari Sales"
                fetchOptions={handleSearchSales}
                filterOption={false}
                className="w-full"
              />
            </Form.Item>

            <Form.Item
              label="Brand"
              name="brand_id"
              rules={[
                {
                  required: true,
                  message: "Silahkan Masukkan Brand!",
                },
              ]}
            >
              <Select
                showSearch
                filterOption={(input, option) => {
                  return (option?.children ?? "")
                    .toLowerCase()
                    .includes(input.toLowerCase())
                }}
                placeholder="Pilih Brand"
                loading={loadingBrand}
              >
                {brands &&
                  brands.map((brand) => (
                    <Select.Option value={brand.id} key={brand.id}>
                      {brand.name}
                    </Select.Option>
                  ))}
              </Select>
            </Form.Item>

            <Form.Item
              label="Warehouse"
              name="warehouse_id"
              rules={[
                {
                  required: true,
                  message: "Silahkan Masukkan Warehouse!",
                },
              ]}
            >
              <Select
                showSearch
                filterOption={(input, option) => {
                  return (option?.children ?? "")
                    .toLowerCase()
                    .includes(input.toLowerCase())
                }}
                placeholder="Pilih Warehouse"
                onChange={(e) => {
                  loadProducts(e)
                  // setProductItems(pro)
                }}
              >
                {warehouses &&
                  warehouses.map((warehouse) => (
                    <Select.Option value={warehouse.id} key={warehouse.id}>
                      {warehouse.name}
                    </Select.Option>
                  ))}
              </Select>
            </Form.Item>

            <Form.Item
              label="Payment Term"
              name="payment_terms"
              rules={[
                {
                  required: true,
                  message: "Silahkan Masukkan Payment Term!",
                },
              ]}
            >
              <Select
                showSearch
                filterOption={(input, option) => {
                  return (option?.children ?? "")
                    .toLowerCase()
                    .includes(input.toLowerCase())
                }}
                placeholder="Pilih Payment Term"
                onChange={(e) => {
                  setShowBilling(e === 4 ? true : false)
                  // setShowBin(e === 3 ? true : false)
                }}
              >
                {termOfPayments &&
                  termOfPayments.map((top) => (
                    <Select.Option value={top.id} key={top.id}>
                      {top.name}
                    </Select.Option>
                  ))}
              </Select>
            </Form.Item>

            <Form.Item label="No Preference" name="preference_number">
              <Input placeholder="Ketik No Preference" />
            </Form.Item>

            {/* {showBin && (
              <Form.Item
                label="Lokasi BIN"
                name="master_bin_id"
                rules={[
                  {
                    required: true,
                    message: "Silahkan Masukkan Warehouse!",
                  },
                ]}
              >
                <Select
                  showSearch
                  filterOption={(input, option) => {
                    return (option?.children ?? "")
                      .toLowerCase()
                      .includes(input.toLowerCase())
                  }}
                  placeholder="Pilih Lokasi BIN"
                >
                  {masterBin &&
                    masterBin
                      .filter((item) =>
                        item?.users?.some(
                          (user) => user?.id === seletedContact.value
                        )
                      )
                      .map((bin) => (
                        <Select.Option value={bin.id} key={bin.id}>
                          {bin.name}
                        </Select.Option>
                      ))}
                </Select>
              </Form.Item>
            )} */}

            <Form.Item
              label="Expired SO"
              name="expired_at"
              rules={[
                {
                  required: true,
                  message: "Silahkan Masukkan Expired SO!",
                },
              ]}
            >
              <DatePicker className="w-full" format={"DD-MM-YYYY"} />
            </Form.Item>

            <Form.Item label="Created By" name="created_by">
              <Input placeholder="Created By" disabled />
            </Form.Item>

            <Form.Item
              className={showBin ? "col-span-2" : ""}
              label="Customer Need"
              name="customer_need"
            >
              {showBin ? (
                <Input.TextArea placeholder="Ketik Customer Need" />
              ) : (
                <Input placeholder="Ketik Customer Need" />
              )}
            </Form.Item>
          </div>
        </Card>
      </Form>

      {showContact && (
        <ContactAddress
          title="Address Information "
          data={userAddressList?.address || []}
          loading={loadingAddress}
          contact={{
            id: seletedContact?.value,
            name: seletedContact?.label,
          }}
          refetch={() => refetchAddress()}
        />
      )}

      <Card title="Detail Product" className="mt-4">
        <ProductListInput
          initialValues={productItems}
          products={products}
          loading={productLoading}
          onChange={(items) => setProductItems(items)}
          taxs={taxs}
        />
      </Card>

      {/* {showBilling && (
        <div className="card mt-8">
          <div className="card-header flex justify-between items-center">
            <h1 className="header-title">Informasi Penagihan</h1>
            <ModalBilling
              detail={{ ...detail, uid_lead }}
              refetch={() => {}}
              user={userData}
            />
          </div>
          <div className="card-body">
            <Table
              dataSource={billingData}
              columns={[...billingColumns]}
              loading={loading}
              pagination={false}
              rowKey="id"
              scroll={{ x: "max-content" }}
              tableLayout={"auto"}
            />
          </div>
        </div>
      )} */}

      <div className="float-right">
        <div className="  w-full mt-6 p-4 flex flex-row">
          {!detail && (
            <button
              onClick={() => {
                if (loadingSubmit) {
                  return null
                }
                setStatus(-1)
                setTimeout(() => {
                  console.log("status", status)
                  form.submit()
                }, 1000)
              }}
              className={`text-blue bg-white hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 border font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mr-2`}
            >
              {loadingSubmit ? (
                <LoadingOutlined />
              ) : (
                <span className="ml-2">Save Draft</span>
              )}
            </button>
          )}
          <button
            onClick={() => {
              if (loadingSubmit) {
                return null
              }
              // if (detail?.amount < 1) {
              //   return message.error("Anda belum input harga");
              // }
              setStatus(1)
              setTimeout(() => {
                form.submit()
              }, 1000)
            }}
            className={`text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center`}
          >
            {loadingSubmit ? (
              <LoadingOutlined />
            ) : (
              <span className="ml-2">Save Order</span>
            )}
          </button>
        </div>
      </div>
    </Layout>
  )
}

export default OrderManualLeadForm
