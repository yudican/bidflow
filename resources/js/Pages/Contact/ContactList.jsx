import {
  CloseCircleFilled,
  DownOutlined,
  FileExcelOutlined,
  LoadingOutlined,
  PlusOutlined,
  SearchOutlined,
  SyncOutlined,
} from "@ant-design/icons"
import { Dropdown, Input, Menu, Pagination, Table } from "antd"
import axios from "axios"
import moment from "moment"
import React, { useEffect, useState } from "react"
import { useNavigate } from "react-router-dom"
import Layout from "../../components/layout"
import { formatNumber } from "../../helpers"
import FilterModal from "./Components/FilterModal"
import ImportModal from "./Components/ImportModal"

import { toast } from "react-toastify"
import ModalConfirm from "../../components/Modal/ModalConfirm"
import ProgressImportData from "../../components/ProgressImportData"
import { contactListColumn } from "./config"

const ContactList = () => {
  const navigate = useNavigate()
  const [loading, setLoading] = useState(false)
  const [contacts, setContacts] = useState([])
  const [total, setTotal] = useState(0)
  const [search, setSearch] = useState("")
  const [isSearch, setIsSearch] = useState(false)
  const [currentPage, setCurrentPage] = useState(1)
  const [perPage, setPerpage] = useState(10)
  const [filterData, setFilterData] = useState({})
  const [loadingExport, setLoadingExport] = useState(false)
  const [selectedRowKeys, setSelectedRowKeys] = useState([])
  const [loadingSubmit, setLoadingSubmit] = useState(false)
  const [selectedAll, setSelectedAll] = useState(false)
  const [progressData, setProgressData] = useState({})
  const loadContact = (
    url = "/api/contact",
    perpage = perPage,
    params = { page: currentPage }
  ) => {
    setLoading(true)
    axios
      .post(url, { perpage, ...params })
      .then((res) => {
        const { data, total, from } = res.data.data

        setTotal(total) // set total of total data products

        const newData = data.map((contact, index) => {
          const number = from + index // overriding response to set paginated number per pages

          return {
            id: contact.id,
            key: contact.id,
            number,
            uid: contact.uid,
            name: contact.name,
            email: contact.email,
            role: contact?.role?.role_name,
            created_by: contact?.created_by_name,
            created_on: moment(contact.created_at).format("DD-MM-YYYY"),
            deposit: formatNumber(contact?.deposit),
            total_debt: formatNumber(contact?.amount_detail.total_debt),
          }
        })
        console.log(newData, "newData")
        console.log(data, "data")
        setContacts(newData)
        if (selectedAll) {
          const customerCodes = newData.map((item) => item.id)
          setSelectedRowKeys((prevState) => [...prevState, ...customerCodes])
        }

        setLoading(false)
      })
      .catch((err) => {
        console.log(err, "err")
        setLoading(false)
      })
  }
  useEffect(() => {
    loadContact()
  }, [])

  const handleChange = (page, pageSize = 10) => {
    setCurrentPage(page)
    loadContact(`/api/contact?page=${page}`, pageSize, {
      search,
      page,
      ...filterData,
    })
  }

  const handleChangeSearch = () => {
    setIsSearch(true)
    loadContact(`/api/contact`, 10, {
      search,
      page: 1,
      ...filterData,
    })
  }

  const handleFilter = (data) => {
    setFilterData(data)
    loadContact(`/api/contact`, 10, data)
  }

  const handleExportContent = () => {
    setLoadingExport(true)
    axios
      .post(`/api/contact/export/`)
      .then((res) => {
        const { data } = res.data
        window.open(data)
        setLoadingExport(false)
      })
      .catch((e) => setLoadingExport(false))
  }

  const syncContact = (url = "/api/contact/service/sync") => {
    setLoading(true)
    axios
      .get(url)
      .then((res) => {
        loadContact()
      })
      .catch((e) => {
        loadContact()
        setLoading(false)
      })
  }

  const handleSubmitGP = (value) => {
    if (selectedRowKeys && selectedRowKeys.length < 1) {
      return toast.error("Mohon Pilih Salah Satu", {
        position: toast.POSITION.TOP_RIGHT,
      })
    }
    setLoadingSubmit(true)
    axios
      .post(`/api/contact/submit-gp`, {
        items: selectedRowKeys,
      })
      .then((res) => {
        if (res.data.status == "failed") {
          toast.error(res.data.message)
        } else {
          toast.success("Contact berhasil di submit ke GP")
        }

        setSelectedRowKeys([])
        setLoadingSubmit(false)
      })
      .catch((err) => {
        setLoadingSubmit(false)
        toast.error("Contact gagal di submit ke GP")
      })
  }

  const menu = (
    <Menu>
      <Menu.Item>
        <a onClick={handleSubmitGP}>
          <span className="">Submit to GP</span>
        </a>
      </Menu.Item>
      <Menu.Divider />
      <Menu.Item>
        <a onClick={() => navigate("/contact/submit/history")}>
          <span>History Submit</span>
        </a>
      </Menu.Item>
      <Menu.Divider />
      <Menu.Item>
        <a onClick={() => navigate("/contact-import/submit/history")}>
          <span>History Import</span>
        </a>
      </Menu.Item>
      <Menu.Divider />
      <Menu.Item>
        <ImportModal handleOk={handleFilter} />
      </Menu.Item>
      <Menu.Divider />
      <Menu.Item>
        <button onClick={() => handleExportContent()}>
          {loadingExport ? <LoadingOutlined /> : <FileExcelOutlined />}
          <span className="ml-2">Export</span>
        </button>
      </Menu.Item>
    </Menu>
  )

  // selected row handler
  const rowSelection = {
    selectedRowKeys,
    onChange: (newSelectedRowKeys) => setSelectedRowKeys(newSelectedRowKeys),
    onSelectAll: (value) => {
      setSelectedAll(value)
      if (!value) {
        setSelectedRowKeys([])
      }
    },
    // getCheckboxProps: (record) => ({
    //   disabled: record.uid === null, // Column configuration not to be checked
    // }),
  }

  const rightContent = (
    <div className="flex justify-between items-center">
      <FilterModal handleOk={handleFilter} />

      <ModalConfirm
        title="Konfirmasi"
        description="Apakah Anda yakin ingin melakukan sinkronisasi data Contact dari Ms Dynamic GP?"
        okText="Ya"
        onConfirm={() => syncContact()}
        okButtonProps={{
          style: { width: "70px" },
          loading: loading,
        }}
        cancelButtonProps={{ style: { width: "70px" } }}
      >
        <button className="text-white bg-blueColor hover:bg-blueColor/90 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2">
          <SyncOutlined />
          <span className="ml-2">Sync Contact</span>
        </button>
      </ModalConfirm>

      <Dropdown overlay={menu}>
        <button
          className="text-blue-700 border-[1px] border-blue-700 hover:bg-blue-800/10 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mx-2"
          onClick={(e) => e.preventDefault()}
        >
          <span className="mr-2">More Option</span>
          <DownOutlined />
        </button>
      </Dropdown>

      <button
        onClick={() => navigate("/contact/create")}
        className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
      >
        <PlusOutlined />
        <span className="ml-2">Tambah Data</span>
      </button>
    </div>
  )

  return (
    <Layout rightContent={rightContent} title="List Contact">
      <div className="card">
        <div className="card-body">
          <div className="row card-body   mb-4">
            <div className="col-md-12"></div>
            <div className="col-md-4 col-sm-6 col-12">
              <Input
                placeholder="Cari disini"
                size={"large"}
                className="rounded "
                onPressEnter={() => handleChangeSearch()}
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
                    <SearchOutlined onClick={() => handleChangeSearch()} />
                  )
                }
                value={search}
                onChange={(e) => setSearch(e.target.value)}
              />
            </div>
            <div className="col-md-8">
              <strong className="float-right text-red-400">
                Total Data: {formatNumber(total)}
              </strong>
            </div>
          </div>
          <ProgressImportData
            callback={(data) => setProgressData(data)}
            refetch={() => loadContact()}
            type="contact"
          />
          <Table
            rowSelection={rowSelection}
            dataSource={contacts}
            columns={contactListColumn}
            loading={loading}
            pagination={false}
            rowKey="number"
            scroll={{ x: "max-content" }}
            tableLayout={"auto"}
          />
          <Pagination
            defaultCurrent={1}
            current={currentPage}
            total={total}
            className="mt-4 text-center"
            onChange={handleChange}
            pageSizeOptions={["10", "20", "50", "100"]}
            onShowSizeChange={(current, size) => {
              setCurrentPage(current)
              setPerpage(size)
            }}
          />
        </div>
      </div>
    </Layout>
  )
}

export default ContactList
