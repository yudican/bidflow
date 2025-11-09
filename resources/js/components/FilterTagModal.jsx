import { Button, Input, Modal, Pagination, Table, Tag, Tooltip } from "antd"
import axios from "axios"
import React, { useEffect, useState } from "react"
import { paginateData, truncateString } from "../helpers"

const WarehouseColumn = [
  {
    title: "No.",
    dataIndex: "number",
    key: "number",
    // render: (value, row, index) => index + 1,
  },
  {
    title: "Kode WH",
    dataIndex: "wh_id",
    key: "wh_id",
    render: (value) => {
      return value || "-"
    },
    sorter: (a, b) => {
      // because format id is WH-005
      const numA = parseInt(a?.wh_id?.split("-")[1])
      const numB = parseInt(b?.wh_id?.split("-")[1])

      return numA - numB
    },
  },
  {
    title: "Nama Warehouse",
    dataIndex: "name",
    key: "name",
  },
]

const BinColumn = [
  {
    title: "No.",
    dataIndex: "number",
    key: "number",
    // render: (value, row, index) => index + 1,
  },
  {
    title: "Nama",
    dataIndex: "name",
    key: "name",
    render: (value) => <span className="line-clamp-1">{value || "-"}</span>,
  },
  {
    title: "Alamat",
    dataIndex: "alamat",
    key: "alamat",
    render: (value) => (
      <Tooltip title={value}>
        <span className="line-clamp-1">{truncateString(value || "-", 60)}</span>
      </Tooltip>
    ),
  },
]

