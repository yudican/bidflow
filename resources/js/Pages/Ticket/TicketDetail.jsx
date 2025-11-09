import { Card, Form } from "antd"
import React, { useEffect, useState } from "react"
import "react-draft-wysiwyg/dist/react-draft-wysiwyg.css"
import { useNavigate, useParams } from "react-router-dom"
import Layout from "../../components/layout"

import "../../index.css"

const TicketDetail = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const { id } = useParams()

  const [loadingSubmit, setLoadingSubmit] = useState(false)
  const [packages, setPackages] = useState([])
  const [detail, setDetail] = useState(null)

  const loadDetailTicket = () => {
    axios.get(`/api/ticket/detail/${id}`).then((res) => {
      const { data } = res.data
      setDetail(data)
      //   const forms = {
      //     ...data,
      //     customer_name: data?.customer_name,
      //     expired_at: moment(data?.expired_at ?? new Date(), "YYYY-MM-DD"),
      //   }
      //   form.setFieldsValue(forms)
    })
  }

  const loadPackages = () => {
    axios.get("/api/master/package").then((res) => {
      const { data } = res.data
      setPackages(data)
    })
  }

  useEffect(() => {
    loadDetailTicket()
    loadPackages()
  }, [])

  return (
    <Layout
      title="Ticket Detail"
      href="/ticket"
      // rightContent={rightContent}
    >
      <Card title="Data Detail">
        <div className="card-body row">
          <div className="col-md-12">
            <table className="w-100" style={{ width: "100%" }}>
              <tbody>
                <tr>
                  <td style={{ width: "50%" }} className="py-2">
                    <strong>Ticket Number</strong>
                  </td>
                  <td>: {detail?.ticket_number || "-"}</td>
                </tr>
                <tr>
                  <td style={{ width: "50%" }} className="py-2">
                    <strong>Customer Name</strong>
                  </td>
                  <td>: {detail?.customer_name || "-"}</td>
                </tr>
                <tr>
                  <td style={{ width: "50%" }} className="py-2">
                    <strong>Assign to</strong>
                  </td>
                  <td>: {detail?.agent_name || "-"}</td>
                </tr>
                <tr>
                  <td style={{ width: "50%" }} className="py-2">
                    <strong>Assign Date</strong>
                  </td>
                  <td>: {detail?.assign_date || "-"}</td>
                </tr>
                <tr>
                  <td style={{ width: "50%" }} className="py-2">
                    <strong>Tags</strong>
                  </td>
                  <td>: {detail?.tags || "-"}</td>
                </tr>
                <tr>
                  <td style={{ width: "50%" }} className="py-2">
                    <strong>Notes</strong>
                  </td>
                  <td>: {detail?.note || "-"}</td>
                </tr>
                <tr>
                  <td style={{ width: "50%" }} className="py-2">
                    <strong>Status</strong>
                  </td>
                  <td>: {detail?.status_ticket || "-"}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </Card>
    </Layout>
  )
}

export default TicketDetail
