import {
    CloseCircleFilled,
    CloseOutlined,
    DownloadOutlined,
    DownOutlined,
    FolderOpenOutlined,
    HistoryOutlined,
    PlusOutlined,
    RightOutlined,
    SearchOutlined,
} from "@ant-design/icons"
import { Dropdown, Input, Menu, Pagination, Popconfirm, Table } from "antd"
import React, { useEffect, useState } from "react"
import { useNavigate } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../../components/layout"
import { getItem, inArray } from "../../../helpers"
import ModalTax from "../../Genie/Components/ModalTax"
import ImportModal from "./../Components/ImportModal"
import FilterModalProduct from "./../Components/FilterModalProduct"
import ProgressImportData from "../../../components/ProgressImportData"
import {
    inventoryTransferStockColumns,
    inventoryStockColumns,
    inventoryStockAdjustmentColumns
} from "./../config"
import axios from "axios"

const StockAdjustment = ({ type = "adjustment" }) => {
    const navigate = useNavigate()

    // state
    const [inventoryData, setInventoryData] = useState([])
    const [loading, setLoading] = useState(false)
    const [loadingCreate, setLoadingCreate] = useState(false)
    const [total, setTotal] = useState(0)
    const [search, setSearch] = useState("")
    const [isSearch, setIsSearch] = useState(false)
    const [currentPage, setCurrentPage] = useState(1)
    const [filterData, setFilterData] = useState({})
    const [loadingExport, setLoadingExport] = useState(false)
    const [selectedRowKeys, setSelectedRowKeys] = useState([])
    const [selectedProducts, setSelectedProducts] = useState([])
    const [loadingSubmit, setLoadingSubmit] = useState(false)
    const [progressData, setProgressData] = useState(null)
    const perpage = 10;
    const loadInventoryData = (
        url = "/api/inventory/product/stock",
        perpage = 10,
        params = {}
    ) => {
        setLoading(true)
        axios
            .post(url, {
                perpage,
                account_id: getItem("account_id"),
                ...params,
                inventory_type: type,
            })
            .then((res) => {
                const { data, total, current_page } = res.data.data
                setTotal(total)
                setCurrentPage(current_page)
                const newData = data.map((item) => {
                    return {
                        ...item,
                        received_by_name: item?.selected_po?.received_by_name ?? "-",
                        order_number: item?.order_transfer?.order_number ?? "-",
                    }
                })

                setInventoryData(newData)
                setLoading(false)
            })
    }

    const handleChange = (page, pageSize = 10) => {
        loadInventoryData(`/api/inventory/product/stock/?page=${page}`, pageSize, {
            search,
            ...filterData,
        })
    }

    const handleChangeSearch = () => {
        setIsSearch(true)
        loadInventoryData(`/api/inventory/product/stock`, 10, { search })
    }

    const handleFilter = (data) => {
        setFilterData(data)
        loadInventoryData(`/api/inventory/product/stock`, 10, data)
    }

    const handleExportContentTransfer = () => {
        setLoadingExport(true)
        axios
            .post(`/api/inventory/product/stock/export_transfer`, {
                account_id: getItem("account_id"),
                inventory_type: type,
            })
            .then((res) => {
                const { data } = res.data
                window.open(data)
                setLoadingExport(false)
            })
            .catch((e) => setLoadingExport(false))
    }

    const handleExportContent = () => {
        setLoadingExport(true)
        axios
            .post(`/api/inventory/product/stock/export_received`)
            .then((res) => {
                const { data } = res.data
                window.open(data)
                setLoadingExport(false)
            })
            .catch((e) => setLoadingExport(false))
    }

    useEffect(() => {
        loadInventoryData()
    }, [])

    const cancelInventory = (id) => {
        setLoading(true)
        axios.post(`/api/inventory/product/stock/cancel/${id}`).then((res) => {
            loadInventoryData()
            toast.success("Data berhasil disimpan", {
                position: toast.POSITION.TOP_RIGHT,
            })
            setLoading(false)
        })
    }

    const cancelInventoryTransfer = (id) => {
        setLoading(true)
        axios
            .post(`/api/inventory/product/transfer/cancel/${id}`, {})
            .then((res) => {
                loadInventoryData()
                toast.success("Transfer Berhasil Dibatalkan", {
                    position: toast.POSITION.TOP_RIGHT,
                })
                setLoading(false)
            })
            .catch(() => {
                setLoading(true)
                toast.error("Transfer Gagal Dibatalkan", {
                    position: toast.POSITION.TOP_RIGHT,
                })
            })
    }

    const cancelInventoryTransferKonsinyasi = (id) => {
        setLoading(true)
        axios
            .post(`/api/inventory/product/konsinyasi/cancel/${id}`, {})
            .then((res) => {
                loadInventoryData()
                toast.success("Transfer Berhasil Dibatalkan", {
                    position: toast.POSITION.TOP_RIGHT,
                })
                setLoading(false)
            })
            .catch(() => {
                setLoading(true)
                toast.error("Transfer Gagal Dibatalkan", {
                    position: toast.POSITION.TOP_RIGHT,
                })
            })
    }

    // selected row handler
    const rowSelection = {
        selectedRowKeys,
        onChange: (e) => {
            setSelectedRowKeys(e)
            const productData = []
            if (e.length > 0) {
                e.map((value) => {
                    const item = inventoryData.find((item) => item.id == value)
                    if (item) {
                        const newItem = {
                            item_id: item.id,
                            transfer_id: item.id,
                            product_name: item.product_name,
                            warehouse_name: item.warehouse_name,
                            warehouse_destination_name: item.warehouse_destination_name,
                        }
                        productData.push(newItem)
                    }
                })
            }

            return setSelectedProducts(productData)
        },
        getCheckboxProps: (record) => {
            if (record.status == "New") {
                return {
                    disabled: true,
                }
            }

            if (record.status_submit === "submited") {
                return {
                    disabled: true,
                }
            }

            return {
                disabled: false,
            }
        },
    }

    const handleSubmitGp = (value) => {
        setLoadingSubmit(true)
        axios
            .post(`/api/inventory/transfer/submit`, {
                ids: selectedRowKeys,
                type: "inventory-transfer",
                ...value,
                products: selectedProducts,
            })
            .then((res) => {
                toast.success("Purchase Order berhasil di submit")
                setSelectedRowKeys([])
                setSelectedProducts([])
                setLoadingSubmit(false)
            })
            .catch((e) => {
                const { message } = e.response?.data
                setLoadingSubmit(false)
                toast.error(message || "Error submitting purchase order")
            })
    }

    const show =
        !inArray(getItem("role"), ["leadwh", "leadsales"]) &&
        inArray(type, ["transfer", "konsinyasi", "adjustment"])

    const menu = (
        <Menu>
            {type !== "konsinyasi" && (
                <>
                    <Menu.Item>
                        <ModalTax
                            handleSubmit={(e) => handleSubmitGp(e)}
                            products={selectedProducts}
                            onChange={() => { }}
                            type={type}
                            title={"Submit GP"}
                            titleModal={"Konfirmasi Submit"}
                        />
                    </Menu.Item>
                    <Menu.Item>
                        <a onClick={() => navigate("/transfer/submit/history")}>
                            <HistoryOutlined />
                            <span className="ml-2">History Submit</span>
                        </a>
                    </Menu.Item>
                </>
            )}
            <Menu.Item>
                <a
                    onClick={() => {
                        if (type === "received") {
                            return handleExportContent()
                        }

                        return handleExportContentTransfer()
                    }}
                >
                    <DownloadOutlined />
                    <span className="ml-2">Export</span>
                </a>
            </Menu.Item>
            {type === "konsinyasi" && !progressData && (
                <Menu.Item>
                    <ImportModal
                        refetch={() => loadInventoryData()}
                        type="transfer-konsinyasi"
                    />
                </Menu.Item>
            )}
        </Menu>
    )

    const rightContent = (
        <div className="flex justify-between items-center">
            {/* {show && (
                <Dropdown overlay={menu}>
                    <button
                        className="text-blue-700 border-[1px] border-blue-700 hover:bg-blue-800/10 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center mx-2"
                        onClick={(e) => e.preventDefault()}
                    >
                        <span className="mr-2">More Option</span>
                        <DownOutlined />
                    </button>
                </Dropdown>
            )} */}

            <FilterModalProduct handleOk={(val) => handleFilter(val)} type={type} />

            {show && (
                <button
                    onClick={() => navigate("form")}
                    className="text-white bg-blueColor hover:bg-blueColor/90 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center ml-2"
                >
                    <PlusOutlined />
                    <span className="ml-2">Tambah Produk</span>
                </button>
            )}
        </div>
    )

    const actionList = [
        {
            title: "Action",
            key: "action",
            fixed: "right",
            width: 100,
            render: (text, record) => {
                const { inventory_status, inventory_type, status, uid_inventory } =
                    record
                return (
                    <Dropdown.Button
                        style={{
                            left: -16,
                        }}
                        // icon={<MoreOutlined />}
                        overlay={
                            <Menu itemIcon={<RightOutlined />}>
                                <Menu.Item
                                    icon={<FolderOpenOutlined />}
                                    onClick={() => navigate(`detail/${uid_inventory}`)}
                                >
                                    Detail
                                </Menu.Item>

                                {inArray(status, ["draft"]) && (
                                    <Menu.Item
                                        icon={<FolderOpenOutlined />}
                                        onClick={() => navigate(`form/${uid_inventory}`)}
                                    >
                                        Ubah
                                    </Menu.Item>
                                )}

                                {!inArray(status, ["cancel"]) && inArray(getItem("role"), ["superadmin"]) && (
                                    <Popconfirm
                                        title="Apakah anda yakin?"
                                        onConfirm={() => cancelInventory(record.id)}
                                        okText="Ya"
                                        cancelText="Batal"
                                        okButtonProps={{ style: { width: "70px" } }}
                                        cancelButtonProps={{ style: { width: "70px" } }}
                                    >
                                        <Menu.Item icon={<CloseOutlined />}>Cancel</Menu.Item>
                                    </Popconfirm>
                                )}
                                {inArray(inventory_type, ["transfer"]) &&
                                    inArray(status, ["done", "draft"]) && (
                                        <Popconfirm
                                            title="Batalkan Transfer Produk Ini?"
                                            onConfirm={() => cancelInventoryTransfer(uid_inventory)}
                                            okText="Ya"
                                            cancelText="Batal"
                                            okButtonProps={{ style: { width: "70px" } }}
                                            cancelButtonProps={{ style: { width: "70px" } }}
                                        >
                                            <Menu.Item icon={<CloseOutlined />}>Cancel</Menu.Item>
                                        </Popconfirm>
                                    )}
                                {inArray(inventory_type, ["konsinyasi"]) &&
                                    inArray(status, ["draft"]) && (
                                        <Popconfirm
                                            title="Batalkan Transfer Produk Ini?"
                                            onConfirm={() =>
                                                cancelInventoryTransferKonsinyasi(uid_inventory)
                                            }
                                            okText="Ya"
                                            cancelText="Batal"
                                            okButtonProps={{ style: { width: "70px" } }}
                                            cancelButtonProps={{ style: { width: "70px" } }}
                                        >
                                            <Menu.Item icon={<CloseOutlined />}>Cancel</Menu.Item>
                                        </Popconfirm>
                                    )}
                            </Menu>
                        }
                    ></Dropdown.Button>
                )
            },
        },
    ]

    const konsinyasiColumns = inArray(type, ["adjustment"])
        ? inventoryStockAdjustmentColumns(currentPage, perpage)
        : inventoryTransferStockColumns

    const columns =
        type === "received" ? inventoryStockColumns : konsinyasiColumns

    return (
        <Layout
            // onClick={() => navigate(-1)}
            href={"/stock-adjustment"}
            title="List Item Stock Adjustment"
            rightContent={rightContent}
        >
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
                                                loadInventoryData()
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
                        <div className="col-md-8">
                            <strong className="float-right mt-3 text-red-400">
                                Total Data: {total}
                            </strong>
                        </div>
                    </div>
                    <ProgressImportData
                        callback={(data) => setProgressData(data)}
                        refetch={() => loadInventoryData()}
                        type="transfer-konsinyasi"
                    />
                    <Table
                        rowSelection={type === "transfer" && rowSelection}
                        scroll={{ x: "max-content" }}
                        tableLayout={"auto"}
                        dataSource={inventoryData}
                        columns={[...columns, ...actionList]}
                        loading={loading}
                        pagination={false}
                        rowKey="id"
                    />
                    <Pagination
                        defaultCurrent={1}
                        current={currentPage}
                        total={total}
                        className="mt-4 text-center"
                        onChange={handleChange}
                        pageSizeOptions={["10", "20", "50", "100", "200", "500"]}
                    />
                </div>
            </div>
        </Layout>
    )
}

export default StockAdjustment
