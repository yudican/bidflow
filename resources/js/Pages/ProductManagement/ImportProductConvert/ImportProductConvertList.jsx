import {
  CloseCircleFilled,
  DeleteOutlined,
  EditOutlined,
  PlusOutlined,
  RightOutlined,
  SearchOutlined,
  SyncOutlined,
} from "@ant-design/icons"
import {
  Dropdown,
  Input,
  Menu,
  Pagination,
  Popconfirm,
  Progress,
  Table,
} from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { useNavigate } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../../components/layout"
import { formatNumber, getItem, inArray } from "../../../helpers"
import FilterModal from "./Components/FilterModal"
import ImportModal from "./Components/ImportModal"
import { productImportListColumn } from "./config"
import Pusher from "pusher-js"

const ImportProductConvertList = () => {
  const navigate = useNavigate()
  const [loading, setLoading] = useState(false)
  const [datas, setDatas] = useState([])
  const [dataCount, setDataCount] = useState({
    success: 0,
    failed: 0,
    showImport: true,
    showConvert: false,
  })

  const userData = getItem("user_data", true)
  const [total, setTotal] = useState(0)
  const [search, setSearch] = useState("")
  const [isSearch, setIsSearch] = useState(false)
  const [currentPage, setCurrentPage] = useState(1)
  const [filterData, setFilterData] = useState({})

  const [pusherChannel, setPusherChannel] = useState(null)
  const [progressData, setProgressData] = useState(null)
  const [type, setType] = useState("Import")

  //   eafb4c1c4f906c90399e
  // 01d9b57c3818c1644cb0
  const pusher_key = "eafb4c1c4f906c90399e"
  const pusher_channel = "aimi"

  const loadData = (
    url = "/api/product-management/import/list",
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
        setDataCount(res.data.data_count)
        const newData = data.map((item) => {
          return {
            id: item.id,
            trx_id: item.trx_id,
            user: item.user,
            channel: item.channel,
            toko: item.toko,
            sku: item.sku,
            produk_nama: item.produk_nama,
            harga_awal: formatNumber(item.harga_awal, "Rp. "),
            harga_promo: formatNumber(item.harga_promo, "Rp. "),
            qty: item.qty,
            ongkir: formatNumber(item.ongkir, "Rp. "),
            diskon: formatNumber(item.diskon, "Rp. "),
            tanggal_transaksi: item.tanggal_transaksi,
            kurir: item.kurir,
            metode_pembayaran: item.metode_pembayaran,
            resi: item.resi,
            status: item.status_convert,
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
          setType(data.type)
          // get percentage from two data
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
      )
    }
  }, [pusherChannel, progressData])

  const handleChange = (page, pageSize = 10) => {
    loadData(`/api/product-management/import/list/?page=${page}`, pageSize, {
      search,
      page,
      ...filterData,
    })
  }

  const handleChangeSearch = () => {
    setIsSearch(true)
    loadData(`/api/product-management/import/list`, 10, { search })
  }

  const handleDiscard = () => {
    setLoading(true)
    axios
      .post("/api/product-management/import/discard")
      .then((res) => {
        toast.success("Data berhasil dihapus", {
          position: toast.POSITION.TOP_RIGHT,
        })
        loadData()
      })
      .catch((e) => setLoading(false))
  }

  const handleConvert = () => {
    setLoading(true)
    axios
      .post("/api/product-management/import/convert")
      .then((res) => {
        toast.success("Convert Sedang Berlangsung", {
          position: toast.POSITION.TOP_RIGHT,
        })
        return navigate("/product-management/convert-product")
      })
      .catch((e) => setLoading(false))
  }

  const canConvert =
    dataCount.showConvert && !progressData?.progress ? true : false
  const rightContent = (
    <div className="flex justify-between items-center">
      {/* <FilterModal handleOk={handleFilter} /> */}
      {total > 0 && (
        <button
          onClick={() => handleDiscard()}
          className="text-white bg-red-800 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mr-2"
        >
          <CloseCircleFilled />
          <span className="ml-2">Discard</span>
        </button>
      )}
      {canConvert && (
        <button
          onClick={() => handleConvert()}
          className="text-white bg-blue-800 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mr-2"
        >
          <SyncOutlined />
          <span className="ml-2">Convert</span>
        </button>
      )}
      <ImportModal disabled={!dataCount?.showImport} />
    </div>
  )
  return (
    <Layout rightContent={rightContent} title="List Import Product">
      <div className="card">
        <div className="card-body">
          <div className="row card-body   mb-4">
            <div className="col-md-12">
              {progressData?.progress && (
                <div className="card">
                  <div className="card-body text-mainColor text-center mx-6">
                    <p>
                      {type} Data Sedang Berlangsung{" "}
                      {`${progressData?.success}/${progressData?.total}`}{" "}
                    </p>
                    <Progress
                      percent={progressData?.percentage}
                      status="active"
                    />
                  </div>
                </div>
              )}
            </div>
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
            scroll={{ x: "max-content" }}
            tableLayout={"auto"}
            dataSource={datas}
            columns={[
              ...productImportListColumn,
              // {
              //   title: "Action",
              //   key: "id",
              //   align: "center",
              //   fixed: "right",
              //   width: 100,
              //   render: (text) => {
              //     if (!show) return null
              //     return (
              //       <Dropdown.Button
              //         style={{
              //           left: -16,
              //         }}
              //         // icon={<MoreOutlined />}
              //         overlay={
              //           <Menu itemIcon={<RightOutlined />}>
              //             <Menu.Item
              //               icon={<EditOutlined />}
              //               onClick={() => navigate(`form/${text.id}`)}
              //             >
              //               Ubah
              //             </Menu.Item>
              //             <Popconfirm
              //               title="Yakin Hapus Data ini?"
              //               onConfirm={() => deleteProductMargin(text.id)}
              //               // onCancel={cancel}
              //               okText="Ya, Hapus"
              //               cancelText="Batal"
              //             >
              //               <Menu.Item icon={<DeleteOutlined />}>
              //                 <span>Hapus</span>
              //               </Menu.Item>
              //             </Popconfirm>
              //           </Menu>
              //         }
              //       ></Dropdown.Button>
              //     )
              //   },
              // },
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

export default ImportProductConvertList
