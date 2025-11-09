import { CheckOutlined, CreditCardOutlined } from "@ant-design/icons";
import { Form, Table } from "antd";
import axios from "axios";
import React, { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import { toast } from "react-toastify";
import LoadingFallback from "../../components/LoadingFallback";
import ModalBillingOrder from "../../components/Modal/ModalBillingOrder";
import Layout from "../../components/layout";
import { formatNumber, getItem, inArray } from "../../helpers";
import ModalBillingReject from "../OrderLead/Components/ModalBillingReject";
import OrderDetailInfo from "./Components/OrderDetailInfo";
import {
  billingColumns,
  orderDeliveryColumns,
  productNeedListColumnDetail,
  historyColumns,
} from "./config";
import {
  useGetSalesOrderBillingItemsDetailQuery,
  useGetSalesOrderDeliveryItemsDetailQuery,
  useGetSalesOrderItemsDetailQuery,
} from "../../configs/Redux/Services/salesOrderService";

const OrderInvoiceDetail = () => {
  const [form] = Form.useForm();
  const params = useParams();
  const userData = getItem("user_data", true);
  const [orderDetail, setDetailOrder] = useState(null);
  // console.log(orderDetail?.ethix_items, "ethix")
  const [billingData, setBilingData] = useState([]);
  const [printUrl, setPrintUrl] = useState(null);
  const [loadingExport, setLoadingExport] = useState(false);
  const [orderDelivery, setOrderDelivery] = useState([]);
  const [productNeed, setProductNeed] = useState([]);
  const [history, setHistory] = useState([]);
  const [loading, setLoading] = useState(false);

  // delivery
  const {
    data: productNeeds,
    isLoading: productNeedsLoading,
    refetch: refetchProductNeeds,
  } = useGetSalesOrderItemsDetailQuery(
    `/api/sales-order/items/${params.uid_lead}`
  );
  // billings
  const {
    data: orderBillings,
    isLoading: loadingBilling,
    refetch: refetchOrderBillings,
  } = useGetSalesOrderBillingItemsDetailQuery(
    `/api/sales-order/billing/${params.uid_lead}`
  );

  const {
    data: orderDeliveries,
    isLoading: orderDeliveryLoading,
    refetch: refetchorderDelivery,
  } = useGetSalesOrderDeliveryItemsDetailQuery(
    `/api/sales-order/delivery/${params.uid_lead}`
  );

  const loadDetailOrderLead = () => {
    setLoading(true);
    axios
      .get(
        `/api/order/invoice/detail/${params.uid_lead}/${params.uid_delivery}`
      )
      .then((res) => {
        const { data, print } = res.data;
        setPrintUrl(print);
        setDetailOrder(data);
        // const orderDeliveryNew = data?.order_delivery.map((item) => {
        //   return {
        //     ...item,
        //     product: item?.product_name,
        //     subtotal: item?.total - item?.discount_amount,
        //   }
        // })
        // setOrderDelivery(orderDeliveryNew)

        // const dataBillings = data?.billings?.map((item) => {
        //   return {
        //     id: item.id,
        //     account_name: item.account_name,
        //     account_bank: item.account_bank,
        //     total_transfer: item.total_transfer,
        //     transfer_date: item.transfer_date,
        //     upload_billing_photo: item.upload_billing_photo_url,
        //     upload_transfer_photo: item.upload_transfer_photo_url,
        //     status: item.status,
        //     notes: item.notes ?? "-",
        //     approved_by_name: item.approved_by_name,
        //     approved_at: item.approved_at || "-",
        //     payment_number: item.payment_number || "-",
        //   }
        // })
        // setBilingData(dataBillings)

        // product needs
        const histories =
          data.histories &&
          data.histories.map((item) => {
            let newData = {
              id: item.id,
              submitted_by: item?.submited_by_name,
              created_at: item?.created_at,
              ref_number: item?.ref_number,
            };
            console.log(item);
            return newData;
          });

        setHistory(histories);
        setLoading(false);
      });
  };

  useEffect(() => {
    loadDetailOrderLead();
  }, []);

  const handleVerifyBilling = (value, status) => {
    const msg = status === 1 ? "Approve" : "Reject";
    axios
      .post(`/api/order-manual/billing/verify`, { status, ...value })
      .then((res) => {
        loadDetailOrderLead();
        toast.success(`${msg} Billing Success`);
      })
      .catch((err) => {
        toast.error(`${msg} Billing Failed`);
      });
  };

  const setClosed = () => {
    axios
      .get(`/api/order-manual/closed/${orderDetail.uid_lead}`)
      .then((res) => {
        loadDetailOrderLead();
        toast.success("Data order berhasil ditutup!");
      });
  };

  const show = !inArray(getItem("role"), [
    "adminsales",
    "leadwh",
    "leadsales",
    "leadcs",
    "warehouse",
  ]);

  const summaries = [
    {
      label: "Sub Total",
      value: formatNumber(parseInt(orderDetail?.subtotal), "Rp "),
    },
    {
      label: "Discount",
      value: formatNumber(parseInt(orderDetail?.discount_amount), "Rp "),
    },
    // {
    //   label: "Tax Total",
    //   value: formatNumber(parseInt(orderDetail?.tax_amount),'Rp '),
    // },
    {
      label: "DPP",
      value: formatNumber(parseInt(orderDetail?.amount), "Rp "),
    },
    {
      label: "PPN",
      value: formatNumber(parseInt(orderDetail?.amount_ppn), "Rp "),
    },
    // {
    //   label: "Kode Unik",
    //   value: orderDetail?.kode_unik,
    // },
    // {
    //   label: "Ongkir",
    //   value: orderDetail?.ongkir,
    // },

    {
      label: "Total",
      value: formatNumber(parseInt(orderDetail?.total), "Rp "),
    },
  ];

  const billingActionColumn = [
    {
      title: "Action",
      dataIndex: "action",
      key: "action",
      render: (text, record, index) => {
        if (record.status == 0) {
          if (orderDetail.amount_billing_approved > 0) {
            if (orderDetail.amount_billing_approved > orderDetail.amount) {
              return "-";
            }
          }
        }
        if (record.status == 2) {
          return (
            <div className="flex items-center justify-around">
              <button
                className="text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
                title="Approve"
              >
                Rejected
              </button>
            </div>
          );
        }
        if (record.status == 1) {
          return (
            <div className="flex items-center justify-around">
              <button
                className="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
                title="Approve"
              >
                Approved
              </button>
            </div>
          );
        }
        if (!show) return null;
        return (
          <div className="flex items-center justify-around">
            <ModalBillingReject
              handleClick={(value) =>
                handleVerifyBilling({ id: record.id, ...value }, 2)
              }
              user={userData}
            />
            <button
              onClick={() =>
                handleVerifyBilling(
                  {
                    id: record.id,
                    deposite: orderDetail.amount_deposite,
                    billing_approved: orderDetail.amount_billing_approved,
                    amount: orderDetail.amount,
                  },
                  1
                )
              }
              className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
              title="Approve"
            >
              <CheckOutlined />
            </button>
          </div>
        );
      },
    },
  ];

  const rightContent = (
    <div className="flex justify-between items-center"></div>
  );

  if (loading) {
    return (
      <Layout title="Detail" rightContent={rightContent} href="/order/invoice">
        <LoadingFallback />
      </Layout>
    );
  }

  const { total_qty, total_qty_delivery, total_qty_payment } =
    orderDetail || {};

  return (
    <Layout title="Detail" rightContent={rightContent} href="/order/invoice">
      <div>
        <OrderDetailInfo
          order={orderDetail}
          printUrl={{
            si: `/print/si/${params.uid_lead}/${params?.uid_delivery}`,
          }}
          refetch={() => loadDetailOrderLead()}
          params={params}
        />

        <div className="card">
          <div className="card-header flex justify-between items-center">
            <h1 className="header-title">Informasi Produk</h1>
          </div>
          <div className="card-body">
            <Table
              scroll={{ x: "max-content " }}
              tableLayout={"auto"}
              className="mb-4"
              dataSource={
                (orderDeliveries &&
                  orderDeliveries.filter((item) => item.is_invoice == 1)) ||
                []
              }
              columns={[...productNeedListColumnDetail]}
              loading={orderDeliveryLoading}
              pagination={false}
              rowKey="id"
              summary={(currentData) => {
                return (
                  <Table.Summary>
                    <Table.Summary.Row>
                      <Table.Summary.Cell align="right" colSpan={8}>
                        <strong>Subtotal (Sebelum Diskon) :</strong>
                      </Table.Summary.Cell>
                      <Table.Summary.Cell align="left" colSpan={1}>
                        <strong>
                          Rp. {formatNumber(orderDetail?.subtotal)}
                        </strong>
                      </Table.Summary.Cell>
                      <Table.Summary.Cell />
                    </Table.Summary.Row>
                    <Table.Summary.Row>
                      <Table.Summary.Cell align="right" colSpan={8}>
                        <strong>Discount :</strong>
                      </Table.Summary.Cell>
                      <Table.Summary.Cell align="left" colSpan={1}>
                        <strong>
                          Rp. {formatNumber(orderDetail?.discount_amount)}
                        </strong>
                      </Table.Summary.Cell>
                      <Table.Summary.Cell />
                    </Table.Summary.Row>
                    <Table.Summary.Row>
                      <Table.Summary.Cell align="right" colSpan={8}>
                        <strong>DPP :</strong>
                      </Table.Summary.Cell>
                      <Table.Summary.Cell align="left" colSpan={1}>
                        <strong>Rp. {formatNumber(orderDetail?.dpp)}</strong>
                      </Table.Summary.Cell>
                      <Table.Summary.Cell />
                    </Table.Summary.Row>
                    <Table.Summary.Row>
                      <Table.Summary.Cell align="right" colSpan={8}>
                        <strong>PPN :</strong>
                      </Table.Summary.Cell>
                      <Table.Summary.Cell align="left" colSpan={1}>
                        <strong>Rp. {formatNumber(orderDetail?.ppn)}</strong>
                      </Table.Summary.Cell>
                      <Table.Summary.Cell />
                    </Table.Summary.Row>
                    <Table.Summary.Row>
                      <Table.Summary.Cell align="right" colSpan={8}>
                        <strong>Kode Unik :</strong>
                      </Table.Summary.Cell>
                      <Table.Summary.Cell align="left" colSpan={1}>
                        <strong>
                          Rp. {formatNumber(orderDetail?.kode_unik)}
                        </strong>
                      </Table.Summary.Cell>
                      <Table.Summary.Cell />
                    </Table.Summary.Row>

                    <Table.Summary.Row>
                      <Table.Summary.Cell align="right" colSpan={8}>
                        <strong>Ongkir :</strong>
                      </Table.Summary.Cell>
                      <Table.Summary.Cell align="left" colSpan={1}>
                        <strong>Rp. {formatNumber(orderDetail?.ongkir)}</strong>
                      </Table.Summary.Cell>
                      <Table.Summary.Cell />
                    </Table.Summary.Row>

                    <Table.Summary.Row>
                      <Table.Summary.Cell align="right" colSpan={8}>
                        <strong>Total Amount :</strong>
                      </Table.Summary.Cell>
                      <Table.Summary.Cell align="left" colSpan={1}>
                        <strong>
                          Rp. {formatNumber(orderDetail?.total_amount)}
                        </strong>
                      </Table.Summary.Cell>
                      <Table.Summary.Cell />
                    </Table.Summary.Row>
                  </Table.Summary>
                );
              }}
            />
          </div>
        </div>

        {/* informasi pengiriman */}
        <div className="card">
          <div className="card-header flex justify-between items-center">
            <h1 className="header-titl">Informasi Pengiriman</h1>
          </div>
          <div className="card-body">
            <table className="mb-4">
              <tbody>
                <tr>
                  <td className="w-32 md:w-56">Order No</td>
                  <td className="w-4">:</td>
                  <td>{orderDetail?.order_number}</td>
                </tr>
                <tr>
                  <td>Tipe Pengiriman</td>
                  <td>:</td>
                  <td>Normal</td>
                </tr>
                <tr>
                  <td>Alamat</td>
                  <td>:</td>
                  <td>{orderDetail?.selected_address}</td>
                </tr>
              </tbody>
            </table>
            <Table
              scroll={{ x: "max-content" }}
              tableLayout={"auto"}
              className="mb-4"
              dataSource={orderDeliveries}
              columns={[...orderDeliveryColumns]}
              loading={orderDeliveryLoading}
              pagination={false}
              rowKey="id"
            />
          </div>
        </div>

        {/* payment info */}
        {/* <PaymentDetail order={orderDetail} /> */}

        {/* informasi penagihan */}
        <div className="card">
          <div className="card-header flex justify-between items-center">
            <h1 className="header-title">Informasi Penagihan</h1>
            <ModalBillingOrder
              detail={{ ...orderDetail, product_needs: productNeeds }}
              refetch={loadDetailOrderLead}
              user={userData}
            />
          </div>
          <div className="card-body">
            <Table
              dataSource={orderBillings}
              columns={
                userData?.role?.role_type !== "sales"
                  ? [...billingColumns, ...billingActionColumn]
                  : [...billingColumns]
              }
              loading={loadingBilling}
              pagination={false}
              rowKey="id"
              scroll={{ x: "max-content" }}
              tableLayout={"auto"}
            />
          </div>
        </div>

        {/* history reset */}
        <div className="card">
          <div className="card-header flex justify-between items-center ">
            <h1 className="header-title ">History Reset GP</h1>
          </div>
          <div className="card-body">
            <Table
              dataSource={history}
              columns={historyColumns}
              loading={loading}
              pagination={false}
              rowKey="id"
              scroll={{ x: "max-content" }}
              tableLayout={"auto"}
            />
          </div>
        </div>

        <div className="card">
          <div className="card-body">
            <div className="flex justify-between items-center">
              <p style={{ width: "60%" }}>
                {inArray(orderDetail?.status, ["2"]) && (
                  <i>
                    Pastikan Anda telah mendownload surat jalan dan melakukan
                    pengemasan terlebih dahulu untuk melanjutkan ke proses
                    Pengiriman Product
                  </i>
                )}
              </p>
              <button
                className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
                onClick={() => {
                  const hasInvoiced = orderDelivery?.every(
                    (item) => item.is_invoice > 0
                  );
                  if (!hasInvoiced) {
                    return toast.error("Pastikan Semua Barang Sudah Invoiced");
                  }

                  if (parseInt(total_qty_delivery) < parseInt(total_qty)) {
                    return toast.error("Pastikan Semua Barang Sudah Dikirim");
                  }

                  if (parseInt(total_qty_payment) < parseInt(total_qty)) {
                    return toast.error("Pastikan Semua Barang Sudah Ditagih");
                  }

                  return setClosed();
                }}
              >
                <CreditCardOutlined />
                <span className="ml-2">Complete </span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </Layout>
  );
};

export default OrderInvoiceDetail;
