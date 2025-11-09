import {
  CloseCircleFilled,
  EyeFilled,
  LoadingOutlined,
  SearchOutlined,
} from "@ant-design/icons"
import { Input, Pagination, Table } from "antd"
import axios from "axios"
import Pusher from "pusher-js"
import React, { useEffect, useState } from "react"
import { useNavigate } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../../components/layout"
import { getItem } from "../../../helpers"
import { productConvertListColumn } from "./config"

const ConvertProductList = () => {
  const navigate = useNavigate()
  const [loading, setLoading] = useState(false)
  const [datas, setDatas] = useState([])

  const userData = getItem("user_data", true)
  const [total, setTotal] = useState(0)
  const [search, setSearch] = useState("")
  const [isSearch, setIsSearch] = useState(false)
  const [currentPage, setCurrentPage] = useState(1)

  const [pusherChannel, setPusherChannel] = useState(null)
  const [progressData, setProgressData] = useState(null)

  const pusher_key = "eafb4c1c4f906c90399e"
  const pusher_channel = "aimi"

  const loadData = (
    url = "/api/product-management/convert/list",
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
        const newData = data.map((item) => {
          return {
            id: item.id,
            user: item.user_name,
            convert_date: item.convert_date,
            success: item.success ?? 0,
            failed: item.failed ?? 0,
            loading: false,
          }
        })

        setDatas(newData)
        setLoading(false)
      })
      .catch((e) => setLoading(false))
  }

  useEffect(() => {
    loadData()

    const pusher = new Pusher(pusher_key, {
      cluster: "ap1",
    })

    const channelPusher = pusher.subscribe(pusher_channel)
    setPusherChannel(channelPusher)
  }, [])

  useEffect(() => {
    if (pusherChannel && pusherChannel.bind) {
      pusherChannel.unbind("progress-import-convert-" + userData.id)
      pusherChannel.bind(
        "progress-import-convert-" + userData.id,
        function (data) {
          if (data.type === "Convert") {
            // get percentage from two data
            const lists = [...datas]
            const newData = lists.map((item) => {
              let newitem = { ...item }
              if (item.id == data.convert_id) {
                newitem.loading = true
                newitem.success = data.success
                newitem.failed = data.failed
              }

              return newitem
            })
            setDatas(newData)
            if (data.progress) {
              setProgressData(data)
            } else {
              setProgressData(data)
              toast.success(data.type + " data telah selesai", {
                position: toast.POSITION.TOP_RIGHT,
              })
              loadData()
            }
          }
        }
      )
    }
  }, [pusherChannel, progressData])

  const handleChange = (page, pageSize = 10) => {
    loadData(`/api/product-management/convert/list/?page=${page}`, pageSize, {
      search,
      page,
    })
  }

  const handleChangeSearch = () => {
    setIsSearch(true)
    loadData(`/api/product-management/convert/list`, 10, { search })
  }

  return (
    <Layout title="List Convert">
      <div className="card">
        <div className="card-body">
          <div className="row card-body   mb-4">
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
          {/* <Progress percent={progressData?.percentage} status="active" /> */}
          <Table
            scroll={{ x: "max-content" }}
            tableLayout={"auto"}
            dataSource={datas}
            columns={[
              ...productConvertListColumn,
              {
                title: "Success",
                dataIndex: "success",
                key: "success",
              },
              {
                title: "Failed",
                dataIndex: "failed",
                key: "failed",
              },
              {
                title: "Action",
                dataIndex: "action",
                key: "action",
                render: (text, record) => {
                  if (record.loading) {
                    return (
                      <div>
                        <button
                          className="text-white bg-gray-800 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
                          disabled={true}
                        >
                          <LoadingOutlined />
                          <span className="ml-2">Loading</span>
                        </button>
                      </div>
                    )
                  }
                  return (
                    <div>
                      <button
                        onClick={() => {
                          navigate("detail/" + record.id)
                        }}
                        className="text-white bg-blue-800 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
                      >
                        <EyeFilled />
                        <span className="ml-2">Detail</span>
                      </button>
                    </div>
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

export default ConvertProductList
