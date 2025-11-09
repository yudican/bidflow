import { Empty, Select, Spin } from "antd"
import debounce from "lodash.debounce"
import React, { useMemo, useRef, useState } from "react"

function DebounceSelect({
  fetchOptions,
  debounceTimeout = 800,
  defaultOptions = [],
  value = null,
  ...props
}) {
  const [fetching, setFetching] = useState(false)
  const [options, setOptions] = useState(defaultOptions || [])
  const fetchRef = useRef(0)
  const debounceFetcher = useMemo(() => {
    const loadOptions = (value) => {
      fetchRef.current += 1
      const fetchId = fetchRef.current
      setOptions([])
      setFetching(true)
      fetchOptions(value).then((newOptions) => {
        if (fetchId !== fetchRef.current) {
          // for fetch callback order
          return
        }
        setOptions(newOptions)
        setFetching(false)
      })
    }
    return debounce(loadOptions, debounceTimeout)
  }, [fetchOptions, debounceTimeout])

  const newOption = options.length === 0 && !fetching ? defaultOptions : options

  return (
    <Select
      labelInValue
      filterOption={false}
      onSearch={debounceFetcher}
      // notFoundContent={fetching ? <Spin size="small" /> : null}
      notFoundContent={
        fetching ? (
          <center>
            <Spin size="default" />
          </center>
        ) : (
          <Empty />
        )
      }
      {...props}
      // options={newOption.filter((option) => !option.selected)}
      options={newOption}
      value={value}
    />
  )
} // Usage of DebounceSelect

export default DebounceSelect
