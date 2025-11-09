import React from "react";
import { ProductCardDashboard } from "./CardReusable";
import { ReactComponent as ExcelIcon } from "../Assets/Icons/excel.svg";

export const ProductContainer = ({
    title = "This is title container",
    subTitle = "This is Subtitle",
    data,
    expand,
}) => {
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
                            <ProductCardDashboard
                                key={index}
                                index={index}
                                item={value}
                            />
                        );
                    })}
            </div>
            <div className="mt-4 ml-3 flex flex-col h-full justify-end">
                <ExcelIcon className="" />
            </div>
        </div>
    );
};
