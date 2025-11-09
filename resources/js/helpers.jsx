import { message } from "antd"
import moment from "moment"
import { useEffect, useRef, useState } from "react"

const getBase64 = (img, callback) => {
  const reader = new FileReader()
  reader.addEventListener("load", () => callback(reader.result))
  reader.readAsDataURL(img)
}

const beforeUpload = (file) => {
  const isJpgOrPng = file.type === "image/jpeg" || file.type === "image/png"

  if (!isJpgOrPng) {
    message.error("You can only upload JPG/PNG file!")
  }

  const isLt2M = file.size / 1024 / 1024 < 2

  if (!isLt2M) {
    message.error("Image must smaller than 2MB!")
  }

  return isJpgOrPng && isLt2M
}

const subStr = (str, length = 25) => {
  if (str?.length > length) {
    return str?.substr(0, length) + "..."
  } else {
    return str
  }
}

// pluck
//
// Description: pluck an array of objects
//
// Arguments:
//   array: array of objects
//   key: key to pluck
//
// Returns: array of plucked values
//
// Example:
//   pluck([{a: 1}, {a: 2}], 'a')
//   // => [1, 2]
//
const pluck = (array, key) => array.map((item) => item[key])
// test
function sumPriceTotal(array) {
  let sum = 0
  // check if is array
  if (Array.isArray(array)) {
    array.map((item) => {
      sum += item.product.price.final_price * item.qty
    })
  }
  return sum
}

// export fuction format number indonesia
function formatNumber(number, prefix = null, defaultValue = 0) {
  // change number format it's number greater than 0
  if (number > 0) {
    const format = parseInt(number)
      .toString()
      .replace(/\B(?=(\d{3})+(?!\d))/g, ".")
    if (prefix) {
      return `${prefix} ${format}`
    }
    return format
  } else {
    return defaultValue
  }
}

const getStatusLeadOrder = (status = 1) => {
  switch (status) {
    case "-1":
      return "Draft"
    case "1":
      return "New"
    case "2":
      return "Open"
    case "3":
      return "Closed"
    case "4":
      return "Canceled"

    default:
      return "New"
  }
}

const statusDetailTransaksi = (status = 1) => {
  switch (status) {
    case 1:
      return "Waiting Payment"
    case 2:
      return "Checking Payment"
    case 3:
      return "Payment Confirmed"
    case 4:
      return "Canceled"
    case 7:
      return "Payment Confirmed"

    default:
      return "Canceled"
  }
}

const truncateString = (fullStr, strLen = 30, separator = "...") => {
  if (fullStr?.length <= strLen) {
    return fullStr
  }

  separator = separator || "..."

  var sepLen = separator?.length,
    charsToShow = strLen - sepLen,
    frontChars = Math.ceil(charsToShow / 2),
    backChars = Math.floor(charsToShow / 2)

  return (
    fullStr?.substr(0, frontChars) +
    separator +
    fullStr?.substr(fullStr?.length - backChars)
  )
}

const snakeToCapitalize = (string) => {
  if (string) {
    return string
      .replace(/^[-_]*(.)/, (_, c) => c.toUpperCase()) // Initial char (after -/_)
      .replace(/[-_]+(.)/g, (_, c) => " " + c.toUpperCase()) // First char after each -/_
  }

  return null
}

const capitalizeString = (str) => {
  if (str) {
    return str.replace(/^(.)(.*)$/, function (_, firstChar, restOfString) {
      return firstChar.toUpperCase() + restOfString
    })
  }
  return str
}

const capitalizeEachWord = (str) => {
  if (str) {
    return str
      .split(" ") // Split the string into an array of words
      .map((value, index) => capitalizeString(value)) // Capitalize the first letter of each word
      .join(" ") // Join the words back into a single string
  }

  return str
}

const RenderIf = ({ isTrue = false, children }) => (isTrue ? children : null)

