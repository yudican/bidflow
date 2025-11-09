const bannerListColumn = [
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
    title: "Brand",
    dataIndex: "brand_name",
    key: "brand_name",
  },
  {
    title: "Image",
    dataIndex: "banner_image",
    key: "banner_image",
    render: (text, record, index) => (
      <img
        src={record.banner_image}
        alt="banner_image"
        width="100"
        height="100"
      />
    ),
  },
];

export { bannerListColumn };
