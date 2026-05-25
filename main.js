const darkModeToggle = document.getElementById("darkModeToggle");

function setTheme(theme) {
  if (theme === "dark") {
    document.documentElement.setAttribute("data-bs-theme", "dark");
    darkModeToggle.checked = true;
    localStorage.setItem("theme", "dark");
  } else {
    document.documentElement.setAttribute("data-bs-theme", "light");
    darkModeToggle.checked = false;
    localStorage.setItem("theme", "light");
  }
}

if (localStorage.getItem("theme") === "dark") {
  setTheme("dark");
} else {
  setTheme("light");
}

darkModeToggle.addEventListener("change", () => {
  darkModeToggle.checked ? setTheme("dark") : setTheme("light");
});
