import React, { Suspense, useState, useEffect } from "react"
import { ThemeSwitcherProvider } from "react-css-theme-switcher"
import ReactDOM from "react-dom/client"
import { Route, BrowserRouter as Router, Routes } from "react-router-dom"
import { ToastContainer } from "react-toastify"
import "react-toastify/dist/ReactToastify.css"
import { ConfigProvider } from "antd"
import { getTheme } from "./themes/antdTheme"
import Login from "./Pages/Auth/Login"
import Register from "./Pages/Auth/Register"
import CaseManual from "./Pages/CaseManual/CaseManual"
import CaseManualDetail from "./Pages/CaseManual/CaseManualDetail"
import CaseManualForm from "./Pages/CaseManual/CaseManualForm"
import ComissionWithdraw from "./Pages/ComissionWithdraw/ComissionWithdraw"
import ComissionWithdrawDetail from "./Pages/ComissionWithdraw/ComissionWithdrawDetail"
import ComissionWithdrawForm from "./Pages/ComissionWithdraw/ComissionWithdrawForm"
import DashboardGinee from "./Pages/Genie/DashboardGinee"
import GpCustomer from "./Pages/GpCustomer/GpCustomer"
import GpSubmissionList from "./Pages/GpSubmission/GpSubmissionList"
import GpSubmissionListDetail from "./Pages/GpSubmission/GpSubmissionListDetail"
import InventoryProductReturnDetail from "./Pages/Inventory/InventoryProductReturnDetail"
import ProductTransferForm from "./Pages/Inventory/ProductTransfer/ProductTransferForm"
import StockAdjustmentForm from "./Pages/Inventory/StockAdjustment/StockAdjustmentForm"
import BannerList from "./Pages/Master/Banner/BannerList"
import FormBanner from "./Pages/Master/Banner/FormBanner"
import BrandList from "./Pages/Master/Brand/BrandList"
import FormBrand from "./Pages/Master/Brand/FormBrand"
import CategoryList from "./Pages/Master/Category/CategoryList"
import FormCategory from "./Pages/Master/Category/FormCategory"
import CategoryTypeCaseForm from "./Pages/Master/CategoryTypeCase/CategoryTypeCaseForm"
import CategoryTypeCaseList from "./Pages/Master/CategoryTypeCase/CategoryTypeCaseList"
import CompanyAccountForm from "./Pages/Master/CompanyAccount/CompanyAccountForm"
import CompanyAccountList from "./Pages/Master/CompanyAccount/CompanyAccountList"
import ProductCartonForm from "./Pages/Master/ProductCarton/ProductCartonForm"
import ProductCartonList from "./Pages/Master/ProductCarton/ProductCartonList"
import LevelForm from "./Pages/Master/Level/LevelForm"
import LevelList from "./Pages/Master/Level/LevelList"
import LogisticList from "./Pages/Master/Logistic/LogisticList"
import MasterDiscountForm from "./Pages/Master/MasterDiscount/MasterDiscountForm"
import MasterDiscountList from "./Pages/Master/MasterDiscount/MasterDiscountList"
import OngkirForm from "./Pages/Master/MasterOngkir/OngkirForm"
import OngkirList from "./Pages/Master/MasterOngkir/OngkirList"
import MasterPphForm from "./Pages/Master/MasterPph/MasterPphForm"
import MasterPphList from "./Pages/Master/MasterPph/MasterPphList"
import MasterTaxForm from "./Pages/Master/MasterTax/MasterTaxForm"
import MasterTaxList from "./Pages/Master/MasterTax/MasterTaxList"
import FormNotif from "./Pages/Master/Notif/FormNotif"
import NotifList from "./Pages/Master/Notif/NotifList"
import OfflineLogisticList from "./Pages/Master/OfflineLogistic/OfflineLogisticList"
import PackageForm from "./Pages/Master/Package/PackageForm"
import PackageList from "./Pages/Master/Package/PackageList"
import FormPaymentMethod from "./Pages/Master/PaymentMethod/FormPaymentMethod"
import PaymentMethodList from "./Pages/Master/PaymentMethod/PaymentMethodList"
import PaymentTermForm from "./Pages/Master/PaymentTerm/PaymentTermForm"
import PaymentTermList from "./Pages/Master/PaymentTerm/PaymentTermList"
import PointForm from "./Pages/Master/Point/PointForm"
import PointList from "./Pages/Master/Point/PointList"
import PriorityCaseForm from "./Pages/Master/PriorityCase/PriorityCaseForm"
import PriorityCaseList from "./Pages/Master/PriorityCase/PriorityCaseList"
import ProductAdditionalForm from "./Pages/Master/ProductAdditional/ProductAdditionalForm"
import ProductAdditionalList from "./Pages/Master/ProductAdditional/ProductAdditionalList"
import SalesChannelForm from "./Pages/Master/SalesChannel/SalesChannelForm"
import SalesChannelList from "./Pages/Master/SalesChannel/SalesChannelList"
import SkuForm from "./Pages/Master/Sku/SkuForm"
import SkuList from "./Pages/Master/Sku/SkuList"
import SourceCaseForm from "./Pages/Master/SourceCase/SourceCaseForm"
import SourceCaseList from "./Pages/Master/SourceCase/SourceCaseList"
import StatusCaseForm from "./Pages/Master/StatusCase/StatusCaseForm"
import StatusCaseList from "./Pages/Master/StatusCase/StatusCaseList"
import TypeCaseForm from "./Pages/Master/TypeCase/TypeCaseForm"
import TypeCaseList from "./Pages/Master/TypeCase/TypeCaseList"
import VariantForm from "./Pages/Master/Variant/VariantForm"
import VariantList from "./Pages/Master/Variant/VariantList"
import VendorForm from "./Pages/Master/Vendor/VendorForm"
import VendorList from "./Pages/Master/Vendor/VendorList"
import FormVoucher from "./Pages/Master/Voucher/FormVoucher"
import VoucherList from "./Pages/Master/Voucher/VoucherList"
import UrlShortenerForm from "./Pages/Master/UrlShortener/Form"
import UrlShortenerList from "./Pages/Master/UrlShortener/List"
import WarehouseForm from "./Pages/Master/Warehouse/WarehouseForm"
import MasterWarehouseList from "./Pages/Master/Warehouse/WarehouseList"
import OrderFreebiesDetail from "./Pages/OrderFreebies/OrderFreebiesDetail"
import OrderFreebiesForm from "./Pages/OrderFreebies/OrderFreebiesForm"
import OrderFreebiesList from "./Pages/OrderFreebies/OrderFreebiesList"
import OrdeSubmitList from "./Pages/OrderSubmit/OrdeSubmitList"
import OrdeSubmitListDetail from "./Pages/OrderSubmit/OrdeSubmitListDetail"
import ConvertProductDetailList from "./Pages/ProductManagement/ConvertProduct/ConvertProductDetailList"
import ConvertProductList from "./Pages/ProductManagement/ConvertProduct/ConvertProductList"
import ImportProductConvertList from "./Pages/ProductManagement/ImportProductConvert/ImportProductConvertList"
import ProductCommentRatingList from "./Pages/ProductManagement/ProductCommentRating/ProductCommentRatingList"
import ProductMarginBottomForm from "./Pages/ProductManagement/ProductMarginBottom/ProductMarginBottomForm"
import ProductMarginBottomList from "./Pages/ProductManagement/ProductMarginBottom/ProductMarginBottomList"
import ProductMasterForm from "./Pages/ProductManagement/ProductMaster/ProductMasterForm"
import ProductMasterList from "./Pages/ProductManagement/ProductMaster/ProductMasterList"
import ProductStockAllocation from "./Pages/ProductManagement/ProductMaster/ProductStockAllocation"
import ProductVariantForm from "./Pages/ProductManagement/ProductVariant/ProductVariantForm"
import ProductVariantList from "./Pages/ProductManagement/ProductVariant/ProductVariantList"
import PurchaseOrder from "./Pages/Purchase/PurchaseOrder"
import PurchaseOrderDetail from "./Pages/Purchase/PurchaseOrderDetail"
import PurchaseOrderForm from "./Pages/Purchase/PurchaseOrderForm"
import PurchaseOrderAccurate from "./Pages/Purchase/PurchaseOrderAccurate"
import CustomerAccurate from "./Pages/Accurate/Customer"
import ProductAccurate from "./Pages/Accurate/Product"
import WarehouseAccurate from "./Pages/Accurate/Warehouse"
import MerchandiserAccurate from "./Pages/Accurate/Merchandiser"
import ListMerchandiser from "./Pages/Accurate/ListMerchandiser"
import MerchandiserDetail from "./Pages/Accurate/MerchandiserDetail"
import StoreStockCount from "./Pages/Accurate/StoreStockCount"
import VisitList from "./Pages/Accurate/VisitList"
import SalesOrderAccurate from "./Pages/Accurate/SalesOrder"
import SalesOrderApp from "./Pages/Accurate/SalesOrderApp"
import SalesInvoiceAccurate from "./Pages/Accurate/SalesInvoice"
import StockTransferAccurate from "./Pages/Accurate/StockTransfer"
import SalesReturn from "./Pages/Accurate/SalesReturn"
import SalesReturnImport from "./Pages/Accurate/SalesReturnImport"
import StockOpnameAccurate from "./Pages/Accurate/StockOpname"
import StockAwalAccurate from "./Pages/Accurate/StockAwalAccurate"
import StockSystemCalculated from "./Pages/Accurate/StockSystemCalculated"
import StockCalculatedPanel from "./Pages/Accurate/StockCalculatedPanel"
import StockSystemAccurate from "./Pages/Accurate/StockSystemAccurate"
import AccurateContactGroup from "./Pages/Accurate/AccurateContactGroup"
import ContactGroupDetail from "./Pages/Accurate/contact-group/detail"
import PurchaseRequisition from "./Pages/Purchase/PurchaseRequisition"
import PurchaseRequisitionDetail from "./Pages/Purchase/PurchaseRequisitionDetail"
import PurchaseRequisitionForm from "./Pages/Purchase/PurchaseRequisitionForm"
import Asset from "./Pages/Asset/AssetList"
import AssetForm from "./Pages/Asset/AssetForm"
import NotificationTemplateForm from "./Pages/Setting/NotificationTemplate/NotificationTemplateForm"
import NotificationTemplateList from "./Pages/Setting/NotificationTemplate/NotificationTemplateList"
import StockMovement from "./Pages/StockMovement/StockMovement"
import TicketList from "./Pages/Ticket/TicketList"
import TransactionDetail from "./Pages/Transaction/Transaction/TransactionDetail"
import TransactionDetailNewOrder from "./Pages/Transaction/Transaction/TransactionDetailNewOrder"
import TransactionList from "./Pages/Transaction/Transaction/TransactionList"
import BarcodeMasterList from "./Pages/BarcodeMaster/BarcodeList"
import BarcodeMasterDetail from "./Pages/BarcodeMaster/BarcodeDetail"

