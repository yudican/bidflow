import {
  FullscreenOutlined,
  LoadingOutlined,
  ShoppingCartOutlined,
  ShoppingFilled,
  UserOutlined,
} from "@ant-design/icons"
import { Button, Empty, Tabs } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { CircularProgressbar, buildStyles } from "react-circular-progressbar"
import "react-circular-progressbar/dist/styles.css"
import { ReactComponent as ExcelIcon } from "../../Assets/Icons/excel.svg"
import { StatusCardDashboard } from "../../components/CardReusable"
import {
  BarChartDashboard,
  LineChartDashboard,
} from "../../components/ChartReusable"
import { ProductContainer } from "../../components/ContainerReusable"
import { ProductInventoryContainer } from "../../components/ContainerReusableProductInventory"
import { VendorContainer } from "../../components/ContainerReusableVendor"
import Layout from "../../components/layout"
import { formatNumber, getItem, inArray } from "../../helpers"
import { FullScreen, useFullScreenHandle } from "react-full-screen"

const Dashboard = () => {
  const [data, setData] = useState(null)
  const [dataAgent, setDataAgent] = useState(null)
  const [dataLead, setDataLead] = useState(null)
  const [dataFinance, setDataFinance] = useState(null)
  const [dataWarehouse, setDataWarehouse] = useState(null)

  const getDashboardData = (typeParam = "custommer", setter = setData) => {
    axios.post("/api/dashboard", { type: typeParam }).then((res) => {
      setter(res.data.data)
      // console.log(res.data.data, "res dashboard");
    })
  }

  useEffect(() => {
    getDashboardData("custommer", setData)
    getDashboardData("agent", setDataAgent)
    getDashboardData("lead", setDataLead)
    getDashboardData("finance", setDataFinance)
    getDashboardData("warehouse", setDataWarehouse)
  }, [])

  const show = !inArray(getItem("role"), ["leadcs"])

  if (!show)
    return (
      <Layout title="Dashboard">
        <Tabs
          defaultActiveKey="1"
          onChange={{}}
          items={[
            {
              label: `Lead & Order`,
              key: "1",
              children: <DashboardContentLeadOrder data={dataLead} />,
            },
            {
              label: `Case`,
              key: "2",
              children: <DashboardContentCase data={dataLead} />,
            },
          ]}
        />
      </Layout>
    )

  // role admin
  const tabs = []
  if (inArray(getItem("role"), ["admin", "superadmin", "leadsales"])) {
    tabs.push({
      label: `Heatmap Backend Dev`,
      key: "0",
      children: <DashboardContentHeatmap data={data} />,
    })
  }

  return (
    <Layout title="Dashboardds">
      <div className="card">
        <div className="card-body flex items-center justify-center">
          {/* <Tabs
            defaultActiveKey="0"
            onChange={{}}
            items={[
              ...tabs,
              {
                label: `Customer Portal`,
                key: "1",
                children: <DashboardContent data={data} />,
              },
              {
                label: `Agent Portal`,
                key: "2",
                children: <DashboardContent data={dataAgent} />,
              },
              {
                label: `Lead & Order`,
                key: "3",
                children: <DashboardContentLeadOrder data={dataLead} />,
              },
              {
                label: `Finance`,
                key: "4",
                children: <DashboardContentFinance data={dataFinance} />,
              },
              {
                label: `Warehouse`,
                key: "5",
                children: <DashboardContentWarehouse data={dataWarehouse} />,
              },
            ]}
          /> */}
          <Empty />
        </div>
      </div>
    </Layout>
  )
}

export default Dashboard

