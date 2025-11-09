import { Form, Input, Modal, Select } from "antd"
import TextArea from "antd/lib/input/TextArea"
import axios from "axios"
import React, { useEffect, useState } from "react"

const ModalUpdateAlamat = ({ children, value, initialValues, onSuccess }) => {
  const [form] = Form.useForm()
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [loading, setLoading] = useState(false)

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
    form.setFieldsValue({
      ...value,
      provinsi_id: initialValues?.provinsi_id,
      kabupaten_id: initialValues?.kabupaten_id,
      kecamatan_id: initialValues?.kecamatan_id,
      kelurahan_id: initialValues?.kelurahan_id,
      kodepos: initialValues?.kodepos,
      alamat: initialValues?.alamat,
    })
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
    form.resetFields([
      "kabupaten_id",
      "kecamatan_id",
      "kelurahan_id",
      "kodepos",
    ])
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
    form.resetFields(["kecamatan_id", "kelurahan_id", "kodepos"])
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
    form.resetFields(["kelurahan_id", "kodepos"])
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
    // console.log(initialValues, "inini")
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

  const onFinish = (values) => {
    const form = { ...value, ...values }
    setLoading(true)
    axios
      .post(`/api/transaction/update/address`, form)
      .then((res) => {
        setLoading(false)
        setIsModalOpen(false)
        onSuccess()
      })
      .catch((e) => setLoading(false))
  }

  return (
    <div>
      <div className="cursor-pointer" onClick={() => showModal()}>
        {children}
      </div>

      <Modal
        title={"Ubah Detail Pengiriman"}
        open={isModalOpen}
        onCancel={() => setIsModalOpen(false)}
        onOk={() => form.submit()}
        width={800}
        confirmLoading={loading}
      >
        <Form
          form={form}
          name="basic"
          layout="vertical"
          onFinish={onFinish}
          // onFinishFailed={onFinishFailed}
          autoComplete="off"
        >
          <div className="row">
            <div className="col-md-4">
              <Form.Item
                label="Nama"
                name="name"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Nama!",
                  },
                ]}
              >
                <Input placeholder="Silakan input Nama.." disabled />
              </Form.Item>
            </div>
            <div className="col-md-4">
              <Form.Item
                label="Email"
                name="email"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Email!",
                  },
                ]}
              >
                <Input placeholder="Silakan input Email.." disabled />
              </Form.Item>
            </div>
            <div className="col-md-4">
              <Form.Item
                label="Telepon"
                name="phone"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Telepon!",
                  },
                ]}
              >
                <Input placeholder="Silakan input Telepon.." disabled />
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
                  showSearch
                  optionFilterProp="children"
                  filterOption={(input, option) =>
                    (option?.children?.toLowerCase() ?? "").includes(
                      input.toLowerCase()
                    )
                  }
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
                  showSearch
                  optionFilterProp="children"
                  filterOption={(input, option) =>
                    (option?.children?.toLowerCase() ?? "").includes(
                      input.toLowerCase()
                    )
                  }
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
                  showSearch
                  optionFilterProp="children"
                  filterOption={(input, option) =>
                    (option?.children?.toLowerCase() ?? "").includes(
                      input.toLowerCase()
                    )
                  }
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
                  showSearch
                  optionFilterProp="children"
                  filterOption={(input, option) =>
                    (option?.children?.toLowerCase() ?? "").includes(
                      input.toLowerCase()
                    )
                  }
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

            <div className="col-md-12">
              <Form.Item
                label="Alamat"
                name="alamat"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Alamat!",
                  },
                ]}
              >
                <TextArea placeholder="Silakan input Alamat.." />
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
            <div className="col-md-9">
              <Form.Item label="Catatan" name="note">
                <Input placeholder="Silakan input Catatan.." />
              </Form.Item>
            </div>
          </div>
        </Form>
      </Modal>
    </div>
  )
}

export default ModalUpdateAlamat
