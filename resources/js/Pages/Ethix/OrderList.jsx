import { CloseCircleFilled, SearchOutlined } from "@ant-design/icons"
import { Input, Pagination, Table } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { toast } from "react-toastify"
import Layout from "../../components/layout"
import ModalTax from "../Genie/Components/ModalTax"
import FilterModal from "./Components/FilterModal"
import { orderListColumn } from "./config"

const OrderListEthixMp = () => {
  const [loading, setLoading] = useState(false)
  const [orders, setOrders] = useState([])
  const [total, setTotal] = useState(0)
  const [currentPage, setCurrentPage] = useState(1)
  const [search, setSearch] = useState(null)
  const [filterData, setFilterData] = useState({})
  const [isSearch, setIsSearch] = useState(false)
  const [syncData, setSyncData] = useState(null)
  const [showProgress, setShowProgress] = useState(false)

  const [selectedRowKeys, setSelectedRowKeys] = useState([])
  const [readyToSubmit, setReadyToSubmit] = useState(false)
  const [selectedProducts, setSelectedProducts] = useState([])

  // selected row
  const loadContact = (
    url = "/api/ethix/order/list",
    perpage = 10,
    params = {}
  ) => {
    setLoading(true)
    axios
      .post(url, { perpage, ...params })
      .then((res) => {
        const { data, total, current_page } = res.data.data
        setTotal(total)
        setCurrentPage(current_page)
        setOrders(data)
        setLoading(false)
        handleCheckSyncData()
      })
      .catch((err) => {
        setLoading(false)
        toast.error("Terjadi Kesalahan, Silakan Coba Lagi", {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }
  useEffect(() => {
    loadContact()
  }, [])

  const handleChangePage = (page, pageSize = 10) => {
    loadContact(`/api/ethix/order/list/?page=${page}`, pageSize, {
      search,
      ...filterData,
    })
  }

  const handleCheckSyncData = () => {
    // axios.get(base_url + "/api/ethix/order/sync-check").then((res) => {
    //   const { data } = res.data;
    //   setSyncData(data);
    // });
  }

  const handleFilter = (data) => {
    setFilterData(data)
    loadContact(`/api/ethix/order/list`, 10, data)
  }

  // selected row handler
  const rowSelection = {
    selectedRowKeys,
    onChange: (e) => {
      setSelectedRowKeys(e)
      const productData = []
      if (e.length > 0) {
        e.map((value) => {
          const item = orderLead.find((item) => item.id == value)
          if (item) {
            const products = item?.product_needs.map((row, index) => {
              return {
                key: index,
                id: row.id,
                so_id: item.id,
                product_name: row.product_name,
                sku: row.product.sku,
              }
            })
            productData.push(...products)
          }
        })
      }

      return setSelectedProducts(productData)
    },
    getCheckboxProps: (record) => {
      if (record.status == "New") {
        return {
          disabled: true,
        }
      }

      if (record.status_submit === "submited") {
        return {
          disabled: true,
        }
      }

      return {
        disabled: false,
      }
    },
  }

  const handleChangeProduct = (e, index) => {
    const data = [...selectedProducts]
    data[index].loc_node = e
    setSelectedProducts(data)
  }

  const rightContent = (
    <div className="flex justify-between items-center">
      {selectedRowKeys.length > 0 && (
        <ModalTax
          handleSubmit={(e) => handleSubmitGp(e)}
          products={selectedProducts}
          onChange={handleChangeProduct}
        />
      )}
      <button
        onClick={() => {
          if (readyToSubmit) {
            setSelectedRowKeys([])
            return setReadyToSubmit(false)
          }
          return setReadyToSubmit(true)
        }}
        className={`text-white bg-${
          !readyToSubmit ? "blue" : "red"
        }-700 hover:bg-${
          !readyToSubmit ? "blue" : "red"
        }-800 focus:ring-4 focus:outline-none focus:ring-${
          !readyToSubmit ? "blue" : "red"
        }-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mr-2`}
      >
        <span className="ml-2">
          {readyToSubmit ? "Cancel Submit" : "Ready To Submit"}
        </span>
      </button>
      <FilterModal handleOk={handleFilter} />
      {/* <button
        className="ml-3 text-white bg-green-800 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mr-2"
        onClick={() => handleExportContent()}
      >
        {loadingExport ? <LoadingOutlined /> : <FileExcelOutlined />}
        <span className="ml-2">Export</span>
      </button> */}
      {/* <ModalSyncGinee
        handleSubmit={(values) => {
          const date = {
            start_date: values.start_date[0].format("YYYY-MM-DD"),
            end_date: values.start_date[1].format("YYYY-MM-DD"),
          }
          let date_1 = new Date(values.start_date[1])
          let date_2 = new Date(values.start_date[0])

          let difference = date_1.getTime() - date_2.getTime()
          let TotalDays = Math.ceil(difference / (1000 * 3600 * 24))

          if (TotalDays > 15) {
            return toast.error("Maksimal 15 Hari", {
              position: toast.POSITION.TOP_RIGHT,
            })
          }
          handleSyncData("sync", { date })
        }}
      /> */}
    </div>
  )

  const syncContent = (
    <div className="flex justify-between items-center">
      {/* <button
      className="text-dark bg-white hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center disabled border-2 border-gray-300"
      disabled
    >
      <img
        src="https://i.ibb.co/V91rZKM/1497.gif"
        alt="1497"
        style={{ height: "20px" }}
        border="0"
      />
      <span className="ml-2">Proses Submit sedang Berlangsung</span>
    </button> */}
      {/* <button
        onClick={() => handleSyncData("cancel-sync")}
        className="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-4"
      >
        <CloseOutlined />
        <span className="ml-2">Cancel Sync</span>
      </button> */}
    </div>
  )

  return (
    <Layout
      title="Order List by Testing"
      rightContent={syncData?.sync || showProgress ? syncContent : rightContent}
    >
      <div className="card">
        <div className="card-body">
          <div className="row mb-4">
            {/* {showProgress && (
          <div className="col-md-12">
            <div className="card">
              <div className="card-body text-mainColor text-center">
                <p>Submit Data Sedang Berlangsung</p>
                <Progress percent={progress} status="active" />
              </div>
            </div>
          </div>
        )} */}
            {/* {syncData?.sync && (
          <div className="col-md-12">
            <div className="card">
              <div className="card-body text-mainColor text-center">
                <p>
                  Sinkronisasi data ginee sedang berlangsung, mohon tunggu
                  <span id="wait">.</span>
                </p> */}
            {/* <Progress percent={syncData?.percentage} status="active" /> */}
            {/* </div>
            </div>
          </div>
        )} */}
            <div className="col-md-4 col-sm-6 col-12">
              <Input
                placeholder="Cari disini"
                size={"large"}
                className="rounded"
                onPressEnter={() => handleChange()}
                suffix={
                  isSearch ? (
                    <CloseCircleFilled
                      onClick={() => {
                        loadContact()
                        setSearch(null)
                        setIsSearch(false)
                      }}
                    />
                  ) : (
                    <SearchOutlined onClick={() => handleChange()} />
                  )
                }
                value={search}
                onChange={(e) => setSearch(e.target.value)}
              />
            </div>
            <div className="col-md-8">
              <strong className="float-right mt-3 text-red-400">
                Total Data: {total}
              </strong>
            </div>
          </div>
          <Table
            rowSelection={rowSelection}
            dataSource={orders}
            columns={orderListColumn}
            loading={loading}
            pagination={false}
            rowKey="id"
            scroll={{ x: "max-content" }}
            tableLayout={"auto"}
            theme="dark"
          />
          <Pagination
            defaultCurrent={1}
            current={currentPage}
            total={total}
            className="mt-4 text-center"
            onChange={handleChangePage}
            pageSizeOptions={["10", "20", "50", "100", "200", "500"]}
          />
        </div>
      </div>
    </Layout>
  )
}
export default OrderListEthixMp
