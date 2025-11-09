import {
    BlockOutlined,
    CheckOutlined,
    LoadingOutlined,
    PlusOutlined,
} from "@ant-design/icons";
import {
    Button,
    DatePicker,
    Form,
    Input,
    Select,
    Switch,
    Table,
    Tabs,
    Upload,
} from "antd";

import { Option } from "antd/lib/mentions";
import React, { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import { toast } from "react-toastify";
import Layout from "../../components/layout";
import { statusDetailTransaksi } from "../../helpers";

const DetailTransAgent = () => {
    const params = useParams();
    const [detailTransAgent, setDetailContact] = useState(null);
    const [loading, setLoading] = useState(false);
    const [fileList, setFileList] = useState(false);
    const loadDetailContact = () => {
        setLoading(true);
        axios.get(`/api/trans-agent/detail/${params.id}`).then((res) => {
            const { data } = res.data;
            setDetailContact(data);
            setLoading(false);
        });
    };

    useEffect(() => {
        loadDetailContact();
    }, []);
    console.log(detailTransAgent);
    const {
        transaction_detail,
        brand,
        user,
        shipping_type,
        product = {},
        voucher,
    } = detailTransAgent || {};

    return (
        <Layout title="Detail" href="/trans-agent/list">
            <div className="row">
                <div className="col-md-6">
                    <table className="table-fixed">
                        <tbody>
                            <tr>
                                <td className="py-2">
                                    <strong>ID Transaksi</strong>
                                </td>
                                <td>: {detailTransAgent?.id_transaksi}</td>
                            </tr>
                            <tr>
                                <td className="py-2">
                                    <strong>Nama Pelanggan</strong>
                                </td>
                                <td>: {user?.name}</td>
                            </tr>
                            <tr>
                                <td className="py-2">
                                    <strong>Brand</strong>
                                </td>
                                <td>: {brand?.name}</td>
                            </tr>
                            <tr>
                                <td className="py-2">
                                    <strong>Kode Voucher</strong>
                                </td>
                                <td>: {voucher?.voucher_code}</td>
                            </tr>
                            <tr>
                                <td className="py-2">
                                    <strong>Tanggal Transaksi</strong>
                                </td>
                                <td>: {detailTransAgent?.created_at}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div className="col-md-6">
                    <table className="table-fixed">
                        <tbody>
                            <tr>
                                <td className="py-2">
                                    <strong>Ongkos Kirim</strong>
                                </td>
                                <td>: {shipping_type?.shipping_price}</td>
                            </tr>
                            <tr>
                                <td className="py-2">
                                    <strong>Diskon Ongkos Kirim</strong>
                                </td>
                                <td>: {shipping_type?.shipping_discount}</td>
                            </tr>
                            <tr>
                                <td className="py-2">
                                    <strong>Kode Unik</strong>
                                </td>
                                <td>
                                    : {detailTransAgent?.payment_unique_code}
                                </td>
                            </tr>
                            <tr>
                                <td className="py-2">
                                    <strong>Total Harga</strong>
                                </td>
                                <td>: {detailTransAgent?.nominal}</td>
                            </tr>
                            <tr>
                                <td className="py-2">
                                    <strong>Hubungi Pembeli</strong>
                                </td>
                                <td>: {user?.telepon}</td>
                            </tr>
                            <tr>
                                <td className="py-2">
                                    <strong>Status</strong>
                                </td>
                                <td>
                                    :{" "}
                                    {statusDetailTransaksi(
                                        detailTransAgent?.status
                                    )}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div className="col-md-12">
                    <div className="card">
                        <div className="card-header">
                            <h1 className="text-lg text-bold flex justify-content-between align-items-center">
                                <span>Rincian Transaksi</span>
                            </h1>
                        </div>
                        <div className="card-body">
                            <div className="ant-table-wrapper">
                                <div className="ant-spin-nested-loading">
                                    <div className="ant-spin-container">
                                        <div className="ant-table ant-table-has-fix-right">
                                            <div className="ant-table-container">
                                                <div className="ant-table-content">
                                                    <table>
                                                        <thead className="ant-table-thead">
                                                            <tr>
                                                                <th className="ant-table-cell">
                                                                    Product
                                                                </th>
                                                                <th className="ant-table-cell">
                                                                    Qty
                                                                </th>
                                                                <th className="ant-table-cell">
                                                                    Harga
                                                                    (satuan)
                                                                </th>
                                                                <th className="ant-table-cell">
                                                                    Total Harga
                                                                </th>
                                                            </tr>
                                                        </thead>

                                                        <tbody className="ant-table-tbody">
                                                            {transaction_detail?.map(
                                                                (item) => {
                                                                    return (
                                                                        <tr
                                                                            data-row-key="163"
                                                                            className="ant-table-row ant-table-row-level-0"
                                                                        >
                                                                            <td className="ant-table-cell">
                                                                                {
                                                                                    item
                                                                                        .product
                                                                                        ?.name
                                                                                }
                                                                            </td>
                                                                            <td className="ant-table-cell">
                                                                                {
                                                                                    item?.qty
                                                                                }
                                                                            </td>
                                                                            <td className="ant-table-cell">
                                                                                {
                                                                                    item?.price
                                                                                }
                                                                            </td>
                                                                            <td className="ant-table-cell">
                                                                                {
                                                                                    item?.subtotal
                                                                                }
                                                                            </td>
                                                                        </tr>
                                                                    );
                                                                }
                                                            )}
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Layout>
    );
};

export default DetailTransAgent;
