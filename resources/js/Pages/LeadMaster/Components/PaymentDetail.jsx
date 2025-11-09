import React from "react";
import { formatNumber } from "../../../helpers";

const PaymentDetail = ({ order }) => {
  return (
    <div className="card w-100">
      <div className="card-body float-right">
        <table>
          <tbody>
            <tr>
              <td className="w-32 md:w-56">Sub Total</td>
              <td className="w-4">:</td>
              <td>{`Rp ${formatNumber(parseInt(order?.subtotal))}`}</td>
            </tr>
            {order?.tax_amount > 0 && (
              <tr>
                <td className="w-32 md:w-56">Tax Total</td>
                <td className="w-4">:</td>
                <td>{`Rp ${formatNumber(parseInt(order?.tax_amount))}`}</td>
              </tr>
            )}
            {order?.discount_amount > 0 && (
              <tr>
                <td>Diskon</td>
                <td>:</td>
                <td>{`Rp ${formatNumber(
                  parseInt(order?.discount_amount)
                )}`}</td>
              </tr>
            )}
            <tr>
              <td>Total</td>
              <td>:</td>
              <td>{`Rp ${formatNumber(parseInt(order?.amount))}`}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  );
};

export default PaymentDetail;
