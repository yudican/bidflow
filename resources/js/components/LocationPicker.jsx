import React from "react";
import GoogleMapReact from "google-map-react";
import { mapApiKey } from "../helpers";

const AnyReactComponent = ({ text }) => <div>{text}</div>;

const LocationPicker = () => {
  const defaultProps = {
    center: {
      lat: 10.99835602,
      lng: 77.01502627,
    },
    zoom: 11,
  };

  return (
    <div style={{ height: 300, width: 400, margin: "auto", marginTop: 40 }}>
      <GoogleMapReact
        bootstrapURLKeys={{ key: mapApiKey }}
        defaultCenter={defaultProps.center}
        defaultZoom={defaultProps.zoom}
      >
        <AnyReactComponent lat={59.955413} lng={30.337844} text="My Marker" />
      </GoogleMapReact>
    </div>
  );
};

export default LocationPicker;
