import axios from "axios"
import { toast } from "react-toastify"

export const loadPackages = (callback) => {
  axios.get("/api/master/package").then((res) => {
    const { data } = res.data
    callback(data)
  })
}

export const loadSku = (callback) => {
  axios.get("/api/master/sku").then((res) => {
    const { data } = res.data
    callback(data)
  })
}

export const loadVariant = (callback) => {
  axios.get("/api/master/variant").then((res) => {
    const { data } = res.data
    callback(data)
  })
}

export const loadProductMaster = (callback, setLoading) => {
  if (setLoading) {
    setLoading(true)
  }
  axios
    .get("/api/master/product-lists")
    .then((res) => {
      const { data } = res.data
      callback(data)
    })
    .finally(() => {
      if (setLoading) {
        setLoading(false)
      }
    })
}
export const loadProductVariant = (callback) => {
  axios.get("/api/master/product-variant-lists").then((res) => {
    const { data } = res.data
    callback(data)
  })
}

export const loadDetailProduct = (product_variant_id, callback) => {
  const id = product_variant_id ? `/${product_variant_id}` : ""
  axios
    .get(`/api/product-management/product-variant/detail${id}`)
    .then((res) => {
      callback(res.data.data)
    })
}

export const deleteProductVariantBundling = (bundling_id, callback) => {
  axios
    .post(
      `/api/product-management/product-variant-bundling/delete/${bundling_id}`,
      {
        _method: "DELETE",
      }
    )
    .then((res) => {
      toast.success("Product berhasil dihapus")
      callback()
    })
    .catch((err) => {
      toast.error("Product gagal dihapus")
    })
}
