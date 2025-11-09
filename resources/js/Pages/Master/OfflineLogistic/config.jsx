const logisticListColumn = [
  {
    title: "No.",
    dataIndex: "number",
    key: "number",
  },
  {
    title: "Nama",
    dataIndex: "logistic_name",
    key: "logistic_name",
  },
  {
    title: "Logo",
    dataIndex: "logistic_url_logo",
    key: "logistic_url_logo",
    render: (text, record, index) => (
      <img src={text} alt="banner_image" width="100" height="100" />
    ),
  },
]

const logisticRatesListColumn = [
  {
    title: "No.",
    dataIndex: "id",
    key: "id",
    render: (text, record, index) => index + 1,
  },
  {
    title: "Kode Servis",
    dataIndex: "logistic_rate_code",
    key: "logistic_rate_code",
  },
  {
    title: "Nama Servis",
    dataIndex: "logistic_rate_name",
    key: "logistic_rate_name",
  },
]

export { logisticListColumn, logisticRatesListColumn }
