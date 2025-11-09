import { CheckOutlined, LoadingOutlined } from "@ant-design/icons"
import { Card, DatePicker, Form, Input, Select } from "antd"
import React, { useEffect, useState } from "react"
import "react-draft-wysiwyg/dist/react-draft-wysiwyg.css"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../../components/layout"
import "../../../index.css"
import moment from "moment"

const OngkirForm = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const { master_ongkir_id } = useParams()

  const [loadingSubmit, setLoadingSubmit] = useState(false)
  const [logistics, setLogistic] = useState([])

  const loadDetailBrand = () => {
    axios.get(`/api/master/ongkir/${master_ongkir_id}`).then((res) => {
      const { data } = res.data

      form.setFieldsValue({
        ...data,
        logistic_id: data.logistic.map((item) => item.id),
        start_date: moment(data?.start_date ?? new Date(), "YYYY-MM-DD"),
        end_date: moment(data?.end_date ?? new Date(), "YYYY-MM-DD"),
      })
    })
  }

  const loadLogistic = () => {
    axios.get(`/api/master/logistic`).then((res) => {
      const { data } = res.data
      const newData = data.filter((item) => item.logistic_type === "online")
      setLogistic(newData)
    })
  }

  useEffect(() => {
    loadDetailBrand()
    loadLogistic()
  }, [])

  const onFinish = (values) => {
    setLoadingSubmit(true)

    const url = master_ongkir_id
      ? `/api/master/ongkir/save/${master_ongkir_id}`
      : "/api/master/ongkir/save"

    axios
      .post(url, {
        ...values,
        start_date: values.start_date.format("YYYY-MM-DD"),
        end_date: values.end_date.format("YYYY-MM-DD"),
      })
      .then((res) => {
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        setLoadingSubmit(false)
        return navigate("/master/ongkir")
      })
      .catch((err) => {
        const { message } = err.response.data
        setLoadingSubmit(false)
        toast.error(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }

  return (
    <>
      <Layout
        title="Ongkir"
        href="/master/ongkir"
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
          <Card title="Ongkir Data">
            <div className="card-body row">
              <div className="col-md-6">
                <Form.Item
                  label="Nama Ongkir"
                  name="nama_ogkir"
                  rules={[
                    {
                      required: true,
                      message: "Silakan masukkan Nama Ongkir!",
                    },
                  ]}
                >
                  <Input placeholder="Ketik Nama Ongkir" />
                </Form.Item>
                <Form.Item
                  label="Kode Ongkir"
                  name="kode_ongkir"
                  rules={[
                    {
                      required: true,
                      message: "Silakan masukkan Kode Ongkir!",
                    },
                  ]}
                >
                  <Input placeholder="Ketik Kode Ongkir" />
                </Form.Item>
                <Form.Item
                  label="Nominal Ongkir"
                  name="harga_ongkir"
                  rules={[
                    {
                      required: true,
                      message: "Silakan masukkan Nominal Ongkir!",
                    },
                  ]}
                >
                  <Input placeholder="Ketik Nominal Ongkir" />
                </Form.Item>
              </div>
              <div className="col-md-6">
                <Form.Item
                  label="Start Date"
                  name="start_date"
                  rules={[
                    {
                      required: true,
                      message: "Silakan pilih Start Date!",
                    },
                  ]}
                >
                  <DatePicker className="w-full" format={"DD-MM-YYYY"} />
                </Form.Item>
                <Form.Item
                  label="End Date"
                  name="end_date"
                  rules={[
                    {
                      required: true,
                      message: "Silakan pilih End Date!",
                    },
                  ]}
                >
                  <DatePicker className="w-full" format={"DD-MM-YYYY"} />
                </Form.Item>
                <Form.Item
                  label="Logistic"
                  name="logistic_id"
                  rules={[
                    {
                      required: true,
                      message: "Silakan pilih Logistic!",
                    },
                  ]}
                >
                  <Select
                    mode="multiple"
                    allowClear
                    className="w-full mb-2"
                    placeholder="Pilih Logistic"
                  >
                    {logistics.map((item) => (
                      <Select.Option key={item.id} value={item.id}>
                        {/* image */}
                        <div className="flex items-center">
                          <img
                            src={item.logistic_url_logo}
                            alt=""
                            style={{ width: 40 }}
                          />
                          <span className="ml-2">{item.logistic_name}</span>
                        </div>
                      </Select.Option>
                    ))}
                  </Select>
                </Form.Item>
              </div>
            </div>
          </Card>
        </Form>
        <div className="card ">
          <div className="card-body flex justify-end">
            <button
              onClick={() => form.submit()}
              className="text-white bg-blueColor hover:bg-blueColor/90 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
            >
              {loadingSubmit ? <LoadingOutlined /> : <CheckOutlined />}
              <span className="ml-2">Simpan</span>
            </button>
          </div>
        </div>
      </Layout>
    </>
  )
}

export default OngkirForm
