import { FormOutlined, PlusOutlined, UploadOutlined } from "@ant-design/icons"
import { Button, DatePicker, Form, Input, Modal, Select, Upload } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { getItem } from "../../helpers"

const ModalInputResi = ({
  onFinish,
  initialValues = {},
  hasInputed,
  fields = {},
}) => {
  const userData = getItem("user_data", true)
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [logistic, setLogistic] = useState([])
  const [form] = Form.useForm()

  // attachments
  const [loadingAtachment, setLoadingAtachment] = useState(false)

  const [fileList, setFileList] = useState([])

  const handleChange = ({ fileList: newFileList }) => {
    setFileList(newFileList)
  }

  const showModal = () => {
    setIsModalOpen(true)
  }
  const handleOk = () => {
    setIsModalOpen(false)
  }
  const handleCancel = () => {
    setIsModalOpen(false)
  }

  const loadLogistic = () => {
    axios
      .get("/api/master/logistic/offline")
      .then((res) => {
        const { data } = res.data
        setLogistic(data)
      })
      .catch((err) => {
        console.log(err)
      })
  }

  useEffect(() => {
    loadLogistic()
  }, [])

  return (
    <div>
      <button
        onClick={() => showModal()}
        className={`
        ${
          hasInputed
            ? "text-blue-700 bg-gray-100/20 hover:bg-gray-100 border-[1px] border-blue-700 "
            : "text-white bg-blue-700 hover:bg-blue-800 "
        }focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center`}
      >
        {hasInputed ? <FormOutlined /> : <PlusOutlined />}

        <span className="ml-2">{hasInputed ? "Edit Resi" : "Input Resi"}</span>
      </button>

      <Modal
        title={hasInputed ? "Edit Resi" : "Input Resi"}
        open={isModalOpen}
        onOk={() => {
          handleOk()
          form.submit()
          setIsModalOpen(false)
        }}
        cancelText={"Cancel"}
        onCancel={handleCancel}
        okText={"Proses Data"}
        width={800}
      >
        <div>
          <div className="card-body">
            <Form
              form={form}
              name="basic"
              layout="vertical"
              onFinish={(values) => {
                const formData = new FormData()
                for (let i = 0; i < fileList.length; i++) {
                  formData.append(`items[${i}]`, fileList[i].originFileObj)
                }
                {
                  values.expedition_name &&
                    formData.append("expedition_name", values.expedition_name)
                }
                {
                  values.sender_phone &&
                    formData.append("sender_phone", values.sender_phone)
                }
                {
                  values.resi && formData.append("resi", values.resi)
                }
                {
                  values.sender_name &&
                    formData.append("sender_name", values.sender_name)
                }
                {
                  userData?.id && formData.append("created_by", userData?.id)
                }
                formData.append(
                  "delivery_date",
                  values.delivery_date.format("YYYY-MM-DD")
                )
                // fields
                for (const [key, value] of Object.entries(fields)) {
                  formData.append(key, value)
                }
                onFinish(formData)
              }}
              // onFinishFailed={onFinishFailed}
              autoComplete="off"
              initialValues={{
                ...initialValues,
                created_by: userData?.id,
                user_created: userData?.name,
                delivery_date: moment(
                  initialValues?.delivery_date ?? new Date(),
                  "YYYY-MM-DD"
                ),
              }}
            >
              <div className="row">
                <div className="col-md-6">
                  <Form.Item label="User Created" name="user_created">
                    <Input disabled />
                  </Form.Item>
                  <Form.Item
                    label="Ekspedisi"
                    name="expedition_name"
                    rules={[
                      {
                        required: false,
                        message: "Silakan masukkan Ekspedisi!",
                      },
                    ]}
                  >
                    <Select
                      allowClear
                      className="w-full mb-2"
                      placeholder="Pilih Exspedisi"
                    >
                      {logistic.map((item) => (
                        <Select.Option key={item.id} value={item.logistic_name}>
                          {item.logistic_name}
                        </Select.Option>
                      ))}
                    </Select>
                  </Form.Item>
                  <Form.Item
                    label="Telepon Pengirim"
                    name="sender_phone"
                    rules={[
                      {
                        required: false,
                        message: "Silakan masukkan Telepon Pengirim!",
                      },
                    ]}
                  >
                    <Input />
                  </Form.Item>
                  <Form.Item label="Attachment" name="attachment">
                    <Upload
                      name="attachments"
                      showUploadList={true}
                      multiple={true}
                      fileList={fileList}
                      beforeUpload={() => false}
                      onChange={(e) => {
                        handleChange({
                          ...e,
                        })
                      }}
                    >
                      <Button
                        icon={<UploadOutlined />}
                        loading={loadingAtachment}
                      >
                        Upload (Multiple)
                      </Button>
                    </Upload>
                    {initialValues?.attachment_url &&
                      initialValues?.attachment_url.length > 0 && (
                        <div>
                          {initialValues?.attachment_url.map((item, index) => {
                            return (
                              <span>
                                <a href={item} target="_blank">
                                  Attachment {index + 1}
                                </a>
                                <br />
                              </span>
                            )
                          })}
                        </div>
                      )}
                  </Form.Item>
                </div>
                <div className="col-md-6">
                  <Form.Item
                    label="Resi"
                    name="resi"
                    rules={[
                      {
                        required: false,
                        message: "Silakan masukkan Resi!",
                      },
                    ]}
                  >
                    <Input />
                  </Form.Item>
                  <Form.Item
                    label="Nama Pengirim"
                    name="sender_name"
                    rules={[
                      {
                        required: false,
                        message: "Silakan masukkan Nama Pengirim!",
                      },
                    ]}
                  >
                    <Input />
                  </Form.Item>
                  <Form.Item
                    label="Delivery Date"
                    name="delivery_date"
                    rules={[
                      {
                        required: true,
                        message: "Silakan pilih Delivery Date!",
                      },
                    ]}
                  >
                    <DatePicker className="w-full" />
                  </Form.Item>
                </div>
              </div>
            </Form>
          </div>
        </div>
      </Modal>
    </div>
  )
}

export default ModalInputResi
