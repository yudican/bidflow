import { Modal } from "antd";
import React, { useState } from "react";

const AddressDetail = ({ onClose, visible = false }) => {
    return (
        <Modal
            title="Detail Alamat"
            visible={visible}
            // onOk={onClick}
            onCancel={onClose}
        >
            <table className="w-100" style={{ width: "100%" }}>
                <tbody>
                    <tr>
                        <td style={{ width: "50%" }} className="py-2">
                            <strong>PIC Sales</strong>
                        </td>
                        <td>: </td>
                    </tr>
                </tbody>
            </table>
        </Modal>
    );
};

export default AddressDetail;
