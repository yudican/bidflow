import { CreditCardOutlined } from "@ant-design/icons";
import { Button, message, Modal, Radio } from "antd";
import React, { useState } from "react";

const ModalWarehouseList = ({
    handleSelected,
    isTrue = false,
    validateText = "text",
}) => {
    const [visible, setVisible] = useState(false);
    const [loadingWarehouse, setLoadingWarehouse] = useState(false);
    const [warehouseList, setWarehouse] = useState([]);
    const [selectedWarehouse, setSelectedWarehouse] = useState(null);
    const [hasSelected, setHasSelected] = useState(false);
    const loadWarehouse = (url = "/api/cart/warehouse") => {
        setLoadingWarehouse(true);
        axios
            .get(url)
            .then((res) => {
                const { warehouses } = res.data;
                console.log(res.data);
                setWarehouse(warehouses);
                setLoadingWarehouse(false);
            })
            .catch((err) => setLoadingWarehouse(false));
    };

    const handleSelectedWarehouse = (warehouse) => {
        handleSelected(warehouse);
        setSelectedWarehouse(warehouse);
        setVisible(false);
        setHasSelected(true);
        message.success("Alamat Warehouse berhasil dipilih");
    };
    console.log(warehouseList);
    return (
        <div>
            <div
                style={{
                    border: "1px solid #e5e5e5",
                    borderRadius: 10,
                    padding: 10,
                }}
            >
                {hasSelected ? (
                    <div className="w-full mb-2">
                        <div
                            className="p-2 rounded-lg"
                            style={{
                                backgroundColor: "#dfffde",
                                border: "2px solid #81c383",
                            }}
                        >
                            <div className="flex justify-between mb-4">
                                <div className="flex flex-col">
                                    <span className="text-[8px]">
                                        Flimty Warehouse
                                    </span>
                                    <span className="text-xs">
                                        {selectedWarehouse?.name}
                                    </span>
                                </div>
                                <Button danger shape="round">
                                    {selectedWarehouse?.type}
                                </Button>
                            </div>

                            <div className="flex flex-col mb-6">
                                <span className="text-[10px] mb-1">
                                    {selectedWarehouse?.telepon}
                                </span>
                                <span className="text-[10px] w-11/12">
                                    {selectedWarehouse?.alamat}
                                </span>
                            </div>

                            <div className="d-flex justify-content-between align-items-center">
                                <Button
                                    type="primary"
                                    color="#FFC120"
                                    className={"w-full"}
                                    style={{
                                        backgroundColor: "#227d0f",
                                        borderColor: "#227d0f",
                                    }}
                                    onClick={() => {
                                        setVisible(true);
                                        loadWarehouse();
                                    }}
                                >
                                    Ganti Alamat
                                </Button>
                            </div>
                        </div>
                    </div>
                ) : (
                    <div
                        style={{
                            border: "1px solid #e5e5e5",
                            borderRadius: 10,
                            padding: 10,
                        }}
                    >
                        <button
                            className="btn d-flex flex-row justify-content-between align-items-center w-100 rounded-lg"
                            style={{
                                border: "1px solid #e5e5e5",
                                color: "#7C9B3A",
                                borderRadius: 10,
                            }}
                            onClick={() => {
                                if (isTrue) {
                                    return message.error(validateText);
                                }
                                setVisible(true);
                                loadWarehouse();
                            }}
                        >
                            <span>
                                <i className="fas fa-warehouse"></i>
                                <span className="ml-2">
                                    Pilih Gudang Pengiriman
                                </span>
                            </span>

                            <i className="fas fa-arrow-right"></i>
                        </button>
                    </div>
                )}
            </div>
            <Modal
                title="Pilih Gudang Pengiriman"
                visible={visible}
                // onOk={handleSelectedWarehouse}
                onCancel={() => setVisible(false)}
                okText="Pilih Gudang Pengiriman"
                footer={false}
            >
                {warehouseList.map((warehouse) => (
                    <div className="w-full mb-2" key={warehouse?.id}>
                        <div
                            className="p-2 rounded-lg"
                            style={{
                                backgroundColor: "#dfffde",
                                border: "2px solid #81c383",
                            }}
                        >
                            <div className="flex justify-between mb-4">
                                <div className="flex flex-col">
                                    <span className="text-[8px]">
                                        Flimty Warehouse
                                    </span>
                                    <span className="text-xs">
                                        {warehouse?.name}
                                    </span>
                                </div>
                                <Button danger shape="round">
                                    {warehouse?.type}
                                </Button>
                            </div>

                            <div className="flex flex-col mb-6">
                                <span className="text-[10px] mb-1">
                                    {warehouse?.telepon}
                                </span>
                                <span className="text-[10px] w-11/12">
                                    {warehouse?.alamat}
                                </span>
                            </div>

                            <div className="d-flex justify-content-between align-items-center">
                                <Button
                                    type="primary"
                                    color="#FFC120"
                                    className={"w-full"}
                                    style={{
                                        backgroundColor: "#227d0f",
                                        borderColor: "#227d0f",
                                    }}
                                    onClick={() =>
                                        handleSelectedWarehouse(warehouse)
                                    }
                                >
                                    Pilih Gudang
                                </Button>
                            </div>
                        </div>
                    </div>
                ))}
            </Modal>
        </div>
    );
};

export default ModalWarehouseList;
