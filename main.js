// ==========================================================================
// 1. Core Theme Logic Persistence Matrix
// ==========================================================================
const darkModeToggle = document.getElementById("darkModeToggle");

function setTheme(theme) {
  if (theme === "dark") {
    document.documentElement.setAttribute("data-bs-theme", "dark");
    if (darkModeToggle) darkModeToggle.checked = true;
    localStorage.setItem("theme", "dark");
  } else {
    document.documentElement.setAttribute("data-bs-theme", "light");
    if (darkModeToggle) darkModeToggle.checked = false;
    localStorage.setItem("theme", "light");
  }
}

// Initial structural theme scan
if (localStorage.getItem("theme") === "dark") {
  setTheme("dark");
} else {
  setTheme("light");
}

// Theme switch listener wrapper
if (darkModeToggle) {
  darkModeToggle.addEventListener("change", () => {
    darkModeToggle.checked ? setTheme("dark") : setTheme("light");
  });
}

// ==========================================================================
// 2. Interactive Visibility Password Toggle Logic
// ==========================================================================
document.querySelectorAll('.toggle-password').forEach(toggleButton => {
  toggleButton.addEventListener('click', function() {
    // Locate the matching input element using the data-target attribute string
    const targetId = this.getAttribute('data-target');
    const inputElement = document.getElementById(targetId);
    const iconElement = this.querySelector('i');

    if (inputElement && iconElement) {
      if (inputElement.type === 'password') {
        inputElement.type = 'text';
        iconElement.classList.replace('fa-eye-slash', 'fa-eye');
      } else {
        inputElement.type = 'password';
        iconElement.classList.replace('fa-eye', 'fa-eye-slash');
      }
    }
  });
});