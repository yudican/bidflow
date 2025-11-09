import { Switch, Table } from "antd"
import React from "react"
import { agentListColumns, DragHandle } from "../config"
import { arrayMoveImmutable } from "array-move"
import { SortableContainer, SortableElement } from "react-sortable-hoc"
import axios from "axios"
import { inArray } from "../../../helpers"

const SortableItem = SortableElement((props) => <tr {...props} />)
const SortableBody = SortableContainer((props) => <tbody {...props} />)
const AgentListTable = ({
  dataSource,
  handleChangeCell,
  loading = false,
  refetch,
  columns = agentListColumns,
}) => {
  const mergedColumns = columns.map((col) => {
    return {
      ...col,
      onCell: (record) => ({
        record,
        dataIndex: col.dataIndex,
        handleChange: (val) => handleChangeCell(val),
      }),
    }
  })

  const onSortEnd = ({ oldIndex, newIndex }) => {
    if (oldIndex !== newIndex) {
      const newData = arrayMoveImmutable(
        dataSource.slice(),
        oldIndex,
        newIndex
      ).filter((el) => !!el)
      console.log("Sorted items: ", newData)
      //   setDataSource(newData);
      const sorted = newData.map((item, index) => {
        return {
          value: index + 1,
          key: item.key,
        }
      })
      console.log(sorted, "sorted")
      axios.post("/api/agent/re-order", { data: sorted }).then((res) => {
        refetch()
      })
    }
  }

  const DraggableContainer = (props) => (
    <SortableBody
      useDragHandle
      disableAutoscroll
      helperClass="row-dragging"
      onSortEnd={onSortEnd}
      {...props}
    />
  )

  const DraggableBodyRow = ({ className, style, ...restProps }) => {
    // function findIndex base on Table rowKey props and should always be a right array index
    const index = dataSource.findIndex(
      (x) => x.index === restProps["data-row-key"]
    )
    return <SortableItem index={index} {...restProps} />
  }

  return (
    <Table
      components={{
        body: {
          cell: EditableCell,
          wrapper: DraggableContainer,
          row: DraggableBodyRow,
        },
      }}
      loading={loading}
      columns={mergedColumns}
      dataSource={dataSource}
      rowKey="index"
      pagination={false}
      scroll={{ x: "max-content" }}
      tableLayout={"auto"}
    />
  )
}

const EditableCell = (props) => {
  const { dataIndex, handleChange, record, className } = props
  if (record) {
    if (inArray(dataIndex, ["status_agent", "libur", "active"])) {
      return (
        <td>
          <Switch
            checked={record[dataIndex]}
            onChange={(e) =>
              handleChange({
                value: e,
                dataIndex,
                key: record.key,
              })
            }
          />
        </td>
      )
    }
    if (dataIndex === "sort") {
      return (
        <td className={className}>
          <DragHandle />
        </td>
      )
    }

    return <td className={className}>{record[dataIndex]}</td>
  }
  return (
    <td colSpan={6} className="text-center">
      Tidak Ada Data
    </td>
  )
}

export default AgentListTable
