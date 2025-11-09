// Ant Design v5 Theme Configuration
// Replaces Less-based theming with CSS-in-JS token system
import { theme } from 'antd'

export const lightTheme = {
  token: {
    colorPrimary: '#1a56db',
    borderRadius: 4,
    // Additional tokens can be added here
    // See: https://ant.design/docs/react/customize-theme
  },
  algorithm: theme.defaultAlgorithm,
}

export const darkTheme = {
  token: {
    colorPrimary: '#1a56db',
    borderRadius: 4,
    // Dark theme specific tokens
    colorBgContainer: '#1a2034',
    colorBgElevated: '#1a2034',
    colorBorder: '#6f6c6c',
    colorSplit: '#424242',
  },
  algorithm: theme.darkAlgorithm,
}

// Helper function to get theme based on name
export const getTheme = (themeName) => {
  return themeName === 'dark' ? darkTheme : lightTheme
}
