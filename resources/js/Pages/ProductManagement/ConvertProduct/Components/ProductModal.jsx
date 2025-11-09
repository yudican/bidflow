import {
  CheckOutlined,
  CloseCircleFilled,
  SearchOutlined,
} from "@ant-design/icons";
import { Input, message, Modal, Pagination } from "antd";
import React, { useState } from "react";
const ProductModal = ({
  handleSelect,
  selectedProduct,
  form,
  paramsData = {},
}) => {
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [loading, setLoading] = useState(false);
  const [datas, setDatas] = useState([]);
  const [total, setTotal] = useState(0);
  const [search, setSearch] = useState("");
  const [isSearch, setIsSearch] = useState(false);
  const [currentPage, setCurrentPage] = useState(1);

  const loadData = (
    url = "/api/product-management/product-variant",
    perpage = 10,
    params = { page: currentPage }
  ) => {
    setLoading(true);
    axios
      .post(url, { perpage, ...params, ...paramsData })
      .then((res) => {
        const { data, total, current_page } = res.data.data;
        setTotal(total);
        setCurrentPage(current_page);
        const newData = data.map((item) => {
          return {
            id: item.id,
            name: item.name,
            product_image: item?.image_url,
            final_price: item.final_price,
          };
        });

        setDatas(newData);
        setLoading(false);
      })
      .catch((e) => setLoading(false));
  };

  const handleChange = (page, pageSize = 10) => {
    loadData(
      `/api/product-management/product-variant/?page=${page}`,
      pageSize,
      {
        search,
        page,
      }
    );
  };

  const handleChangeSearch = () => {
    setIsSearch(true);
    loadData(`/api/product-management/product-variant`, 10, { search });
  };

  const showModal = () => {
    if (!paramsData?.role_id) {
      return message.error("Pilih role terlebih dahulu");
    }
    setIsModalOpen(true);
    loadData();
  };

  return (
    <div>
      <Input.Search
        placeholder="Pilih Product"
        onSearch={() => showModal()}
        value={selectedProduct?.name}
        readOnly
      />

      <Modal
        title="Pilih Product Variant"
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
                      loadData();
                      setSearch(null);
                      setIsSearch(false);
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
                      {item.name}
                    </td>
                    <td
                      className="ant-table-row ant-table-row-level-0"
                      width={"5%"}
                    >
                      <button
                        onClick={() => {
                          handleSelect(item);
                          form.setFieldsValue({
                            product_variant_id: item.id,
                            basic_price: item.final_price,
                          });
                          console.log(item, "test");
                          setIsModalOpen(false);
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
  );
};

export default ProductModal;
