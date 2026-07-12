// Admin Panel JavaScript
document.addEventListener("DOMContentLoaded", () => {
  // Initialize all components
  initSidebar()
  initDropdowns()
  initTooltips()
  initAlerts()
  initForms()

  // Sidebar functionality
  function initSidebar() {
    const sidebar = document.querySelector(".admin-sidebar")
    const content = document.querySelector(".admin-content")
    const toggleBtn = document.querySelector(".sidebar-toggle")
    const overlay = document.querySelector(".sidebar-overlay")

    // Toggle sidebar
    if (toggleBtn) {
      toggleBtn.addEventListener("click", () => {
        if (window.innerWidth <= 768) {
          // Mobile behavior
          sidebar.classList.toggle("show")
          if (overlay) {
            overlay.classList.toggle("show")
          }
        } else {
          // Desktop behavior
          sidebar.classList.toggle("collapsed")
          content.classList.toggle("expanded")
        }
      })
    }

    // Close sidebar on overlay click (mobile)
    if (overlay) {
      overlay.addEventListener("click", () => {
        sidebar.classList.remove("show")
        overlay.classList.remove("show")
      })
    }

    // Handle dropdown menus in sidebar
    const menuItems = document.querySelectorAll('.menu-link[data-toggle="submenu"]')
    menuItems.forEach((item) => {
      item.addEventListener("click", function (e) {
        e.preventDefault()

        const submenu = this.nextElementSibling
        const arrow = this.querySelector(".menu-arrow")

        if (submenu && submenu.classList.contains("submenu")) {
          // Close other submenus
          menuItems.forEach((otherItem) => {
            if (otherItem !== item) {
              const otherSubmenu = otherItem.nextElementSibling
              const otherArrow = otherItem.querySelector(".menu-arrow")
              if (otherSubmenu) {
                otherSubmenu.classList.remove("show")
              }
              if (otherArrow) {
                otherArrow.classList.remove("rotated")
              }
            }
          })

          // Toggle current submenu
          submenu.classList.toggle("show")
          if (arrow) {
            arrow.classList.toggle("rotated")
          }
        }
      })
    })

    // Set active menu item
    const currentPath = window.location.pathname
    const menuLinks = document.querySelectorAll(".menu-link, .submenu-link")
    menuLinks.forEach((link) => {
      const href = link.getAttribute("href")
      if (href && currentPath.includes(href.split("/").pop())) {
        link.classList.add("active")

        // If it's a submenu link, show parent submenu
        if (link.classList.contains("submenu-link")) {
          const submenu = link.closest(".submenu")
          const parentLink = submenu.previousElementSibling
          const arrow = parentLink.querySelector(".menu-arrow")

          submenu.classList.add("show")
          if (arrow) {
            arrow.classList.add("rotated")
          }
        }
      }
    })
  }

  // Dropdown functionality
  // function initDropdowns() {
  //   // Bootstrap dropdown initialization
  //   const dropdownTriggers = document.querySelectorAll('[data-bs-toggle="dropdown"]')
  //   dropdownTriggers.forEach((trigger) => {
  //     trigger.addEventListener("click", function (e) {
  //       e.preventDefault()
  //       const menu = this.nextElementSibling
  //
  //       // Close other dropdowns
  //       document.querySelectorAll(".dropdown-menu.show").forEach((openMenu) => {
  //         if (openMenu !== menu) {
  //           openMenu.classList.remove("show")
  //         }
  //       })
  //
  //       // Toggle current dropdown
  //       if (menu && menu.classList.contains("dropdown-menu")) {
  //         menu.classList.toggle("show")
  //       }
  //     })
  //   })
  //
  //   // Close dropdowns when clicking outside
  //   document.addEventListener("click", (e) => {
  //     if (!e.target.closest(".dropdown")) {
  //       document.querySelectorAll(".dropdown-menu.show").forEach((menu) => {
  //         menu.classList.remove("show")
  //       })
  //     }
  //   })
  // }

  // Tooltip functionality
  function initTooltips() {
    const tooltipElements = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    tooltipElements.forEach((element) => {
      element.addEventListener("mouseenter", function () {
        const title = this.getAttribute("title") || this.getAttribute("data-bs-title")
        if (title) {
          showTooltip(this, title)
        }
      })

      element.addEventListener("mouseleave", () => {
        hideTooltip()
      })
    })
  }

  function showTooltip(element, text) {
    const tooltip = document.createElement("div")
    tooltip.className = "custom-tooltip"
    tooltip.textContent = text
    tooltip.style.cssText = `
            position: absolute;
            background: #333;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            z-index: 9999;
            pointer-events: none;
        `

    document.body.appendChild(tooltip)

    const rect = element.getBoundingClientRect()
    tooltip.style.left = rect.left + rect.width / 2 - tooltip.offsetWidth / 2 + "px"
    tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + "px"
  }

  function hideTooltip() {
    const tooltip = document.querySelector(".custom-tooltip")
    if (tooltip) {
      tooltip.remove()
    }
  }

  // Alert functionality
  function initAlerts() {
    const alertCloseButtons = document.querySelectorAll(".alert .btn-close")
    alertCloseButtons.forEach((button) => {
      button.addEventListener("click", function () {
        const alert = this.closest(".alert")
        if (alert) {
          alert.style.opacity = "0"
          setTimeout(() => {
            alert.remove()
          }, 300)
        }
      })
    })

    // Auto-hide alerts after 5 seconds
    const autoHideAlerts = document.querySelectorAll('.alert[data-auto-hide="true"]')
    autoHideAlerts.forEach((alert) => {
      setTimeout(() => {
        alert.style.opacity = "0"
        setTimeout(() => {
          alert.remove()
        }, 300)
      }, 5000)
    })
  }

  // Form functionality
  function initForms() {
    // Form validation
    const forms = document.querySelectorAll('form[data-validate="true"]')
    forms.forEach((form) => {
      form.addEventListener("submit", function (e) {
        if (!validateForm(this)) {
          e.preventDefault()
        }
      })
    })

    // File upload preview
    const fileInputs = document.querySelectorAll('input[type="file"][data-preview="true"]')
    fileInputs.forEach((input) => {
      input.addEventListener("change", function () {
        previewFile(this)
      })
    })

    // Auto-resize textareas
    const textareas = document.querySelectorAll('textarea[data-auto-resize="true"]')
    textareas.forEach((textarea) => {
      textarea.addEventListener("input", function () {
        this.style.height = "auto"
        this.style.height = this.scrollHeight + "px"
      })
    })
  }

  function validateForm(form) {
    let isValid = true
    const requiredFields = form.querySelectorAll("[required]")

    requiredFields.forEach((field) => {
      if (!field.value.trim()) {
        showFieldError(field, "यह फील्ड आवश्यक है")
        isValid = false
      } else {
        clearFieldError(field)
      }
    })

    // Email validation
    const emailFields = form.querySelectorAll('input[type="email"]')
    emailFields.forEach((field) => {
      if (field.value && !isValidEmail(field.value)) {
        showFieldError(field, "कृपया वैध ईमेल पता दर्ज करें")
        isValid = false
      }
    })

    return isValid
  }

  function showFieldError(field, message) {
    clearFieldError(field)

    const errorDiv = document.createElement("div")
    errorDiv.className = "field-error text-danger small mt-1"
    errorDiv.textContent = message

    field.classList.add("is-invalid")
    field.parentNode.appendChild(errorDiv)
  }

  function clearFieldError(field) {
    field.classList.remove("is-invalid")
    const existingError = field.parentNode.querySelector(".field-error")
    if (existingError) {
      existingError.remove()
    }
  }

  function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return emailRegex.test(email)
  }

  function previewFile(input) {
    const file = input.files[0]
    if (file) {
      const reader = new FileReader()
      reader.onload = (e) => {
        let preview = input.parentNode.querySelector(".file-preview")
        if (!preview) {
          preview = document.createElement("div")
          preview.className = "file-preview mt-2"
          input.parentNode.appendChild(preview)
        }

        if (file.type.startsWith("image/")) {
          preview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 4px;">`
        } else {
          preview.innerHTML = `<p class="text-muted">फाइल चुनी गई: ${file.name}</p>`
        }
      }
      reader.readAsDataURL(file)
    }
  }

  // Utility functions
  window.showAlert = (message, type = "info") => {
    const alertDiv = document.createElement("div")
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`
    alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" aria-label="Close"></button>
        `

    const container = document.querySelector(".main-content")
    if (container) {
      container.insertBefore(alertDiv, container.firstChild)

      // Auto-hide after 5 seconds
      setTimeout(() => {
        alertDiv.style.opacity = "0"
        setTimeout(() => {
          alertDiv.remove()
        }, 300)
      }, 5000)
    }
  }

  window.confirmAction = (message, callback) => {
    if (confirm(message)) {
      callback()
    }
  }

  // AJAX helper
  window.ajaxRequest = (url, data, method = "POST") =>
    fetch(url, {
      method: method,
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
      body: method !== "GET" ? JSON.stringify(data) : null,
    })
      .then((response) => response.json())
      .catch((error) => {
        console.error("AJAX Error:", error)
        window.showAlert("कुछ गलत हुआ। कृपया पुनः प्रयास करें।", "danger")
      })

  // Handle responsive behavior
  function handleResize() {
    const sidebar = document.querySelector(".admin-sidebar")
    const overlay = document.querySelector(".sidebar-overlay")

    if (window.innerWidth > 768) {
      if (sidebar) {
        sidebar.classList.remove("show")
      }
      if (overlay) {
        overlay.classList.remove("show")
      }
    }
  }

  window.addEventListener("resize", handleResize)

  // Initialize on page load
  handleResize()
})

