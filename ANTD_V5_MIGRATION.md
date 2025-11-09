# Ant Design v5 Migration Guide

## ✅ Sudah Selesai

### 1. Update Dependencies
- ✅ `antd`: ^4.24.14 → ^5.22.2
- ✅ `@ant-design/icons`: ^4.7.0 → ^5.5.1
- ✅ `npm install` telah dijalankan

### 2. Theme Configuration
- ✅ Dibuat file baru: `resources/js/themes/antdTheme.js`
  - Menggunakan token system (CSS-in-JS) menggantikan Less
  - Support light & dark theme
- ✅ Update `resources/js/index.jsx`:
  - Tambah `ConfigProvider` dari Ant Design v5
  - Integrasi dengan theme switcher yang sudah ada

## 🔍 Yang Perlu Diperhatikan

### Breaking Changes dari v4 ke v5

#### 1. **Less → CSS-in-JS**
- File `.less` tidak lagi digunakan oleh Ant Design
- Theme sekarang dikonfigurasi via `ConfigProvider` dengan token system
- Custom styling tetap bisa menggunakan Less/CSS biasa

#### 2. **Moment.js → Day.js**
Ant Design v5 sudah menggunakan Day.js secara default, bukan Moment.js.
- ✅ Project sudah memiliki `dayjs` di dependencies
- Cek apakah ada DatePicker/TimePicker yang perlu disesuaikan format-nya

#### 3. **Component API Changes**

Beberapa komponen mungkin memiliki perubahan API minor:

**Form**
- `form.setFieldsValue()` - masih sama
- `form.getFieldsValue()` - masih sama

**Table**
- Pagination props sedikit berubah
- Cek jika ada custom pagination

**Select**
- `dropdownMatchSelectWidth` default berubah
- Mode `tags` dan `multiple` lebih konsisten

**Modal**
- `visible` → `open` (deprecated tapi masih didukung)
- `onOk` dan `onCancel` tetap sama

**Notification/Message**
- API tetap sama, tapi styling bisa berbeda

#### 4. **CSS Class Names**
Beberapa class name internal Ant Design berubah dari `.ant-*` prefix.
Jika ada custom CSS yang override class Ant Design, perlu dicek ulang.

## 📋 Testing Checklist

Silakan test area-area berikut:

- [ ] **Forms** - Semua form input, validation, submit
- [ ] **Tables** - Pagination, sorting, filtering
- [ ] **Modals** - Open, close, submit
- [ ] **Date Pickers** - Format tanggal masih sesuai
- [ ] **Select/Dropdown** - Options, search, multiple select
- [ ] **Notifications** - Toast/message/notification styling
- [ ] **Breadcrumbs** - Yang baru saja diupdate di layout
- [ ] **Theme Switcher** - Toggle dark/light mode
- [ ] **Responsive** - Mobile view semua komponen

## 🎨 Customizing Theme

Untuk customize theme lebih lanjut, edit file `resources/js/themes/antdTheme.js`:

```javascript
export const lightTheme = {
  token: {
    colorPrimary: '#1a56db',        // Primary color
    colorSuccess: '#52c41a',        // Success color
    colorWarning: '#faad14',        // Warning color
    colorError: '#ff4d4f',          // Error color
    borderRadius: 4,                // Border radius
    fontSize: 14,                   // Base font size
    // Lihat semua token: https://ant.design/docs/react/customize-theme
  },
}
```

## 🔗 Resources

- [Ant Design v5 Migration Guide](https://ant.design/docs/react/migration-v5)
- [Ant Design v5 Customize Theme](https://ant.design/docs/react/customize-theme)
- [Token List](https://ant.design/docs/react/customize-theme#seedtoken)
- [Component Changes](https://ant.design/docs/react/migration-v5#component-changes)

## ⚠️ Known Issues

1. **Less Theme Files**: File `dark-theme.less` dan `light-theme.less` masih ada tapi tidak lagi digunakan oleh Ant Design v5. Bisa dihapus jika tidak ada custom styling lain.

2. **CSS File Generation**: Jika ada gulp task yang generate CSS dari Less Ant Design, perlu diupdate atau dihapus karena Ant Design v5 sudah tidak export Less variables.

## 🚀 Next Steps

1. ✅ Dependencies sudah diupdate
2. ✅ Theme configuration sudah dibuat
3. 🔄 Test semua fitur yang menggunakan Ant Design components
4. 🔄 Fix issues jika ditemukan
5. 🔄 Update custom styling jika diperlukan
