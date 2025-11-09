import { CloseCircleFilled, SearchOutlined } from "@ant-design/icons"
import { Button, Menu, Dropdown, Input, Pagination, Table } from "antd"
import { EyeOutlined, RightOutlined } from "@ant-design/icons";
import axios from "axios"
import CryptoJS from "crypto-js"
import React, { useEffect, useState } from "react"
import { useNavigate } from "react-router-dom"
import { toast } from "react-toastify"
import Layout from "../../components/layout"
import { formatNumber, getItem } from "../../helpers"
import { getOrderStatus, purchaseOrderListColumn } from "./configacc"

const PurchaseOrderAccurate = () => {
  const navigate = useNavigate()

  const [loading, setLoading] = useState(false)
  const [purchaseOrderList, setPurchaseOrderList] = useState([])
  const [searchPurchaseOrderList, setSearchPurchaseOrderList] = useState(null)
  const [total, setTotal] = useState(0)
  const [search, setSearch] = useState("")
  const [isSearch, setIsSearch] = useState(false)
  const [currentPage, setCurrentPage] = useState(1)
  const [perPage, setPerpage] = useState(10)

  const loadData = (
    url = "/api/purchase/purchase-order-accurate",
    perpage = perPage,
    params = { page: currentPage }
  ) => {
    setLoading(true)
    axios
      .post(url, { perpage, account_id: getItem("account_id"), ...params })
      .then((res) => {
        const { data, total, from } = res.data.data
        setTotal(total)

        const newData = data.map((item, index) => {
          const number = from + index
          return {
            ...item,
            number,
            order: getOrderStatus(item.status),
            created_by: item?.created_by_name,
            total_tax: item?.tax_amount ?? 0,
            total: item.total_amount,
          }
        })

        setPurchaseOrderList(newData)
        setLoading(false)
      })
      .catch(() => setLoading(false))
  }

  const handleSyncAccurate = async () => {
    setLoading(true)
    // try {
    //   const timestamp = Date.now().toString()
    //   const secretKey =
    //     "be7oZxpiPDXooS4ra2Hut3aLhB74lUi9yblxC2DKGPO2Mt7DhqhGttpKj57rnWnY"
    //   const signature = CryptoJS.HmacSHA256(timestamp, secretKey).toString()

    //   const response = await axios.get(
    //     "https://zeus.accurate.id/accurate/api/purchase-order/list.do?fields=id,number,approvalStatus,availableDownPayment,branch,cashDiscPercent,cashDiscount,charField1,charField10,charField2,charField3,charField4,charField5,charField6,charField7,charField8,charField9,createdByUserName,currency,dateField1,dateField2,description,dppAmount,fob,id,inclusiveTax,lastUpdate,number,numericField1,numericField10,numericField2,numericField3,numericField4,numericField5,numericField6,numericField7,numericField8,numericField9,orderPrintedTime,paymentTerm,printedByUser,rate,shipDate,shipment,status,statusName,tax1Amount,tax2Amount,tax3Amount,tax4Amount,taxable,totalAmount,totalDownPayment,totalDownPaymentUsed,totalExpense,transDate,vendor",
    //     {
    //       headers: {
    //         Authorization:
    //           "Bearer aat.NTA.eyJ2IjoxLCJ1Ijo4MDQ5MzQsImQiOjE1OTYwNjQsImFpIjo1MTk2MCwiYWsiOiIyMTQxMmMzNS0wYmI2LTRiMDgtOWY4Mi03YjJhNzg0NDcwNzgiLCJhbiI6Ik9SQ0EgRkxJTUdST1VQIiwiYXAiOiI3MzU3ZTZjNC0xOGJmLTRiYjUtYTM5My05OTVjNWViZGIyYzQiLCJ0IjoxNzM3MDAzNzI5MzY3fQ.l95oW0P0BvFZlyg56v2mUMRryVBGJfpKO82FZQiiSRAhTyE6Tfrgwgc4LOqaK0VgDsAgkbANdJGD8bGJLsIYzct82oNjGcS+kbbYy8O3CKi0BUATKgy2JGBJ5KOun6Wq0WQE0e7e0S+LRytI+FK8FubdFOjZREsbdCl3Z9aoIVrYu57MhB7TIMREaQ2z1LtBExdXBVM6jrg=.Y4Wpfa+BUAvelo9jGxoVcsXZh91ny7ASrcR0PVPMyJc",
    //         "x-api-timestamp": timestamp,
    //         "x-api-signature": signature,
    //       },
    //     }
    //   )

    //   if (!response.data.s) {
    //     throw new Error("API Error: " + JSON.stringify(response.data.d))
    //   }

    //   const { data } = response.data
    //   const saveResponse = await axios.post(
    //     "/api/purchase-order-accurate/save",
    //     { data }
    //   )

    //   if (saveResponse.data.newDataCount > 0) {
    //     toast.success(
    //       `Data berhasil diproses. ${saveResponse.data.newDataCount} data baru ditambahkan.`
    //     )
    //   } else {
    //     toast.info("Tidak ada data baru.")
    //   }

    //   loadData()
    // } catch (error) {
    //   console.error("Sync Error:", error)
    //   toast.error("Gagal melakukan sinkronisasi dengan Accurate.")
    // } finally {
    //   setLoading(false)
    // }
    try {
      setLoading(true); // Pastikan loading diaktifkan saat proses mulai
      const response = await axios.post('/api/purchase/purchase-order-accurate-sync/');
      console.log('Sync berhasil:', response.data);

      toast.success(
        `Sync Accurate berhasil diproses. ${response.data.new_records || 0} data baru ditambahkan.`
      );
    } catch (error) {
      console.error('Error:', error.response?.data || error.message);
      toast.error("Gagal melakukan sinkronisasi dengan Accurate.");
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    loadData()
  }, [])

  const handleChange = (page, pageSize = 10) => {
    setCurrentPage(page)
    loadData(`/api/purchase/purchase-order-accurate/?page=${page}`, pageSize, {
      search,
      page,
    })
  }

  const handleChangeSearch = () => {
    setIsSearch(true)
    loadData(`/api/purchase/purchase-order-accurate`, 10, { search })
  }

  const listActions = [
    {
      title: "Action",
      key: "id",
      align: "center",
      fixed: "right",
      width: 100,
      render: (text, record) => {
        return (
          <Dropdown.Button
            style={{
              left: -16,
            }}
            overlay={
              <Menu itemIcon={<RightOutlined />}>
                <Menu.Item
                  icon={<EyeOutlined />}
                  onClick={() => navigate(`detail/${text.id_acc}`)}
                >
                  Detail
                </Menu.Item>
              </Menu>
            }
          ></Dropdown.Button>
        )
      },
    },
  ]

  const columns = purchaseOrderListColumn

  return (
    <Layout
      title="List Purchase Order Accurate"
      rightContent={
        <div className="flex justify-between items-center">
          <Button type="primary" onClick={handleSyncAccurate} loading={loading}>
            Sync Get Accurate
          </Button>
        </div>
      }
    >
      <div className="card">
        <div className="card-body">
          <div className="row mb-4">
            <div className="col-md-4 col-sm-6 col-12">
              <Input
                placeholder="Cari disini"
                size={"large"}
                className="rounded"
                onPressEnter={handleChangeSearch}
                suffix={
                  isSearch ? (
                    <CloseCircleFilled
                      onClick={() => {
                        setSearch("")
                        setIsSearch(false)
                      }}
                    />
                  ) : (
                    <SearchOutlined onClick={handleChangeSearch} />
                  )
                }
                value={search}
                onChange={(e) => setSearch(e.target.value)}
              />
            </div>
            <div className="col-md-8">
              <strong className="float-right text-red-400">
                Total Data: {formatNumber(total)}
              </strong>
            </div>
          </div>

          {/* <Table
            dataSource={searchPurchaseOrderList || purchaseOrderList}
            columns={purchaseOrderListColumn}
            loading={loading}
            pagination={false}
            rowKey="id"
          /> */}
          <Table
            // rowSelection={rowSelection}
            dataSource={searchPurchaseOrderList || purchaseOrderList}
            columns={[...purchaseOrderListColumn, ...listActions]}
            loading={loading}
            pagination={false}
            rowKey="id"
            scroll={{ x: "max-content" }}
            tableLayout={"auto"}
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

export default PurchaseOrderAccurate
