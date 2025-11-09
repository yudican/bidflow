import React from "react"
import { badgeColor, formatDate, handleString } from "../../../helpers"
import { Skeleton } from "antd"

const OrderDetailInfo = ({ order = null, loading = false }) => {
  return (
    <div className="card">
      <div className="card-header flex justify-between items-center">
        {loading ? (
          <Skeleton.Input
            active
            size={"default"}
            block={false}
            style={{ width: 500 }}
          />
        ) : (
          <h1 className="text-lg text-bold ">{order?.title}</h1>
        )}
        <div
          className={`text-xs font-bold 
          ${badgeColor(order?.status_name)} 
          text-white p-2 rounded-t-full rounded-bl-full`}
        >
          {order?.status_name}
        </div>
      </div>
      <div className="card-body row">
        <div className="col-md-6">
          <table className="w-100" style={{ width: "100%" }}>
            <tbody>
              <tr>
                <td style={{ width: "50%" }} className="py-2">
                  <strong>Contact</strong>
                </td>

                <td>
                  :{" "}
                  {loading ? (
                    <Skeleton.Input active size={"small"} />
                  ) : (
                    order?.contact_user?.name
                  )}
                </td>
              </tr>

              <tr>
                <td style={{ width: "50%" }} className="py-2">
                  <strong>Company</strong>
                </td>
                <td>
                  :{" "}
                  {loading ? (
                    <Skeleton.Input active size={"small"} />
                  ) : (
                    handleString(order?.contact_user?.company?.name)
                  )}
                </td>
              </tr>
              <tr>
                <td style={{ width: "50%" }} className="py-2">
                  <strong>Warehouse</strong>
                </td>
                <td>
                  :{" "}
                  {loading ? (
                    <Skeleton.Input active size={"small"} />
                  ) : (
                    handleString(order?.warehouse_name)
                  )}
                </td>
              </tr>

              <tr>
                <td style={{ width: "50%" }} className="py-2">
                  <strong>Customer Need</strong>
                </td>
                <td>
                  :{" "}
                  {loading ? (
                    <Skeleton.Input active size={"small"} />
                  ) : (
                    order?.customer_need
                  )}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div className="col-md-6">
          <table className="w-100" style={{ width: "100%" }}>
            <tbody>
              <tr>
                <td style={{ width: "50%" }} className="py-2">
                  <strong>PIC Sales</strong>
                </td>
                <td>
                  :{" "}
                  {loading ? (
                    <Skeleton.Input active size={"small"} />
                  ) : (
                    order?.sales_user?.name
                  )}
                </td>
              </tr>
              <tr>
                <td style={{ width: "50%" }} className="py-2">
                  <strong>Created On</strong>
                </td>
                <td>
                  :{" "}
                  {loading ? (
                    <Skeleton.Input active size={"small"} />
                  ) : (
                    formatDate(order?.created_at)
                  )}
                </td>
              </tr>
              <tr>
                <td style={{ width: "50%" }} className="py-2">
                  <strong>Created by</strong>
                </td>
                <td>
                  :{" "}
                  {loading ? (
                    <Skeleton.Input active size={"small"} />
                  ) : (
                    order?.create_user?.name
                  )}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  )
}

export default OrderDetailInfo
