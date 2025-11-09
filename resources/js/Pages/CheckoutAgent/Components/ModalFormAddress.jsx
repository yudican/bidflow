import { PlusOutlined } from "@ant-design/icons"
import { Button, Form, Input, Modal, Select } from "antd"
import React, { useEffect, useState } from "react"
import { toast } from "react-toastify"
const ModalFormAddress = ({ initialValues = {}, refetch, update = false }) => {
  const [form] = Form.useForm()
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [provinsi, setProvinsi] = useState([])
  const [kabupaten, setKabupaten] = useState([])
  const [kecamatan, setKecamatan] = useState([])
  const [kelurahan, setKelurahan] = useState([])

  // loading
  const [loadingProvinsi, setLoadingProvinsi] = useState(false)
  const [loadingKabupaten, setLoadingKabupaten] = useState(false)
  const [loadingKecamatan, setLoadingKecamatan] = useState(false)
  const [loadingKelurahan, setLoadingKelurahan] = useState(false)

  const showModal = () => {
    setIsModalOpen(true)
  }

  const loadProvinsi = () => {
    setLoadingProvinsi(true)
    axios
      .get("/api/master/provinsi")
      .then((res) => {
        setProvinsi(res.data.data)
        setLoadingProvinsi(false)
      })
      .catch((err) => setLoadingProvinsi(false))
  }
  const loadKabupaten = (provinsi_id) => {
    setLoadingKabupaten(true)
    axios
      .get("/api/master/kabupaten/" + provinsi_id)
      .then((res) => {
        setKabupaten(res.data.data)
        setLoadingKabupaten(false)
      })
      .catch((err) => setLoadingKabupaten(false))
  }
  const loadKecamatan = (kabupaten_id) => {
    setLoadingKecamatan(true)
    axios
      .get("/api/master/kecamatan/" + kabupaten_id)
      .then((res) => {
        setKecamatan(res.data.data)
        setLoadingKecamatan(false)
      })
      .catch((err) => setLoadingKecamatan(false))
  }
  const loadKelurahan = (kelurahan_id) => {
    setLoadingKelurahan(true)
    axios
      .get("/api/master/kelurahan/" + kelurahan_id)
      .then((res) => {
        setKelurahan(res.data.data)
        setLoadingKelurahan(false)
      })
      .catch((err) => setLoadingKelurahan(false))
  }

  useEffect(() => {
    loadProvinsi()
    if (initialValues?.provinsi_id) {
      loadKabupaten(initialValues?.provinsi_id)
    }
    if (initialValues?.kabupaten_id) {
      loadKecamatan(initialValues?.kabupaten_id)
    }
    if (initialValues?.kecamatan_id) {
      loadKelurahan(initialValues?.kecamatan_id)
    }
  }, [
    initialValues?.provinsi_id,
    initialValues?.kabupaten_id,
    initialValues?.kecamatan_id,
  ])

  const handleSaveAddress = (values) => {
    axios
      .post("/api/contact/address/save-address", {
        ...initialValues,
        ...values,
      })
      .then((res) => {
        toast.success(res.data.message, {
          position: toast.POSITION.TOP_RIGHT,
        })
        setIsModalOpen(false)
        refetch()
      })
      .catch((err) => {
        const { message } = err.response.data
        toast.error(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }
  console.log(initialValues, "initialValues")
  return (
    <div style={{ width: update ? "49%" : "100%" }}>
      {update ? (
        <Button
          type="primary"
          color="#FFC120"
          className={"w-full"}
          onClick={() => showModal()}
          style={{
            backgroundColor: "#BE8900",
            borderColor: "#BE8900",
          }}
        >
          Ubah Alamat
        </Button>
      ) : (
        <button
          onClick={() => showModal()}
          className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center justify-center w-full mt-2 mb-2"
        >
          <PlusOutlined />
          <span className="ml-2">Tambah Alamat</span>
        </button>
      )}

      <Modal
        title="Form Alamat"
        open={isModalOpen}
        onOk={() => {
          form.submit()
          // setIsModalOpen(false);
        }}
        cancelText={"Batal"}
        onCancel={() => setIsModalOpen(false)}
        okText={"Simpan"}
        width={1000}
      >
        <Form
          form={form}
          name="basic"
          layout="vertical"
          initialValues={initialValues}
          onFinish={handleSaveAddress}
          //   onFinishFailed={onFinishFailed}
          autoComplete="off"
        >
          <div className="row">
            <div className="col-md-4">
              <Form.Item
                label="Nama Lengkap Penerima"
                name="nama"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Nama Lengkap Penerima!",
                  },
                ]}
              >
                <Input />
              </Form.Item>
            </div>
            <div className="col-md-4">
              <Form.Item
                label="No Telepon"
                name="telepon"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan No Telepon!",
                  },
                ]}
              >
                <Input />
              </Form.Item>
            </div>
            <div className="col-md-4">
              <Form.Item
                label="Type Alamat"
                name="type"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Type Alamat!",
                  },
                ]}
              >
                <Input />
              </Form.Item>
            </div>
            <div className="col-md-6">
              <Form.Item
                label="Provinsi"
                name="provinsi_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Provinsi!",
                  },
                ]}
              >
                <Select
                  loading={loadingProvinsi}
                  allowClear
                  className="w-full mb-2"
                  placeholder="Pilih Provinsi"
                  onChange={(value) => loadKabupaten(value)}
                >
                  {provinsi.map((item) => (
                    <Select.Option key={item.pid} value={item.pid}>
                      {item.nama}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>
              <Form.Item
                label="Kecamatan"
                name="kecamatan_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Kecamatan!",
                  },
                ]}
              >
                <Select
                  allowClear
                  className="w-full mb-2"
                  placeholder="Pilih Kecamatan"
                  loading={loadingKecamatan}
                  onChange={(value) => loadKelurahan(value)}
                >
                  {kecamatan.map((item) => (
                    <Select.Option key={item.pid} value={item.pid}>
                      {item.nama}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>
            </div>
            <div className="col-md-6">
              <Form.Item
                label="Kabupaten"
                name="kabupaten_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Kabupaten!",
                  },
                ]}
              >
                <Select
                  allowClear
                  className="w-full mb-2"
                  placeholder="Pilih Kabupaten"
                  loading={loadingKabupaten}
                  onChange={(value) => loadKecamatan(value)}
                >
                  {kabupaten.map((item) => (
                    <Select.Option key={item.pid} value={item.pid}>
                      {item.nama}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>
              <Form.Item
                label="Kelurahan"
                name="kelurahan_id"
                rules={[
                  {
                    required: true,
                    message: "Silakan pilih Kelurahan!",
                  },
                ]}
              >
                <Select
                  allowClear
                  className="w-full mb-2"
                  placeholder="Pilih Kelurahan"
                  loading={loadingKelurahan}
                  onChange={(value) => {
                    const data = kelurahan.find((item) => item.pid === value)
                    form.setFieldValue("kodepos", data.zip)
                  }}
                >
                  {kelurahan.map((item) => (
                    <Select.Option key={item.pid} value={item.pid}>
                      {item.nama}
                    </Select.Option>
                  ))}
                </Select>
              </Form.Item>
            </div>
            <div className="col-md-9">
              <Form.Item
                label="Nama Jalan"
                name="alamat"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Nama Jalan!",
                  },
                ]}
              >
                <Input />
              </Form.Item>
            </div>
            <div className="col-md-3">
              <Form.Item
                label="Kode Pos"
                name="kodepos"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Kode Pos!",
                  },
                ]}
              >
                <Input />
              </Form.Item>
            </div>
          </div>
        </Form>
      </Modal>
    </div>
  )
}

export default ModalFormAddress
