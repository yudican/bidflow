import { createApi, fetchBaseQuery } from "@reduxjs/toolkit/query/react"
const currentUrl = new URL(window.location.href)
export const authService = createApi({
  reducerPath: "authService",
  baseQuery: fetchBaseQuery({ baseUrl: currentUrl?.origin }),
  endpoints: (builder) => ({
    login: builder.mutation({
      query: (body) => {
        console.log(body)
        return {
          url: "/auth/login",
          method: "POST",
          body,
        }
      },
    }),

    register: builder.mutation({
      query: (body) => ({
        url: "/auth/register",
        method: "POST",
        body,
      }),
    }),

    forgotPassword: builder.mutation({
      query: (body) => ({
        url: "/auth/forgot-password",
        method: "POST",
        body,
      }),
    }),
    resetPassword: builder.mutation({
      query: (body) => ({
        url: "/auth/reset-password",
        method: "POST",
        body,
      }),
    }),
    // Add other endpoints here if needed
  }),
})

export const {
  useLoginMutation,
  useRegisterMutation,
  useForgotPasswordMutation,
  useResetPasswordMutation,
} = authService