import AgentList from "./Pages/AgentManagement/AgentList"
import DomainAgent from "./Pages/AgentManagement/DomainAgent"
import CaseRefund from "./Pages/CaseRefund/CaseRefund"
import CaseRefundDetail from "./Pages/CaseRefund/CaseRefundDetail"
import CaseReturn from "./Pages/CaseReturn/CaseReturn"
import CaseReturnDetail from "./Pages/CaseReturn/CaseReturnDetail"
import CartList from "./Pages/CheckoutAgent/CartList"
import ContactList from "./Pages/Contact/ContactList"
import DetailContact from "./Pages/Contact/DetailContact"
import FormContact from "./Pages/Contact/FormContact"
import Dashboard from "./Pages/Dashboard/Dashboard"
import OrderList from "./Pages/Genie/OrderList"
import OrderListDetail from "./Pages/Genie/OrderListDetail"
import Inventory from "./Pages/Inventory/Inventory"
import InventoryAddProducts from "./Pages/Inventory/InventoryAddProducts"
import InventoryProductReturn from "./Pages/Inventory/InventoryProductReturn"
import InventoryProductReturnForm from "./Pages/Inventory/InventoryProductReturnForm"
import InventoryProductStock from "./Pages/Inventory/InventoryProductStock"
import StockAdjustment from "./Pages/Inventory/StockAdjustment/StockAdjustment"
import LeadMasterDetail from "./Pages/LeadMaster/LeadMasterDetail"

import LeadMasterForm from "./Pages/LeadMaster/LeadMasterForm"
import LeadMasterList from "./Pages/LeadMaster/LeadMasterList"
import MenuPages from "./Pages/Menu/Menu"
// import OrderLeadDetail from "./Pages/OrderLead/OrderLeadDetail" // Commented out as not used
// import OrderLeadList from "./Pages/OrderLead/OrderLeadList" // Commented out as not used
import OrderManualLeadDetail from "./Pages/OrderManual/OrderManualLeadDetail"
import OrderManualLeadForm from "./Pages/OrderManual/OrderManualLeadForm"
import OrderManualLeadList from "./Pages/OrderManual/OrderManualLeadList"

import OrderKonsinyasiDetail from "./Pages/OrderKonsinyasi/OrderKonsinyasiDetail"
import OrderKonsinyasiForm from "./Pages/OrderKonsinyasi/OrderKonsinyasiForm"
import OrderKonsinyasiList from "./Pages/OrderKonsinyasi/OrderKonsinyasiList"

import SalesReturnDetail from "./Pages/SalesReturn/SalesReturnDetail"
import SalesReturnForm from "./Pages/SalesReturn/SalesReturnForm"
import SalesReturnList from "./Pages/SalesReturn/SalesReturnList"
import AgentWaitingList from "./Pages/TransAgent/AgentWaitingList"
import AllTransList from "./Pages/TransAgent/AllTransList"
import ConfirmationList from "./Pages/TransAgent/ConfirmationList"
import DeliveryList from "./Pages/TransAgent/DeliveryList"
import DetailTransAgent from "./Pages/TransAgent/DetailTransAgent"
import NewTransactionList from "./Pages/TransAgent/NewTransactionList"
import OrderAcceptedList from "./Pages/TransAgent/OrderAcceptedList"
import ReadyProductList from "./Pages/TransAgent/ReadyProductList"
import WarehouseList from "./Pages/TransAgent/WarehouseList"

import { Provider } from "react-redux"
import store from "./configs/Redux/store"
import { formatDate } from "./helpers"
import BarcodeDetail from "./Pages/Barcode/BarcodeDetail"
import BarcodeList from "./Pages/Barcode/BarcodeList"
import BinListDetail from "./Pages/Bin/BinDetail"
import BinList from "./Pages/Bin/BinList"
import OrderListEthixMp from "./Pages/Ethix/OrderList"
import OrderListDetailMpc from "./Pages/Ethix/OrderListDetail"
import EthixOrderSubmitList from "./Pages/EthixOrderSubmit/EthixOrderSubmitList"
import EthixOrdeSubmitListDetail from "./Pages/EthixOrderSubmit/EthixOrdeSubmitListDetail"
import DetailOrderMP from "./Pages/Marketplace/ListOrder/DetailOrderMP"
import ListOrderMP from "./Pages/Marketplace/ListOrder/ListOrderMP"
import CheckbookForm from "./Pages/Master/Checkbook/CheckbookForm"
import CheckbookList from "./Pages/Master/Checkbook/CheckbookList"
import MasterBatchIDForm from "./Pages/Master/MasterBatchID/MasterBatchIDForm"
import MasterBatchIDList from "./Pages/Master/MasterBatchID/MasterBatchIDList"
import MasterBinForm from "./Pages/Master/MasterBin/MasterBinForm"
import MasterBinList from "./Pages/Master/MasterBin/MasterBinList"
import MasterSiteIDForm from "./Pages/Master/MasterSiteID/MasterSiteIDForm"
import MasterSiteIDList from "./Pages/Master/MasterSiteID/MasterSiteIDList"
import RateLimitSetting from "./Pages/Master/RateLimitSetting/RateLimitSetting"
import OrderInvoiceDetail from "./Pages/OrderInvoice/OrderInvoiceDetail"
import OrderInvoiceForm from "./Pages/OrderInvoice/OrderInvoiceForm"
import OrderInvoiceList from "./Pages/OrderInvoice/OrderInvoiceList"
import PurchaseInvoiceEntry from "./Pages/PurchaseInvoiceEntry/PurchaseInvoiceEntry"
import PurchaseInvoiceEntryDetail from "./Pages/PurchaseInvoiceEntry/PurchaseInvoiceEntryDetail"
import PurchaseInvoiceEntryForm from "./Pages/PurchaseInvoiceEntry/PurchaseInvoiceEntryForm"
import TicketDetail from "./Pages/Ticket/TicketDetail"
import HistoryList from "./Pages/TransAgent/HistoryList"
import ProductTransferDetail from "./Pages/Inventory/ProductTransfer/ProductTransferDetail"
import StockAdjustmentDetail from "./Pages/Inventory/StockAdjustment/StockAdjustmentDetail"
import ContactGroupList from "./Pages/ContactGroup/ContactGroupList"
import ContactGroupForm from "./Pages/ContactGroup/ContactGroupForm"
import OrderSalesDetail from "./Pages/OrderSales/OrderSalesDetail"
import RoleList from "./Pages/SiteManagement/Role/RoleList"
import FormRole from "./Pages/SiteManagement/Role/FormRole"
import NotificationTemplateListGroup from "./Pages/Setting/NotificationTemplate/NotificationTemplateListGroup"
import { Tag } from "antd"
import TransactionAgentList from "./Pages/Transaction/Agent/TransactionAgentList"
import TransactionAgentDetail from "./Pages/Transaction/Agent/TransactionAgentDetail"
import AccurateActualStocks from "./Pages/Accurate/AccurateActualStocks"
import AccurateStockCount from "./Pages/Accurate/AccurateStockCount"
// import ReportComaprison from "./Pages/Accurate/ReportComparison" -- awal
import ReportComaprison from "./Pages/Accurate/ReportComparisonExport"
import SalesOrderDetail from "./Pages/Accurate/SalesOrderDetail"

