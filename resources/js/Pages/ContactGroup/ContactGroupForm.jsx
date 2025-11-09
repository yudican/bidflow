import {
  CheckOutlined,
  CloseCircleFilled,
  DeleteOutlined,
  DownOutlined,
  EditFilled,
  LoadingOutlined,
  RightOutlined,
  SearchOutlined,
} from "@ant-design/icons"
import {
  Card,
  Dropdown,
  Form,
  Input,
  Menu,
  Pagination,
  Popconfirm,
  Table,
} from "antd"
import React, { useEffect, useState } from "react"
import "react-draft-wysiwyg/dist/react-draft-wysiwyg.css"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../components/layout"
import FormAddressGroupModal from "./Components/FormAddressGroupModal"
import { contactAddressListColumn } from "./config"
import TextArea from "antd/lib/input/TextArea"
import { formatDate, formatPhone, generateRandomString } from "../../helpers"

const defaultAddressItems = [
  {
    key: 0,
    id: null,
    contact_group_id: null,
    nama: null,
    telepon: null,
    alamat: null,
    provinsi_id: null,
    kabupaten_id: null,
    kelurahan_id: null,
    kecamatan_id: null,
    kodepos: null,
    default: null,
  },
]

const ContactGroupForm = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const { group_id } = useParams()

  const [loadingSubmit, setLoadingSubmit] = useState(false)
  const [members, setMembers] = useState([])
  const [currentPage, setCurrentPage] = useState(1)
  const [total, setTotal] = useState(0)
  const [loading, setLoading] = useState(false)
  const [search, setSearch] = useState("")
  const [isSearch, setIsSearch] = useState(false)
  const [selectedRowKeys, setSelectedRowKeys] = useState([])
  const [uncheckedKey, setUncheckedKey] = useState([])
  const [addressItems, setAddressItems] = useState([])
  const [logs, setLogs] = useState([])
  const [pageSize, setPageSize] = useState(10)

  const loadDetailBrand = () => {
    axios.get(`/api/contact-group/detail/${group_id}`).then((res) => {
      const { data } = res.data
      const contactids = data?.group_members.map((item) => item.contact_id)
      setSelectedRowKeys(contactids)
      setAddressItems(
        data?.group_address_members.map((item) => {
          return {
            ...item,
            telepon: formatPhone(item.telepon),
          }
        }) || []
      )

      setLogs(data?.logs)
      loadDataMember()
      form.setFieldsValue(data)
    })
  }

  const loadDataMember = (
    url = "/api/contact-group/member",
    perpage = 10,
    params = { page: currentPage }
  ) => {
    setLoading(true)
    axios
      .post(url, { perpage, contact_group_id: group_id, ...params })
      .then((res) => {
        const { data, total, current_page, from } = res.data.data
        setTotal(total)
        setCurrentPage(current_page)
        // setMembers(data)
        setMembers(
          data.map((item, index) => {
            return {
              ...item,
              number: from + index,
            }
          })
        )
        setLoading(false)
        setUncheckedKey([])
      })
      .catch((e) => setLoading(false))
  }

  // const handleChange = (page, pageSize = 10) => {
  //   loadDataMember("/api/contact-group/member", pageSize, {
  //     search,
  //     page,
  //   })
  // }

  const handleChange = (page, pageSize) => {
    setPageSize(pageSize) // <--- Pastikan pageSize diperbarui di sini
    loadDataMember("/api/contact-group/member", pageSize, {
      search,
      page,
    })
  }

  const handleChangeSearch = () => {
    setIsSearch(true)
    loadDataMember(`/api/contact-group/member`, 10, { search })
  }

  useEffect(() => {
    loadDetailBrand()

    form.setFieldValue("code", generateRandomString(10))
  }, [])

  const onFinish = (values) => {
    setLoadingSubmit(true)
    const form = {
      ...values,
      items: selectedRowKeys,
      items_deletes: uncheckedKey,
      addresss: addressItems,
    }

    const url = group_id
      ? `/api/contact-group/save/${group_id}`
      : "/api/contact-group/save"

    axios
      .post(url, form)
      .then((res) => {
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        setLoadingSubmit(false)
        setAddressItems([])
        return navigate("/contact-group")
      })
      .catch((err) => {
        const { message } = err.response.data
        setLoadingSubmit(false)
        toast.error(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }

  const rowSelection = {
    selectedRowKeys,
    onChange: (newSelectedRowKeys) => {
      setSelectedRowKeys(newSelectedRowKeys)
      // const uncheckedKeys = selectedRowKeys.filter(
      //   (key) => !newSelectedRowKeys.includes(key)
      // );
      // setUncheckedKey(uncheckedKeys);
    },
    getCheckboxProps: (record) => ({
      disabled: false,
    }),
  }

  return (
    <Layout
      title="Tambah Data Group Contact"
      href="/contact-group"
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
        <Card title=" Group Contact Data">
          <div className="card-body row">
            <div className="col-md-6">
              <Form.Item
                label="Group Code"
                name="code"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Group Code!",
                  },
                ]}
              >
                <Input placeholder="Ketik Group Code" disabled={group_id} />
              </Form.Item>
            </div>
            <div className="col-md-6">
              <Form.Item
                label="Group Name"
                name="name"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Group Name!",
                  },
                ]}
              >
                <Input placeholder="Ketik Group Name" />
              </Form.Item>
            </div>
            <div className="col-md-12">
              <Form.Item
                label="Group Description"
                name="deskripsi"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Group Description!",
                  },
                ]}
              >
                <TextArea placeholder="Ketik Group Description" />
              </Form.Item>
            </div>
          </div>
        </Card>
      </Form>
      <div className="card mt-6">
        <div className="card-header">
          <h1 className="text-lg text-bold flex justify-content-between align-items-center">
            <span>Daftar Alamat</span>
            <FormAddressGroupModal
              onChange={(value) => {
                const newAddressItems = [...addressItems]
                newAddressItems.push({
                  id: null,
                  key: newAddressItems.length + 1,
                  ...value,
                })
                setAddressItems(newAddressItems)
              }}
            />
          </h1>
        </div>
        <div className="card-body">
          <Table
            dataSource={addressItems}
            columns={[
              ...contactAddressListColumn,
              {
                title: "Aksi",
                key: "id",
                fixed: "right",
                width: 100,
                hide: true,
                render: (text, record, index) => (
                  <Dropdown.Button
                    icon={<DownOutlined />}
                    overlay={
                      <Menu itemIcon={<RightOutlined />}>
                        <Menu.Item icon={<EditFilled />}>
                          <FormAddressGroupModal
                            initialValues={{
                              ...record,
                              kabupaten_id: parseInt(record?.kabupaten_id),
                              kecamatan_id: parseInt(record?.kecamatan_id),
                              kelurahan_id: parseInt(record?.kelurahan_id),
                              provinsi_id: parseInt(record?.provinsi_id),
                              telepon: formatPhone(record?.telepon),
                            }}
                            onChange={(value) => {
                              const newAddressItems = [...addressItems]
                              newAddressItems[index] = { ...record, ...value }

                              setAddressItems(newAddressItems)
                            }}
                            update={true}
                          />
                        </Menu.Item>
                        <Popconfirm
                          title="Yakin Hapus Alamat ini?"
                          onConfirm={() => {
                            const newAddressItems = [...addressItems]
                            newAddressItems.splice(index, 1)

                            setAddressItems(
                              newAddressItems.map((item, index) => ({
                                ...item,
                                key: index,
                              }))
                            )
                          }}
                          // onCancel={cancel}
                          okText="Ya, Hapus"
                          cancelText="Batal"
                        >
                          <Menu.Item icon={<DeleteOutlined />}>
                            <span>Hapus</span>
                          </Menu.Item>
                        </Popconfirm>
                      </Menu>
                    }
                    onClick={() => alert("detail")}
                  ></Dropdown.Button>
                ),
              },
            ]}
            loading={loading}
            pagination={false}
            rowKey="id"
            scroll={{ x: "max-content" }}
            tableLayout={"auto"}
          />
        </div>
      </div>

      <Card
        title="Pilih Contact"
        className="mt-8"
        extra={
          <Input
            placeholder="Cari disini"
            size={"large"}
            className="rounded"
            onPressEnter={() => handleChangeSearch()}
            suffix={
              isSearch ? (
                <CloseCircleFilled
                  onClick={() => {
                    loadDataMember()
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
        }
      >
        <Table
          scroll={{ x: "max-content" }}
          tableLayout={"auto"}
          rowSelection={rowSelection}
          dataSource={members}
          columns={[
            {
              title: "No.",
              dataIndex: "number",
              key: "number",
            },
            {
              title: "Customer Code",
              dataIndex: "uid",
              key: "uid",
            },
            {
              title: "Customer Name",
              dataIndex: "name",
              key: "name",
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
      </Card>

      {group_id && (
        <Card title="Log History" className="mt-8">
          <Table
            scroll={{ x: "max-content" }}
            tableLayout={"auto"}
            dataSource={logs}
            columns={[
              {
                title: "No",
                dataIndex: "id",
                key: "id",
                render: (text, record, index) => index + 1,
              },
              {
                title: "Action",
                dataIndex: "action",
                key: "action",
              },
              {
                title: "User",
                dataIndex: "user_name",
                key: "user_name",
              },
              {
                title: "Updated At",
                dataIndex: "created_at",
                key: "created_at",
                render: (text) => formatDate(text),
              },
            ]}
            loading={loading}
            pagination={false}
            rowKey="id"
          />
        </Card>
      )}

      <div className="float-right mt-6">
        <button
          className="text-white bg-blueColor hover:bg-blueColor/90 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
          onClick={() => form.submit()}
          type={"button"}
        >
          {loadingSubmit ? <LoadingOutlined /> : <CheckOutlined />}
          <span className="ml-2">Simpan</span>
        </button>
      </div>
    </Layout>
  )
}

export default ContactGroupForm
