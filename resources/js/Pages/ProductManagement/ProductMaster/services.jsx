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
