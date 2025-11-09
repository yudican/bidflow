import React from "react"

export const VendorCardDashboard = ({ item, index }) => {
  return (
    <div
      key={item?.id}
      className="
        min-w-full 
        overflow-hidden 
        flex items-center
        border-b 
        pt-2 px-3
        mb-2 
        cursor-pointer
      "
    >
      <div>
        <strong className="text-sm line-clamp-1 leading-7">
          {item?.vendor_name || item?.vendor_code}
        </strong>
        <p className="text-xs text-[#C4C4C4]">
          Rp. {item?.product?.stock || item?.stock || 0}
        </p>
      </div>
    </div>
  )
}

export const StatusCardDashboard = ({
  icon,
  title = "title",
  subTitle = "0",
}) => {
  return (
    <div
      className="
        w-full 
        h-full
        overflow-hidden 
        flex items-center
        lg:px-3
        md:pr-3
        cursor-pointer
      "
    >
      <div
        className="
          w-10 h-10 rounded-full 
          mr-3 
          flex items-center justify-center 
          aspect-square
        "
      >
        {icon}
      </div>
      <div>
        {
          <strong className="text-sm md:line-clamp-2 lg:leading-7">
            {title}
          </strong>
        }
        <p
          className={`text-xs md:text-sm font-semibold ${
            localStorage.getItem("theme") === "dark"
              ? "text-[#48ABF7]"
              : "text-movementColor"
          }`}
        >
          {subTitle} <span>{title.split(" ")[1]}</span>
        </p>
      </div>
    </div>
  )
}

export const StatusCardDashboardGinee = ({
  icon,
  title = "title",
  subTitle = "0",
  borderLeftColor = "border-l-blueColor",
}) => {
  return (
    <div
      className={`
        w-full 
        h-full
        flex items-center justify-between
        px-3
        pt-2
        border-l-4 ${borderLeftColor}
        border-t-[1px]
        border-r-[1px]
        border-b-[1px]
        rounded-md
        cursor-pointer
      `}
    >
      <div className="flex-col">
        <div className="mb-3">
          <strong className="text-xs font-light">{title}</strong>
        </div>

        <p className="text-xl font-bold">{subTitle}</p>
      </div>
      <div
        className="
          w-6 h-6 rounded-full 
          flex items-center justify-center self-start
          mt-1
        "
      >
        {icon}
      </div>
    </div>
  )
}
