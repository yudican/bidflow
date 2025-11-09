import { Badge, Menu } from "antd"
import Sider from "antd/lib/layout/Sider"
import React, { useEffect } from "react"
import Skeleton from "react-loading-skeleton"
import "react-loading-skeleton/dist/skeleton.css"
import { useNavigate } from "react-router-dom"
import { useGetSidebarMenuQuery } from "../configs/Redux/Services/generalServices"

function getItem(label, key, url, icon, children, badge) {
  return {
    key,
    icon,
    badge,
    children,
    label,
    url,
  }
}

const Sidebar = ({ collapse = false }) => {
  const navigate = useNavigate()
  const localMenus = localStorage.getItem("menus")
    ? JSON.parse(localStorage.getItem("menus"))
    : []
  const { data: menuData, isLoading } = useGetSidebarMenuQuery()
  const menus = menuData || localMenus
  const itemsSidebar = menus?.map((value) => {
    return getItem(
      `${value.menu_label}`,
      `${value.id}`,
      `${value.menu_url}`,
      value.menu_icon,
      value?.children?.length > 0 &&
        value.children
          .map((value) => {
            if (value?.show_menu === "1") {
              return getItem(
                `${value.menu_label}`,
                `${value.id}`,
                `${value.menu_url}`,
                null,
                null,
                value.badge_count
              )
            } else {
              return null
            }
          })
          .filter((value) => value),
      value.badge_count
    )
  })
  // console.log("itemsSidebar: ", itemsSidebar)

  const currentUrl = new URL(window.location.href)
  const pathName = currentUrl?.pathname
  const parts = pathName?.split("/").filter(Boolean)
  const MainUrl = parts[0]
  const ChildUrl = parts[1] || ""
  const activeUrl = // pick value from sidebar items
    itemsSidebar &&
    itemsSidebar?.find((value) => {
      const labelSidebar = value.label.replace(/-/g, " ").toLowerCase()
      // console.log(labelSidebar, "label sidebar")
      const mainUrlString = MainUrl.replace(/-/g, " ").toLowerCase()
      // console.log(mainUrlString, "main url string")

      const includes =
        mainUrlString.split(" ")[0] ||
        mainUrlString.split(" ")[1] ||
        mainUrlString

      const includesNew = () => {
        if (labelSidebar === mainUrlString) {
          return mainUrlString
        } else if (labelSidebar.includes(mainUrlString.split(" ")[0])) {
          return mainUrlString.split(" ")[0] || mainUrlString.split(" ")[1]
        } else {
          return mainUrlString.split(" ")[1]
        }
      }
      return labelSidebar.includes(includesNew())
    })

  const activeUrlChildren =
    activeUrl?.children &&
    activeUrl?.children?.find((value) => {
      const labelSidebar = value.label.replace(/-/g, " ").toLowerCase()
      let childUrlString = ChildUrl.replace(/-/g, " ")
      let includes = null
      // let includes =
      //   childUrlString.split(" ")[1] ||
      //   childUrlString.split(" ")[0] ||
      //   childUrlString

      function isCamelCase(str) {
        // Check if the string has at least one uppercase letter
        return /[A-Z]/.test(str) && !/_/.test(str)
      }

      function camelToSnake(camelCase) {
        return camelCase.replace(/([A-Z])/g, "_$1").toLowerCase()
      }

      if (isCamelCase(childUrlString)) {
        childUrlString = camelToSnake(childUrlString).replace(/_/g, " ")
      }
      if (childUrlString.includes("case")) {
        includes = childUrlString
      } else {
        // old includes method for children
        // includes =
        //   childUrlString.split(" ")[0] ||
        //   childUrlString.split(" ")[1] ||
        //   childUrlString

        // new includes method for children
        if (labelSidebar === childUrlString) {
          includes = childUrlString
        } else if (labelSidebar.includes(childUrlString.split(" ")[0])) {
          includes =
            childUrlString.split(" ")[0] || childUrlString.split(" ")[1]
        } else {
          includes = childUrlString.split(" ")[1]
        }
      }

      return labelSidebar.includes(includes)
    })

  const ActiveSidebarId = activeUrlChildren?.key || activeUrl?.key
  const ActiveSidebarKeyPath =
    activeUrlChildren?.key === undefined
      ? [activeUrl?.key].toString()
      : [activeUrlChildren?.key, activeUrl?.key].toString()

  const activeSidebarId = localStorage.getItem("activeSidebarId")
  const activeSidebarKeyPath = [localStorage.getItem("activeSidebarKeyPath")]
  const activeSidebarOpenKeys = [localStorage.getItem("activeSidebarOpenKeys")]

  // useEffect(() => {
  //   if (menus && menus.length === 0) {
  //   } else {
  //     localStorage.setItem("activeSidebarId", ActiveSidebarId)
  //     localStorage.setItem("activeSidebarKeyPath", ActiveSidebarKeyPath)
  //   }
  // }, [menus])

  if (isLoading) {
    return (
      <ul className="nav nav-primary">
        {[1, 2, 3, 4, 5, 6, 7, 8, 9, 10].map((item, index) => (
          <li key={index} className={`nav-item mb-2`}>
            <Skeleton height={40} />
          </li>
        ))}
      </ul>
    )
  }

  return (
    <div className="nav nav-primary">
      <Sider width={"100%"}>
        <Menu
          theme="light"
          selectedKeys={activeSidebarId}
          defaultSelectedKeys={activeSidebarKeyPath}
          defaultOpenKeys={activeSidebarOpenKeys}
          mode="inline"
          onClick={({ key, keyPath }) => {
            localStorage.setItem("activeSidebarId", key)
            localStorage.setItem("activeSidebarKeyPath", keyPath)
            localStorage.setItem("activeSidebarOpenKeys", keyPath.slice(-1)[0])
          }}
          inlineCollapsed={collapse}
        >
          {itemsSidebar &&
            itemsSidebar.map((value) => {
              if (value?.children?.length > 0) {
                // if have children
                return (
                  <Menu.SubMenu
                    icon={
                      <i className={value?.icon || "fas fa-layer-group"}></i>
                    }
                    key={value?.key}
                    title={<span className="font-normal">{value?.label}</span>}
                  >
                    {value.children.map((children) => {
                      return (
                        <Menu.Item key={children?.key}>
                          <div className="flex justify-between items-center">
                            <a
                              className="font-normal"
                              href={children?.url}
                              onClick={(e) => {
                                e.preventDefault()
                                if (children?.url) {
                                  const url = children?.url?.replace(
                                    currentUrl?.origin,
                                    ""
                                  )
                                  navigate(url)
                                }
                              }}
                            >
                              {children?.label}
                            </a>
                            <Badge
                              showZero
                              key={children?.key}
                              count={children?.badge}
                            />
                          </div>
                        </Menu.Item>
                      )
                    })}
                  </Menu.SubMenu>
                )
              }
              // if no children
              return (
                <Menu.Item
                  key={value?.key}
                  icon={<i className={value?.icon || "fas fa-layer-group"}></i>}
                >
                  <div className="flex justify-between items-center">
                    <a
                      className="font-normal"
                      href={value?.url}
                      onClick={(e) => {
                        e.preventDefault()
                        if (value?.url) {
                          const url = value?.url?.replace(
                            currentUrl?.origin,
                            ""
                          )
                          navigate(url)
                        }
                      }}
                    >
                      {value?.label}
                    </a>
                    <Badge showZero key={value?.key} count={value?.badge} />
                  </div>
                </Menu.Item>
              )
            })}

          <div className="text-center text-[#D4D4D4] mt-4 text-sm font-light">
            <p>Version 3.5.0</p>
          </div>
        </Menu>
      </Sider>
    </div>
  )
}

export default Sidebar
