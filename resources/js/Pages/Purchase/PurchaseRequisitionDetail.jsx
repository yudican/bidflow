import {
  CheckCircleOutlined,
  ClockCircleOutlined,
  CloseCircleOutlined,
  LoadingOutlined,
  DeleteFilled,
  LinkOutlined,
  PrinterTwoTone,
  UploadOutlined,
  WarningFilled,
} from "@ant-design/icons"
import {
  Button,
  Card,
  Form,
  Popconfirm,
  Table,
  Tag,
  Upload,
  message,
  Tooltip,
  Spin,
  Menu,
  Dropdown,
} from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import ModalPriceRequisition from "../../components/Modal/ModalPriceRequisition"
import Layout from "../../components/layout"
import { formatDate, formatNumber, getItem, inArray } from "../../helpers"
import ModalGeneratePo from "./Components/ModalGeneratePo"
import ModalNotes from "./Components/ModalNotes"
import { renderStatusRequisitionComponent } from "./config"

const TableInformation = ({ title = "Company", value = "PT AIMI Group" }) => {
  return (
    <div>
      <tr>
        <td className="w-28 lg:w-36 ">
          <h3 className="font-semibold">{title}</h3>
        </td>
        <td className="w-4">:</td>
        <td>
          <h3>{value}</h3>
        </td>
      </tr>
    </div>
  )
}