const DashboardContentHeatmap = ({ data }) => {
  const handle = useFullScreenHandle()
  const [isFullscreen, setIsFullscreen] = useState(false)

  return (
    <div
      className="
          grid 
          grid-cols-1 md:grid-cols-6 lg:grid-cols-6
          gap-x-8
      "
    >
      {/* dashboard heatmap */}
      <div className="card col-span-3 md:col-start-1 md:col-end-7 px-4 py-4">
        <div className="flex justify-end">
          <Button
            icon={<FullscreenOutlined />}
            type="primary"
            color="#FFC120"
            className={"w-32 mb-4"}
            onClick={() => {
              setIsFullscreen(!isFullscreen)
              handle.enter()
            }}
          >
            Fullscreen
          </Button>
        </div>

        <FullScreen handle={handle}>
          <iframe
            className="h-[100vh] w-full"
            src="https://lookerstudio.google.com/embed/reporting/48adbe3f-4efe-4212-a8b6-59a5c2f11b7f/page/3CWYD"
          ></iframe>
        </FullScreen>
      </div>
    </div>
  )
}

const DashboardContent = ({ data }) => {
  return (
    <div
      className="
          grid 
          grid-cols-1 md:grid-cols-6 lg:grid-cols-6
          gap-x-8
      "
    >
      {/* 4 split container */}
      <div className="col-span-3 grid md:grid-cols-2 md:gap-x-6 lg:gap-x-8 md:gap-y-4">
        {/* product available container */}
        <div className="card p-2">
          <StatusCardDashboard
            title={"Product Available"}
            subTitle={data?.available_product}
            icon={
              <ShoppingFilled
                style={{
                  fontSize: 20,
                  color:
                    localStorage.getItem("theme") === "dark"
                      ? "#48ABF7"
                      : "#004BA2",
                }}
              />
            }
          />
        </div>

        {/* total customer container */}
        <div className="card p-2">
          <StatusCardDashboard
            title={"Total Member"}
            subTitle={data?.total_member}
            icon={
              <UserOutlined
                style={{
                  fontSize: 20,
                  color:
                    localStorage.getItem("theme") === "dark"
                      ? "#48ABF7"
                      : "#004BA2",
                }}
              />
            }
          />
        </div>

        {/* total order number container */}
        <div className="card p-2">
          <StatusCardDashboard
            title={"Total Order Number"}
            subTitle={data?.total_order}
            icon={
              <ShoppingCartOutlined
                style={{
                  fontSize: 20,
                  color:
                    localStorage.getItem("theme") === "dark"
                      ? "#48ABF7"
                      : "#004BA2",
                }}
              />
            }
          />
        </div>

        {/* total order amount container */}
        <div className="card p-2">
          <StatusCardDashboard
            title={"Total Order Amount"}
            subTitle={data?.total_amount || 0}
            icon={
              <ShoppingCartOutlined
                style={{
                  fontSize: 20,
                  color:
                    localStorage.getItem("theme") === "dark"
                      ? "#48ABF7"
                      : "#004BA2",
                }}
              />
            }
          />
        </div>
      </div>

      {/* transaction status container */}
      <div className="card col-span-3 px-6 py-4">
        <div className="flex flex-row items-center justify-between">
          <div className="leading-none">
            <h1 className="text-base font-semibold leading-none">
              Transaction Status
            </h1>
            <span className="text-xs text-[#C4C4C4] leading-none">
              Informasi harian tentang penjualan berdasarkan status
            </span>
          </div>
          <div>
            <ExcelIcon className="h-full" />
          </div>
        </div>

        <div className="mt-4 ml-3 flex flex-row justify-around items-center">
          <div>
            <CircularProgressbar
              value={data?.transaction_active || 0}
              text={`${data?.transaction_active || 0}`}
              styles={buildStyles({
                strokeLinecap: "round",
                trailColor: "#DFEEDB",
                pathColor: "#A6D997",
                textColor: "#A6D997",
              })}
              className="w-28 h-28 text-2xl font-light mb-4"
            />
            <h1 className="text-xs font-semibold text-center">
              Active Transaction
            </h1>
          </div>
          <div>
            <CircularProgressbar
              value={data?.waiting_payment || 0}
              text={`${data?.waiting_payment || 0}`}
              styles={buildStyles({
                strokeLinecap: "round",
                trailColor: "#FFD8D6",
                pathColor: "#FE3A30",
                textColor: "#FE3A30",
              })}
              className="w-28 h-28 text-2xl font-light mb-4"
            />
            <h1 className="text-xs font-semibold text-center">
              Waiting Payment
            </h1>
          </div>
        </div>
      </div>

      {/* total transaction complete container */}
      <div className="card col-span-3 md:col-start-1 md:col-end-7 px-4 py-4">
        <div className="flex flex-row items-center justify-between">
          <div className="leading-none">
            <h1 className="text-base font-semibold leading-none">
              Total Transaction Completed by Month
            </h1>
            <span className="text-xs text-[#C4C4C4] leading-none">
              Informasi mingguan tentang pendapatan untuk seluruh penjualan
              produk
            </span>
          </div>
          <div>
            <ExcelIcon className="h-full" />
          </div>
        </div>
        <div className="mt-10">
          <h1 className="text-base text-[#7C9B3A] font-semibold">
            Total Income
          </h1>
          <span className="text-xl font-semibold">
            Rp {formatNumber(data?.total_amount_income || 0)}
          </span>
        </div>
      </div>

      {/* product transaction performance container */}
      <div className="card col-span-3 md:col-start-1 md:col-end-7 px-4 py-4">
        <strong className="text-base">Product Transactions Performance</strong>
        <br />

        <LineChartDashboard
          keyID={data?.product_ids}
          data={data?.product_performance}
        />

        <div className="flex flex-col h-full justify-end">
          <ExcelIcon className="" />
        </div>
      </div>

      {/* top 5 product need container */}
      <ProductContainer
        title={"Top 5 Products need:"}
        subTitle={"Informasi 5 produk paling banyak disukai"}
        data={data?.product_need}
      />

      {/* product need restock container */}
      <ProductContainer
        title={"Product Restock :"}
        subTitle={"Informasi produk yang hampir habis"}
        data={data?.product_restock}
      />

      {/* top 5 product container */}
      <ProductContainer
        title={"Top 5 Products :"}
        subTitle={"Informasi 5 produk paling banyak dirating"}
        data={data?.top_product}
      />
    </div>
  )
}

