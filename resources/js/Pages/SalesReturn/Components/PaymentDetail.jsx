import React from "react";
import { formatNumber } from "../../../helpers";

const PaymentDetail = ({ order }) => {
  return (
    <div className="card w-100">
      <div className="card-body float-right">
        <table>
          <tbody>
            {/* <tr>
                            <td className="w-32 md:w-56">Sub Total</td>
                            <td className="w-4">:</td>
                            <td>{`Rp ${formatNumber(order?.amount)}`}</td>
                        </tr> */}
            <tr>
              <td className="w-32 md:w-56">Tax Total</td>
              <td className="w-4">:</td>
              <td>{`Rp ${formatNumber(order?.tax_amount)}`}</td>
            </tr>
            <tr>
              <td>Diskon</td>
              <td>:</td>
              <td>{`Rp ${formatNumber(order?.discount_amount)}`}</td>
            </tr>
            <tr>
              <td>Total</td>
              <td>:</td>
              <td>
                {`Rp ${formatNumber(
                  // order?.amount +
                  //     order?.tax_amount +
                  //     order?.discount_amount
                  order?.total
                )}`}
              </td>
            </tr>
            <tr>
              <td>Notes</td>
              <td>:</td>
              <td>{order?.notes || "-"}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  );
};

export default PaymentDetail;
