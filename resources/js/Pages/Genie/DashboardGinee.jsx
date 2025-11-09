import { InfoCircleOutlined } from "@ant-design/icons";
import { Button, Checkbox, DatePicker, Radio } from "antd";
import axios from "axios";
import React, { useEffect, useState } from "react";
import { StatusCardDashboardGinee } from "../../components/CardReusable";
import { LineChartDashboardGinee } from "../../components/ChartReusable";
import Layout from "../../components/layout";
import LoadingFallback from "../../components/LoadingFallback";
import { channel } from "./DummyGinee";

// antd ui
const { RangePicker } = DatePicker;

const DashboardGinee = () => {
  // state
  const [loading, setLoading] = useState(false);
  // const [dataGinee, setDataGinee] = useState([]);
  const [dataDashboard, setDataDashboard] = useState(null);
  console.log(dataDashboard, "dataDashboard");
  const [time, setTime] = useState("year");
  const [selectedChannel, setSelectedChannel] = useState([]);
  const [checkedChannel, setCheckedChannel] = useState(
    new Array(channel.length).fill(false)
  );
  const [stores, setStores] = useState([]);
  const [selectedStore, setSelectedStore] = useState([]);
  const [checkedStore, setCheckedStore] = useState([]);

  // params for api
  const body = {
    channel: selectedChannel?.sort((a, b) => a.label - b.label),
    shopId: selectedStore?.sort((a, b) => a.store_name - b.store_name),
    time: time,
  };

  // function
  const handleOnChangeTime = (e) => {
    setTime(e.target.value);
  };

  const resetCheckedChannel = () =>
    setCheckedChannel(new Array(channel.length).fill(false));

  const handleOnChangeChannel = (position, value, isChecked) => {
    const updatedCheckedState = checkedChannel.map((item, index) =>
      index === position ? !item : item
    );
    setCheckedChannel(updatedCheckedState);
    isChecked
      ? setSelectedChannel((prevState) => [...prevState, value])
      : setSelectedChannel((prevState) =>
          prevState.filter((item) => item !== value)
        );
  };

  const resetCheckedStore = () =>
    setCheckedStore(new Array(stores.length).fill(false));

  const handleOnChangeStore = (position, value, isChecked) => {
    const updatedCheckedState = checkedStore.map((item, index) =>
      index === position ? !item : item
    );
    setCheckedStore(updatedCheckedState);
    isChecked
      ? setSelectedStore((prevState) => [...prevState, value])
      : setSelectedStore((prevState) =>
          prevState.filter((item) => item !== value)
        );
  };

  // api
  // const loadDataGinee = (
  //   url = "/api/genie/order/list",
  //   perpage = 10,
  //   params = {}
  // ) => {
  //   setLoading(true);
  //   axios
  //     .post(url, { perpage, ...params })
  //     .then((res) => {
  //       const { data } = res.data.data;
  //       setDataGinee(data);
  //       setLoading(false);
  //     })
  //     .catch((err) => {
  //       console.log(err, "error gine order list");
  //       setLoading(false);
  //     });
  // };

  const loadDashboard = (params) => {
    setLoading(true);
    axios
      .post("/api/genie/dashboard", params)
      .then((res) => {
        // console.log(res.data.data, "res dashboard ginee");
        setDataDashboard(res.data.data.dashboard);
        setStores(res.data.data.stores);
        setCheckedStore(new Array(stores.length).fill(false));
        setLoading(false);
      })
      .catch((err) => {
        console.log(err, "error dashboard ginee");
        setLoading(false);
      });
  };

  // lifecycle
  useEffect(() => {
    // loadDataGinee();
    loadDashboard(body);
  }, []);

  if (loading) {
    return <LoadingFallback />;
  }

  return (
    <Layout title="Dashboard Ginee">
      {/* search and filter container */}
      <div className="card md:p-4 p-2">
        {/* time container */}
        <div className="md:flex mb-2">
          <div className="mr-4">
            <strong className="mr-4 font-semibold">Time : </strong>
            <Radio.Group value={time} onChange={handleOnChangeTime}>
              <Radio.Button value="yesterday">Yesterday</Radio.Button>
              <Radio.Button value="week">Weekly</Radio.Button>
              <Radio.Button value="month">Monthly</Radio.Button>
              <Radio.Button value="year">Yearly</Radio.Button>
            </Radio.Group>
          </div>
          <div>
            <RangePicker
              format={"YYYY-MM-DD"}
              onChange={(e, dateString) => setTime(dateString)}
            />
          </div>
        </div>

        {/* channel container */}
        <div id="channelCheckbox" className="mb-2">
          <strong className="mr-4 font-semibold">Channel : </strong>
          <Checkbox
            onChange={() => {
              setSelectedChannel([]);
              resetCheckedChannel();
            }}
            checked={selectedChannel.length === 0}
          >
            All
          </Checkbox>
          {channel.map((value, index) => (
            <Checkbox
              key={index}
              onChange={(e) => {
                handleOnChangeChannel(index, value.value, e.target.checked);
              }}
              checked={checkedChannel[index]}
            >
              {" "}
              {value.label}
            </Checkbox>
          ))}
        </div>

        {/* store container */}
        <div className="mb-2">
          <strong className="mr-4 font-semibold">Store : </strong>
          <Checkbox
            onChange={() => {
              setSelectedStore([]);
              resetCheckedStore();
            }}
            checked={selectedStore.length === 0}
          >
            All
          </Checkbox>
          {stores.map((value, index) => (
            <Checkbox
              key={index}
              onChange={(e) => {
                handleOnChangeStore(index, value.shopId, e.target.checked);
              }}
              checked={checkedStore[index]}
            >
              {" "}
              {value.store_name}
            </Checkbox>
          ))}
        </div>

        <div className="flex self-center py-4">
          <Button
            onClick={() => loadDashboard(body)}
            style={{
              marginRight: 16,
              backgroundColor: "#6245FF",
              color: "white",
              borderRadius: 4,
            }}
          >
            Filter
          </Button>

          <Button
            onClick={() => {
              setTime("week");
              resetCheckedChannel();
              setSelectedChannel([]);
              resetCheckedStore();
              setSelectedStore([]);
              loadDashboard(body);
            }}
            style={{
              marginRight: 16,
              backgroundColor: "white",
              color: "black",
              borderRadius: 4,
            }}
          >
            Reset
          </Button>
        </div>
      </div>

      {/* status card container */}
      <div className="grid md:grid-cols-4 gap-5 mb-4">
        <StatusCardDashboardGinee
          borderLeftColor="border-l-[#6245FF]"
          title="Total Order Number"
          icon={<InfoCircleOutlined />}
          subTitle={dataDashboard?.total_order_number}
        />
        <StatusCardDashboardGinee
          borderLeftColor="border-l-[#E93733]"
          title="Total Order Amount"
          icon={<InfoCircleOutlined />}
          subTitle={dataDashboard?.total_order_amount}
        />
        <StatusCardDashboardGinee
          borderLeftColor="border-l-[#5884FF]"
          title="Shipping Fee"
          icon={<InfoCircleOutlined />}
          subTitle={dataDashboard?.total_ongkir_dibayar_sistem}
        />
        <StatusCardDashboardGinee
          borderLeftColor="border-l-[#6245FF]"
          title="Return Order"
          icon={<InfoCircleOutlined />}
          subTitle={dataDashboard?.total_return}
        />
        <StatusCardDashboardGinee
          borderLeftColor="border-l-[#F8B73A]"
          title="Total Refund Amount"
          icon={<InfoCircleOutlined />}
          subTitle={dataDashboard?.total_refund_amount}
        />
        <StatusCardDashboardGinee
          borderLeftColor="border-l-[#E93733]"
          title="Total Biaya Komisi"
          icon={<InfoCircleOutlined />}
          subTitle={dataDashboard?.total_biaya_komisi}
        />
        <StatusCardDashboardGinee
          borderLeftColor="border-l-[#5884FF]"
          title="Total Ongkir Dibayar Sistem"
          icon={<InfoCircleOutlined />}
          subTitle={dataDashboard?.total_ongkir_dibayar_sistem}
        />
        <StatusCardDashboardGinee
          borderLeftColor="border-l-[#F8B73A]"
          title="Total Biaya Layanan"
          icon={<InfoCircleOutlined />}
          subTitle={dataDashboard?.total_biaya_layanan}
        />
      </div>

      {/* chart container */}
      <div className="card p-4">
        <div className="mb-4">
          <strong className="font-bold">Trend Chart (Curreny: IDR)</strong>
        </div>
        <LineChartDashboardGinee
          data={dataDashboard?.charts}
          filterType={time}
        />
      </div>
    </Layout>
  );
};

export default DashboardGinee;
