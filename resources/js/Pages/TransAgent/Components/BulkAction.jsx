import { Dropdown, Menu, message } from "antd";
import axios from "axios";
import React from "react";

const BulkAction = ({ selectedRowKeys = [] }) => {
    const onClick = ({ key }) => {
        console.log(key);
        if (key == 0) {
            message.info("Menu 1 clicked");
        } else if (key == 1) {
            axios
                .post("/api/trans-agent/bulk/invoice", {
                    data: selectedRowKeys,
                })
                .then((res) => {
                    axios.post("https://giraffe.daftar-agen.com/task", {
                        topic: "aimi_maintasks",
                        task: "html_to_pdf",
                        data: {
                            papersize: "A4",
                            urls: res.data.data,
                        },
                    });
                });
        }
    };

    const menu = (
        <Menu
            onClick={onClick}
            items={[
                {
                    label: "Cetak Label",
                    key: 0,
                },
                {
                    label: "Cetak Invoice",
                    key: 1,
                },
            ]}
        />
    );
    if (selectedRowKeys.length > 0) {
        return (
            <Dropdown overlay={menu}>
                <button className="ml-2 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center">
                    {`${selectedRowKeys.length} Selected`}
                </button>
            </Dropdown>
        );
    }

    return (
        <button className="ml-2 text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center">
            {`${selectedRowKeys.length} Selected`}
        </button>
    );
};

export default BulkAction;
