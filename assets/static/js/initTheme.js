const body = document.body;
let theme = window.sessionTheme || localStorage.getItem('theme');

if (!theme) {
  // Default to dark theme if no theme is set
  theme = 'dark';
  localStorage.setItem('theme', theme);
}

document.documentElement.setAttribute('data-bs-theme', theme);
