import {
  LoadingOutlined,
  MinusCircleOutlined,
  PlusOutlined,
} from "@ant-design/icons"
import {
  Card,
  Form,
  Input,
  DatePicker,
  Switch,
  Button,
  Row,
  Col,
  Space,
} from "antd"
import React, { useEffect, useState } from "react"
import { useNavigate, useParams } from "react-router-dom"
import { toast } from "react-toastify"
import axios from "axios"
import Layout from "../../../components/layout"
import moment from "moment"

const { TextArea } = Input

const FormUrlShortener = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const { url_shortener_id } = useParams()

  const [loadingSubmit, setLoadingSubmit] = useState(false)
  const [previewUrl, setPreviewUrl] = useState("")

  const loadDetailUrlShortener = () => {
    if (url_shortener_id) {
      axios
        .get(`/api/master/url-shortener/${url_shortener_id}`)
        .then((res) => {
          const { data } = res.data

          // Convert parameters object to array for form
          let parametersArray = []
          if (data.parameters) {
            // If parameters is already an object, convert to array
            if (typeof data.parameters === "object") {
              parametersArray = Object.entries(data.parameters).map(
                ([key, value]) => ({
                  key,
                  value,
                })
              )
            } else {
              // If parameters is a JSON string (legacy), parse it
              try {
                const parametersObj = JSON.parse(data.parameters)
                parametersArray = Object.entries(parametersObj).map(
                  ([key, value]) => ({
                    key,
                    value,
                  })
                )
              } catch (e) {
                console.warn("Failed to parse parameters JSON:", e)
              }
            }
          }

          // Format data for form
          const formData = {
            ...data,
            expires_at: data.expires_at ? moment(data.expires_at) : null,
            status: data.status === "active",
            parameters: parametersArray,
          }

          form.setFieldsValue(formData)
          setPreviewUrl(data.short_url)
        })
        .catch(() => {
          toast.error("Gagal memuat data URL shortener")
        })
    }
  }

  useEffect(() => {
    loadDetailUrlShortener()
  }, [url_shortener_id])

  const onFinish = (values) => {
    setLoadingSubmit(true)

    // Convert parameters array to object (not JSON string)
    let parametersObj = null
    if (values.parameters && values.parameters.length > 0) {
      const params = {}
      values.parameters.forEach((param) => {
        if (param.key && param.value) {
          params[param.key] = param.value
        }
      })
      parametersObj = Object.keys(params).length > 0 ? params : null
    }

    const formData = {
      original_url: values.original_url,
      title: values.title || "",
      description: values.description || "",
      short_code: values.short_code || "",
      expires_at: values.expires_at
        ? values.expires_at.format("YYYY-MM-DD HH:mm:ss")
        : null,
      status: values.status ? "active" : "inactive",
      parameters: parametersObj,
    }

    const url = url_shortener_id
      ? `/api/master/url-shortener/${url_shortener_id}`
      : "/api/master/url-shortener"

    const method = url_shortener_id ? "put" : "post"

    axios[method](url, formData)
      .then((res) => {
        toast.success(res.data.message || "URL shortener berhasil disimpan", {
          position: toast.POSITION.TOP_RIGHT,
        })
        setLoadingSubmit(false)
        return navigate("/master/url-shortener")
      })
      .catch((err) => {
        const message =
          err.response?.data?.message || "Gagal menyimpan URL shortener"
        setLoadingSubmit(false)
        toast.error(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }

  const onFinishFailed = (errorInfo) => {
    console.log("Failed:", errorInfo)
  }

  const handleUrlChange = (e) => {
    const url = e.target.value
    if (url && !url_shortener_id) {
      // Generate preview for new URLs
      setPreviewUrl(`utm-eight.vercel.app/s/[auto-generated]`)
    }
  }

  const handleShortCodeChange = (e) => {
    const code = e.target.value
    if (code) {
      setPreviewUrl(`utm-eight.vercel.app/s/${code}`)
    } else if (!url_shortener_id) {
      setPreviewUrl(`utm-eight.vercel.app/s/[auto-generated]`)
    }
  }

  return (
    <Layout>
      <div className="content-wrapper">
        <div className="content-header">
          <div className="container-fluid">
            <div className="row mb-2">
              <div className="col-sm-6">
                <h1 className="m-0">
                  {url_shortener_id ? "Edit" : "Tambah"} URL Shortener
                </h1>
              </div>
            </div>
          </div>
        </div>

        <section className="content">
          <div className="container-fluid">
            <Card>
              <Form
                form={form}
                name="url_shortener_form"
                layout="vertical"
                onFinish={onFinish}
                onFinishFailed={onFinishFailed}
                autoComplete="off"
              >
                <Row gutter={16}>
                  <Col span={24}>
                    <Form.Item
                      label="URL Asli"
                      name="original_url"
                      rules={[
                        {
                          required: true,
                          message: "Silakan masukkan URL asli!",
                        },
                        {
                          type: "url",
                          message: "Format URL tidak valid!",
                        },
                      ]}
                    >
                      <Input
                        placeholder="https://example.com"
                        onChange={handleUrlChange}
                      />
                    </Form.Item>
                  </Col>
                </Row>

                <Row gutter={16}>
                  <Col span={12}>
                    <Form.Item label="Judul" name="title">
                      <Input placeholder="Judul untuk URL shortener" />
                    </Form.Item>
                  </Col>
                  <Col span={12}>
                    <Form.Item
                      label="Kode Pendek (Opsional)"
                      name="short_code"
                      extra="Kosongkan untuk generate otomatis"
                    >
                      <Input
                        placeholder="custom-code"
                        onChange={handleShortCodeChange}
                      />
                    </Form.Item>
                  </Col>
                </Row>

                <Row gutter={16}>
                  <Col span={24}>
                    <Form.Item label="Deskripsi" name="description">
                      <TextArea
                        rows={3}
                        placeholder="Deskripsi untuk URL shortener"
                      />
                    </Form.Item>
                  </Col>
                </Row>

                {/* Dynamic Parameters Section */}
                <Row gutter={16}>
                  <Col span={24}>
                    <Form.Item label="Parameter Tambahan">
                      <Form.List name="parameters">
                        {(fields, { add, remove }) => (
                          <>
                            {fields.map(({ key, name, ...restField }) => (
                              <Row
                                key={key}
                                gutter={8}
                                style={{ marginBottom: 8 }}
                              >
                                <Col span={10}>
                                  <Form.Item
                                    {...restField}
                                    name={[name, "key"]}
                                    rules={[
                                      {
                                        required: true,
                                        message: "Parameter key diperlukan!",
                                      },
                                    ]}
                                  >
                                    <Input placeholder="Parameter key (contoh: utm_source)" />
                                  </Form.Item>
                                </Col>
                                <Col span={12}>
                                  <Form.Item
                                    {...restField}
                                    name={[name, "value"]}
                                    rules={[
                                      {
                                        required: true,
                                        message: "Parameter value diperlukan!",
                                      },
                                    ]}
                                  >
                                    <Input placeholder="Parameter value (contoh: facebook)" />
                                  </Form.Item>
                                </Col>
                                <Col span={2}>
                                  <Button
                                    type="text"
                                    danger
                                    icon={<MinusCircleOutlined />}
                                    onClick={() => remove(name)}
                                    style={{ marginTop: 4 }}
                                  />
                                </Col>
                              </Row>
                            ))}
                            <Form.Item>
                              <Button
                                type="dashed"
                                onClick={() => add()}
                                block
                                icon={<PlusOutlined />}
                              >
                                Tambah Parameter
                              </Button>
                            </Form.Item>
                          </>
                        )}
                      </Form.List>
                    </Form.Item>
                  </Col>
                </Row>

                <Row gutter={16}>
                  <Col span={12}>
                    <Form.Item
                      label="Tanggal Kadaluarsa"
                      name="expires_at"
                      extra="Kosongkan jika tidak ada batas waktu"
                    >
                      <DatePicker
                        showTime
                        format="YYYY-MM-DD HH:mm:ss"
                        placeholder="Pilih tanggal kadaluarsa"
                        style={{ width: "100%" }}
                      />
                    </Form.Item>
                  </Col>
                  <Col span={12}>
                    <Form.Item
                      label="Status"
                      name="status"
                      valuePropName="checked"
                      initialValue={true}
                    >
                      <Switch
                        checkedChildren="Aktif"
                        unCheckedChildren="Nonaktif"
                      />
                    </Form.Item>
                  </Col>
                </Row>

                {previewUrl && (
                  <Row gutter={16}>
                    <Col span={24}>
                      <Card size="small" title="Preview URL">
                        <p style={{ margin: 0, color: "#1890ff" }}>
                          {previewUrl}
                        </p>
                      </Card>
                    </Col>
                  </Row>
                )}

                <Row gutter={16} style={{ marginTop: 24 }}>
                  <Col span={24}>
                    <Space>
                      <Button
                        type="primary"
                        htmlType="submit"
                        loading={loadingSubmit}
                        icon={loadingSubmit ? <LoadingOutlined /> : null}
                      >
                        {loadingSubmit ? "Menyimpan..." : "Simpan"}
                      </Button>
                      <Button onClick={() => navigate("/master/url-shortener")}>
                        Batal
                      </Button>
                    </Space>
                  </Col>
                </Row>
              </Form>
            </Card>
          </div>
        </section>
      </div>
    </Layout>
  )
}

export default FormUrlShortener
