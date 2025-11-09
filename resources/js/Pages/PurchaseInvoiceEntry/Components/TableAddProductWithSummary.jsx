import { Table } from "antd"
import React from "react"
import { formatNumber, getItem, inArray } from "../../../helpers"

const productListColumns = [
  {
    title: "No",
    align: "center",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Nama Item",
    dataIndex: "product_name",
    key: "product_name",
    width: 240,
  },
  {
    title: "Jumlah",
    dataIndex: "qty",
    key: "qty",
    align: "center",
  },
  {
    title: "UoM",
    dataIndex: "uom",
    key: "uom",
    align: "center",
  },
  {
    title: "Harga Satuan",
    dataIndex: "harga_satuan",
    key: "harga_satuan",
    align: "center",
    render: (text) => {
      return `${formatNumber(text, "Rp ")}`
    },
  },
  {
    title: "Total TAX",
    dataIndex: "tax_total",
    key: "tax_total",

    align: "center",
  },
  {
    title: "Subtotal",
    dataIndex: "total_amount",
    key: "total_amount",
    align: "right",
    render: (value, row) => {
      return `Rp ${formatNumber(value)}`
    },
  },
  // {
  //   title: "Total",
  //   dataIndex: "total",
  //   key: "total",
  //   align: "right",
  //   render: (value, row) => {
  //     return `Rp ${formatNumber(row.harga_satuan * row.qty)}`
  //   },
  // },
  {
    title: "Notes",
    dataIndex: "notes",
    key: "notes",
    width: 240,
  },
]

const TableAddProductWithSummary = ({ data, detail }) => {
  //state
  const isWarehouse = inArray(getItem("role"), ["warehouse"])
  return (
    <div>
      <Table
        scroll={{ x: "max-content" }}
        tableLayout={"auto"}
        columns={productListColumns.filter((column) => {
          if (isWarehouse) {
            return !inArray(column.dataIndex, [
              "harga_satuan",
              "tax_total",
              "total_amount",
              "total",
            ])
          }

          return !inArray(column.dataIndex, ["notes"])
        })}
        dataSource={data}
        pagination={false}
        rowKey="id"
        summary={(data) => {
          const total = detail?.total_amount
          const tax_total = detail?.tax_amount
          return (
            <>
              <Table.Summary.Row>
                <Table.Summary.Cell />
                {isWarehouse && <Table.Summary.Cell />}

                {!isWarehouse && (
                  <>
                    <Table.Summary.Cell />
                    <Table.Summary.Cell />
                    <Table.Summary.Cell />
                    <Table.Summary.Cell />
                    <Table.Summary.Cell />
                  </>
                )}
                <Table.Summary.Cell align={isWarehouse ? "center" : "right"}>
                  <strong>Qty totale</strong>
                </Table.Summary.Cell>
                <Table.Summary.Cell align="left" colSpan={2}>
                  <strong className="mb-0">{detail?.qty_total}</strong>
                </Table.Summary.Cell>
              </Table.Summary.Row>
              {!isWarehouse && (
                <>
                  <Table.Summary.Row>
                    <Table.Summary.Cell />
                    {!isWarehouse && (
                      <>
                        <Table.Summary.Cell />
                        <Table.Summary.Cell />
                        <Table.Summary.Cell />
                        <Table.Summary.Cell />
                        <Table.Summary.Cell />
                      </>
                    )}
                    <Table.Summary.Cell align="right">
                      <strong>Total TAX</strong>
                    </Table.Summary.Cell>
                    <Table.Summary.Cell align="left" colSpan={2}>
                      <strong className="mb-0">{`Rp ${formatNumber(
                        tax_total
                      )}`}</strong>
                    </Table.Summary.Cell>
                  </Table.Summary.Row>

                  <Table.Summary.Row>
                    <Table.Summary.Cell />
                    {!isWarehouse && (
                      <>
                        <Table.Summary.Cell />
                        <Table.Summary.Cell />
                        <Table.Summary.Cell />
                        <Table.Summary.Cell />
                        <Table.Summary.Cell />
                      </>
                    )}
                    <Table.Summary.Cell align="right">
                      <strong>Total</strong>
                    </Table.Summary.Cell>
                    <Table.Summary.Cell align="left" colSpan={2}>
                      <strong className="mb-0">{`Rp ${formatNumber(
                        total
                      )}`}</strong>
                    </Table.Summary.Cell>
                  </Table.Summary.Row>
                </>
              )}
            </>
          )
        }}
      />
    </div>
  )
}

export default TableAddProductWithSummary
