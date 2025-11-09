import { Tag } from "antd"

const discountListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Title",
    dataIndex: "title",
    key: "title",
  },
  {
    title: "Discount Percentage",
    dataIndex: "percentage",
    key: "percentage",
  },
  {
    title: "Sales Tag",
    dataIndex: "sales_tag",
    key: "sales_tag",
    render: (text) => {
      try {
        const salesTag = {
          corner: "Corner",
          "agent-portal": "Agent Portal",
          distributor: "Distributor",
          "super-agent": "Super Agent",
          "modern-store": "Modern Store",
          "e-store": "E-Store",
        }

        // Mengembalikan teks yang sesuai atau teks default jika tidak ada kecocokan
        return salesTag[text] || text
      } catch (error) {
        // Penanganan error jika terjadi kesalahan
        return text
      }
    },
  },
  {
    title: "Sales Channel",
    dataIndex: "sales_channel",
    key: "sales_channel",
    render: (text, record, index) => {
      if (text) {
        return record.sales_channels.map((item) => (
          <Tag key={item} color="green">
            {item.replace("-", " ")}
          </Tag>
        ))
      }

      return "-"
    },
  },
]

export { discountListColumn }
