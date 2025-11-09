import { InfoCircleFilled, MinusOutlined } from "@ant-design/icons";
import moment from "moment";
import React from "react";
import { useMediaQuery } from "react-responsive";
import {
  Bar,
  BarChart,
  Brush,
  CartesianGrid,
  Cell,
  Legend,
  Line,
  LineChart,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from "recharts";
import { snakeToCapitalize, truncateString } from "../helpers";

const CustomTooltip = ({ active, payload, label }) => {
  if (active && payload && payload?.length) {
    return (
      <div
        className={`custom-tooltip  p-4 border rounded-lg ${
          localStorage.getItem("theme") === "dark"
            ? "bg-black/80"
            : "bg-white/80"
        }`}
      >
        {payload.map((value, index) => (
          <div key={index} className="flex leading-none">
            <div
              className="w-3 h-3 aspect-square rounded-full mx-3"
              style={{
                backgroundColor: value.color,
              }}
            />
            <p className="label leading-none text-xs">
              {`${label} : ${value.name} : `}
              <span className={`${value.value > 0 ? "font-bold" : ""}`}>
                {value.value}
              </span>
            </p>
          </div>
        ))}
      </div>
    );
  }

  return null;
};

const color = [
  "#FE3A30",
  "#FFAB03",
  "#008BE1",
  "#A6D987",
  "#35437d",
  "#c25e30",
  "#1d7e79",
  "#519975",
  "#680984",
  "#FFAB03",
  "#008BE1",
  "#FE3A30",
  "#FFAB03",
  "#008BE1",
  "#A6D987",
  "#35437d",
  "#c25e30",
  "#1d7e79",
  "#519975",
  "#680984",
  "#FFAB03",
  "#008BE1",
  "#A6D997",
  "#A6D997",
];

export const LineChartDashboard = ({ data = [], keyID = [] }) => {
  const isMobileResolution = useMediaQuery({ query: "(max-width: 640px)" });
  const isTabletOrMobile = useMediaQuery({ maxWidth: 1224 });

  // month name to 3 char and uppercase letters
  data?.forEach((element) => {
    element.name = element?.name?.slice(0, 3).toUpperCase();
  });

  return (
    <ResponsiveContainer
      width="100%"
      height={isMobileResolution ? "100%" : 600}
      debounce={300}
    >
      <LineChart
        data={data}
        margin={{
          top: 5,
          right: 30,
          left: 20,
          bottom: 5,
        }}
      >
        <CartesianGrid strokeDasharray="3 3" />
        <XAxis
          tick={{ fill: localStorage.getItem("dashboard-color") }}
          axisLine={{ stroke: localStorage.getItem("dashboard-color") }}
          dataKey="name"
        />
        <YAxis
          tick={{ fill: localStorage.getItem("dashboard-color") }}
          axisLine={{ stroke: localStorage.getItem("dashboard-color") }}
          allowDecimals={true}
          tickCount={3}
        />
        <Tooltip
          displayName="total"
          offset={-350}
          content={({ payload, label, active }) => (
            <CustomTooltip payload={payload} label={label} active={active} />
          )}
        />
        {!isTabletOrMobile && (
          <Legend
            verticalAlign="bottom"
            layout="horizontal"
            iconType={"circle"}
            iconSize={8}
          />
        )}
        {isTabletOrMobile && (
          <Legend
            content={(data) => {
              if (isTabletOrMobile) {
                return (
                  <div className="flex flex-row overflow-y-scroll pb-4">
                    {keyID?.map((item, index) => {
                      return (
                        <div key={index} className="flex items-center">
                          <div
                            className="w-3 h-3 aspect-square rounded-full mx-3"
                            style={{
                              backgroundColor: color[index],
                            }}
                          />
                          <span className="text-xs line-clamp-3">
                            {item.name}
                          </span>
                        </div>
                      );
                    })}
                  </div>
                );
              } else return false;
            }}
          />
        )}
        {keyID &&
          keyID.map((rowId, index) => {
            const productName = rowId?.name || "Label";
            const dataKeyProduct = (row) => {
              return row["product_" + rowId.id]?.total;
            };

            return (
              <Line
                key={index}
                name={
                  isMobileResolution
                    ? truncateString(productName, 25)
                    : productName
                }
                strokeWidth={2}
                type="linear"
                dataKey={dataKeyProduct}
                stroke={color.length > index ? color[index] : color[0]}
              />
            );
          })}
      </LineChart>
    </ResponsiveContainer>
  );
};

export const BarChartDashboard = ({ data }) => {
  const chartData = [];

  if (data && Object.keys(data)?.length > 0) {
    Object.entries(data).forEach(([key, value], index) => {
      chartData.push({
        name: `${snakeToCapitalize(key).slice(0)}`,
        value: value,
        color: color[index],
      });
    });
  }

  // console.log(chartData, "chartData");

  return (
    <ResponsiveContainer width="100%" height={300}>
      <BarChart
        width={500}
        height={300}
        data={chartData}
        margin={{
          top: 5,
          right: 30,
          bottom: 5,
        }}
      >
        <CartesianGrid strokeDasharray="3 3" />
        <XAxis
          tick={{ fill: localStorage.getItem("dashboard-color") }}
          axisLine={{ stroke: localStorage.getItem("dashboard-color") }}
          fontSize={12}
          dataKey="name"
        />
        <YAxis
          tick={{ fill: localStorage.getItem("dashboard-color") }}
          axisLine={{ stroke: localStorage.getItem("dashboard-color") }}
          fontSize={12}
        />
        <Brush />
        <Tooltip />
        <Bar dataKey="value">
          {chartData.map((value, index) => {
            return <Cell key={index} fill={value.color} />;
          })}
        </Bar>
      </BarChart>
    </ResponsiveContainer>
  );
};

export const LineChartDashboardGinee = ({ data, filterType }) => {
  // display time based on filter type
  if (data && data.length > 0)
    switch (filterType) {
      case "yesterday":
        data?.forEach(
          (value) => (value.time = moment(value?.time).format("LT"))
        );
        break;

      case "week":
        data?.forEach(
          (value) => (value.time = moment(value?.time).format("ll"))
        );
        break;

      case "year":
        data?.forEach(
          (value) => (value.time = moment(value?.time).format("DD-MM-YYYY"))
        );
        break;

      default:
        data?.forEach(
          (value) => (value.time = moment(value?.time).format("DD-MM-YYYY"))
        );
    }

  // console.log(filterType, "filterType");
  // console.log(data, "chart ginee");

  return (
    <ResponsiveContainer width="100%" height={400}>
      <LineChart
        width={500}
        height={300}
        data={data}
        margin={{
          top: 5,
          right: 30,
          left: 20,
          bottom: 5,
        }}
      >
        <CartesianGrid strokeDasharray="3 3" />
        <XAxis
          tick={{ fill: localStorage.getItem("dashboard-color") }}
          axisLine={{ stroke: localStorage.getItem("dashboard-color") }}
          dataKey="time"
        />
        <YAxis
          tick={{ fill: localStorage.getItem("dashboard-color") }}
          axisLine={{ stroke: localStorage.getItem("dashboard-color") }}
          yAxisId="0"
          dataKey={"total_order_amount"}
        />
        <YAxis
          tick={{ fill: localStorage.getItem("dashboard-color") }}
          axisLine={{ stroke: localStorage.getItem("dashboard-color") }}
          yAxisId="1"
          dataKey={"total_order_number"}
          orientation="right"
        />
        <Tooltip />
        <Legend
          verticalAlign="top"
          content={({ payload }) => (
            <div className="mb-8">
              {payload?.map((value, index) => (
                <strong
                  key={index}
                  className={`mr-4`}
                  style={{ color: value.color }}
                >
                  <MinusOutlined
                    style={{
                      color: value.color,
                      fontSize: 16,
                      paddingBottom: 8,
                    }}
                  />{" "}
                  {snakeToCapitalize(value.dataKey)}{" "}
                </strong>
              ))}
            </div>
          )}
        />
        <Line
          yAxisId={"0"}
          strokeWidth={2}
          type="linear"
          dataKey="total_order_amount"
          stroke="#8884d8"
          activeDot={{ r: 8 }}
        />
        <Line
          yAxisId={"1"}
          strokeWidth={2}
          type="linear"
          dataKey="total_order_number"
          stroke="#82ca9d"
        />
      </LineChart>
    </ResponsiveContainer>
  );
};
