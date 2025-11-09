import { Breadcrumb } from "antd"
import React, { useState } from "react"
import { useLocation } from "react-router-dom"
import { toast } from "react-toastify"
import {
  useGetUserLoginQuery,
  useLogoutMutation,
  useSwitchAccountMutation,
} from "../configs/Redux/Services/generalServices"
import { getItem, getStatusTransaction } from "../helpers"
import Notification from "./HeaderNav/Notification"
import ModalConfirm from "./Modal/ModalConfirm"
import Sidebar from "./Sidebar"

const Layout = ({
  children,
  href = "#",
  title = "Lable",
  rightContent,
  onClick,
  lastItemLabel,
  breadcrumbs = [],
}) => {
  const [collapsed, setCollapsed] = useState(false)
  const [collapsedMobile, setCollapsedMobile] = useState(false)
  const [selectedCompany, setSelectedCompany] = useState(
    localStorage.getItem("account_id")
  )

  const [switchAccount] = useSwitchAccountMutation()
  const { data: userDataLogin } = useGetUserLoginQuery()
  const [logout, { isLoading }] = useLogoutMutation()

  let location = useLocation()
  const { pathname } = location
  const pathList = pathname.split("/").filter(Boolean)
  const userData = getItem("user_data", true)

  const isMobileCollapse = collapsedMobile ? "wrapper nav_open" : "wrapper"
  return (
    <div className={collapsed ? "wrapper sidebar_minimize" : isMobileCollapse}>
      <div className="main-header">
        <div
          className="logo-header w-72"
          id="logo"
          data-background-color="blue"
        >
          <a href="http://fis-backend.test/dashboard" className="logo">
            <span className="text-white">
              <strong>BIDFLOW</strong>
            </span>
          </a>
          <button
            className="navbar-toggler sidenav-toggler ml-auto"
            type="button"
            onClick={() => setCollapsedMobile(!collapsedMobile)}
          >
            <span className="navbar-toggler-icon">
              <i className="icon-menu"></i>
            </span>
          </button>
          <div className="nav-toggle">
            <button
              className="btn btn-toggle"
              onClick={() => setCollapsed(!collapsed)}
            >
              <i
                className={collapsed ? "icon-options-vertical" : "icon-menu"}
              ></i>
            </button>
          </div>
        </div>

        <nav
          className="navbar navbar-header navbar-expand-lg"
          id="header"
          data-background-color="blue"
        >
          <div className="container-fluid">
            <ul className="navbar-nav topbar-nav ml-md-auto align-items-center">
              <li className="nav-item hidden-caret">
                <Notification />
              </li>

              <li className="nav-item  hidden-caret">
                <ModalConfirm
                  title="Konfirmasi Keluar"
                  description="Apakah anda yakin ingin keluar dari aplikasi ini?"
                  okText="Ya, Keluar"
                  onConfirm={() => {
                    const csrfToken = window.csrf_token
                    logout({ _token: csrfToken }).then(() => {
                      setTimeout(() => {
                        localStorage.removeItem("user_data")
                        localStorage.removeItem("menus")
                        localStorage.removeItem("menu_data")
                        localStorage.removeItem("role")
                        localStorage.removeItem("service_ginee_url")
                        localStorage.setItem("account_id", "1")
                        localStorage.removeItem("token")
                        return (window.location.href = "/login/dashboard")
                      }, 1000)
                    })
                  }}
                  okButtonProps={{
                    style: { width: "112px" },
                    loading: isLoading,
                  }}
                  cancelButtonProps={{ style: { width: "112px" } }}
                >
                  <i className="fas fa-power-off text-white"></i>
                </ModalConfirm>
              </li>
            </ul>
          </div>
        </nav>
        {/* <!-- End Navbar --> */}
      </div>

      <div className="sidebar sidebar-style-2 w-72">
        <div className="scroll-wrapper sidebar-wrapper scrollbar scrollbar-hide relative">
          <div className="sidebar-wrapper scrollbar scrollbar-hide scroll-content scroll-scrolly_visible">
            <div className="sidebar-content">
              <div className="user">
                <div className="avatar-sm float-left mr-2">
                  <img
                    src={userData?.profile_photo_url}
                    alt="..."
                    className="avatar-img rounded-circle"
                  />
                </div>
                <div className="info">
                  <a
                    data-toggle="collapse"
                    href="#collapseExample"
                    aria-expanded="true"
                  >
                    <span
                      className="mb-0 text-bold"
                      style={{ fontWeight: "bold" }}
                    >
                      {userData?.name}
                    </span>
                    <span style={{ fontSize: 14 }}>
                      {userData?.role?.role_name}
                    </span>
                  </a>
                </div>
              </div>

              {/* fix sidebar not scrollable */}
              <div className="h-[80vh] overflow-y-scroll scrollbar-hide">
                <Sidebar collapse={collapsed} />
              </div>
            </div>
          </div>
        </div>
      </div>
      <div className="main-panel">
        <div className="content">
          <div className="page-inner pt-20 mb-20">
            <div className="mb-2 w-full text-right">
              {" "}
              <Breadcrumb separator={<i className="flaticon-right-arrow"></i>}>
                <Breadcrumb.Item href="/dashboard">
                  <i className="flaticon-home"></i>
                </Breadcrumb.Item>
                {breadcrumbs && breadcrumbs.length > 0
                  ? breadcrumbs.map((breadcrumb, index) => {
                      return (
                        <Breadcrumb.Item key={index}>
                          {breadcrumb.title}
                        </Breadcrumb.Item>
                      )
                    })
                  : pathList.map((value, index, array) => {
                      let formattedValue = value
                        .replace("transaction", "Transaksi")
                        .replace("waiting-payment", "Menunggu Pembayaran")
                        .replace("product-management", "Manajemen Produk")
                        .replace("product-variant", "Produk Varian")
                        .replace("product", "Produk Master")
                        .replace("point", "Poin")
                        .replace("online-logistic", "Metode Pengiriman")
                        .replace("payment-method", "Metode Pembayaran")
                        .replace("notification-template", "Template Notifikasi")
                        .split("-")
                        .map(
                          (word) => word.charAt(0).toUpperCase() + word.slice(1)
                        )
                        .join(" ")

                      let lastIndex = index === array.length - 1

                      if (lastIndex && lastItemLabel) {
                        return (
                          <Breadcrumb.Item key={index}>
                            {getStatusTransaction(lastItemLabel)}
                          </Breadcrumb.Item>
                        )
                      } else {
                        return (
                          <Breadcrumb.Item key={index}>
                            {getStatusTransaction(formattedValue)}
                          </Breadcrumb.Item>
                        )
                      }
                    })}
              </Breadcrumb>
            </div>
            <div
              className="card"
              style={{
                position: "sticky",
                top: 100,
                zIndex: 10,
                background: "white",
                boxShadow: "0px 4px 6px rgba(0, 0, 0, 0.1)",
              }}
            >
              <div className="card-body">
                <div className="flex justify-content-between align-items-center">
                  {/* Title Section - Left */}
                  <h4 className="card-title text-capitalize mb-0">
                    <a href={href} onClick={onClick}>
                      <span>
                        {/* <i className="fas fa-arrow-left mr-3"></i> */}
                        {title}
                      </span>
                    </a>
                  </h4>

                  {/* Breadcrumb Section - Right */}
                  <div className="d-flex align-items-center">
                    {rightContent && (
                      <span className="mr-3">{rightContent}</span>
                    )}
                  </div>
                </div>
              </div>
            </div>
            {children}
          </div>
        </div>

        <footer className="bg-white py-6 sticky top-[100vh] w-full z-10">
          <div className="container-fluid ">
            <div className="copyright  text-right">
              AIMI FIS - BY IT DIVISION 2022
            </div>
          </div>
        </footer>
      </div>
    </div>
  )
}

export default Layout
