import {
  ClockCircleOutlined,
  CloseCircleFilled,
  DownOutlined,
  EyeOutlined,
  LoadingOutlined,
  RightOutlined,
  SearchOutlined,
} from "@ant-design/icons";
import {
  DatePicker,
  Dropdown,
  Input,
  Menu,
  Pagination,
  Table,
  message,
} from "antd";
import axios from "axios";
import React, { useState } from "react";
import { useNavigate } from "react-router-dom";
import { toast } from "react-toastify";
import Layout from "../../components/layout";
import { useGetSalesOrderInvoicesQuery } from "../../configs/Redux/Services/salesOrderService";
import { createQueryString, getItem, inArray } from "../../helpers";
import ModalTax from "../Genie/Components/ModalTax";
import OrderInvoiceFormModal from "./Components/OrderInvoiceFormModal";
import { productNeedListColumn } from "./config";

const { RangePicker } = DatePicker;

const getStatusItems = (status, record) => {
  switch (status) {
    default:
      return [
        {
          label: "Detail",
          key: "detail",
          icon: <EyeOutlined />,
          children: [
            {
              label: "Open Directly",
              key: "detail",
              icon: <EyeOutlined />,
            },
            {
              label: "Open In New Tab",
              key: "detail_new_tab",
              icon: <EyeOutlined />,
            },
          ],
        },
        {
          label: "Cancel Invoice",
          key: "cancel",
          icon: <CloseCircleFilled />,
          disabled: record?.gp_submit_number || record?.no_faktur,
        },
      ];
  }
};

const ActionMenu = ({ value, status = 1, callback }) => {
  const navigate = useNavigate();
  return (
    <Menu
      onClick={({ key }) => {
        switch (key) {
          case "detail":
            return navigate(
              `/order/invoice/detail/${value.uid_lead}/${value.uid_delivery}`
            );
          case "detail_new_tab":
            return window.open(
              `/order/invoice/detail/${value.uid_lead}/${value.uid_delivery}`
            );
          case "cancel":
            return axios
              .post(`/api/order/invoice/cancel/${value.id}`, {
                uid_invoice: value?.uid_invoice,
              })
              .then(() => {
                message.success("Data Invoice berhasil diatalkan!");
                return callback();
              });
        }
      }}
      itemIcon={<RightOutlined />}
      items={getStatusItems(status, value)}
    />
  );
};

