import React, { useEffect, useState } from "react"
import DebounceSelect from "../../../components/atoms/DebounceSelect"
import { searchBin } from "../service"

const FilterBin = ({ onChange, selected }) => {
  const [binList, setBinList] = useState([])

  const handleGetBin = () => {
    searchBin(null).then((results) => {
      const newResult = results.map((result) => {
        return { label: result.nama, value: result.id }
      })
      setBinList(newResult)
    })
  }

  const handleSearchBin = async (e) => {
    return searchBin(e).then((results) => {
      const newResult = results.map((result) => {
        return { label: result.nama, value: result.id }
      })

      return newResult
    })
  }

  useEffect(() => {
    handleGetBin()
  }, [])

  return (
    <DebounceSelect
      defaultOptions={binList}
      showSearch
      placeholder="Cari Destinasi BIN"
      fetchOptions={handleSearchBin}
      filterOption={false}
      value={selected}
      className="w-full"
      onChange={(value) => {
        onChange(value)
      }}
    />
  )
}

export default FilterBin
