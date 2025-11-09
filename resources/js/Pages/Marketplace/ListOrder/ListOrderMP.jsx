import {
  CloseCircleFilled,
  DownOutlined,
  SearchOutlined,
} from "@ant-design/icons"
import { Dropdown, Input, Menu, Pagination, Progress, Table } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { useNavigate } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../../components/layout"
import ModalTax from "../../Genie/Components/ModalTax"
import FilterModal from "./Components/FilterModal"
import ImportOrder from "./Components/ImportOrder"
import { packageListColumn } from "./config"
import Pusher from "pusher-js"

const ListOrderMP = () => {
  const navigate = useNavigate()
  const [loading, setLoading] = useState(false)
  const [datas, setDatas] = useState([])
  const [total, setTotal] = useState(0)
  const [search, setSearch] = useState("")
  const [isSearch, setIsSearch] = useState(false)
  const [currentPage, setCurrentPage] = useState(1)
  const [filterData, setFilterData] = useState({})
  const [selectedProducts, setSelectedProducts] = useState([])
  const [selectedRowKeys, setSelectedRowKeys] = useState([])
  const [loadingSubmit, setLoadingSubmit] = useState(false)

  // event
  const [pusherChannel, setPusherChannel] = useState(null)
  const [progressData, setProgressData] = useState(null)

  const loadData = (
    url = "/api/marketplace/list",
    perpage = 10,
    params = { page: currentPage }
  ) => {
    setLoading(true)
    axios
      .post(url, { perpage, ...params })
      .then((res) => {
        const { data, total, current_page } = res.data.data
        setTotal(total)
        setCurrentPage(current_page)
        setDatas(data)
        setLoading(false)
      })
      .catch((e) => setLoading(false))
  }
  useEffect(() => {
    loadData()
    const pusher = new Pusher("d1b03f4c9a2b2345784b", {
      cluster: "ap1",
      debug: true, // Enable debug mode
    })
    const channelPusher = pusher.subscribe("aimigroup-crm-development")
    setPusherChannel(channelPusher)
  }, [])
  console.log(pusherChannel, "channelPusher")
  useEffect(() => {
    // console.log("Updated data : ", syncData);
    if (pusherChannel && pusherChannel.bind) {
      pusherChannel.unbind("progress-submit-mp")

      pusherChannel.bind("progress-submit-mp", function (data) {
        console.log(data, "data")
        // get percentage from two data
        if (data.progress == data.total) {
          toast.success("Submit data telah selesai", {
            position: toast.POSITION.TOP_RIGHT,
          })
          setProgressData(null)
          loadData()
        } else {
          setProgressData(data)
        }
      })
    }
  }, [pusherChannel, progressData])

  const handleChange = (page, pageSize = 10) => {
    loadData(`/api/marketplace/list/?page=${page}`, pageSize, {
      search,
      page,
      ...filterData,
    })
  }

  const handleChangeSearch = () => {
    setIsSearch(true)
    loadData(`/api/marketplace/list`, 10, { search })
  }

  const handleFilter = (data) => {
    setFilterData(data)
    loadData(`/api/marketplace/list`, 10, data)
  }

  // selected row handler
  const rowSelection = {
    selectedRowKeys,
    onChange: (e) => {
      setSelectedRowKeys(e)
      const productData = []
      if (e.length > 0) {
        e.map((value) => {
          const item = datas.find((item) => item.id == value)
          if (item) {
            const products = item?.items.map((row, index) => {
              return {
                key: index,
                id: row.id,
                order_id: item.id,
                trx_id: item.trx_id,
                product_name: row.product_name,
                sku: row.sku,
              }
            })
            productData.push(...products)
          }
        })
      }
      setSelectedProducts(productData)
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

  const handleSubmitGp = (value) => {
    setLoadingSubmit(true)
    const hasLocNode = selectedProducts.every((item) => item.loc_node)
    if (!hasLocNode) {
      toast.error("Lokasi Site ID harus diisi")
      return setLoadingSubmit(false)
    }
    axios
      .post(`/api/marketplace/submit`, {
        ids: selectedRowKeys,
        ...value,
        products: selectedProducts,
      })
      .then((res) => {
        const { data } = res.data
        toast.success("Order marketplace berhasil di submit")
        setSelectedRowKeys([])
        setSelectedProducts([])
        setLoadingSubmit(false)
      })
      .catch((e) => {
        setLoadingSubmit(false)
        toast.error("Error submitting order marketplace")
      })
  }

  const handleSubmitEthix = () => {
    setLoadingSubmit(true)
    axios
      .post(`/api/marketplace/submit/ethix`, { ids: selectedRowKeys })
      .then((res) => {
        const { data } = res.data
        setLoadingSubmit(false)
        toast.success("Data berhasil Disubmit Ke Ethix")
      })
      .catch((err) => {
        setLoadingSubmit(false)
        toast.success("Data gagal Disubmit Ke Ethix")
      })
  }

  const handleChangeProduct = (e, index) => {
    const data = [...selectedProducts]
    data[index].loc_node = e
    setSelectedProducts(data)
  }

  const rightContent = (
    <div className="flex justify-between items-center">
      {/* <ModalTax
        handleSubmit={(e) => handleSubmitGp(e)}
        products={selectedProducts}
        onChange={handleChangeProduct}
        type={"marketplace"}
        isAction
      />
      <button
        onClick={() => handleSubmitEthix()}
        className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
      >
        Submit Ethix
      </button> */}
      <Dropdown
        overlay={
          <Menu>
            <Menu.Item>
              <ModalTax
                asMenuItem
                handleSubmit={(e) => handleSubmitGp(e)}
                products={selectedProducts}
                onChange={handleChangeProduct}
                type={"marketplace"}
                isAction
              />
            </Menu.Item>
            <Menu.Item>
              <a onClick={() => handleSubmitEthix()}>
                <span>Submit Ethix</span>
              </a>
            </Menu.Item>
          </Menu>
        }
      >
        <button
          className="text-blue-700 border-[1px] border-blue-700 hover:bg-blue-800/10 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mx-2"
          onClick={(e) => e.preventDefault()}
        >
          <span className="mr-2">More Option</span>
          <DownOutlined />
        </button>
      </Dropdown>
      <FilterModal handleOk={handleFilter} />
      <ImportOrder refetch={() => loadData()} />
      {progressData && (
        <Progress
          type="circle"
          className="ml-3"
          percent={progressData?.percentage}
          format={(percent) =>
            `${progressData?.progress}/${progressData?.total}`
          }
          width={50}
        />
      )}
    </div>
  )

  return (
    <Layout rightContent={rightContent} title="List Order Marketplace">
      <div className="card">
        <div className="card-body">
          <div className="row mb-4">
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
                        loadData()
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
              <strong className="float-right mt-3 text-red-400">
                Total Data: {total}
              </strong>
            </div>
          </div>
          <Table
            rowSelection={rowSelection}
            scroll={{ x: "max-content" }}
            tableLayout={"auto"}
            dataSource={datas}
            columns={[
              ...packageListColumn,
              {
                title: "Action",
                key: "id",
                align: "center",
                fixed: "right",
                width: 100,
                render: (text, record) => {
                  return (
                    <button
                      onClick={() =>
                        navigate(
                          `/marketplace/detail/${record.trx_id.replace(
                            /\//g,
                            "_"
                          )}`
                        )
                      }
                      className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
                    >
                      Detail
                    </button>
                  )
                },
              },
            ]}
            loading={loading}
            pagination={false}
            rowKey="id"
          />
          <Pagination
            defaultCurrent={1}
            current={currentPage}
            total={total}
            className="mt-4 text-center"
            onChange={handleChange}
          />
        </div>
      </div>
    </Layout>
  )
}

export default ListOrderMP
