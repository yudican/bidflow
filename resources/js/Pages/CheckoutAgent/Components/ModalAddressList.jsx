import { HomeOutlined } from "@ant-design/icons";
import { Button, message, Modal } from "antd";
import React, { useEffect, useState } from "react";
import ModalFormAddress from "./ModalFormAddress";

const ModalAddressList = ({ handleSelected, initialValues = {} }) => {
    const [visible, setVisible] = useState(false);
    const [loadingAddress, setLoadingAddress] = useState(false);
    const [addressList, setAddressList] = useState([]);
    const [selectedAddress, setSelectedAddress] = useState(null);
    const [hasSelected, setHasSelected] = useState(false);
    const [initialUser, setInitialUser] = useState({});
    const loadAddressList = (url = "/api/cart/address") => {
        setLoadingAddress(true);
        axios
            .get(url)
            .then((res) => {
                const { address, user } = res.data;
                setInitialUser(user || {});
                setAddressList(address);
                setLoadingAddress(false);
            })
            .catch((err) => setLoadingAddress(false));
    };

    console.log(initialUser, "initialUser");
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
                    <div className="w-full">
                        <div
                            className="p-2 rounded-lg"
                            style={{
                                backgroundColor: "#fef4e4",
                                border: "2px solid #FFC120",
                            }}
                        >
                            <div className="flex justify-between mb-4">
                                <div className="flex flex-col">
                                    <span className="text-[8px]">
                                        Nama Penerima
                                    </span>
                                    <span className="text-xs">
                                        {selectedAddress?.nama}
                                    </span>
                                </div>
                                <Button danger shape="round">
                                    {selectedAddress?.type}
                                </Button>
                            </div>

                            <div className="flex flex-col mb-6">
                                <span className="text-[10px] mb-1">
                                    {selectedAddress?.telepon}
                                </span>
                                <span className="text-[10px] w-11/12">
                                    {selectedAddress?.alamat_detail}
                                </span>
                            </div>

                            <Button
                                type="primary"
                                color="#FFC120"
                                className={"w-full"}
                                style={{
                                    backgroundColor: "#BE8900",
                                    borderColor: "#BE8900",
                                }}
                                onClick={() => {
                                    setVisible(true);
                                    loadAddressList();
                                }}
                            >
                                Ganti Alamat
                            </Button>
                        </div>
                    </div>
                ) : (
                    <button
                        className="btn d-flex flex-row justify-content-between align-items-center w-100 rounded-lg"
                        style={{
                            border: "1px solid #e5e5e5",
                            color: "#0478ae",
                            borderRadius: 10,
                        }}
                        onClick={() => {
                            setVisible(true);
                            loadAddressList();
                        }}
                    >
                        <span>
                            <HomeOutlined />
                            <span className="ml-2">
                                Pilih Alamat Pengiriman
                            </span>
                        </span>

                        <i className="fas fa-arrow-right"></i>
                    </button>
                )}
            </div>
            <Modal
                title="Pilih Alamat Pengiriman"
                visible={visible}
                // onOk={handleSelectedAddress}
                onCancel={() => setVisible(false)}
                // okText="Pilih Alamat"
                footer={false}
            >
                <ModalFormAddress
                    initialValues={initialValues}
                    refetch={loadAddressList}
                />
                {addressList.map((address) => (
                    <div className="w-full mb-2" key={address.id}>
                        <div
                            className="p-2 rounded-lg"
                            style={{
                                backgroundColor: "#fef4e4",
                                border: "2px solid #FFC120",
                            }}
                        >
                            <div className="flex justify-between mb-4">
                                <div className="flex flex-col">
                                    <span className="text-[8px]">
                                        Nama Penerima
                                    </span>
                                    <span className="text-xs">
                                        {address.nama}
                                    </span>
                                </div>
                                <Button danger shape="round">
                                    {address.type}
                                </Button>
                            </div>

                            <div className="flex flex-col mb-6">
                                <span className="text-[10px] mb-1">
                                    {address.telepon}
                                </span>
                                <span className="text-[10px] w-11/12">
                                    {address?.alamat_detail}
                                </span>
                            </div>

                            <div className="d-flex justify-content-between align-items-center">
                                <ModalFormAddress
                                    initialValues={{
                                        ...address,
                                        address_id: address.id,
                                    }}
                                    update
                                    refetch={loadAddressList}
                                    className={"w-full"}
                                />
                                <Button
                                    type="primary"
                                    color="#FFC120"
                                    className={"w-full"}
                                    style={{
                                        backgroundColor: "#BE8900",
                                        borderColor: "#BE8900",
                                        width: "48%",
                                    }}
                                    onClick={() => {
                                        handleSelected(address);
                                        setVisible(false);
                                        setHasSelected(true);
                                        setSelectedAddress(address);
                                        message.success(
                                            "Alamat berhasil dipilih"
                                        );
                                    }}
                                >
                                    Gunakan Alamat
                                </Button>
                            </div>
                        </div>
                    </div>
                ))}
            </Modal>
        </div>
    );
};

export default ModalAddressList;
