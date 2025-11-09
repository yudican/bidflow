import {
  EyeInvisibleOutlined,
  EyeTwoTone,
  LoadingOutlined,
} from "@ant-design/icons"
import { Button, Form, Input } from "antd"
import axios from "axios"
import React, { useState } from "react"
import { useNavigate } from "react-router-dom"
import { toast } from "react-toastify"
import { inArray } from "../../helpers"
import { ReactComponent as BgAuth } from "../../Assets/BgAuth.svg"

const Login = () => {
  const [form] = Form.useForm()
  const [loading, setLoading] = useState(false)
  const navigate = useNavigate()

  const onFinish = (values) => {
    setLoading(true)
    axios
      .post("/api/proccess/login", values)
      .then((res) => {
        const { data } = res
        const { message, token, redirect, user } = data
        setLoading(false)
        toast.success(message, {
          position: toast.POSITION.TOP_RIGHT,
        })

        loadUserLogin()

        inArray(user.role, ["sales", "adminsales"]) &&
          localStorage.setItem("is_filtered_admin_sales", true)
        localStorage.setItem("token", token)

        localStorage.removeItem("user_data")
        localStorage.removeItem("menus")
        localStorage.removeItem("menu_data")
        localStorage.removeItem("role")
        localStorage.removeItem("service_ginee_url")

        setTimeout(() => {
          return (window.location.href = redirect)
        }, 2500)
      })
      .catch((err) => {
        const { message } = err.response.data
        setLoading(false)
        toast.error(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
      })
  }
  const loadUserLogin = () => {
    axios.get("/api/general/load-user").then((res) => {
      const { data } = res.data
      localStorage.setItem("account_id", data.company_id)
      localStorage.setItem("user_data", JSON.stringify(data))
      localStorage.setItem("role", data?.role?.role_type)
      localStorage.setItem("service_ginee_url", data?.service_ginee_url)
      // navigate("/dashboard")
    })
  }

  return (
    <div className="bg-white font-sans">
      <div className="grid lg:grid-cols-2 gap-10 my-auto">
        {/* background container */}
        <div className="w-full h-screen lg:flex hidden justify-center items-center">
          <BgAuth className="w-3/4 h-3/4" />
        </div>

        {/* form container */}
        <div
          className="
            lg:my-auto
            my-32
            w-full
            px-[20%]
          "
        >
          <div className="mb-4">
            <h4 className="pt-4 mb-2 text-xl font-normal">Silakan Masuk </h4>
          </div>
          <Form
            onKeyUp={() => {
              // Enter
              if (event.keyCode === 13) {
                form.submit()
              }
            }}
            form={form}
            name="basic"
            layout="vertical"
            onFinish={onFinish}
            autoComplete="off"
            className="px-8 pt-6 pb-8 mb-4 bg-white rounded"
          >
            <div className="mb-4">
              <Form.Item
                label="Email Address"
                name="email"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Email!",
                  },
                ]}
              >
                <Input
                  id="email"
                  type="email"
                  placeholder="Enter Email Address..."
                />
              </Form.Item>
            </div>
            <div className="mb-6 relative z-0">
              <div className="absolute right-0 z-10">
                <a
                  tabIndex={"-1"}
                  className="link text-blue-500 align-baseline hover:text-blue-800 cursor-pointer"
                  href="/forgot-password"
                >
                  Lupa kata sandi?
                </a>
              </div>
              <Form.Item
                label="Password"
                name="password"
                rules={[
                  {
                    required: true,
                    message: "Silakan masukkan Password!",
                  },
                ]}
              >
                <Input.Password
                  id="password"
                  type="password"
                  placeholder="Enter Password..."
                  iconRender={(visible) =>
                    visible ? <EyeTwoTone /> : <EyeInvisibleOutlined />
                  }
                />
              </Form.Item>
            </div>
            <div className="my-6 text-center">
              <Button
                style={{
                  backgroundColor: "#01bfff",
                  borderColor: "#01bfff",
                  color: "white",
                  width: "100%",
                }}
                type="submit"
                onClick={() => form.submit()}
              >
                {loading && <LoadingOutlined />}
                Masuk
              </Button>
            </div>
          </Form>
          <hr className="mb-6 border-t" />
          <div className="text-center">
            <span>Anda belum memiliki akun? </span>
            <a
              className="inline-block text-sm text-blue-500 align-baseline hover:text-blue-800"
              href="/register"
            >
              Daftar disini
            </a>
          </div>
          <div className="text-center text-[#D4D4D4] mt-4 text-sm font-light">
            <p>Version 3.5.0</p>
          </div>
        </div>
      </div>
    </div>
  )
}

export default Login
