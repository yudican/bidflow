import {
  CheckOutlined,
  CloseCircleFilled,
  SearchOutlined,
} from "@ant-design/icons"
import { Input, Modal, Pagination } from "antd"
import React, { useEffect, useState } from "react"
const POModal = ({ handleSelect, selectedProduct, form }) => {
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [loading, setLoading] = useState(false)
  const [datas, setDatas] = useState([])
  const [total, setTotal] = useState(0)
  const [search, setSearch] = useState("")
  const [isSearch, setIsSearch] = useState(false)
  const [currentPage, setCurrentPage] = useState(1)

  const loadData = (
    url = "/api/purchase/purchase-order",
    perpage = 10,
    params = { page: currentPage }
  ) => {
    setLoading(true)
    axios
      .post(url, { perpage, ...params })
      .then((res) => {
        const { data, total, current_page } = res.data.data
        setTotal(total)
        setCurrentPage(current_page)

        setDatas(data)
        setLoading(false)
      })
      .catch((e) => setLoading(false))
  }

  const handleChange = (page, pageSize = 10) => {
    loadData(`/api/purchase/purchase-order/?page=${page}`, pageSize, {
      search,
      page,
    })
  }

  const handleChangeSearch = () => {
    setIsSearch(true)
    loadData(`/api/purchase/purchase-order`, 10, { search })
  }

  const showModal = () => {
    setIsModalOpen(true)
    loadData()
  }

  return (
    <div>
      <Input.Search
        placeholder="Pilih PO Number"
        onSearch={() => showModal()}
        value={selectedProduct?.po_number}
        readOnly
      />

      <Modal
        title="Pilih  PO Number"
        open={isModalOpen}
        cancelText={"Batal"}
        onCancel={() => setIsModalOpen(false)}
        okButtonProps={{ style: { display: "none" } }}
        width={1000}
      >
        <div className="row mb-4">
          <div className="col-md-6">
            <Input
              placeholder="Cari disini"
              size={"large"}
              className="rounded"
              onPressEnter={() => handleChangeSearch()}
              suffix={
                isSearch ? (
                  <CloseCircleFilled
                    onClick={() => {
                      loadData()
                      setSearch(null)
                      setIsSearch(false)
                    }}
                  />
                ) : (
                  <SearchOutlined onClick={() => handleChangeSearch()} />
                )
              }
              value={search}
              onChange={(e) => setSearch(e.target.value)}
            />
          </div>
        </div>

        <div className="ant-table-content">
          <table width={"100%"}>
            <thead className="ant-table-thead" style={{ tableLayout: "auto" }}>
              <tr>
                <th className="ant-table-cell">No.</th>
                <th className="ant-table-cell">Nama Product</th>
                <th className="ant-table-cell">#</th>
              </tr>
            </thead>
            {loading ? (
              <tbody className="ant-table-tbody">
                <tr>
                  <td colSpan={3} className="text-center">
                    Loading...
                  </td>
                </tr>
              </tbody>
            ) : (
              <tbody className="ant-table-tbody">
                {datas.map((item, index) => (
                  <tr key={item.id}>
                    <td className="ant-table-row ant-table-row-level-0">
                      {index + 1}
                    </td>
                    <td className="ant-table-row ant-table-row-level-0">
                      {item.po_number}
                    </td>
                    <td
                      className="ant-table-row ant-table-row-level-0"
                      width={"5%"}
                    >
                      {item.qty_not_allocated < 1 ? (
                        <button
                          className={`text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center`}
                        >
                          <CheckOutlined />
                          <span className="ml-2">Pilih</span>
                        </button>
                      ) : (
                        <button
                          onClick={() => {
                            handleSelect(item)
                            form.setFieldsValue({ product_id: item.id })
                            setIsModalOpen(false)
                          }}
                          className={`text-white bg-${
                            selectedProduct?.id === item.id ? "green" : "blue"
                          }-700 hover:bg-${
                            selectedProduct?.id === item.id ? "green" : "blue"
                          }-800 focus:ring-4 focus:outline-none focus:ring-${
                            selectedProduct?.id === item.id ? "green" : "blue"
                          }-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center`}
                        >
                          <CheckOutlined />
                          <span className="ml-2">Pilih</span>
                        </button>
                      )}
                    </td>
                  </tr>
                ))}
              </tbody>
            )}
          </table>
        </div>

        <Pagination
          defaultCurrent={1}
          current={currentPage}
          total={total}
          className="mt-4 text-center"
          onChange={(e) => handleChange(e)}
        />
      </Modal>
    </div>
  )
}

export default POModal
