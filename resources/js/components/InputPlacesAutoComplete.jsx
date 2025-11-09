import { Input } from "antd"
import React from "react"
import PlacesAutocomplete from "react-places-autocomplete"

const InputPlacesAutoComplete = ({ value, onChange, onSelect }) => {
  return (
    <PlacesAutocomplete value={value} onChange={onChange} onSelect={onSelect}>
      {({ getInputProps, suggestions, getSuggestionItemProps, loading }) => (
        <div>
          <Input
            {...getInputProps({
              placeholder: "Search Places ....",
              className: "location-search-input",
            })}
          />
          <div className="autocomplete-dropdown-container">
            {loading && <div>Loading...</div>}
            {suggestions.map((suggestion) => {
              const className = suggestion.active
                ? "suggestion-item--active"
                : "suggestion-item"
              // console.log("suggestion", suggestion);
              // inline style for demonstration purpose
              const style = suggestion.active
                ? { backgroundColor: "#fafafa", cursor: "pointer" }
                : { backgroundColor: "#ffffff", cursor: "pointer" }
              return (
                <div
                  key={suggestion?.index}
                  {...getSuggestionItemProps(suggestion, {
                    className,
                    style,
                  })}
                >
                  <span>{suggestion.description}</span>
                </div>
              )
            })}
          </div>
        </div>
      )}
    </PlacesAutocomplete>
  )
}

export default InputPlacesAutoComplete
