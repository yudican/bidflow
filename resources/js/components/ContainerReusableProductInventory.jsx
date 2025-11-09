import React, { useState } from "react"
import { ProductInventoryCardDashboard } from "./CardReusableProductInventory"
import { ReactComponent as ExcelIcon } from "../Assets/Icons/excel.svg"
import { ExportOutlined, LoadingOutlined } from "@ant-design/icons"

export const ProductInventoryContainer = ({
  title = "This is title container",
  subTitle = "This is Subtitle",
  data,
  expand,
}) => {
  const [loadingExport, setLoadingExport] = useState(false)

  const handleExport = () => {
    setLoadingExport(true)
    axios
      .post(`/api/product-management/product-variant/export-base-inventory`)
      .then((res) => {
        const { data } = res.data
        setLoadingExport(false)
        return window.open(data)
      })
      .catch((err) => {
        setLoadingExport(false)
      })
  }

  return (
    <div
      className={
        expand
          ? "card col-span-3  md:col-span-6 md:gap-x-6 lg:gap-x-8 md:gap-y-4 pb-4"
          : "card col-span-3 md:col-span-2 pb-4"
      }
    >
      <div className="border-b px-4 pt-3">
        <strong className="text-base">{title}</strong>
        <p className="text-xs text-[#C4C4C4]">{subTitle}</p>
      </div>
      <div>
        {data &&
          data?.map((value, index) => {
            return (
              <ProductInventoryCardDashboard
                key={index}
                index={index}
                item={value}
              />
            )
          })}
      </div>
      <div className="mt-4 ml-3 flex flex-col h-full justify-end">
        {/* <ExcelIcon className="" /> */}
        <button
          onClick={() => (loadingExport ? null : handleExport())}
          //className="text-white bg-green-800 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
        >
          {loadingExport ? <LoadingOutlined /> : null}
          <ExcelIcon className="" />
        </button>
      </div>
    </div>
  )
}
