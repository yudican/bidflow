import React, { useState, useEffect } from 'react';
import Layout from "../../components/layout";
import { Card, Table, Input, Space, Button, message } from 'antd';
import { SearchOutlined, ReloadOutlined, EyeOutlined } from '@ant-design/icons';
import VisitDetailModal from './VisitDetail';

const VisitList = () => {
    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(false);
    const [searchText, setSearchText] = useState('');
    const [pagination, setPagination] = useState({
        current: 1,
        pageSize: 10,
        total: 0,
    });
    
    // Modal state
    const [modalVisible, setModalVisible] = useState(false);
    const [selectedPicName, setSelectedPicName] = useState('');

    // Fetch data from API
    const fetchData = async (page = 1, search = '') => {
        setLoading(true);
        try {
            const params = new URLSearchParams({
                page: page.toString(),
                per_page: pagination.pageSize.toString(),
            });
            
            if (search) {
                params.append('search', search);
            }

            const response = await fetch(`/api/accurate/visits/statistics?${params}`);
            const result = await response.json();
            
            if (result.status === 'success') {
                setData(result.data || []);
                setPagination(prev => ({
                    ...prev,
                    current: result.pagination?.current_page || 1,
                    total: result.pagination?.total || 0,
                }));
            } else {
                message.error('Failed to fetch data: ' + (result.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error fetching data:', error);
            message.error('Failed to fetch data');
        } finally {
            setLoading(false);
        }
    };

    // Load data on component mount
    useEffect(() => {
        fetchData();
    }, []);

    // Handle search
    const handleSearch = (value) => {
        setSearchText(value);
        setPagination(prev => ({ ...prev, current: 1 }));
        fetchData(1, value);
    };

    // Handle table change (pagination, sorting, filtering)
    const handleTableChange = (paginationInfo) => {
        const { current } = paginationInfo;
        setPagination(prev => ({ ...prev, current }));
        fetchData(current, searchText);
    };

    // Handle refresh
    const handleRefresh = () => {
        fetchData(pagination.current, searchText);
    };

    // Handle view detail - open modal instead of navigation
    const handleViewDetail = (picName) => {
        setSelectedPicName(picName);
        setModalVisible(true);
    };

    const columns = [
        {
            title: 'PIC Name',
            dataIndex: 'pic_name',
            key: 'pic_name',
            className: 'text-sm font-medium',
        },
        {
            title: 'Role',
            dataIndex: 'role',
            key: 'role',
            className: 'text-sm',
            render: (role) => (
                <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {role}
                </span>
            ),
        },
        {
            title: 'Total Store Assigned',
            dataIndex: 'total_store_assigned',
            key: 'total_store_assigned',
            className: 'text-sm text-center',
            render: (count) => (
                <span className="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-50 text-blue-700 font-semibold">
                    {count || 0}
                </span>
            ),
        },
        {
            title: 'Total Visit Store',
            dataIndex: 'total_visit_store',
            key: 'total_visit_store',
            className: 'text-sm text-center',
            render: (count) => (
                <span className="inline-flex items-center justify-center w-8 h-8 rounded-full bg-purple-50 text-purple-700 font-semibold">
                    {count || 0}
                </span>
            ),
        },
        // {
        //     title: 'Status',
        //     dataIndex: 'status',
        //     key: 'status',
        //     className: 'text-sm',
        //     render: (status) => {
        //         const statusConfig = {
        //             'Completed': { color: 'bg-green-100 text-green-800', text: 'Completed' },
        //             'Pending': { color: 'bg-yellow-100 text-yellow-800', text: 'Pending' },
        //             'Unknown': { color: 'bg-gray-100 text-gray-800', text: 'Unknown' },
        //         };
                
        //         const config = statusConfig[status] || statusConfig['Unknown'];
                
        //         return (
        //             <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${config.color}`}>
        //                 {config.text}
        //             </span>
        //         );
        //     },
        // },
        {
            title: 'Action',
            key: 'action',
            className: 'text-sm',
            render: (_, record) => (
                <Button
                    type="primary"
                    size="small"
                    icon={<EyeOutlined />}
                    onClick={() => handleViewDetail(record.pic_name)}
                >
                    View Details
                </Button>
            ),
        },
    ];

    return (
        <Layout>
            <div style={{ padding: '24px' }}>
                <Card 
                    title="Visit List Dashboard" 
                    style={{ 
                        borderRadius: '8px',
                        boxShadow: '0 2px 8px rgba(0,0,0,0.1)'
                    }}
                >
                    <Space style={{ marginBottom: 16, width: '100%', justifyContent: 'space-between' }}>
                        <Input.Search
                            placeholder="Search by PIC name or role..."
                            allowClear
                            enterButton={<SearchOutlined />}
                            size="large"
                            style={{ width: 400 }}
                            onSearch={handleSearch}
                            value={searchText}
                            onChange={(e) => setSearchText(e.target.value)}
                        />
                        <Button 
                            type="primary" 
                            icon={<ReloadOutlined />} 
                            onClick={handleRefresh}
                            loading={loading}
                        >
                            Refresh
                        </Button>
                    </Space>

                    <Table
                        columns={columns}
                        dataSource={data}
                        rowKey="pic_name"
                        loading={loading}
                        pagination={{
                            ...pagination,
                            showSizeChanger: true,
                            showQuickJumper: true,
                            showTotal: (total, range) => 
                                `${range[0]}-${range[1]} of ${total} items`,
                        }}
                        onChange={handleTableChange}
                        scroll={{ x: 800 }}
                        style={{
                            backgroundColor: '#fff',
                            borderRadius: '6px',
                        }}
                    />
                </Card>
            </div>
            
            {/* Visit Detail Modal */}
            <VisitDetailModal
                open={modalVisible}
                onClose={() => setModalVisible(false)}
                picName={selectedPicName}
            />
        </Layout>
    );
};

export default VisitList;