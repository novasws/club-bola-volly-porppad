document.addEventListener("DOMContentLoaded", () => {
  // Fetch jumlah anggota
  fetch("../api.php?action=get_members")
    .then((res) => res.json())
    .then((data) => {
      document.getElementById("totalMembers").textContent = data.length;
    });

  // Fetch jumlah prestasi
  fetch("../api.php?action=get_trophies")
    .then((res) => res.json())
    .then((data) => {
      document.getElementById("totalTrophies").textContent = data.length;
    });

  // Fetch saldo kas
  fetch("../api.php?action=get_kas")
    .then((res) => res.json())
    .then((data) => {
      let total = 0;
      data.forEach((k) => {
        if (k.jenis === "masuk") total += parseInt(k.jumlah);
        else total -= parseInt(k.jumlah);
      });
      document.getElementById("totalKas").textContent =
        "Rp " + total.toLocaleString("id-ID");
    });

  // Grafik kas
  fetch("../api.php?action=get_kas_chart")
    .then((res) => res.json())
    .then((data) => {
      const ctx = document.getElementById("kasChart");
      new Chart(ctx, {
        type: "bar",
        data: {
          labels: data.map((d) => d.bulan),
          datasets: [
            {
              label: "Total Kas Masuk",
              data: data.map((d) => d.total),
            },
          ],
        },
      });
    });
});
