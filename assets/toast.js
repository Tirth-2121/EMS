document.addEventListener("DOMContentLoaded", function () {
  // Create a container for toasts if it doesn't exist
  let toastContainer = document.createElement("div");
  toastContainer.className = "toast-container";
  document.body.appendChild(toastContainer);
});

function showToast(message, type = "success", duration = 3000) {
  const toastContainer = document.querySelector(".toast-container");

  // Create toast element
  let toast = document.createElement("div");
  toast.className = `toast toast-${type}`;
  toast.innerHTML = `
        <span>${message}</span>
        <button class="close-btn" onclick="this.parentElement.remove()">×</button>
    `;

  toastContainer.appendChild(toast);

  // Remove toast after duration
  setTimeout(() => {
    toast.style.animation = "fadeOut 0.5s ease-in-out";
    setTimeout(() => toast.remove(), 500);
  }, duration);
}

console.log("include this file toast");
