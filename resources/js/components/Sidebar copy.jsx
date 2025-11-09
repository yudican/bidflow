import axios from "axios"
import React, { useEffect, useState } from "react"
import ReactDOM from "react-dom/client"
import Skeleton from "react-loading-skeleton"
import "react-loading-skeleton/dist/skeleton.css"

const Sidebar = (props) => {
  const [active, setActive] = useState(1)
  const [activeChildren, setActiveChildren] = useState(1)
  const [collapse, setCollapse] = useState(false)
  const [menus, setMenus] = useState([])
  const [loading, setLoading] = useState(false)

  const loadUserLogin = () => {
    setLoading(true)
    axios
      .get("/api/general/load-user")
      .then((res) => {
        const { data } = res.data
        setLoading(false)
        if (menus.length === 0) {
          localStorage.setItem("user_data", JSON.stringify(data))
          localStorage.setItem("menu_data", JSON.stringify(data?.menu_data))
          localStorage.setItem("role", data?.role?.role_type)
          localStorage.setItem("service_ginee_url", data?.service_ginee_url)
          setMenus(data?.menu_data || [])
        }
      })
      .catch(() => setLoading(false))
  }

  const loadSetting = () => {
    axios
      .post("/api/general/load-setting", { key: "REFRESH_MENU" })
      .then((res) => {
        const { data } = res.data
        if (data) {
          loadUserLogin()
          axios.post("/api/general/delete-setting", {
            key: "REFRESH_MENU",
          })
        }
      })
  }

  const menu_id = localStorage.getItem("menu_id") || 1
  const children_id = localStorage.getItem("children") || 1

  useEffect(() => {
    loadSetting()
    loadUserLogin()
  }, [])

  useEffect(() => {
    if (menu_id) {
      setActive(parseInt(menu_id))
    }
    if (children_id) {
      setActiveChildren(parseInt(children_id))
      setCollapse(true)
    }
    const menu = JSON.parse(localStorage.getItem("menu_data"))
    if (menu) {
      setMenus(menu)
    }
  }, [menu_id, children_id])

  if (loading) {
    return (
      <ul className="nav nav-primary">
        {[1, 2, 3, 4, 5, 6, 7, 8, 9, 10].map((item, index) => (
          <li key={item} className={`nav-item mb-2`}>
            <Skeleton height={40} />
          </li>
        ))}
      </ul>
    )
  }
  return (
    <ul className="nav nav-primary">
      {menus &&
        menus.map((menu) => {
          if (menu.children.length > 0) {
            return (
              <li
                // className={`nav-item ${
                //   active === menu.id ? "active" : ""
                // } submenu`}
                className={`nav-item ${
                  active === menu.id ? "hover:bg-blue-700" : ""
                } submenu`}
                key={menu.id}
              >
                <a
                  onClick={() => {
                    localStorage.setItem("menu_id", menu.id)
                    setActive(menu.id)
                    setCollapse(!collapse)
                  }}
                >
                  <i className={menu.menu_icon || "fas fa-layer-group"}></i>
                  {/* main menu with children label */}
                  <p>{menu.menu_label}</p>
                  <span className="caret"></span>
                </a>
                <div
                  className={`collapse ${
                    active === menu.id && collapse ? "show" : ""
                  }`}
                  id={`menu-${menu.id}`}
                >
                  <ul className="nav nav-collapse">
                    {menu.children.map((children) => {
                      if (children?.show_menu > 0) {
                        return (
                          <li
                            key={children.id}
                            // className={
                            //   activeChildren === children.id ? "active" : ""
                            // }
                            className={
                              window.location.href === children.menu_url
                                ? "active"
                                : ""
                            }
                          >
                            <a
                              href={children.menu_url}
                              onClick={() => {
                                window.location.href = children.menu_url

                                localStorage.setItem("children", children.id)
                                setActiveChildren(children.id)
                              }}
                            >
                              {/* sub menu label */}
                              <span>{children.menu_label}</span>
                              {children.badge && (
                                <span className="badge badge-info">
                                  {children?.badge_count}
                                </span>
                              )}
                            </a>
                          </li>
                        )
                      }
                    })}
                  </ul>
                </div>
              </li>
            )
          }
          if (menu?.show_menu > 0) {
            return (
              <li
                key={menu.id}
                // className={`nav-item ${active === menu.id ? "active" : ""}`}
                className={`nav-item ${
                  window.location.href === menu.menu_url ? "active" : ""
                }`}
              >
                <a
                  href={menu.menu_url}
                  onClick={() => {
                    localStorage.setItem("menu_id", menu.id)
                    setActive(menu.id)
                    window.location.href = menu.menu_url
                  }}
                >
                  <i className={menu.menu_icon || "fas fa-layer-group"}></i>
                  <p>{menu.menu_label}</p>
                  {menu.badge && (
                    <span className="badge badge-info">
                      {menu?.badge_count}
                    </span>
                  )}
                  {/* <span className="badge badge-count">4</span> */}
                </a>
              </li>
            )
          }
        })}
    </ul>
  )
}

const sidebarRoot = ReactDOM.createRoot(
  document.getElementById("sidebar-react")
)
sidebarRoot.render(
  <React.StrictMode>
    <Sidebar />
  </React.StrictMode>
)