const mapApiKey = "AIzaSyCH6ql7a8mP4xZmfZ-mqXejHTwzfuHqoMI"

const badgeColor = (color) => {
  switch (color) {
    case "New Lead":
      return "bg-purple-500"
    case "New":
      return "bg-purple-500"

    case "Waiting Approval":
      return "bg-secondaryOutlineColor"

    case "In Progress":
      return "bg-blueColor"

    case "Qualified":
      return "bg-green-500"
    case "Approved":
      return "bg-green-500"

    case "Rejected":
      return "bg-red-500"
    case "Unqualified":
      return "bg-red-500"
    case "Cancelled":
      return "bg-red-500 "

    case "Open":
      return "bg-blueColor"
    case "Closed":
      return "bg-blueColor"
    default:
      return "bg-movementColor"
  }
}

const useScript = (url) => {}

const removeArrayItemWithSpecificString = (arr, value) => {
  return arr.filter(function (ele) {
    return ele != value
  })
}

const formatDate = (date, format = "DD-MM-YYYY") => {
  if (date) {
    return moment(new Date(date)).format(format)
  }
  return "-"
}

const formatDateTime = (dateTime, format = "DD-MM-YYYY HH:mm:ss") => {
  if (dateTime) {
    return moment(new Date(dateTime)).format(format)
  }
  return "-"
}

const getItem = (key, parse = false) => {
  if (parse) {
    return JSON.parse(localStorage.getItem(key))
  }
  return localStorage.getItem(key)
}

//Hide Menu
const inArray = (needle, haystack) => {
  var length = haystack.length
  for (var i = 0; i < length; i++) {
    if (haystack[i] == needle) return true
  }
  return false
}

const groupBy = (array, key) => {
  if (array) {
    const ids = array.map((o) => o[key])
    const filtered = array.filter(
      (val, index) => !ids.includes(val[key], index + 1)
    )
    return filtered
  }

  return []
}
// sum value array object without key

const BOUNCE_RATE = 3000
const useDebounce = () => {
  const busy = useRef(false)

  const debounce = async (callback) => {
    setTimeout(() => {
      busy.current = false
    }, BOUNCE_RATE)

    if (!busy.current) {
      busy.current = true
      callback()
    }
  }

  return debounce
}

const handleString = (string, defaultValue = "-") => {
  if (
    string === "undefined" ||
    string === undefined ||
    string === "" ||
    string === null ||
    string === "null"
  ) {
    return defaultValue
  } else {
    return string
  }
}

const dummyTrack = [
  {
    id: 67,
    tiktok_order_id: "577468095692639011",
    description: "Your package is being packed.",
    update_time: "1687338196321",
    created_at: "2023-06-21T09:37:47.000000Z",
    updated_at: "2023-06-21T09:37:47.000000Z",
  },
  {
    id: 68,
    tiktok_order_id: "577468095692639011",
    description: "Your order was delivery.",
    update_time: "1688338196321",
    created_at: "2023-06-21T09:37:47.000000Z",
    updated_at: "2023-06-21T09:37:47.000000Z",
  },
  {
    id: 69,
    tiktok_order_id: "577469095692639011",
    description: "Your order was recieved.",
    update_time: "1688338196321",
    created_at: "2023-06-21T09:37:47.000000Z",
    updated_at: "2023-06-21T09:37:47.000000Z",
  },
]

function getInitials(fullName) {
  const names = fullName.split(" ")
  let initials = ""

  for (let i = 0; i < names.length; i++) {
    initials += names[i][0]
  }

  return initials
}

function paginateData(data, currentPage, pageSize) {
  const startIndex = (currentPage - 1) * pageSize
  const endIndex = startIndex + pageSize
  const paginatedData = data.slice(startIndex, endIndex)
  return paginatedData
}