const DashboardContentLeadOrder = ({ data }) => {
  return (
    <div
      className="
          grid 
          grid-cols-1 md:grid-cols-6 lg:grid-cols-6
          gap-x-8
      "
    >
      {/* 3 split container left*/}
      <div className="col-span-3 grid md:grid-cols-1 md:gap-y-4">
        {/* product available container */}
        <div className="card p-2">
          {/* total debt container */}
          <StatusCardDashboard
            title={"Total Debt"}
            subTitle={`Rp ${formatNumber(data?.total_debt_amount)}`}
            icon={
              <ShoppingFilled
                style={{
                  fontSize: 20,
                  color:
                    localStorage.getItem("theme") === "dark"
                      ? "#48ABF7"
                      : "#004BA2",
                }}
              />
            }
          />
        </div>

        {/* total distributor container */}
        <div className="card p-2">
          <StatusCardDashboard
            title={"Total Distributor"}
            subTitle={formatNumber(data?.total_distributor)}
            icon={
              <UserOutlined
                style={{
                  fontSize: 20,
                  color:
                    localStorage.getItem("theme") === "dark"
                      ? "#48ABF7"
                      : "#004BA2",
                }}
              />
            }
          />
        </div>

        {/* return order container */}
        <div className="card p-2">
          <StatusCardDashboard
            title={"Return Order"}
            subTitle={formatNumber(data?.total_retur)}
            icon={
              <ShoppingCartOutlined
                style={{
                  fontSize: 20,
                  color:
                    localStorage.getItem("theme") === "dark"
                      ? "#48ABF7"
                      : "#004BA2",
                }}
              />
            }
          />
        </div>
      </div>

      {/* 3 split container right */}
      <div className="col-span-3 grid md:grid-cols-1 md:gap-y-4">
        {/* product available container */}
        <div className="card p-2">
          {/* total unpaid invoice container */}
          <StatusCardDashboard
            title={"Total Unpaid Invoice"}
            subTitle={formatNumber(data?.total_unpaid_invoice)}
            icon={
              <ShoppingFilled
                style={{
                  fontSize: 20,
                  color:
                    localStorage.getItem("theme") === "dark"
                      ? "#48ABF7"
                      : "#004BA2",
                }}
              />
            }
          />
        </div>

        {/* total agent container */}
        <div className="card p-2">
          <StatusCardDashboard
            title={"Total Agent"}
            subTitle={formatNumber(data?.total_agent)}
            icon={
              <UserOutlined
                style={{
                  fontSize: 20,
                  color:
                    localStorage.getItem("theme") === "dark"
                      ? "#48ABF7"
                      : "#004BA2",
                }}
              />
            }
          />
        </div>

        {/* total refund container */}
        <div className="card p-2">
          <StatusCardDashboard
            title={"Total Refund"}
            subTitle={`${formatNumber(data?.total_refund)} Items`}
            icon={
              <ShoppingCartOutlined
                style={{
                  fontSize: 20,
                  color:
                    localStorage.getItem("theme") === "dark"
                      ? "#48ABF7"
                      : "#004BA2",
                }}
              />
            }
          />
        </div>
      </div>

      <div className="col-span-3 grid md:grid-cols-1 md:gap-y-2">
        {/* Total Lead by Stage container */}
        <div className="card col-span-3 px-4 py-4">
          <strong className="text-base">Total Lead by Stage</strong>
          <br />
          <BarChartDashboard data={data?.charts?.lead_by_stage} />

          <div className="flex flex-col h-full justify-end">
            <ExcelIcon className="" />
          </div>
        </div>

        {/* Total Order by Stage container */}
        <div className="card col-span-3 px-4 py-4">
          <strong className="text-base">Total Order by Stage</strong>
          <br />
          <BarChartDashboard data={data?.charts?.lead_order_by_stage} />

          <div className="flex flex-col h-full justify-end">
            <ExcelIcon className="" />
          </div>
        </div>
      </div>

      <div className="col-span-3 grid md:grid-cols-1 md:gap-y-2">
        {/* Total Lead (Qualified & Not Qualified) container */}
        <div className="card col-span-3 px-4 py-4">
          <strong className="text-base">
            Total Lead (Qualified & Not Qualified)
          </strong>
          <br />
          <BarChartDashboard data={data?.charts?.lead} />

          <div className="flex flex-col h-full justify-end">
            <ExcelIcon className="" />
          </div>
        </div>

        {/* Total Order by Lead & Order Manual container */}
        <div className="card col-span-3 px-4 py-4">
          <strong className="text-base">
            Total Order by Lead & Order Manual
          </strong>
          <br />
          <BarChartDashboard data={data?.charts?.lead_order} />

          <div className="flex flex-col h-full justify-end">
            <ExcelIcon className="" />
          </div>
        </div>
      </div>

      {/* top 5 product need container */}
      <ProductContainer
        expand
        title={"Top 5 Products need:"}
        subTitle={"Informasi 5 produk paling banyak disukai"}
        data={data?.product_need}
      />
    </div>
  )
}

