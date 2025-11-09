import {
    AuditOutlined,
    DeleteOutlined,
    DownOutlined,
    EditFilled,
    EyeOutlined,
    RightOutlined,
} from "@ant-design/icons";
import { Dropdown, Menu, message } from "antd";
import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { toast } from "react-toastify";
import { formatNumber } from "../../helpers";
//import AddressDetail from "./Components/AddressDetail";

const ActionMenu = ({ value, role }) => {
    const navigate = useNavigate();
    const [modalDetail, setModalDetail] = useState(false);
    return (
        <Menu
            onClick={({ key }) => {
                switch (key) {
                    case "detail":
                        navigate(`/trans-agent/detail/${value}`);
                        break;
                    case "edit":
                        navigate(`/contact/${value}/edit`);
                        break;
                }
            }}
            itemIcon={<RightOutlined />}
            items={[
                {
                    label: "Detail Pesanan",
                    key: "detail",
                    icon: <EyeOutlined />,
                },
            ]}
        />
    );
};


const TransAgentAllListColumn = [
    {
        title: "No",
        dataIndex: "id",
        key: "id",
    },
    {
        title: "User",
        dataIndex: "name",
        key: "name",
    },
    {
        title: "Trans ID",
        dataIndex: "id_transaksi",
        key: "id_transaksi",
    },
    {
        title: "Trans Date",
        dataIndex: "created_at",
        key: "created_at",
    },
    {
        title: "Nominal",
        dataIndex: "nominal",
        key: "nominal",
    },
    {
        title: "Action",
        key: "id",
        fixed: "right",
        width: 100,
        render: (text) => (
            <Dropdown.Button
                icon={<DownOutlined />}
                overlay={<ActionMenu value={text.key} role="list" />}
            ></Dropdown.Button>
        ),
    },
];

const TransAgentWaitingPaymentListColumn = [
    {
        title: "No",
        dataIndex: "id",
        key: "id",
    },
    {
        title: "User",
        dataIndex: "name",
        key: "name",
    },
    {
        title: "Trans ID",
        dataIndex: "id_transaksi",
        key: "id_transaksi",
    },
    {
        title: "Trans Date",
        dataIndex: "created_at",
        key: "created_at",
    },
    {
        title: "Nominal",
        dataIndex: "nominal",
        key: "nominal",
    },
    {
        title: "Action",
        key: "id",
        fixed: "right",
        width: 100,
        render: (text) => (
            <Dropdown.Button
                icon={<DownOutlined />}
                overlay={<ActionMenu value={text.key} role="list" />}
            ></Dropdown.Button>
        ),
    },
];

const ConfirmationAgentListColumn = [
    {
        title: "No",
        dataIndex: "id",
        key: "id",
    },
    {
        title: "User",
        dataIndex: "name",
        key: "name",
    },
    {
        title: "Trans ID",
        dataIndex: "id_transaksi",
        key: "id_transaksi",
    },
    {
        title: "Trans Date",
        dataIndex: "created_at",
        key: "created_at",
    },
    {
        title: "Nominal",
        dataIndex: "nominal",
        key: "nominal",
    },
    {
        title: "Action",
        key: "id",
        fixed: "right",
        width: 100,
        render: (text) => (
            <Dropdown.Button
                icon={<DownOutlined />}
                overlay={<ActionMenu value={text.key} role="list" />}
            ></Dropdown.Button>
        ),
    },
];

const NewTransactionListColumn = [
    {
        title: "No",
        dataIndex: "id",
        key: "id",
    },
    {
        title: "User",
        dataIndex: "name",
        key: "name",
    },
    {
        title: "Trans ID",
        dataIndex: "id_transaksi",
        key: "id_transaksi",
    },
    {
        title: "Trans Date",
        dataIndex: "created_at",
        key: "created_at",
    },
    {
        title: "Nominal",
        dataIndex: "nominal",
        key: "nominal",
        render: (value) => `Rp ${formatNumber(value)}`,
    },
    
];

const WarehouseListColumn = [
    {
        title: "No",
        dataIndex: "id",
        key: "id",
    },
    {
        title: "User",
        dataIndex: "name",
        key: "name",
    },
    {
        title: "Trans ID",
        dataIndex: "id_transaksi",
        key: "id_transaksi",
    },
    {
        title: "Trans Date",
        dataIndex: "created_at",
        key: "created_at",
    },
    {
        title: "Nominal",
        dataIndex: "nominal",
        key: "nominal",
    },
];

const ReadyProductListColumn = [
    {
        title: "No",
        dataIndex: "id",
        key: "id",
    },
    {
        title: "User",
        dataIndex: "name",
        key: "name",
    },
    {
        title: "Trans ID",
        dataIndex: "id_transaksi",
        key: "id_transaksi",
    },
    {
        title: "Trans Date",
        dataIndex: "created_at",
        key: "created_at",
    },
    {
        title: "Nominal",
        dataIndex: "nominal",
        key: "nominal",
    },
    {
        title: "Action",
        key: "id",
        fixed: "right",
        width: 100,
        render: (text) => (
            <Dropdown.Button
                icon={<DownOutlined />}
                overlay={<ActionMenu value={text.key} role="list" />}
            ></Dropdown.Button>
        ),
    },
];

const DeliveryListColumn = [
    {
        title: "No",
        dataIndex: "id",
        key: "id",
    },
    {
        title: "User",
        dataIndex: "name",
        key: "name",
    },
    {
        title: "Trans ID",
        dataIndex: "id_transaksi",
        key: "id_transaksi",
    },
    {
        title: "Trans Date",
        dataIndex: "created_at",
        key: "created_at",
    },
    {
        title: "Nominal",
        dataIndex: "nominal",
        key: "nominal",
    },
];

const OrderAcceptedListColumn = [
    {
        title: "No",
        dataIndex: "id",
        key: "id",
    },
    {
        title: "User",
        dataIndex: "name",
        key: "name",
    },
    {
        title: "Trans ID",
        dataIndex: "id_transaksi",
        key: "id_transaksi",
    },
    {
        title: "Trans Date",
        dataIndex: "created_at",
        key: "created_at",
    },
    {
        title: "Nominal",
        dataIndex: "nominal",
        key: "nominal",
    },
    {
        title: "Action",
        key: "id",
        fixed: "right",
        width: 100,
        render: (text) => (
            <Dropdown.Button
                icon={<DownOutlined />}
                overlay={<ActionMenu value={text.key} role="list" />}
            ></Dropdown.Button>
        ),
    },
];

const HistoryListColumn = [
    {
        title: "No",
        dataIndex: "id",
        key: "id",
    },
    {
        title: "User",
        dataIndex: "name",
        key: "name",
    },
    {
        title: "Trans ID",
        dataIndex: "id_transaksi",
        key: "id_transaksi",
    },
    {
        title: "Trans Date",
        dataIndex: "created_at",
        key: "created_at",
    },
    {
        title: "Nominal",
        dataIndex: "nominal",
        key: "nominal",
    },
    {
        title: "Action",
        key: "id",
        fixed: "right",
        width: 100,
        render: (text) => (
            <Dropdown.Button
                icon={<DownOutlined />}
                overlay={<ActionMenu value={text.key} role="list" />}
            ></Dropdown.Button>
        ),
    },
];


export {
    TransAgentAllListColumn,
    TransAgentWaitingPaymentListColumn,
    ConfirmationAgentListColumn,
    NewTransactionListColumn,
    WarehouseListColumn,
    ReadyProductListColumn,
    DeliveryListColumn,
    OrderAcceptedListColumn,
    HistoryListColumn,
};
