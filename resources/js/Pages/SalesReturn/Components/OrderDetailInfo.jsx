import React from "react"
import { handleString } from "../../../helpers"

const OrderDetailInfo = ({ order = null }) => {
  return (
    <div className="card">
      <div className="card-header">
        <h1 className="text-lg text-bold ">{order?.sr_number}</h1>
      </div>
      <div className="card-body row">
        <div className="col-md-6">
          <table className="w-100" style={{ width: "100%" }}>
            <tbody>
              <tr>
                <td style={{ width: "50%" }} className="py-2">
                  <strong>SR Number</strong>
                </td>
                <td>: {order?.sr_number || "-"}</td>
              </tr>
              <tr>
                <td style={{ width: "50%" }} className="py-2">
                  <strong>Order Number</strong>
                </td>
                <td>: {order?.order_number || "-"}</td>
              </tr>
              <tr>
                <td style={{ width: "50%" }} className="py-2">
                  <strong>Contact</strong>
                </td>
                <td>: {order?.contact_user?.name || "-"}</td>
              </tr>
              <tr>
                <td style={{ width: "50%" }} className="py-2">
                  <strong>Company</strong>
                </td>
                <td>
                  : {handleString(order?.contact_user?.company?.name) || "-"}
                </td>
              </tr>

              <tr>
                <td style={{ width: "50%" }} className="py-2">
                  <strong>PIC Sales</strong>
                </td>
                <td>: {order?.sales_user?.name || "-"}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div className="col-md-6">
          <table className="w-100" style={{ width: "100%" }}>
            <tbody>
              <tr>
                <td style={{ width: "50%" }} className="py-2">
                  <strong>Created On</strong>
                </td>
                <td>: {order?.created_at || "-"}</td>
              </tr>
              <tr>
                <td style={{ width: "50%" }} className="py-2">
                  <strong>Warehouse</strong>
                </td>
                <td>: {order?.warehouse?.name || "-"}</td>
              </tr>

              <tr>
                <td style={{ width: "50%" }} className="py-2">
                  <strong>Payment Term</strong>
                </td>
                <td>: {order?.payment_term?.name || "-"}</td>
              </tr>
              <tr>
                <td style={{ width: "50%" }} className="py-2">
                  <strong>Due Date</strong>
                </td>
                <td>: {order?.due_date || "-"}</td>
              </tr>
              <tr>
                <td style={{ width: "50%" }} className="py-2">
                  <strong>Status</strong>
                </td>
                <td>: {order?.status_return || "-"}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  )
}

export default OrderDetailInfo
