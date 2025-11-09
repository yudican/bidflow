import React, { useState } from "react"
import {
    CloseOutlined,
    EditFilled,
    EyeOutlined,
    RightOutlined, 
    UploadOutlined
  } from "@ant-design/icons"
import { Dropdown, Menu, message, Modal, Upload, Button } from "antd"
import { formatDate, formatNumber } from "../../helpers"
import { useNavigate } from "react-router-dom"

const ActionMenu = ({ value, status = 1 }) => {
    const navigate = useNavigate();
    const [visible, setVisible] = useState(false);
    const [file, setFile] = useState(null);

    const showModal = () => {
      setVisible(true);
    };

    const handleOk = () => {
      // Periksa apakah file dan value (ID) sudah ada
      if (!file) {
        message.error("Please select a file");
        return;
      }

      // Kirim permintaan POST ke endpoint dengan data file dan ID
      const formData = new FormData();
      formData.append("file", file); // Tambahkan file ke FormData
      formData.append("id", value); // Tambahkan ID ke FormData

      axios
        .post(`/api/barcode/upload`, formData, {
          headers: {
            "Content-Type": "multipart/form-data" // Atur header untuk FormData
          }
        })
        .then((res) => {
          message.success("File uploaded successfully!");
          setVisible(false);
          window.location.reload();
        })
        .catch((error) => {
          message.error("Failed to upload file!");
        });
    };

    const handleCancel = () => {
      setVisible(false);
    };

    return (
      <>
        <Menu
          onClick={({ key }) => {
            switch (key) {
              case "detail":
                navigate(`/order/order-manual/detail/${value}`);
                break;
              case "detail_new_tab":
                window.open(`/order/order-manual/detail/${value}`);
                break;
              case "update":
                showModal();
                break;
              case "reset":
                axios
                  .get(`/api/barcode/reset/${value}`)
                  .then((res) => {
                    message.success("Data barcode berhasil di reset!");
                    window.location.reload();
                  })
                  .catch((error) => {
                    message.error("Failed to reset barcode!");
                  });
                break;
            }
          }}
          itemIcon={<RightOutlined />}
          items={getStatusItems(status)}
        />
        <Modal
          title="Upload File"
          visible={visible}
          onOk={handleOk}
          onCancel={handleCancel}
        >
          <b>Upload Bukti Barcode</b>
          <Upload
            beforeUpload={(file) => {
              setFile(file);
              return false;
            }}
            fileList={file ? [file] : []}
          >
            <Button icon={<UploadOutlined />}>Select File</Button>
          </Upload>
        </Modal>
      </>
    );
};
  
const getStatusItems = (status) => {
  switch (status) {
    case "Draft":
      return [
        {
          label: "Detail",
          key: "detail",
          icon: <EyeOutlined />,
        },
        {
          label: "Ubah",
          key: "update",
          icon: <EditFilled />,
        },
      ]
    case "New":
      return [
        {
          label: "Detail",
          key: "detail",
          icon: <EyeOutlined />,
          children: [
            {
              label: "Open Directly",
              key: "detail",
              icon: <EyeOutlined />,
            },
            {
              label: "Open In New Tab",
              key: "detail_new_tab",
              icon: <EyeOutlined />,
            },
          ],
        },
        {
          label: "Ubah",
          key: "update",
          icon: <EditFilled />,
        },
        {
          label: "Cancel",
          key: "cancel",
          icon: <CloseOutlined />,
        },
      ]
    case "Open":
      return [
        {
          label: "Detail",
          key: "detail",
          icon: <EyeOutlined />,
          children: [
            {
              label: "Open Directly",
              key: "detail",
              icon: <EyeOutlined />,
            },
            {
              label: "Open In New Tab",
              key: "detail_new_tab",
              icon: <EyeOutlined />,
            },
          ],
        },
      ]

    case "Closed":
      return [
        {
          label: "Detail",
          key: "detail",
          icon: <EyeOutlined />,
          children: [
            {
              label: "Open Directly",
              key: "detail",
              icon: <EyeOutlined />,
            },
            {
              label: "Open In New Tab",
              key: "detail_new_tab",
              icon: <EyeOutlined />,
            },
          ],
        },
      ]
    default:
      return [
        {
          label: "Update Manual",
          key: "update",
          icon: <EditFilled />,
        },
        {
          label: "Reset",
          key: "reset",
          icon: <CloseOutlined />,
        },
      ]
  }
}

const transactionListColumn = [
  {
    title: "No.",
    dataIndex: "number",
    key: "number",
    // render: (text, record, index) => index + 1,
  },
  {
    title: "Master Box ID",
    dataIndex: "product_id",
    key: "product_id",
  },
  {
    title: "Location",
    dataIndex: "location",
    key: "location",
  },
  {
    title: "Qty",
    dataIndex: "qty",
    key: "qty",
  },
  {
    title: "Prefix",
    dataIndex: "prefixs",
    key: "prefixs",
  },
  {
    title: "Status",
    dataIndex: "status",
    key: "status",
  },
]

const transactionMealPlanListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "TRX ID",
    dataIndex: "id_transaksi",
    key: "id_transaksi",
  },
  {
    title: "Nama Customer",
    dataIndex: "user_name",
    key: "user_name",
  },
  {
    title: "Email",
    dataIndex: "user_email",
    key: "user_email",
  },
  {
    title: "No. Handphone",
    dataIndex: "user_phone",
    key: "user_phone",
  },
  {
    title: "Transaction Date",
    dataIndex: "created_at",
    key: "created_at",
    render: (text, record) => {
      if (text) {
        return formatDate(text)
      }
      return "-"
    },
  },
  {
    title: "Batas Pembayaran",
    dataIndex: "expire_payment",
    key: "expire_payment",
    render: (text, record) => {
      if (text) {
        return formatDate(text)
      }
      return "-"
    },
  },
  {
    title: "Nominal",
    dataIndex: "nominal",
    key: "nominal",
    render: (value) => `Rp ${formatNumber(value)}`,
  },
]

const transactionProductListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Barcode",
    dataIndex: "barcode",
    key: "barcode",
  },
  {
    title: "Status",
    dataIndex: "status",
    key: "status",
  },
  {
    title: "Action",
    key: "id",
    align: "center",
    fixed: "right",
    width: 100,
    render: (text) => (
      <Dropdown.Button
        style={{
          left: -16,
        }}
        // icon={<MoreOutlined />}
        overlay={<ActionMenu value={text.barcode} status={text.status} />}
      ></Dropdown.Button>
    ),
  },
]

const transactionUploadPaymentListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Activity",
    dataIndex: "activity",
    key: "activity",
  },
  {
    title: "Updated At",
    dataIndex: "created_at",
    key: "created_at",
    render: (text, record) => {
      if (text) {
        return formatDate(text)
      }
      return "-"
    },
  },
  {
    title: "Updated By",
    dataIndex: "created_by",
    key: "created_by",
  }
]

export {
  transactionListColumn,
  transactionMealPlanListColumn,
  transactionProductListColumn,
  transactionUploadPaymentListColumn,
}
