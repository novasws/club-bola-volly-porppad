document.addEventListener("DOMContentLoaded", () => {
  AOS.init({ once: true, duration: 800 });

  const btns = document.querySelectorAll(
    "#btnLoginGabung,#btnHeroGabung,#btnCTAGabung,#btnJadwalGabung"
  );
  btns.forEach((btn) => {
    btn.addEventListener("click", () => {
      const modal = new bootstrap.Modal(document.getElementById("loginModal"));
      modal.show();
    });
  });

  // Navbar scroll effect
  window.addEventListener("scroll", () => {
    const nav = document.querySelector(".navbar");
    nav.classList.toggle("shadow-sm", window.scrollY > 20);
  });

  // Smooth scroll
  document.querySelectorAll('a[href^="#"]').forEach((a) => {
    a.addEventListener("click", function (e) {
      const t = document.querySelector(this.getAttribute("href"));
      if (t) {
        e.preventDefault();
        t.scrollIntoView({ behavior: "smooth" });
      }
    });
  });

  // Dummy login
  const formLogin = document.getElementById("formLoginMember");
  if (formLogin) {
    formLogin.addEventListener("submit", (e) => {
      e.preventDefault();
      alert("Login berhasil (demo)!");
      bootstrap.Modal.getInstance(document.getElementById("loginModal")).hide();
    });
  }
});
