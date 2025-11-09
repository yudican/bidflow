import { MenuOutlined } from "@ant-design/icons";
import { SortableHandle } from "react-sortable-hoc";

const DragHandle = SortableHandle(() => (
    <MenuOutlined
        style={{
            cursor: "grab",
            color: "#999",
        }}
    />
));
const menuColumns = [
    // {
    //     title: "#",
    //     key: "id",
    //     dataIndex: "id",
    //     render: (text, record, index) => {
    //         return index + 1;
    //     },
    // },
    {
        title: "Sort",
        key: "sort",
        dataIndex: "sort",
        width: 30,
        className: "drag-visible",
        render: () => {
            return <DragHandle />;
        },
    },
    {
        title: "Nama Menu",
        key: "menu_label",
        // className: "drag-visible",
        dataIndex: "menu_label",
    },
    {
        title: "Menu Url",
        key: "menu_url",
        dataIndex: "menu_url",
    },
];

const roleColumns = [
    {
        title: "#",
        key: "id",
        dataIndex: "id",
        render: (text, record, index) => {
            return index + 1;
        },
    },
    {
        title: "Nama Role",
        key: "name",
        dataIndex: "name",
    },
];

export { menuColumns, roleColumns };
