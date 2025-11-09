import {
  CheckCircleOutlined,
  CloseCircleFilled,
  DownOutlined,
  LoadingOutlined,
  PlusOutlined,
  SearchOutlined,
} from "@ant-design/icons"
import { Checkbox, Dropdown, Input, Menu, Pagination, Table } from "antd"
import axios from "axios"
import React, { useEffect, useMemo, useState } from "react"
import { useNavigate } from "react-router-dom"
import { toast } from "react-toastify"
import ModalOrderExport from "../../components/Modal/ModalOrderExport"
import Layout from "../../components/layout"
import {
  useGetSalesChannelQuery,
  useGetSalesOrderQuery,
  useSubmitBillingToGpMutation,
  useSubmitSIToGpMutation,
} from "../../configs/Redux/Services/salesOrderService"
import {
  createQueryString,
  formatNumber,
  getItem,
  inArray,
} from "../../helpers"
import ModalTax from "../Genie/Components/ModalTax"
import FilterModal from "./Components/FilterModal"
import ImportModal from "./Components/ImportModal"
import { orderFreebiesListColumn } from "./config"
import ProgressImportData from "../../components/ProgressImportData"

const OrderFreebiesList = () => {
  const navigate = useNavigate()
  const userData = getItem("user_data", true)
  const filteredRoleShow = useMemo(
    () => inArray(getItem("role"), ["adminsales"]),
    []
  )
  const isFilteredAdminSalesLocalStorage = getItem(
    "is_filtered_admin_sales",
    true
  )
  const [isFilteredAdminSales, setIsFilteredAdminSales] = useState(
    isFilteredAdminSalesLocalStorage || false
  )

  const [paramUrl, setParamUrl] = useState("/api/sales-order")
  const {
    data: salesOrderData,
    isLoading: salesOrderLoading,
    isFetching: salesOrderFetching,
    refetch: salesOrderRefetch,
  } = useGetSalesOrderQuery(paramUrl)

  const { data: channelList, isLoading: loadingSalesChannel } =
    useGetSalesChannelQuery("freebies")

  const [submitSiToGP, { isLoading: loadingSubmitSI }] =
    useSubmitSIToGpMutation()
  const [submitBillingToGP, { isLoading: loadingSubmitBilling }] =
    useSubmitBillingToGpMutation()

  const [search, setSearch] = useState("")
  const [isSearch, setIsSearch] = useState(false)
  const [currentPage, setCurrentPage] = useState(1)
  const [perPage] = useState(10)
  const [filterData, setFilterData] = useState({})
  const [selectedRowKeys, setSelectedRowKeys] = useState([])
  const [readyToSubmit, setReadyToSubmit] = useState(false)
  const [selectedProducts, setSelectedProducts] = useState([])
  const [selectedBillings, setSelectedBillings] = useState([])
  const [progressData, setProgressData] = useState(null)
  const [selectedChannel, setselectedChannel] = useState(null)

  const loadOrder = (
    url = "/api/sales-order",
    perpage = perPage,
    params = { page: currentPage }
  ) => {
    const bodyData = {
      perpage,
      type: "freebies",
      account_id: parseInt(getItem("account_id")),
      ...params,
    }

    if (params?.contact) {
      bodyData.contact = params?.contact?.value
    }

    if (params?.sales) {
      bodyData.sales = params?.sales?.value
    }

    Object.keys(bodyData).forEach((value) => {
      if (Array.isArray(bodyData[value])) {
        bodyData[value] = bodyData[value].join(",")
      } else {
        bodyData[value] = bodyData[value]
      }
    })

    const cleanedData = Object.fromEntries(
      Object.entries(bodyData).filter(
        ([key, value]) => value !== null && value !== undefined
      )
    )

    const queryString = createQueryString(cleanedData)
    setParamUrl(`${url}${queryString}`)
  }

  useEffect(() => {
    if (filteredRoleShow) {
      setIsFilteredAdminSales(true)
      localStorage.setItem("is_filtered_admin_sales", true)
      let data = {
        contact: null,
        created_at: null,
        print_status: null,
        resi_status: null,
        status: null,
        user_created: userData?.id,
      }
      setFilterData(data)
      handleFilter(data)
    } else {
      loadOrder()
    }
  }, [filteredRoleShow])

  const handleChange = (page, pageSize = 10) => {
    setCurrentPage(page)
    loadOrder(`/api/sales-order`, pageSize, {
      search,
      page,
      account_id: getItem("account_id"),
      ...filterData,
    })
  }

  const handleChangeSearch = () => {
    setIsSearch(true)
    loadOrder(`/api/sales-order`, 10, { search })
  }

  const handleFilter = (data) => {
    setFilterData(data)
    loadOrder(`/api/sales-order`, 10, data)
  }

  const handleSubmitGp = (value) => {
    const hasLocNode = selectedProducts.every((item) => item.loc_node)
    if (!hasLocNode) {
      return toast.error("Lokasi Site ID harus diisi")
    }
    submitSiToGP({
      url: `/api/order/sales-order/submit`,
      body: {
        ids: selectedRowKeys,
        type: "sales-order",
        ...value,
        products: selectedProducts,
      },
    }).then(({ data }) => {
      if (data) {
        setReadyToSubmit(false)
        setSelectedRowKeys([])
        setSelectedProducts([])
        return toast.success("Data order berhasil di submit!")
      }

      return toast.error("Error submitting order lead")
    })
  }

  const handleSubmitBillingGp = (value) => {
    submitBillingToGP({
      url: `/api/order/manual/invoice/submit`,
      body: {
        ids: selectedRowKeys,
        ...value,
        billings: selectedBillings,
      },
    }).then((res) => {
      setReadyToSubmit(false)
      setSelectedRowKeys([])
      setSelectedBillings([])
      return toast.success("Data order berhasil di submit!")
    })
  }

  const handleFilterChecked = (e) => {
    if (e.target.checked === false) {
      // when unchecked
      setIsFilteredAdminSales(e.target.checked)
      localStorage.setItem("is_filtered_admin_sales", e.target.checked)
      let data = {
        contact: null,
        created_at: null,
        print_status: null,
        resi_status: null,
        status: null,
        user_created: null,
      }
      setFilterData(data)
      loadOrder()
    } else {
      // when checked
      setIsFilteredAdminSales(e.target.checked)
      localStorage.setItem("is_filtered_admin_sales", e.target.checked)
      let data = {
        contact: null,
        created_at: null,
        print_status: null,
        resi_status: null,
        status: null,
        user_created: userData?.id,
      }
      setFilterData(data)
      handleFilter(data)
    }
  }

  // selected row handler
  const rowSelection = {
    selectedRowKeys,
    onChange: (e) => {
      setSelectedRowKeys(e)
    },
    getCheckboxProps: (record) => {
      if (record.status == "New") {
        return {
          disabled: true,
        }
      }

      if (record.status == "Canceled") {
        return {
          disabled: true,
        }
      }

      return {
        disabled: false,
      }
    },
  }

  const handleChangeProduct = (e, index, field) => {
    const data = [...selectedProducts]
    // old mechanism (select each index for loc_node change)
    // data[index].loc_node = e

    // new mechanism (mapping all index for loc_node change)
    data.map((item, i) => (data[i][field] = e))
    setSelectedProducts(data)
  }

  const menu = (
    <Menu>
      <Menu.Item>
        <a
          onClick={() => {
            if (readyToSubmit) {
              setSelectedRowKeys([])
              return setReadyToSubmit(false)
            }
            return setReadyToSubmit(true)
          }}
        >
          {readyToSubmit ? <CloseCircleFilled /> : <CheckCircleOutlined />}
          <span className="ml-2">
            {readyToSubmit ? "Cancel Submit" : "Ready To Submit"}
          </span>
        </a>
      </Menu.Item>
      {selectedRowKeys.length > 0 && (
        <>
          <Menu.Item>
            <ModalTax
              handleSubmit={(e) => handleSubmitGp(e)}
              // products={selectedProducts.filter(
              //   (item) => !item.gp_submit_number
              // )}
              // products={[]}
              onChange={handleChangeProduct}
              title="Submit SI to GP"
              loading={loadingSubmitSI}
              orderIds={selectedRowKeys}
            />
          </Menu.Item>

          {/* submit billing */}
          <Menu.Item>
            <ModalTax
              handleSubmit={(e) => handleSubmitBillingGp(e)}
              products={selectedBillings}
              onChange={() => {}}
              type="billing"
              titleModal={"Konfirmasi Submit"}
              title="Submit Payment Entry to GP"
              loading={loadingSubmitBilling}
            />
          </Menu.Item>
        </>
      )}
      <Menu.Item>
        <ModalOrderExport formData={{ ...filterData, type: "freebies" }} />
      </Menu.Item>
      {/* <Menu.Item>
        <ImportModal handleOk={handleFilter} withContact />
      </Menu.Item> */}
      {!progressData && (
        <Menu.Item>
          <ImportModal
            handleOk={handleFilter}
            refetch={() => salesOrderRefetch()}
          />
        </Menu.Item>
      )}
    </Menu>
  )

  const show = !inArray(getItem("role"), ["adminwarehouse"])

  const rightContent = (
    <div className="flex justify-between items-center">
      <FilterModal
        handleOk={handleFilter}
        isFiltered={filterData?.sales?.value}
      />
      {show && (
        <Dropdown overlay={menu}>
          <button
            className="text-blue-700 border-[1px] border-blue-700 hover:bg-blue-800/10 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mx-2"
            onClick={(e) => e.preventDefault()}
          >
            <span className="mr-2">More Option</span>
            {inArray(true, [loadingSubmitSI, loadingSubmitBilling]) ? (
              <LoadingOutlined />
            ) : (
              <DownOutlined />
            )}
          </button>
        </Dropdown>
      )}
      {show && (
        <button
          onClick={() => navigate("/order/freebies/form")}
          className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
        >
          <PlusOutlined />
          <span className="ml-2">Tambah Order</span>
        </button>
      )}
    </div>
  )

  return (
    <Layout rightContent={rightContent} title="List Freebies">
      <div className="card">
        <div className="card-body">
          <div className="row mb-4">
            <div className="flex overflow-y-auto mb-4">
              {channelList?.map((item, index) => (
                <div
                  key={index}
                  className="cursor-pointer mr-4"
                  onClick={() => {
                    const params = { ...filterData, sales_channel: item.value }
                    setselectedChannel(item.value)
                    loadOrder(`/api/sales-order`, 10, {
                      search,
                      ...params,
                    })
                  }}
                >
                  <div
                    key={index}
                    className={`
                  card w-96
                  bg-gradient-to-r from-white via-white ${
                    selectedChannel === item.value ? "to-blue-500/20" : ""
                  }
                  hover:to-blue-500/20
                `}
                  >
                    <div className="p-3 border-b flex justify-between">
                      <div className="flex items-center">
                        <strong
                          className={`text-base font-semibold text-${
                            selectedChannel === item.value
                              ? "blue-500"
                              : "black"
                          }`}
                        >
                          {item.label}
                        </strong>
                      </div>
                    </div>
                    <div className="card-body">
                      <strong
                        className={`text-${
                          selectedChannel === item.value ? "blue-500" : "black"
                        } text-lg font-medium`}
                      >
                        Total Order: {formatNumber(item.count)}
                      </strong>
                    </div>
                  </div>
                </div>
              ))}
            </div>
            <div className="col-md-12"></div>
            <div className="col-md-4 col-sm-6 col-12">
              <Input
                placeholder="Cari disini"
                size={"large"}
                className="rounded"
                onPressEnter={() => handleChangeSearch()}
                suffix={
                  isSearch ? (
                    <CloseCircleFilled
                      onClick={() => {
                        loadOrder()
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
            </div>
            <div className="col-md-8">
              <div className="float-right text-right">
                <Checkbox
                  checked={filterData?.user_created && isFilteredAdminSales}
                  onChange={(e) => handleFilterChecked(e)}
                >
                  Aktifkan filter created by
                </Checkbox>
                <br />
                <strong className="text-red-400">
                  Total Data: {salesOrderData?.data?.total || 0}
                </strong>
              </div>
            </div>
          </div>
          <ProgressImportData
            callback={(data) => setProgressData(data)}
            refetch={() => salesOrderRefetch()}
            type="freebies"
          />
          <Table
            dataSource={salesOrderData?.data?.data || []}
            columns={orderFreebiesListColumn}
            loading={salesOrderLoading || salesOrderFetching}
            pagination={false}
            rowKey="id"
            scroll={{ x: "max-content" }}
            tableLayout={"auto"}
            rowSelection={readyToSubmit ? rowSelection : null}
          />
          <Pagination
            defaultCurrent={1}
            current={currentPage}
            total={salesOrderData?.data?.total}
            className="mt-4 text-center"
            onChange={handleChange}
            pageSizeOptions={["10", "20", "50", "100", "200", "500"]}
            onShowSizeChange={(current, size) => {
              setCurrentPage(current)
              // setPerpage(size)
            }}
          />
        </div>
      </div>
    </Layout>
  )
}

export default OrderFreebiesList