const FilterTagModal = ({ handleOk, isBin }) => {
  let fieldName = isBin ? "master_bin_id" : "warehouse_ids"

  const [allSelectedKeys, setAllSelectedKeys] = useState(["all"])
  const [selectedRowKeys, setSelectedRowKeys] = useState(["all"])
  const [selectedTagKeys, setselectedTagKeys] = useState(["all"])
  const [selectedWarehouseData, setSelectedWarehouseData] = useState({})

  const [isFilterWarehouseTagOpen, setIsFilterWarehouseTagOpen] =
    useState(false)
  const [filter, setFilter] = useState({})
  const [datas, setDatas] = useState([])
  const [currentPage, setCurrentPage] = useState(1)
  const [perPage, setPerpage] = useState(10)
  const [total, setTotal] = useState(0)
  const [search, setSearch] = useState("")
  const [loading, setLoading] = useState(false)

  const loadData = (
    url = isBin ? "/api/master/master-bin" : "/api/master/warehouse",
    perpage = perPage,
    params = { page: currentPage }
  ) => {
    setLoading(true)

    axios
      .post(url, { perpage, ...params })
      .then((res) => {
        setLoading(false)
        const { data, current_page, total, from } = res.data.data
        setCurrentPage(current_page)
        setTotal(total)

        const adjustedFrom = search ? 0 : from

        let newData = data?.map((wh, index) => {
          const number = adjustedFrom + index + 1
          return {
            number,
            key: wh.id,
            ...wh,
          }
        })

        const finalData =
          current_page == 1 && !search
            ? [
                {
                  number: 1,
                  key: "all",
                  id: "all",
                  name: `${isBin ? "All Bin" : "Semua Warehouse"}`,
                },
                ...newData,
              ]
            : [...newData]

        setDatas(finalData)

        // Update selectedRowKeys berdasarkan allSelectedKeys
        setSelectedRowKeys(
          allSelectedKeys.filter((key) =>
            finalData.some((item) => item.key === key)
          )
        )
      })
      .catch(() => setLoading(false))
  }

  const handleChange = (page, pageSize = 10) => {
    setCurrentPage(page)
    loadData(
      `/api/master/${isBin ? "master-bin" : "warehouse"}/?page=${page}`,
      pageSize,
      {
        search,
        page,
        ...filter,
      }
    )
  }

  useEffect(() => {
    loadData()
  }, [])

  // Tambahkan useEffect untuk memuat data warehouse yang sudah dipilih sebelumnya
  useEffect(() => {
    if (selectedTagKeys.length > 0 && selectedTagKeys[0] === "all") {
      setSelectedWarehouseData({
        all: `${isBin ? "All Bin" : "Semua Warehouse"}`,
      })
    } else if (selectedTagKeys.length > 0) {
      // Load data untuk tag yang dipilih jika belum ada di selectedWarehouseData
      selectedTagKeys.forEach((key) => {
        if (!selectedWarehouseData[key]) {
          axios
            .get(`/api/master/${isBin ? "master-bin" : "warehouse"}/${key}`)
            .then((res) => {
              if (res.data?.data) {
                setSelectedWarehouseData((prev) => ({
                  ...prev,
                  [key]: res.data.data.name,
                }))
              }
            })
        }
      })
    }
  }, [selectedTagKeys])

  const handleChangeFilter = (value, field) => {
    setFilter((filters) => ({ ...filters, [field]: value }))
  }

  // old function
  // const onSelectChange = (newSelectedRowKeys) => {
  //   setSelectedRowKeys(newSelectedRowKeys)
  //   handleChangeFilter(newSelectedRowKeys, "warehouse_ids")
  // }

  // new function
  const onSelectRow = (record, selected, selectedRows) => {
    const isAllSelected = record.key === "all" && selected
    const newSelectedRowKeys = selectedRows
      .filter((row) => row)
      .map((value) => value.key)
    const selectedRowKeysWithNoAll = newSelectedRowKeys.filter(
      (item) => item !== "all"
    )

    if (isAllSelected) {
      setAllSelectedKeys(["all"])
      setSelectedRowKeys(["all"])
      setSelectedWarehouseData({
        all: `${isBin ? "All Bin" : "Semua Warehouse"}`,
      })
      handleChangeFilter(["all"], fieldName)
    } else if (newSelectedRowKeys.includes("all")) {
      setAllSelectedKeys(selectedRowKeysWithNoAll)
      setSelectedRowKeys(selectedRowKeysWithNoAll)
      // Hapus "all" dari data yang disimpan
      const { all, ...restData } = selectedWarehouseData
      setSelectedWarehouseData(restData)
      handleChangeFilter(selectedRowKeysWithNoAll, fieldName)
    } else {
      const updatedKeys = selected
        ? [...allSelectedKeys, record.key]
        : allSelectedKeys.filter((key) => key !== record.key)

      // Update data warehouse yang dipilih
      if (selected) {
        setSelectedWarehouseData((prev) => ({
          ...prev,
          [record.key]: record.name,
        }))
      } else {
        const { [record.key]: removed, ...rest } = selectedWarehouseData
        setSelectedWarehouseData(rest)
      }

      setAllSelectedKeys(updatedKeys)
      setSelectedRowKeys(newSelectedRowKeys)
      handleChangeFilter(updatedKeys, fieldName)
    }
  }

  const rowSelection = {
    selectedRowKeys,
    // onChange: onSelectChange,
    onSelect: onSelectRow,
    getCheckboxProps: (record) => {
      // console.log(record, "record")
      // return {
      //   disabled: record.key === "all",
      //   // Column configuration not to be checked
      //   name: record.name,
      // }
    },
  }

  const hasSelected = selectedRowKeys.length > 0

  const onOk = () => {
    setIsFilterWarehouseTagOpen(false)
    setselectedTagKeys(allSelectedKeys)
    setSelectedRowKeys([])
    setAllSelectedKeys([])
    // Jangan reset selectedWarehouseData di sini
    handleOk(filter)
  }

  const handleCloseTag = (removedTag) => {
    const newTags = selectedTagKeys.filter((tag) => tag !== removedTag)
    setselectedTagKeys(newTags)
    // Hapus data warehouse yang dihapus
    const { [removedTag]: removed, ...restData } = selectedWarehouseData
    setSelectedWarehouseData(restData)
    handleOk({ [fieldName]: newTags })
  }
  // Update fungsi warehouseNameByKey
  const warehouseNameByKey = (key) => {
    return (
      selectedWarehouseData[key] ||
      datas.find((value) => value.id === key)?.name ||
      "-"
    )
  }

  const columns = isBin ? BinColumn : WarehouseColumn

  return (
    <div className="bg-[#F4F4F4] p-2 mb-4">
      <>
        Nama {isBin ? "Bin" : "Warehouse"}:{" "}
        {selectedTagKeys?.map((value) => {
          let key = value
          return (
            <Tag
              key={key}
              color="blue"
              closable
              onClose={() => handleCloseTag(key)}
            >
              {warehouseNameByKey(key)}
            </Tag>
          )
        })}
        <Button
          onClick={(e) => {
            e.preventDefault()
            setIsFilterWarehouseTagOpen(true)
          }}
        >
          Pilih Tag
        </Button>
      </>
      <Modal
        title={isBin ? "Silakan Pilih Tag Bin" : "Silakan Pilih Tag Warehouse"}
        open={isFilterWarehouseTagOpen}
        okText={"Simpan"}
        onOk={onOk}
        onCancel={() => {
          setIsFilterWarehouseTagOpen(false)
          setSelectedRowKeys([])
        }}
        cancelButtonProps={{ style: { display: "none" } }}
        bodyStyle={{ height: "32rem", overflowY: "auto" }}
        width={isBin && "60%"}
      >
        <Input.Search
          className="mb-4"
          placeholder={isBin ? "Cari Nama Bin" : "Cari Nama Warehouse"}
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          onPressEnter={() => {
            loadData(`/api/master/${isBin ? "master-bin" : "warehouse"}`, 10, {
              search,
            })
          }}
          onSearch={() => {
            loadData(`/api/master/${isBin ? "master-bin" : "warehouse"}`, 10, {
              search,
            })
          }}
        />

        {hasSelected ? `Selected ${selectedRowKeys.length} items` : ""}

        <Table
          loading={loading}
          rowSelection={rowSelection}
          dataSource={datas}
          columns={columns}
          pagination={false}
        />
        <Pagination
          defaultCurrent={1}
          current={currentPage}
          total={total}
          className="mt-2 text-center"
          onChange={handleChange}
          pageSizeOptions={["10", "20"]}
          onShowSizeChange={(current, size) => {
            setCurrentPage(current)
            setPerpage(size)
          }}
        />
      </Modal>
    </div>
  )
}

export default FilterTagModal
