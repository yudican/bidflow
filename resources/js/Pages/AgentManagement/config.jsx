import { MenuOutlined } from "@ant-design/icons"
import { SortableHandle } from "react-sortable-hoc"
import "../../index.css"
const DragHandle = SortableHandle(() => (
  <MenuOutlined
    style={{
      cursor: "grab",
      color: "#999",
    }}
  />
))
const agentListColumns = [
  {
    title: "Sort",
    dataIndex: "sort",
    width: 30,
    className: "drag-visible",
  },
  {
    title: "Nama",
    dataIndex: "nama",
    className: "drag-visible",
    key: "nama",
  },
  {
    title: "Telepon",
    dataIndex: "telepon",
    key: "telepon",
  },
  {
    title: "Alamat",
    dataIndex: "alamat",
    key: "alamat",
  },
  {
    title: "Libur",
    dataIndex: "libur",
    key: "libur",
  },
  {
    title: "Active",
    dataIndex: "active",
    key: "active",
  },
]

const agentListDomainColumns = [
  {
    title: "No.",
    dataIndex: "no",
    key: "no",
    render: (text, record, index) => {
      return text + 1
    },
  },
  {
    title: "Status",
    dataIndex: "status_agent",
    key: "status_agent",
  },
  {
    title: "Nama",
    dataIndex: "nama",
    className: "drag-visible",
    key: "nama",
  },
  {
    title: "Telepon",
    dataIndex: "telepon",
    key: "telepon",
  },
  {
    title: "Alamat",
    dataIndex: "alamat",
    key: "alamat",
  },
]

const domainListColumns = [
  {
    title: "Nama Domain",
    dataIndex: "name",
    key: "name",
  },
  {
    title: "Icon",
    dataIndex: "icon_url",
    key: "icon_url",
    render: (text) => <img src={text} style={{ height: 30 }} alt="icon" />,
  },
  {
    title: "Url",
    dataIndex: "url",
    key: "url",
  },
  {
    title: "Status",
    dataIndex: "status",
    key: "status",
  },
  {
    title: "Aksi",
    dataIndex: "id",
    key: "id",
  },
]

const agentDomainListColumns = [
  {
    title: "Nama Domain",
    dataIndex: "name",
    key: "name",
  },
  {
    title: "Status",
    dataIndex: "status_agent",
    key: "status_agent",
  },
]

const agentProvinceListColumns = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Nama Provinsi",
    dataIndex: "nama",
    key: "nama",
  },
]

const agentCityListColumns = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Nama Kota/Kabupaten",
    dataIndex: "nama",
    key: "nama",
  },
]

export {
  agentListColumns,
  DragHandle,
  domainListColumns,
  agentDomainListColumns,
  agentProvinceListColumns,
  agentCityListColumns,
  agentListDomainColumns,
}
