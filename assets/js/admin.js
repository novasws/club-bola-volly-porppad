document.addEventListener("DOMContentLoaded", () => {
  const API_URL = "http://localhost/porppad/api.php";

  fetch(`${API_URL}?action=get_members`)
    .then((res) => res.json())
    .then((data) => {
      document.getElementById("totalAnggota").textContent = data.length;
    });

  fetch(`${API_URL}?action=get_trophies`)
    .then((res) => res.json())
    .then((data) => {
      document.getElementById("totalPrestasi").textContent = data.length;
    });

  fetch(`${API_URL}?action=get_kas`)
    .then((res) => res.json())
    .then((data) => {
      let saldo = 0;
      data.forEach((k) => {
        if (k.jenis === "Pemasukan") saldo += parseInt(k.jumlah);
        else saldo -= parseInt(k.jumlah);
      });
      document.getElementById("totalKas").textContent =
        "Rp " + saldo.toLocaleString();
    });
});

// === LOAD DATA ANGGOTA ===
function loadMembers() {
  fetch("http://localhost/porppad/api.php?action=get_members")
    .then((res) => res.json())
    .then((data) => {
      const putra = data.filter((m) => m.gender === "Putra");
      const putri = data.filter((m) => m.gender === "Putri");

      const tbodyPutra = document.querySelector("#tablePutra tbody");
      const tbodyPutri = document.querySelector("#tablePutri tbody");

      tbodyPutra.innerHTML = "";
      tbodyPutri.innerHTML = "";

      putra.forEach((m, i) => {
        tbodyPutra.innerHTML += `
          <tr>
            <td>${i + 1}</td>
            <td>${m.nama}</td>
            <td>${m.umur}</td>
            <td>${m.posisi}</td>
            <td>${m.alamat}</td>
            <td><img src="${
              m.foto || "../assets/img/default-profile.jpg"
            }" width="50" class="rounded"></td>
            <td><button class="btn btn-danger btn-sm" onclick="deleteMember(${
              m.id
            })"><i class="fa fa-trash"></i></button></td>
          </tr>`;
      });

      putri.forEach((m, i) => {
        tbodyPutri.innerHTML += `
          <tr>
            <td>${i + 1}</td>
            <td>${m.nama}</td>
            <td>${m.umur}</td>
            <td>${m.posisi}</td>
            <td>${m.alamat}</td>
            <td><img src="${
              m.foto || "../assets/img/default-profile.jpg"
            }" width="50" class="rounded"></td>
            <td><button class="btn btn-danger btn-sm" onclick="deleteMember(${
              m.id
            })"><i class="fa fa-trash"></i></button></td>
          </tr>`;
      });
    });
}

// === TAMBAH ANGGOTA ===
const form = document.getElementById("memberForm");
const modal = new bootstrap.Modal(document.getElementById("memberModal"));

document.getElementById("openAddMember").addEventListener("click", () => {
  form.reset();
  modal.show();
});

form.addEventListener("submit", (e) => {
  e.preventDefault();
  const data = new FormData(form);
  fetch("http://localhost/porppad/api.php?action=save_member", {
    method: "POST",
    body: data,
  })
    .then((res) => res.json())
    .then(() => {
      modal.hide();
      loadMembers();
    });
});

// === HAPUS ANGGOTA ===
function deleteMember(id) {
  if (!confirm("Hapus anggota ini?")) return;
  fetch("http://localhost/porppad/api.php?action=delete_member&id=" + id)
    .then((res) => res.json())
    .then(() => loadMembers());
}

// === AUTO LOAD ===
document.addEventListener("DOMContentLoaded", loadMembers);