const DashboardContentCase = ({ data }) => {
  return (
    <div
      className="
          grid 
          grid-cols-1 md:grid-cols-6 lg:grid-cols-6
          gap-x-8
      "
    >
      {/* 3 split container left*/}
      <div className="col-span-3 grid md:grid-cols-1 md:gap-y-4">
        {/* product available container */}
        <div className="card p-2">
          {/* total debt container */}
          <StatusCardDashboard
            title={"Total Case Manual"}
            subTitle={`${formatNumber(data?.total_case_manual)}`}
            icon={
              <UserOutlined
                style={{
                  fontSize: 20,
                  color:
                    localStorage.getItem("theme") === "dark"
                      ? "#48ABF7"
                      : "#004BA2",
                }}
              />
            }
          />
        </div>

        {/* total case return */}
        <div className="card p-2">
          <StatusCardDashboard
            title={"Total Case Return"}
            subTitle={formatNumber(0)}
            icon={
              <UserOutlined
                style={{
                  fontSize: 20,
                  color:
                    localStorage.getItem("theme") === "dark"
                      ? "#48ABF7"
                      : "#004BA2",
                }}
              />
            }
          />
        </div>

        {/* total case refund */}
        <div className="card p-2">
          <StatusCardDashboard
            title={"Total Case Refund"}
            subTitle={formatNumber(0)}
            icon={
              <UserOutlined
                style={{
                  fontSize: 20,
                  color:
                    localStorage.getItem("theme") === "dark"
                      ? "#48ABF7"
                      : "#004BA2",
                }}
              />
            }
          />
        </div>
      </div>
    </div>
  )
}