function removeDuplicatesById(arr) {
  const uniqueObjects = []
  const uniqueIdsAndPoNumbers = new Set()

  for (const obj of arr) {
    const key = obj.id + obj.po_number
    if (!uniqueIdsAndPoNumbers.has(key)) {
      uniqueIdsAndPoNumbers.add(key)
      uniqueObjects.push(obj)
    }
  }

  return uniqueObjects
}

function checkAllObjectPropertiesValueFilled(array) {
  for (const obj of array) {
    for (const key in obj) {
      if (obj[key] === null || obj[key] === undefined) {
        return false
      }
    }
  }
  return true
}

function createQueryString(queryParams) {
  // Encode each key-value pair
  const encodedKeyValuePairs = Object.entries(queryParams).map(
    ([key, value]) => {
      return `${encodeURIComponent(key)}=${encodeURIComponent(value)}`
    }
  )

  // Join encoded pairs with & separator
  return `?${encodedKeyValuePairs.join("&")}`
}

// groupby before upload
const groupAndSumData = (data, type = "Code SO", key = "uid") => {
  const result = data.reduce((acc, item) => {
    // Membuat composite key dari type dan uid
    const typeValue = item[type]
    const uidValue = item[key]
    const otherKey = typeValue || uidValue
    const defaultKey = otherKey || item?.reference_number
    const id = typeValue && uidValue ? `${typeValue}_${uidValue}` : defaultKey // Gunakan salah satu jika yang lain kosong
    console.log(id, "id")
    if (!acc[id]) {
      acc[id] = { ...item, value: 0 }
    }
    acc[id].value += item.value
    return acc
  }, {})
  return Object.values(result)
}

// Fungsi untuk menghasilkan string acak dengan panjang tertentu
function generateRandomString(length) {
  var result = ""
  var characters =
    "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789"
  var charactersLength = characters.length
  for (var i = 0; i < length; i++) {
    result += characters.charAt(Math.floor(Math.random() * charactersLength))
  }
  return result
}

const maskingText = (name, masking = false) => {
  if (!masking) {
    return name
  }
  if (!name) {
    return ""
  }

  if (name?.length < 3) {
    return name
  }

  const parts = name.split(" ")
  const lastName = parts.pop() // Get the last name
  const firstPart = lastName.charAt(0) // First character of the last name
  if (lastName?.length > 0) {
    const lastPart = lastName.charAt(lastName?.length - 1) // Last character of the last name

    // Generate masked part
    const maskedPart = "*".repeat(lastName?.length - 2) // Mask all characters except first and last

    // Construct the masked last name
    const maskedLastName = firstPart + maskedPart + lastPart

    // Add the masked last name back to the parts
    parts.push(maskedLastName)
  }

  return parts.join(" ") // Join the parts back into a string
}

const validatePhoneNumber = (_, value) => {
  // Jika value kosong, skip validasi
  if (!value) {
    return Promise.resolve()
  }

  // Jika ada value, jalankan validasi
  let phone = formatPhone(value)

  if (phone.length < 11 || phone.length > 15) {
    return Promise.reject(
      "Nomor telepon tidak valid. Pastikan nomor 8-13 digit dan awalan benar"
    )
  }

  return Promise.resolve()
}

const validateEmail = (_, value) => {
  // Skip validasi jika value kosong, null, atau "-"
  if (!value || value === null || value === "-" || value === "null") {
    return Promise.resolve()
  }

  const emailRegex = /^[a-zA-Z0-9._]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/

  if (!emailRegex.test(value)) {
    return Promise.reject(
      "Masukkan alamat email yang valid! Hanya karakter alfanumerik, titik (.) dan garis bawah (_) yang diizinkan."
    )
  }

  return Promise.resolve()
}

