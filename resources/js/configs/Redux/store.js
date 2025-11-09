// store.js
import { configureStore } from "@reduxjs/toolkit"
import { setupListeners } from "@reduxjs/toolkit/query/react"
import generalReducer from "./Reducers/generalReducer"
import userReducer from "./Reducers/userReducer"

import { pokemonService } from "./Services/pokemonService"
import { authService } from "./Services/authService"
import { salesOrderService } from "./Services/salesOrderService"
import { generalService } from "./Services/generalServices"

export const store = configureStore({
  reducer: {
    general: generalReducer,
    user: userReducer,
    // Add your other reducers here if you have any
    [pokemonService.reducerPath]: pokemonService.reducer,
    [authService.reducerPath]: authService.reducer,
    [salesOrderService.reducerPath]: salesOrderService.reducer,
    [generalService.reducerPath]: generalService.reducer,
  },
  middleware: (getDefaultMiddleware) =>
    getDefaultMiddleware().concat(
      pokemonService.middleware,
      authService.middleware,
      salesOrderService.middleware,
      generalService.middleware
    ),
})

setupListeners(store.dispatch)

export default store