const OrderInvoiceList = () => {
  const navigate = useNavigate();

  const [search, setSearch] = useState("");
  const [isSearch, setIsSearch] = useState(false);
  const [currentPage, setCurrentPage] = useState(1);
  const [perPage, setPerpage] = useState(10);
  const [filterData, setFilterData] = useState({});
  const [selectedRowKeys, setSelectedRowKeys] = useState([]);
  const [loadingSubmit, setLoadingSubmit] = useState(false);
  const [loadingSubmitPajak, setLoadingSubmitPajak] = useState(false);
  const [selectedProducts, setSelectedProducts] = useState([]);

  const [paramUrl, setParamUrl] = useState("/api/sales-order/invoice");
  const {
    data: salesOrderInvoicesData,
    isLoading: salesOrderInvoicesLoading,
    isFetching: salesOrderInvoicesFetching,
    refetch: refetchSalesOrderInvoice,
  } = useGetSalesOrderInvoicesQuery(paramUrl);

  const loadOrder = (
    url = "/api/sales-order/invoice",
    perpage = perPage,
    params = { page: currentPage }
  ) => {
    const bodyData = {
      perpage,
      account_id: parseInt(getItem("account_id")),
      ...params,
    };

    // if (params?.contact) {
    //   bodyData.contact = params?.contact?.value
    // }

    // if (params?.sales) {
    //   bodyData.sales = params?.sales?.value
    // }

    Object.keys(bodyData).forEach((value) => {
      if (Array.isArray(bodyData[value])) {
        bodyData[value] = bodyData[value].join(",");
      } else {
        bodyData[value] = bodyData[value];
      }
    });

    const cleanedData = Object.fromEntries(
      Object.entries(bodyData).filter(
        ([key, value]) => value !== null && value !== undefined
      )
    );

    const queryString = createQueryString(cleanedData);
    setParamUrl(`${url}${queryString}`);
  };

  const handleChange = (page, pageSize = 10) => {
    setCurrentPage(page);
    loadOrder(`/api/sales-order/invoice`, pageSize, {
      search,
      page,
      account_id: getItem("account_id"),
      ...filterData,
    });
  };

  const handleChangeSearch = () => {
    setIsSearch(true);
    loadOrder(`/api/sales-order/invoice`, 10, { search });
  };

  const handleFilter = (data) => {
    setFilterData(data);
    loadOrder(`/api/sales-order/invoice`, 10, data);
  };

  const handleSubmitGp = (value) => {
    setLoadingSubmit(true);
    const hasLocNode = selectedProducts.every((item) => item.loc_node);
    if (!hasLocNode) {
      toast.error("Lokasi Site ID harus diisi");
      return setLoadingSubmit(false);
    }
    axios
      .post(`/api/order/order-lead/submit`, {
        ids: selectedRowKeys,
        type: "invoice",
        ...value,
        products: selectedProducts,
      })
      .then((res) => {
        const { data } = res.data;
        toast.success("Data order berhasil di submit!");
        setSelectedRowKeys([]);
        setSelectedProducts([]);
        setLoadingSubmit(false);
        refetchSalesOrderInvoice();
      })
      .catch((e) => {
        setLoadingSubmit(false);
        toast.error("Error submitting order lead");
      });
  };

  const handleSubmitKlikPajak = () => {
    setLoadingSubmitPajak(true);

    axios
      .post(`/api/order/invoice/submit/bulk/klik-pajak`, {
        items: selectedRowKeys,
      })
      .then((res) => {
        const data = res.data;
        refetchSalesOrderInvoice();
        toast.success(
          data?.message ||
          data?.data?.message ||
          "Data invoice berhasil di submit!"
        );
        setSelectedRowKeys([]);
        setSelectedProducts([]);
        setLoadingSubmitPajak(false);
      })
      .catch((error) => {
        const data = error?.response || {};
        setLoadingSubmitPajak(false);
        toast.error(
          data?.message ||
          data?.data?.message ||
          "Error submitting invoice klik pajak "
        );
      });
  };

  const handleResetGp = () => {
    setLoadingSubmitPajak(true);

    axios
      .post(`/api/order/invoice/reset-gp`, {
        items: selectedRowKeys,
      })
      .then((res) => {
        const { data } = res.data;
        toast.success("Data gp invoice berhasil di reset!");
        setSelectedRowKeys([]);
        setSelectedProducts([]);
        setLoadingSubmitPajak(false);
        refetchSalesOrderInvoice();
      })
      .catch((e) => {
        setLoadingSubmitPajak(false);
        toast.error("Error reset gp invoice");
      });
  };

  // selected row handler
  const rowSelection = {
    selectedRowKeys,
    onChange: (e) => {
      setSelectedRowKeys(e);
      // const productData = [];
      // if (e.length > 0) {
      //   e.map((value) => {
      //     const item = orderLead.find((item) => item.id == value);
      //     const deliveries = item?.deliveries || [];
      //     const products = deliveries
      //       ?.filter((rowItem) => rowItem.is_invoice == 1)
      //       ?.filter((val) => !val.gp_submit_number)
      //       .map((row, index) => {
      //         return {
      //           key: index,
      //           id: row.id,
      //           so_id: row.id,
      //           uid_lead: row.uid_lead,
      //           product_name: row.product_name,
      //           product_id: row?.product_need?.product_id || row?.product_id,
      //           sku: row?.sku,
      //           gp_submit_number: row.gp_submit_number,
      //           submit_klikpajak: row.submit_klikpajak,
      //           invoice_number: row.invoice_number,
      //         };
      //       });
      //     productData.push(...products);
      //   });
      // }

      // setSelectedProducts(productData);
    },
    getCheckboxProps: (record) => {
      // if (record?.billings?.length < 1) {
      //   return {
      //     disabled: true,
      //   }
      // }
      // const isInvoiceProduct = record?.product_needs?.filter(
      //   (item) => item.is_invoice == 1
      // )
      // if (isInvoiceProduct && isInvoiceProduct.length < 1) {
      //   return {
      //     disabled: true,
      //   }
      // }

      // if (record.status == "New") {
      //   return {
      //     disabled: true,
      //   }
      // }

      // if (record.submit_klikpajak === "submitted") {
      //   return {
      //     disabled: true,
      //   };
      // }

      return {
        disabled: false,
      };
    },
  };

  const handleChangeProduct = (e, index, field) => {
    if (field === "products") {
      setSelectedProducts(e);
    } else {
      const data = [...selectedProducts];
      // old mechanism (select each index for loc_node change)
      // data[index].loc_node = e

      // new mechanism (mapping all index for loc_node change)
      data.map((item, i) => (data[i][field] = e));
      setSelectedProducts(data);
    }
  };

  const menu = (
    <Menu>
      <Menu.Item>
        <a onClick={() => navigate("/order/invoice/history-submit")}>
          <ClockCircleOutlined />
          <span className="mb-0 ml-2">History Submit Pajak</span>
        </a>
      </Menu.Item>
      {selectedRowKeys.length > 0 && (
        <>
          <Menu.Item>
            <ModalTax
              handleSubmit={(e) => handleSubmitGp(e)}
              // products={selectedProducts}
              onChange={handleChangeProduct}
              type="so"
              titleModal={"Konfirmasi Submit"}
              title="Submit SI to GP"
              orderIds={selectedRowKeys}
            />
          </Menu.Item>

          <Menu.Item>
            {loadingSubmitPajak ? (
              <button disabled>
                {true}
                <span className="cursor-not-allowed">
                  <LoadingOutlined />
                  <span className="mb-0 ml-2">Submit Klik Pajak</span>
                </span>
              </button>
            ) : (
              <ModalTax
                handleSubmit={(e) => handleSubmitKlikPajak(e)}
                products={selectedProducts}
                onChange={() => { }}
                type="klik-pajak"
                titleModal={"Konfirmasi Submit"}
                title="Submit Klik Pajak"
              />
            )}
          </Menu.Item>
          <Menu.Item>
            <ModalTax
              handleSubmit={(e) => handleResetGp(e)}
              products={selectedProducts}
              onChange={() => { }}
              type="reset-gp"
              titleModal={"Konfirmasi Submit"}
              title="Reset SI GP"
            />
          </Menu.Item>
        </>
      )}

      {/* <Menu.Item>
        <ImportModal handleOk={handleFilter} />
      </Menu.Item> */}
    </Menu>
  );

  const show = !inArray(getItem("role"), ["adminwarehouse"]);

  const rightContent = (
    <div className="flex justify-between items-center">
      {/* <button
        onClick={() => {
          if (readyToSubmit) {
            setSelectedRowKeys([])
            return setReadyToSubmit(false)
          }
          return setReadyToSubmit(true)
        }}
        className={`text-white bg-${
          !readyToSubmit ? "blue" : "red"
        }-700 hover:bg-${
          !readyToSubmit ? "blue" : "red"
        }-800 focus:ring-4 focus:outline-none focus:ring-${
          !readyToSubmit ? "blue" : "red"
        }-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mr-2`}
      >
        <span className="ml-2">
          {readyToSubmit ? "Cancel Submit" : "Ready To Submit"}
        </span>
      </button> */}

      {/* <FilterModal
        handleOk={handleFilter}
        isFiltered={filterData?.sales?.value}
      /> */}
      {/* <button
        className="ml-3 text-white bg-green-800 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mr-2"
        onClick={() => handleExportContent()}
      >
        {loadingExport ? <LoadingOutlined /> : <FileExcelOutlined />}
        <span className="ml-2">Export</span>
      </button> */}
      {show && (
        <Dropdown overlay={menu}>
          <button
            className="text-blue-700 border-[1px] border-blue-700 hover:bg-blue-800/10 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mx-2"
            onClick={(e) => e.preventDefault()}
          >
            <span className="mr-2">More Option</span>
            {loadingSubmitPajak || loadingSubmit ? (
              <LoadingOutlined />
            ) : (
              <DownOutlined />
            )}
          </button>
        </Dropdown>
      )}
      {show && (
        <OrderInvoiceFormModal handleOk={() => refetchSalesOrderInvoice()} />
      )}
    </div>
  );

  return (
    <Layout rightContent={rightContent} title="List Sales Invoice">
      <div className="card">
        <div className="card-body">
          <div className="row mb-4">
            <div className="col-md-12"></div>
            <div className="col-md-4 col-sm-6 col-12">
              <Input
                placeholder="Cari disini"
                size={"large"}
                className="rounded"
                onPressEnter={() => handleChangeSearch()}
                suffix={
                  isSearch ? (
                    <CloseCircleFilled
                      onClick={() => {
                        refetchSalesOrderInvoice();
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
            <div className="col-md-8">
              <div className="float-right text-right">
                <strong className="text-red-400">
                  Total Data: {salesOrderInvoicesData?.data?.total}
                </strong>
              </div>
            </div>
          </div>

          <Table
            dataSource={salesOrderInvoicesData?.data?.data || []}
            columns={[
              ...productNeedListColumn,
              {
                title: "Action",
                dataIndex: "action",
                key: "action",
                align: "center",
                fixed: "right",
                render: (text, record) => {
                  return (
                    <Dropdown.Button
                      style={{ marginRight: 32 }}
                      overlay={
                        <ActionMenu
                          value={{
                            uid_lead: record?.uid_lead,
                            uid_invoice: record?.uid_invoice,
                            uid_delivery: record?.uid_delivery,
                            gp_submit_number: record?.gp_submit_number,
                            no_faktur: record?.no_faktur,
                            id: record?.id,
                          }}
                          status={record?.status}
                          callback={() => refetchSalesOrderInvoice()}
                        />
                      }
                    ></Dropdown.Button>
                  );
                },
              },
            ]}
            loading={salesOrderInvoicesLoading || salesOrderInvoicesFetching}
            pagination={false}
            rowKey="id"
            scroll={{ x: "max-content" }}
            tableLayout={"auto"}
            rowSelection={rowSelection}
          />
          <Pagination
            defaultCurrent={1}
            current={currentPage}
            total={salesOrderInvoicesData?.data?.total}
            className="mt-4 text-center"
            onChange={handleChange}
            pageSizeOptions={["10", "20", "50", "100", "200", "500"]}
            onShowSizeChange={(current, size) => {
              setCurrentPage(current);
              setPerpage(size);
            }}
          />
        </div>
      </div>
    </Layout>
  );
};

export default OrderInvoiceList;
