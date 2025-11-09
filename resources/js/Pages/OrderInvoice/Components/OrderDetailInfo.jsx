import { PrinterTwoTone } from "@ant-design/icons"
import { Dropdown, Menu, Tag } from "antd"
import moment from "moment"
import React from "react"
import { useLocation, useNavigate } from "react-router-dom"
import ModalInvoiceDate from "../../../components/Modal/ModalInvoiceDate"
import { formatDate, handleString } from "../../../helpers"

const OrderDetailInfo = ({ order = null, printUrl, refetch }) => {
  let navigate = useNavigate()
  let location = useLocation()
  let isInvoiceDetail = location.pathname.includes("invoice")

  return (
    <div className="card">
      <div className="card-header flex items-center justify-between">
        <h1 className="text-lg text-bold ">
          {order?.invoice_number}
          {" - "}
          <span>
            <Tag>{order?.status_invoice}</Tag>
          </span>
        </h1>
        <div>
          <Dropdown.Button
            style={{ borderRadius: 10 }}
            icon={<PrinterTwoTone />}
            overlay={
              <Menu>
                {order?.status != 1 && (
                  <Menu.Item className="flex justify-between items-center">
                    <PrinterTwoTone />{" "}
                    <a href={printUrl?.si} target="_blank">
                      <span>Print SI</span>
                    </a>
                  </Menu.Item>
                )}
                {/* {!isInvoiceDetail && (
                  <Menu.Item className="flex justify-between items-center">
                    <PrinterTwoTone />{" "}
                    <a href={printUrl?.so} target="_blank">
                      <span>Print SO</span>
                    </a>
                  </Menu.Item>
                )} */}
                {/* <Menu.Item className="flex justify-between items-center">
                  <PrinterTwoTone />{" "}
                  <a href={printUrl?.sj} target="_blank">
                    <span>Print SJ</span>
                  </a>
                </Menu.Item> */}
              </Menu>
            }
          ></Dropdown.Button>
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
                <td>: {order?.contact_name || "-"}</td>
              </tr>
              <tr>
                <td style={{ width: "50%" }} className="py-2">
                  <strong>Company</strong>
                </td>
                <td>: {handleString(order?.company_name) || "-"}</td>
              </tr>

              <tr>
                <td style={{ width: "50%" }} className="py-2">
                  <strong>Customer Need</strong>
                </td>
                <td>: {order?.customer_need || "-"}</td>
              </tr>
              <tr>
                <td style={{ width: "50%" }} className="py-2">
                  <strong>PIC Sales</strong>
                </td>
                <td>: {order?.sales_name || "-"}</td>
              </tr>
              <tr>
                <td style={{ width: "50%" }} className="py-2">
                  <strong>Created On</strong>
                </td>
                <td>: {formatDate(order?.created_at) || "-"}</td>
              </tr>
              <tr>
                <td style={{ width: "50%" }} className="py-2">
                  <strong>Created by</strong>
                </td>
                <td>: {order?.created_by_name || "-"}</td>
              </tr>
              <tr>
                <td style={{ width: "50%" }} className="py-2">
                  <strong>No. Faktur</strong>
                </td>
                <td>: {order?.no_faktur || "-"}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div className="col-md-6">
          <table className="w-100" style={{ width: "100%" }}>
            <tbody>
              <tr>
                <td style={{ width: "40%" }} className="py-2">
                  <strong>PIC Warehouse</strong>
                </td>
                <td>: {order?.courier_name || "-"}</td>
              </tr>
              <tr>
                <td style={{ width: "40%" }} className="py-2">
                  <strong>Warehouse</strong>
                </td>
                <td>: {handleString(order?.warehouse_name) || "-"}</td>
              </tr>
              <tr>
                <td style={{ width: "40%" }} className="py-2">
                  <strong>SO Number</strong>
                </td>
                <td>: {order?.order_number || "-"}</td>
              </tr>

              {/* <tr>
                <td style={{ width: "40%" }} className="py-2">
                  <strong>Invoice Number</strong>
                </td>
                <td>: {order?.invoice_number || "-"}</td>
              </tr> */}
              <tr>
                <td style={{ width: "40%" }} className="py-2">
                  <strong>Reference No</strong>
                </td>
                <td>: {order?.preference_number || "-"}</td>
              </tr>
              <tr>
                <td style={{ width: "40%" }} className="py-2">
                  <strong>Payment Term</strong>
                </td>
                <td>: {order?.payment_term_name || "-"}</td>
              </tr>
              <tr>
                <td style={{ width: "40%" }} className="py-2">
                  <strong>Invoice Date</strong>
                </td>
                <td>
                  <ModalInvoiceDate
                    value={order?.invoice_date}
                    initialValues={{
                      uid_lead: order?.uid_lead,
                      invoice_date: moment(
                        order?.invoice_date || new Date(),
                        "YYYY-MM-DD"
                      ),
                    }}
                    url={"/api/order/invoice/update/date"}
                    refetch={() => {
                      refetch()
                    }}
                  />
                </td>
              </tr>
              {order?.type_so == 'order-konsinyasi' && (
                <tr>
                  <td style={{ width: "40%" }} className="py-2">
                    <strong>Kategori Data</strong>
                  </td>
                  <td>
                    :{" "}
                    {order?.order_type === "new"
                      ? "Data Baru"
                      : order?.order_type === "old"
                        ? "Data Lama"
                        : "-"}
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  )
}

export default OrderDetailInfo
