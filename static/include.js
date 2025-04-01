function includeHTML() {
    document.querySelectorAll('[data-include]').forEach(async (el) => {
      const file = el.getAttribute('data-include');
      try {
        const res = await fetch(file);
        const html = await res.text();
        el.innerHTML = html;
  
        if (file.includes("navbar")) {
          highlightCurrentLink();
        }
      } catch (err) {
        console.error(`Erreur de chargement : ${file}`, err);
      }
    });
  
    // Ajouter dynamiquement le style commun
    const cssId = 'navbar-footer-css';
    if (!document.getElementById(cssId)) {
      const link = document.createElement('link');
      link.id = cssId;
      link.rel = 'stylesheet';
      link.href = 'css/navbar-footer.css';
      document.head.appendChild(link);
    }
  }
  
  function highlightCurrentLink() {
    const path = window.location.pathname;
    const page = path.split("/").pop(); // ex: "wishlist.html"
  
    document.querySelectorAll(".nav-link").forEach(link => {
      if (link.getAttribute("href") === page) {
        link.classList.add("active");
      }
    });
  }
  
  window.addEventListener("DOMContentLoaded", includeHTML);
  