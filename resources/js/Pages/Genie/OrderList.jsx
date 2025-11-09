import {
  CloseCircleFilled,
  CloseOutlined,
  DownOutlined,
  FileExcelOutlined,
  LoadingOutlined,
  SearchOutlined,
} from "@ant-design/icons"
import { Dropdown, Input, Menu, Pagination, Progress, Table } from "antd"
import axios from "axios"
import Pusher from "pusher-js"
import React, { useEffect, useState } from "react"
import { toast } from "react-toastify"
import Layout from "../../components/layout"
import { getItem } from "../../helpers"
import FilterModal from "./Components/FilterModal"
import ModalSyncGinee from "./Components/ModalSyncGinee"
import ModalTax from "./Components/ModalTax"
import { orderListColumn } from "./config"

const OrderList = () => {
  const [loading, setLoading] = useState(false)
  const [orders, setOrders] = useState([])
  const [total, setTotal] = useState(0)
  const [currentPage, setCurrentPage] = useState(1)
  const [search, setSearch] = useState(null)
  const [filterData, setFilterData] = useState({})
  const [isSearch, setIsSearch] = useState(false)
  const [pusherChannel, setPusherChannel] = useState(null)
  const [syncData, setSyncData] = useState(null)
  const [showProgress, setShowProgress] = useState(false)
  const [progress, setProgress] = useState(0)
  const [showMessage, setShowMessage] = useState(true)
  const [loadingExport, setLoadingExport] = useState(false)

  // selected row
  const [selectedRowKeys, setSelectedRowKeys] = useState([])
  const userData = JSON.parse(localStorage.getItem("user_data"))
  const base_url = getItem("service_ginee_url")
  const pusher_key = "d804f6dcafacf4804032"
  const pusher_channel = "ginee-development"
  const loadContact = (
    url = "/api/genie/order/list",
    perpage = 10,
    params = {}
  ) => {
    setLoading(true)
    axios
      .post(base_url + url, { perpage, ...params })
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

    const pusher = new Pusher(pusher_key, {
      cluster: "ap1",
    })

    const channelPusher = pusher.subscribe(pusher_channel)
    setPusherChannel(channelPusher)
  }, [])

  useEffect(() => {
    // console.log("Updated data : ", syncData);
    if (pusherChannel && pusherChannel.bind) {
      pusherChannel.unbind("progress")
      pusherChannel.unbind("progress-submit")
      pusherChannel.bind("progress", function (data) {
        // get percentage from two data
        if (data.sync) {
          setSyncData(data)
          loadContact()
        } else {
          toast.success("Sync data telah selesai", {
            position: toast.POSITION.TOP_RIGHT,
          })
        }
      })

      pusherChannel.bind("progress-submit", function (data) {
        setShowProgress(data.progress)
        setProgress(data.percentage)

        if (!data.progress) {
          if (showMessage) {
            toast.success("Submit Data berhasil", {
              position: toast.POSITION.TOP_RIGHT,
            })
            setShowMessage(false)
          }
        }

        if (data.error) {
          toast.error("Submit gagal,Silakan Coba Lagi", {
            position: toast.POSITION.TOP_RIGHT,
          })
          setShowMessage(false)
          setShowProgress(false)
          setProgress(0)
        }
      })
    }
  }, [pusherChannel, syncData])

  const handleChangePage = (page, pageSize = 10) => {
    loadContact(`/api/genie/order/list/?page=${page}`, pageSize, {
      search,
      ...filterData,
    })
  }

  const handleChange = () => {
    setIsSearch(true)
    loadContact(`/api/genie/order/list`, 10, { search })
  }

  const handleSyncData = (type, params = {}) => {
    if (type === "sync") {
      return axios
        .post(base_url + "/api/genie/order/" + type, params)
        .then((res) => {
          const { message } = res.data
          setSyncData({ sync: true, percentage: 0 })
          toast.success(message, {
            position: toast.POSITION.TOP_RIGHT,
          })
        })
    }

    return axios.get(base_url + "/api/genie/order/" + type).then((res) => {
      const { message } = res.data
      setSyncData({ sync: type === "sync", percentage: 0 })
      toast.success(message, {
        position: toast.POSITION.TOP_RIGHT,
      })
    })
  }

  const handleCheckSyncData = () => {
    // axios.get(base_url + "/api/genie/order/sync-check").then((res) => {
    //   const { data } = res.data;
    //   setSyncData(data);
    // });
  }

  const handleFilter = (data) => {
    setFilterData(data)
    loadContact(`/api/genie/order/list`, 10, data)
  }

  // selected row handler
  const onSelectChange = (newSelectedRowKeys) => {
    return setSelectedRowKeys(newSelectedRowKeys)
  }

  const rowSelection = {
    selectedRowKeys,
    onChange: onSelectChange,
    getCheckboxProps: (record) => ({
      disabled: record.status_submit === "submited", // Column configuration not to be checked
    }),
  }

  const handleSubmitGp = (taxs) => {
    if (selectedRowKeys && selectedRowKeys.length < 1) {
      return toast.error("Mohon Pilih Salah Satu", {
        position: toast.POSITION.TOP_RIGHT,
      })
    }
    axios
      .post(base_url + "/api/genie/sync/gp", {
        total: selectedRowKeys.length,
        order_ids: selectedRowKeys,
        user_id: userData.id,
        name: userData.name,
        ...taxs,
      })
      .then((res) => {
        const { data } = res.data
        setShowProgress(true)
      })
  }

  const handleExportContent = () => {
    setLoadingExport(true)
    axios
      .post(`/api/genie-order/export/`)
      .then((res) => {
        const { data } = res.data
        window.open(data)
        setLoadingExport(false)
      })
      .catch((e) => setLoadingExport(false))
  }

  // di comment karena kena timeout, server jadi down
  // useEffect(() => {
  //   setInterval(() => {
  //     var wait = document.getElementById("wait")
  //     if (wait.innerHTML.length > 3) wait.innerHTML = ""
  //     else wait.innerHTML += "."
  //   }, 500)
  // }, [])

  const menu = (
    <Menu>
      <Menu.Item>
        <ModalTax handleSubmit={handleSubmitGp} />
      </Menu.Item>

      <Menu.Item onClick={() => handleExportContent()}>
        {loadingExport ? <LoadingOutlined /> : <FileExcelOutlined />}
        <span className="ml-2">Export</span>
      </Menu.Item>
    </Menu>
  )

  const rightContent = (
    <div className="flex justify-between items-center">
      <Dropdown overlay={menu}>
        <button
          className="text-blue-700 border-[1px] border-blue-700 hover:bg-blue-800/10 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mx-2"
          onClick={(e) => e.preventDefault()}
        >
          <span className="mr-2">More Option</span>
          <DownOutlined />
        </button>
      </Dropdown>
      <FilterModal handleOk={handleFilter} />
      {/* <button
        className="ml-3 text-white bg-green-800 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mr-2"
        onClick={() => handleExportContent()}
      >
        {loadingExport ? <LoadingOutlined /> : <FileExcelOutlined />}
        <span className="ml-2">Export</span>
      </button> */}
      <ModalSyncGinee
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
      />
    </div>
  )

  const syncContent = (
    <div className="flex justify-between items-center">
      <button
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
      </button>
      <button
        onClick={() => handleSyncData("cancel-sync")}
        className="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-4"
      >
        <CloseOutlined />
        <span className="ml-2">Cancel Sync</span>
      </button>
    </div>
  )

  return (
    <Layout
      title="Order List by Genie"
      rightContent={syncData?.sync || showProgress ? syncContent : rightContent}
    >
      <div className="card">
        <div className="card-body">
          <div className="row mb-4">
            {showProgress && (
              <div className="col-md-12">
                <div className="card">
                  <div className="card-body text-mainColor text-center">
                    <p>Submit Data Sedang Berlangsung</p>
                    <Progress percent={progress} status="active" />
                  </div>
                </div>
              </div>
            )}
            {syncData?.sync && (
              <div className="col-md-12">
                <div className="card">
                  <div className="card-body text-mainColor text-center">
                    <p>
                      Sinkronisasi data ginee sedang berlangsung, mohon tunggu
                      <span id="wait">.</span>
                    </p>
                    {/* <Progress percent={syncData?.percentage} status="active" /> */}
                  </div>
                </div>
              </div>
            )}
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

export default OrderList