const PurchaseRequisitionDetail = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const { purchase_requisition_id } = useParams()
  const [loading, setLoading] = React.useState(false)
  const [loadingApprove, setLoadingApprove] = React.useState(false)
  const [detail, setDetail] = React.useState({})
  const role = localStorage.getItem("role")
  const userData = getItem("user_data", true)
  const [roles, setRoles] = useState([])
  const [initialValues, setInitialValues] = useState([])
  const [loadingUpload, setLoadingUpload] = useState(false)
  const [isModalOpen, setIsModalOpen] = useState(false)

  const [fileList, setFileList] = useState([])

  const handleChange = ({ fileList: newFileList }) => {
    newFileList.map((file) => {
      const size = file.size / 1024
      if (size > 1024) {
        return message.error("Maksimum ukuran file adalah 1 MB")
      }
      setFileList([...fileList, file])
    })
  }

  const loadDetail = () => {
    setLoading(true)
    axios
      .get(`/api/purchase/purchase-requitition/${purchase_requisition_id}`)
      .then((res) => {
        const { data } = res.data
        // console.log(data?.attachment_url, "cek data")
        setLoading(false)
        const newLeadApproval = data.approval_leads.map((item) => {
          return {
            ...item,
            show: userData?.id == item.user_id,
          }
        })
        setDetail({
          ...data,
          approval_leads: newLeadApproval,
        })
        if (data?.attachment) {
          setInitialValues(data?.attachment_url?.split(","))
        } else {
          setInitialValues([])
        }

        const forms = {
          received_by_name: data.received_by_name,
          received_role_id: data.received_role_id,
          received_address: data.received_address,
        }

        form.setFieldsValue(forms)
      })
      .catch((e) => setLoading(false))
  }

  const approvePurchaseOrder = () => {
    setLoadingApprove(true)
    axios
      .post(
        `/api/purchase/purchase-requitition/approve/${purchase_requisition_id}`,
        {
          status: 1,
        }
      )
      .then((res) => {
        setLoadingApprove(false)
        toast.success("Approve berhasil", {
          position: toast.POSITION.TOP_RIGHT,
        })
        loadDetail()
      })
      .catch((err) => {
        setLoadingApprove(false)
        toast.error("Approve gagal", {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }

  const handleComplete = () => {
    if (detail?.request_status < 1) {
      return toast.error("Proses Approval Belum Dilakukan atau selesai")
    }

    if (detail?.attachment === "" || !detail?.attachment) {
      return toast.error("Anda belum mengupload attacment")
    }
    axios
      .post(
        `/api/purchase/purchase-requitition/complete/${purchase_requisition_id}`
      )
      .then((res) => {
        setLoadingApprove(false)
        toast.success("Status berhasil diupdate")
        loadDetail()
      })
      .catch((err) => {
        setLoadingApprove(false)
        toast.error("Status gagal diupdate")
      })
  }

  const updateApprovalStatus = (approval_id, status) => {
    const account_id = localStorage.getItem("account_id")
    axios
      .post(
        `/api/purchase/purchase-requitition/approval/status/${approval_id}`,
        {
          status,
          purchase_requisition_id,
          account_id,
        }
      )
      .then((res) => {
        toast.success("Status berhasil diupdate", {
          position: toast.POSITION.TOP_RIGHT,
        })
        loadDetail()
      })
      .catch((err) => {
        toast.error("Status gagal diupdate", {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }

  const loadRole = () => {
    axios.get(`/api/master/role/${role}`).then((res) => {
      setRoles(res.data.data)
    })
  }

  useEffect(() => {
    loadRole()
    loadDetail()
  }, [])

  const uploadAttachment = () => {
    setLoadingUpload(true)
    const formData = new FormData()
    for (let i = 0; i < fileList.length; i++) {
      formData.append(`attachments[${i}]`, fileList[i].originFileObj)
    }

    axios
      .post(
        `/api/purchase/purchase-requitition/attachment/upload/${detail?.id}`,
        formData
      )
      .then((res) => {
        setLoadingUpload(false)
        toast.success("Attachment berhasil diupload")
        setFileList([])
        loadDetail()
      })
      .catch((err) => {
        setLoadingUpload(false)
        toast.error("Attachment gagal diupload")
      })
  }

  const deleteAttachment = (attachments) => {
    console.log(attachments, "attachments")
    axios
      .post(
        `/api/purchase/purchase-requitition/attachment/delete/${detail?.id}`,
        {
          attachments: attachments
            .filter((item) => item.attachment)
            .map((item) => "upload/purchase/attachment/" + item.attachment),
        }
      )
      .then((res) => {
        loadDetail()
        toast.success("Attachment berhasil dihapus")
      })
      .catch((err) => {
        toast.error("Attachment gagal dihapus")
      })
  }

  const handleGeneratePO = () => {
    setIsModalOpen(true)
  }

  const handleCloseModal = () => {
    setIsModalOpen(false)
  }

  const handleSubmitModal = (kategoriPR) => {
    // handle the form submission for generating PO
    setIsModalOpen(false)
  }

  // const role = getItem("role")
  const isWaitingApproval = detail?.request_status || "-" === "0"
  const isFinance = inArray(role, [
    "finance",
    "lead_finance",
    "superadmin",
    "admin",
  ])

  const isSuperadmin = inArray(role, ["superadmin"])

  const canApprove = isFinance && isWaitingApproval
  const canPrintPdf = inArray(role, [
    "finance",
    "purchasing",
    "superadmin",
    "lead_finance",
    "hrd",
  ])
  const roleApproval =
    detail?.approval_leads?.map((item) => item.role_type) || []
  const canVerifOrder = inArray(role, [...roleApproval, "superadmin", "hrd"])
  const canApproveDivision = inArray(role, ["superadmin", "hrd"])
  const canComplete = detail?.approval_count || "-" == 3
  const requestStatus = detail?.request_status

  const rightContent = (
    <div className="flex items-center">
      {canComplete && (
        <button
          onClick={() =>
            requestStatus == 2 ? handleGeneratePO() : handleComplete()
          }
          className="mr-4 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
          title={requestStatus === 2 ? "Generate PO" : "Complete"}
        >
          {/* <CheckOutlined className="md:mr-2" /> */}
          <span className="hidden md:block">
            {requestStatus == 2 ? "Generate PO" : "Complete"}
          </span>
        </button>
      )}
      <ModalGeneratePo
        isOpen={isModalOpen}
        onClose={handleCloseModal}
        onSubmit={handleSubmitModal}
      />
      {/* {canApprove ? (
        <>
          <button
            onClick={() => rejectPurchaseOrder()}
            className="mr-4 text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
            title="Reject"
          >
            <CloseOutlined className="md:mr-2" />{" "}
            <span className="hidden md:block">Reject</span>
          </button>

          <button
            onClick={() => approvePurchaseOrder()}
            className="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
            title="Reject"
          >
            {loadingApprove ? (
              <LoadingOutlined />
            ) : (
              <CheckCircleOutlined className="md:mr-2" />
            )}
            <span className="hidden md:block">Approve</span>
          </button>
        </>
      ) : null} */}
    </div>
  )

  const attachments = fileList.map((item, index) => {
    return {
      id: index,
      attachment: item.name,
      url: "-",
      status: requestStatus,
    }
  })

  const urls =
    (initialValues && initialValues.length > 0 && initialValues) || []

  console.log(urls, "cek url")
  const attachments2 = urls.map((item, index) => {
    console.log(item, "cek item")
    const attachment = item?.split("/attachment/")
    return {
      id: index,
      attachment: attachment.length > 0 ? attachment[1] : item,
      url: item,
      status: requestStatus,
    }
  })

  const finalAttachments =
    attachments2.length > 0 ? [...attachments2, ...attachments] : attachments
  console.log(finalAttachments, "finalAttachments")
  // const [auto, setAuto] = React.useState(false)
  // const [percent, setPercent] = React.useState(-50)
  // React.useEffect(() => {
  //   const timeout = setTimeout(() => {
  //     setPercent((v) => {
  //       const nextPercent = v + 2.5
  //       return nextPercent > 150 ? -50 : nextPercent
  //     })
  //   }, 100)
  //   return () => {
  //     clearTimeout(timeout)
  //   }
  // }, [percent])

  // const mergedPercent = auto ? "auto" : percent
  const printMenu = (
    <Menu>
      <Menu.Item key="1">
        <a
          href={"/purchase/purchase-requitition/print/" + detail?.id || "-"}
          target="_blank"
        >
          Print
        </a>
      </Menu.Item>
      {isSuperadmin && (
        <Menu.Item key="2">
          <a
            href={
              "/purchase/purchase-requitition/print-nostamp/" + detail?.id ||
              "-"
            }
            target="_blank"
          >
            Print With No Stampel
          </a>
        </Menu.Item>
      )}
    </Menu>
  )

  return (
    <Layout
      title="Proses Data Purchase Requisition"
      href="/purchase/purchase-requisition"
      rightContent={rightContent}
    >
      <Card
        title="Informasi Purchase Requisition"
        extra={
          <div className="flex items-center">
            <div className="flex justify-end items-center">
              <strong className="mr-2">Status :</strong>
              {renderStatusRequisitionComponent(detail?.request_status || "-")}
            </div>
            {/* {canPrintPdf && (
              <a
                href={
                  "/purchase/purchase-requitition/print/" + detail?.id || "-"
                }
                target="_blank"
              >
                <Button className="ml-4" title="Print">
                  <PrinterTwoTone />
                </Button>
              </a>
            )} */}
            {canPrintPdf && (
              <Dropdown overlay={printMenu}>
                <Button className="ml-4" title="Print">
                  <PrinterTwoTone />
                </Button>
              </Dropdown>
            )}
          </div>
        }
      >
        <div className="card-body grid md:grid-cols-3 gap-4">
          <TableInformation
            title="PR Number"
            value={detail?.pr_number || "-"}
          />
          <TableInformation
            title="Company"
            value={
              detail?.company_account_name ||
              "PT Anugrah Inovasi Makmur Indonesia"
            }
          />
          {/* <TableInformation
            title="Vendor Code"
            value={detail?.vendor_code || "-"}
          />
          <TableInformation
            title="Vendor Name"
            value={detail?.vendor_name || "-"}
          /> */}
          <TableInformation
            title="Created On"
            value={formatDate(detail?.created_at) || "-"}
          />
          <TableInformation
            title="Request by"
            value={detail?.request_by_name || "-"}
          />
          {/* <TableInformation title="Currency ID" value={"Rp (Rupiah)"} /> */}
          {/* <TableInformation
            title="Payment Term"
            value={detail?.payment_term_name || "-"}
          /> */}
          <TableInformation
            title="Request Division"
            value={detail?.request_by_division || "-"}
          />
          <TableInformation
            title="Created by"
            value={detail?.created_by_name || detail?.request_by_name || "-"}
          />
          <TableInformation
            title="Notes"
            value={<ModalNotes value={detail?.request_note || "-" || "-"} />}
          />
          <TableInformation title="Brand" value={detail?.brand_name || "-"} />
          {requestStatus == 6 && (
            <div className="md:col-span-3 bg-red-50">
              <Tag
                className="p-2 w-full"
                icon={<WarningFilled />}
                color="warning"
              >
                Reject Reason : {detail?.rejected_reason || "-"}
              </Tag>
            </div>
          )}
          <div className="lg:col-span-3 mt-4">
            <h1 className="border-b-2  text-base font-medium pb-4 mb-4">
              Detail Item
            </h1>
            <Table
              // rowSelection={rowSelection}
              dataSource={detail?.items || []}
              columns={[
                {
                  title: "No",
                  align: "center",
                  render: (text, record, index) => index + 1,
                },
                {
                  title: "Nama Item",
                  dataIndex: "item_name",
                  key: "item_name",
                },
                {
                  title: "QTY",
                  dataIndex: "item_qty",
                  key: "item_qty",
                },
                // {
                //   title: "Price",
                //   dataIndex: "item_price",
                //   key: "item_price",
                //   render: (text, record, index) => {
                //     return (
                //       <ModalPriceRequisition
                //         value={formatNumber(text, "Rp. ")}
                //         initialValues={{ item_price: text }}
                //         url={`/api/purchase/purchase-requitition/approval/price/${record.id}`}
                //         refetch={() => loadDetail()}
                //         disabled={
                //           !inArray(role, [
                //             "superadmin",
                //             "finance",
                //             "purchasing",
                //           ])
                //         }
                //       />
                //     )
                //   },
                // },
                // {
                //   title: "Tax",
                //   dataIndex: "item_tax",
                //   key: "item_tax",
                // },
                // {
                //   title: "Subtotal",
                //   dataIndex: "item_subtotal",
                //   key: "item_subtotal",
                //   render: (text, record, index) => {
                //     return formatNumber(text, "Rp. ")
                //   },
                // },
                {
                  title: "Url",
                  dataIndex: "item_url",
                  key: "item_url",
                  render: (text, record, index) => {
                    if (text) {
                      const url =
                        text.startsWith("http://") ||
                        text.startsWith("https://")
                          ? text
                          : `http://${text}`
                      return (
                        <a href={url} target="_blank" rel="noopener noreferrer">
                          Open URL
                        </a>
                      )
                    }

                    return "-"
                  },
                },
                {
                  title: "Notes",
                  dataIndex: "item_note",
                  key: "item_note",
                  render: (text, record, index) => {
                    return text ? text : "-"
                  },
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
      </Card>
      {canVerifOrder && (
        <div className="card p-4 my-4">
          <Card
            title={
              <div>
                <span className="mr-4">Informasi Approval</span>
              </div>
            }
          >
            <div className="row">
              <div className="col-md-12 mt-4">
                <Table
                  // rowSelection={rowSelection}
                  dataSource={detail?.approval_leads || []}
                  columns={[
                    {
                      title: "No",
                      align: "center",
                      render: (text, record, index) => index + 1,
                    },
                    {
                      title: "Approval",
                      dataIndex: "label",
                      key: "label",
                    },
                    {
                      title: "Contact",
                      dataIndex: "user_name",
                      key: "user_name",
                    },
                    {
                      title: "Role",
                      dataIndex: "role_name",
                      key: "role_name",
                    },
                    {
                      title: "Status",
                      dataIndex: "status",
                      key: "status",
                      align: "center",
                      render: (text, record, index) => {
                        if (record.label == "Excecuted by") {
                          return <span>-</span>
                        }

                        if (record.status == 1) {
                          return (
                            <Tooltip title="Sudah dikonfirmasi">
                              <CheckCircleOutlined style={{ color: "green" }} />
                            </Tooltip>
                          )
                        }
                        if (record.status == 2) {
                          return (
                            <Tooltip title="Sudah direject">
                              <CloseCircleOutlined style={{ color: "red" }} />
                            </Tooltip>
                          )
                        }
                        if (record.status == 3) {
                          return (
                            <Tooltip title="Sedang menunggu approval">
                              <ClockCircleOutlined
                                style={{ color: "orange" }}
                              />
                            </Tooltip>
                          )
                        }

                        return (
                          <Tooltip title="Sedang menunggu approval">
                            <ClockCircleOutlined style={{ color: "orange" }} />
                          </Tooltip>
                        )
                      },
                    },
                    {
                      title: "Action",
                      dataIndex: "action",
                      key: "action",
                      align: "center",
                      render: (text, record, index) => {
                        if (record.label == "Excecuted by") {
                          return <span>-</span>
                        }
                        if (record.status == 1) {
                          return (
                            <Tooltip title="Sudah dikonfirmasi">
                              <CheckCircleOutlined style={{ color: "green" }} />
                            </Tooltip>
                          )
                        }
                        if (record.status == 2) {
                          return (
                            <Tooltip title="Sudah direject">
                              <CloseCircleOutlined style={{ color: "red" }} />
                            </Tooltip>
                          )
                        }

                        if (record.status == 3) {
                          return (
                            <Tooltip title="Sedang menunggu approval">
                              <ClockCircleOutlined
                                style={{ color: "orange" }}
                              />
                            </Tooltip>
                          )
                        }

                        if (canApproveDivision) {
                          if (
                            inArray(record.label, [
                              "Verified by",
                              "Excecuted by",
                            ])
                          ) {
                            if (role == "hrd") {
                              if (record.status == 1) {
                                return (
                                  <Tooltip title="Sudah dikonfirmasi">
                                    <CheckCircleOutlined
                                      style={{ color: "green" }}
                                    />
                                  </Tooltip>
                                )
                              }
                              if (record.status == 2) {
                                return (
                                  <Tooltip title="Sudah direject">
                                    <CloseCircleOutlined
                                      style={{ color: "red" }}
                                    />
                                  </Tooltip>
                                )
                              }

                              if (inArray(record.status, [3, 0])) {
                                return (
                                  <Tooltip title="Sedang menunggu approval">
                                    <ClockCircleOutlined
                                      style={{ color: "orange" }}
                                    />
                                  </Tooltip>
                                )
                              }
                            }
                          }
                          return (
                            <div
                              style={{
                                display: "flex",
                                justifyContent: "center",
                                alignItems: "center",
                              }}
                            >
                              <Button
                                size="small"
                                className="mr-2"
                                disabled={record.status >= 1}
                                onClick={() =>
                                  updateApprovalStatus(record.id, 2)
                                }
                                style={{
                                  padding: "0",
                                  display: "flex",
                                  alignItems: "center",
                                  justifyContent: "center",
                                  height: "32px",
                                  width: "32px",
                                }}
                              >
                                <CloseCircleOutlined
                                  style={{ color: "red", fontSize: "16px" }}
                                />
                              </Button>
                              &nbsp;
                              <Button
                                size="small"
                                className="mr-2"
                                disabled={record.status >= 1}
                                onClick={() =>
                                  updateApprovalStatus(record.id, 1)
                                }
                                style={{
                                  padding: "0",
                                  display: "flex",
                                  alignItems: "center",
                                  justifyContent: "center",
                                  height: "32px",
                                  width: "32px",
                                }}
                              >
                                <CheckCircleOutlined
                                  style={{ color: "green", fontSize: "16px" }}
                                />
                              </Button>
                            </div>
                          )
                        }

                        if (record.show) {
                          return (
                            <div>
                              <Button
                                size="small"
                                className="mr-2"
                                onClick={() =>
                                  updateApprovalStatus(record.id, 2)
                                }
                              >
                                <CloseCircleOutlined style={{ color: "red" }} />
                              </Button>
                              <Button
                                size="small"
                                onClick={() =>
                                  updateApprovalStatus(record.id, 1)
                                }
                              >
                                <CheckCircleOutlined
                                  style={{ color: "green" }}
                                />
                              </Button>
                            </div>
                          )
                        }
                      },
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
          </Card>
        </div>
      )}

      <div className="card p-4 my-4">
        <Card
          title={
            <div>
              <span className="mr-4">Log Approval</span>
            </div>
          }
        >
          <div className="row">
            <div className="col-md-12 mt-4">
              <Table
                dataSource={detail?.approval_log || []}
                columns={[
                  {
                    title: "No",
                    align: "center",
                    render: (text, record, index) => index + 1,
                  },
                  {
                    title: "Action",
                    dataIndex: "action",
                    key: "action",
                  },
                  {
                    title: "Executed By",
                    dataIndex: "user_name",
                    key: "user_name",
                  },
                  {
                    title: "Executed Date",
                    dataIndex: "created_at",
                    key: "created_at",
                    render: (text) => {
                      return formatDate(text)
                    },
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
        </Card>
      </div>

      <div className="card p-4 my-4">
        <Card
          title="Attachment"
          extra={
            requestStatus < 2 && (
              <div className="flex items-center">
                <Button
                  type="primary"
                  onClick={() => uploadAttachment()}
                  loading={loadingUpload}
                  disabled={loadingUpload}
                >
                  Save
                </Button>
              </div>
            )
          }
        >
          {requestStatus < 2 && (
            <Upload
              name="attachments"
              showUploadList={false}
              multiple={true}
              fileList={fileList}
              beforeUpload={() => false}
              onChange={(e) => {
                handleChange({
                  ...e,
                })
              }}
              className="w-full"
            >
              <Button
                className="mr-3 mb-4"
                icon={<UploadOutlined />}
                // loading={loadingAtachment}
              >
                Upload
              </Button>
            </Upload>
          )}
          <AttachmentTable
            isLoadingUpload={loadingUpload}
            attachments={finalAttachments.filter((item) => item.attachment)}
            onChange={(index) => {
              const files = [...finalAttachments]
              const newFileLists = files.filter((file, key) => key != index)
              setFileList(newFileLists)
              deleteAttachment(newFileLists)
            }}
          />
        </Card>
      </div>

      {/* <div className="card p-4">
        <Card title="Informasi Penerimaan Item">
          <Form form={form} layout="vertical">
            <div className="card-body grid md:grid-cols-2 gap-4">
              <Form.Item
                label="Received by"
                name="received_by_name"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Received by!",
                  },
                ]}
              >
                <Input disabled />
              </Form.Item>

              <Form.Item
                label="Role (Automatic)"
                name="received_role_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Role!",
                  },
                ]}
              >
                <Select
                  disabled
                  placeholder="Pilih Role"
                  onChange={(e) => {
                    const role = roles.find((role) => role.id === e)
                    setRoleSelected(role.role_type)
                  }}
                >
                  {roles.map((role) => (
                    <Select.Option value={role.id} key={role.id}>
                      {role.role_name}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>

              <div className="md:col-span-2">
                <Form.Item
                  requiredMark={"Automatic"}
                  label="Detail Alamat Penerima"
                  name="received_address"
                  rules={[
                    {
                      required: false,
                      message: "Silakan masukkan Warehouse!",
                    },
                  ]}
                >
                  <TextArea
                    placeholder="Silakan input catatan.."
                    showCount
                    disabled
                    maxLength={100}
                  />
                </Form.Item>
              </div>
              {detail?.attachment_url && (
                <a href={detail?.attachment_url || "#"} target="_blank">
                  Show Attachment
                </a>
              )}
            </div>
          </Form>
        </Card>
      </div> */}
    </Layout>
  )
}

const AttachmentTable = ({ attachments, onChange, isLoadingUpload }) => {
  return (
    <Table
      dataSource={attachments}
      rowKey={"id"}
      className="w-full"
      pagination={false}
      columns={[
        {
          title: "No",
          dataIndex: "id",
          key: "id",
          width: 10,
          render: (text, record, index) => index + 1,
        },
        {
          title: "Attachment",
          dataIndex: "attachment",
          key: "attachment",
          render: (text, record, index) => {
            const showProgress = record.url === "-" && isLoadingUpload
            return (
              <div>
                {text}{" "}
                <span>
                  {showProgress && (
                    <Spin
                      indicator={
                        <LoadingOutlined
                          style={{
                            fontSize: 12,
                          }}
                          spin
                        />
                      }
                    />
                  )}
                </span>
              </div>
            )
          },
        },
        {
          title: "Url",
          dataIndex: "url",
          key: "url",
          render: (text) => {
            if (text) {
              if (text != "-") {
                return (
                  <a href={text} target="_blank">
                    <LinkOutlined />
                  </a>
                )
              }

              return "#"
            }
          },
        },
        {
          title: "",
          dataIndex: "action",
          key: "action",
          width: 10,
          render: (text, record, index) => {
            const role = localStorage.getItem("role")
            const canDelete = inArray(role, ["superadmin", "hrd"])

            if (canDelete && record.status < 2) {
              return (
                // <DeleteFilled
                //   className="cursor-pointer"
                //   onClick={() => {
                //     onChange(index);
                //   }}
                // />
                <Popconfirm
                  title="Apakah anda yakin ingin menghapus attachment ini?"
                  onConfirm={() => onChange(index)}
                  // onCancel={cancel}
                  okText="Ya, Hapus"
                  cancelText="Batal"
                >
                  <DeleteFilled></DeleteFilled>
                </Popconfirm>
              )
            }

            return null
          },
        },
      ]}
    />
  )
}

const PengirimanTable = ({ attachments, onChange }) => {
  return (
    <Table
      dataSource={attachments}
      rowKey={"id"}
      className="w-full"
      pagination={false}
      columns={[
        {
          title: "No",
          dataIndex: "id",
          key: "id",
          width: 10,
          render: (text, record, index) => index + 1,
        },
        {
          title: "Received Number",
          dataIndex: "attachment",
          key: "attachment",
        },
        {
          title: "Item Name",
          dataIndex: "attachment",
          key: "attachment",
        },
        {
          title: "Qty",
          dataIndex: "attachment",
          key: "attachment",
        },
        {
          title: "Total Tax",
          dataIndex: "attachment",
          key: "attachment",
        },
        {
          title: "Qty Diterima",
          dataIndex: "attachment",
          key: "attachment",
        },
      ]}
    />
  )
}

export default PurchaseRequisitionDetail
