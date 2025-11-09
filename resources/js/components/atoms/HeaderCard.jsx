import React from "react"
import { Link } from "react-router-dom"

const HeaderCard = ({
  href,
  title = "Lable",
  rightContent = null,
  onClick,
}) => {
  return (
    <div className="card"
      style={{
        position: "sticky",
        top: 170,
        zIndex: 10,
        background: "white",
        boxShadow: "0px 4px 6px rgba(0, 0, 0, 0.1)",
      }}>
      <div className="card-body">
        <h4 className="card-title text-capitalize flex justify-content-between align-items-center">
          <Link to={href} onClick={onClick}>
            <span>
              <i className="fas fa-arrow-left mr-3"></i>
              {title}
            </span>
          </Link>
          <span>{rightContent}</span>
        </h4>
      </div>
    </div>
  )
}

export default HeaderCard
