// === LOAD DATA PRESTASI ===
function loadTrophies() {
  fetch("http://localhost/porppad/api.php?action=get_trophies")
    .then((res) => res.json())
    .then((data) => {
      const tbody = document.querySelector("#trophyTable tbody");
      tbody.innerHTML = "";
      data.forEach((t, i) => {
        tbody.innerHTML += `
          <tr>
            <td>${i + 1}</td>
            <td>${t.nama}</td>
            <td>${t.hasil}</td>
            <td>${t.tahun}</td>
            <td><img src="${
              t.foto || "../assets/img/default/trophy.jpg"
            }" width="60" class="rounded"></td>
            <td>
              <button class="btn btn-danger btn-sm" onclick="deleteTrophy(${
                t.id
              })">
                <i class="fa fa-trash"></i>
              </button>
            </td>
          </tr>`;
      });
    });
}

// === TAMBAH PRESTASI ===
const form = document.getElementById("trophyForm");
const modal = new bootstrap.Modal(document.getElementById("trophyModal"));

document.getElementById("openAddTrophy").addEventListener("click", () => {
  form.reset();
  modal.show();
});

form.addEventListener("submit", (e) => {
  e.preventDefault();
  const data = new FormData(form);
  fetch("http://localhost/porppad/api.php?action=save_trophy", {
    method: "POST",
    body: data,
  })
    .then((res) => res.json())
    .then(() => {
      modal.hide();
      loadTrophies();
    });
});

// === HAPUS PRESTASI ===
function deleteTrophy(id) {
  if (!confirm("Hapus prestasi ini?")) return;
  fetch("http://localhost/porppad/api.php?action=delete_trophy&id=" + id)
    .then((res) => res.json())
    .then(() => loadTrophies());
}

// === AUTO LOAD ===
document.addEventListener("DOMContentLoaded", loadTrophies);