function isEqual(obj1, obj2) {
  if (obj1 === obj2) {
    return true
  }

  if (
    obj1 == null ||
    obj2 == null ||
    typeof obj1 !== "object" ||
    typeof obj2 !== "object"
  ) {
    return false
  }

  if (Object.keys(obj1).length !== Object.keys(obj2).length) {
    return false
  }

  for (let key in obj1) {
    if (obj1.hasOwnProperty(key)) {
      if (!obj2.hasOwnProperty(key) || !isEqual(obj1[key], obj2[key])) {
        return false
      }
    }
  }

  return true
}

function compareObjects(obj1, obj2) {
  return new Promise((resolve, reject) => {
    if (!isEqual(obj1, obj2)) {
      resolve()
    } else {
      reject("Data sama, Tidak ada perubahan yang tersimpan")
    }
  })
}

function capitalizeFirstLetter(str) {
  if (!str) return "" // Check if the string is empty
  return str.charAt(0).toUpperCase() + str.slice(1)
}

function formatPhone(str) {
  if (!str) return "" // Mengembalikan string kosong jika input tidak ada

  // Hapus karakter non-digit di awal (seperti spasi atau simbol lain)
  str = str.replace(/[^\d\+]/g, "")

  // Jika nomor dimulai dengan "08", ubah menjadi "+628"
  if (str.startsWith("08")) {
    return "+628" + str.slice(2)
  }

  // Jika nomor dimulai dengan "628", tambahkan "+" di depan
  if (str.startsWith("628")) {
    return "+628" + str.slice(3)
  }

  // Jika nomor sudah dimulai dengan "+628", biarkan apa adanya
  if (str.startsWith("+628")) {
    return str
  }

  // Jika nomor dimulai dengan "021", biarkan apa adanya (nomor lokal)
  if (str.startsWith("02")) {
    return "+62" + str.slice(1)
  }
  if (str.startsWith("021")) {
    return "+62" + str.slice(1)
  }

  if (str.startsWith("622")) {
    return "+" + str
  }

  if (str.startsWith("+622")) {
    return str
  }

  // Jika input tidak sesuai format di atas, secara default tambahkan "+628" di depan
  return "+628" + str
}

function getStatusTransaction(status) {
  if (status == "confirm-payment" || status == "Confirm Payment") {
    return "Pembayaran Dikonfirmasi"
  } else if (status == "on-process" || status == "On Process") {
    return "Diproses Bagian Gudang"
  } else if (status == "ready-to-ship" || status == "Ready To Ship") {
    return "Siap Kirim"
  } else if (status == "on-delivery" || status == "On Delivery") {
    return "Pengiriman"
  } else if (status == "delivered" || status == "Delivered") {
    return "Pesanan Diterima"
  } else if (status == "waiting-payment" || status == "Waiting Payment") {
    return "Menunggu Pembayaran"
  } else if (status == "transaction" || status == "Transaction") {
    return "Transaksi"
  } else if (status == "group" || status == "Group") {
    return "Grup"
  } else if (
    status == "notification-template" ||
    status == "Notification Template"
  ) {
    return "Template Notifikasi"
  }

  return status.replace("-", " ")
}

export {
  getBase64,
  beforeUpload,
  pluck,
  sumPriceTotal,
  formatNumber,
  getStatusLeadOrder,
  statusDetailTransaksi,
  truncateString,
  snakeToCapitalize,
  RenderIf,
  subStr,
  maskingText,
  mapApiKey,
  badgeColor,
  useScript,
  removeArrayItemWithSpecificString,
  formatDate,
  formatDateTime,
  getItem,
  inArray,
  groupBy,
  useDebounce,
  handleString,
  dummyTrack,
  capitalizeString,
  capitalizeEachWord,
  getInitials,
  paginateData,
  removeDuplicatesById,
  checkAllObjectPropertiesValueFilled,
  createQueryString,
  groupAndSumData,
  generateRandomString,
  isEqual,
  compareObjects,
  capitalizeFirstLetter,
  formatPhone,
  getStatusTransaction,
  // validator
  validatePhoneNumber,
  validateEmail,
}