// Additional utility functions for specific admin features
function deleteItem(id, type, element) {
  if (confirm("क्या आप वाकई इसे हटाना चाहते हैं?")) {
    // AJAX call to delete item
    window.ajaxRequest(`delete_${type}.php`, { id: id }, "POST").then((response) => {
      if (response.success) {
        element.closest("tr").remove()
        window.showAlert("आइटम सफलतापूर्वक हटा दिया गया।", "success")
      } else {
        window.showAlert("हटाने में त्रुटि: " + response.message, "danger")
      }
    })
  }
}

function toggleStatus(id, type, element) {
  const currentStatus = element.textContent.trim()
  const newStatus = currentStatus === "सक्रिय" ? "निष्क्रिय" : "सक्रिय"

  window.ajaxRequest(`toggle_status.php`, { id: id, type: type }, "POST").then((response) => {
    if (response.success) {
      element.textContent = newStatus
      element.className = newStatus === "सक्रिय" ? "badge badge-success" : "badge badge-danger"
      window.showAlert("स्थिति सफलतापूर्वक अपडेट की गई।", "success")
    } else {
      window.showAlert("अपडेट में त्रुटि: " + response.message, "danger")
    }
  })
}

// Export functions for global use
window.deleteItem = deleteItem
window.toggleStatus = toggleStatus
