import { EditOutlined, LoadingOutlined, PlusOutlined } from "@ant-design/icons"
import { DatePicker, Form, Input, Modal, Upload, message } from "antd"
import { useForm } from "antd/es/form/Form"
import TextArea from "antd/lib/input/TextArea"
import React, { useState } from "react"
import { toast } from "react-toastify"
import { getBase64 } from "../../../helpers"
import axios from "axios"
import moment from "moment"

import "../../../index.css"

const ModalActivity = ({
  refetch,
  detail,
  initialValues = {},
  update = false,
}) => {
  const [form] = useForm()
  const [loadingSubmit, setLoadingSubmit] = useState(false)
  const [loading, setLoading] = useState({
    attachment: false,
  })

  const [imageUrl, setImageUrl] = useState({
    attachment: null,
  })

  const [fileList, setFileList] = useState({
    attachment: null,
  })

  const [isModalOpen, setIsModalOpen] = useState(false)

  const showModal = () => {
    setIsModalOpen(true)
  }

  const handleCancel = () => {
    setIsModalOpen(false)
  }

  const handleChange = ({ fileList, field }) => {
    const list = fileList.pop()
    setLoading({ ...loading, [field]: true })
    setTimeout(() => {
      const size = list.size / 1024
      if (size > 1024) {
        setLoading({ ...loading, [field]: false })
        return message.error("Maksimum ukuran file adalah 1 MB")
      }
      getBase64(list.originFileObj, (url) => {
        setLoading({ ...loading, [field]: false })
        setImageUrl({ ...imageUrl, [field]: url })
      })
      setFileList({ ...fileList, [field]: list.originFileObj })
    }, 1000)
  }

  const onFinish = (value) => {
    setLoadingSubmit(true)
    let formData = new FormData()

    if (fileList.attachment) {
      formData.append("attachment", fileList.attachment)
    }

    formData.append("uid_lead", detail.uid_lead)
    formData.append("title", value.title)
    formData.append("description", value.description ?? "-")
    formData.append("latitude", coorData?.lat ?? 0)
    formData.append("longitude", coorData?.lng ?? 0)
    formData.append("address_name", address ?? "-")
    formData.append(
      "start_date",
      value.start_date.format("YYYY-MM-DD HH:mm:ss")
    )
    formData.append("end_date", value.end_date.format("YYYY-MM-DD HH:mm:ss"))
    formData.append("result", value.result ?? "-")
    let url = `/api/lead-master/activity/create`
    if (initialValues?.id) {
      url = `/api/lead-master/activity/update/${initialValues?.id}`
    }
    axios
      .post(url, formData)
      .then((res) => {
        const { message } = res.data
        refetch()
        setFileList({
          attachment: null,
        })
        setImageUrl({
          attachment: null,
        })
        toast.success(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        setIsModalOpen(false)
        setLoadingSubmit(false)
        form.resetFields()
      })
      .catch((error) => {
        const { message } = error.response.data
        toast.error(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        setLoadingSubmit(false)
      })
  }

  // handle geolocation
  const [address, setAddress] = useState("")
  const [coorData, setCoorData] = useState({})
  // const handleSelectAddress = (address) => {
  //   geocodeByAddress(address)
  //     .then((results) => getLatLng(results[0]))
  //     .then((latLng) => {
  //       setCoorData(latLng);
  //       console.log("Success", latLng);
  //     })
  //     .catch((error) => console.error("Error", error));
  // };
  // console.log(address, "address");
  // console.log(coorData, "coorData");
  // useScript(
  //   `https://maps.googleapis.com/maps/api/js?key=${mapApiKey}&libraries=places`
  // );

  return (
    <div>
      {update ? (
        <button
          onClick={() => showModal()}
          className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
          title="Edit"
        >
          <EditOutlined />
        </button>
      ) : (
        <button
          onClick={() => showModal()}
          className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
        >
          <PlusOutlined />
          <span className="ml-2">Tambah Data</span>
        </button>
      )}

      <Modal
        title={update ? "Edit Activity" : "Tambah Activity"}
        open={isModalOpen}
        onOk={() => {
          form.submit()
        }}
        cancelText={"Cancel"}
        onCancel={handleCancel}
        okText={"Simpan"}
        confirmLoading={loadingSubmit}
      >
        <Form
          form={form}
          name="basic"
          layout="vertical"
          initialValues={{
            ...initialValues,
            start_date: moment(initialValues?.start_date),
            end_date: moment(initialValues?.end_date),
          }}
          onFinish={onFinish}
          //   onFinishFailed={onFinishFailed}
          autoComplete="off"
        >
          <div className="row">
            <div className="col-md-12">
              <Form.Item
                label={"Title"}
                name={"title"}
                rules={[
                  {
                    required: true,
                    message: "Field Tidak Boleh Kosong!",
                  },
                ]}
              >
                <Input />
              </Form.Item>

              <Form.Item
                label={"Description"}
                name={"description"}
                rules={[
                  {
                    // required: true,
                    message: "Field Tidak Boleh Kosong!",
                  },
                ]}
              >
                <TextArea />
              </Form.Item>
              {/* <Form.Item
                label={"Geolocation"}
                name={"geo_location"}
                rules={[
                  {
                    // required: true,
                    message: "Field Tidak Boleh Kosong!",
                  },
                ]}
              >
                <InputPlacesAutoComplete
                  value={address}
                  onChange={(val) => setAddress(val)}
                  onSelect={handleSelectAddress}
                />
              </Form.Item> */}
              <Form.Item
                label="Start Date"
                name="start_date"
                rules={[
                  {
                    required: true,
                    message: "Field Tidak Boleh Kosong!",
                  },
                ]}
              >
                <DatePicker
                  className="w-full"
                  showTime
                  format="YYYY-MM-DD HH:mm"
                />
              </Form.Item>
              <Form.Item
                label="End Date"
                name="end_date"
                rules={[
                  {
                    required: true,
                    message: "Field Tidak Boleh Kosong!",
                  },
                ]}
              >
                <DatePicker
                  className="w-full"
                  format="YYYY-MM-DD HH:mm"
                  showTime
                />
              </Form.Item>
              <Form.Item
                label={"Result"}
                name={"result"}
                rules={[
                  {
                    // required: true,
                    message: "Field Tidak Boleh Kosong!",
                  },
                ]}
              >
                <Input />
              </Form.Item>
            </div>
            <div className="col-md-6">
              <Form.Item
                label="Attachment"
                name="attachment"
                rules={[
                  {
                    required: false,
                    message: "Silakan masukkan Attachment!",
                  },
                ]}
              >
                <Upload
                  name="attachment_file"
                  listType="picture-card"
                  className="avatar-uploader w-100"
                  showUploadList={false}
                  multiple={false}
                  beforeUpload={() => false}
                  onChange={(e) =>
                    handleChange({
                      ...e,
                      field: "attachment",
                    })
                  }
                >
                  {imageUrl.attachment ? (
                    loading.attachment ? (
                      <LoadingOutlined />
                    ) : (
                      <img
                        src={imageUrl.attachment}
                        alt="avatar"
                        style={{
                          height: 104,
                        }}
                      />
                    )
                  ) : (
                    <div style={{ width: "100%" }}>
                      {loading.attachment ? (
                        <LoadingOutlined />
                      ) : (
                        <PlusOutlined />
                      )}
                      <div
                        style={{
                          marginTop: 8,
                          width: "100%",
                        }}
                      >
                        Upload
                      </div>
                    </div>
                  )}
                </Upload>
              </Form.Item>
            </div>
          </div>
        </Form>
      </Modal>
    </div>
  )
}

export default ModalActivity