const App = () => {
  const [currentTheme, setCurrentTheme] = useState(
    localStorage.getItem("theme") || "light"
  )

  useEffect(() => {
    const handleStorageChange = () => {
      setCurrentTheme(localStorage.getItem("theme") || "light")
    }
    window.addEventListener("storage", handleStorageChange)
    return () => window.removeEventListener("storage", handleStorageChange)
  }, [])

  // Get theme config
  const themeConfig = getTheme(currentTheme)

  return (
    <ConfigProvider theme={themeConfig}>
      <div>
        <ToastContainer />
        <Router>
        <Suspense fallback={(loading) => console.log(loading, "loading")}>
          <Routes>
            {/* auth */}
            <Route path="/" element={<ContactList />} />
            <Route path="/login/dashboard" element={<Login />} />
            <Route path="/agent/register" element={<Register />} />

            {/* start contact */}
            <Route path="/contact" element={<ContactList />} />
            <Route path="/contact/create" element={<FormContact />} />
            <Route
              path="/contact/detail/:user_id"
              element={<DetailContact />}
            />
            <Route path="/contact/update/:user_id" element={<FormContact />} />
            {/* end contact */}

            {/* start contact */}
            <Route path="/contact-group" element={<ContactGroupList />} />
            <Route path="/contact-group/form" element={<ContactGroupForm />} />
            <Route
              path="/contact-group/form/:group_id"
              element={<ContactGroupForm />}
            />
            {/* end contact */}

            {/* start Trans Agent */}
            <Route path="/trans-agent/all-trans" element={<AllTransList />} />
            <Route
              path="/trans-agent/waiting-payment"
              element={<AgentWaitingList />}
            />
            <Route
              path="/trans-agent/confirmation"
              element={<ConfirmationList />}
            />
            <Route
              path="/trans-agent/new-transaction"
              element={<NewTransactionList />}
            />
            <Route path="/trans-agent/warehouse" element={<WarehouseList />} />
            <Route
              path="/trans-agent/ready-product"
              element={<ReadyProductList />}
            />
            <Route path="/trans-agent/delivery" element={<DeliveryList />} />
            <Route
              path="/trans-agent/order-accepted"
              element={<OrderAcceptedList />}
            />
            <Route path="/trans-agent/history" element={<HistoryList />} />
            <Route
              path="/trans-agent/detail/:id"
              element={<DetailTransAgent />}
            />
            {/* end Trans Agent */}

            {/* start genie */}
            <Route path="/genie/dashboard" element={<DashboardGinee />} />
            <Route path="/genie/order/list" element={<OrderList />} />
            <Route
              path="/genie/order/detail/:orderId"
              element={<OrderListDetail />}
            />

            {/* start genie */}
            <Route path="/mp-ethix/dashboard" element={<OrderListEthixMp />} />
            <Route
              path="/mp-ethix/detail/:orderId"
              element={<OrderListDetailMpc />}
            />
            {/* <Route path="/mp-ethix/dashboard" element={<MpEthix />} /> */}
            <Route
              path="/mp-ethix/order/detail/:orderId"
              element={<OrderListDetail />}
            />

            {/* checkout agent */}
            <Route path="/cart/list" element={<CartList />} />

            {/* order lead */}
            <Route
              path="/order/order-lead"
              element={<OrderManualLeadList type="lead" />}
            />
            <Route
              path="/order/order-lead/form"
              element={<OrderManualLeadForm />}
            />
            <Route
              path="/order/order-lead/detail/:uid_lead"
              element={<OrderManualLeadDetail type="lead" />}
            />

            {/* sales-order */}
            <Route
              path="/order/sales-order/detail/:uid_lead"
              element={<OrderSalesDetail />}
            />

            {/* order lead manual*/}
            <Route
              path="/order/order-manual"
              element={<OrderManualLeadList type="manual" />}
            />
            <Route
              path="/order/order-manual/detail/:uid_lead"
              element={<OrderManualLeadDetail type="manual" />}
            />
            <Route
              path="/order/order-manual/form"
              element={<OrderManualLeadForm />}
            />
            <Route
              path="/order/order-manual/form/:uid_lead"
              element={<OrderManualLeadForm />}
            />

            {/* order konsinyasi */}
            <Route
              path="/order/order-konsinyasi"
              element={<OrderKonsinyasiList />}
            />
            <Route
              path="/order/order-konsinyasi/detail/:uid_lead"
              element={<OrderKonsinyasiDetail />}
            />
            <Route
              path="/order/order-konsinyasi/form"
              element={<OrderKonsinyasiForm />}
            />
            <Route
              path="/order/order-konsinyasi/form/:uid_lead"
              element={<OrderKonsinyasiForm />}
            />

            <Route path="/order/invoice" element={<OrderInvoiceList />} />
            <Route
              path="/order/invoice/detail/:uid_lead/:uid_delivery"
              element={<OrderInvoiceDetail />}
            />
            <Route
              path="/order/invoice/form/:uid_lead"
              element={<OrderInvoiceForm />}
            />

            {/* Dashboard */}
            <Route path="/dashboard" element={<Dashboard />} />

            {/* agent list */}
            <Route path="/agent/list" element={<AgentList />} />
            <Route path="/agent/domain" element={<DomainAgent />} />

            {/* menu */}
            <Route path="/menu" element={<MenuPages />} />

            {/* case return */}
            <Route path="/case/return" element={<CaseReturn />} />
            <Route
              path="/case/return/:uid_retur"
              element={<CaseReturnDetail />}
            />

            {/* case refund */}
            <Route path="/case/refund" element={<CaseRefund />} />
            <Route
              path="/case/refund/:uid_refund"
              element={<CaseRefundDetail />}
            />

            {/* case refund */}
            <Route path="/case/manual" element={<CaseManual />} />
            <Route path="/case/manual/form">
              <Route path=":uid_case" element={<CaseManualForm />} />
              <Route path="" element={<CaseManualForm />} />
            </Route>
            <Route
              path="/case/manual/detail/:uid_case"
              element={<CaseManualDetail />}
            />

            <Route
              path="/accurate-integration/customer"
              element={<CustomerAccurate />}
            />
            <Route
              path="/accurate-integration/product"
              element={<ProductAccurate />}
            />
            <Route
              path="/accurate-integration/warehouse"
              element={<WarehouseAccurate />}
            />
            <Route
              path="/accurate-integration/merchandiser"
              element={<MerchandiserAccurate />}
            />
            <Route
              path="/accurate-integration/list-merchandiser"
              element={<ListMerchandiser />}
            />
            <Route
              path="/accurate-integration/list-merchandiser/:id"
              element={<MerchandiserDetail />}
            />
            <Route
              path="/accurate-integration/store-stock-count/:storeId/:userId"
              element={<StoreStockCount />}
            />
            <Route
              path="/accurate-integration/visit-list"
              element={<VisitList />}
            />
            <Route
              path="/accurate-integration/sales-order"
              element={<SalesOrderAccurate />}
            />
            <Route
              path="/accurate-integration/sales-order-app"
              element={<SalesOrderApp />}
            />
            <Route
              path="/accurate-integration/sales-order-app/:id"
              element={<SalesOrderDetail />}
            />
            <Route
              path="/accurate-integration/sales-invoice"
              element={<SalesInvoiceAccurate />}
            />
            <Route
              path="/accurate-integration/stock-transfer"
              element={<StockTransferAccurate />}
            />
            <Route
              path="/accurate-integration/sales-return"
              element={<SalesReturn />}
            />
            <Route
              path="/accurate-integration/sales-return-import"
              element={<SalesReturnImport />}
            />
            <Route
              path="/accurate-integration/stock-system-calculated"
              element={<StockSystemCalculated />}
            />
            <Route
              path="/accurate-integration/stock-system-accurate"
              element={<StockSystemAccurate />}
            />
            <Route
              path="/accurate-integration/contact-group"
              element={<AccurateContactGroup />}
            />
            <Route
              path="/accurate-integration/contact-group/detail"
              element={<ContactGroupDetail />}
            />
            <Route
              path="/accurate-integration/actual-stocks"
              element={<AccurateActualStocks />}
            />
            <Route
              path="/accurate-integration/stock-count"
              element={<AccurateStockCount />}
            />
            <Route
              path="/accurate-integration/stock-comparison"
              element={<ReportComaprison />}
            />
            <Route
              path="/accurate-integration/stock-awal-accurate"
              element={<StockAwalAccurate />}
            />
            <Route
              path="/accurate-integration/stock-awal-opname"
              element={<StockOpnameAccurate />}
            />

            {/* freebies */}
            <Route path="/order/freebies" element={<OrderFreebiesList />} />
            <Route path="/order/freebies/form">
              <Route path=":uid_lead" element={<OrderFreebiesForm />} />
              <Route path="" element={<OrderFreebiesForm />} />
            </Route>
            <Route
              path="/order/freebies/detail/:uid_lead"
              element={<OrderFreebiesDetail />}
            />

            {/* order submit id */}
            <Route
              path="/order/submit/history"
              element={
                <OrdeSubmitList
                  columns={[
                    {
                      title: "No.",
                      dataIndex: "number",
                      key: "number",
                    },
                    {
                      title: "Invoice Number",
                      dataIndex: "po_number",
                      key: "po_number",
                    },
                    {
                      title: "Submited by",
                      dataIndex: "submited_by_name",
                      key: "submited_by_name",
                    },
                    {
                      title: "Status",
                      dataIndex: "status",
                      key: "status",
                      render: (text, record) => {
                        if (text == "failed") {
                          return <Tag color="red">Failed</Tag>
                        }
                        if (text == "success") {
                          return <Tag color="green">Success</Tag>
                        }
                        if (record.failed > 0) {
                          return <Tag color="red">Failed</Tag>
                        }
                        if (record.success > 0) {
                          return <Tag color="green">Success</Tag>
                        }
                        return <Tag color="red">Failed</Tag>
                      },
                    },
                    {
                      title: "Error Message",
                      dataIndex: "message",
                      key: "message",
                    },
                    {
                      title: "Submited On",
                      dataIndex: "created_at",
                      key: "created_at",
                      render: (text) => formatDate(text),
                    },
                  ]}
                />
              }
            />
            <Route
              path="/order/submit/history/:submit_id"
              element={<OrdeSubmitListDetail />}
            />

            {/* transaction agent */}
            <Route
              path="/transaction-agent/submit/history"
              element={
                <OrdeSubmitList
                  type={["transaction-agent"]}
                  action={"transaction"}
                  columns={[
                    {
                      title: "No.",
                      dataIndex: "number",
                      key: "number",
                    },
                    {
                      title: "SI Number",
                      dataIndex: "po_number",
                      key: "po_number",
                    },
                    {
                      title: "GP Number",
                      dataIndex: "gp_po_number",
                      key: "gp_po_number",
                    },
                    {
                      title: "Submited by",
                      dataIndex: "submited_by_name",
                      key: "submited_by_name",
                    },
                    {
                      title: "Status",
                      dataIndex: "status",
                      key: "status",
                      render: (text, record) => {
                        if (text == "failed") {
                          return <Tag color="red">Failed</Tag>
                        }
                        if (text == "success") {
                          return <Tag color="green">Success</Tag>
                        }
                        if (record.failed > 0) {
                          return <Tag color="red">Failed</Tag>
                        }
                        if (record.success > 0) {
                          return <Tag color="green">Success</Tag>
                        }
                        return <Tag color="red">Failed</Tag>
                      },
                    },
                    {
                      title: "Error Message",
                      dataIndex: "message",
                      key: "message",
                    },
                    {
                      title: "Submited On",
                      dataIndex: "created_at",
                      key: "created_at",
                      render: (text) => formatDate(text),
                    },
                  ]}
                />
              }
            />
            <Route
              path="/transaction-agent/submit/history/:submit_id"
              element={<OrdeSubmitListDetail type={["transaction-agent"]} />}
            />

            {/* invoice */}
            <Route
              path="/order/invoice/history-submit"
              element={
                <OrdeSubmitList
                  type={["submit-klik-pajak"]}
                  action={"invoice"}
                  columns={[
                    {
                      title: "No.",
                      dataIndex: "number",
                      key: "number",
                    },
                    {
                      title: "Invoice Number",
                      dataIndex: "po_number",
                      key: "po_number",
                    },
                    {
                      title: "Submited by",
                      dataIndex: "submited_by_name",
                      key: "submited_by_name",
                    },
                    {
                      title: "Status",
                      dataIndex: "status",
                      key: "status",
                      render: (text, record) => {
                        if (text == "failed") {
                          return <Tag color="red">Failed</Tag>
                        }
                        if (text == "success") {
                          return <Tag color="green">Success</Tag>
                        }
                        if (record.failed > 0) {
                          return <Tag color="red">Failed</Tag>
                        }
                        if (record.success > 0) {
                          return <Tag color="green">Success</Tag>
                        }
                        return <Tag color="red">Failed</Tag>
                      },
                    },
                    {
                      title: "Error Message",
                      dataIndex: "message",
                      key: "message",
                    },
                    {
                      title: "Submited On",
                      dataIndex: "created_at",
                      key: "created_at",
                      render: (text) => formatDate(text),
                    },
                  ]}
                />
              }
            />
            <Route
              path="/order/invoice/history-submit/:submit_id"
              element={<OrdeSubmitListDetail type={["submit-klik-pajak"]} />}
            />

            {/* ethix */}
            <Route
              path="/ethix/submit/history"
              element={<EthixOrderSubmitList />}
            />
            <Route
              path="/ethix/submit/history/:submit_id"
              element={<EthixOrdeSubmitListDetail />}
            />

            {/* transaction */}
            <Route
              path="/transaction/submit/history"
              element={
                <OrdeSubmitList
                  type={["trx_general"]}
                  action={"transaction"}
                  columns={[
                    {
                      title: "No.",
                      dataIndex: "number",
                      key: "number",
                    },
                    {
                      title: "SI Number",
                      dataIndex: "po_number",
                      key: "po_number",
                    },
                    {
                      title: "GP Number",
                      dataIndex: "gp_po_number",
                      key: "gp_po_number",
                    },
                    {
                      title: "Submited by",
                      dataIndex: "submited_by_name",
                      key: "submited_by_name",
                    },
                    {
                      title: "Status",
                      dataIndex: "status",
                      key: "status",
                      render: (text, record) => {
                        if (text == "failed") {
                          return <Tag color="red">Failed</Tag>
                        }
                        if (text == "success") {
                          return <Tag color="green">Success</Tag>
                        }
                        if (record.failed > 0) {
                          return <Tag color="red">Failed</Tag>
                        }
                        if (record.success > 0) {
                          return <Tag color="green">Success</Tag>
                        }
                        return <Tag color="red">Failed</Tag>
                      },
                    },
                    {
                      title: "Error Message",
                      dataIndex: "message",
                      key: "message",
                    },
                    {
                      title: "Submited On",
                      dataIndex: "created_at",
                      key: "created_at",
                      render: (text) => formatDate(text),
                    },
                  ]}
                />
              }
            />
            <Route
              path="/transaction/submit/history/:submit_id"
              element={<OrdeSubmitListDetail type={["trx_general"]} />}
            />

            {/* transaction telmart */}
            <Route
              path="/transaction-telmart/submit/history"
              element={
                <OrdeSubmitList
                  type={["telmark"]}
                  action={"transaction"}
                  columns={[
                    {
                      title: "No.",
                      dataIndex: "number",
                      key: "number",
                    },
                    {
                      title: "SI Number",
                      dataIndex: "po_number",
                      key: "po_number",
                    },
                    {
                      title: "GP Number",
                      dataIndex: "gp_po_number",
                      key: "gp_po_number",
                    },
                    {
                      title: "Submited by",
                      dataIndex: "submited_by_name",
                      key: "submited_by_name",
                    },
                    {
                      title: "Status",
                      dataIndex: "status",
                      key: "status",
                      render: (text, record) => {
                        if (text == "failed") {
                          return <Tag color="red">Failed</Tag>
                        }
                        if (text == "success") {
                          return <Tag color="green">Success</Tag>
                        }
                        if (record.failed > 0) {
                          return <Tag color="red">Failed</Tag>
                        }
                        if (record.success > 0) {
                          return <Tag color="green">Success</Tag>
                        }
                        return <Tag color="red">Failed</Tag>
                      },
                    },
                    {
                      title: "Error Message",
                      dataIndex: "message",
                      key: "message",
                    },
                    {
                      title: "Submited On",
                      dataIndex: "created_at",
                      key: "created_at",
                      render: (text) => formatDate(text),
                    },
                  ]}
                />
              }
            />
            <Route
              path="/transaction-telmart/submit/history/:submit_id"
              element={<OrdeSubmitListDetail type={["telmark"]} />}
            />

            {/* transaction lms */}
            <Route
              path="/transaction-lms/submit/history"
              element={
                <OrdeSubmitList
                  type={["lms"]}
                  action={"transaction"}
                  columns={[
                    {
                      title: "No.",
                      dataIndex: "number",
                      key: "number",
                    },
                    {
                      title: "SI Number",
                      dataIndex: "po_number",
                      key: "po_number",
                    },
                    {
                      title: "GP Number",
                      dataIndex: "gp_po_number",
                      key: "gp_po_number",
                    },
                    {
                      title: "Submited by",
                      dataIndex: "submited_by_name",
                      key: "submited_by_name",
                    },
                    {
                      title: "Status",
                      dataIndex: "status",
                      key: "status",
                      render: (text, record) => {
                        if (text == "failed") {
                          return <Tag color="red">Failed</Tag>
                        }
                        if (text == "success") {
                          return <Tag color="green">Success</Tag>
                        }
                        if (record.failed > 0) {
                          return <Tag color="red">Failed</Tag>
                        }
                        if (record.success > 0) {
                          return <Tag color="green">Success</Tag>
                        }
                        return <Tag color="red">Failed</Tag>
                      },
                    },
                    {
                      title: "Error Message",
                      dataIndex: "message",
                      key: "message",
                    },
                    {
                      title: "Submited On",
                      dataIndex: "created_at",
                      key: "created_at",
                      render: (text) => formatDate(text),
                    },
                  ]}
                />
              }
            />
            <Route
              path="/transaction-lms/submit/history/:submit_id"
              element={<OrdeSubmitListDetail type={["lms"]} />}
            />

            <Route
              path="/purchase/history-submit"
              element={
                <OrdeSubmitList
                  type={[
                    "purchase-order",
                    "receiving-purchase-order",
                    "purchasing-invoice-entry",
                    "manual-payment-entry",
                    "payables-entry",
                  ]}
                  action={"purchase"}
                  columns={[
                    {
                      title: "No.",
                      dataIndex: "number",
                      key: "number",
                    },
                    {
                      title: "FIS Number",
                      dataIndex: "po_number",
                      key: "po_number",
                    },
                    {
                      title: "GP Number",
                      dataIndex: "gp_po_number",
                      key: "gp_po_number",
                    },
                    {
                      title: "Submited by",
                      dataIndex: "submited_by_name",
                      key: "submited_by_name",
                    },
                    {
                      title: "Type Submit",
                      dataIndex: "type_si",
                      key: "type_si",
                    },
                    {
                      title: "Success",
                      dataIndex: "success",
                      key: "success",
                    },
                    {
                      title: "Failed",
                      dataIndex: "failed",
                      key: "failed",
                    },
                    {
                      title: "Submited On",
                      dataIndex: "created_at",
                      key: "created_at",
                      render: (text) => formatDate(text),
                    },
                  ]}
                />
              }
            />
            <Route
              path="/purchase/history-submit/:submit_id"
              element={
                <OrdeSubmitListDetail
                  type={[
                    "purchase-order",
                    "receiving-purchase-order",
                    "purchasing-invoice-entry",
                    "payables-entry",
                    "manual-payment-entry",
                  ]}
                />
              }
            />

            <Route
              path="/transfer/submit/history"
              element={
                <OrdeSubmitList
                  type={["inventory-transfer"]}
                  action={"transfer"}
                  columns={[
                    {
                      title: "No.",
                      dataIndex: "number",
                      key: "number",
                    },
                    {
                      title: "GP Number",
                      dataIndex: "gp_po_number",
                      key: "gp_po_number",
                    },
                    {
                      title: "Submited by",
                      dataIndex: "submited_by_name",
                      key: "submited_by_name",
                    },
                    {
                      title: "Type Submit",
                      dataIndex: "type_si",
                      key: "type_si",
                    },
                    {
                      title: "Success",
                      dataIndex: "success",
                      key: "success",
                    },
                    {
                      title: "Failed",
                      dataIndex: "failed",
                      key: "failed",
                    },
                    {
                      title: "Submited On",
                      dataIndex: "created_at",
                      key: "created_at",
                      render: (text) => formatDate(text),
                    },
                  ]}
                />
              }
            />
            <Route
              path="/transfer/submit/history/:submit_id"
              element={<OrdeSubmitListDetail type={["inventory-transfer"]} />}
            />
            <Route
              path="/contact/submit/history"
              element={
                <OrdeSubmitList
                  type={["customer-contact"]}
                  action={"contact"}
                  columns={[
                    {
                      title: "No.",
                      dataIndex: "number",
                      key: "number",
                    },
                    {
                      title: "GP Number",
                      dataIndex: "po_number",
                      key: "po_number",
                    },
                    {
                      title: "Submited by",
                      dataIndex: "submited_by_name",
                      key: "submited_by_name",
                    },
                    {
                      title: "Type Submit",
                      dataIndex: "type_si",
                      key: "type_si",
                    },
                    {
                      title: "Success",
                      dataIndex: "success",
                      key: "success",
                    },
                    {
                      title: "Failed",
                      dataIndex: "failed",
                      key: "failed",
                    },
                    {
                      title: "Submited On",
                      dataIndex: "created_at",
                      key: "created_at",
                      render: (text) => formatDate(text),
                    },
                  ]}
                />
              }
            />
            <Route
              path="/contact/submit/history/:submit_id"
              element={<OrdeSubmitListDetail type={["customer-contact"]} />}
            />
            <Route
              path="/marketplace/submit/history"
              element={
                <OrdeSubmitList
                  type={["marketplace"]}
                  action={"marketplace"}
                  columns={[
                    {
                      title: "No.",
                      dataIndex: "number",
                      key: "number",
                    },
                    {
                      title: "FIS Number",
                      dataIndex: "po_number",
                      key: "po_number",
                      render: (value) => {
                        return value
                      },
                    },
                    {
                      title: "GP Number",
                      dataIndex: "gp_po_number",
                      key: "gp_po_number",
                    },
                    {
                      title: "Submited by",
                      dataIndex: "submited_by_name",
                      key: "submited_by_name",
                    },
                    {
                      title: "Type Submit",
                      dataIndex: "type_si",
                      key: "type_si",
                    },
                    {
                      title: "Success",
                      dataIndex: "success",
                      key: "success",
                    },
                    {
                      title: "Failed",
                      dataIndex: "failed",
                      key: "failed",
                    },
                    {
                      title: "Submited On",
                      dataIndex: "created_at",
                      key: "created_at",
                      render: (text) => formatDate(text),
                    },
                  ]}
                />
              }
            />
            <Route
              path="/marketplace/submit/history/:submit_id"
              element={<OrdeSubmitListDetail type={["marketplace"]} />}
            />
            <Route
              path="/ethix/submit/history"
              element={
                <OrdeSubmitList
                  type={["marketplace"]}
                  action={"marketplace"}
                  title="Submit Ethix History"
                  columns={[
                    {
                      title: "No.",
                      dataIndex: "number",
                      key: "number",
                    },
                    {
                      title: "FIS Number",
                      dataIndex: "po_number",
                      key: "po_number",
                      render: (value) => {
                        return value
                      },
                    },
                    {
                      title: "Submited by",
                      dataIndex: "submited_by_name",
                      key: "submited_by_name",
                    },
                    {
                      title: "Type Submit",
                      dataIndex: "type_si",
                      key: "type_si",
                    },
                    {
                      title: "Success",
                      dataIndex: "success",
                      key: "success",
                    },
                    {
                      title: "Failed",
                      dataIndex: "failed",
                      key: "failed",
                    },
                    {
                      title: "Submited On",
                      dataIndex: "created_at",
                      key: "created_at",
                      render: (text) => formatDate(text),
                    },
                  ]}
                />
              }
            />
            <Route
              path="/ethix/submit/history/:submit_id"
              element={<OrdeSubmitListDetail type={["submit-ethix"]} />}
            />

            {/* import contact */}
            <Route
              path="/contact-import/submit/history"
              element={
                <OrdeSubmitList
                  type={["import-contact"]}
                  action={"contact-import"}
                  title="History Import"
                  columns={[
                    {
                      title: "No.",
                      dataIndex: "number",
                      key: "number",
                    },

                    {
                      title: "Import by",
                      dataIndex: "submited_by_name",
                      key: "submited_by_name",
                    },
                    // {
                    //   title: "Type Submit",
                    //   dataIndex: "type_si",
                    //   key: "type_si",
                    // },
                    {
                      title: "Success",
                      dataIndex: "success",
                      key: "success",
                    },
                    {
                      title: "Failed",
                      dataIndex: "failed",
                      key: "failed",
                    },
                    {
                      title: "Import date",
                      dataIndex: "created_at",
                      key: "created_at",
                      render: (text) => formatDate(text),
                    },
                  ]}
                />
              }
            />
            <Route
              path="/contact-import/submit/history/:submit_id"
              element={<OrdeSubmitListDetail type={["import-contact"]} />}
            />

            {/* sales return */}
            <Route path="/order/sales-return" element={<SalesReturnList />} />
            <Route
              path="/order/sales-return/detail/:uid_retur"
              element={<SalesReturnDetail />}
            />
            <Route path="/order/sales-return/form">
              <Route path=":uid_return" element={<SalesReturnForm />} />
              <Route path="" element={<SalesReturnForm />} />
            </Route>

            {/* inventory */}
            <Route path="/inventory-new" element={<Inventory />} />
            <Route
              path="/inventory-new/inventory-product-return"
              element={<InventoryProductReturn />}
            />
            <Route path="/inventory-new/inventory-product-return/form">
              <Route
                path=":inventory_id"
                element={<InventoryProductReturnForm />}
              />
              <Route path="" element={<InventoryProductReturnForm />} />
            </Route>
            <Route
              path="/inventory-new/inventory-product-return/detail/:inventory_id"
              element={<InventoryProductReturnDetail />}
            />

            {/* product stock form */}
            <Route
              path="/inventory-new/inventory-product-stock"
              element={<InventoryProductStock />}
            />
            <Route path="/inventory-new/inventory-product-stock/detail">
              <Route path=":inventory_id" element={<InventoryAddProducts />} />
              <Route path="" element={<InventoryAddProducts />} />
            </Route>

            {/* product transfer */}
            <Route
              path="/inventory-new/inventory-product-transfer"
              element={<InventoryProductStock type={"transfer"} />}
            />
            <Route
              path="/inventory-new/inventory-product-transfer/detail/:inventory_id"
              element={<ProductTransferDetail />}
            />

            <Route path="/inventory-new/inventory-product-transfer/form">
              <Route path=":inventory_id" element={<ProductTransferForm />} />
              <Route path="" element={<ProductTransferForm />} />
            </Route>

            {/* transfer konsinyasi */}
            <Route
              path="/inventory-new/item-transfer-konsinyasi"
              element={<InventoryProductStock type={"konsinyasi"} />}
            />
            <Route
              path="/inventory-new/item-transfer-konsinyasi/detail/:inventory_id"
              element={<ProductTransferDetail inventory_type="konsinyasi" />}
            />

            <Route path="/inventory-new/item-transfer-konsinyasi/form">
              <Route
                path=":inventory_id"
                element={<ProductTransferForm inventory_type="konsinyasi" />}
              />
              <Route
                path=""
                element={<ProductTransferForm inventory_type="konsinyasi" />}
              />
            </Route>

            {/* stock adjustment */}
            <Route
              path="/stock-adjustment"
              element={<StockAdjustment type={"adjustment"} />}
            />
            <Route
              path="/stock-adjustment/detail/:inventory_id"
              element={<StockAdjustmentDetail inventory_type="adjustment" />}
            />

            <Route path="/stock-adjustment/form">
              <Route
                path=":inventory_id"
                element={<StockAdjustmentForm inventory_type="adjustment" />}
              />
              <Route
                path=""
                element={<StockAdjustmentForm inventory_type="adjustment" />}
              />
            </Route>

            {/* product return */}
            <Route path="/inventory-new/inventory-product-return/form">
              <Route
                path=":inventory_id"
                element={<InventoryProductReturnForm />}
              />
              <Route path="" element={<InventoryProductReturnForm />} />
            </Route>

            {/* lead master */}

            <Route path="/lead-master" element={<LeadMasterList />} />
            <Route
              path="/lead-master/detail/:uid_lead"
              element={<LeadMasterDetail />}
            />
            <Route path="/lead-master/form">
              <Route path=":uid_lead" element={<LeadMasterForm />} />
              <Route path="" element={<LeadMasterForm />} />
            </Route>
            <Route path="/gp-submission" element={<GpSubmissionList />} />
            <Route
              path="/gp-submission/list/detail/:list_id"
              element={<GpSubmissionListDetail />}
            />
            <Route path="/master/gp-customer-code" element={<GpCustomer />} />

            {/* master data */}
            <Route path="/master/brand" element={<BrandList />} />
            <Route path="/master/brand/form" element={<FormBrand />} />
            <Route
              path="/master/brand/form/:brand_id"
              element={<FormBrand />}
            />

            {/* master master/company-account */}
            <Route
              path="/master/company-account"
              element={<CompanyAccountList />}
            />
            <Route
              path="/master/company-account/form"
              element={<CompanyAccountForm />}
            />
            <Route
              path="/master/company-account/form/:company_account_id"
              element={<CompanyAccountForm />}
            />

            {/* master master/product-carton */}
            <Route
              path="/master/produk-karton"
              element={<ProductCartonList />}
            />
            <Route
              path="/master/produk-karton/form"
              element={<ProductCartonForm />}
            />
            <Route
              path="/master/produk-karton/form/:product_carton_id"
              element={<ProductCartonForm />}
            />

            {/* master data banner */}
            <Route path="/master/banner" element={<BannerList />} />
            <Route path="/master/banner/form" element={<FormBanner />} />
            <Route
              path="/master/banner/form/:banner_id"
              element={<FormBanner />}
            />

            {/* master data category */}
            <Route path="/master/category" element={<CategoryList />} />
            <Route path="/master/category/form" element={<FormCategory />} />
            <Route
              path="/master/category/form/:category_id"
              element={<FormCategory />}
            />

            {/* master data notif */}
            <Route path="/master/notification" element={<NotifList />} />
            <Route path="/master/notification/form" element={<FormNotif />} />
            <Route
              path="/master/notification/form/:notif_id"
              element={<FormNotif />}
            />

            {/* master data point */}
            <Route path="/master/point" element={<PointList />} />
            <Route path="/master/point/form" element={<PointForm />} />
            <Route
              path="/master/point/form/:master_point_id"
              element={<PointForm />}
            />

            {/* master data PACKAGE */}
            <Route path="/master/package" element={<PackageList />} />
            <Route path="/master/package/form" element={<PackageForm />} />
            <Route
              path="/master/package/form/:package_id"
              element={<PackageForm />}
            />

            {/* master data Payment Method */}
            <Route
              path="/master/payment-method"
              element={<PaymentMethodList />}
            />
            <Route
              path="/master/payment-method/form"
              element={<FormPaymentMethod />}
            />
            <Route
              path="/master/payment-method/form/:payment_method_id"
              element={<FormPaymentMethod />}
            />

            {/* shipping method */}
            <Route path="/master/online-logistic" element={<LogisticList />} />
            <Route
              path="/master/offline-logistic"
              element={<OfflineLogisticList />}
            />

            {/* master data Variant */}
            <Route path="/master/variant" element={<VariantList />} />
            <Route path="/master/variant/form" element={<VariantForm />} />
            <Route
              path="/master/variant/form/:variant_id"
              element={<VariantForm />}
            />
            {/* master data voucher */}
            <Route path="/master/voucher" element={<VoucherList />} />
            <Route path="/master/voucher/form" element={<FormVoucher />} />
            <Route
              path="/master/voucher/form/:voucher_id"
              element={<FormVoucher />}
            />

            {/* master data url shortener */}
            <Route path="/master/url-shortener" element={<UrlShortenerList />} />
            <Route path="/master/url-shortener/form" element={<UrlShortenerForm />} />
            <Route
              path="/master/url-shortener/form/:url_shortener_id"
              element={<UrlShortenerForm />}
            />

            {/* master data payment term */}
            <Route path="/master/payment-term" element={<PaymentTermList />} />
            <Route
              path="/master/payment-term/form"
              element={<PaymentTermForm />}
            />
            <Route
              path="/master/payment-term/form/:payment_term_id"
              element={<PaymentTermForm />}
            />
            {/* master data master tax */}
            <Route path="/master/tax" element={<MasterTaxList />} />
            <Route path="/master/tax/form" element={<MasterTaxForm />} />
            <Route
              path="/master/tax/form/:master_tax_id"
              element={<MasterTaxForm />}
            />

            {/* master data vendor */}
            <Route path="/master/vendor" element={<VendorList />} />
            <Route path="/master/vendor/form" element={<VendorForm />} />
            <Route path="/master/vendor/form/:id" element={<VendorForm />} />

            {/* master data checkbook */}
            <Route path="/master/checkbook" element={<CheckbookList />} />
            <Route path="/master/checkbook/form" element={<CheckbookForm />} />
            <Route
              path="/master/checkbook/form/:id"
              element={<CheckbookForm />}
            />

            {/* master data site id */}
            <Route path="/master/site-id" element={<MasterSiteIDList />} />
            <Route path="/master/site-id/form" element={<MasterSiteIDForm />} />
            <Route
              path="/master/site-id/form/:master_site_id"
              element={<MasterSiteIDForm />}
            />

            {/* master data batch id */}
            <Route path="/master/batch-id" element={<MasterBatchIDList />} />
            <Route
              path="/master/batch-id/form"
              element={<MasterBatchIDForm />}
            />
            <Route
              path="/master/batch-id/form/:master_batch_id"
              element={<MasterBatchIDForm />}
            />

            {/* master data master tax */}
            <Route path="/master/master-pph" element={<MasterPphList />} />
            <Route path="/master/master-pph/form" element={<MasterPphForm />} />
            <Route
              path="/master/master-pph/form/:master_pph_id"
              element={<MasterPphForm />}
            />

            {/* master data sku */}
            <Route path="/master/sku" element={<SkuList />} />
            <Route path="/master/sku/form" element={<SkuForm />} />
            <Route path="/master/sku/form/:sku_id" element={<SkuForm />} />
            {/* ticket master */}
            <Route path="/ticket" element={<TicketList />} />
            <Route path="/ticket/detail/:id" element={<TicketDetail />} />
            {/* master data warehouse */}
            <Route path="/master/warehouse" element={<MasterWarehouseList />} />
            <Route path="/master/warehouse/form" element={<WarehouseForm />} />
            <Route
              path="/master/warehouse/form/:warehouse_id"
              element={<WarehouseForm />}
            />
            {/* master bin */}
            <Route path="/master/bin" element={<MasterBinList />} />
            <Route path="/master/bin/form" element={<MasterBinForm />} />
            <Route
              path="/master/bin/form/:master_bin_id"
              element={<MasterBinForm />}
            />
            {/* master data master discount */}
            <Route
              path="/master/master-discount"
              element={<MasterDiscountList />}
            />
            <Route
              path="/master/master-discount/form"
              element={<MasterDiscountForm />}
            />
            <Route
              path="/master/master-discount/form/:master_discount_id"
              element={<MasterDiscountForm />}
            />
            {/* master data type case */}
            <Route path="/master/type-case" element={<TypeCaseList />} />
            <Route path="/master/type-case/form" element={<TypeCaseForm />} />
            <Route
              path="/master/type-case/form/:type_case_id"
              element={<TypeCaseForm />}
            />
            {/* master data category type case */}
            <Route
              path="/master/category-type-case"
              element={<CategoryTypeCaseList />}
            />
            <Route
              path="/master/category-type-case/form"
              element={<CategoryTypeCaseForm />}
            />
            <Route
              path="/master/category-type-case/form/:category_type_case_id"
              element={<CategoryTypeCaseForm />}
            />
            {/* master data status case */}
            <Route path="/master/status-case" element={<StatusCaseList />} />
            <Route
              path="/master/status-case/form"
              element={<StatusCaseForm />}
            />
            <Route
              path="/master/status-case/form/:status_case_id"
              element={<StatusCaseForm />}
            />
            {/* master data priority case */}
            <Route
              path="/master/priority-case"
              element={<PriorityCaseList />}
            />
            <Route
              path="/master/priority-case/form"
              element={<PriorityCaseForm />}
            />
            <Route
              path="/master/priority-case/form/:priority_case_id"
              element={<PriorityCaseForm />}
            />
            {/* master data source case */}
            <Route path="/master/source-case" element={<SourceCaseList />} />
            <Route
              path="/master/source-case/form"
              element={<SourceCaseForm />}
            />
            <Route
              path="/master/source-case/form/:source_case_id"
              element={<SourceCaseForm />}
            />
            {/* master data level */}
            <Route path="/master/level-price" element={<LevelList />} />
            <Route path="/master/level-price/form" element={<LevelForm />} />
            <Route
              path="/master/level-price/form/:level_id"
              element={<LevelForm />}
            />

            {/* master pengemasan */}
            <Route
              path="/master/pengemasan"
              element={<ProductAdditionalList type="pengemasan" />}
            />
            <Route
              path="/master/pengemasan/form"
              element={<ProductAdditionalForm type="pengemasan" />}
            />
            <Route
              path="/master/pengemasan/form/:product_additional_id"
              element={<ProductAdditionalForm type="pengemasan" />}
            />

            {/* master perlengkapan */}
            <Route
              path="/master/perlengkapan"
              element={<ProductAdditionalList type="perlengkapan" />}
            />
            <Route
              path="/master/perlengkapan/form"
              element={<ProductAdditionalForm type="perlengkapan" />}
            />
            <Route
              path="/master/perlengkapan/form/:product_additional_id"
              element={<ProductAdditionalForm type="perlengkapan" />}
            />

            {/* master sales-channel */}
            <Route
              path="/master/sales-channel"
              element={<SalesChannelList />}
            />
            <Route
              path="/master/sales-channel/form"
              element={<SalesChannelForm />}
            />
            <Route
              path="/master/sales-channel/form/:sales_channel_id"
              element={<SalesChannelForm />}
            />

            {/* master ongkir */}
            <Route path="/master/ongkir" element={<OngkirList />} />
            <Route path="/master/ongkir/form" element={<OngkirForm />} />
            <Route
              path="/master/ongkir/form/:master_ongkir_id"
              element={<OngkirForm />}
            />

            {/* master ongkir */}
            <Route path="/master/rate-limit" element={<RateLimitSetting />} />

            {/* role */}
            <Route path="/site-management/role" element={<RoleList />} />
            <Route path="/site-management/role/form" element={<FormRole />} />
            <Route
              path="/site-management/role/form/:role_id"
              element={<FormRole />}
            />

            {/* product */}
            <Route path="/product-management">
              <Route
                path="/product-management/product"
                element={<ProductMasterList />}
              />
              <Route
                path="/product-management/product/form"
                element={<ProductMasterForm />}
              />
              <Route
                path="/product-management/product/form/:product_id"
                element={<ProductMasterForm />}
              />
              <Route
                path="/product-management/product/stock-allocation/:product_id"
                element={<ProductStockAllocation />}
              />
            </Route>

            {/* product */}
            <Route path="/product-management">
              <Route
                path="/product-management/product-variant"
                element={<ProductVariantList />}
              />
              <Route
                path="/product-management/product-variant/form"
                element={<ProductVariantForm />}
              />
              <Route
                path="/product-management/product-variant/form/:product_variant_id"
                element={<ProductVariantForm />}
              />
            </Route>

            {/* product margin */}
            <Route path="/product-management">
              <Route
                path="/product-management/margin-bottom"
                element={<ProductMarginBottomList />}
              />
              <Route
                path="/product-management/margin-bottom/form"
                element={<ProductMarginBottomForm />}
              />
              <Route
                path="/product-management/margin-bottom/form/:product_margin_id"
                element={<ProductMarginBottomForm />}
              />
            </Route>

            {/* comment rating */}
            <Route path="/product-management">
              <Route
                path="/product-management/comment-rating"
                element={<ProductCommentRatingList />}
              />
            </Route>

            {/* import */}
            <Route path="/product-management">
              <Route
                path="/product-management/import-product"
                element={<ImportProductConvertList />}
              />
            </Route>

            {/* convert */}
            <Route path="/product-management">
              <Route
                path="/product-management/convert-product"
                element={<ConvertProductList />}
              />
            </Route>
            <Route path="/product-management">
              <Route
                path="/product-management/convert-product/detail/:convert_id"
                element={<ConvertProductDetailList />}
              />
            </Route>

            {/* setting */}
            {/* template notification */}
            <Route
              path="/setting/notification-template/group"
              element={<NotificationTemplateListGroup />}
            />
            <Route
              path="/setting/notification-template/list/:group_id"
              element={<NotificationTemplateList />}
            />
            <Route
              path="/setting/notification-template/form/:group_id"
              element={<NotificationTemplateForm />}
            />
            <Route
              path="/setting/notification-template/form/:group_id/:template_id"
              element={<NotificationTemplateForm />}
            />

            <Route path="/asset-control" element={<Asset />} />
            <Route path="/asset-control/form/:id" element={<AssetForm />} />

            {/* purchase order */}
            <Route path="/purchase">
              <Route
                path="/purchase/purchase-order"
                element={<PurchaseOrder />}
              />
              <Route
                path="/purchase/purchase-order/form"
                element={<PurchaseOrderForm />}
              />
              <Route
                path="/purchase/purchase-order/form/:purchase_order_id"
                element={<PurchaseOrderForm />}
              />
              <Route
                path="/purchase/purchase-order/detail/:purchase_order_id"
                element={<PurchaseOrderDetail />}
              />
              <Route
                path="/purchase/purchase-order-accurate"
                element={<PurchaseOrderAccurate />}
              />
            </Route>

            {/* purchase invoice entry */}
            <Route path="/purchase">
              <Route
                path="/purchase/invoice-entry"
                element={<PurchaseInvoiceEntry />}
              />
              <Route
                path="/purchase/invoice-entry/form"
                element={<PurchaseInvoiceEntryForm />}
              />
              <Route
                path="/purchase/invoice-entry/form/:purchase_invoice_entry_id"
                element={<PurchaseInvoiceEntryForm />}
              />
              <Route
                path="/purchase/invoice-entry/detail/:purchase_invoice_entry_id"
                element={<PurchaseInvoiceEntryDetail />}
              />
            </Route>

            {/* purchase requisition */}
            <Route path="/purchase">
              <Route
                path="/purchase/purchase-requisition"
                element={<PurchaseRequisition />}
              />
              <Route
                path="/purchase/purchase-requisition/form"
                element={<PurchaseRequisitionForm />}
              />
              <Route
                path="/purchase/purchase-requisition/form/:purchase_requisition_id"
                element={<PurchaseRequisitionForm />}
              />
              <Route
                path="/purchase/purchase-requisition/detail/:purchase_requisition_id"
                element={<PurchaseRequisitionDetail />}
              />
            </Route>

            {/* ORDER MARKETPLACE */}
            <Route path="/">
              <Route path="/marketplace/list" element={<ListOrderMP />} />
              <Route
                path="/marketplace/detail/:orderId"
                element={<DetailOrderMP />}
              />
            </Route>

            {/* stock movement */}
            <Route path="/">
              <Route path="/stock-movement" element={<StockMovement />} />
            </Route>

            {/* barcode */}
            <Route path="/barcode">
              <Route
                path="/barcode/on-production"
                element={<BarcodeMasterList stage={"on-production"} />}
              />
              <Route
                path="/barcode/inbound"
                element={<BarcodeMasterList stage={"inbound"} />}
              />
              <Route
                path="/barcode/transfer"
                element={<BarcodeMasterList stage={"transfer"} />}
              />
              <Route
                path="/barcode/outbound"
                element={<BarcodeMasterList stage={"outbound"} />}
              />
              <Route
                path="/barcode/detail/:barcode_id"
                element={<BarcodeMasterDetail type={"customer"} />}
              />
            </Route>

            {/* transaction */}
            <Route path="/transaction">
              {/* customer */}
              <Route
                path="/transaction/waiting-payment"
                element={<TransactionList stage={"waiting-payment"} />}
              />
              <Route
                path="/transaction/waiting-confirmation"
                element={<TransactionList stage={"waiting-confirmation"} />}
              />
              <Route
                path="/transaction/confirm-payment"
                element={<TransactionList stage={"confirm-payment"} />}
              />
              <Route
                path="/transaction/on-process"
                element={<TransactionList stage={"on-process"} />}
              />
              <Route
                path="/transaction/ready-to-ship"
                element={<TransactionList stage={"ready-to-ship"} />}
              />
              <Route
                path="/transaction/on-delivery"
                element={<TransactionList stage={"on-delivery"} />}
              />
              <Route
                path="/transaction/delivered"
                element={<TransactionList stage={"delivered"} />}
              />
              <Route
                path="/transaction/cancelled"
                element={<TransactionList stage={"cancelled"} />}
              />
              <Route
                path="/transaction/new-order"
                element={<TransactionList stage={"new-order"} />}
              />
              <Route
                path="/transaction/report-transaction"
                element={<TransactionList stage={"report-transaction"} />}
              />

              {/* agent */}
              <Route
                path="/transaction/agent/waiting-payment"
                element={
                  <TransactionList stage={"waiting-payment"} type={"agent"} />
                }
              />
              <Route
                path="/transaction/agent/waiting-confirmation"
                element={
                  <TransactionList
                    stage={"waiting-confirmation"}
                    type={"agent"}
                  />
                }
              />
              <Route
                path="/transaction/agent/confirm-payment"
                element={
                  <TransactionList stage={"confirm-payment"} type={"agent"} />
                }
              />
              <Route
                path="/transaction/agent/on-process"
                element={
                  <TransactionList stage={"on-process"} type={"agent"} />
                }
              />
              <Route
                path="/transaction/agent/ready-to-ship"
                element={
                  <TransactionList stage={"ready-to-ship"} type={"agent"} />
                }
              />
              <Route
                path="/transaction/agent/on-delivery"
                element={
                  <TransactionList stage={"on-delivery"} type={"agent"} />
                }
              />
              <Route
                path="/transaction/agent/delivered"
                element={<TransactionList stage={"delivered"} type={"agent"} />}
              />
              <Route
                path="/transaction/agent/cancelled"
                element={<TransactionList stage={"cancelled"} type={"agent"} />}
              />
              <Route
                path="/transaction/detail/:transaction_id"
                element={<TransactionDetail type={"customer"} />}
              />
              <Route
                path="/transaction/detail/new-order/:transaction_id"
                element={<TransactionDetailNewOrder type={"customer"} />}
              />
              <Route
                path="/transaction/detail/agent/:transaction_id"
                element={<TransactionDetail type={"agent"} />}
              />
            </Route>

            {/* transaction-telmart */}
            <Route path="/transaction-telmart">
              {/* customer */}
              <Route
                path="/transaction-telmart/waiting-payment"
                element={
                  <TransactionList type="telmart" stage={"waiting-payment"} />
                }
              />
              <Route
                path="/transaction-telmart/waiting-confirmation"
                element={
                  <TransactionList
                    type="telmart"
                    stage={"waiting-confirmation"}
                  />
                }
              />
              <Route
                path="/transaction-telmart/confirm-payment"
                element={
                  <TransactionList type="telmart" stage={"confirm-payment"} />
                }
              />
              <Route
                path="/transaction-telmart/on-process"
                element={
                  <TransactionList type="telmart" stage={"on-process"} />
                }
              />
              <Route
                path="/transaction-telmart/ready-to-ship"
                element={
                  <TransactionList type="telmart" stage={"ready-to-ship"} />
                }
              />
              <Route
                path="/transaction-telmart/on-delivery"
                element={
                  <TransactionList type="telmart" stage={"on-delivery"} />
                }
              />
              <Route
                path="/transaction-telmart/delivered"
                element={<TransactionList type="telmart" stage={"delivered"} />}
              />
              <Route
                path="/transaction-telmart/cancelled"
                element={<TransactionList type="telmart" stage={"cancelled"} />}
              />

              <Route
                path="/transaction-telmart/new-order"
                element={<TransactionList type="telmart" stage={"new-order"} />}
              />
              <Route
                path="/transaction-telmart/report-transaction"
                element={
                  <TransactionList
                    type="telmart"
                    stage={"report-transaction"}
                  />
                }
              />

              <Route
                path="/transaction-telmart/detail/:transaction_id"
                element={<TransactionDetail type={"telmart"} />}
              />
            </Route>

            {/* transaction-lms */}
            <Route path="/transaction-lms">
              {/* customer */}
              <Route
                path="/transaction-lms/waiting-payment"
                element={
                  <TransactionList type="lms" stage={"waiting-payment"} />
                }
              />
              <Route
                path="/transaction-lms/waiting-confirmation"
                element={
                  <TransactionList type="lms" stage={"waiting-confirmation"} />
                }
              />
              <Route
                path="/transaction-lms/confirm-payment"
                element={
                  <TransactionList type="lms" stage={"confirm-payment"} />
                }
              />
              <Route
                path="/transaction-lms/on-process"
                element={<TransactionList type="lms" stage={"on-process"} />}
              />
              <Route
                path="/transaction-lms/ready-to-ship"
                element={<TransactionList type="lms" stage={"ready-to-ship"} />}
              />
              <Route
                path="/transaction-lms/on-delivery"
                element={<TransactionList type="lms" stage={"on-delivery"} />}
              />
              <Route
                path="/transaction-lms/delivered"
                element={<TransactionList type="lms" stage={"delivered"} />}
              />
              <Route
                path="/transaction-lms/cancelled"
                element={<TransactionList type="lms" stage={"cancelled"} />}
              />

              <Route
                path="/transaction-lms/new-order"
                element={<TransactionList type="lms" stage={"new-order"} />}
              />
              <Route
                path="/transaction-lms/report-transaction"
                element={
                  <TransactionList type="lms" stage={"report-transaction"} />
                }
              />

              <Route
                path="/transaction-lms/detail/:transaction_id"
                element={<TransactionDetail type={"lms"} />}
              />
            </Route>

            {/* transaction-agent */}
            <Route path="/transaction-agent">
              {/* customer */}
              <Route
                path="/transaction-agent/new-order"
                element={
                  <TransactionAgentList
                    type="agent"
                    stage={"new-order"}
                    status={"0"}
                  />
                }
              />
              <Route
                path="/transaction-agent/waiting-confirmation"
                element={
                  <TransactionAgentList
                    type="agent"
                    stage={"waiting-confirmation"}
                    status={"1"}
                  />
                }
              />

              <Route
                path="/transaction-agent/on-delivery"
                element={
                  <TransactionAgentList
                    type="agent"
                    stage={"on-delivery"}
                    status={"2"}
                  />
                }
              />
              <Route
                path="/transaction-agent/completed"
                element={
                  <TransactionAgentList
                    type="agent"
                    stage={"completed"}
                    status={"3"}
                  />
                }
              />
              <Route
                path="/transaction-agent/cancelled"
                element={
                  <TransactionAgentList
                    type="agent"
                    stage={"cancelled"}
                    status={"4"}
                  />
                }
              />
              <Route
                path="/transaction-agent/delivered"
                element={
                  <TransactionAgentList
                    type="agent"
                    stage={"delivered"}
                    status={"5"}
                  />
                }
              />
              <Route
                path="/transaction-agent/sales-invoice"
                element={
                  <TransactionAgentList
                    type="agent"
                    stage={"sales-invoice"}
                    status={"6"}
                  />
                }
              />

              <Route
                path="/transaction-agent/report-transaction"
                element={
                  <TransactionAgentList
                    type="agent"
                    stage={"report-transaction"}
                  />
                }
              />

              <Route
                path="/transaction-agent/detail/:transaction_id"
                element={<TransactionAgentDetail type={"agent"} />}
              />
            </Route>

            {/* withdraw */}
            <Route
              path="/comission-withdraw"
            // element={<ComissionWithdraw />}
            >
              <Route
                path="/comission-withdraw"
                element={<ComissionWithdraw />}
              />
              <Route
                path="/comission-withdraw/detail"
                element={<ComissionWithdrawDetail />}
              />
              <Route
                path="/comission-withdraw/form"
                element={<ComissionWithdrawForm />}
              />
              <Route
                path="/comission-withdraw/form/:commission_id"
                element={<ComissionWithdrawForm />}
              />
            </Route>

            {/* bin */}
            <Route path="/bin">
              <Route path="/bin/list" element={<BinList />} />
              <Route
                path="/bin/detail/:product_variant_id"
                element={<BinListDetail />}
              />
            </Route>

            {/* barcode */}
            <Route path="/barcode">
              <Route path="/barcode/list" element={<BarcodeList />} />
              <Route
                path="/barcode/detail/:barcode_id"
                element={<BarcodeDetail />}
              />
            </Route>
          </Routes>
        </Suspense>
      </Router>
      </div>
    </ConfigProvider>
  )
}

const themes = {
  dark: "/../../assets/css/dark-theme.css",
  light: "/../../assets/css/light-theme.css",
}

if (document.getElementById("spa-index")) {
  const contactRoot = ReactDOM.createRoot(document.getElementById("spa-index"))
  contactRoot.render(
    <React.StrictMode>
      <Provider store={store}>
        <ThemeSwitcherProvider
          themeMap={themes}
          defaultTheme={localStorage.getItem("theme")}
        >
          <App />
        </ThemeSwitcherProvider>
      </Provider>
    </React.StrictMode>
  )
}
