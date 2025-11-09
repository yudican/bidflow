import { Form, Table } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { useNavigate, useParams } from "react-router-dom"
import Layout from "../../components/layout"
import LoadingFallback from "../../components/LoadingFallback"
import { productListColumns } from "./config"

const CaseManualDetail = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const { uid_case } = useParams()

  const [detail, setDetail] = useState(null)

  const [productItems, setProductItems] = useState([])
  const [loading, setLoading] = useState(false)

  const loadProductDetail = () => {
    setLoading(true)
    axios
      .get(`/api/case/manual/detail/${uid_case}`)
      .then((res) => {
        const { data } = res.data
        setLoading(false)
        setDetail(data)
        const forms = {
          ...data,
          contact: {
            label: data?.contact_name,
            value: data?.contact_user?.id,
          },
          payment_terms: data?.payment_term?.id,
        }

        const newData = data?.items?.map((item, key) => {
          return {
            ...item,
            product_name: item?.product_name,
            key,
          }
        })
        setProductItems(newData)
        form.setFieldsValue(forms)
      })
      .catch((e) => setLoading(false))
  }

  useEffect(() => {
    loadProductDetail()
  }, [])

  if (loading) {
    return (
      <Layout title="Detail" href="/case/manual">
        <LoadingFallback />
      </Layout>
    )
  }
  return (
    <Layout title="Detail Case Manual" href="/case/manual">
      <div className="card">
        <div className="card-body">
          <div className="row card-body  ">
            <div className="col-md-12">
              <div className="card">
                <div className="card-header">
                  <div className="h1 card-title">Informasi Detail</div>
                </div>
                <div className="card-body">
                  <table className="w-100">
                    <tbody>
                      <tr>
                        <td className="py-2">
                          <strong>Contact</strong>
                        </td>
                        <td>: {detail?.contact_name}</td>
                      </tr>
                      <tr>
                        <td className="py-2">
                          <strong>Type</strong>
                        </td>
                        <td>: {detail?.type_name}</td>
                      </tr>
                      <tr>
                        <td className="py-2">
                          <strong>Priority</strong>
                        </td>
                        <td>: {detail?.priority_name || "-"}</td>
                      </tr>
                      <tr>
                        <td className="py-2">
                          <strong>Source</strong>
                        </td>
                        <td>: {detail?.source_name || "-"}</td>
                      </tr>
                      <tr>
                        <td className="py-2">
                          <strong>Category</strong>
                        </td>
                        <td>: {detail?.category_name}</td>
                      </tr>
                      <tr>
                        <td className="py-2">
                          <strong>Status</strong>
                        </td>
                        <td>: {detail?.status_name}</td>
                      </tr>
                      <tr>
                        <td className="py-2">
                          <strong>Created by</strong>
                        </td>
                        <td>: {detail?.created_by_name}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            {productItems.length > 0 && (
              <div className="col-md-12">
                <div className="card">
                  <div className="card-header">
                    <div className="h1 card-title">Informasi Product</div>
                  </div>
                  <div className="card-body">
                    <Table
                      dataSource={productItems}
                      columns={[
                        {
                          title: "Product",
                          dataIndex: "product_name",
                          key: "product_name",
                        },

                        {
                          title: "Qty",
                          dataIndex: "qty",
                          key: "qty",
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
              </div>
            )}
          </div>
        </div>
      </div>
    </Layout>
  )
}

export default CaseManualDetail