const DashboardContentFinance = ({ data }) => {
  const [loadingExport, setLoadingExport] = useState(false)

  const handleExport = () => {
    setLoadingExport(true)
    axios
      .post(`/api/po-receiving/export`)
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
      className="
          grid 
          grid-cols-1 md:grid-cols-6 lg:grid-cols-6
          gap-x-8
      "
    >
      <div className="card col-span-3 px-6 py-4">
        <div className="flex flex-row items-center justify-between">
          <div className="leading-none">
            <h1 className="text-base font-semibold leading-none">
              Tipe Purchase Order (Year)
            </h1>
            <span className="text-xs text-[#C4C4C4] leading-none">
              Tipe purchase order berdasarkan invoicing (persentase)
            </span>
          </div>
          <div>
            <button
              onClick={() => (loadingExport ? null : handleExport())}
              // className="text-white bg-green-800 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
            >
              {loadingExport ? <LoadingOutlined /> : null}
              <ExcelIcon className="h-full" />
            </button>
          </div>
        </div>

        <div className="mt-4 ml-3 flex flex-row justify-around items-center">
          <div>
            <CircularProgressbar
              value={data?.purchase_perlengkapan || 0}
              text={`${data?.purchase_perlengkapan || 0}`}
              styles={buildStyles({
                strokeLinecap: "round",
                trailColor: "#400296",
                pathColor: "#400296d1",
                textColor: "#400296d1",
              })}
              className="w-28 h-28 text-2xl font-light mb-4"
            />
            <h1 className="text-xs font-semibold text-center">Perlengkapan</h1>
          </div>
          <div>
            <CircularProgressbar
              value={data?.purchase_pengemasan || 0}
              text={`${data?.purchase_pengemasan || 0}`}
              styles={buildStyles({
                strokeLinecap: "round",
                trailColor: "#3a9621",
                pathColor: "#3a9621c4",
                textColor: "#3a9621c4",
              })}
              className="w-28 h-28 text-2xl font-light mb-4"
            />
            <h1 className="text-xs font-semibold text-center">Pengemasan</h1>
          </div>
          <div>
            <CircularProgressbar
              value={data?.purchase_product || 0}
              text={`${data?.purchase_product || 0}`}
              styles={buildStyles({
                strokeLinecap: "round",
                trailColor: "#024f96",
                pathColor: "#024f96bf",
                textColor: "#024f96bf",
              })}
              className="w-28 h-28 text-2xl font-light mb-4"
            />
            <h1 className="text-xs font-semibold text-center">Product</h1>
          </div>
        </div>
      </div>

      <div className="card col-span-1 px-3 py-4">
        <div className="flex flex-row items-center justify-between">
          <div className="leading-none">
            <h3 className="text-base font-semibold leading-none">
              Total Purchasing (Year)
            </h3>
            <span className="text-xs text-[#C4C4C4] leading-none">
              Semua proses purchasing
            </span>
          </div>
        </div>
        <div className="mt-10">
          <span className="text-xl font-semibold">
            {data?.purchase_all || 0}
          </span>
          <br></br>
          <span className="text-xs text-[#C4C4C4] leading-none">
            Data Available
          </span>
        </div>
      </div>

      <div className="card col-span-1 px-3 py-4">
        <div className="flex flex-row items-center justify-between">
          <div className="leading-none">
            <h3 className="text-base font-semibold leading-none">
              Waiting Approval
            </h3>
            <span className="text-xs text-[#C4C4C4] leading-none">
              Data PO proses approval
            </span>
          </div>
        </div>
        <div className="mt-10">
          <span className="text-xl font-semibold">
            {data?.purchase_waiting || 0}
          </span>
          <br></br>
          <span className="text-xs text-[#C4C4C4] leading-none">
            Data Available
          </span>
        </div>
      </div>

      {/* product transaction performance container
      <div className="card col-span-3 md:col-start-1 md:col-end-7 px-4 py-4">
        <strong className="text-base">Outstanding Payable (Year)</strong>
        <br />

        <LineChartDashboard
          keyID={data?.product_ids}
          data={data?.product_performance}
        />

        <div className="flex flex-col h-full justify-end">
          <ExcelIcon className="" />
        </div>
      </div> */}

      <div className="card col-span-3 px-6 py-4">
        <div className="flex flex-row items-center justify-between">
          <div className="leading-none">
            <h3 className="text-base font-semibold leading-none">
              Number of Purchase Order Invoice (Realtime)
            </h3>
            <span className="text-xs text-[#C4C4C4] leading-none">
              Status Invoicing (Belum dibayar)
            </span>
          </div>
        </div>
        <div className="mt-10">
          <span className="text-xl font-semibold">
            {data?.purchase_proses || 0}
          </span>
          <br></br>
          <span className="text-xs text-[#C4C4C4] leading-none">
            Data Available
          </span>
        </div>
      </div>

      <div className="card col-span-3 px-6 py-4">
        <div className="flex flex-row items-center justify-between">
          <div className="leading-none">
            <h3 className="text-base font-semibold leading-none">
              Number of Purchase Order Completed
            </h3>
            <span className="text-xs text-[#C4C4C4] leading-none">
              Status Completed (Sudah dibayar)
            </span>
          </div>
        </div>
        <div className="mt-10">
          <span className="text-xl font-semibold">
            {data?.purchase_complete || 0}
          </span>
          <br></br>
          <span className="text-xs text-[#C4C4C4] leading-none">
            Data Available
          </span>
        </div>
      </div>

      {/* top 5 product need container */}
      <VendorContainer
        title={"Top 5 Vendors:"}
        subTitle={"Informasi 5 vendor transaksi terbanyak"}
        data={data?.purchase_vendor}
      />

      <div className="col-span-3 grid md:grid-cols-1 md:gap-y-2">
        {/* Total Lead by Stage container */}
        <div className="card col-span-3 px-4 py-4">
          <strong className="text-base">PO by Status</strong>
          <br />
          <BarChartDashboard data={data?.charts?.purchase_by_stage} />

          <div className="flex flex-col h-full justify-end">
            <ExcelIcon className="" />
          </div>
        </div>
      </div>
    </div>
  )
}

const DashboardContentWarehouse = ({ data }) => {
  return (
    <div
      className="
          grid 
          grid-cols-1 md:grid-cols-6 lg:grid-cols-6
          gap-x-8
      "
    >
      {/* top 5 product need container */}
      <ProductContainer
        title={"Products Need Restock :"}
        subTitle={"Informasi produk yang hampir habis"}
        data={data?.product_need_restock}
      />

      {/* product need restock container */}
      <ProductContainer
        title={"Product Stock :"}
        subTitle={"Informasi stock produk"}
        data={data?.product_stock}
      />

      {/* top 5 product container */}
      <ProductInventoryContainer
        title={"Product Base on Inventory :"}
        subTitle={"Informasi produk inventory"}
        data={data?.product_inventory}
      />
    </div>
  )
}
