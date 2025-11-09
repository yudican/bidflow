import { FolderFilled } from "@ant-design/icons"
import { Modal, Table } from "antd"
import React, { useState } from "react"
import { menuColumns } from "../config"
import { arrayMoveImmutable } from "array-move"
import { SortableContainer, SortableElement } from "react-sortable-hoc"
import { toast } from "react-toastify"

const SortableItem = SortableElement((props) => <tr {...props} />)
const SortableBody = SortableContainer((props) => <tbody {...props} />)

const SubmenuModal = ({
  actionColumns = [],
  dataSource = [],
  loading = false,
  hasChildren = false,
  title = "Submenu",
  refetch,
}) => {
  const [isModalOpen, setIsModalOpen] = useState(false)

  const showModal = () => {
    setIsModalOpen(true)
  }

  const onSortEnd = ({ oldIndex, newIndex }) => {
    if (oldIndex !== newIndex) {
      const newData = arrayMoveImmutable(
        dataSource.slice(),
        oldIndex,
        newIndex
      ).filter((el) => !!el)

      const sorted = newData.map((item, index) => {
        return {
          value: index + 1,
          id: item.id,
        }
      })
      // console.log("Sorted items: ", newData);
      // setMenus(newData);
      //   setDataSource(newData);
      axios.post("/api/menu/order", { menus: sorted }).then((res) => {
        const { message } = res.data
        refetch()
        toast.success(message, {
          position: toast.POSITION.TOP_RIGHT,
        })
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
    <div>
      {hasChildren ? (
        <button
          onClick={() => showModal()}
          className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center"
        >
          <FolderFilled />
          <span className="ml-2">Submenu</span>
        </button>
      ) : (
        <button className="text-white bg-gray-400 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center">
          <FolderFilled />
          <span className="ml-2">Submenu</span>
        </button>
      )}

      <Modal
        title={title}
        open={isModalOpen}
        cancelText={"Tutup"}
        onCancel={() => setIsModalOpen(false)}
        okButtonProps={{ style: { display: "none" } }}
        width={1000}
      >
        <Table
          components={{
            body: {
              wrapper: DraggableContainer,
              row: DraggableBodyRow,
            },
          }}
          dataSource={dataSource}
          columns={[...menuColumns, ...actionColumns]}
          loading={loading}
          pagination={false}
          rowKey="index"
          scroll={{ x: "max-content" }}
          tableLayout={"auto"}
        />
      </Modal>
    </div>
  )
}

export default SubmenuModal
